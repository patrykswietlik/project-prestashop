{*
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
*}

<div class="registration-actions">
    <button type="button" class="btn btn-secondary" onclick="backToWelcomePage()">
        <i class="material-icons">chevron_left</i>{l s='Back to the welcome page' mod='mbeshipping'}
    </button>
</div>
<iframe
        id="mbe_registration"
        src="{$registration_iframe_url|escape:'html':'UTF-8'}"
        onload="onMbeRegistrationIframeLoad()">
</iframe>
<style>
    iframe#mbe_registration {
        display: block;
        border: 0;
        height: calc(120vh);
        width: 100%;
    }
</style>

<script>
    const onMbeRegistrationIframeLoad = () => {
        console.log('MBE > registration iframe loaded')
        let endpoint = '{$registration_iframe_url nofilter}'
        if (typeof endpoint !== 'undefined') {
            const $iframe = $('iframe#mbe_registration')
            if ($iframe) {
                $iframe[0].contentWindow.postMessage({
                    'urlLogin': '{$registration_iframe_login_url nofilter}',
                    'language': '{$registration_iframe_lang nofilter}'
                }, endpoint)
            }
        }
    }
</script>
