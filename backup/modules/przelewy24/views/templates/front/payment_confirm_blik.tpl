{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
{extends file=$layout}

{block name='content'}
    <section id="main">
        <div class="box">
            <h2>
                <a href="http://przelewy24.pl" target="_blank">
                    <img src="{$logo_url}"
                         alt="{l s='Pay with Przelewy24' mod='przelewy24'}"/>
                </a>
                {l s='Confirm your payment' mod='przelewy24'}
                <p>
                    {l s='Thank you for your purchase. You can track your order in history of orders.' mod='przelewy24'}
                </p>
                <p>
                    <strong>{l s='Please confirm your payment on your mobile device' mod='przelewy24'}</strong>
                </p>
                <p class="cart_navigation">
                    <a href="{$urls.base_url}" class="btn btn-primary">
                        {l s='Return to shop' mod='przelewy24'}
                    </a>
                    <a class="btn btn-primary" href="{$urls.pages.history}">
                        {l s='Show order history' mod='przelewy24'}
                    </a>
                </p>
            </h2>
        </div>
    </section>
{/block}