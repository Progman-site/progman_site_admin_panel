<?php require_once "admin_room_controller.php"; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles/admin.css">
    <title>Admin room</title>
</head>
<body>
<?php if (!isset($_SESSION['authorization'])) { ?>
    <form class="authorization" method="post">
        <h3>Entre to the admin panel</h3>
        <div>
            <input type="text" name="login" placeholder="login">
        </div>
        <div>
            <input type="password" name="password" placeholder="password">
        </div>
        <button type="submit">LOGIN</button>
    </form>
<?php exit();?>
<?php }?>
    <form class="reset" method="post">
        <input type="hidden" name="reset" value="1">
        How are you doing, <b><?= $_SESSION['authorization']['login']?></b>!
        <button type="submit">logout</button>
    </form>
    <form class="navigation">
        <input type="submit" class="<?= @$_GET['navigation'] == "certificates" ? "pressed" : "" ?>" name="navigation" value="certificates">
        <input type="submit" class="<?= @$_GET['navigation'] == "setting" ? "pressed" : "" ?>" name="navigation" value="setting">
        <input type="submit" class="<?= @$_GET['navigation'] == "courses" ? "pressed" : "" ?>" name="navigation" value="courses">
    </form>
<?php if (@$_GET['navigation'] == "setting") { ?>
    <form class="edit_panel" method="post" enctype="multipart/form-data">
        <h3>Tag content settings</h3>
        <input type="text" id="tag_search" value="" placeholder="Search by tag name or description">
        <input type="checkbox" id="tag_search_with_values" title="Searching by current values">
        <label for="tag_search_with_values">values</label>
        <input type="hidden" name="form_name" value="edit">
        <?php foreach ($siteInfo as $tag => $langItems) { ?>
        <details id="<?= $tag ?>" data-description="<?= $langItems[DEFAULT_LANGUAGE]['description'] ?>">
            <summary><b><?= $tag ?></b></summary>
            <?php foreach (AVAILABLE_LANGUAGES as $lang) {
                $tagValue = $langItems[$lang]["value"] ?? "";
                $tagType = $langItems[DEFAULT_LANGUAGE]['type'];?>
                <div>
                    <label>
                        <?= $langItems[DEFAULT_LANGUAGE]['description'] ?> (<?= $lang?>)
                    </label>
                    <br>
                    <?php if ($tagType == 'string') {?>
                        <input type="text" class="touch_sensitive_input" placeholder="EMPTY FIELD" data-touched="" name="<?= $tag . "-" . $lang ?>" value="<?= $tagValue ?>">
                    <?php } elseif  ($tagType == 'text') { ?>
                        <textarea  class="touch_sensitive_input" placeholder="EMPTY FIELD" data-touched="" name="<?= $tag . "-" . $lang ?>"><?= $tagValue ?></textarea>
                    <?php } elseif  ($tagType == 'image') { ?>
                        <div class="image_editor">
                            <div>
                                <?php if (isset($langItems[$lang])) { ?>
                                    <img src="<?= $tagValue ?>">
                                <?php } else { ?>
                                    <strong>EMPTY FIELD</strong>
                                <?php } ?>
                            </div>
                            <input type="file" class="touch_sensitive_input" data-touched="" name="<?= $tag  . "-" . $lang ?>">
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </details>
        <?php } ?>
        <div class="submit_place">
            <button type="submit">SAVE CHANGES</button>
            <button type="button" class="reset">RESET</button>
        </div>
    </form>
    <pre>
<?php } ?>
<?php if (@$_GET['navigation'] == "certificates") { ?>
    <div class="edit_panel certificates price_list">
        <h3>Certificates management</h3>
        <div id="new_price_item">
            <b>Generate a NEW one</b>
            <div>
                <input type="hidden" name="users__id" data-field="user_id" value="">
                <input type="text" class="search_field" name="users__email_name" data-field="email_name" placeholder="Email-name" required><br>
                <input type="text" class="search_field" name="users__email_id" data-field="email_id" placeholder="Email" required><br>
                <input type="text" class="search_field" name="users__tg_name" data-field="tg_name" placeholder="telegram nikname" required><br>
                <input type="text" class="search_field" name="users__tg_id" data-field="tg_id" placeholder="telegram id" required><br>
                <input type="text" name="users__real_first_name" placeholder="a legal first name" required><br>
                <input type="text" name="users__real_last_name" placeholder="a legal last name" required><br>
                <input type="text" name="users__real_middle_name" placeholder="a legal middle name">
                <br><br>
                <label>Hours: <input type="number" name="certificates__hours" placeholder="count of hours" ></label>
                <br><br>
                <div>
                    <label>Teach lang:
                        <select name="certificates__language">
                            <option value="en">EN</option>
                            <option value="ru">RU</option>
                        </select>
                    </label>
                    &nbsp;&nbsp;
                    <label>Blank type:
                        <select name="certificates__blank">
                            <option value="en">en</option>
                            <option value="ru">ru</option>
                        </select>
                    </label>
                </div>
                <br><br>
                <textarea name="certificates__description" cols="30" rows="10" placeholder="Special notes"></textarea>
            </div>
            <div style="margin-top: 20px">
                <select name="certificates__course">
                    <option>Course</option>
                    <?php foreach ($courses as $course) { ?>
                        <option
                                data-type="<?= $course["type"]?>"
                                data-level="<?= $course["level"]?>"
                                data-technologies="<?= $course["technologies"] ?>"
                                data-technologies_ids="<?= $course["technologies_ids"] ?>"
                                data-technologies_descriptions="<?= $course["technologies_descriptions"] ?>"
                                value="<?= $course["id"] ?>">
                            <?= $course["name"] ?>
                        </option>
                    <?php } ?>
                </select>
                <br>
                <strong>type: <span class="course_type"></span></strong>
                &nbsp;&nbsp;
                <strong>Level: <span class="course_level"></span></strong>
                <h4>Technologies:</h4>
                <div class="checkbox_list"></div>
            </div>
            <div>
                <button class="changer" data-task="save" data-api_method="updateCertificates" data-id="">GENERATE</button>
            </div>
        </div>

        <?php $count = 0; foreach ($certificates as $item) { ?>
            <details>
                <summary><b><?= $item["real_first_name"] ?> <?= $item["real_last_name"] ?></b> certificate</summary>
                <div data-form_name="user_data">
                    <div>
                        <strong># <?= $item["full_number"] ?></strong><br><br>
                        <input type="hidden" name="users__id" data-field="user_id" value="<?= $item["user_id"] ?>">
                        <span>Start: <?= date('d-m-Y', strtotime($item["date"])) ?></span>
                        <br><br>
                        <label>Tg nick: <b><?= $item["tg_name"]  ?? "-" ?></b></label>
                        <br>
                        <label>Tg DI: <b><?= $item["tg_id"]   ?? "-" ?></b></label>
                        <br><br>
                        <label>Email-name: <b><?= $item["email_name"]  ?? "-" ?></b></label>
                        <br>
                        <label>Email: <b><?= $item["email_id"]  ?? "-" ?></b></label>
                        <br><br>
                        <input type="text" name="users__real_first_name" placeholder="a legal first name" value="<?= $item["real_first_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_last_name" placeholder="a legal last name" value="<?= $item["real_last_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_middle_name" placeholder="a legal middle name" value="<?= $item["real_middle_name"] ?>" disabled>
                    </div>
                    <div>
                        <textarea name="certificates__description" cols="30" rows="10" placeholder="Special notes" disabled><?= $item['description'] ?></textarea>
                        <br><br>
                        <div>
                            Course: <strong><?= $item['course']?></strong>
                        </div>
                        <br>
                        <strong>type: <span class="course_type"><?= $item['id']?></span></strong>
                        &nbsp;&nbsp;
                        <strong>Level: <span class="course_level"><?= $item['level']?></span></strong>
                        <h4>Technologies:</h4>
                    </div>
                    <div>
                        <button class="changer" title="unblock changing of the certificate" data-task="change" data-api_method="updateCertificates" data-id="<?= $item['id']?>">change</button>
                        <br/><br/>
                        <button class="deleter" title="delete the certificate" data-id=<?= $item['id']?> data-api_method="delCertificate">del</button>
                        <br/><br/><br/><br/><br/>
                        <button title="Downloading the graphic picture of the certificate" onclick="downloadCertificate(<?= $item['id']?>, this.parentNode.parentNode)">DOWNLOAD</button>
                    </div>
                    <div>
                        <br>
                        <div>
                            <label>
                                Hours:
                                <input type="number" name="certificates__hours" placeholder="count of hours" value="<?= $item['hours']?>" disabled>
                            </label>
                        </div>
                        <br>
                        <strong>LANG: <span class="course_type"><?= $item['language']?></span></strong>
                        &nbsp;&nbsp;
                        <label>
                            BLANK:
                            <select name="certificates__blank" disabled>
                                <option value="en" <?= $item['blank'] == 'en' ? 'selected': '' ?>>en</option>
                                <option value="ru" <?= $item['blank'] == 'ru' ? 'selected': '' ?>>ru</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <div class="checkbox_list">
                            <?php foreach ($courses[$item['course_id']]['technologies_arr'] as $technologyData) { ?>
                                <label title="<?= $technologyData['descriptions'] ?>">
                                    <input
                                        type="checkbox"
                                        name="technologies__<?= $technologyData['id'] ?>"
                                        value=<?= @in_array($technologyData['id'], $item['technologies_ids']) ? 1 : 0 ?>
                                        onchange="this.value = Number(this.checked)"
                                        <?= @in_array($technologyData['id'], $item['technologies_ids']) ? 'checked' : '' ?>
                                        disabled
                                    >
                                    <?= $technologyData['name'] ?>
                                </label>
                            <?php } ?>
                        </div>
                    </div>


                </div>
            </details>
        <?php } ?>
    </div>
<?php } ?>
<?php if (@$_GET['navigation'] == "courses") { ?>
    <div class="edit_panel courses price_list">
        <h3>Courses management</h3>
        <div id="new_price_item">
            <strong>Generate a NEW one</strong>
            <div>
                <input type="text" class="input_adviser" data-table="courses" data-field="name" name="courses__name" value="" placeholder="Name of the course">
                <br>
                <textarea name="courses__description_en" cols="30" rows="10" placeholder="Description of the course (en)"></textarea>
                <br>
                <textarea name="courses__description_ru" cols="30" rows="10" placeholder="Description of the course (ru)"></textarea>
                <br><br>
                <div>
                    <label>Level:
                        <select name="courses__level">
                            <?php foreach (COURSE_LEVELS as $level) { ?>
                                <option value="<?= $level ?>"><?= ucfirst($level) ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    &nbsp;&nbsp;
                    <label>Type:
                        <select name="courses__type">
                            <?php foreach (COURSE_TYPES as $type) { ?>
                                <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </div>
                <br><br>
            </div>
            <div class="sub_course search_editor">
                <h4>Sub courses:</h4>
                <input
                    type="search"
                    class="input_adviser"
                    placeholder="Name of the sub course"
                    data-table="courses"
                    data-field="name"
                    data-creating=0
                    data-child_attributes='{"type": "checkbox", "checked": true, "onclick": "return false;", "value": 1}'
                >
                <button class="add_item" disabled>add</button>
                <div class="checkbox_list"></div>
            </div>
            <div class="technology search_editor">
                <h4>Technologies:</h4>
                <input
                    type="search"
                    class="input_adviser"
                    placeholder="Name of the technology"
                    data-table="technologies"
                    data-field="name"
                    data-creating=1
                    data-child_attributes='{"type": "number", "max": 100, "min": 1}'
                >
                <button class="add_item" disabled>add</button>
                <div class="checkbox_list"></div>
            </div>
            <div style="margin-top: 40px">
                <button class="changer" data-task="save" data-api_method="updateCourse" data-id="">CREATE</button>
            </div>
        </div>

        <?php $count = 0; foreach ($courses as $item) { ?>
            <details>
                <summary><strong><?= $item["name"] ?></strong> course (<?= $item["id"] ?>)</summary>
                <div data-form_name="user_data">
                    <div>
                        <textarea name="courses__description_en" cols="30" rows="10" placeholder="Description of the course (en)" disabled><?= $item["description_en"] ?></textarea>
                        <br>
                        <textarea name="courses__description_ru" cols="30" rows="10" placeholder="Description of the course (ru)" disabled><?= $item["description_ru"] ?></textarea>
                        <br><br>
                        <div>
                            <label>Level:
                                <select name="courses__level" disabled>
                                    <?php foreach (COURSE_LEVELS as $level) { ?>
                                        <option value="<?= $level ?>" <?= $type == $item["level"] ? "selected" : "" ?> ><?= ucfirst($level) ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                            &nbsp;&nbsp;
                            <label>Type:
                                <select name="courses__type" disabled>
                                    <?php foreach (COURSE_TYPES as $type) { ?>
                                        <option value="<?= $type ?>" <?= $type == $item["type"] ? "selected" : "" ?> ><?= ucfirst($type) ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
                        <br><br>
                    </div>
                    <div class="sub_course search_editor">
                        <h4>Sub courses:</h4>
                        <input
                            type="search"
                            class="input_adviser"
                            placeholder="Name of the sub course"
                            data-table="courses"
                            data-field="name"
                            data-creating=0
                            data-child_attributes='{"type": "checkbox", "checked": true, "onclick": "return false;", "value": 1}'
                            disabled
                        >
                        <button class="add_item" disabled>add</button>
                        <div class="checkbox_list">
                            <?php if ($item['sub_courses']) {
                            foreach ($item['sub_courses'] as $subCourse) { ?>
                                <label title="<?= $subCourse['description_en'] ?? "no description" ?>">
                                    <strong><?= $subCourse['name'] ?></strong> (<?= $subCourse['level'] ?>/<?= $subCourse['type'] ?>)
                                    <input name="courses__<?= $subCourse['id'] ?>" type="checkbox" ="return false;" value="1" data-id="<?= $subCourse['id'] ?>" data-name="<?= $subCourse['name'] ?>" checked disabled>
                                    <input type="hidden" name="courses__<?= $subCourse['id'] ?>_name" value="<?= $subCourse['name'] ?>" >
                                    <input type="hidden" name="courses__<?= $subCourse['id'] ?>_description" value="<?= $subCourse['description_en'] ?>" >
                                    <span class="remover" onclick="this.parentElement.querySelector('input').disabled || this.parentElement.remove()">✖</span>
                                </label>
                            <?php }} else { echo "no sub courses......"; } ?>
                        </div>
                    </div>
                    <div class="technology search_editor">
                        <h4>Technologies:</h4>
                        <input
                                type="search"
                                class="input_adviser"
                                placeholder="Name of the technology"
                                data-table="technologies"
                                data-field="name"
                                data-creating=1
                                data-child_attributes='{"type": "number", "max": 100, "min": 1}'
                                disabled
                        >
                        <button class="add_item" disabled>add</button>
                        <div class="checkbox_list">
                            <?php foreach ($item['technologies_arr'] as $technologyData) { ?>
                                <label title="<?= $technologyData['descriptions'] ?>">
                                    <?= $technologyData['name'] ?>
                                    <input
                                            type="number"
                                            name="technologies__<?= $technologyData['id'] ?>"
                                            value="<?= $technologyData['hours'] ?>"
                                            data-id="<?= $technologyData['id'] ?>"
                                            data-name="<?= $technologyData['name'] ?>"
                                            checked
                                            disabled
                                    >
                                    <span class="remover" onclick="this.parentElement.querySelector('input').disabled || this.parentElement.remove()">✖</span>
                                </label>
                            <?php } ?>
                        </div>
                    </div>
                    <div style="margin-top: 40px">
                        <button class="changer" data-task="change" data-api_method="updateCourse" data-id="<?=$item['id'] ?>">CHANGE</button>
                        <button class="deleter" title="delete the course" data-id=<?= $item['id']?> data-api_method="delCourse">del</button>
                    </div>
                </div>
            </details>
        <?php } ?>
    </div>
<?php } ?>
    <script src="js/admin_main.js"></script>
</body>
</html>