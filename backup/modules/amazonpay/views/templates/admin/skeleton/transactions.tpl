{*
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
*}

{if $transactions}
	<a name="amazonpay_transactions"></a>
	<div class="panel {if $isHigher176}card{/if}" id="amazonPayTransactions">
		<div class="panel-heading {if $isHigher176}card-header{/if}">
			<i class="icon-money"></i>
			{l s='Amazon Pay - Transactions' mod='amazonpay'}
		</div>

    <div class="{if $isHigher176}card-body{/if}">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><span class="title_box">{l s='Type' mod='amazonpay'}</span></th>
              <th><span class="title_box">{l s='ID' mod='amazonpay'}</span></th>
              <th><span class="title_box">{l s='Amount' mod='amazonpay'}</span></th>
              <th><span class="title_box">{l s='Date' mod='amazonpay'}</span></th>
            </tr>
          </thead>
          <tbody>
            {foreach item=tx from=$transactions}
              <tr>
                <td>{$tx.transaction_type|replace:'_pending':' (pending)'|replace:'_declined':' (declined)'}</td>
                <td>{$tx.amazon_transaction}</td>
                <td>{displayPrice price=$tx.transaction_amount currency=$OrderObj->id_currency}</td>
                <td>{dateFormat date=$tx.date_add full=true}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
        <br/>
        <form class="form-horizontal well" id="amazonPayTransactionsActions" method="post" action="{$amazon_form_action}" onsubmit="return confirm('{l s='Should the action really be executed?' mod='amazonpay'}')">
          {if $amazon_actions.refund_allowed}
            <div class="form-element">
              <input type="text" class="form-control fixed-width-md pull-left" name="amazon_refund_amount" placeholder="{l s='max. ' mod='amazonpay'} {$amazon_actions.max_refund}">
              <button class="btn btn-default" type="submit" name="amazonpay_action" value="amazonpay_refund">
                {l s='Refund' mod='amazonpay'}
              </button>
            </div>
          {/if}
          {if $amazon_actions.charge_allowed}
            <div class="form-element">
              <button class="btn btn-default" type="submit" name="amazonpay_action" value="amazonpay_charge">
                {l s='Charge' mod='amazonpay'}
              </button>
            </div>
          {/if}
          {if $amazon_actions.cancel_allowed}
            <div class="form-element">
              <input type="text" class="form-control fixed-width-md pull-left" name="amazon_cancel_reason" placeholder="{l s='Cancel reason' mod='amazonpay'}">
              <button class="btn btn-default" type="submit" name="amazonpay_action" value="amazonpay_cancel">
                {l s='Cancel Charge' mod='amazonpay'}
              </button>
            </div>
          {/if}
          {*if $amazon_actions.close_allowed}
            <div class="form-element">
              <input type="text" class="form-control fixed-width-md pull-left" name="amazon_close_reason" placeholder="{l s='Close reason' mod='amazonpay'}">
              <button class="btn btn-default" type="submit" name="amazonpay_action" value="amazonpay_close">
                {l s='Close Charge permission' mod='amazonpay'}
              </button>
            </div>
          {/if*}
        </form>
      </div>
    </div>
	</div>
{/if}
