<?php
if(!isset($_SESSION['authorization'])) {exit();}
$siteInfo = getSiteInfo();
?>
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
    <br />
    <br />
    <div class="submit_place">
        <button type="submit">SAVE CHANGES</button>
        <button type="button" class="reset">RESET</button>
    </div>
</form>
