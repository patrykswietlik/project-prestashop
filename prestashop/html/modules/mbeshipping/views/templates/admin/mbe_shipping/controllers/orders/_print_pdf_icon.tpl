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

<span class="btn-group-action">
    <span class="btn-group">
        {if $ldv_gp && !$isLDVavailable}
            {if isset($is_download_available)}
                {if $is_download_available}
                    <a class="btn btn-default _blank"
                       href="{$link->getAdminLink('AdminMbeShipping')|escape:'html':'UTF-8'}&amp;submitAction=requestDelivery&amp;id_order={$order->id|escape:'html':'UTF-8'}">
                <i class="icon-truck"></i>
            </a>
                {/if}
            {/if}
        {else}
            {if isset($is_download_available)}
            {if $is_download_available}
                <a class="btn btn-default _blank"
                   href="{$link->getAdminLink('AdminMbeShipping')|escape:'html':'UTF-8'}&amp;submitAction=generateDelivery&amp;id_order={$order->id|escape:'html':'UTF-8'}">
                <i class="icon-truck"></i>
            </a>
            {/if}
        {else}
            {if $order_carrier->tracking_number}
            <a class="btn btn-default _blank"
                   href="{$link->getAdminLink('AdminMbeShipping')|escape:'html':'UTF-8'}&amp;submitAction=generateDelivery&amp;id_order={$order->id|escape:'html':'UTF-8'}">
                <i class="icon-truck"></i>
            </a>
            {/if}
        {/if}
        {if !empty($href)}
            <a class="btn btn-default _blank" href="{$href|escape:'html':'UTF-8'}">
                <i class="icon-file-text"></i>
            </a>
        {/if}
        {/if}
	</span>
</span>
