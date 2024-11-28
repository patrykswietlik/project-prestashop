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

{assign var="includeFile" value="`$modulePath`/views/templates/hook/_partials/button.tpl"}

{include
file=$includeFile
AmazonPayButtonID='amazonPayCheckoutEmbedded'
AmazonPayButtonClasses='amazonPayCheckoutEmbeddedButton'
AmazonPayButtonPlacement='Checkout'
}
