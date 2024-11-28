{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<form method="post" action="{$paymentControllerLink|escape:'htmlall':'UTF-8'}" id="sumPaymentForm"></form>


{if $popup eq 1}
    <div class="sumup-module-wrap">
        <div class="sumup-content">
            <span class="close-sumup-content">×</span>
           <div style="padding-top: 20px">
               <div id="sumup-card"></div>
           </div>
        </div>
    </div>
    <div class="loading sumup_loading" style="display: none">
        <div class="loading-wheel"></div>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.2.js"></script>
    <script src="https://gateway.sumup.com/gateway/ecom/card/v2/sdk.js"></script>
    <script>
        var checkoutId = "{$checkoutId|escape:'javascript':'UTF-8'}";
        var locale = "{$locale|escape:'javascript':'UTF-8'}";
        var zip_code = "{$zip_code|escape:'javascript':'UTF-8'}";
        var paymentCurrency = "{$paymentCurrency|escape:'javascript':'UTF-8'}";
        var paymentAmount = "{$paymentAmount|escape:'javascript':'UTF-8'}";
        var paymentControllerLink  = "{$paymentControllerLink|escape:'javascript':'UTF-8'}";
        $(document).ready(function () {
            mountSumupCard(checkoutId, paymentControllerLink, locale, paymentCurrency, paymentAmount, zip_code);

            $('#sumPaymentForm').on('submit', function (e) {
                e.preventDefault();
                toggleSumupModal();
            });
        })
    </script>
{/if}