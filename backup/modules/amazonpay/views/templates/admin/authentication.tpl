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

{capture name=help_page_link_assign assign=help_page_link}<a href="{$help_page_link|escape:'htmlall':'UTF-8'}" target="_blank">{/capture}

<div class="panel">

	<div class="alert alert-info">

		{if isset($create_keypair_action)}
			<p>
				{l s='To authenticate for Amazon Pay, please generate your key-pair with the following button.' mod='amazonpay'}<br>
				{l s='Email the public key to your Amazon Pay account manager. They will respond with your publicKeyId. Never share your private key.' mod='amazonpay'}<br>
				{l s='This publicKeyId you then can enter in the following configuration form.' mod='amazonpay'}
			</p>

			<a href="{$create_keypair_action}" class="btn btn-primary" onclick="return confirm('{l s='Are you sure? This might overwrite an existing key-pair.' mod='amazonpay'}')">{l s='Create key-pair' mod='amazonpay'}</a>
		{else}
			<p>
				{l s='If you haven’t registered an Amazon Pay merchant account, please [1]register[/1] your merchant account first.[2]If you have already created an Amazon Pay merchant account, please [3]sign-in to[/3] your merchant account (initiated as a registration flow but please switch to ‘Sign in’ when you are prompted to enter account details)[4]For both cases you will arrive to a page to ‘Transfer API Keys’, come back here after that’s done and follow [5]Amazon Pay Help Page[/5] to finish the configuration. If the automatic  key transfer did not work please manually enter your keys below.' tags=['<a href="JavaScript:initAmazonReg()">', '<br>', '<a href="JavaScript:initAmazonReg()">', '<br>' , $help_page_link] mod='amazonpay'}<br>
			</p>
		{/if}
	</div>

	<div style="" id="authformDiv">
		{$authform}
	</div>
</div>
{literal}
	<script> function initAmazonReg() { jQuery('#amazonRegForm').submit(); }</script>
{/literal}