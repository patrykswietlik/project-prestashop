/**
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
 */
document.addEventListener("DOMContentLoaded", function() {
  var buttons = document.querySelectorAll(".btn.btn-primary.center-block");

  buttons.forEach(function(button) {
    button.addEventListener("click", function(e) {
        $('#sumPaymentForm').on('submit', function () {
		    mountSumupCard(checkoutId, paymentControllerLink, locale, paymentCurrency, paymentAmount, zip_code);
			e.preventDefault();
			toggleSumupModal();
		
            $('.sumup-content').show();
            $('.sumup-module-wrap').addClass('show-sumup-modal');
            button.disabled = true;

            $('.close-sumup-content').click(function() {
                $('.sumup-module-wrap').removeClass('show-sumup-modal');
                button.disabled = false;
            });

        });

    });
  });
});

function mountSumupCard(checkoutId, redirectUrl, locale, currency, amount, zip_code) {
    SumUpCard.mount({
        checkoutId: checkoutId,
        locale: locale,
        currency: currency,
        amount: amount,
        showZipCode: Boolean(zip_code),
        onResponse: function (type, body) {
            if (type == 'success') {
                $('.sumup_loading').show();
                confirmOrder(body, redirectUrl);
            } else if (type == 'error') {
                var message = body.message;
                if (typeof message == 'undefined') {
                    message = ""
                }
                $.growl.error({
                    title: body.error_code,
                    message: message
                });
                $('.sumup_loading').hide();
            }
        },
    });
}

function confirmOrder(responce, redirectUrl) {
    var form = '<input type="hidden" name="status" value=\'' + responce.status + '\'>';
    form += '<input type="hidden" name="amount" value=\'' + responce.amount + '\'>';
    form += '<input type="hidden" name="transaction_code" value=\'' + responce.transaction_code + '\'>';
    form += '<input type="hidden" name="transaction_id" value=\'' + responce.transaction_id + '\'>';
    form += '<input type="hidden" name="id" value=\'' + responce.id + '\'>';
    form += '<input type="hidden" name="checkout_reference" value=\'' + responce.checkout_reference + '\'>';
    form += '<input type="hidden" name="submitValidateOrder" value="1">';
    $('<form action="' + redirectUrl + '" method="POST">' + form + '</form>').appendTo('body').submit();
}

function toggleSumupModal() {
    $('.sumup-module-wrap').toggleClass("show-sumup-modal");
}

/* $(document).on("click",".sumup-module-wrap", function (e){
    toggleSumupModal();
}); */