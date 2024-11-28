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

<div
        id="{if isset($AmazonPayButtonID)}{$AmazonPayButtonID|escape:'html':'UTF-8'}{else}AmazonPayButton{/if}"
        class="amazonPayButton {if isset($AmazonPayButtonClasses)}{$AmazonPayButtonClasses|escape:'html':'UTF-8'}{/if}"
        data-placement="{if isset($AmazonPayButtonPlacement)}{$AmazonPayButtonPlacement|escape:'html':'UTF-8'}{else}Other{/if}"
        data-color="{if isset($AmazonPayButtonColor)}{$AmazonPayButtonColor|escape:'html':'UTF-8'}{else}{if isset($AmazonPayButtonPlacement)}{AmazonPayHelper::getButtonColor($AmazonPayButtonPlacement)}{else}Gold{/if}{/if}"
        data-design="{if isset($AmazonPayDesign)}{$AmazonPayDesign|escape:'html':'UTF-8'}{/if}"
        data-rendered="0"
>
</div>
