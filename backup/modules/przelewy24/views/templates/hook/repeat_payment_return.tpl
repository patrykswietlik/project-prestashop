{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="box">

    <img src="{$logo_url}"
         alt="{l s='Pay with Przelewy24' mod='przelewy24'}">
    <h2>{l s='Pay with Przelewy24' mod='przelewy24'}</h2>

    <p>
        {l s='We waiting for accept your payment. If you cancel payment process you can start it again by button' mod='przelewy24'}.
    </p>
    <div class="add">
    <p>
        <a class="btn btn-primary add-to-cart" href="{$redirect_url}">
            {l s='try again' mod='przelewy24'}
        </a>
    </p>

    </div>
</div>

{if $extracharge > 0}
    <input type="hidden" name="extracharge_text" id="extracharge_text" value="{l s='Extracharge Przelewy24' mod='przelewy24'}">
    <input type="hidden" name="extracharge" id="extracharge" value="{$extracharge} {$currencySign}">
{/if}
