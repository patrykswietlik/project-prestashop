{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="p24-sortable-contener p24-for-checkout{$altSuffix}" name="paymethod_list{$suffix}">

    <div class="p24-container">
        <p>
            {l s='Payment methods featured when choosing payment system' mod='przelewy24'}:
        </p>
        <div class="draggable-list draggable-list-promote promote" data-name="list{$suffix}" style="float:left; width: 100%">

            {if $p24_paymethod_list_promote{$suffix}|sizeof > 0}
                {foreach $p24_paymethod_list_promote{$suffix} as $bank_id => $bank_name}
                    {if !empty($bank_name)}
                        <div class="draggable-item bank-box" data-id="{$bank_id}">
                            <div class="bank-logo" style="background-image: url({$p24_paymethod_cache{$suffix}[$bank_id]['mobileImgUrl']})"></div>
                            <div class="bank-name">{$bank_name}</div>
                        </div>
                    {/if}
                {/foreach}
            {/if}

            <p class="p24-hint">
                {l s='Drag and drop icons between sections' mod='przelewy24'}
            </p>
        </div>
        <p>
            {l s='Payment methods list' mod='przelewy24'}
        </p>
        <div class="draggable-list draggable-list-promote-2 promote" data-name="list{$suffix}" style="float:left; width: 100%">

            {if $p24_paymethod_list_promote_2{$suffix}|sizeof > 0}
                {foreach $p24_paymethod_list_promote_2{$suffix} as $bank_id => $bank_name}
                    {if !empty($bank_name)}
                        <div class="draggable-item bank-box" data-id="{$bank_id}">
                            <div class="bank-logo" style="background-image: url({$p24_paymethod_cache{$suffix}[$bank_id]['mobileImgUrl']})"></div>
                            <div class="bank-name">{$bank_name}</div>
                        </div>
                    {/if}
                {/foreach}
            {/if}

        </div>
    </div>
</div>
