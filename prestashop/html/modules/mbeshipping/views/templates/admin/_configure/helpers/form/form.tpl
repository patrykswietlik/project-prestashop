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

{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'access_form'}
        <div name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}" class="bootstrap col-lg-12">
            <div class="row bootstrap">
                <p class="custom">
                    {$input.text1|escape:'html':'UTF-8'}
                </p>
            </div>
            <div class="row bootstrap justify-content-center mt-1">
                {if isset($input.select)}
                    <div class="form-group">
                        {if isset($input.select.label)}
                            <label class="control-label col-lg-4">
                                {$input.select.label|escape:'html':'UTF-8'}
                            </label>
                        {/if}
                        <div class="col-lg-9">
                            <select name="{$input.select.name|escape:'html':'UTF-8'}"
                                    id="{$input.select.name|escape:'html':'UTF-8'}"
                                    {if isset($input.select.disabled) && $input.select.disabled} disabled="disabled"{/if}>
                                {foreach $input.select.options.query AS $option}
                                    <option value="{$option[$input.select.options.id]|escape:'html':'UTF-8'}">
                                        {$option[$input.select.options.name]|escape:'html':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
            </div>
            <div class="row justify-content-between">
                <div class="form-group flex">
                    <input type="text" class="form-control" id="mbe_user" name="mbe_user"
                           placeholder="{l s='User' mod='mbeshipping'}" value="{$fields_value.mbe_user|escape:'htmlall':'UTF-8'}"/>
                </div>
                <div class="form-group flex">
                    <input type="password" class="form-control" id="mbe_pass" name="mbe_pass"
                           placeholder="{l s='Password' mod='mbeshipping'}" value="{$fields_value.mbe_pass|escape:'htmlall':'UTF-8'}"/>
                </div>
            </div>
            <div class="row">
                <p class="custom">
                    {$input.text2|escape:'html':'UTF-8'}
                </p>
            </div>
            <div class="row column buttons">
                <button type="submit" class="btn btn-primary" id="mbe_login_btn" name="mbeLogin">
                    {l s='Login' mod='mbeshipping'}
                </button>
            </div>
            <div class="row">
                <p class="help-block">
                    {l s='Or proceed with the' mod='mbeshipping'}
                    <button type="submit" class="btn-link" id="mbe_adv_auth_btn" name="mbeAdvAuth">
                        {l s='advanced configuration' mod='mbeshipping'}
                    </button>
                </p>
            </div>
        </div>
    {elseif $input.type == 'auth_reset'}
        <div name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}" class="row buttons">
            <button type="submit" class="btn btn-primary" id="{$input.btn_name|escape:'html':'UTF-8'}"
                    name="{$input.btn_name|escape:'html':'UTF-8'}">
                {if $input.isAdvanced}
                    {l s='Return to login area' mod='mbeshipping'}
                {else}
                    {l s='Reset' mod='mbeshipping'}
                {/if}
            </button>
        </div>
        <div class="row">
            <p class="help-block">
                {l s='Click here if you want to reset the information entered in the form above' mod='mbeshipping'}
            </p>
        </div>
    {elseif $input.type == 'custom_text'}
        {if isset($input.text)}
            <p class="custom{if isset($input.class)} {$input.class|escape:'html':'UTF-8'}{/if}" name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}">{$input.text} {*HTML*}
            {if isset($input.list)}
                <br>
                <ul>
                    {foreach $input.list as $list_item}
                        <li>{$list_item|escape:'html':'UTF-8'}</li>
                    {/foreach}
                </ul>
            {/if}
            </p>
        {/if}
    {elseif $input.type == 'link_button'}
        <div class="col-lg-8{if !isset($input.label)} col-lg-offset-3{/if}">
            {if isset($input.name) && isset($input.link)}
                <a name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}"
                   href="{$input.link|escape:'html':'UTF-8'}"
                   {if isset($input.class)}class="{$input.class|escape:'html':'UTF-8'}"{/if}>
                    {if isset($input.icon)}<i
                        class="{$input.icon|escape:'html':'UTF-8'}"></i>&ensp;{/if}{$input.text|escape:'html':'UTF-8'}
                </a>
                {if isset($input.desc)}<p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>{/if}
            {/if}
        </div>
    {elseif $input.type == 'change_conf_mode'}
        <div name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}">
            {if isset($input.text)}
                <div class="alert medium-alert alert-warning" role="alert">
                    <p class="alert-text">
                        {$input.text|escape:'html':'UTF-8'}
                    </p>
                </div>
            {/if}
            <div class="row buttons">
                <button type="submit" class="btn btn-primary" id="{$input.btn_name|escape:'html':'UTF-8'}" name="{$input.btn_name|escape:'html':'UTF-8'}">
                    {l s='Change configuration mode' mod='mbeshipping'}
                </button>
            </div>
        </div>
    {elseif $input.type == 'advanced_conf'}
        <div class="row buttons" name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}">
            <a href="{$input.admin_package|escape:'htmlall':'UTF-8'}" class="btn btn-primary"
               target="_blank">{l s='Go to packages section' mod='mbeshipping'}</a>
            <a href="{$input.admin_product_package|escape:'htmlall':'UTF-8'}" class="btn btn-primary"
               target="_blank">{l s='Go to product packages section' mod='mbeshipping'}</a>
        </div>
    {elseif $input.type == 'pickup_address'}
        <div class="row buttons" name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}">
            <a href="{$input.admin_pickup_address|escape:'htmlall':'UTF-8'}" class="btn btn-primary"
               target="_blank">{l s='Set your pickup Addresses' mod='mbeshipping'}</a>
        </div>
    {elseif $input.type == 'checklist'}
        <div name="{$input.name|escape:'html':'UTF-8'}" id="{$input.name|escape:'html':'UTF-8'}">
            <div class="check">
                <label class="control-label col-lg-6">
                    {l s='Prestashop compatibility' mod='mbeshipping'}
                </label>
                <div id="mbe_check_version" class="col-lg-6">
                    <i class="icon-remove-circle warning"></i>
                </div>
            </div>
            <div class="check">
                <label class="control-label col-lg-6">
                    {l s='Hooks status' mod='mbeshipping'}
                </label>
                <div id="mbe_check_hooks" class="col-lg-6">
                    <i class="icon-remove-circle warning"></i>
                </div>
            </div>
            <div class="check">
                <label class="control-label col-lg-6">
                    {l s='DB status' mod='mbeshipping'}
                </label>
                <div id="mbe_check_db" class="col-lg-6">
                    <i class="icon-remove-circle warning"></i>
                </div>
            </div>
            <div class="check">
                <label class="control-label col-lg-6">
                    {l s='Tabs status' mod='mbeshipping'}
                </label>
                <div id="mbe_check_tabs" class="col-lg-6">
                    <i class="icon-remove-circle warning"></i>
                </div>
            </div>
            <div class="check">
                <label class="control-label col-lg-6">
                    {l s='Overrides status' mod='mbeshipping'}
                </label>
                <div id="mbe_check_overrides" class="col-lg-6">
                    <i class="icon-remove-circle warning"></i>
                </div>
            </div>
            <div class="row buttons">
                <a id="mbe_checkup_btn" class="btn btn-primary"
                   onclick="runCheckup()">{l s='Run check-up' mod='mbeshipping'}</a>
            </div>
        </div>
    {elseif $input.type == 'custom_button'}
        <div class="row">
            <button type="{if $input.submit}submit{else}button{/if}"
                    {if $input.class}class="{$input.class}"{/if}
                    {if $input.name}id="{$input.name}"{/if}
                    {if $input.name}name="{$input.name}"{/if}
                    {if $input.onclick}onclick="{$input.onclick}"{/if}>
                {$input.text}
            </button>
        </div>
    {elseif $input.type == 'alert'}
        {if isset($input.text)}
            <div class="alert medium-alert alert-{$input.alert}" role="alert">
                <p class="alert-text">
                    {$input.text|escape:'html':'UTF-8'}
                </p>
            </div>
        {/if}
    {elseif $input.type == 'time'}
        <div class="col-lg-8{if !isset($input.label)} col-lg-offset-3{/if}">
            <input id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                   type="text"
                   class="validate-time {if isset($input.class)}{$input.class}{/if}"
                   name="{$input.name}"
                   value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"
                   minlength="5"
                   maxlength="5"
                   {if isset($input.placeholder)}placeholder="{$input.placeholder|escape:'html':'UTF-8'}"{/if}
            />
            {if isset($input.desc)}<p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>{/if}
        </div>
    {elseif $input.type == 'select_with_alt_button'}
        <div class="col-lg-8{if !isset($input.label)} col-lg-offset-3{/if}">
            <div class="row m-0">
                <select name="{$input.name|escape:'html':'UTF-8'}"
                        class="{if isset($input.button_link)}col-lg-12{else}col-lg-12{/if}"
                        id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}"
                        {if isset($input.multiple) && $input.multiple} multiple="multiple"{/if}
                        {if isset($input.size)} size="{$input.size|escape:'html':'UTF-8'}"{/if}
                        {if isset($input.search)} data-search="{$input.search|escape:'html':'UTF-8'}"{/if}
                        {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}>
                    {foreach $input.options.query AS $option}
                        {if $option == "-"}
                            <option value="">-</option>
                        {else}
                            <option value="{$option[$input.options.id]}"
                                    {if isset($input.multiple)}
                                        {foreach $fields_value[$input.name] as $field_value}
                                            {if $field_value == $option[$input.options.id]}
                                                selected="selected"
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $fields_value[$input.name] == $option[$input.options.id]}
                                            selected="selected"
                                        {/if}
                                    {/if}
                            >{$option[$input.options.name]}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
            {if isset($input.button_link)}
                <div class="row mx-0 mt-1">
                    <a href="{$input.button_link|escape:'html':'UTF-8'}"
                       class="btn btn-default pull-left">
                        {if isset($input.button_icon)}
                            <i class="{$input.button_icon|escape:'html':'UTF-8'}"></i>
                            &ensp;{/if}
                        {$input.button_title|escape:'html':'UTF-8'}
                    </a>
                </div>
            {/if}
            <div>
                {if isset($input.desc)}<p class="help-block">{$input.desc|escape:'html':'UTF-8'}</p>{/if}
            </div>
        </div>
    {elseif $input.type == 'table'}
        <div class="col-lg-8{if !isset($input.label)} col-lg-offset-3{/if}">
            <table id="table-configuration" class="table panel">
                <thead>
                <tr class="nodrag nodrop">
                    {foreach from=$input.columns item=column}
                        <th class="left">
                            {$column}
                        </th>
                    {/foreach}
                </tr>
                </thead>
                <tbody>
                {foreach from=$input.rows key=i item=row}
                    <tr class="{if $i is even}{else}odd{/if}">
                        {foreach from=$row item=field}
                            <td class="left">
                                {$field}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        {$smarty.block.parent} {*HTML*}
    {/if}
{/block}

{block name="description"}
    {if isset($input.desc_link)}
        {if isset($input.desc) && !empty($input.desc)}
            <p class="help-block">
                {$input.desc|escape:'html':'UTF-8'}
                <a href="{$input.desc_link.url|escape:'html':'UTF-8'}" target="_blank">{$input.desc_link.text|escape:'html':'UTF-8'}</a>
            </p>
        {/if}
    {else}
        {$smarty.block.parent} {*HTML*}
    {/if}
{/block}
