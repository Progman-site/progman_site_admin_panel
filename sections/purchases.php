<?php
if (!isset($_SESSION['authorization'])) {
    exit();
}
$siteInfo = getSiteInfo();
?>

<div class="edit_panel certificates price_list">
    <h3>Purchase control</h3>
    <div id="new_price_item">
        <b>Register a NEW Purchase/Payment</b>
        <div>
            <label>Request ID:
                <input type="text" class="search_field" name="requests__id" data-field="id" data-form_name="getRequest" placeholder="Request ID (number)" value="">
            </label>
            <br>
            <br>
            <label>UID (<input name="requests__uid_type" style="width: 50px" placeholder="empty" disabled>): <input name="requests__contact" placeholder="empty" disabled></label>
            <br>
            <br>
            <label>Name: <input name="requests__name" placeholder="empty" disabled></label>
            <br>
            <br>
            <label>Created: <input name="requests__created_at" placeholder="empty" disabled></label>
            <br>
            <br>
            <label>Product: <input name="products__name" placeholder="empty" disabled> [<input name="requests__quantity" style="width: 50px" placeholder="empty" disabled> ea]</label>
            <br>
            <br>
            <label>Coupon: <input name="coupons__serial_number" placeholder="empty" disabled></label>
        </div>
        <div style="margin-top: 20px">
            <label><span id="purchases__total_price"></span></label><br>
            <div>
                <label>Payment type:
                    <select name="purchases__payment_type">
                        <option selected></option>
                        <option value="paypal">Paypal</option>
                        <option value="cash">Cash</option>
                        <option value="zelle">Zelle</option>
                        <option value="russian_mobile">Russian mobile</option>
                    </select>
                </label>
                &nbsp;&nbsp;
                <label>Service fee:
                    <input type="text" name="purchases__service_fee" id="" placeholder="00.00" style="width: 100px">
                </label>
                <br><br>
                <textarea class="json_only" name="purchases__payment_details" cols="30" rows="10" placeholder="Clear JSON only!"></textarea>
                <br><br>
                <textarea name="purchases__comment" cols="30" rows="10" placeholder="Comment"></textarea>
                <br><br>
            </div>
        </div>
        <div>
            <button class="changer" data-task="save" data-api_method="updatePurchase" data-id="">REGISTER PAYMENT</button>
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
                    <button title="Downloading the graphic picture of the certificate" onclick="downloadFile(<?= $item['id']?>, this.parentNode.parentNode, 'downloadCertificate')">DOWNLOAD</button>
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

