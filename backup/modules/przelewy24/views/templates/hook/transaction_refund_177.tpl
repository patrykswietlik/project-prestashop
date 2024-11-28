{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="card przelewy-24">

    <div class="card-header">
        <h3 class="card-header-title">{l s='Refunds to Przelewy24' mod='przelewy24'}</h3>
    </div>

    <div class="card-body">

        {if '' !== $refundError}
            <div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>
                {foreach from=$refundError item=singleError}
                    <p>{l s=$singleError mod='przelewy24'}</p>
                {/foreach}
            </div>
        {/if}
            {if $amount > 0}
                {assign var="amountToRefund" value=$amount/100}
                    <p>
                        {l s='Here you can send a refund to the customer. The amount of the refund may not exceed the value of the transaction and the amount of funds available in your account.' mod='przelewy24'}
                    </p>
                    <p>{l s='Amount to refund' mod='przelewy24'}: {$amountToRefund} {$sign}</p>


                    <form class="form-horizontal hidden-print refundAmount" id="p24-refund-form" method="post">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table">
                                    <tbody>
                                    {foreach $productsPossibleToRefund as $i => $oneProduct}
                                        <tr>
                                            <td>{$oneProduct.name}</td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number"
                                                           name="refundProductQuantity[{$oneProduct.productId}]"
                                                           class="form-control js-line"
                                                           min="0"
                                                           max="{$oneProduct.quantity}"
                                                           value="{$oneProduct.quantity}"
                                                           data-unit-price="{$oneProduct.price}"
                                                    >
                                                    <div class="input-group-append">
                                                        <div class="input-group-text">
                                                            / {$oneProduct.quantity}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{$oneProduct.priceFormatted}</td>
                                            <td><!-- empty --></td>
                                        </tr>
                                    {/foreach}
                                    </tbody>

                                    <tfoot>
                                    <tr>
                                        <th>
                                            <label for="p24-amount-to-refund">{l s='Amount' mod='przelewy24'}</label>
                                        </th>
                                        <td colspan="2">
                                            <input class="form-control" id="p24-amount-to-refund" type="number"
                                                   data-maximum="{$amountToRefund}"
                                                   name="amountToRefund" value="{$amountToRefund}" step="0.01">
                                        </td>
                                        <td>
                                            <input class="btn btn-primary pull-right" type="submit" name="submitRefund"
                                                   value="{l s='Send' mod='przelewy24'}">
                                            <input type="hidden" id="refundAmountText"
                                                   value="{l s='This will generate outgoing transfer. Can you confirm the operation?' mod='przelewy24'}">
                                            <input type="hidden" name="refundMaximum" value="{$amountToRefund}">
                                            <input type="hidden" name="refundToken" value="{$refundToken}">
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </form>
            {else}
                <p>{l s='The payment has already been fully refunded - no funds to make further returns.' mod='przelewy24'}</p>
            {/if}

            {if $refunds}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>
                            <span class="title_box">
                                {l s='Amount refunded' mod='przelewy24'}
                            </span>
                            </th>
                            <th>
                            <span class="title_box">
                                {l s='Date of refund' mod='przelewy24'}
                            </span>
                            </th>
                            <th>
                            <span class="title_box">
                                Status
                            </span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$refunds item=refund}
                            <tr>
                                <td>
                                    {$refund['amount_refunded']/100} {$sign}
                                </td>
                                <td>
                                    {$refund['created']}
                                </td>
                                <td>
                                    {l s=$refund['status'] mod='przelewy24'}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {/if}

        <span id="p24-need-reload-refund" data-need="{$p24NeedReloadRefund}"></span>
    </div>

</div>
