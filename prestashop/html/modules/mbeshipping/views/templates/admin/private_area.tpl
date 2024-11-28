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

<iframe
        id="mbe_private_area"
        src="{$private_area_iframe_url|escape:'html':'UTF-8'}"
        onload="onMbePrivateAreaIframeLoad()">
</iframe>

<style>
    iframe#mbe_private_area {
        display: block;
        border: 0;
        height: calc(100vh - 205px);
        width: 100%;
    }
</style>

<script>
    const onMbePrivateAreaIframeLoad = () => {
        console.log('MBE > iframe loaded')
        let endpoint = '{$private_area_iframe_url nofilter}'
        if (typeof endpoint !== 'undefined') {
            const $iframe = $('iframe#mbe_private_area')
            if ($iframe) {
                $iframe[0].contentWindow.postMessage({
                    'accessToken': '{$private_area_iframe_access_token nofilter}',
                    'language': '{$private_area_iframe_lang nofilter}',
                    'urlLogin': '{$private_area_iframe_login_url nofilter}'
                }, endpoint)
            }
        }
    }
</script>
