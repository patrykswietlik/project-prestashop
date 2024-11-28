{*
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*}

<div
        id="p24-card-config-element"
        data-ajaxurl="{$p24_charge_card_url|escape:'html':'UTF-8'}"
        data-pagetype="{$p24_page_type|escape:'html':'UTF-8'}"
        data-cartid="{$p24_cart_id|escape:'html':'UTF-8'}"
        data-ids="{$p24_card_ids_string|escape:'html':'UTF-8'}"
        data-success-url="{$p24_success_url|escape:'html':'UTF-8'}"
        data-failure-url="{$p24_failure_url|escape:'html':'UTF-8'}"
        data-terms-confirm-required="{if $p24_card_needs_term_accept}true{else}false{/if}"
></div>

<div style="display: none;">
    <div>
        <div id="p24-card-section" class="p24-inside-section">
            <h1>{l s='Register card and payment' mod='przelewy24'}</h1>
            {if $p24_card_needs_term_accept}
                <p>
                    <input type="checkbox" name="terms" id="p24-card-regulation-accept" value="1">
                    {l s='Please accept' mod='przelewy24'}
                    <a href="http://www.przelewy24.pl/regulamin.htm" target="_blank">
                        {l s='the Przelewy24 Terms' mod='przelewy24'}
                    </a>
                </p>
            {/if}
            <div
                id="P24FormContainer"
                data-successCallback="payInShopSuccess"
                data-failureCallback="payInShopFailure"
                data-cartid="{$p24_cart_id|escape:'html':'UTF-8'}"
                data-dictionary='{
                    "cardHolderLabel":"{$p24_card_translation.card_holder_label|escape:'html':'UTF-8'}",
                    "cardNumberLabel":"{$p24_card_translation.card_number_label|escape:'html':'UTF-8'}",
                    "cvvLabel":"{$p24_card_translation.cvv_label|escape:'html':'UTF-8'}",
                    "expDateLabel":"{$p24_card_translation.exp_date_label|escape:'html':'UTF-8'}",
                    "payButtonCaption":"{$p24_card_translation.pay_button_caption|escape:'html':'UTF-8'}",
                    "description":"{$p24_card_translation.description|escape:'html':'UTF-8'}",
                    "threeDSAuthMessage":"{$p24_card_translation.three_ds_auth_message|escape:'html':'UTF-8'}",
                    "registerCardLabel":"{$p24_card_translation.register_card_label|escape:'html':'UTF-8'}"
                }'
            >
            </div>
            <div class="p24-card-loader"></div>
        </div>
    </div>
</div>
