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
<div class="bm-slideshow-wrapper">
<div class="bm-{$gateway_type}{$currency}-slideshow bm-slideshow" data-slideshow="{$gateway_type}{$currency}">
{foreach from=$gateway_slideshow item=$gateway}
	<div class="slide">
		<img src="{$gateway.gateway_logo_url}" alt="{$gateway.gateway_name}">
	</div>
{/foreach}
</div>
</div>
