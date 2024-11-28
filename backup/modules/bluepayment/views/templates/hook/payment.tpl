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
{if $selectPayWay}
	<span class="bm-payment__elm bm-transfer" data-bm-modal="true" data-open-payment="transfer"></span>
	<section>
		<span class="bm-small-info">
		    {l s='You will be redirected to the website of the bank of your choice.' mod='bluepayment'}
	    </span>
	<span class="bm-legals bm-small-info">
			{l s='The payment order is submitted to your bank via Autopay S.A. located in Sopot and will be executed according to the conditions set by your bank.' mod='bluepayment'}
        {l s='After selecting your bank, you will authorize the payment.' mod='bluepayment'}
	</span>
    {else}
	<span class="bm-payment__elm" data-open-payment="maingateway"></span>
	<section>
		<span class="bm-small-info">
	        {l s='You will be redirected to the website of our partner Autopay, where you can choose your fast and secure payment method.' mod='bluepayment'}
		</span>
        {/if}

		<script>
			var start_payment_translation = '{$start_payment_translation|escape:'javascript':'UTF-8'}';
			var order_subject_to_payment_obligation_translation = '{$order_subject_to_payment_obligation_translation|escape:'javascript':'UTF-8'}';
		</script>

        {if !$selectPayWay}
		<script>
			var start_payment_intro = '{$start_payment_intro|escape:'javascript':'UTF-8'}';
			document.addEventListener("DOMContentLoaded", function () {
				var base_element = $('input[name=bluepayment-hidden-psd2-regulation-id]').parent().parent().prev().prev();
				var payment_subtitle = $("<p></p>").text(start_payment_intro);
				base_element.find('label span').append(payment_subtitle);
			});
		</script>
	</section>
    {/if}


    {if $selectPayWay}
	<div class="bm-transfer-slideshow bm-slideshow bm-hide" data-slideshow="transfer">
        {foreach from=$img_transfers item=row name='img_transfers'}
			<div class="slide">
				<img src="{$row['gateway_logo_url']}" alt="{$row['gateway_name']}">
			</div>
        {/foreach}
	</div>
	<div class="bm-modal bm-fade bm-modal-transfer" tabindex="-1"
	     aria-labelledby="bm-modal-transfer" aria-hidden="true">
		<div class="bm-modal__dialog">
			<div class="bm-modal__content">
				<div class="bm-modal__header">
					<h5 class="bm-modal__title" id="bm-modal-transfer">
                        {l s='Choose bank' mod='bluepayment'}
					</h5>
					<button type="button" class="bm-modal__close"
					        data-dismiss="bm-modal"
					        data-modal-type="transfer"
					        aria-label="{l s='Close' mod='bluepayment'}">
						<img src="{$bm_dir}views/img/close.svg" width="20"
						     alt="{l s='Close' mod='bluepayment'}"/>
					</button>
				</div>

				<div class="bm-modal__body">
					<form id="bluepayment-gateway" method="GET">
						<div id="blue_payway" class="bluepayment-gateways">
							<div class="bluepayment-gateways__wrap">
                                {foreach from=$gateway_transfers item=row name='gateway_transfers'}
									<div class="bluepayment-gateways__item" data-bm-gateway-id="{$row['gateway_id']}">
										<label for="{$row['gateway_name']}">
											<input type="radio" id="{$row['gateway_name']}"
											       class="bluepayment-gateways__radio"
											       name="bm-transfer-id"
											       value="{$row['gateway_id']}" required="required">
											<img class="bluepayment-gateways__img" src="{$row['gateway_logo_url']}"
											     alt="{$row['bank_name']}">
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
	<div class="bm-clause-ajax" style="width:100%;">
		<div class="bm-small-info">
			<p class="text bm-legals bm-pis" style="display: inline"></p>
			<a href="#" class="bm-read-more">
                {l s='read more' mod='bluepayment'}
			</a>
		</div>
	</div>
	<div id="bm-end"></div>
</section>
{/if}

