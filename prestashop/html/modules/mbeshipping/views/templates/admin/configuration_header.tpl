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

<div class="col-lg-12">
    <div class="panel">
        <div id="mbeshipping_header">
            <div id="header_left">
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/logo_eship_for_prestashop.png" height="50px" alt="Logo">
            </div>
            <div id="header_right">
                {if !isset($customer_id)} {* Not Logged *}
                    <a title="{l s='Contact us' mod='mbeshipping'}" class="header-link" href="{$link_contact|escape:'htmlall':'UTF-8'}"
                       target="_blank">
                        <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/mail.png" alt=""/><span>{l s='Contact us' mod='mbeshipping'}</span>
                    </a>
                {else}
                    {if !$is_direct_channel_user}
                        <a title="{l s='Informations' mod='mbeshipping'}" class="header-link" href="{$link_info|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/info.png" alt=""/><span>{l s='Informations' mod='mbeshipping'}</span>
                        </a>
                        <a title="{l s='Guide' mod='mbeshipping'}" class="header-link" href="{$link_guide|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/book.png" alt=""/><span>{l s='Guide' mod='mbeshipping'}</span>
                        </a>
                        <a title="{l s='Contact us' mod='mbeshipping'}" class="header-link" href="{$link_contact|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/mail.png" alt=""/><span>{l s='Contact us' mod='mbeshipping'}</span>
                        </a>
                    {else}
                        <a title="{l s='Guide' mod='mbeshipping'}" class="header-link" href="{$link_guide|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/book.png" alt=""/><span>{l s='Guide' mod='mbeshipping'}</span>
                        </a>
                        <a title="{l s='Call us' mod='mbeshipping'}" class="header-link" href="{$link_phone|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/phone.png" alt=""/><span>{l s='Call us' mod='mbeshipping'}</span>
                        </a>
                        <a title="{l s='Write us' mod='mbeshipping'}" class="header-link" href="{$link_contact|escape:'htmlall':'UTF-8'}"
                           target="_blank">
                            <img src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/mail.png" alt=""/><span>{l s='Write us' mod='mbeshipping'}</span>
                        </a>
                    {/if}
                {/if}
            </div>
        </div>
    </div>
    {$result} {*HTML*}
</div>
