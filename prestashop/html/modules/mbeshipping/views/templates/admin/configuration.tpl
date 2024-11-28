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

<div id="mbeshipping_conf_content" class="clearfix">
    <!-- Nav tabs -->
    {if $show_side_menu}
    <div class="col-lg-2">
        <div class="list-group">
            {foreach $conf_tabs as $tab}
                <a id="{$tab.id|escape:'htmlall':'UTF-8'}" href="#{$tab.id|escape:'htmlall':'UTF-8'}" class="list-group-item {if $tab.show_this}active{/if}"
                   data-toggle="tab"
                   onclick="showTabContent(this)">
                    {if $tab.icon_class}<i class="{$tab.icon_class}"></i>&ensp;{/if}{$tab.label} {*HTML*}
                </a>
            {/foreach}
        </div>

        {if !$is_direct_channel_user} {* Show only for MBE Users *}
        <!-- Nav links -->
        <div class="list-group">
            <a href="{$link_info|escape:'htmlall':'UTF-8'}" class="list-group-item" target="_blank">
                <img class="icon" alt="" src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/info.png">&ensp;
                {l s='Informations' mod='mbeshipping'}
            </a>
            <a href="{$link_guide|escape:'htmlall':'UTF-8'}" class="list-group-item" target="_blank">
                <img class="icon" alt="" src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/book.png">&ensp;
                {l s='Guide' mod='mbeshipping'}
            </a>
            <a href="{$link_support|escape:'htmlall':'UTF-8'}" class="list-group-item" target="_blank">
                <img class="icon" alt="" src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/mail.png">&ensp;
                {l s='Assistant' mod='mbeshipping'}
            </a>
            <a href="{$link_portal|escape:'htmlall':'UTF-8'}" class="list-group-item" target="_blank">
                <img class="icon-round" alt="" src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/icons/logo.jpg">&nbsp;&ensp;
                <span class="go-to-portal">
                    <span class="text-nowrap">{l s='Go to portal MBE eShip' mod='mbeshipping'}</span>
                </span>&ensp;
                <i class="icon-chevron-right float-right"></i>
            </a>
        </div>
        {/if}

        <!-- Version -->
        <div class="list-group">
            <span class="list-group-item">
                {l s='Version' mod='mbeshipping'}:&nbsp;{$module_version|escape:'htmlall':'UTF-8'}
            </span>
        </div>
    </div>
    {/if}

    <!-- Tab panes -->
    <div class="tab-content {if $show_side_menu}col-lg-10{else}col-lg-12{/if}">
        {foreach $conf_tabs as $tab}
            <div class="tab-pane panel {if $tab.show_this}active{/if}" id="{$tab.id|escape:'htmlall':'UTF-8'}">
                {if !empty($tab.desc)}
                <div class="panel mbe">
                    <div class="panel-heading mbe">
                       {$tab.label} {*HTML*}
                       {if $is_direct_channel_user && (isset($tab.guide) && isset($tab.guide[$employee_iso_code]) && !empty($tab.guide[$employee_iso_code]))}
                       <a class="panel-heading-manual" target="_blank" href="{$tab.guide[$employee_iso_code]|escape:'htmlall':'UTF-8'}">{l s='User Manual' mod='mbeshipping'}</a>
                       {/if}
                    </div>
                    <p class="tab-description">{$tab.desc}</p> {*HTML*}
                </div>
                {/if}
                {if !empty($tab.content)}
                    {$tab.content} {*HTML*}
                {else}
                    <p>{l s='No configurations to display' mod='mbeshipping'}</p>
                {/if}
            </div>
        {/foreach}
    </div>
</div>
