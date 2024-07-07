<?php
if (!isset($_SESSION['authorization'])) {
    exit();
}
$siteInfo = getSiteInfo();
$purchases = getAllPurchasesByFieldName();
?>

<div class="edit_panel purchases price_list">
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
            <label>STATUS: <input name="coupons__status" style="font-weight: bolder; width: 80px; text-transform: uppercase" placeholder="empty" disabled></label>
            <br>
            <br>
            <label>Product: <input name="requests__product_name" placeholder="empty" disabled readonly> [<input name="requests__quantity" style="width: 30px" placeholder="empty" disabled>ea * <input name="requests__current_product_price" style="width: 50px" placeholder="empty" disabled>]</label>
            <br>
            <br>
            <label>Coupon: <input name="coupons__coupon_serial_number" placeholder="empty" disabled> </label>
            <br>
            <br>
            <label><input name="requests__coupon_formula" placeholder="empty" disabled> D=<input name="requests__coupon_value" style="width: 50px" placeholder="empty" disabled></label>
            <br>
            <br>
            <label>TOTAL: $<input name="requests__total_price" style="font-weight: bolder; width: 80px; text-transform: uppercase" placeholder="empty" disabled></label>
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
            <button class="changer" data-task="save" data-api_method="updatePurchase" data-id="">REGISTER PURCHASE PAYMENT</button>
        </div>
    </div>

    <?php $count = 0; foreach ($purchases as $item) { ?>
        <details>
            <summary><b>#<?= $item["request_id"] ?> <?= $item["request_name"] ?></b> "<?= $item["product_name"] ?>" (<?= date("m/d/Y", strtotime($item["created_at"])) ?>)</summary>
            <div data-form_name="user_data" style="text-align: center">
                <input type="hidden" name="purchase_id" value="<?= $item["id"] ?>">
                <div>
                    <label>Request ID:
                        <input type="text" name="requests__id" style="background: lightgray" value="<?= $item["request_id"] ?>" readonly>
                    </label>
                    <br>
                    <br>
                    <label>UID (<input name="requests__uid_type" style="width: 50px"  value="<?= $item["request_uid_type"] ?>" disabled readonly>): <input name="requests__contact" value="<?= $item["request_contact"] ?>" disabled readonly></label>
                    <br>
                    <br>
                    <label>Name: <input name="requests__name"  value="<?= $item["request_name"] ?>" disabled readonly></label>
                    <br>
                    <br>
                    <label>Created: <input name="requests__created_at"  value="<?= date("m/d/Y H:i:s", strtotime($item["request_created_at"])) ?>" disabled readonly></label>
                    <br>
                    <br>
                    <label>STATUS: <input name="request__status" style="font-weight: bolder; width: 80px; text-transform: uppercase" value="<?= $item["request_status"] ?>" disabled readonly></label>
                    <br>
                    <br>
                    <label>Product: <input name="requests__product_name" value="<?= $item["product_name"] ?>" disabled readonly> [<input name="requests__quantity" style="width: 30px" value="<?= $item["request_quantity"] ?>" disabled readonly>ea * <input name="request_current_product_price" style="width: 50px" value="<?= $item["request_current_product_price"] ?>" disabled readonly>]</label>
                    <br>
                    <br>
                    <label>Coupon: <input name="coupons__coupon_serial_number" value="<?= $item["coupon_serial_number"] ?>" disabled readonly> </label>
                    <br>
                    <br>
                    <label><input name="requests__coupon_formula" value="<?= $item["coupon_formula"] ?>" disabled readonly> D=<input name="requests__coupon_value" style="width: 50px" value="<?= $item["coupon_value"] ?>" disabled readonly></label>
                    <br>
                    <br>
                    <label>TOTAL: $<input name="requests__total_price" style="font-weight: bolder; width: 80px; text-transform: uppercase"
                                          value="<?= $requests['total_price'] = countPriceByCoupon($item['request_current_product_price'], $item['coupon_value'], $item['coupon_formula'], $item['request_quantity']) ?>"
                                    disabled readonly></label>
                    <br>
                    <br>
                    <label>Payment type:
                        <select name="purchases__payment_type" required disabled>
                            <?php foreach (PURCHASE_PAYMENT_TYPES as $purchaseMethod) { ?>
                                <option
                                        value="<?= $purchaseMethod ?>"
                                    <?= $purchaseMethod == $item["method"] ? "selected" : "" ?>
                                >
                                    <?= ucfirst(str_replace("_", " ", $purchaseMethod)) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                &nbsp;&nbsp;
                    <label>Service fee:
                        <input type="text" name="purchases__service_fee" id="" placeholder="00.00" value="<?= $item["service_fee"] ?>" style="width: 100px" required disabled>
                    </label>
                    <br><br>
                    <textarea class="json_only" name="purchases__payment_details" cols="30" rows="10" placeholder="Clear JSON only!" required disabled><?= $item["payment_details"] ?></textarea>
                    <br><br>
                    <textarea name="purchases__comment" cols="30" rows="10" placeholder="Comment" required disabled><?= $item["comment"] ?></textarea>
                    <br><br>
                </div>
                <div>
                    <button class="changer" data-task="change" data-api_method="updateCoupon" data-id="<?=$item['id'] ?>">CHANGE</button>
                    <button class="deleter" title="delete the course" data-id=<?= $item['id']?> data-api_method="delCoupon">del</button>
                </div>
            </div>
        </details>
    <?php } ?>
</div>

