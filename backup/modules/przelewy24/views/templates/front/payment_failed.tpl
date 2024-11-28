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
                         alt="{l s='Pay with Przelewy24' mod='przelewy24'}">
                </a>
                {l s='Payment failed!' mod='przelewy24'}
            </h2>
            <div style="text-align: center;">
                <p class="alert alert-danger" style="font-size: 1.4em; font-weight: bold;">
                    {l s='Payment has failed. Try agian.' mod='przelewy24'}
                </p>
            </div>
            <p class="warning">
                {l s='Your payment was not confirmed by Przelewy24. Contact with your seller for more information.' mod='przelewy24'}
            </p>
            {if isset($errorReason)}
                <p>
                    <strong>
                        {$errorReason}
                    </strong>
                </p>
            {/if}
            <p class="cart_navigation">
                <a href="{$urls.base_url}" class="btn btn-primary">
                    {l s='Return to shop' mod='przelewy24'}
                </a>
                <a class="btn btn-primary" href="{$urls.pages.history}">
                    {l s='Show order history' mod='przelewy24'}
                </a>
                {if $extra}
                <a class="btn btn-primary add-to-cart" href="{$extra.redirect_url}">
                    {l s='try again' mod='przelewy24'}
                </a>
                {/if}
            </p>
        </div>
    </section>
{/block}
