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

<div class="amzbuttoncontainer">
    <h3 class="page-subheading">
        {l s='Use your Amazon Account' mod='amazonpay'}
    </h3>
    <p>
        {l s='With Amazon Pay and Login with Amazon, you can easily sign-in and use the shipping and payment information stored in your Amazon Account to place an order on this shop.' mod='amazonpay'}
    </p>
    {include
    file="./_partials/button.tpl"
    AmazonPayButtonPlacement='Cart'
    AmazonPayButtonID=$loginButtonId
    AmazonPayButtonClasses='amazonLogin'
    }
</div>
