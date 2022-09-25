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
<?php } else { ?>
    <form class="reset" method="post">
        <input type="hidden" name="reset" value="1">
        Доброго времени, <b><?= $_SESSION['authorization']['login']?></b>!
        <button type="submit">выход</button>
    </form>
    <form class="edit_panel" method="post" enctype="multipart/form-data">
        <h3>Основные настройки</h3>
        <input type="hidden" name="form_name" value="edit">
        <?php foreach ($siteInfo as $item) { ?>
            <div>
                <label>
                    <?= $item['name']?>
                    <br>
                    <?php if ($item['type'] == 'string') {?>
                        <input type="text" name="<?= $item['field']?>" value="<?= $item['value']?>">
                    <?php } elseif  ($item['type'] == 'text') { ?>
                        <textarea  name="<?= $item['field']?>"><?= $item['value']?></textarea>
                    <?php } elseif  ($item['type'] == 'image') { ?>
                        <div class="image_editor">
                            <div>
                                <img src="<?= $item['value']?>">
                            </div>
                            <input type="file" name="<?= $item['field']?>">
                        </div>
                    <?php } ?>
                </label>
            </div>
        <?php } ?>
        <button type="submit">СОХРАНИТЬ ИЗМЕНЕНИЯ</button>
    </form>
    <div class="edit_panel price_list">
        <h3>Управление сертификатами</h3>
        <div id="new_price_item">
            <b>N</b>
            <div>
                <input type="text" class="search_field" name="users__tg_name" data-field="tg_name" placeholder="telegram nikname" required><br>
                <input type="text" class="search_field" name="users__tg_id" data-field="tg_id" placeholder="telegram id" required><br>
                <input type="text" name="users__real_last_name" placeholder="Реальная фамилия" required><br>
                <input type="text" name="users__real_first_name" placeholder="Реальное имя" required><br>
                <input type="text" name="users__real_middle_name" placeholder="Реальное отчество">
                <br><br>
                <label>Hours: <input type="number" name="certificates__hours" placeholder="кол-во часов" ></label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label>Lang:
                    <select name="certificates__language">
                        <option value="EN">EN</option>
                        <option value="RU">RU</option>
                    </select>
                </label>
                &nbsp;&nbsp;
                <label>Blank:
                    <select name="certificates__blank">
                        <option value="EN">en</option>
                        <option value="RU">ru</option>
                    </select>
                </label>
                <br><br>
                <textarea name="certificates__description" cols="30" rows="10" placeholder="Особые отметки"></textarea>
            </div>
            <div>
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
            <details open>
                <summary><b><?= $count?>) </b> Skills and certificate</summary>
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
                        <textarea name="user__notes" cols="30" rows="10" placeholder="Заметки по пользователю" disabled><?= null ?></textarea>
                    </div>
                    <div>
                        <button class="changer" data-task="change" data-id="<?= $item['id']?>">изменить</button>
                    </div>
                </div>
            </details>
            <details open>
                <summary><b><?= $count?>) </b>Lessons journal</summary>
                <div>
                    <div>
                        <select name="certificates__course">
                            <option>Course</option>
                            <?php foreach ($courses as $course) { ?>
                                <option data-type="<?= $course["type"]?>" data-level="<?= $course["level"]?>" data-technologies="<?= $course["technologies"] ?>" data-technologies_ids="<?= $course["technologies_ids"] ?>" data-technologies_descriptions="<?= $course["technologies_descriptions"] ?>" value="<?= $course["id"] ?>"><?= $course["name"] ?></option>
                            <?php } ?>
                        </select>
                        <span>Date <?= date('d-m-Y', strtotime($item["date"])) ?></span>
                        <br><br>
                        <label>Tg nick: <b><?= $item["tg_name"] ?></b></label><br>
                        <label>Tg DI: <b><?= $item["tg_id"] ?></b></label><br>
                        <input type="text" name="users__real_last_name" placeholder="Реальная фамилия" value="<?= $item["real_last_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_first_name" placeholder="Реальное имя" value="<?= $item["real_first_name"] ?>" required disabled><br>
                        <input type="text" name="users__real_middle_name" placeholder="Реальное отчество" value="<?= $item["real_middle_name"] ?>" disabled>
                    </div>
                    <div>
                        <textarea name="user__notes" cols="30" rows="10" placeholder="Заметки по пользователю" disabled><?= null ?></textarea>
                    </div>
                    <div>
                        <button class="changer" data-task="change" data-id="<?= $item['id']?>">изменить</button>
                    </div>
                </div>
            </details>
            <details>
                <summary>Skills and certificate</summary>
                <div>
                    <div>
                        <h3><?= $item["full_number"] ?></h3>
                        <input type="hidden" name="certificates__id" data-field="id" value="<?= $item["id"] ?>">
                        <span><?= date('d-m-Y', strtotime($item["date"])) ?></span>
                        <br><br>
                        <label>Hours: <input type="number" name="certificates__hours" placeholder="кол-во часов" value="<?= $item["hours"] ?>" disabled></label>
                        <label>Lang: <b><?= $item["language"] ?></b></label>
                        &nbsp;&nbsp;
                        <label>Blank:
                            <select name="certificates__blank" disabled>
                                <option value="en" <?= $item["blank"] == 'en'?'selected':''?>>en</option>
                                <option value="ru" <?= $item["blank"] == 'ru'?'selected':''?>>ru</option>
                            </select>
                        </label>
                        <br>
                        <textarea name="certificates__description" cols="30" rows="10" placeholder="Особые отметки" disabled><?= $item["description"] ?></textarea><br>
                    </div>
                    <div>
                        Course:&nbsp;
                        <strong style="background: "><?= $item['course'] ?></strong>
                        <h5>Technologies:</h5>
                        <div class="checkbox_list">
                            <?php foreach ($courses[$item['course_id']]['technologies_arr'] as $technology) { ?>
                                <?php $checked = in_array($technology['id'], $item['technologies_ids']) ? 'checked' : '' ?>
                                <label title="<?= $technology['descriptions'] ?>">
                                    <input type="checkbox" name="technologies__<?= $technology['id'] ?>" value=<?= $checked ? 1 : 0 ?> onchange="this.value = Number(this.checked)" <?= $checked ?>  disabled><?= $technology['name'] ?>
                                </label>
                            <?php } ?>
                        </div>
                    </div>
                    <div>
                        <button class="changer" data-task="change" data-id="<?= $item['id']?>">изменить</button>
                        <br/>
                        <button class="deleter" data-id=<?= $item['id']?>>удалить</button>
                        <br/><br/><br/><br/><br/>
                        <button title="Загрузка графического файла сертификата" onclick="downloadCertificate(<?= $item['id']?>, this.parentNode)">СКАЧАТЬ</button>
                    </div>
                </div>
            </details>
        <?php } ?>
    </div>
<?php } ?>
    <script src="js/admin_main.js"></script>
</body>
</html>