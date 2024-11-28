{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
{extends file=$layout}

{block name='content'}
    <section id="main" class="przelewy-24">
        <div class="box">
            {if $message}
                <div class="alert alert-success">
                    <span class="message">{$message}</span>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                </div>
            {/if}

            <h1 class="page-heading">
                {l s='My stored cards' mod='przelewy24'}
            </h1>

            <div class="p24-account-card-form" data-translate-error="{l s='Something wrong' mod='przelewy24'}">
                <form action="" method="post">
                    <div>
                        <label>
                            <input type="checkbox"
                                   {if $remember_customer_cards}checked="checked"{/if}
                                   name="remember_credit_cards" value="1">
                            {l s='Memorize payment cards, which I pay' mod='przelewy24'}
                        </label>
                        <p>
                            <button type="submit" name="submit" class="btn btn-default" value="submit">
                                {l s='Save' mod='przelewy24'}
                            </button>
                        </p>
                    </div>
                    <input type="hidden" name="remember_cc_post" value="1">
                </form>
            </div>

            <div class="row p24-account-cards">
                {foreach $customer_cards as $ccard}
                    {if $ccard@first}
                        <div class="col-xs-12">
                            <p>{l s='Your credit cards are listed below.' mod='przelewy24'}</p>
                        </div>
                    {/if}
                    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                        <div class="p24-ccbox">
                            <h1 class="page-heading">{$ccard.card_type}</h1>
                            <p>{$ccard.mask}</p>
                            <p>{$ccard.year}/{$ccard.month}</p>

                            <form action="" method="post"
                                  onsubmit="return confirm('{l s='Are you sure?' js=1 mod='przelewy24'}');">
                                <button type="submit" name="submit" class="btn btn-danger" value="submit">
                                    {l s='Delete' mod='przelewy24'}
                                </button>
                                <input type="hidden" name="remove_card" value="{$ccard.id}">
                            </form>

                        </div>
                    </div>
                    {foreachelse}
                    <div class="col-xs-12">
                        <h3>{l s='Credit cards not found' mod='przelewy24'}</h3>
                    </div>
                {/foreach}
            </div>
            <div id="P24FormAreaHolder" onclick="hidePayJsPopup();showRegisterCardButton();" style="display: none">
                <div onclick="arguments[0].stopPropagation();" id="P24FormArea" class="popup"
                     style="visibility: hidden"></div>
                <div id="p24-card-loader"></div>
                <div id="p24-card-alert" style="display: none"></div>
            </div>

            <p class="p24-account-bottom-nav">
                <a class="btn btn-primary" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
                    {l s='Back to your account' mod='przelewy24'}
                </a>
                <a href="{$urls.base_url}" class="btn btn-primary">
                    {l s='Return to shop' mod='przelewy24'}
                </a>
            </p>
        </div>
    </section>
{/block}
