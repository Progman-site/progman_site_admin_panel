<?php
if(!isset($_SESSION['authorization'])) {exit();}
$coupons = getCoupons();
$couponUnits = getCouponUnits();
$couponTypes = getCouponTypes();
$couponPlacements = getCouponPlacements();
?>
<div class="edit_panel coupons price_list">
    <h3>Promotion coupons</h3>
    <div id="new_price_item">
        <b>Generate a NEW one</b>
        <div>
            <input type="hidden" name="coupons__id" data-field="coupons_id" value="" minlength="5">
            <input type="text" class="large_input" name="coupons__name" placeholder="Name/Title of the coupon" required>
            <br><br>
            <div>
                <label>Method:
                    <select name="coupons__method" required>
                        <?php foreach (COUPON_METHODS as $couponMethod) { ?>
                            <option
                                    value="<?= $couponMethod ?>"
                                <?= $couponMethod == COUPON_DEFAULT_METHOD ? "selected" : "" ?>
                            >
                                <?= ucfirst($couponMethod) ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                &nbsp;&nbsp;
                <label>Type:
                    <select data-prefix="" title="" name="coupons__coupon_type_id" required>
                        <?php foreach ($couponTypes as $couponType) { ?>
                            <option data-prefix="" value="" title="" selected></option>
                            <option
                                    value="<?= $couponType['id'] ?>"
                                    data-prefix="<?= $couponType['prefix'] ?>"
                                    title="<?= $couponType['use_link'] ?>"
                            >
                                <?= ucfirst($couponType['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <input type="hidden" value="" name="coupon_types__prefix">
                </label>
            </div>
            <br>
            <input type="text" name="coupons__serial_number" placeholder="Serial number" required>
            <br>
            <br>
            <label>Language:
                <select name="coupons__language" required>
                    <?php foreach (AVAILABLE_LANGUAGES as $language) { ?>
                        <option
                                value="<?= $language ?>"
                            <?= $language == DEFAULT_LANGUAGE ? "selected" : "" ?>
                        >
                            <?= strtoupper($language) ?>
                        </option>
                    <?php } ?>
                </select>
            </label>
            <br>
            <textarea name="coupons__description" cols="30" rows="10" placeholder="Coupon description (optional)"></textarea>
            <br>
            <br>
            <div>
                <select name="coupons__coupon_unit_id" required>
                    <option selected></option>
                    <?php foreach ($couponUnits as $couponUnit) { ?>
                        <option
                                data-symbol="<?= $couponUnit['symbol'] ?>"
                                data-symbol_placement="<?= $couponUnit['symbol_placement'] ?>"
                                data-formula="<?= $couponUnit['formula'] ?>"
                                value="<?= $couponUnit['id'] ?>"
                        >
                            <?= strtoupper($couponUnit['name']) ?>
                        </option>
                    <?php } ?>
                </select>
                &nbsp;&nbsp;
                <label>Value:
                    <strong class="coupon_unit_prefix coupon_unit_prefix_before"></strong>
                    <input type="number" name="coupons__value" required>
                    <strong class="coupon_unit_prefix coupon_unit_prefix_after"></strong>
                </label>
                &nbsp;&nbsp;
                <label>Active:
                    <input type="checkbox" name="coupons__is_active" style="zoom: 1.5;" checked onchange="this.value = this.checked" value=1>
                </label>
            </div>
            <br>
            <label>Formula:&nbsp;
                <strong class="coupon_unit_formula"></strong>
            </label>
            <br>
            <br>
            <div>
                <label>Max times:
                    <input type="number" name="coupons__max_times" value="1" required>
                </label>
                &nbsp;&nbsp;
                <label>Expired at:
                    <input type="date" name="coupons__expired_at" required>
                </label>
            </div>
            <br>
            <br>
            <input type="text" class="large_input" name="coupons__area"  placeholder="Promotion area" required>
            <br>
            <br>
            <div>
                <label>Area type:
                    <select name="coupons__area_type">
                        <?php foreach (COUPON_AREA_TYPES as $areaType) { ?>
                            <option
                                    value="<?= $areaType ?>"
                                <?= $areaType == COUPON_DEFAULT_AREA_TYPE ? "selected" : "" ?>
                            >
                                <?= strtoupper($areaType) ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
                &nbsp;&nbsp;
                <label>Placement:
                    <select name="coupons__placement_id">
                        <option selected></option>
                        <?php foreach ($couponPlacements as $couponPlacement) { ?>
                            <option value="<?= $couponPlacement['id'] ?>">
                                <?= ucfirst($couponPlacement['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
            </div>
            <br>
            <br>
            <div>
                <button class="changer" data-task="save" data-api_method="updateCoupon" data-id="">GENERATE</button>
            </div>
        </div>
    </div>

    <?php foreach ($coupons as $item) { ?>
        <details>
            <summary <?= (!$item["is_active"] || strtotime($item["expired_at"]) < time()) ? 'style="color: dimgray"' : '' ?>><?= $item["name"] ?> <b><?= $item["serial_number"] ?></b> <span style="font-style: italic"><?= strtotime($item["expired_at"]) < time() ? '(expired)' : '' ?></span></summary>
            <div data-form_name="user_data">
                Created: <b><?= date('m/d/Y', strtotime($item["created_at"])) ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;Last update: <b><?= date('m/d/Y H:i', strtotime($item["updated_at"])) ?></b>
                <div>
                    <input type="hidden" name="coupons__id" data-field="coupons_id" value="<?= $item["id"] ?>" minlength="5" disabled>
                    <input type="text" class="large_input" name="coupons__name" placeholder="Name/Title of the coupon" value="<?= $item["name"] ?>" disabled required>
                    <br><br>
                    <div>
                        <label>Method:
                            <select name="coupons__method" style="pointer-events: none;" required disabled class="select_readonly">
                                <?php foreach (COUPON_METHODS as $couponMethod) { ?>
                                    <option
                                            value="<?= $couponMethod ?>"
                                        <?= $couponMethod == $item["method"] ? "selected" : "" ?>
                                    >
                                        <?= ucfirst($couponMethod) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>
                        &nbsp;&nbsp;
                        <label>Type:
                            <select data-prefix="" title="" name="coupons__coupon_type_id" required disabled class="select_readonly">
                                <?php $prefix = null; foreach ($couponTypes as $couponType) { ?>
                                    <?= $prefix = (!$prefix && $couponType['id'] == $item["coupon_type_id"]) ? $couponType['prefix'] : null ?>
                                    <option
                                            value="<?= $couponType['id'] ?>"
                                            data-prefix="<?= $couponType['prefix'] ?>"
                                            title="<?= $couponType['use_link'] ?>"
                                        <?= $couponType['id'] == $item["coupon_type_id"] ? "selected" : "" ?>
                                    >
                                        <?= ucfirst($couponType['name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <input type="hidden" value="<?= $prefix ?>" name="coupon_types__prefix" disabled readonly>
                        </label>
                    </div>
                    <br>
                    <input type="text" name="coupons__serial_number" placeholder="Serial number" value="<?= $item["serial_number"] ?>" required disabled readonly>
                    <br>
                    <br>
                    <label>Language:
                        <select name="coupons__language" required disabled>
                            <?php foreach (AVAILABLE_LANGUAGES as $language) { ?>
                                <option
                                        value="<?= $language ?>"
                                    <?= $language == $item["language"] ? "selected" : "" ?>
                                >
                                    <?= strtoupper($language) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                    <br>
                    <textarea name="coupons__description" cols="30" rows="10" placeholder="Coupon description (optional)" disabled><?= $item['description'] ?></textarea>
                    <br>
                    <br>
                    <div>
                        <select name="coupons__coupon_unit_id" required disabled class="select_readonly">
                            <?php $formula = null; $symbol = null; $symbolPlacement = null;
                                foreach ($couponUnits as $couponUnit) {
                                 if(!$formula && $couponUnit['id'] == $item["coupon_unit_id"]) {
                                    $formula = $couponUnit['formula'];
                                    $symbol = $couponUnit['symbol'];
                                    $symbolPlacement = $couponUnit['symbol_placement'];
                                } ?>
                                <option
                                        data-symbol="<?= $couponUnit['symbol'] ?>"
                                        data-symbol_placement="<?= $couponUnit['symbol_placement'] ?>"
                                        data-formula="<?= $couponUnit['formula'] ?>"
                                        value="<?= $couponUnit['id'] ?>"
                                    <?= $couponUnit['id'] == $item["coupon_unit_id"] ? "selected" : "" ?>
                                >
                                    <?= strtoupper($couponUnit['name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                        &nbsp;&nbsp;
                        <label>Value:
                            <strong class="coupon_unit_prefix coupon_unit_prefix_before"><?= $symbolPlacement == 'before' ? $symbol : '' ?></strong>
                            <input type="number" name="coupons__value" value="<?= $item['value'] ?>" required disabled>
                            <strong class="coupon_unit_prefix coupon_unit_prefix_after"><?= $symbolPlacement == 'after' ? $symbol : '' ?></strong>
                        </label>
                        &nbsp;&nbsp;
                        <label>Active:
                            <input
                                    type="checkbox"
                                    name="coupons__is_active"
                                    style="zoom: 1.5;"
                                    onchange="this.value = this.checked"
                                    value=<?= (bool) $item["is_active"] ?>
                                <?= $item["is_active"] ? "checked" : "" ?>
                                    disabled
                            >
                        </label>
                    </div>
                    <br>
                    <label>Formula:&nbsp;
                        <strong class="coupon_unit_formula"><?= $formula ?></strong>
                    </label>
                    <br>
                    <br>
                    <div>
                        <label>Max times:
                            <input type="number" name="coupons__max_times" value="<?= $item["max_times"] ?>" required disabled>
                        </label>
                        &nbsp;&nbsp;
                        <label>Expired at:
                            <input type="date" name="coupons__expired_at" value="<?= date("Y-m-d", strtotime($item["expired_at"])) ?>" required disabled>
                        </label>
                    </div>
                    <br>
                    <br>
                    <input type="text" class="large_input" name="coupons__area"  placeholder="Promotion area" value="<?= $item["area"] ?>" required disabled>
                    <br>
                    <br>
                    <div>
                        <label>Area type:
                            <select name="coupons__area_type" disabled>
                                <?php foreach (COUPON_AREA_TYPES as $areaType) { ?>
                                    <option
                                            value="<?= $areaType ?>"
                                        <?= $areaType == $item["area_type"] ? "selected" : "" ?>
                                    >
                                        <?= strtoupper($areaType) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>
                        &nbsp;&nbsp;
                        <label>Placement:
                            <select name="coupons__placement_id" disabled>
                                <option selected></option>
                                <?php foreach ($couponPlacements as $couponPlacement) { ?>
                                    <option
                                            value="<?= $couponPlacement['id'] ?>"
                                        <?= $couponPlacement['id'] == $item["placement_id"] ? "selected" : "" ?>
                                    >
                                        <?= ucfirst($couponPlacement['name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>
                    </div>
                    <br>
                    <br>
                    <div>
                        <button class="changer" data-task="change" data-api_method="updateCoupon" data-id="<?=$item['id'] ?>">CHANGE</button>
                        <button class="deleter" title="delete the course" data-id=<?= $item['id']?> data-api_method="delCoupon">del</button>
                    </div>
                </div>
            </div>
        </details>
    <?php } ?>
</div>
