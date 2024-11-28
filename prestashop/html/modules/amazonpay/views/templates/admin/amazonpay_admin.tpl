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

<script>
	let amazonpay_get_public_key_link = '{if isset($getPublicKeyLink)}{$getPublicKeyLink}{/if}';
	let amazonpay_ref_info_link = '{if isset($ref_link)}{$ref_link}{/if}';
</script>

{if isset($keygen_success)}
	<div class="alert alert-success">{l s='Key generation successful. [1]Request your Public Key ID[/1]. Amazon Pay will send your Public Key ID to the email address associated with your Amazon Pay merchant account. Check your inbox for an email from Amazon Pay with your [2]Public Key ID[/2], and then enter it to the Public Key ID field.' tags=['<a href="JavaScript:void(0)" id="public_key_mail_init_2">', '<b>']  mod='amazonpay'}</div>
{elseif isset($keygen_error)}
	<div class="alert alert-warning" role="alert">{l s='Key generation failed. To generate your keys manually, follow the steps in the Amazon Pay integration guide: ' mod='amazonpay'} <a href="https://developer.amazon.com/docs/amazon-pay-automatic/delivery-notifications.html#keys" target="_blank">https://developer.amazon.com/docs/amazon-pay-automatic/delivery-notifications.html</a></div>
{/if}

{if isset($quickcheck)}
	{include file='./quickcheck.tpl'}
{/if}

<ul class="nav nav-tabs" role="tablist" id="amazontabs">
	<li class="active"><a href="#amazonpay_connect" role="tab" data-toggle="tab">{l s='Connect' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_authentication" role="tab" data-toggle="tab">{l s='Authentication' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_configuration" role="tab" data-toggle="tab">{l s='Configuration' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_expertmode" role="tab" data-toggle="tab">{l s='Expert Mode' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_alexa" role="tab" data-toggle="tab">{l s='Alexa Delivery Notification' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_troubleshooter" role="tab" data-toggle="tab">{l s='Troubleshooter' mod='amazonpay'}</a></li>
	<li><a href="#amazonpay_support" role="tab" data-toggle="tab">{l s='Contact us' mod='amazonpay'}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="amazonpay_connect">{include file='./connect.tpl'}</div>
	<div class="tab-pane" id="amazonpay_authentication">{include file='./authentication.tpl'}</div>
	<div class="tab-pane" id="amazonpay_configuration">{include file='./configuration.tpl'}</div>
	<div class="tab-pane" id="amazonpay_expertmode">{include file='./expertmode.tpl'}</div>
	<div class="tab-pane" id="amazonpay_alexa">{include file='./alexa.tpl'}</div>
	<div class="tab-pane" id="amazonpay_troubleshooter">{include file='./troubleshooter.tpl'}</div>
	<div class="tab-pane" id="amazonpay_support">{include file='./support.tpl'}</div>
</div>

<div id="amazonpay_hidden_parts" style="display:none;">
	<a href="{if isset($getPublicKeyLink)}{$getPublicKeyLink}{/if}" id="getPublicKeyLink">{l s='Download Public Key' mod='amazonpay'}</a>
</div>
