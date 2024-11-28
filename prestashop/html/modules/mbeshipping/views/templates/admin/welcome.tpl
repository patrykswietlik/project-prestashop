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
<div id="mbeshipping_welcome_page"
     class="welcome-page {if Context::getContext()->isMobile()}tab-content col-lg-12 clearfix{/if}">

    {* Banner 1*}
    <div class="row first_banner_welcome">
        <div class="col-xs-12 col-md-6 black_left_banner align-content-center text-center">
            {if !Context::getContext()->isMobile()}
                <div style="clear: both;">
                    <img width="400px" class="logo_welcome_first" src="{$logo_welcome_first|escape:'htmlall':'UTF-8'}"
                         alt="logo_welcome_first"/>
                </div>
                <div style="clear: both;">
                    <img width="400px" class="illustration_welcome_first"
                         src="{$illustration_welcome_first|escape:'htmlall':'UTF-8'}" alt="logo_welcome_first"/>
                </div>
            {else}
                <img width="250px" class="logo_welcome_first" src="{$logo_welcome_first|escape:'htmlall':'UTF-8'}"
                     alt="logo_welcome_first"/>
                <img width="250px" class="illustration_welcome_first"
                     src="{$illustration_welcome_first|escape:'htmlall':'UTF-8'}" alt="logo_welcome_first"/>
            {/if}
        </div>

        {if Context::getContext()->isMobile()}
            <div class="row pt-4 mobile-button">
                <div class="col-xs-6 text-left">
                    <button class="btn btn-register" onclick="registerMbeUser()"
                            id="btn_register_mbe_user">{l s='Register' mod='mbeshipping'}</button>
                </div>
                <div class="col-xs-6 text-right">
                    <button class="btn btn-login" onclick="loginMbeUser()"
                            id="btn_login_mbe_user">{l s='Login' mod='mbeshipping'}</button>
                </div>
            </div>
        {/if}

        <div class="col-xs-12 col-md-6 right_banner pl-4">
            <h2 class="title mb-2 pr-5">{l s='The shipping management platform for your e-commerce.' mod='mbeshipping'}</h2>
            <h4 class="mt-2">
                {l s='Experience seamless ' mod='mbeshipping'}
                <b>{l s='automatic shipment creation' mod='mbeshipping'}</b>
                {l s=' across multiple domestic and international carriers, minimizing errors and saving valuable time in just a few clicks.' mod='mbeshipping'}
                <br><br>
                {l s='With eShip for PrestaShop, you can tailor prices and packing solutions for each order: you can ' mod='mbeshipping'}
                <b>{l s='assign custom prices' mod='mbeshipping'}</b>
                {l s='to each shipping category (destination,dimensions,etc.) and ' mod='mbeshipping'}
                <b>{l s='set the correct package size' mod='mbeshipping'}</b>
                {l s=' for each purchase. Elevate your PrestaShop store today and provide a ' mod='mbeshipping'}
                <b>{l s='smooth shipping experience' mod='mbeshipping'}</b>
                {l s=' to your customers ' mod='mbeshipping'}
                <b>{l s='with eShip for PrestaShop!' mod='mbeshipping'}</b>
            </h4>

            {if !Context::getContext()->isMobile()}
                <div class="buttons my-2 text-left justify-content-start">
                    <div class="pt-3">
                        <button class="btn btn-register" onclick="registerMbeUser()"
                                id="btn_register_mbe_user">{l s='Register' mod='mbeshipping'}</button>
                        <button class="btn btn-login" onclick="loginMbeUser()"
                                id="btn_login_mbe_user">{l s='Login' mod='mbeshipping'}</button>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    {if !Context::getContext()->isMobile()}
        <div class="row second_banner_welcome">
            <div class="title_container_features">
                <h2 class="title title-features">{l s='Features' mod='mbeshipping'}</h2>
            </div>
            {* colonna sinistra *}
            <div class="col-xs-6 row rassicuration_container">
                {* icone *}
                <div class="col-xs-1 mt-2 image_contaiener">
                    <div>
                        <img width="40" height="40" src="{$carriers_icon|escape:'htmlall':'UTF-8'}"
                             alt="carriers_icon">
                    </div>
                    <div>
                        <img width="40" height="40" src="{$configuration_icon|escape:'htmlall':'UTF-8'}"
                             alt="configuration_icon">
                    </div>
                </div>
                {* testi *}
                <div class="col-xs-11 mt-2 pl-3 text_container">
                    <h4>{l s='A select of the best national and international carriers' mod='mbeshipping'}</h4>
                    <h4>{l s='Easy configuration of pricing and packing rules' mod='mbeshipping'}</h4>
                </div>
            </div>
            {* colonna destra *}
            <div class="col-xs-6 row rassicuration_container second">
                {* icone *}
                <div class="col-xs-1 mt-2 image_contaiener">
                    <div>
                        <img width="40" height="40" src="{$customer_service_icon|escape:'htmlall':'UTF-8'}"
                             alt="customer_service_icon">
                    </div>
                    <div>
                        <img width="40" height="40" src="{$returns_icon|escape:'htmlall':'UTF-8'}"
                             alt="returns_icon">
                    </div>
                </div>
                {* testi *}
                <div class="col-xs-11 mt-2 pl-3 text_container">
                    <h4>{l s='Automated return management' mod='mbeshipping'}</h4>
                    <h4>{l s='Free and easy installation with dedicated customer service' mod='mbeshipping'}</h4>
                </div>
            </div>
        </div>
    {else}
        {* MOBILE *}
        {* BANNER 2 *}
        <div class="title_container_features_mobile">
            <h2 class="title title-features">{l s='Features' mod='mbeshipping'}</h2>
        </div>
        <div class="row mobile_banner_2">
            <div class="col-xs-12 mobile_banner_2_down">
                {* colonna 1 *}
                <div class="row">
                    <div class="col-xs-3 row_icon">
                        <img width="40" height="40" src="{$carriers_icon|escape:'htmlall':'UTF-8'}"
                             alt="carriers_icon">
                    </div>
                    <div class="col-xs-9">
                        <h4>{l s='A select of the best national and international carriers' mod='mbeshipping'}</h4>
                    </div>
                </div>
                {* colonna 2 *}
                <div class="row">
                    <div class="col-xs-3 row_icon">
                        <img width="40" height="40" src="{$configuration_icon|escape:'htmlall':'UTF-8'}"
                             alt="configuration_icon">
                    </div>
                    <div class="col-xs-9">
                        <h4>{l s='Easy configuration of pricing and packing rules' mod='mbeshipping'}</h4>
                    </div>
                </div>
                {* colonna 3 *}
                <div class="row">
                    <div class="col-xs-3 row_icon">
                        <img width="40" height="40" src="{$customer_service_icon|escape:'htmlall':'UTF-8'}"
                             alt="customer_service_icon">
                    </div>
                    <div class="col-xs-9">
                        <h4>{l s='Automated return management' mod='mbeshipping'}</h4>
                    </div>
                </div>
                {* colonna 4 *}
                <div class="row">
                    <div class="col-xs-3 row_icon">
                        <img width="40" height="40" src="{$returns_icon|escape:'htmlall':'UTF-8'}"
                             alt="returns_icon">
                    </div>
                    <div class="col-xs-9">
                        <h4>{l s='Free and easy installation with dedicated customer service' mod='mbeshipping'}</h4>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
