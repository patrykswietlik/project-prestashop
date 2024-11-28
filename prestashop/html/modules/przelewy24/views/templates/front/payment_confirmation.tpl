{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
{extends file='page.tpl'}
{capture name=path}{l s='Pay with Przelewy24' mod='przelewy24'}{/capture}
{assign var='current_step' value='payment'}
{block name='page_content'}

{include file='module:przelewy24/views/templates/hook/payment_block.tpl'}

{/block}
