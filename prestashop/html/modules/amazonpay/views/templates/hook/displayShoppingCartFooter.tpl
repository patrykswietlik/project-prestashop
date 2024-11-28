{*
* 2007-2023 patworx.de
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AmazonPay to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    patworx multimedia GmbH <service@patworx.de>
*  @copyright 2007-2023 patworx multimedia GmbH
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if isset($isInAmazonPayCheckout) && $isInAmazonPayCheckout}
    <p class="amzpay_reset">
        <a href="{$resetlink|escape:'html':'UTF-8'}">
            {l s='You have chosen Amazon Pay - please click here for an alternate payment method' mod='amazonpay'}
        </a>
    </p>
    {literal}<style>#container_express_checkout, #payment_paypal_express_checkout {display:none;}</style>{/literal}
{else}
    <div class="amazonPayShoppingCartFooterButton">
        {include
        file="./_partials/button.tpl"
        AmazonPayButtonPlacement='Cart'
        }
    </div>
{/if}

{if isset($amazon_errors)}
    {include file="$tpl_dir./errors.tpl"}
{/if}
