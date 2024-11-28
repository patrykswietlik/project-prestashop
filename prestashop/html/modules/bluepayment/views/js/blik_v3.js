/**
 * BlueMedia_BluePayment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @category       BlueMedia
 * @package        BlueMedia_BluePayment
 * @copyright      Copyright (c) 2015-2024
 * @license        https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

$(document).ready(function () {
    var bmSubmitBlikData = {};

    $('#wrongBlikCode').hide();
    $('#bm-termofuse').hide();
    $('#responseMessages').hide();

    $('#bluepaymentForm').on('submit', function () {
        hideBlikErrors();
        var blikAction = $(this).attr('action');

        var submitFlag = validateBlikCode();
        var blikCode = $('[name="bluepayment_input_blikCode"]').val();
        if (!submitFlag) {
            return false
        }

        bmShowLoader();
        submitter.attr('disabled', 'disabled');
        bmSubmitBlikData.blikCode = blikCode;

        sendBlikCode(blikAction);
        return false;
    });

    function sendBlikCode(blikAction) {
        var blik_verify_check = null;
        var responseMessages = $('#responseMessages');
        $.ajax(blikAction, {
            method: 'POST',
            type: 'POST',
            data: bmSubmitBlikData,
            success: function (response) {
                response = JSON.parse(response);
                if (response) {
                    if (response.status === 'PENDING') {
                        if (typeof response.redirectUrl !== "undefined" && response.redirectUrl.length > 10) {
                            window.location.href = response.redirectUrl;
                        }
                        responseMessages.html('<b>' + response.message + '</b>');
                        responseMessages.show();
                        bmSubmitBlikData.postOrderId = response.postOrderId;
                        blik_verify_check = setInterval(sendBlikCode(blikAction), 1000)
                    }
                    else if (response.status === 'SUCCESS') {
                        clearInterval(blik_verify_check);
                        window.location.href = response.backUrl
                    }
                    else if (response.status === 'FAILURE') {
                        let message = typeof response.message !== 'undefined' && response.message.length !== 0
                            ? response.message
                            : 'Transaction ERROR - Empty data.';

                        clearInterval(blik_verify_check)
                        bmHideLoader()
                        responseMessages.parent('div').addClass('has-error')
                        responseMessages.html(message)
                        responseMessages.show()
                        submitter.removeAttr('disabled')
                    } else {
                        clearInterval(blik_verify_check)
                        bmHideLoader()
                        responseMessages.parent('div').addClass('has-error')
                        responseMessages.html('Transaction ERROR - Empty data.')
                        responseMessages.show()
                        submitter.removeAttr('disabled')
                    }
                }
            }
        });
    }

    function validateBlikCode() {
        var show = true;
        var blikCodeInput = $('[name="bluepayment_input_blikCode"]');
        var blikCodeValidation = blikCodeInput.val();
        if (/^[0-9]{6}$/.test(blikCodeValidation)) {
            blikCodeInput.parent('div').removeClass('has-error');
            $('#wrongBlikCode').hide();
            show = true;
        } else {
            blikCodeInput.parent('div').addClass('has-error');
            $('#wrongBlikCode').show();
            show = false;
        }
        $('#bm-termofuse').hide();
        $(conditionsSelector + ' input[type="checkbox"]').each(function (_, checkbox) {
            if (!checkbox.checked) {
                blikCodeInput.parent('div').addClass('has-error');
                $('#bm-termofuse').show();
                show = false;
            }
        });

        return show
    }

    function clearValidateBlik() {
        var blikCodeInput = $('[name="bluepayment_input_blikCode"]');

        $('#blikSubmit').removeAttr('disabled').removeClass('disabled');
        $('#wrongBlikCode').hide();
        $('#bm-termofuse').hide();
        blikCodeInput.parent('div').removeClass('has-error');

    }

    $(conditionsSelector + ' input[type="checkbox"]').click(function () {
        clearValidateBlik();
    });

    $('#bluepayment-blikCode').on('keyup', function() {
        clearValidateBlik();
    });

});


var submitter = $('#bluepaymentForm').find('[type="submit"]');
var conditionsSelector = '#conditions-to-approve';

function hideBlikErrors() {

    $('[name="bluepayment_input_blikCode"]').parent('div').removeClass('has-error');
    $('#blikAliasError').hide();
    $('#blikCodeError').hide();
    $('#responseMessages').hide();
}

function bmShowLoader() {
    $('.bluepayment-loader').fadeIn(400);
    $('.bluepayment-loader-bg').fadeIn(300);
}

function bmHideLoader() {
    $('.bluepayment-loader').fadeOut(300);
    $('.bluepayment-loader-bg').fadeOut(300);
}
