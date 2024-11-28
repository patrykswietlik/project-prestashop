{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

{if (isset($status) == true) && ($status == 'ok')}
    <h3>{l s='Your order is complete.'  mod='sumuppaymentgateway'}</h3>
    <p>
        <br/>- {l s='Amount' mod='sumuppaymentgateway'} : <span
                class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
        <br/>- {l s='Reference' mod='sumuppaymentgateway'} : <span
                class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
        <br/><br/>{l s='An email has been sent with this information.' mod='sumuppaymentgateway'}
        <br/><br/>{l s='If you have questions, comments or concerns, please contact our' mod='sumuppaymentgateway'} <a
                href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='sumuppaymentgateway'}</a>
    </p>
{else}
    <h3>{l s='Your order has not been accepted.' mod='sumuppaymentgateway'}</h3>
    <p>
        <br/>- {l s='Reference' mod='sumuppaymentgateway'} <span
                class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
        <br/><br/>{l s='Please, try to order again.' mod='sumuppaymentgateway'}
        <br/><br/>{l s='If you have questions, comments or concerns, please contact our' mod='sumuppaymentgateway'} <a
                href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='sumuppaymentgateway'}</a>
    </p>
{/if}
<hr/>