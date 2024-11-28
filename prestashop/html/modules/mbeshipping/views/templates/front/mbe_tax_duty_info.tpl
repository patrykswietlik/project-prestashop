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

<div class="bootstrap">
    <div class="module_warning alert alert-warning">
        {if $custom_duties_guaranteed}
            {l s='As with for all international shipments, customs requires a payment to clear goods through customs. It is a cost independent of our policies and tariffs, but the figure of [1]%s[/1] charged ensures customs clearance, taken care of by us.' mod='mbeshipping' sprintf=[Tools::displayPrice($net_tax_and_duty_total_price)] tags=['<b>']}
        {else}
            {l s='As for all international shipments, customs requires a payment to clear goods through customs. It is a cost independent of our policies and tariffs. The [1]%s[/1] figure shown may vary depending on the legislation of the country of destination.' mod='mbeshipping' sprintf=[Tools::displayPrice($net_tax_and_duty_total_price)] tags=['<b>']}
        {/if}
    </div>
</div>
