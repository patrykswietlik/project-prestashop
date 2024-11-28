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
<div class="section-heading">
    {l s='Learn more about payments' mod='bluepayment'}
</div>

<ul class="bm-list-icon">
	<li class="bm-list-icon__item">
		<img class="bm-list-icon__icon" src="{$src_img|escape:'html':'UTF-8'}/analitics-connect.svg" alt="">
		<span class="bm-list-icon__content">{l s='Combine Google Analytics with the Autopay payment plugin and get more data about your customers at the payment stage.' mod='bluepayment'}</span>
	</li>
	<li class="bm-list-icon__item">
		<img class="bm-list-icon__icon" src="{$src_img|escape:'html':'UTF-8'}/analytical-benefits.svg" alt="">
		<span class="bm-list-icon__content">{l s='Thanks to the connection, you can find out, for example, what the conversion rate of individual payment methods is or what the sales funnel looks like at the payment stage.' mod='bluepayment'}<br /></span>
	</li>
</ul>

<div class="section-heading">
    {l s='Google Analytics Configuration' mod='bluepayment'}
</div>


<div class="modal fade" id="bm-helper-analitics-ga-id" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-analitics-ga-id" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>{l s='Where can I find my Google Account ID?' mod='bluepayment'}</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<div class="row">
					<div class="col-sm-12">
						<span class="bm-helper__header">{l s='Universal Analytics' mod='bluepayment'}</span>
						<ul class="bm-helper__list">
							<li>{l s='Go to "Administrator" in the lower left corner.' mod='bluepayment'}</li>
							<li>{l s='In the "Property" section, click "Tracking information".' mod='bluepayment'}</li>
							<li>{l s='Click "Tracking Code."' mod='bluepayment'}</li>
							<li>{l s='Your tracking ID is located in the upper right corner (e.g. UA-000000-2).' mod='bluepayment'}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="bm-helper-analitics-ga4-id" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-analitics-ga4-id" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>{l s='Where can I find the measurement ID?' mod='bluepayment'}</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<div class="row">
					<div class="col-sm-12">
						<span class="bm-helper__header">{l s='Google Analytics 4' mod='bluepayment'}</span>
						<ul class="bm-helper__list">
							<li>{l s='Go to "Administration" in the lower left corner.' mod='bluepayment'}</li>
							<li>{l s='In the "Services" section, click "Data Streams."' mod='bluepayment'}</li>
							<li>{l s='Click the name of the data stream.' mod='bluepayment'}</li>
							<li>{l s='Your measurement ID is located in the upper right corner (e.g., G-QCX4K9GSPC).' mod='bluepayment'}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="bm-helper-analitics-ga4-key" tabindex="-1" role="dialog"
     aria-labelledby="bm-helper-analitics-ga4-key" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2>{l s='How do I create an API secret?' mod='bluepayment'}</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="bm-helper modal-body">
				<div class="row">
					<div class="col-sm-6">
						<span class="bm-helper__header">{l s='Google Analytics 4' mod='bluepayment'}</span>
						<ul class="bm-helper__list">
							<li>{l s='Go to "Administration" in the lower left corner.' mod='bluepayment'}</li>
							<li>{l s='In the "Services" section, click "Data Streams."' mod='bluepayment'}</li>
							<li>{l s='Click the name of the data stream.' mod='bluepayment'}</li>
							<li>{l s='Then click "Create" in the "Measurement Protocol" section' mod='bluepayment'}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
