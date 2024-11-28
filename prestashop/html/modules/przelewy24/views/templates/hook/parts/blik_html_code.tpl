{*
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*}

<div
        id="p24-blik-config-element"
        data-ajaxurl="{$p24_charge_blik_url|escape:'html':'UTF-8'}"
        data-pagetype="{$p24_page_type|escape:'html':'UTF-8'}"
        data-cartid="{$p24_cart_id|escape:'html':'UTF-8'}"
></div>

<div style="display: none;">
    <div>
        <div id="p24-blik-section" class="p24-inside-section">
            <h1>{l s='Enter BLIK code' mod='przelewy24'}</h1>
            <form method="post">
                <div>
                    <p>{l s='Enter 6-digit BLIK code from Your bank application.' mod='przelewy24'}</p>
                    <p>
                        <input
                                minlength="6"
                                maxlength="6"
                                pattern="^\d{'{6}'|escape:'htmlall':'UTF-8'}$"
                                type="text"
                                name="blik"
                                placeholder="______"
                        >
                    </p>
                    {if $p24_blik_needs_term_accept }
                    <p>
                        <input type="checkbox" name="terms" id="p24-blik-regulation-accept" value="1">
                        {l s='Please accept' mod='przelewy24'}
                        <a href="http://www.przelewy24.pl/regulamin.htm" target="_blank">
                            {l s='the Przelewy24 Terms' mod='przelewy24'}
                        </a>
                    </p>
                    {/if}
                    <p class="error error-code">{l s='Invalid BLIK code.' mod='przelewy24'}</p>
                    <p class="error error-regulation">{l s='Terms not accepted.' mod='przelewy24'}</p>
                    <p class="error error-pr-regulation">{l s='Terms of this shop not accepted.' mod='przelewy24'}</p>
                    <p>
                        <button>{l s='Pay by BLIK' mod='przelewy24'}</button>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
