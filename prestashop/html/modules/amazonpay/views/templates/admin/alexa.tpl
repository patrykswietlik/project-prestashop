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

{capture name=blog_link_assign assign=blog_link}<a href="{$blog_link|escape:'htmlall':'UTF-8'}" target="_blank">{/capture}
{capture name=alexa_hint_assign assign=alexa_hint}{l s='To learn more about Alexa delivery notifications, visit the [1]Amazon Pay blog[/1].' tags=[$blog_link] mod='amazonpay'}{/capture}

<div class="panel">

	<div class="alert alert-info">

		<p>
			{$alexa_hint}
		</p>

	</div>

	{$alexaform}
</div>

{capture name=public_key_mail_assign assign=public_key_mail}{l s='[1]Request your Public Key ID[/1]' tags=['<a href="JavaScript:void(0)" id="public_key_mail_init">'] mod='amazonpay'}{/capture}

<span id="public_key_mail_init_span" style="display:none;">{$public_key_mail}</span>
<span id="alexa_mail_subject" style="display:none">[{$alexa_region|escape:'htmlall':'UTF-8'}] {l s='Request for Amazon Pay Public Key ID for' mod='amazonpay'}</span>
<span id="alexa_mail_body" style="display:none">{l s='Merchant ID:' mod='amazonpay'}</span>
<span id="alexa_public_key" style="display:none">{$alexa_public_key|escape:'htmlall':'UTF-8'|replace:"\n":"%0D%0A"}</span>
