{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="p24-sortable-contener p24-payment-description{$altSuffix}"" name="paymethod_list{$suffix}">
    <div class="p24-container">
        <div class="available" data-name="list{$suffix}" style="float:left; width: 100%">
            {foreach $p24_paymethod_description{$suffix} as $bank_id => $bank_name}
                <div class="bank-box" data-id="{$bank_id}" style="height: 90px;">
                    <div class="bank-logo" style="background-image: url({$p24_paymethod_cache{$suffix}[$bank_id]['mobileImgUrl']})"></div>
                    <div class="bank-name">
                        <input type="text" value="{$bank_name}" name="P24_PAYMENT_DESCRIPTION_{$bank_id}{$suffix}"/>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>
