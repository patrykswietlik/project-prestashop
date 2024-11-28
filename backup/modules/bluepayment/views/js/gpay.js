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
    let gpayConfigUrl = null,
        paymentsClient = null,
        gpayConfig = null,
        bmSubmitGpayData = {},
        conditionsSelector = null;

    if ($('#gpay-button').length !== 0) {
        initializeGpay();
    }

    function initializeGpay() {
        conditionsSelector = $('#conditions-to-approve input[type="checkbox"]');
        gpayConfigUrl = $('#gpay-url').attr('data-merchant-info-address');

        $.ajax(gpayConfigUrl, {
            method: 'POST',
            data: {"postOrderId": null},
            success: function (response) {
                $.getScript("https://pay.google.com/gp/p/js/pay.js", function () {
                    gpayConfig = JSON.parse(response);
                    addGooglePayButton();
                });
            },
            error: function (response, status, error) {
                console.error(status, error);
            }
        });
    }

    function getGooglePaymentsClient() {
        if (paymentsClient === null) {
            paymentsClient = new google.payments.api.PaymentsClient({environment: bluepayment_env});
        }

        return paymentsClient;
    }

    function addGooglePayButton() {
        if ($('#gpay-button').length === 0) {
            return;
        }

        paymentsClient = getGooglePaymentsClient();
        document.getElementById('gpay-button')
            .appendChild(paymentsClient.createButton({onClick: onGooglePaymentButtonClicked}));
    }

    function onGooglePaymentButtonClicked() {
        if (!conditionsSelector.prop("checked")) {
            $('.js-g-pay-terms-of-use').show();
            return;
        } else {
            $('.js-g-pay-terms-of-use').hide();
        }

        paymentsClient = getGooglePaymentsClient();
        paymentsClient.loadPaymentData(gpayConfig)
            .then(function (paymentData) {
                processPayment(paymentData);
            })
            .catch(function (err) {
                console.error(err);
            });
    }

    function processPayment(paymentData) {
        let paymentToken = paymentData.paymentMethodData.tokenizationData.token;

        if (bluepayment_env !== 'TEST') {
            paymentToken = JSON.parse(paymentToken);
        }

        chargeGPay(paymentToken);
    }

    function chargeGPay(paymentToken) {
        bmShowLoader();

        var gpayVerifyCheck = null;
        var responseMessages = $('#responseGPayMessages');
        var chargeGpayAction = $('#gpay-url').attr('data-charge-address');

        bmSubmitGpayData.token = paymentToken;

        $.ajax(chargeGpayAction, {
            method: "POST",
            data: bmSubmitGpayData,
            success: function (response) {
                response = JSON.parse(response);
                if (response) {
                    if (response.status === 'PENDING') {
                        if (typeof response.redirectUrl !== "undefined" && response.redirectUrl.length > 10) {
                            window.location.href = response.redirectUrl;
                        }

                        responseMessages.html('<b>' + response.message + '</b>');
                        responseMessages.show();
                        bmSubmitGpayData.postOrderId = response.postOrderId;
                        gpayVerifyCheck = setInterval(chargeGPay(paymentToken), 1250)
                    } else if (response.status === 'SUCCESS') {
                        clearInterval(gpayVerifyCheck);

                        window.location.href = response.backUrl
                    } else {
                        bmSubmitGpayData.postOrderId = response.postOrderId;
                        clearInterval(gpayVerifyCheck);
                        bmHideLoader();
                        responseMessages.parent('div').addClass('has-error');
                        responseMessages.html(response.message);
                        responseMessages.show();
                        submitter.removeAttr('disabled')
                    }
                } else {
                    clearInterval(gpayVerifyCheck);
                    bmHideLoader();
                    responseMessages.parent('div').addClass('has-error');
                    responseMessages.html('Transaction ERROR - Empty data.');
                    responseMessages.show();
                    submitter.removeAttr('disabled');
                }
            },
            error: function(data) {
                bmHideLoader();
            }
        });
    }

    function bmShowLoader () {
        $('.bluepayment-loader').fadeIn(400)
        $('.bluepayment-loader-bg').fadeIn(300)
    }

    function bmHideLoader () {
        $('.bluepayment-loader').fadeOut(300)
        $('.bluepayment-loader-bg').fadeOut(300)
    }
});
