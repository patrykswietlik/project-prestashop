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
<span class="bm-payment__elm bm-wallet" data-bm-modal="true" data-open-payment="wallet"
      {if $gpayRedirect}data-payment-redirect="true"{/if}></span>
<section>

	<div class="bm-wallet-slideshow bm-slideshow bm-hide" data-slideshow="wallet">
        {foreach from=$img_wallets item=row name='img_wallets'}
			<div class="slide">
				<img src="{$row['gateway_logo_url']}"
				     alt="{$row['gateway_name']}">
			</div>
        {/foreach}
	</div>

	{if $googlePay}
	    {include file="module:bluepayment/views/templates/hook/_partials/gPay.tpl"
	    wallet_merchantInfo=$wallet_merchantInfo
	    gpay_moduleLinkCharge=$gpay_moduleLinkCharge}
    {/if}

    {if $applePay}
        {include file="module:bluepayment/views/templates/hook/_partials/applePay.tpl"
        wallet_merchantInfo=$wallet_merchantInfo}
	{/if}

	<script>
		var start_payment_translation = '{$start_payment_translation|escape:'javascript':'UTF-8'}';
		var order_subject_to_payment_obligation_translation = '{$order_subject_to_payment_obligation_translation|escape:'javascript':'UTF-8'}';
	</script>

</section>

<div class="bm-modal bm-fade bm-modal-wallet" tabindex="-1" aria-labelledby="modal-wallet" aria-hidden="true">
	<div class="bm-modal__dialog">
		<div class="bm-modal__content">
			<div class="bm-modal__header">
				<h5 class="bm-modal__title" id="modal-wallet">{l s='Choose bank' mod='bluepayment'}</h5>
				<button type="button" class="bm-modal__close"
				        data-modal-type="wallet"
				        data-dismiss="bm-modal"
				        aria-label="{l s='Close' mod='bluepayment'}">
					<img src="{$bm_dir}views/img/close.svg" width="20" alt="{l s='Close' mod='bluepayment'}"/>
				</button>
			</div>

			<div class="bm-modal__body">
				<form id="bluepayment-gateway" method="GET">
					<div id="blue_payway" class="bluepayment-gateways">
						<div class="bluepayment-gateways__wrap">
                            {foreach from=$gateway_wallets item=row name='gateway_wallets'}
								<div class="bluepayment-gateways__item" data-bm-gateway-id="{$row['gateway_id']}"
								     data-bm-back="wallet"
								     data-bm-wallet-name="{$row['gateway_name']}">
									<input type="radio" id="{$row['gateway_name']}"
									       class="bluepayment-gateways__radio"
									       name="bm-wallet-id" value="{$row['gateway_id']}"
									       required="required">
									<label class="bm-default" for="{$row['gateway_name']}">
										<img class="bluepayment-gateways__img" src="{$row['gateway_logo_url']}"
										     alt="{$row['gateway_name']}">
										<span class="bluepayment-gateways__name">{$row['gateway_name']}</span>
									</label>
								</div>
                            {/foreach}
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
