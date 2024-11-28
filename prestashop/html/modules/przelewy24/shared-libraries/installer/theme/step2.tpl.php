<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
?>
<div class="p24-step p24-step-2">
    <?php $content = []; ?>
    <p>
        Autoryzacja
    </p>
    <p>
        Proin nibh augue, suscipit a, scelerisque sed, lacinia
        in, mi. Cras vel lorem. Etiam pellentesque aliquet tellus. Phasellus pharetra nulla ac diam. Quisque semper
        justo at risus. Donec venenatis, turpis vel hendrerit interdum, dui ligula ultricies purus, sed posuere libero
        dui id orci. Nam congue, pede vitae dapibus aliquet, elit magna vulputate arcu, vel tempus metus leo non est.
        Etiam sit amet lectus quis est congue mollis.
    </p>
    <div class="p24-inputs-group">
        <p>
            <label class="p24-required">
               <span>
                    <?php echo $content['translations']['merchant_id']; ?>
               </span>
                <input name="p24_merchant_id" value="12345" class="p24-valid-number" type="text"/>
            </label>
        </p>
        <p>
            <label class="p24-required">
                <span>
                    <?php echo $content['translations']['shop_id']; ?>
                </span>
                <input name="p24_shop_id" value="12345" class="p24-valid-number" type="text"/>
            </label>
        </p>
        <p>
            <label class="p24-required">
                <span>
                    <?php echo $content['translations']['crc_key']; ?>
                </span>
                <input name="p24_crc_key" value="testtest" class="p24-valid-crc" type="text"/>
            </label>
        </p>
    </div>
</div>
