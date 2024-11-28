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

$(function () {
    $('select[data-search]').select2({closeOnSelect: true})

    $('.validate-time').on('input', function (e) {
        validateTimeInput(e.target);
    })

    // fix DatePicker
    let $pickup_date_input = $('#MBESHIPPING_PICKUP_DATE')
    if ($pickup_date_input.length) {
        $.datepicker.setDefaults({
            ...$.datepicker._defaults,
            minDate: +1
        });
    }
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
