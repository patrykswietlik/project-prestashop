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
<div class="panel paymentList">
	<div class="panel-heading">
        {l s='Payment list' mod='bluepayment'}
	</div>
	<div class="row">
        {foreach $list as $l}
            {$l}
        {/foreach}
	</div>
    {if isset($transfer_payments)}
        {foreach $transfer_payments as $key => $currency}
			<div class="modal fade" id="Przelew_internetowy_{$key}" tabindex="-1" role="dialog"
			     aria-labelledby="Przelew_internetowy_{$key}" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h2>
                                {l s='List of supported banks' mod='bluepayment'}
							</h2>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">

							<div id="blue_payway" class="bluepayment-gateways">
								<div class="bluepayment-gateways__wrap">
                                    {foreach $currency as $card}
										<div class="bluepayment-gateways__item">
											<label for="{$card['gateway_name']}">
												<img class="bluepayment-gateways__img"
												     src="{$card['gateway_logo_url']}"
												     alt="{$card['gateway_name']}">
												<span class="bluepayment-gateways__name">
													{$card['gateway_name']}
												</span>
											</label>
										</div>
                                    {/foreach}
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
        {/foreach}
    {/if}


    {if isset($wallets) and is_array($wallets) }
        {foreach $wallets as $key => $currency}
			<div class="modal fade" id="Wirtualny_portfel_{$key}" tabindex="-1" role="dialog"
			     aria-labelledby="Wirtualny_portfel_{$key}" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h2>
                                {l s='List of supported wallets' mod='bluepayment'}
							</h2>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div id="blue_payway" class="bluepayment-gateways">
								<div class="bluepayment-gateways__wrap">
                                    {foreach $currency as $card}
										<div class="bluepayment-gateways__item">
											<label for="{$card['gateway_name']}">
												<img class="bluepayment-gateways__img"
												     src="{$card['gateway_logo_url']}"
												     alt="{$card['gateway_name']}">
												<span class="bluepayment-gateways__name">
                                                    {$card['gateway_name']}
                                                </span>
											</label>
										</div>
                                    {/foreach}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
        {/foreach}
    {/if}

    {include file="./form/notification-position.tpl"}

</div>


<div class="modal fade" id="bm-helper-main-name" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-main-name" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>
                    {l s='Name of the payment module in the store' mod='bluepayment'}
				</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<p>
                    {l s='You can set a different payment module name when methods are visible or hidden in the store.' mod='bluepayment'}
				</p>
				<div class="row">
					<div class="col-sm-6">
						<p>
								<span class="bm-helper__header">
									{l s='Name for visible payment methods' mod='bluepayment'}
								</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-name.png"
						     alt="{l s='How to change the order?' mod='bluepayment'}">
					</div>

					<div class="col-sm-6">
						<p>
								<span class="bm-helper__header">
									{l s='Name for hidden payment methods' mod='bluepayment'}
								</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-name2.png"
						     alt="{l s='How to change the order?' mod='bluepayment'}">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="bm-helper-visibility" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-visibility" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>
                    {l s='Show payment methods in store' mod='bluepayment'}
				</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<p>
                    {l s='When the option is enabled, the customer will see the available payment methods' mod='bluepayment'}
                    {l s='(e.g. BLIK, online transfer, etc.) already on the store page.' mod='bluepayment'}
                    {l s='This makes it easier and faster for them to choose the most convenient one.' mod='bluepayment'}
				</p>
				<div class="row">
					<div class="col-sm-6">
						<p>
								<span class="bm-helper__header">
									{l s='Visible payment methods' mod='bluepayment'}
								</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-payment.png"
						     alt="{l s='How to change the order?' mod='bluepayment'}">
					</div>

					<div class="col-sm-6">
						<p>
								<span class="bm-helper__header">
									{l s='Hidden payment methods' mod='bluepayment'}
								</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-payment2.png"
						     alt="{l s='How to change the order?' mod='bluepayment'}">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="bm-helper-gpay" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-gpay" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>
                    {l s='Google Pay' mod='bluepayment'}
				</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<p>
                    {l s='When the option is enabled, the customer can use Google Pay directly on the store page.' mod='bluepayment'}
                    {l s='This makes the payment process faster and easier.' mod='bluepayment'}
				</p>
				<div class="row">
					<div class="col-sm-6">
						<p>
							<span class="bm-helper__header">
								{l s='Google Pay on the store page' mod='bluepayment'}
							</span>
							<span class="bm-helper__subheader">
								{l s='(recommended)' mod='bluepayment'}
							</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-gpay.png"
						     alt="{l s='How to change the order?' mod='bluepayment'}">
					</div>
					<div class="col-sm-6"></div>
				</div>

				<div class="row">

					<div class="col-sm-12">
						<p>
							<span class="bm-helper__header">
								{l s='Google Pay on a dedicated page outside the store' mod='bluepayment'}
							</span>
							<span class="bm-helper__subheader">
								{l s='(recommended if you use a one page checkout module)' mod='bluepayment'}
							</span>
						</p>

						<div class="bm-modal-flex">
							<img class="bm-helper__image img-responsive" width="330"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-gpay2.png"
							     alt="{l s='Google Pay' mod='bluepayment'}">
							<img class="bm-helper__image img-responsive" width="32" style="margin: 10px;"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-gpay3.png"
							     alt="{l s='Google Pay' mod='bluepayment'}">
							<img class="bm-helper__image img-responsive" width="330"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-gpay4.png"
							     alt="{l s='Google Pay' mod='bluepayment'}">
						</div>

					</div>

				</div>

			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="bm-helper-blik" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-blik" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>
                    {l s='Entering BLIK code' mod='bluepayment'}
				</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<p>
                    {l s='When the option is enabled, the customer can enter the BLIK code already on the store page.' mod='bluepayment'}
                    {l s='This makes the payment process faster and easier.' mod='bluepayment'}
				</p>
				<div class="row">
					<div class="col-sm-6">
						<p>
							<span class="bm-helper__header">
								{l s='BLIK code on the store\'s website' mod='bluepayment'}
							</span>
							<span class="bm-helper__subheader">
								{l s='(recommended)' mod='bluepayment'}
							</span>
						</p>
						<img class="bm-helper__image img-responsive" width="330"
						     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-blik.png"
						     alt="{l s='Entering BLIK code' mod='bluepayment'}">
					</div>
					<div class="col-sm-6">
					</div>
				</div>

				<div class="row">

					<div class="col-sm-12">
						<p>
							<span class="bm-helper__header">
								{l s='BLIK code on the page outside the store' mod='bluepayment'}
							</span>
							<span class="bm-helper__subheader">
								{l s='(recommended if you use a module like "One page checkout")' mod='bluepayment'}
							</span>
						</p>
						<div class="bm-modal-flex">
							<img class="bm-helper__image img-responsive" width="330"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-blik2.png"
							     alt="{l s='Entering BLIK code' mod='bluepayment'}">
							<img class="bm-helper__image img-responsive" width="32" style="margin: 10px;"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-blik3.png"
							     alt="{l s='Entering BLIK code' mod='bluepayment'}">
							<img class="bm-helper__image img-responsive" width="330"
							     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-blik4.png"
							     alt="{l s='Entering BLIK code' mod='bluepayment'}">
						</div>
					</div>


				</div>

			</div>
		</div>
	</div>
</div>


