<?php if(!isset($_SESSION['authorization'])) exit(); ?>
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
