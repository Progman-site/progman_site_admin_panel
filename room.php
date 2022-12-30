<?php require_once "admin_room_controller.php"; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles/admin.css">
    <title>Админка</title>
</head>
<body>
<?php if (!isset($_SESSION['authorization'])) { ?>
    <form class="authorization" method="post">
        <h3>Вход в админ панель</h3>
        <div>
            <input type="text" name="login" placeholder="login">
        </div>
        <div>
            <input type="password" name="password" placeholder="password">
        </div>
        <button type="submit">ВОЙТИ</button>
    </form>
<?php exit();?>
<?php }?>
    <form class="reset" method="post">
        <input type="hidden" name="reset" value="1">
        Доброго времени, <b><?= $_SESSION['authorization']['login']?></b>!
        <button type="submit">выход</button>
    </form>
    <form class="navigation">
        <input type="submit" class="<?= @$_GET['navigation'] == "certificates" ? "pressed" : "" ?>" name="navigation" value="certificates">
        <input type="submit" class="<?= @$_GET['navigation'] == "setting" ? "pressed" : "" ?>" name="navigation" value="setting">
    </form>
<?php if (@$_GET['navigation'] == "setting") { ?>
    <form class="edit_panel" method="post" enctype="multipart/form-data">
        <h3>Основные настройки</h3>
        <input type="hidden" name="form_name" value="edit">
        <?php foreach ($siteInfo as $tag => $langs) { ?>
        <details>
            <summary><b><?= $tag ?></b></summary>
            <?php foreach ($langs as $lang => $item) { ?>
                <div>
                    <label>
                        <?= $item['name']?> (<?= $lang?>)
                    </label>
                    <br>
                    <?php if ($item['type'] == 'string') {?>
                        <input type="text" name="<?= $item['tag'] . "-" . $lang . "_" . LANG_SESSION_KEY ?>" value="<?= $item[$lang . "_" . LANG_SESSION_KEY]?>">
                    <?php } elseif  ($item['type'] == 'text') { ?>
                        <textarea  name="<?= $item['tag'] . "-" . $lang . "_" . LANG_SESSION_KEY ?>"><?= $item[$lang . "_" . LANG_SESSION_KEY]?></textarea>
                    <?php } elseif  ($item['type'] == 'image') { ?>
                        <div class="image_editor">
                            <div>
                                <img src="<?= $item[$lang . "_" . LANG_SESSION_KEY] ?>">
                            </div>
                            <input type="file" name="<?= $item['tag'] . "-" . $lang . "_" . LANG_SESSION_KEY?>">
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </details>
        <?php } ?>
        <button type="submit">СОХРАНИТЬ ИЗМЕНЕНИЯ</button>
    </form>
    <pre>
<?php } ?>
<?php if (@$_GET['navigation'] == "certificates") { ?>
    <div class="edit_panel certificates price_list">
        <h3>Управление сертификатами</h3>
        <div id="new_price_item">
            <b>NEW</b>
            <div>
                <input type="text" class="search_field" name="users__tg_name" data-field="tg_name" placeholder="telegram nikname" required><br>
                <input type="text" class="search_field" name="users__tg_id" data-field="tg_id" placeholder="telegram id" required><br>
                <input type="text" name="users__real_last_name" placeholder="Реальная фамилия" required><br>
                <input type="text" name="users__real_first_name" placeholder="Реальное имя" required><br>
                <input type="text" name="users__real_middle_name" placeholder="Реальное отчество">
                <br><br>
                <label>Hours: <input type="number" name="certificates__hours" placeholder="кол-во часов" ></label>
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
                <textarea name="certificates__description" cols="30" rows="10" placeholder="Особые отметки"></textarea>
            </div>
            <div style="margin-top: 20px">
                <select name="certificates__course">
                    <option>Course</option>
                    <?php foreach ($courses as $course) { ?>
                        <option data-type="<?= $course["type"]?>" data-level="<?= $course["level"]?>" data-technologies="<?= $course["technologies"] ?>" data-technologies_ids="<?= $course["technologies_ids"] ?>" data-technologies_descriptions="<?= $course["technologies_descriptions"] ?>" value="<?= $course["id"] ?>"><?= $course["name"] ?></option>
                    <?php } ?>
                </select>
                <br>
                <strong>тип: <span class="course_type"></span></strong>
                &nbsp;&nbsp;
                <strong>Level: <span class="course_level"></span></strong>
                <h4>Technologies:</h4>
                <div class="checkbox_list"></div>
            </div>
            <div>
                <button class="changer" data-task="save" data-id="">ВЫДАТЬ</button>
            </div>
        </div>

        <?php $count = 0; foreach ($certificates as $item) { ?>
            <details>
                <summary><b><?= $item["real_first_name"] ?> <?= $item["real_last_name"] ?></b> certificate</summary>
                <div data-form_name="user_data">
                    <div>
                        <input type="hidden" name="users__tg_name" data-field="tg_name" value="<?= $item["tg_name"] ?>">
                        <input type="hidden" name="users__tg_id" data-field="tg_id" value="<?= $item["tg_id"] ?>">
                        <span>Start: <?= date('d-m-Y', strtotime($item["date"])) ?></span>
                        <br><br>
                        <label>Tg nick: <b><?= $item["tg_name"] ?></b></label><br>
                        <label>Tg DI: <b><?= $item["tg_id"] ?></b></label><br>
                        <input type="text" name="users__real_last_name" placeholder="Реальная фамилия" value="<?= $item["real_last_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_first_name" placeholder="Реальное имя" value="<?= $item["real_first_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_middle_name" placeholder="Реальное отчество" value="<?= $item["real_middle_name"] ?>" disabled>
                    </div>
                    <div>
                        <textarea name="certificates__description" cols="30" rows="10" placeholder="Заметки по пользователю" disabled><?= $item['description'] ?></textarea>
                        <br><br>
                        <div>
                            Course: <strong><?= $item['course']?></strong>
                        </div>
                        <br>
                        <strong>тип: <span class="course_type"><?= $item['id']?></span></strong>
                        &nbsp;&nbsp;
                        <strong>Level: <span class="course_level"><?= $item['level']?></span></strong>
                        <h4>Technologies:</h4>
                    </div>
                    <div>
                        <button class="changer" data-task="change" data-id="<?= $item['id']?>">изменить</button>
                        <br/><br/>
                        <button class="deleter" data-id=<?= $item['id']?>>удалить</button>
                        <br/><br/><br/><br/><br/>
                        <button title="Загрузка графического файла сертификата" onclick="downloadCertificate(<?= $item['id']?>, this.parentNode.parentNode)">СКАЧАТЬ</button>
                    </div>
                    <div>
                        <br>
                        <div>
                            <label>
                                Hours:
                                <input type="number" name="certificates__hours" placeholder="кол-во часов" value="<?= $item['hours']?>" disabled>
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
    <script src="js/admin_main.js"></script>
</body>
</html>