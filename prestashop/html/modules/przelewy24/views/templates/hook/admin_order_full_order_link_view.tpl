{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-money"></i> {l s='Przelewy24 Payment' mod='przelewy24'}
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th><span class="title_box">{l s='Date' mod='przelewy24'}</span></th>
                <th><span class="title_box">{l s='Link' mod='przelewy24'}</span></th>
                <th><span class="title_box">{l s='P24 ID' mod='przelewy24'}</span></th>
                <th><span class="title_box">{l s='Amount' mod='przelewy24'}</span></th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span>{$p24_order_received}</span></td>
                    <td><span><a class="" href="{$link}">{$link}</a></span></td>
                    <td><span>{$p24_order_id|escape:'html':'UTF-8'}</a></span></td>
                    <td><span>{$p24_order_amount}</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
