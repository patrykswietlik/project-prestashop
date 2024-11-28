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
<span>
    {l s='Below you will find a list of events and their assigned actions, which will be visible in your Google Analytics dashboard after connection.' mod='bluepayment'}
</span>

<table class="bm-admin-events">
	<thead>
	<tr>
		<td>
            {l s='Event name' mod='bluepayment'}
		</td>
		<td>
            {l s='Description' mod='bluepayment'}
		</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
            {l s='View the product in the list' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered for each product that is listed and visible to the customer while browsing the site.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='Click on the product' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when the product link is clicked. This can be done on any list on the page.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='See product details' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when a user visits a specific product page. The event should be triggered when the page is displayed/loaded.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='Add product to cart' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when the user adds the product to the cart.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='Remove product from cart' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when the user removes a product from the shopping cart.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='Start the order process' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when the user goes to checkout.' mod='bluepayment'}
		</td>
	</tr>
	<tr>
		<td>
            {l s='Completion of the transaction' mod='bluepayment'}
		</td>
		<td>
            {l s='The event should be triggered when the transaction is successfully completed. It should be sent on the server side so that we know exactly that the transaction has been completed, even if the client has not returned to the thank you page.' mod='bluepayment'}
		</td>
	</tr>
	</tbody>
</table>
