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

/* + Input time validation */
$(function () {
    let $validate_time_inputs = $('.validate-time');
    $validate_time_inputs.on('input', function (e) {
        validateTimeInput(e.target);
    })
});

let timeoutId;

function validateTimeInput(input) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(function() {
        let value = input.value;
        input.value = formatTime(value);
    }, 500);
}

function formatTime(value) {
    let formattedValue = value.replace(/\D/g, "").slice(0, 4);
    let hours = parseInt(formattedValue.slice(0, 2));
    let minutes = parseInt(formattedValue.slice(2));

    if (isNaN(hours)) {
        hours = 0;
    } else if (hours > 23) {
        hours = 23;
    }

    if (isNaN(minutes)) {
        minutes = 0;
    } else if (minutes > 59) {
        minutes = 59;
    }

    return String(hours).padStart(2, "0") + ":" + String(minutes).padStart(2, "0");
}
/* - Input time validation */

function backToWelcomePage() {
    showTabContent($('<a>', { id: 'welcome' }));
    $("body")[0].scrollIntoView({block: 'start', behavior: 'smooth'});
}

function loginMbeUser() {
    showTabContent($('<a>', { id: 'initial_login' }));
}

function registerMbeUser() {
    showTabContent($('<a>', { id: 'registration' }));
}

function showTabContent(input) {
    $('#mbeshipping_conf_content').children('div.tab-content').children('div.tab-pane').each(function (i, e) {
        $(e).removeClass('active');
    });

    $('div.tab-pane#' + $(input).attr('id')).addClass('active');
}

function showConfigContent(input) {
    if (typeof is_direct_channel_user !== 'undefined' && is_direct_channel_user === true) {
        let configurations = $('form[id="module_form_1"] > div[id^="fieldset_"].panel').map(function () {
            $(this).hide()
            return $(this).attr('id')
        }).get()

        if (tab2_conf_mode === '3') $(`#${configurations[2]}`).show()

        return
    }

    let configurations = $('form[id="module_form_1"] > div[id^="fieldset_"].panel:not(:first)').map(function () {
        $(this).hide()
        return $(this).attr('id')
    }).get()

    switch (input) {
        case '1':
            if (tab2_conf_mode === '1')
                $(`#${configurations[0]}`).show()
            break
        case '2':
            if (tab2_conf_mode === '2')
                $(`#${configurations[1]}`).show()
            break
        case '3':
            if (tab2_conf_mode === '3')
                $(`#${configurations[2]}`).show()
            break
    }
}

function showCouriersServicesOptions(options) {
    $('select[id^="mbe_custom_mapping_"]').prop('disabled', true)

    let curr_options_values = [];
    $('select[id^="mbe_custom_mapping_"] > option').each(function (i, e) {
        curr_options_values.push(e.value)
    })

    if (options.length) {
        if (!curr_options_values.length) {
            $('select[id^="mbe_custom_mapping_"]').append(new Option(' ', ' '))
        }

        options.forEach(function (opt) {
            if (!curr_options_values.includes(opt.value)) {
                $('select[id^="mbe_custom_mapping_"]').append(opt)
            }
        })

        let options_values = options.map(function (opt) {
            return opt.value
        })

        curr_options_values.forEach(function (value) {
            if (value.trim() !== '' && !options_values.includes(value))
                $(`select[id^="mbe_custom_mapping_"] > option[value="${value}"]`).remove()
        })

        $('select[id^="mbe_custom_mapping_"]').prop('disabled', false)
        $('select[id^="tax_rule_carrier_"]').prop('disabled', false)
    } else {
        $('select[id^="mbe_custom_mapping_"] > option').each(function (i, e) {
            $(e).remove()
        })
    }
}

function showServicesCustomDescription(options) {
    $('.form-group:has(input[id^="mbe_custom_label_"])').each(function (i, e) {
        $(e).hide()
    })

    $('.form-group:has(select[id^="mbe_tax_rule_"])').each(function (i, e) {
        $(e).hide()
    })

    if (options.length) {
        options.forEach(function (e) {
            $(`.form-group:has(input[id="mbe_custom_label_${e.id.toLowerCase()}"])`).show()
            $(`.form-group:has(select[id="mbe_tax_rule_${e.id.toLowerCase()}"])`).show()
        })

        checkAndToggleFields(options)
    }
}

function checkAndToggleFields(options){

    var ids = ["NMDP", "GPP"]

    if($(`.form-group:has(select[id="mbe_tax_rule_11"])`).length &&
        $(`.form-group:has(select[id="mbe_tax_rule_11"])`).length){
        if($(`.form-group:has(select[id="mbe_tax_rule_12"])`).length &&
            $(`.form-group:has(select[id="mbe_tax_rule_12"])`).length){
            //numeric
            ids = ["11", "12"]
        }
    }

    const counts = ids.reduce((obj, id) => {
        obj[id] = 0
        return obj
    }, {})

    options.forEach(option => {
        if (ids.includes(option.id)) {
            counts[option.id]++
        }
    });

    if(counts.GPP > 0 && counts.NMDP > 0){
        // alfanumeric
        showMergedFieldDP()

        // hide GPDP and MPED
        $(`.form-group:has(input[id="mbe_custom_label_nmdp"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_nmdp"])`).hide()
        $(`.form-group:has(input[id="mbe_custom_label_gpp"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_gpp"])`).hide()

    } else if (counts[11] > 0 && counts[12] > 0){
        // numeric
        showMergedFieldDP()

        // hide 11 and 12
        $(`.form-group:has(input[id="mbe_custom_label_12"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_12"])`).hide()
        $(`.form-group:has(input[id="mbe_custom_label_11"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_11"])`).hide()
    } else {
        // hide Delivery Point field
        $(`.form-group:has(input[id="mbe_custom_label_nmdp-gpp"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_nmdp-gpp"])`).hide()
        $(`.form-group:has(input[id="mbe_custom_label_13"])`).hide()
        $(`.form-group:has(select[id="mbe_tax_rule_13"])`).hide()
    }
}

function showMergedFieldDP(){
    // mostrare campo merge delivery point
    $(`.form-group:has(input[id="mbe_custom_label_nmdp-gpp"])`).show()
    $(`.form-group:has(select[id="mbe_tax_rule_nmdp-gpp"])`).show()
    $(`.form-group:has(input[id="mbe_custom_label_13"])`).show()
    $(`.form-group:has(select[id="mbe_tax_rule_13"])`).show()
}

function getSelectedServicesOptions() {
    let options = [];
    $('#mbe_allowed_shipment_services_2').children('option:selected').each(function (i, e) {
        options.push(new Option(e.text, e.value))
    })
    return options
}

function showCheckResult(action, result) {
    switch (action) {
        case 'CheckVersion':
            result ? $('#mbe_check_version').html('<i class="icon-ok-circle"></i>') :
                $('#mbe_check_version').html('<i class="icon-remove-circle"></i><p class="help-block">' + mbe_ajax_check_version_error + '</p>')
            break
        case 'CheckHooks':
            result ? $('#mbe_check_hooks').html('<i class="icon-ok-circle"></i>') :
                $('#mbe_check_hooks').html('<i class="icon-remove-circle"></i><p class="help-block">' + mbe_ajax_check_generic_error + '</p>')
            break
        case 'CheckDb':
            result ? $('#mbe_check_db').html('<i class="icon-ok-circle"></i>') :
                $('#mbe_check_db').html('<i class="icon-remove-circle"></i><p class="help-block">' + mbe_ajax_check_generic_error + '</p>')
            break
        case 'CheckTabs':
            result ? $('#mbe_check_tabs').html('<i class="icon-ok-circle"></i>') :
                $('#mbe_check_tabs').html('<i class="icon-remove-circle"></i><p class="help-block">' + mbe_ajax_check_generic_error + '</p>')
            break
        case 'CheckOverrides':
            result ? $('#mbe_check_overrides').html('<i class="icon-ok-circle"></i>') :
                $('#mbe_check_overrides').html('<i class="icon-remove-circle"></i><p class="help-block">' + mbe_ajax_check_generic_error + '</p>')
            break
    }
}

function ajaxCall(action) {
    return $.ajax({
        url: mbe_ajax_check_controller_url,
        dataType: 'json',
        type: 'POST',
        data: {
            ajax: true,
            action: action,
        },
        cache: false,
        success: function (res) {
            showCheckResult(action, res.result)
        },
    });
}

function runCheckup() {
    $('#mbe_checkup_btn').prop('disabled', true)
    $('#mbe_checkup_btn').addClass('spinner')

    Promise.all(
        [
            ajaxCall('CheckVersion'),
            ajaxCall('CheckHooks'),
            ajaxCall('CheckDb'),
            ajaxCall('CheckTabs'),
            ajaxCall('CheckOverrides'),
        ]
    ).then(() => {
        $('#mbe_checkup_btn').prop('disabled', false)
        $('#mbe_checkup_btn').removeClass('spinner')
    })
}

$(function () {
    if (active_tab !== null) {
        $('#mbeshipping_conf_content').children('div.tab-content').children('div.tab-pane').each(function (i, e) {
            $(e).removeClass('active')
        });
        $('#mbeshipping_conf_content [data-toggle="tab"]').each(function (i, e) {
            $(e).removeClass('active')
        });
        $('div.tab-pane#' + active_tab).addClass('active')
        $('#mbeshipping_conf_content [data-toggle="tab"]#' + active_tab).addClass('active')
    }

    if ($('#handling_fee_rounding').val() === '1' || $('#handling_fee_rounding').val() === '2')
        $('.form-group:has(#handling_fee_rounding_amount)').hide()

    $('#handling_fee_rounding').on('change', function () {
        if ($(this).val() === '1' || $(this).val() === '2')
            $('.form-group:has(#handling_fee_rounding_amount)').hide()
        else
            $('.form-group:has(#handling_fee_rounding_amount)').show()
    })

    $('#mbe_login_btn').on('click', function () {
        $(this).css('pointer-events', 'none')
        $(this).addClass('disabled')
        $(this).after('<p class="help-block">' + text_signing_in + '</p>')
    })

    showConfigContent(0)

    if (typeof is_direct_channel_user === 'undefined' || !is_direct_channel_user) {
        $('#mbe_change_conf_mode').hide()

        $('#mbe_couriers_services_mode').on('change', function () {
            showConfigContent($(this).val())

            if (tab2_conf_mode !== $(this).val() && $(this).val() !== 'default') {
                $('#mbe_change_conf_mode').show()
            } else {
                $('#mbe_change_conf_mode').hide()
            }
        })

        if (tab2_conf_mode !== null) {
            switch (tab2_conf_mode) {
                case '1':
                    $('#mbe_couriers_services_mode').val('1').change()
                    break
                case '2':
                    $('#mbe_couriers_services_mode').val('2').change()
                    break
                case '3':
                    $('#mbe_couriers_services_mode').val('3').change()
                    break
                default:
                    $('#mbe_couriers_services_mode').val('default').change()
            }
        }
    }

    showCouriersServicesOptions(getSelectedServicesOptions())

    $('select[multiple="multiple"]').select2({closeOnSelect: false}).on('load change', function () {
        let id = $(this).attr('id')
        if (id === 'mbe_allowed_shipment_services_2')
            showCouriersServicesOptions(getSelectedServicesOptions())
        else if (id === 'mbe_allowed_shipment_services_3') {
            showServicesCustomDescription($(this).select2('data'))
        }
    })

    showServicesCustomDescription($('select#mbe_allowed_shipment_services_3').select2('data'))
});

;(function () {
    function isValidUrl(url, isCheckEmpty = false) {
        if (url === '' || url === null || typeof url === 'undefined') {
            if (isCheckEmpty) {
                return false;
            } else {
                return true;
            }
        }
        let r = new RegExp(/^(ftp|http|https):\/\/[^ "]+$/);
        return r.test(url);
    }

    function checkFieldUrl(isCheckEmpty = false) {
        if (!isValidUrl($('#url').val(), isCheckEmpty)) {
            $('#mbe-error-message-field-url').show();
            if ($('#module_form_submit_btn').length) {
                $('#module_form_submit_btn').prop('disabled', true);
                $('#module_form_submit_btn').attr('disabled', true);
            }
        } else {
            $('#mbe-error-message-field-url').hide();
            if ($('#module_form_submit_btn').length) {
                $('#module_form_submit_btn').prop('disabled', false);
                $('#module_form_submit_btn').attr('disabled', false);
            }
        }
    }

    function preSelectCountry(mbe_user) {
        if (mbe_user === '' || mbe_user === null || typeof mbe_user === 'undefined') {
            return false;
        }

        if(mbe_user.indexOf('@') > -1) {
            let mbe_user_arr = mbe_user.split('@');
            if(mbe_user_arr[1] === '' || mbe_user_arr[1] === null || typeof mbe_user_arr[1] === 'undefined') {
                return false;
            }

            switch(mbe_user_arr[1]) {
                case 'Italy':
                    $('#mbecountry').val('IT').change();
                    break
                case 'Spain':
                    $('#mbecountry').val('ES').change();
                    break
                case 'Germany':
                    $('#mbecountry').val('DE').change();
                    break
                case 'France':
                    $('#mbecountry').val('FR').change();
                    break
                case 'Poland':
                    $('#mbecountry').val('PL').change();
                    break
                case 'Austria':
                    $('#mbecountry').val('AT').change();
                    break
                case 'Croatia':
                    $('#mbecountry').val('HR').change();
                    break
                case 'United Kingdom':
                    $('#mbecountry').val('UK').change();
                    break
                default:
                    $('#mbecountry').val('IT').change()
            }
        }
    }

    $(document).ready(function ($) {
        if ($('#general_settings').length && $('#general_settings').is(':visible')) {
            if ($('#url').length) {
                const htmlError = '<p style="display:none;" id="mbe-error-message-field-url">' + url_field_not_valid + '</p>';
                $('#url').after($(htmlError));
                $('#mbe-error-message-field-url').css('color', 'red');
                checkFieldUrl(false);
                $('#url').on('blur', function (e) {
                    checkFieldUrl(true);
                });
            }
        }

        $('#mbe_user').on('focusout', function (e) {
            preSelectCountry($('#mbe_user').val());
        });
    });

    // $(window).load(function () {});
})();
