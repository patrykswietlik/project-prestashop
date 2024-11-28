{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="box hidden-sm-down" id="extracharge_data_wrap">
    <input type="hidden" name="extracharge_text" id="extracharge_text" value="{$extracharge_text}">
    <input type="hidden" name="extracharge" id="extracharge" value="{$extracharge}">
    <input type="hidden" name="extrachargeFormatted" id="extrachargeFormatted" value="{$extrachargeFormatted}">
    <input type="hidden" name="currencySign" id="currencySign" value="{$currencySign}">
</div>
{*
see hookDisplayAdminOrderTabContent for reasons why it is here:
*}
<script>
    $(document).ready(function () {
        if($("#extracharge_data_wrap").length > 0){
            p24AddExtrachargeAdmin($('#extracharge_text').val(), $('#extrachargeFormatted').val(), $('#currencySign').val());
        }
    });

    function p24AddExtrachargeAdmin(extrachargeText, extrachargeReturnFormatted, currency)
    {
        if ('undefined' !== typeof currency && 0 !== $("#orderProductsPanel").find("#orderTotal").length) {
            $("#orderProductsPanel").find("#orderTotal").parent().before(
                '<div class="col-sm text-center">' +
                '<p class="text-muted mb-0"><strong>' + extrachargeText + '</strong></p>' +
                '<strong id="p24-extra-charge-amount">' + extrachargeReturnFormatted + ' ' + currency + '</strong>' +
                '</div>'
            );
        }
    }
</script>
