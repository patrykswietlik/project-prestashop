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

<div id="sumup-card"></div>
<div class="loading sumup_loading" style="display: none">
    <div class="loading-wheel"></div>
</div>
<script src="https://gateway.sumup.com/gateway/ecom/card/v2/sdk.js"></script>
<script>
    $(document).ready(function (e) {
        var checkoutId = "{$checkoutId|escape:'javascript':'UTF-8'}";
        var locale = "{$locale|escape:'javascript':'UTF-8'}";
        var zip_code = "{$zip_code|escape:'javascript':'UTF-8'}";
        var location  = "{$paymentControllerLink|escape:'javascript':'UTF-8'}";
        var paymentCurrency = "{$paymentCurrency|escape:'javascript':'UTF-8'}";
        var paymentAmount = "{$paymentAmount|escape:'javascript':'UTF-8'}";
        mountSumupCard(checkoutId, location, locale, paymentCurrency, paymentAmount, zip_code);
    });
</script>


