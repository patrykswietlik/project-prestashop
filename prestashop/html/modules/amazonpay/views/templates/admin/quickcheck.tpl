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

{assign var=quickcheck_img_status_1 value='<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIElEQVQ4jY2ST28SURTFL8U0NFIhgV1ZIC5MDAMznRmhTdohIWkXlpqYVINgYGN3xg+giexd2G/Qld2ONdAiNqVALFAGMfxLupHFUE1dSCRxoV0cF1joAJqe5C5e3vm9c999j2hIN2urLqbxQGZrD1WhHgZXDeLWp3vqjXJAthX8rmG/Ru5GUJ5thmCt+KFXboMUHqTw0CsiTMoiHKUV2ArL8ggYbcUMQiOs2quBPvSvshR9mDlcUu2bPoMm+TLweZnzC7BkpV4nbD0kzjZDlwKjrRik48cghYc1I+Ha3rxIztpaylrx/xf0Ha+j9esEkdYLUJYFfeBgyHtgPJhPkdh41L44MHs1ALYZ7K83TrcAAE8/vwTFmd4BCg9dScD0e2+bhHpYk5buKtj4ugVzSYLcSQMA5G/7oDdO0AGr8RqTHhBXHaSxzSDO9f3sBwCgc9aFKeEFvXONXG1qVwQxlbW2XhFBCo9Xp68xrEj5GeitE3SkhXUlHpNxrk3XP95JmZRFkMLDVJGw+WW7D7d+nvRaz7Ij6RM5HvokmyJHcVVwlFYGm0c8IvXn6Pzu4m7hSW9wY17myg4L2nYKRERkKyzLlqJPY3CX7/daz3Gj6fscKMEMvrQvHTXMHC6p5vyC1pyfHQ/HGZVidgMNy5KVZGtGgiHvga4kaAY2kfvb9sXkcbqamWOm03Oycc+rGpMeTO2KmNzhVH3SLVOCYYb9fwA8T9y7cxPBgwAAAABJRU5ErkJggg==" />'}
{assign var=quickcheck_img_status_0 value='<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACrElEQVQ4jW1T30taYRh+VShjehTsri5MPd+h7OjRVOgiirnBGBtRsfv9KV33J2zQaIEUhMdK7aAnNS1tuCbaBrvr4jiysq0xxlj78eziOPuxvfDywfc+3/O+z8P7Ed2KgweCrzEtyvUZSavPhVCbCeD1wxGtes8t708M+m7jb0Rj2i8fzgZRHe9HecSEkkB6DptQDtlQiQ6hPDko//PwaP6p+XAupNWiTuwKhB1GyDHCNiOonTPH9PtSxIHixID2YsppvtG5FnWiJBDyjJAP2qHwhLTnKtUAhyyv1wtjdhQiDn2St7NS+HA2iN3O4w9ri7hoVJGUbJDdBNlNSEkc2kUFR88XkOmQqGEHVIkLU+PxaKY63o8dRsgHbfj87g1+X16iXasg7rch7rfhbE/F92MNJ7kkEqIVWZ6Q8/ZClSwZajwJN8sjJuQYQeEJWwEbzutV/Pr2FafVEk73C7g8O0arqCDmtSLmImzxhG3BgLTf2qT6XAglQTcq7SEk3KR3rVXw88sFfnxqo7WnYslrxZJTl5T26PhN0QKqzQRQEnS30x4dEJfsaF8n2FWxPMphxXVFoDLChrcPdPBIbJaGTd0JUgE7Pl6T0LomIe7jkOgSGJAQeppUvTuUKYdsyDGCOmbvmnhWq+Cll8PyKIeTjomtXBIpiYPCExTBiCRvytCr+65QJTqEHUbI8oTm2iLO61XERA5LTsKKixD3cTgpKnj/bAGrLkKGJySYCesuChERUXlyUC5FHMgzvZiSbIh19Mpu3diU34pVFyHuJqSYEQk3Xa10fmrKXJwY0ApjduQ7k6Q8ev7dRIXvkDMj4h7S5onMdDsKEYeshh3IeXuxLRi6f0FlBiiCEQlmutn5f5EV74iqZJW3fBZtU7Rgw9uHdaFH22AmOeUi8Tb+Dx+s3LBdcjXYAAAAAElFTkSuQmCC" />'}
{assign var=quickcheck_img_info value='<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDcuMi1jMDAwIDc5LmRlZTNhNzcwMywgMjAyMi8wOC8xOC0xNjo1MDozMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIzLjUgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjZGMTRCNzkxN0NGMTFFRTgxQzVFMUMzRDJCNjZGNDQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjZGMTRCN0ExN0NGMTFFRTgxQzVFMUMzRDJCNjZGNDQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCNkYxNEI3NzE3Q0YxMUVFODFDNUUxQzNEMkI2NkY0NCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNkYxNEI3ODE3Q0YxMUVFODFDNUUxQzNEMkI2NkY0NCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqFp4o8AAADmSURBVHjaYmRY958BDWgBcQoQewKxIlTsHhDvAOI5QHwNWTEjkgFsQNwHxJlAzMSAHfwD4tlAXAjE30ECLEiatwKxCwN+ADI4HYhVoS78BbOpH5vmHcYMDP8DsRrkBHUt2AvaQPoiEDMzkAb+ArE+EzTAsGrG4wIGqJ4UkAEeDOQDD5ABShQYoMTEQBkAx8I9Cgx4yARNYeSCPSAD5kKjhIGMaJwDMuAKEM8kwwCQniuwvEBsUoaBfehJ+RcQewPxVGiGYcCTmUA2+0D1oORGGAAl7VRoAlOCKnwACjBodr6CrBggwACigzUkgZYq1AAAAABJRU5ErkJggg==" />'}

<div class="amazonpay_quickcheck panel">
    <ul>
        {if isset($quickcheck.SSLEnabled)}
            <li class="amazonpay_quickcheck_status_{$quickcheck.SSLEnabled.status}">
                {if $quickcheck.SSLEnabled.status == 1}
                    {$quickcheck_img_status_1}
                {else}
                    {$quickcheck_img_status_0}
                {/if}
                {l s='SSL' mod='amazonpay'}
            </li>
        {/if}
        {if isset($quickcheck.Versioncheck)}
            <li class="amazonpay_quickcheck_status_{$quickcheck.Versioncheck.status}">
                {if $quickcheck.Versioncheck.status == 1}
                    {$quickcheck_img_status_1}
                    {l s='You are using the latest version' mod='amazonpay'}
                {else}
                    {$quickcheck_img_status_0}
                    {l s='Modul update available' mod='amazonpay'}
                {/if}
            </li>
        {/if}
        {if isset($quickcheck.KeysProvided)}
            <li class="amazonpay_quickcheck_status_{$quickcheck.KeysProvided.status}">
                {if $quickcheck.KeysProvided.status == 1}
                    {$quickcheck_img_status_1}
                    {l s='Credentials validated' mod='amazonpay'}
                {else}
                    {$quickcheck_img_status_0}
                    {l s='Credentials not validated' mod='amazonpay'}
                {/if}
            </li>
        {/if}
        {if isset($quickcheck.KYCPassed)}
            <li class="amazonpay_quickcheck_status_{$quickcheck.KYCPassed.status}">
                {if $quickcheck.KYCPassed.status == 1}
                    {$quickcheck_img_status_1}
                    {l s='KYC passed' mod='amazonpay'}
                {else}
                    {$quickcheck_img_status_0}
                    {l s='KYC not passed' mod='amazonpay'}
                {/if}
            </li>
        {/if}
        {if isset($quickcheck.sandbox)}
            <li class="amazonpay_quickcheck_status_info">
                {$quickcheck_img_info}
                {if $quickcheck.sandbox.status == 1}
                    {l s='Sandbox enabled' mod='amazonpay'}
                {else}
                    {l s='Livemode enabled' mod='amazonpay'}
                {/if}
            </li>
        {/if}
    </ul>
</div>