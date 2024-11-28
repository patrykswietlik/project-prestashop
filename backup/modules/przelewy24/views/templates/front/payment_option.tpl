{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<section>
    {if $enable_intro}
        <p>
            {l s='After ordering you will be redirected to the service Przelewy24 to finish payments' mod='przelewy24'}.
        </p>
        <img src="{$logo_url}"
             alt="{l s='Pay with Przelewy24' mod='przelewy24'}">
    {/if}
        <dl>
            {if $extracharge > 0}
                <dt>{l s='Extracharge Przelewy24' mod='przelewy24'}</dt>
                <dd>{$extracharge_formatted} {$tax}</dd>
                <dt>{l s='Amount' mod='przelewy24'}</dt>
                <dd>{$checkTotal} {$tax}</dd>
            {else}
                <dt>{l s='Amount' mod='przelewy24'}</dt>
                <dd>{$checkTotal} {$tax}</dd>
            {/if}
        </dl>
    {hook h='displayInstallmentPayment'}
</section>
