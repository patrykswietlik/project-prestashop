{*
*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*
*}
{if isset($actionLink)}
    <form action="{$actionLink}" method="get">
        <div style="padding-left:45px;">
            <p>
                {l s='Insert BlikCode number' mod='przelewy24'}
            </p>
            <dl>
                <dd>
                    <input id="p24-blikCode" class="form-control" type="text" name="p24_blik_code"
                           required autocomplete="off" maxlength="6" style="width:200px;">
                </dd>
            </dl>
        </div>
    </form>
{else}
    <section>
        <p>
            {l s='You have existing alias, please proceed with your order' mod='przelewy24'}.
        </p>
    </section>
{/if}