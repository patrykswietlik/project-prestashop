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
    if ($("#button_order_cart").length > 0 && $("#AmazonPayMiniCart").length > 0) {
        $('#button_order_cart').before($("#AmazonPayMiniCart"));
        $("#AmazonPayMiniCart").show();
    }
    setInterval(function() {
        if ($("#form_onepagecheckoutps").length > 0 && $(".amzpay_reset").length > 0) {
            if ($('input[name="method_payment"]').length > 0) {
                $('input[name="method_payment"]').each(function() {
                    if ($(this).val()  == 'amazonpay') {
                        if (!$(this).is(':checked')) {
                            $(this).trigger('click');
                        }
                    } else {
                        $(this).closest('.row.module_payment_container').hide().attr("style", "display: none !important");
                    }
                });
            }
        }
    }, 1000);

    if ($("#authentication #SubmitCreate").length > 0 && amazonpay.showInLoginSection == 'true') {
        if ($("body#authentication").length > 0 && $("#order_step").length == 0) {
            $("#authentication #SubmitCreate").parent().append(amazonpay.loginButtonCode);
        }
    }
    if ($("#authentication #SubmitCreate").length > 0 && amazonpay.showInCheckoutSection == 'true') {
        if (!($("body#authentication").length > 0 && $("#order_step").length == 0)) {
            $("#authentication #SubmitCreate").parent().append(amazonpay.loginButtonCode);
            $(".amazonLogin").removeClass('amazonLogin').addClass('amazonPayButton');
        }
    }

});
