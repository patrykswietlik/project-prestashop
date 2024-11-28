/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

;(function () {
    let IS_CONSOLE_LOG_ENABLED = true;
    let $gel_prx_delivery_option_form = '';
    let $gel_prx_delivery_option_form_submit = '';
    let isAjaxInit = false;
    let isPageReloadNeeded = true;

    /* Log utility */
    function console_log(obj) {
        if (IS_CONSOLE_LOG_ENABLED) {
            console.log('GEL PROXIMITY >', obj);
        }
    }

    function console_error(obj) {
        if (IS_CONSOLE_LOG_ENABLED) {
            console.error('GEL PROXIMITY >', obj);
        }
    }

    console_log('PS8');

    function validateEmail(email) {
        if (email.length == 0) {
            return true;
        }
        let re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function gelprx_fancybox_alert(message, is_modal = false) {
        $.fancybox({
            'modal': is_modal,
            "padding": 5,
            "margin": 5,
            "autoSize": true,
            "autoCenter": true,
            'content': "<div style=\"width:300px;\" class=\"text-center\"><strong>" + message + "</strong></div>",
        });
    }

    function gelPrxSetPickUpPointForCurrentCart(payload) {
        console_log('gelPrxSetPickUpPointForCurrentCart');

        $.ajax({
            url: gelprx_conf.ajax_url,
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                'token': gelprx_conf.ajax_token,
                'action': 'setPickUpPointForCurrentCart',
                'status': 'ok',
                'payload': payload,
            }
        }).done(function (response) {
            console_log(response);
            if ($('div.gelproximity-app__selection-details').length) {
                $('div.gelproximity-app__selection-details').html(response.data.html);

                if($('div.gelproximity-app__selection-details').html().trim() === ''){
                    $gel_prx_delivery_option_form_submit.hide();
                }
                // else{
                //     $gel_prx_delivery_option_form_submit.show();
                // }
            }
            if (isPageReloadNeeded) {
                location.reload();
            }
        }).fail(function (response, err) {
            console_error(response);
            console_error(err);
            if ($('div.gelproximity-app__selection-details').length) {
                $('div.gelproximity-app__selection-details-error').removeClass('gelproximity-app__hidden');
                $('#payment-confirmation').addClass('gelproximity-app__hidden');
            }
        });
    }

    function disableCheckoutPaymentStep() {
        $('div.payment-options ').addClass('gelproximity-app__hidden');
        $('#conditions-to-approve').addClass('gelproximity-app__hidden');
        $('#payment-confirmation').addClass('gelproximity-app__hidden');
    }

    function enableCheckoutPaymentStep() {
        $('div.payment-options ').removeClass('gelproximity-app__hidden');
        $('#conditions-to-approve').removeClass('gelproximity-app__hidden');
        $('#payment-confirmation').removeClass('gelproximity-app__hidden');
    }

    function init()
    {
        // If is showed the alert about no pick up selected remove every payment option and block the checkout process
        if ($('#gelproximity-app-no-pick-point-selected').length) {
            disableCheckoutPaymentStep();
        }

        if ($('#gelproximity-app__selection-button-sdk-wrapper').length) {

            if (typeof gelprx_conf !== 'undefined') {
                const options = {
                    apiKey: gelprx_conf.apiKey,
                    merchantCode: gelprx_conf.merchantCode,
                    reference: gelprx_conf.reference,
                    redirectMode: gelprx_conf.redirectMode,
                    locale: gelprx_conf.locale,
                    urlEndUser: gelprx_conf.urlEndUser,
                    urlRedirectOk: gelprx_conf.urlRedirectOk,
                    urlRedirectCancel: gelprx_conf.urlRedirectCancel,
                    networks: gelprx_conf.networkCode,
                    prices: gelprx_conf.prices,
                };
                const gelSDK = new GelSDK(options);

                const gelUIModalOptions = {
                    callbackOk: (data) => {
                        // Funzione nel caso venga selezionato un punto di ritiro
                        console_log('modal pick-up point selected');
                        console_log(data);
                        gelPrxSetPickUpPointForCurrentCart(data);
                    },
                    callbackKo: () => {
                        // Funzione nel caso venga chiusa la modale della mappa
                        console_log('modal closed');
                    }
                };

                function renderButtonModal() {
                    gelSDK.createUIModal(gelUIModalOptions);
                }

                $('#gelproximity-app-pick-point-select-button').on('click', function () {
                    renderButtonModal();
                });

                // Find and manage carrier's input radio
                let $gel_prx_delivery_option_radio = '';
                if ($('.delivery-option input[value="' + gelprx_conf.id_carrier + '"]').length) {
                    $gel_prx_delivery_option_radio = $('.delivery-option input[value="' + gelprx_conf.id_carrier + '"]');
                } else if ($('.delivery-option input[value="' + gelprx_conf.id_carrier + ',"]').length) {
                    $gel_prx_delivery_option_radio = $('.delivery-option input[value="' + gelprx_conf.id_carrier + ',"]');
                }

                if ($gel_prx_delivery_option_radio.length) {
                    let $gel_prx_delivery_option_radio_wrapper = $gel_prx_delivery_option_radio.closest('.delivery-option');

                    // Default carrier price and edit button
                    if ($gel_prx_delivery_option_radio_wrapper.length) {

                        // + Detect parent form and submit button
                        $gel_prx_delivery_option_form = $gel_prx_delivery_option_radio_wrapper.closest('form');
                        console.log($gel_prx_delivery_option_form);
                        $gel_prx_delivery_option_form_submit = $gel_prx_delivery_option_form.find('[type="submit"]');
                        console.log($gel_prx_delivery_option_form_submit);

                        if ($gel_prx_delivery_option_form.length && $gel_prx_delivery_option_form_submit.length) {
                            if ($gel_prx_delivery_option_radio.is(':checked')) {
                                if($('div.gelproximity-app__selection-details').html().trim() === ''){
                                    // $gel_prx_delivery_option_form_submit.hide();
                                    $gel_prx_delivery_option_form_submit.css('opacity','0.3');
                                    $gel_prx_delivery_option_form_submit.prop('disabled',true);
                                }else{
                                    // $gel_prx_delivery_option_form_submit.show();
                                    $gel_prx_delivery_option_form_submit.css('opacity','1');
                                    $gel_prx_delivery_option_form_submit.prop('disabled',false);
                                }
                            }else{
                                // $gel_prx_delivery_option_form_submit.show();
                                $gel_prx_delivery_option_form_submit.css('opacity','1');
                                $gel_prx_delivery_option_form_submit.prop('disabled',false);
                            }
                            $gel_prx_delivery_option_form.find('input[type="radio"]').on('change', function () {
                                if ($gel_prx_delivery_option_radio.is(':checked')) {
                                    if($('div.gelproximity-app__selection-details').html().trim() === ''){
                                        // $gel_prx_delivery_option_form_submit.hide();
                                        $gel_prx_delivery_option_form_submit.css('opacity','0.3');
                                        $gel_prx_delivery_option_form_submit.prop('disabled',true);
                                    }else{
                                        // $gel_prx_delivery_option_form_submit.show();
                                        $gel_prx_delivery_option_form_submit.css('opacity','1');
                                        $gel_prx_delivery_option_form_submit.prop('disabled',false);
                                    }
                                }else{
                                    // $gel_prx_delivery_option_form_submit.show();
                                    $gel_prx_delivery_option_form_submit.css('opacity','1');
                                    $gel_prx_delivery_option_form_submit.prop('disabled',false);
                                }
                            });
                        }
                        // - Detect parent form and submit button

                        // Force carrier logo size
                        let $gel_prx_delivery_option_carrier_logo = $gel_prx_delivery_option_radio_wrapper.find('.carrier-logo img')
                        if ($gel_prx_delivery_option_carrier_logo.length) {
                            $gel_prx_delivery_option_carrier_logo.css('width', '50px');
                            $gel_prx_delivery_option_carrier_logo.css('height', '50px');
                        }
                        if ($('table.gelproximity-app__selection-pick-up-point-details-table').length <= 0) {
                            // $gel_prx_delivery_option_radio_wrapper.find('.carrier-price').text(gelprx_conf.defaultCarrierCostLabel);
                        } else {
                            $('.gelproximity-app__pick-button-text').text(gelprx_conf.buttonEditText);
                        }
                    }
                }
            }
        }
    }

    $(document).ready(function ($) {
        if (typeof gelprx_conf === 'undefined') {
            console_error('CONFIGURATION NOT FOUND');
        } else {
            console_log('CONFIGURATION IS FOUND');
            init();

            // Init configuration for The Checkout Plugin
            // https://addons.prestashop.com/it/express-checkout/42005-the-checkout.html
            if (gelprx_conf.checkoutPlugin === 'thecheckout') {
                isPageReloadNeeded = false;
                $(document).ajaxSuccess(function (event, xhr, settings) {
                    if (typeof settings !== "undefined" && typeof settings.url !== 'undefined' && settings.url.indexOf('selectDeliveryOption') !== -1) {
                        if (!isAjaxInit) {
                            init();
                        }
                        isAjaxInit = true;
                    }
                });
            }
        }
    });




    // $(window).load(function () {});
})();