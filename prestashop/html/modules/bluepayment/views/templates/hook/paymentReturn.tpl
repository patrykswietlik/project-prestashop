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
<script type="text/javascript">
	ga('require', 'ecommerce');
	ga('ecommerce:addTransaction', {
		'id': '{$order_id|escape:'html':'UTF-8'}',
		'affiliation': '{$shop_name|escape:'html':'UTF-8'}',
		'revenue': '{$revenue|escape:'html':'UTF-8'}',
		'shipping': '{$shipping|escape:'html':'UTF-8'}',
		'tax': '{$tax|escape:'html':'UTF-8'}',
		'currency': '{$currency|escape:'html':'UTF-8'}'
	});

    {foreach from=$products item=product}
	ga('ecommerce:addItem', {
		'id': '{$order_id|escape:'html':'UTF-8'}',
		'name': '{$product->name|escape:'html':'UTF-8'}',
		'sku': '{$product->sku|escape:'html':'UTF-8'}',
		'category': '{$product->category|escape:'html':'UTF-8'}',
		'price': '{$product->price|escape:'html':'UTF-8'}',
		'quantity': '{$product->quantity|escape:'html':'UTF-8'}'
	});
    {/foreach}

	ga('ecommerce:send');
</script>
