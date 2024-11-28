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

<div class="panel">

	<div class="alert alert-warning" role="alert">
		{l s='Please note: if you use server side caching (for example in combination with nginx), remember to empty it after saving the configuration.' mod='amazonpay'}
	</div>

	{$configform}
</div>
<div class="panel">

	<p>
		<strong>{l s='Please use this URL for your IPN configuration:' mod='amazonpay'}</strong><br>
		{$ipn_url}
	</p>

	<p>
		<strong>{l s='Please use this URL to automatically refresh transactions:' mod='amazonpay'}</strong><br>
		{$cron_url}
	</p>

	<p>
		<strong>{l s='You can add the Amazon Pay button anywhere in your template with adding this code:' mod='amazonpay'}</strong>
	</p>

	<code>
		{literal}
			&#x3C;div class=&quot;amazonPayButton&quot;
			data-placement=&quot;Other&quot;
			data-rendered=&quot;0&quot;
			data-color=&quot;gold&quot;&#x3E;&#x3C;/div&#x3E;
		{/literal}
	</code>
	<br/>
	<code>
		{literal}
			&#x3C;div class=&quot;amazonPayButton amazonLogin&quot;
			data-placement=&quot;Other&quot;
			data-rendered=&quot;0&quot;
			data-color=&quot;gold&quot;&#x3E;&#x3C;/div&#x3E;
		{/literal}
	</code>

</div>

<div id="amazonpay_hidden" style="display:none;">
	<textarea name="AMAZONPAY_PUBLIC_KEY" id="AMAZONPAY_PUBLIC_KEY">{$alexa_public_key}</textarea>
</div>
