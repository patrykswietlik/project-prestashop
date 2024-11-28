{*
* 2007-2023 patworx.de
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AmazonPay to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    patworx multimedia GmbH <service@patworx.de>
*  @copyright 2007-2023 patworx multimedia GmbH
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file=$default_template}

{block name="other_input"}
    {if isset($field[0])}
        {if isset($field[0]['name'])}
            {if $field[0]['name'] == 'AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS'}
                <div id="carriers_mapping_alexa">
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Carrier mapping' mod='amazonpay'}
                        </label>
                        <div class="col-lg-9">
                            {foreach from=$amz_carrier_options item=co}
                                <div class="row">
                                    <label class="col-lg-3">{$co.label}:</label>
                                    <select name="AMAZONPAY_CARRIERS_MAPPING[{$co.val}]" class="col-lg-9">
                                        <option value="">---</option>
                                        {foreach from=$amazon_carriers item=ac}
                                            <option value="{$ac.1}"{if isset($mapped_carriers[$co.val]) && $mapped_carriers[$co.val] == {$ac.1}} selected{/if}>{$ac.0}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            {/foreach}

                            <p class="help-block">{l s='Map the carriers to your carrier names. Note: If no carrier is assigned, a delivery notification will not be sent.' mod='amazonpay'}</p>

                        </div>
                    </div>
                </div>
            {/if}
        {/if}
    {/if}
{/block}
