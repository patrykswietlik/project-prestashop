/**
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
 */

$(document).ready(function() {
    if ($("#HOOK_ADVANCED_PAYMENT").length > 0) {
        if (amazonpay.isInAmazonPayCheckout == 'true') {
            if ($("a.payment_module_adv").length > 0) {
                $("a.payment_module_adv").each(function() {
                    if ($(this).html().indexOf('amazonpay') < 0 && $(this).html().indexOf('Amazon') < 0) {
                        $(this).closest(".payment_module").hide();
                    }
                });
            }
        }
    }
});
