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

{extends file="./_base_list_content.tpl"}
{assign "table_columns" {count($fields_display) + (($has_actions) ? 1 : 0) + (($has_bulk_actions) ? 1 : 0) + (($multishop_active && $shop_link_type) ? 1 : 0)}}
{block name="row"}
    {if isset($tr.pickup_batch_index) && $tr.pickup_batch_index === 0}
        {assign "is_single_pickup" $pickup_batch_list[$tr.pickup_batch_id]['is_single_pickup']}
        <tr class="tr_row_pickup_batch">
            {if count($list) > 1}
                <td class="td_row_pickup_batch row-selector text-center">
                    <input type="checkbox" name="pickupBatchBox[]"
                           value="{$pickup_batch_list[$tr.pickup_batch_id]['id_mbeshipping_pickup_batch']}"
                           class="noborder"/>
                </td>
            {/if}
            <td class="td_row_pickup_batch" colspan="{if count($list) > 1}{$table_columns - 2}{else}{$table_columns - 1}{/if}">
                {if $is_single_pickup}
                    {l s='Single order pickup' mod='mbeshipping'},
                {else}
                    {l s='Pickup Batch ID' mod='mbeshipping'}:&nbsp;{$pickup_batch_list[$tr.pickup_batch_id]['pickup_batch_id']},
                {/if}
                {l s='Status' mod='mbeshipping'}:&nbsp;{$pickup_batch_list[$tr.pickup_batch_id]['status']}
                {*{' | '|implode:$pickup_batch_list[$tr.pickup_batch_id]}*}
            </td>
            <td class="td_row_pickup_batch" class="text-right">
                {assign "pickup_batch_actions" $pickup_batch_list[$tr.pickup_batch_id]['actions']}
                {if $pickup_batch_actions|count > 0}
                    {if $pickup_batch_actions|count > 1}<div class="btn-group-action">{/if}
                    <div class="btn-group pull-right" style="width: 100%">
                        <a href="{$pickup_batch_actions[0]['href']}" class="btn btn-default" title="{$pickup_batch_actions[0]['label']}" style="min-width: calc(100% - 39px)">
                            <i class="{$pickup_batch_actions[0]['icon']}"></i>
                            <span style="margin-inline: auto">{$pickup_batch_actions[0]['label']}</span>
                        </a>
                        {if $pickup_batch_actions|count > 1}
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach $pickup_batch_actions AS $key => $action}
                                    {if $key != 0}
                                        <li>
                                            <a href="{$action['href']}" title="{$action['label']}">
                                                <i class="{$action['icon']}"></i>
                                                {$action['label']}
                                            </a>
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        {/if}
                    </div>
                    {if $pickup_batch_actions|count > 1}</div>{/if}
                {/if}
            </td>
        </tr>
    {/if}
    {$smarty.block.parent} {*HTML*}
{/block}

{block name="open_bulk_actions_td"}
    {if isset($tr.pickup_batch_index)}
        {if count($list) > 1}
            <td class="row-selector text-center">
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/icons/list_item.png" alt="list_item"
                     width="16"
                     height="16">
            </td>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="td_content"}
    {if $key === 'pickup_batch_id' && isset($tr.pickup_batch_index)}
        {assign "is_single_pickup" $pickup_batch_list[$tr.pickup_batch_id]['is_single_pickup']}
        {if $is_single_pickup}
            {l s='Single order pickup' mod='mbeshipping'}
        {else}
            {$tr.$key|escape:'html':'UTF-8'}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
