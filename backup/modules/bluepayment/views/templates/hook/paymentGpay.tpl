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
<span class="bm-payment__elm"></span>

{if $gpayRedirect}
	<p>{l s='You will be redirected to the website of our partner Autopay, where you can choose your fast and secure payment method.' mod='bluepayment'}</p>
{else}
	<section>
		<div style="padding-bottom: 25px">
			<div class="bluepayment-loader"></div>
			<div class="bluepayment-loader-bg"></div>
			<div id="gpay-button"></div>
			<span id="gpay-url" style="display:none;"
			      data-merchant-info-address="{$wallet_merchantInfo}"
			      data-charge-address="{$gpay_moduleLinkCharge}"></span>
			<div id="bm-termofuse" class="help-block js-g-pay-terms-of-use" style="display:none; color: red;">
                {l s='Please accept the [1]Transaction Regulations[/1]' tags=['<strong>'] mod='bluepayment'}
			</div>
			<div id="responseGPayMessages" class="help-block"></div>
		</div>
	</section>
{/if}
