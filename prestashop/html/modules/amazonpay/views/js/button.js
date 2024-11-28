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

function getEstimatedOrderAmount() {
    if (!amazonpay.is_prestashop16) {
        let estimatedAmount = 0;
        if (typeof prestashop.cart !== 'undefined' && typeof prestashop.cart.totals.total_including_tax.amount !== 'undefined') {
            estimatedAmount = parseFloat(prestashop.cart.totals.total_including_tax.amount);
        } else if ($(".cart-summary-line.cart-total span.value").length > 0) {
            estimatedAmount = parseFloat($(".cart-summary-line.cart-total span.value").html().replace(',', '.').replace(/[^\d.]+/g, ""));
        }
        if ($("body").attr("id") == 'product' && $(".current-price-value[content]").length > 0) {
            if ($("#quantity_wanted").length > 0) {
                estimatedAmount = parseFloat(estimatedAmount) + parseFloat(($("#quantity_wanted").val() * $(".current-price-value[content]").attr('content')).toFixed(2));
            }
        } else if ($("body").attr("id") == 'product' && $("span[itemprop=price]").length > 0) {
            if ($("#quantity_wanted").length > 0) {
                estimatedAmount = parseFloat(estimatedAmount) + parseFloat(($("#quantity_wanted").val() * $("span[itemprop=price]").attr('content')).toFixed(2));
            }
        }
        if (estimatedAmount > 0) {
            return estimatedAmount.toFixed(2);
        }
    } else {
        if ($(".price.cart_block_total.ajax_block_cart_total").length > 0) {
            return parseFloat($(".price.cart_block_total.ajax_block_cart_total").html().replace(',', '.'));
        }
    }
    return amazonpay.estimatedOrderAmount;
}

function amazonPayInit()
{
    let initCheckoutLoad = {
        merchantId: amazonpay.merchant_id,
        ledgerCurrency: amazonpay.ledgerCurrency,
        sandbox: amazonpay.sandbox,
        checkoutLanguage: amazonpay.checkoutLanguage,
        productType: amazonpay.checkoutType,
        placement: 'Checkout',
        createCheckoutSessionConfig: {
            payloadJSON: amazonpay.button_payload,
            signature: amazonpay.button_signature,
            publicKeyId: amazonpay.public_key_id
        }
    };
    if (getEstimatedOrderAmount() > 0) {
        initCheckoutLoad.estimatedOrderAmount = { "amount": getEstimatedOrderAmount(), "currencyCode": amazonpay.customerCurrencyCode};
    }
    amazon.Pay.initCheckout(initCheckoutLoad);
}
function amazonPayInitApb()
{
    let initCheckoutLoad = {
        merchantId: amazonpay.merchant_id,
        ledgerCurrency: amazonpay.ledgerCurrency,
        sandbox: amazonpay.sandbox,
        checkoutLanguage: amazonpay.checkoutLanguage,
        productType: amazonpay.checkoutType,
        placement: 'Checkout',
        createCheckoutSessionConfig: {
            payloadJSON: amazonpay.button_payload_apb,
            signature: amazonpay.button_signature_apb,
            publicKeyId: amazonpay.public_key_id
        }
    }
    if (getEstimatedOrderAmount() > 0) {
        initCheckoutLoad.estimatedOrderAmount = { "amount": getEstimatedOrderAmount(), "currencyCode": amazonpay.customerCurrencyCode};
    }
    amazon.Pay.initCheckout(initCheckoutLoad);
}

let setEstimatedAmount = getEstimatedOrderAmount();
let AmazonPayProductClickInProcess = false;

$(document).ready(function() {

    if (typeof isLoginToCheckout !== 'undefined') {
        if (isLoginToCheckout === true) {
            amazonPayInitApb();
        } else {
            amazonPayInit();
        }
    }

    function renderAmazonPayButton() {
        if (typeof amazon === 'undefined') {
            return;
        }

        $(".amazonPayButton").each(function() {
            if ($(this).attr('data-rendered') != '1') {

                if ($(this).parent().hasClass('product-quantity') && !amazonpay.is_prestashop16) {
                    $(this).insertAfter($(this).parent());
                }

                let date = new Date();
                let amazonPayButton = $(this);

                if ($(this).attr("id") == '' || typeof $(this).attr("id") === typeof undefined) {
                    $(this).attr("id", "amazonPayTsBtn_" + date.getTime());
                }

                if (amazonPayButton.hasClass('amazonLogin')) {
                    let renderedAmazonPayButton = amazon.Pay.renderButton('#'+amazonPayButton.attr('id'), {
                        merchantId: amazonpay.merchant_id,
                        sandbox: amazonpay.sandbox, // dev environment
                        ledgerCurrency: amazonpay.ledgerCurrency, // Amazon Pay account ledger currency
                        checkoutLanguage: amazonpay.checkoutLanguage, // render language
                        productType: 'SignIn',
                        placement: amazonPayButton.attr('data-placement'), // button placement
                        buttonColor: amazonPayButton.attr('data-color'), // button color
                        signInConfig: {
                            payloadJSON: amazonPayButton.hasClass('amazonLoginCheckout') ? amazonpay.login_to_checkout_button_payload : amazonpay.login_button_payload,
                            signature: amazonPayButton.hasClass('amazonLoginCheckout') ? amazonpay.login_to_checkout_button_signature : amazonpay.login_button_signature,
                            publicKeyId: amazonpay.public_key_id
                        }
                    });
                    $(this).attr('data-rendered', '1');
                    return;
                }

                let createCheckoutSessionParams = {
                    url: amazonpay.amazonPayCheckoutSessionURL
                };

                let renderAmazonPayButtonLoad = {
                    merchantId: amazonpay.merchant_id,
                    sandbox: amazonpay.sandbox, // dev environment
                    ledgerCurrency: amazonpay.ledgerCurrency, // Amazon Pay account ledger currency
                    checkoutLanguage: amazonpay.checkoutLanguage, // render language
                    productType: amazonpay.checkoutType, // 'PayAndShip', // checkout type
                    placement: amazonPayButton.attr('data-placement'), // button placement
                    buttonColor: amazonPayButton.attr('data-color'), // button color
                    design: amazonPayButton.attr('data-design') != '' ? amazonPayButton.attr('data-design') : false // specific design params
                };

                if (getEstimatedOrderAmount() > 0) {
                    renderAmazonPayButtonLoad.estimatedOrderAmount = { "amount": getEstimatedOrderAmount(), "currencyCode": amazonpay.customerCurrencyCode};
                }

                let renderedAmazonPayButton = amazon.Pay.renderButton('#'+amazonPayButton.attr('id'), renderAmazonPayButtonLoad);

                setInterval(function() { updateEstimatedAmount(renderedAmazonPayButton)}, 1000);

                renderedAmazonPayButton.onClick(function() {
                    let click_timeout = 1;
                    if (amazonPayButton.hasClass('amazonPayProductButton')) {
                        AmazonPayProductClickInProcess = true;
                        if ($("#add-to-cart-or-refresh").length > 0) {
                            if ($("#add-to-cart-or-refresh .add-to-cart").length > 0) {
                                $("#add-to-cart-or-refresh .add-to-cart").trigger('click');
                                click_timeout = 2000;
                            }
                        } else if ($("#buy_block").length > 0) {
                            if ($("#buy_block button[type=submit]").length > 0) {
                                $("#buy_block button[type=submit]").trigger('click');
                                click_timeout = 2000;
                            }
                        }
                    }
                    setTimeout(function() { buttonInitCheckout(renderedAmazonPayButton) }, click_timeout);
                });

                $(this).attr('data-rendered', '1');
            }
        });
    }

    function buttonInitCheckout(renderedButton)
    {
        let initCheckoutLoad = {
            createCheckoutSessionConfig: {
                payloadJSON: amazonpay.button_payload,
                signature: amazonpay.button_signature,
                publicKeyId: amazonpay.public_key_id
            }
        }
        if (getEstimatedOrderAmount() > 0) {
            initCheckoutLoad.estimatedOrderAmount = { "amount": getEstimatedOrderAmount(), "currencyCode": amazonpay.customerCurrencyCode};
        }
        renderedButton.initCheckout(initCheckoutLoad);
    }

    function updateEstimatedAmount(button)
    {
        if (AmazonPayProductClickInProcess) {
            return;
        }
        if (setEstimatedAmount != getEstimatedOrderAmount() && getEstimatedOrderAmount() > 0) {
            button.updateButtonInfo({"amount": getEstimatedOrderAmount(), "currencyCode": amazonpay.customerCurrencyCode});
            setEstimatedAmount = getEstimatedOrderAmount();
        }
    }

    renderAmazonPayButton();
    setInterval(renderAmazonPayButton, 1000);

});
