/*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*/

/* Hide Apple Pay if not configured or not available. */
$(function() {
    var hideById = function(id) {
        /* There are different places and modes the payment can be displayed. */
        $('a.bank-box[data-id=' + id + ']').css('display', 'none');
        $('input[data-module-name=przelewy24-method-' + id + ']').parent().parent().parent().css('display', 'none');
        $('input#paymethod-bank-id-' + id).parent().css('display', 'none');
    }

    var hasApplePay = window.ApplePaySession && window.ApplePaySession.canMakePayments();
    if (!hasApplePay) {
        [
            232,
            252,
            253,
        ].forEach(hideById);
    }
});

