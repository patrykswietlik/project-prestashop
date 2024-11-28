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

{if isset($isInAmazonPayCheckout) && $isInAmazonPayCheckout}
	<div class="row">
		<div class="col-xs-12 text-right">
			<button type="button" name="changeAddressPayment" id="changeAddressPayment" class="button btn btn-default button-small"><span>{l s='Change' mod='amazonpay'}</span></button>
		</div>
	</div>
{/if}
<div class="row">
	<div class="col-xs-12">
		<p class="payment_module" id="amazonpay_payment_button">
			{if isset($isInAmazonPayCheckout) && $isInAmazonPayCheckout}
				<a href="{$link->getModuleLink('amazonpay', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Amazon Pay' mod='amazonpay'}" class="amazonPayPayNow">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.gif" alt="{l s='Pay with Amazon Pay' mod='amazonpay'}" width="86" />
					{l s='Pay with Amazon Pay' mod='amazonpay'} {if isset($amazon_card)}{$amazon_card|escape:'htmlall':'UTF-8'}{/if}
				</a>
			{else}
				<a href="JavaScript:amazonPayInitApb()" title="{l s='Pay with Amazon Pay' mod='amazonpay'}" class="amazonPayPayNow">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.gif" alt="{l s='Pay with Amazon Pay' mod='amazonpay'}" width="86" />
					{l s='Pay with Amazon Pay' mod='amazonpay'} {if isset($amazon_card)}{$amazon_card|escape:'htmlall':'UTF-8'}{/if}
				</a>
			{/if}
		</p>
	</div>
</div>
{if isset($isInAmazonPayCheckout) && $isInAmazonPayCheckout}
	{literal}
		<script>
			$(document).ready(function() {
				if ($(".payment_module").length > 0) {
					$(".payment_module").each(function() {
						if (!$(this).find("a").first().hasClass('amazonPayPayNow')) {
							$(this).hide();
						}
					});
					if ($("#stripe-card-payment").length > 0) {
						$("#stripe-card-payment").css('display', 'none');
					}
					if ($("#opc_new_account").length > 0 && isGuest && !isLogged) {
						$("#login_form,#new_account_form").hide();
						var changeBtn = $('<button type="button" name="changeAddressAmz" id="changeAddressAmz" class="button btn btn-default button-small"><span>{/literal}{l s='Change address' mod='amazonpay'}{literal}</span></button><br><br>');
						$("#new_account_form").before(changeBtn);
						amazon.Pay.bindChangeAction('#changeAddressAmz', {
							amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
							changeAction: 'changeAddress'
						});
					}
					amazon.Pay.bindChangeAction('#changeAddressPayment', {
						amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
						changeAction: 'changePayment'
					});
					{/literal}
					{if isset($isOnePageCheckoutPSInstalled) && $isOnePageCheckoutPSInstalled}
					{literal}
					$("#paymentMethodsTable tr").each(function(){
						if (!$(this).find(".payment_description label").first().html().includes("Amazon")) {
							$(this).hide();
						} else {
							var x = $(this).find(".payment_action input").prop("checked", true);
							if (typeof $.uniform !== 'undefined' && typeof $.uniform.update === 'function') {
								$.uniform.update(x);
							}
						}
					});
					{/literal}
					{/if}
					{literal}
				}
			});
		</script>
	{/literal}
{/if}
