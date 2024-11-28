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
<span class="bm-payment__elm" data-open-payment="blik"></span>
<section>
	<form id="bluepaymentForm" action="{$blik_moduleLink}" method="POST">
		<div class="bluepayment-loader"></div>
		<div class="bluepayment-loader-bg"></div>
		<div style="padding-bottom: 25px;">
			<div class="form-group">
				<label class="col-form-label bm-small-info" style="text-align: left">
                    {l s='Insert BlikCode number' mod='bluepayment'}
				</label>
				<input id="bluepayment-blikCode" class="form-control" type="text" name="bluepayment_input_blikCode"
				       required autocomplete="off" maxlength="6" style="width:200px;">

				<span class="bm-small-info" style="margin-top: 6px;">
					{l s='The code has 6 digits. You will find it in your banking application.' mod='bluepayment'}
				</span>

				<div id="wrongBlikCode" class="help-block">
                    {l s='The BLIK code must contain 6 characters' mod='bluepayment'}
				</div>
				<div id="bm-termofuse" class="help-block">
                    {l s='Please accept the [1]Transaction Regulations[/1]' tags=['<strong>'] mod='bluepayment'}
				</div>
				<div id="responseMessages" class="help-block"></div>
			</div>
			<button id="blikSubmit" type="submit" class="btn btn-primary center-block">
                {l s='Order with the obligation to pay' mod='bluepayment'}
			</button>
		</div>
	</form>
</section>
