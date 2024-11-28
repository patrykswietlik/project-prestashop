{*
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
*}
<div class="row">
    <div class="col-lg-12">
        <div id="bmOrders" class="panel card">
            <div class="panel-heading card-header">
                <i class="icon-money"></i>
                {l s='Bluemedia Refunds' mod='bluepayment'}
            </div>

            {$BM_CANCEL_ORDER_MESSAGE}

            <div class="card-body">


                {if $SHOW_REFUND}
                    <div class="row">
                        <div class="col-sm-6">
                            <form action="" method="post"
                                  onsubmit="return confirm('{l s='Do you really want to submit the refund request?' mod='bluepayment'}');">
                                <input type="hidden" name="go-to-refund-bm" value="1">
                                <table class="table-sm table-condensed">
                                    <tr>
                                        <td>
                                            <select id="bm_refund_type" name="bm_refund_type" class="custom-select">
                                                <option value="full"{if $REFUND_TYPE eq "full"} selected="selected"{/if}>{l s='Full refund' mod='bluepayment'}</option>
                                                <option value="partial"{if $REFUND_TYPE eq "partial"} selected="selected"{/if}>{l s='Partial refund' mod='bluepayment'}</option>
                                            </select>
                                        </td>
                                        <td>
                                            {l s='amount' mod='bluepayment'}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="bm_refund_amount"
                                                   name="bm_refund_amount"
                                                   value="{$REFUND_AMOUNT|escape:'htmlall':'UTF-8'}"/>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm">
                                                {l s='Perform refund' mod='bluepayment'}
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>

                {if $REFUND_ERRORS|count}
                    <div role="alert" class="alert alert-danger">
                        <p class="alert-text">
                            {foreach from = $REFUND_ERRORS item = error}
                        <div>{$error}</div>
                        {/foreach}
                        </p>
                    </div>
                {/if}
                {if $REFUND_SUCCESS|count}
                    <div role="alert" class="alert alert-success">
                        <p class="alert-text">
                            {foreach from = $REFUND_SUCCESS item = success}
                        <div>{$success}</div>
                        {/foreach}
                        </p>
                    </div>
                {/if}
                    <script>
                        {literal}
                        $(document).ready(function () {
                            var refund_type_select = $('#bm_refund_type');
                            var set_type = function (type) {
                                if ('full' === type) {
                                    $('#bm_refund_amount').attr('readonly', true).val('{/literal}{$REFUND_FULL_AMOUNT|escape:'htmlall':'UTF-8'}{literal}');
                                } else {
                                    $('#bm_refund_amount').attr('readonly', false);
                                }
                            };
                            set_type(refund_type_select.val());
                            refund_type_select.on('change', function () {
                                set_type(refund_type_select.val());
                            });
                        });
                        {/literal}
                    </script>
                {/if}
            </div>
        </div>
    </div>
</div>
