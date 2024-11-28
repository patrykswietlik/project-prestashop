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
<div class="bm-info--small" style="margin-top: 26px">
	<img width="22" class="bm-info--small__icon img-fluid" src="{$bm_assets_images|escape:'html':'UTF-8'}/info.svg" alt="Info" />

	<p>{l s='Reorder the modules so that Autopay payments display first in your store.' mod='bluepayment'}
		<a href="#" data-toggle="modal" data-target="#bm-helper-position" style="cursor:pointer">
            {l s='How to change the order of modules?' mod='bluepayment'}
		</a>
	</p>

	<div class="modal fade" id="bm-helper-position" tabindex="-1" role="dialog"
	     aria-labelledby="bm-helper-position" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2>
                        {l s='Changing the order of the payment module' mod='bluepayment'}
					</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="bm-helper modal-body">
					<p>
						{l s='Reorder the modules so that Autopay payments display first in your store.' mod='bluepayment'}
					</p>
					<img class="bm-helper__image img-responsive" width="330"
					     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-position.png"
					     alt="{l s='How to change the order?' mod='bluepayment'}">

					<span class="bm-helper__header">{l s='How to change the order?' mod='bluepayment'}</span>
					<ul class="bm-helper__list">
						<li>{l s='Go to IMPROVEMENTS > Appearance > Items' mod='bluepayment'}</li>
						<li>{l s='Check the "Display unpositioned hooks" checkbox at the top of the page' mod='bluepayment'}</li>
						<li>{l s='Search for "paymentOptions"' mod='bluepayment'}</li>
						<li>{l s='Move Autopay payment module to the top (changes will be saved automatically)' mod='bluepayment'}</li>
					</ul>

					<img class="bm-helper__image img-responsive" width="860"
					     src="{$bm_assets_images|escape:'html':'UTF-8'}/helpers/helper-position2.png"
					     alt="{l s='How to change the order?' mod='bluepayment'}">
				</div>
			</div>
		</div>
	</div>

</div>