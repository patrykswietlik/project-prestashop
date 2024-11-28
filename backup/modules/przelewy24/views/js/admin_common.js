/*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*/

$(function() {
    let $targetInput = $('#p24-amount-to-refund ');
    let $lines = $('#p24-refund-form input.js-line')

    let recount = function ()
    {
        let newVal = 0;
        $lines.each(function(idx, elm) {
            let $elm = $(elm);
            let q = $elm.val();
            let p = $elm.data('unit-price');
            newVal += q * p;
            newVal = Math.round(newVal * 100) / 100;
        });

        newVal = Math.min(newVal, $targetInput.data('maximum'));
        $targetInput.val(newVal);
    }

    let reset = function ()
    {
        $lines.each(function(idx, elm) {
            let $elm = $(elm);
            $elm.val(0);
        });
    }

    $lines.on('change', recount);
    $targetInput.on('change', reset);
});

$(function () {
    let needReloadRefund = $('#p24-need-reload-refund').data('need');
    if (needReloadRefund) {
        location = location.protocol + '//' + location.host + location.pathname + location.search;
    }
});
