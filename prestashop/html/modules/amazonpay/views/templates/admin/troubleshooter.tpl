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

{if !isset($troubleshooter_results)}
	<div class="panel">

		<div class="alert alert-info">
			<p>
				{l s='Use the troubleshooter to find potential problems in your integraton' mod='amazonpay'}
			</p>
		</div>

		<button type="button" id="start_troubleshooter" class="btn btn-primary" data-url="{$troubleshooter|escape:'htmlall':'UTF-8'}">
			<strong>{l s='Run Troubleshooter' mod='amazonpay'}</strong>
			<img style="display:none; height: 15px" src="{$smarty.const._PS_BASE_URL_SSL_|escape:'htmlall':'UTF-8'}{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/views/img/ts_loader.gif" />
		</button>

		<div id="troubleshooter_results"></div>
	</div>

{else}
	{foreach from=$troubleshooter_results item=tsr}

		<div class="row">
			<div class="col-xs-6">
				<div class="row innerrow">
					<div class="col-xs-1">
						{if $tsr.status == '1'}
							<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIElEQVQ4jY2ST28SURTFL8U0NFIhgV1ZIC5MDAMznRmhTdohIWkXlpqYVINgYGN3xg+giexd2G/Qld2ONdAiNqVALFAGMfxLupHFUE1dSCRxoV0cF1joAJqe5C5e3vm9c999j2hIN2urLqbxQGZrD1WhHgZXDeLWp3vqjXJAthX8rmG/Ru5GUJ5thmCt+KFXboMUHqTw0CsiTMoiHKUV2ArL8ggYbcUMQiOs2quBPvSvshR9mDlcUu2bPoMm+TLweZnzC7BkpV4nbD0kzjZDlwKjrRik48cghYc1I+Ha3rxIztpaylrx/xf0Ha+j9esEkdYLUJYFfeBgyHtgPJhPkdh41L44MHs1ALYZ7K83TrcAAE8/vwTFmd4BCg9dScD0e2+bhHpYk5buKtj4ugVzSYLcSQMA5G/7oDdO0AGr8RqTHhBXHaSxzSDO9f3sBwCgc9aFKeEFvXONXG1qVwQxlbW2XhFBCo9Xp68xrEj5GeitE3SkhXUlHpNxrk3XP95JmZRFkMLDVJGw+WW7D7d+nvRaz7Ij6RM5HvokmyJHcVVwlFYGm0c8IvXn6Pzu4m7hSW9wY17myg4L2nYKRERkKyzLlqJPY3CX7/daz3Gj6fscKMEMvrQvHTXMHC6p5vyC1pyfHQ/HGZVidgMNy5KVZGtGgiHvga4kaAY2kfvb9sXkcbqamWOm03Oycc+rGpMeTO2KmNzhVH3SLVOCYYb9fwA8T9y7cxPBgwAAAABJRU5ErkJggg==" />
						{else}
							<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACrElEQVQ4jW1T30taYRh+VShjehTsri5MPd+h7OjRVOgiirnBGBtRsfv9KV33J2zQaIEUhMdK7aAnNS1tuCbaBrvr4jiysq0xxlj78eziOPuxvfDywfc+3/O+z8P7Ed2KgweCrzEtyvUZSavPhVCbCeD1wxGtes8t708M+m7jb0Rj2i8fzgZRHe9HecSEkkB6DptQDtlQiQ6hPDko//PwaP6p+XAupNWiTuwKhB1GyDHCNiOonTPH9PtSxIHixID2YsppvtG5FnWiJBDyjJAP2qHwhLTnKtUAhyyv1wtjdhQiDn2St7NS+HA2iN3O4w9ri7hoVJGUbJDdBNlNSEkc2kUFR88XkOmQqGEHVIkLU+PxaKY63o8dRsgHbfj87g1+X16iXasg7rch7rfhbE/F92MNJ7kkEqIVWZ6Q8/ZClSwZajwJN8sjJuQYQeEJWwEbzutV/Pr2FafVEk73C7g8O0arqCDmtSLmImzxhG3BgLTf2qT6XAglQTcq7SEk3KR3rVXw88sFfnxqo7WnYslrxZJTl5T26PhN0QKqzQRQEnS30x4dEJfsaF8n2FWxPMphxXVFoDLChrcPdPBIbJaGTd0JUgE7Pl6T0LomIe7jkOgSGJAQeppUvTuUKYdsyDGCOmbvmnhWq+Cll8PyKIeTjomtXBIpiYPCExTBiCRvytCr+65QJTqEHUbI8oTm2iLO61XERA5LTsKKixD3cTgpKnj/bAGrLkKGJySYCesuChERUXlyUC5FHMgzvZiSbIh19Mpu3diU34pVFyHuJqSYEQk3Xa10fmrKXJwY0ApjduQ7k6Q8ev7dRIXvkDMj4h7S5onMdDsKEYeshh3IeXuxLRi6f0FlBiiCEQlmutn5f5EV74iqZJW3fBZtU7Rgw9uHdaFH22AmOeUi8Tb+Dx+s3LBdcjXYAAAAAElFTkSuQmCC" />
						{/if}
					</div>
					<div class="col-xs-11 troubleshooter_state_{$tsr.status|escape:'htmlall':'UTF-8'}">
						{$tsr.title|escape:'htmlall':'UTF-8'}
					</div>
				</div>
				{if $tsr.status == '0'}
					<div class="row">
						<div class="col-xs-1">
							&nbsp;
						</div>
						<div class="col-xs-11">
							{$tsr.description nofilter} {* no escaping, previously prepared html content! *}
						</div>
					</div>
				{/if}
			</div>
		</div>

	{/foreach}
{/if}
