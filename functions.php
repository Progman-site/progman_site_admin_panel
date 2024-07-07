<?php

use ImageSigner\ImageBlank;
use ImageSigner\constants\Fonts;
use ImageSigner\constants\Colors;
use libs\QRCode;


function sqlConnect():mysqli
{
    $dbConnect = mysqli_connect(
        config("database.host"),
        config("database.user"),
        config("database.password"),
        config("database.name")
    );

    mysqli_query($dbConnect, 'SET NAMES utf8');
    if (!$dbConnect) {
        throw new Exception(
            'Connection error (' .
            mysqli_connect_errno() .
            ') ' .
            mysqli_connect_error()
        );
    }
    return $dbConnect;
}

function sqlQuery(string $query, bool $multiAnswer = true): array|int|bool|null {
    global $connect;
    $result = mysqli_query($connect, $query);
    if (is_bool($result) && $result) {
        if (stripos($query, "insert") !== false){
            return mysqli_insert_id($connect);
        } elseif (stripos($query, "update") !== false){
            return mysqli_affected_rows($connect);
        } elseif (stripos($query, "delete") !== false){
            return $result;
        }
    } elseif ($result === false) {
        if (mysqli_errno($connect)){
            printError(mysqli_error($connect));
        }
    }
    return $multiAnswer ? mysqli_fetch_all($result, MYSQLI_ASSOC) : mysqli_fetch_assoc($result);
}
function getLanguage(): string {
    return isset($_SESSION[LANG_SESSION_KEY]) ? LANG_SESSION_KEY : DEFAULT_LANGUAGE;
}
function getSiteInfo(): array {
    $siteInfo = sqlQuery("
        SELECT * FROM `tags` t
        LEFT JOIN `tag_tag_value` tv ON tv.`tag_id` = t.`id`
        LEFT JOIN `tag_values` v ON v.`id` = tv.`tag_value_id`
        ORDER BY t.`order` DESC;
    ");
    $tagsArray = [];
    foreach ($siteInfo as $item) {
        $tagsArray[$item['name']][$item['content'] ?? DEFAULT_LANGUAGE] = $item;
    }
    return $tagsArray;
}

function getServicesImages(): array {
    return @sqlQuery("SELECT * FROM `pricelist` WHERE `image` is not null and `show` = 1 ORDER BY `order` DESC;");
}

function checkUser(string $login, string $password, string $level = 'admin'): ?array {
    $user = sqlQuery(
        "SELECT * FROM `admins` WHERE `login` = '{$login}' AND `access` = '{$level}' AND `is_active` = 1;",
        false
    );
    if (@$user['id'] && password_verify($password, $user['password'])){
        return $user;
    }
    return null;
}

function updateSiteInfo($connect): string
{
    mysqli_begin_transaction($connect);
    foreach ($_FILES as $fileInputName => $fileData) {
        $imgPath = "./images/{$_FILES[$fileInputName]['name']}";
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $imgPath)) {
            sqlQuery("UPDATE `language_contents` SET `file_url` = '{$imgPath}' WHERE `tag` = '{$fileInputName}'");
        }
    }
    $updatedTags = [];
    foreach ($_POST as $field => $value) {
        $fieldParts = explode('-', $field);
        $tag = sqlQuery("SELECT * FROM `tags` WHERE `name` = '{$fieldParts[0]}'", false);
        if (!$tag) {
            throw new Exception("Error tag(name:{$fieldParts[0]}) is not found!");
        }
        $updatedTags[] = $tag["name"];
        $valueRecord = sqlQuery("
            SELECT v.`id` 
            FROM `tag_tag_value` tv
            LEFT JOIN `tags` t ON t.`id` = tv.`tag_id`
            LEFT JOIN `tag_values` v ON v.`id` = tv.`tag_value_id`
            WHERE t.`id` = '{$tag["id"]}' AND v.content = '{$fieldParts[1]}';
        ", false);

        $value = str_replace("'", "\'", $value);
        if (!$valueRecord && $value) {
            $valueId = sqlQuery("INSERT INTO `tag_values` SET `content` = '{$fieldParts[1]}', `value`='{$value}';");
            if (!$valueId) {
                throw new Exception("Error while adding tag value '{$tag["name"]}' with content '{$fieldParts[1]}'");
            }
            if (!sqlQuery("INSERT INTO `tag_tag_value` SET `tag_id` = '{$tag["id"]}', `tag_value_id` = '{$valueId}';")) {
                throw new Exception("Error while connecting tag {$tag["name"]} with value {$valueId}({$fieldParts[1]})");
            }
            continue;
        }
        if (!sqlQuery("UPDATE `tag_values` SET `value` = '{$value}' WHERE `id` = '{$valueRecord["id"]}'")) {
            throw new Exception("Error while updating tag {$tag["name"]}");
        }

    }
    mysqli_commit($connect);
    return "Tags (" . count($updatedTags)."pс) have been successfully updated!\n\n" . implode("\n", $updatedTags);
}

function getUserByFieldName(string $field, string $value):?array {
    $db_field = "";
    $table = "";
    switch ($field) {
        case "tg_name":
            $secondField = "tg_id";
            $secondTable = "emails";
            $firstFieldSecondTable = "email_id";
            $secondFieldSecondTable = "email_name";
            $db_field = "service_login";
            $db_secondField = "service_uid";
            $table = "telegrams";
            break;
        case "tg_id":
            $secondField = "tg_name";
            $secondTable = "emails";
            $firstFieldSecondTable = "email_id";
            $secondFieldSecondTable = "email_name";
            $db_field = "service_uid";
            $db_secondField = "service_login";
            $table = "telegrams";
            break;
        case "email_name":
            $secondField = "email_id";
            $secondTable = "telegrams";
            $firstFieldSecondTable = "tg_id";
            $secondFieldSecondTable = "tg_name";
            $db_field = "service_login";
            $db_secondField = "service_uid";
            $table = "emails";
            break;
        case "email_id":
            $secondField = "email_name";
            $secondTable = "telegrams";
            $firstFieldSecondTable = "tg_id";
            $secondFieldSecondTable = "tg_name";
            $db_field = "service_uid";
            $db_secondField = "service_login";
            $table = "emails";
            break;
    }
    $uid =  sqlQuery(
        "SELECT * FROM `{$table}` WHERE `{$db_field}` = '{$value}'",
        false
    );
    $userData = sqlQuery("
        SELECT
           u.`id`,
           u.`real_last_name`, 
           u.`real_first_name`, 
           u.`real_middle_name` 
        FROM `users` u 
        LEFT JOIN `telegrams` tg ON tg.`user_id` = u.`id`
        LEFT JOIN `emails` em ON em.`user_id` = u.`id`
        WHERE u.`id` = {$uid["user_id"]}
        GROUP BY u.`id`
    ", false);

    $secondUid =  sqlQuery(
        "SELECT * FROM `{$secondTable}` ORDER BY `id` DESC LIMIT 1",
        false
    );
    $userData[$field] = $uid[$db_field] ?? null;
    $userData[$secondField] = $uid[$db_secondField] ?? null;
    $userData[$firstFieldSecondTable] = $secondUid[$db_field] ?? null;
    $userData[$secondFieldSecondTable] = $secondUid[$db_secondField] ?? null;

    return $userData;
}

function printResult(mixed $data, string $status = OK_API_STATUS): void {
    exit(json_encode(['status' => $status, 'data' => $data], JSON_UNESCAPED_UNICODE));
}

function printError(mixed $data): void {
    printResult($data, ERROR_API_STATUS);
}

/**
 * @throws Exception
 */
function updateCertificate($connect): string
{
    $user = sqlQuery("SELECT * FROM `users` WHERE `id` = '{$_POST['users__id']}'", false);
    if (!$user) {
        throw new Exception("The user(id:{$_POST['users__id']}) is not found!");
    }
    mysqli_begin_transaction($connect);
    sqlQuery("UPDATE `users` SET 
                   `real_last_name` = '{$_POST['users__real_last_name']}', 
                   `real_first_name` = '{$_POST['users__real_first_name']}', 
                   `real_middle_name` = '{$_POST['users__real_middle_name']}' 
               WHERE `id` = '{$user['id']}';"
    );

    if (isset($_POST['id'])) {
        $certificate = sqlQuery("SELECT * FROM `certificates` WHERE `id` = '{$_POST['id']}'", false);
        if (!@$certificate['id']) {
            throw new Exception("Error while the certificate(id:{$certificate['id']}) updating!");
        }
        sqlQuery("
            UPDATE `certificates` SET 
                `hours` = '{$_POST['certificates__hours']}',
                `description` = '{$_POST['certificates__description']}',
                `blank` = '{$_POST['certificates__blank']}'
                WHERE `id` = {$certificate['id']};
", false);
    } else {
        $certificate = sqlQuery("
            INSERT INTO `certificates` SET 
                `user_id` = '{$user['id']}',
                `course_id` = '{$_POST['certificates__course']}',
                `hours` = '{$_POST['certificates__hours']}',
                `description` = '{$_POST['certificates__description']}',
                `language` = '{$_POST['certificates__language']}',
                `blank` = '{$_POST['certificates__blank']}';
", false);
        $certificate = sqlQuery("SELECT * FROM `certificates` WHERE `id` = '{$certificate}'", false);

        if (!@$certificate['id']) {
            throw new Exception("Error while creating a new certificate for the user(id:{$_POST['users__id']})!");
        }
        $certificateNumber = "{$user['id']}00-{$certificate['course_id']}-{$certificate['id']}" . strtoupper($_POST['certificates__language']) . date('Y');
        sqlQuery("UPDATE `certificates` SET `full_number` = '{$certificateNumber}' WHERE `id` = '{$certificate['id']}';");
    }
    foreach ($_POST as $keyPost => $onePost) {
        if(str_starts_with($keyPost, 'technologies')) {
            $technologyData = explode('__', $keyPost);
            $checkTechnology = sqlQuery("SELECT * FROM `course_technology` WHERE `technology_id` = '{$technologyData[1]}' AND `course_id` = '{$certificate['course_id']}'", false);
            if (!$checkTechnology) {
                throw new Exception("Error while attaching a technology to a certificate  {$technologyData[1]}/{$certificate['id']}");
            }
            $technology = sqlQuery("SELECT * FROM `certificate_technology` WHERE `technology_id` = '{$technologyData[1]}' AND `certificate_id` = '{$certificate['id']}'", false);
            if ($technology && !$onePost) {
                sqlQuery("DELETE FROM `certificate_technology` WHERE `technology_id` = '{$technology['id']}';");
            } elseif (!$technology && $onePost) {
                $newTechnology = sqlQuery("INSERT INTO `certificate_technology` SET `technology_id` = '{$technologyData[1]}', `certificate_id` = '{$certificate['id']}';");
                if (!$newTechnology) {
                    throw new Exception("Error while creating the technology(id:{$technologyData[1]}) and the certificate(id:{$certificate['id']})");
                }
            }
        }
    }
    mysqli_commit($connect);
    return "The certificate(id:{$certificate['id']}) is successfully updated/created!";
}

function delCertificate($connect, ?int $id = null): string {
    $certificate = sqlQuery("SELECT * FROM `certificates` WHERE `id` = {$id}", false);
    mysqli_begin_transaction($connect);
    if (
        $id
        && !empty($certificate)
        && sqlQuery("DELETE FROM `certificate_technology` WHERE `certificate_id` = {$id};")
        && sqlQuery("DELETE FROM `certificates` WHERE `id` = $id;")
    ) {
        mysqli_commit($connect);
        return "The certificate(id:{$certificate['id']}) is successfully deleted from the database!";
    }
    return "Error while deleting the certificate(id:{$certificate['id']})!";
}

function getCourses():array {
    $coursesArr = sqlQuery("
        SELECT c.*,
               GROUP_CONCAT(t.`name`) AS 'technologies',
               GROUP_CONCAT(t.`id`) AS 'technologies_ids',
               GROUP_CONCAT(t.`description` SEPARATOR '<~>') AS 'technologies_descriptions',
               GROUP_CONCAT(t.`type`) AS 'technologies_types',
               GROUP_CONCAT(tc.`hours`) AS 'technologies_hours',
                a.`login` AS 'admin_login'
        FROM `courses` c
        LEFT JOIN `course_technology` tc ON c.`id` = tc.`course_id`
        LEFT JOIN `technologies` t ON t.`id` = tc.`technology_id`
        LEFT JOIN `admins` a ON a.`id` = c.`admin_id`
        GROUP BY c.`id`
        ORDER BY c.`active` DESC, c.`order` ASC;
        ");

    $courses = [];
    foreach ($coursesArr as $course) {
        $courses[$course['id']] = $course;
        $courses[$course['id']]["sub_courses"] = $course['sub_courses_ids'] ?
            sqlQuery("SELECT * FROM `courses` WHERE `id` IN ({$course['sub_courses_ids']});") : null;
        $courses[$course['id']]['technologies_arr'] = [];

        if (!$course['technologies_ids']) {
            continue;
        }
        $technologiesIds = explode(',', $course['technologies_ids']);
        $technologiesNames = explode(',', $course['technologies']);
        $technologiesDescriptions = explode('<~>', $course['technologies_descriptions']);
        $technologiesTypes = explode(',', $course['technologies_types']);
        $technologiesHours = explode(',', $course['technologies_hours']);
        foreach ($technologiesIds as $technologyKey => $technologyId) {
            $courses[$course['id']]['technologies_arr'][$technologyId] = [
                'id' => $technologyId,
                'name' => $technologiesNames[$technologyKey],
                'descriptions' => $technologiesDescriptions[$technologyKey] ?? null,
                'type' => $technologiesTypes[$technologyKey],
                'hours' => $technologiesHours[$technologyKey],
            ];
        }
    }
    return $courses;
}

function getCertificates(?int $id = null): array {
    $certificates = sqlQuery("
        SELECT tg.`service_uid` AS 'tg_id', 
               tg.`service_login` AS 'tg_name',
               em.`service_uid` AS 'email_id', 
               em.`service_login` AS 'email_name',
               u.`id` AS 'user_id',
               u.`real_last_name`, 
               u.`real_first_name`, 
               u.`real_middle_name`, 
               c.`id`, 
               c.`date`, 
               c.`hours`, 
               c.`language`, 
               c.`blank`,
               c.`description`, 
               c.`full_number`,
               cr.`name` AS 'course',
               cr.`level`,
               cr.`id` AS 'course_id',
               GROUP_CONCAT(t.`name`) AS 'technologies',
               GROUP_CONCAT(t.`id`) AS 'technologies_ids',
               GROUP_CONCAT(t.`description`) AS 'technologies_descriptions'
        FROM `certificates` c 
        left JOIN `courses` cr ON c.`course_id` = cr.`id`
        INNER JOIN `certificate_technology` tc ON c.`id` = tc.`certificate_id`
        INNER JOIN `technologies` t ON tc.`technology_id` = t.`id`
        INNER JOIN `users` u ON c.`user_id` = u.`id`
        LEFT JOIN `telegrams` tg ON tg.`user_id` = u.`id`
        LEFT JOIN `emails` em ON em.`user_id` = u.`id`"
        . ($id ? "WHERE c.`id` = {$id}" : "" ) . "
        GROUP BY c.`id`
        ORDER BY c.`id` DESC;
    ");

    foreach ($certificates as &$certificate) {
        $certificate['technologies'] = explode(',', $certificate['technologies']);
        $certificate['technologies_ids'] = explode(',', $certificate['technologies_ids']);
        $certificate['technologies_descriptions'] = explode(',', $certificate['technologies_descriptions']);
    }

    return $id ? $certificates[0] : $certificates;
}

function getAdviseList(string $table, string $field, string $value): array {
    $value = implode("|", array_filter(
        array_map('trim', explode(' ', strtolower(trim($value)))),
        fn($item) => strlen($item) > 1)
    );
    $adviseList = sqlQuery("
        SELECT * FROM `{$table}` WHERE `{$field}` REGEXP '{$value}'
    ");
    return $adviseList;
}

function getCourseTechnologies(int $courseId): array {
    return sqlQuery("
        SELECT t.*, ct.`hours` FROM `course_technology` ct
        LEFT JOIN `technologies` t ON t.`id` = ct.`technology_id`
        WHERE ct.`course_id` = '{$courseId}'
    ");
}

function updateCourse($connect, array $data) {
    $subCoursesIds = [];
    $technologiesIds = [];
    $newTechnologiesIds = [];
    mysqli_begin_transaction($connect);

    foreach ($data as $dataKey => $dataValue) {
        if ($dataKey == "id") {
            continue;
        }
        $itemProps = explode('__', $dataKey);
        if (str_ends_with($itemProps[1], '_description')
            || str_ends_with($itemProps[1], '_name')
            || str_ends_with($itemProps[1], '_type')
        ) {
            continue;
        }
        if ($itemProps[0] == 'courses' && is_numeric($itemProps[1])) {
            $subCourse = sqlQuery("SELECT * FROM `courses` WHERE `id` = '{$itemProps[1]}'", false);
            if (!$subCourse) {
                throw new Exception("Error while the sub course(id:{$subCourse}) adding, sub course is not found!");
            }
            $subCoursesIds[] = $itemProps[1];
        } elseif ($itemProps[0] == 'technologies') {
            if (str_starts_with($itemProps[1], 'new_')) {
                $technologyId = sqlQuery(sprintf("
                    INSERT INTO `technologies` SET `name` = '%s',`type` = '%s', `description` = '%s';",
                    mysqli_real_escape_string($connect, $data["{$dataKey}_name"]),
                    mysqli_real_escape_string($connect, $data["{$dataKey}_type"]),
                    mysqli_real_escape_string($connect, $data["{$dataKey}_description"])
                ));
                if (!$technologyId) {
                    throw new Exception("Error while saving the technology(name:{$dataValue})!");
                }
                $newTechnologiesIds[$technologyId] = $data["{$dataKey}"];
            } else {
                $technology = sqlQuery("SELECT * FROM `technologies` WHERE `id` = '{$itemProps[1]}'", false);
                if (!$technology) {
                    throw new Exception("Error while the technology(id:{$itemProps[1]}) adding, technology is not found!");
                }
                $technologiesIds[] = $technology['id'];
            }
        }
    }
    $subCoursesIds = implode(',', $subCoursesIds);


    if (isset($data['id'])) {
        $course = sqlQuery("SELECT * FROM `courses` WHERE `id` = '{$data['id']}'", false);
        $course_id = $course['id'];
        sqlQuery(sprintf("
        UPDATE `courses` SET `level` = '%s', `type` = '%s', `description_en` = '%s', `description_ru` = '%s', `sub_courses_ids` = '%s', `active` = '%s', `order` = '%s' WHERE `id` = '%s';",
            mysqli_real_escape_string($connect, $data['courses__level']),
            mysqli_real_escape_string($connect, $data['courses__type']),
            mysqli_real_escape_string($connect, $data['courses__description_en']),
            mysqli_real_escape_string($connect, $data['courses__description_ru']),
            mysqli_real_escape_string($connect, $subCoursesIds),
            mysqli_real_escape_string($connect, $data['courses__active']),
            mysqli_real_escape_string($connect, $data['courses__order']),
            mysqli_real_escape_string($connect, $course['id'])
        ), false);
    } else {
        $course_id = sqlQuery(sprintf("
        INSERT INTO `courses` SET `name` = '%s', `level` = '%s', `type` = '%s', `description_en` = '%s', `description_ru` = '%s', `sub_courses_ids` = '%s', `admin_id` = '%s';",
            mysqli_real_escape_string($connect, $data['courses__name']),
            mysqli_real_escape_string($connect, $data['courses__level']),
            mysqli_real_escape_string($connect, $data['courses__type']),
            mysqli_real_escape_string($connect, $data['courses__description_en']),
            mysqli_real_escape_string($connect, $data['courses__description_ru']),
            mysqli_real_escape_string($connect, $subCoursesIds),
            mysqli_real_escape_string($connect, $_SESSION['authorization']['id'])
        ), false);
    }

    $courseTechnologies = sqlQuery("SELECT * FROM `course_technology` WHERE `course_id` = '{$course_id}'");
    foreach ($courseTechnologies as $courseTechnology) {
        if (!in_array($courseTechnology['technology_id'], $technologiesIds)) {
            sqlQuery("DELETE FROM `course_technology` WHERE `id` = '{$courseTechnology['id']}';");
            continue;
        }
        sqlQuery("UPDATE `course_technology` SET `hours` = '{$data["technologies__{$courseTechnology['technology_id']}"]}' WHERE `id` = '{$courseTechnology['id']}';");
    }
    foreach ($technologiesIds as $technologyId) {
        if (!in_array($technologyId, array_column($courseTechnologies, 'technology_id'))) {
            sqlQuery("INSERT INTO `course_technology` SET `course_id` = '{$course_id}', `technology_id` = '{$technologyId}', `hours` = '{$data["technologies__{$technologyId}"]}';");
        }
    }
    foreach ($newTechnologiesIds as $newTechnologyId => $technologyHours) {
        sqlQuery("INSERT INTO `course_technology` SET `course_id` = '{$course_id}', `technology_id` = '{$newTechnologyId}', `hours` = {$technologyHours};");
    }
    mysqli_commit($connect);
    return "The course(id:{$course_id}) is successfully updated/created!";
}

function delCourse($connect, int $id): string {
    $course = sqlQuery("SELECT * FROM `courses` WHERE `id` = {$id}", false);
    if (!$course) {
        throw new Exception("The course(id:{$id}) is not found!");
    }

    $courses = sqlQuery("SELECT * FROM `courses`");
    $deleteExceptions = [];
    foreach ($courses as $courseItem) {
        if (in_array($id, explode(',', $courseItem['sub_courses_ids']))) {
            $deleteExceptions[] = "as a sub course for the course(id:{$courseItem['id']})\n\n";
        }
    }
    $certificates = sqlQuery("SELECT * FROM `certificates` WHERE `course_id` = {$id}");
    if (!empty($certificates)) {
        $certificates = implode(",\n", array_column($certificates, 'name'));
        $deleteExceptions[] = "as a course for the certificates:\n{$certificates}\n\n";
    }
    $ruqeusts = sqlQuery("SELECT * FROM `requests` WHERE `course_id` = {$id}");
    if (!empty($ruqeusts)) {
        $ruqeusts = implode(",\n", array_column($ruqeusts, 'name'));
        $deleteExceptions[] = "as a course for the requests:\n{$ruqeusts}\n\n";
    }

    if (!empty($deleteExceptions)) {
        throw new Exception("The course(id:{$course['id']}) can not be deleted because it is used:\n" . implode("\n", $deleteExceptions));
    }
    mysqli_begin_transaction($connect);

    if (
        sqlQuery("DELETE FROM `course_technology` WHERE `course_id` = {$id};")
        && sqlQuery("DELETE FROM `courses` WHERE `id` = $id;")
    ) {
        mysqli_commit($connect);
        return "The course(id:{$course['id']}) is successfully deleted from the database!";
    }
    return "Error while deleting the course(id:{$course['id']})!";
}

function getUnusedTechnologies(int $id = null): array {
    $byId = $id ? "AND t.`id` = {$id}" : "";
    $unusedTechnologies = sqlQuery("
        SELECT t.* FROM `technologies` t
        LEFT JOIN `course_technology` ct ON ct.`technology_id` = t.`id`
        LEFT JOIN `certificate_technology` cet ON cet.`technology_id` = t.`id`
        WHERE ct.`technology_id` IS NULL AND cet.`technology_id` IS NULL {$byId};
    ");
    return $unusedTechnologies;
}

function removeTechnology($connect, int $id): string {
    if (empty(getUnusedTechnologies($id))) {
        throw new Exception("The technology(id:{$id}) can not be deleted because it is used or doesn't exist!");
    }
    mysqli_begin_transaction($connect);
    if (sqlQuery("DELETE FROM `technologies` WHERE `id` = {$id};")) {
        mysqli_commit($connect);
        return "The technology(id:{$id}) is successfully deleted from the database!";
    }
    return "Error while deleting the technology(id:{$id})!";
}

function downloadCertificate(int $id): string {
    $certificateData = getCertificates($id);
    $blank = new ImageBlank(__DIR__ . "/images/pg_cert_blank_{$certificateData['blank']}.jpg");
    $blank
        ->addString(
            $certificateData['blank'] == 'ru'
                ? "`{$certificateData['course']}` с уровнем `{$certificateData['level']}`"
                : "`{$certificateData['course']}` of the `{$certificateData['level']}` level",
            44,
            round($blank->getXSize() * 0.51),
            round($blank->getYSize() * 0.43),
            Fonts::ARIAL_BLACK
        )
        ->addString(
            $certificateData['full_number'],
            40,
            round($blank->getXSize() * 0.5),
            round($blank->getYSize() * 0.51),
            Fonts::ARIAL_BLACK,
            0,
            Colors::BLACK
        )
        ->addString(
            $certificateData['blank'] == 'ru' ?
                ($certificateData['real_last_name'] . " " . $certificateData['real_first_name'] . " " . $certificateData['real_middle_name']) :
                ($certificateData['real_first_name'] . " " . $certificateData['real_last_name'])
            ,
            50,
            round($blank->getXSize() * 0.62),
            round($blank->getYSize() * 0.58),
            Fonts::ARIAL_REGULAR,
        )
        ->addString(
            $certificateData['hours'],
            35,
            round($blank->getXSize() * 0.631),
            round($blank->getYSize() * 0.64),
            Fonts::ARIAL_BLACK,
        )
        ->addString(
            date($certificateData['blank'] == 'ru' ? 'd.m.Yг.' : 'M j, Y', strtotime($certificateData['date'])),
            32,
            round($blank->getXSize() * 0.123),
            round($blank->getYSize() * 0.725),
            Fonts::CALIBRI,
        )
        ->addStringBlock(
            $certificateData['description'],
            27,
            round($blank->getXSize() * 0.05),
            round($blank->getYSize() * 0.9),
            Fonts::CALIBRI,
            round($blank->getXSize() * 0.5),
            false,
            0,
            0,
            Colors::WHITE,
            1
        )->addColumnsStringBlock(
            $certificateData['technologies'],
            2,
            round($blank->getXSize() * 0.8),
            round($blank->getYSize() * 0.91),
            Fonts::ARIAL_REGULAR,
            round($blank->getXSize() * 0.16),
            24,
            false,
            Colors::WHITE,
            0,
            0.7,
            5
        );
    return $blank->getBase64(65);
}

function getCoupons(int $id = null): array {
    $coupons = sqlQuery("
        SELECT c.*, ct.`name` AS 'type', cu.`name` AS 'unit', cp.`name` AS 'placement' FROM `coupons` c
        LEFT JOIN `coupon_types` ct ON c.`coupon_type_id` = ct.`id`
        LEFT JOIN `coupon_units` cu ON c.`coupon_unit_id` = cu.`id`
        LEFT JOIN `coupon_placements` cp ON c.`placement_id` = cp.`id` "
        . ($id ? "WHERE c.`id` = {$id}" : "" ) . "
        ORDER BY c.`id` DESC;
    ");
    return $id ? $coupons[0] : $coupons;
}

function getCouponUnits(?int $id = null): array {
    $units = sqlQuery("SELECT * FROM `coupon_units` " . ($id ? "WHERE `id` = {$id}" : "" ) . ";");
    return $id ? $units[0] : $units;
}

function getCouponTypes(?int $id = null): array {
    $types = sqlQuery("SELECT * FROM `coupon_types` " . ($id ? "WHERE `id` = {$id}" : "" ) . ";");
    return $id ? $types[0] : $types;
}

function getCouponPlacements(): array {
    return sqlQuery("SELECT * FROM `coupon_placements`");
}

/**
 * @throws Exception
 */
function updateCoupon($connect, array $data): string {
    mysqli_begin_transaction($connect);

    if (@$data['coupons__id']) {
        sqlQuery("SELECT * FROM `coupons` WHERE `id` = '{$data['id']}'", false);
        sqlQuery(sprintf("UPDATE `coupons` 
                SET `name` = '%s', `language` = '%s', `description` = '%s', `value` = '%s', `is_active` = '%s', `max_times` = '%s', `expired_at` = '%s', `area` = '%s', `area_type` = '%s', `placement_id` = '%s'
                WHERE `id` = '%s'",
            mysqli_real_escape_string($connect, $data['coupons__name']),
            mysqli_real_escape_string($connect, $data['coupons__language']),
            mysqli_real_escape_string($connect, $data['coupons__description']),
            mysqli_real_escape_string($connect, $data['coupons__value']),
            mysqli_real_escape_string($connect, $data['coupons__is_active']),
            mysqli_real_escape_string($connect, $data['coupons__max_times']),
            mysqli_real_escape_string($connect, $data['coupons__expired_at']),
            mysqli_real_escape_string($connect, $data['coupons__area']),
            mysqli_real_escape_string($connect, $data['coupons__area_type']),
            mysqli_real_escape_string($connect, $data['coupons__placement_id']),
            mysqli_real_escape_string($connect, $data['coupons__id'])
        ));
        $couponId = $data['coupons__id'];
    } else {
        $couponId = sqlQuery(sprintf("INSERT INTO `coupons` SET 
                `name` = '%s', `method` = '%s', `language` = '%s', `coupon_type_id` = '%s', `coupon_unit_id` = '%s', `value` = '%s', `max_times` = '%s', `expired_at` = '%s', `description` = '%s', `is_active` = '%s', `area` = '%s', `area_type` = '%s', `placement_id` = '%s'",
            mysqli_real_escape_string($connect, $data['coupons__name']),
            mysqli_real_escape_string($connect, $data['coupons__method']),
            mysqli_real_escape_string($connect, $data['coupons__language']),
            mysqli_real_escape_string($connect, $data['coupons__coupon_type_id']),
            mysqli_real_escape_string($connect, $data['coupons__coupon_unit_id']),
            mysqli_real_escape_string($connect, $data['coupons__value']),
            mysqli_real_escape_string($connect, $data['coupons__max_times']),
            mysqli_real_escape_string($connect, $data['coupons__expired_at']),
            mysqli_real_escape_string($connect, $data['coupons__description']),
            mysqli_real_escape_string($connect, $data['coupons__is_active']),
            mysqli_real_escape_string($connect, $data['coupons__area']),
            mysqli_real_escape_string($connect, $data['coupons__area_type']),
            mysqli_real_escape_string($connect, $data['coupons__placement_id'])
        ));
        if ($data['coupons__method'] == COUPON_GENERATED_METHOD) {
            $serialNumber = generateCouponSerialNumber($couponId, $data['coupon_types__prefix']);
            sqlQuery("UPDATE `coupons` SET `serial_number` = '{$serialNumber}' WHERE `id` = '{$couponId}';");
        } else {
            if (isCouponSerialNumberExists($data['coupons__serial_number'])) {
                throw new Exception(
                    "Error while creating the coupon, the serial number '{$data['coupons__serial_number']}' is already exists!"
                );
            }
            $data['coupons__serial_number'] = strtoupper($data['coupons__serial_number']);
            sqlQuery("UPDATE `coupons` SET `serial_number` = '{$data['coupons__serial_number']}' WHERE `id` = '{$couponId}';");
        }
    }
    mysqli_commit($connect);
    return "The coupon(id:{$couponId}) is successfully updated/created!";
}

function generateCouponSerialNumber(int $couponId, ?string $prefix = null): string {
    $charFirst = chr(rand(ord('a'), ord('z')));
    $charLast = chr(rand(ord('a'), ord('z')));
    $serialNumber = ($prefix ? ($prefix . "-") : strtoupper($charFirst)) . $couponId .
        strtoupper($charLast . substr(md5($couponId), 0, $prefix ? rand(4, 5) : rand(6, 8)));
    if (isCouponSerialNumberExists($serialNumber)) {
        throw new Exception(
            "Error while generating the serial number, the serial number '{$serialNumber}' is already exists!"
        );
    }
    return strtoupper($serialNumber);
}

function isCouponSerialNumberExists(string $serialNumber): bool {
    return !empty(sqlQuery(
            "SELECT * FROM `coupons` WHERE `serial_number` = '{$serialNumber}'",
            false
        ));
}

function deleteCoupon($connect, int $id): string {
    $coupon = sqlQuery("SELECT * FROM `coupons` WHERE `id` = {$id}", false);
    if (!$coupon) {
        throw new Exception("The coupon(id:{$id}) is not found!");
    }
    mysqli_begin_transaction($connect);
    if (sqlQuery("DELETE FROM `coupons` WHERE `id` = {$id};")) {
        mysqli_commit($connect);
        return "The coupon(id:{$coupon['id']}) is successfully deleted from the database!";
    }
    return "Error while deleting the coupon(id:{$coupon['id']})!";
}

function createGDQRimage(string $data, $size = 12) {
    $qr = new QRCode();
    $qr->setErrorCorrectLevel(QR_ERROR_CORRECT_LEVEL_L);

    $qr->setTypeNumber(4);

    $qr->addData($data);
    $qr->make();
    return $qr->createImage($size, fg: 0xFFFFFE,  bgtrans: true);
}

function downloadCoupon(int $couponId):string {
    $templateFile = __DIR__ . "/images/coupon_template.jpg";
    $couponData = getCoupons($couponId);
    $couponType = getCouponTypes($couponData['coupon_type_id']);
    $couponUnit = getCouponUnits($couponData['coupon_unit_id']);
    $valueString = "[ " . ($couponUnit['symbol_placement'] == 'before' ? "{$couponUnit['symbol']}{$couponData['value']}" : "{$couponData['value']}{$couponUnit['symbol']}") . " ]";
    $blank = new ImageBlank($templateFile);
    $blank
        ->addGDImage(
            createGDQRimage($couponType['use_link'] . "?coupon={$couponData['serial_number']}"),
            400,
            400,
            228,
            510
        )
        ->addString(
            "{$valueString} {$couponData['name']}",
            56,
            round($blank->getXSize() * 0.50),
            round($blank->getYSize() * 0.308),
            Fonts::AVENIR_NEXT_CYR
        )
        ->addString(
            $couponData['serial_number'],
            80,
            round($blank->getXSize() * 0.64),
            round($blank->getYSize() * 0.62),
            Fonts::ARIAL_BLACK,
            color: Colors::BLACK
        )
        ->addString(
            $couponData['description'],
            22,
            round($blank->getXSize() * 0.64),
            round($blank->getYSize() * 0.44),
            Fonts::CALIBRI,
        )
        ->addString(
            $couponType['use_link'],
            38,
            round($blank->getXSize() * 0.66),
            round($blank->getYSize() * 0.77),
            Fonts::CALIBRI,
        )
        ->addString(
            date($couponData['language'] == "en" ? "m/d/Y" : "d.m.Y", strtotime($couponData['expired_at'])),
            24,
            round($blank->getXSize() * 0.525),
            round($blank->getYSize() * 0.873),
            Fonts::ARIAL_REGULAR,
            color: Colors::BLACK
        )
        ->addString(
            $couponData['max_times'],
            24,
            round($blank->getXSize() * 0.868),
            round($blank->getYSize() * 0.873),
            Fonts::ARIAL_REGULAR,
            color: Colors::BLACK
        );
    return $blank->getBase64(65);
}

function getRequestsByFieldName(?string $field = null, mixed $value = null): array {
    $requests = sqlQuery("
        SELECT r.*, p.`name` AS 'product', u.`real_last_name`, u.`real_first_name`, u.`real_middle_name`, u.`id` AS 'user_id' 
        FROM `requests` r
        LEFT JOIN `products` p ON r.`product_id` = p.`id`
        LEFT JOIN `users` u ON r.`user_id` = u.`id`"
        . ($field ? "WHERE r.`{$field}` = {$value}" : "" ) . "
        ORDER BY r.`id` DESC;
    ");
    return count($requests) > 1 ? $requests : $requests[0];
}
