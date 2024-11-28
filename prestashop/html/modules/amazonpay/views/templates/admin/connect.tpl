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
{capture name=imgdir_assign assign=imgdir}https://m.media-amazon.com/images/G/01/EPSDocumentation/AmazonPay/Prestashop/img/{/capture}
{capture name=videodir_assign assign=videodir}https://m.media-amazon.com/images/G/01/EPSDocumentation/AmazonPay/Prestashop/video/{/capture}
{capture name=langdir_assign assign=langdir}{if !$language_code|in_array:['de', 'es', 'fr', 'it', 'uk', 'us']}uk{else}{$language_code|escape:'htmlall':'UTF-8'}{/if}{/capture}

<div class="panel">


    <div class="row">
        <div class="col-xs-12 col-md-6">
            <p>
                <img src="{$imgdir|escape:'html':'UTF-8'}amazon-payments.jpg" alt="amazon payments" class="img-responsive" />
            </p>
            <p>
                <strong>{l s='Amazon Pay: Amazon’s Payment and Checkout method for your website' mod='amazonpay'}</strong>
            </p>
            <p>
                {l s='Add Amazon Pay to your website and allow Amazon customers to sign in with their Amazon credentials and easily pay with the address and payment information stored in their Amazon account.' mod='amazonpay'}
                <br />
            </p>
            <p>
                <strong>{l s='Amazon Pay can help you:' mod='amazonpay'}</strong><br />
            <ul>
                <li>{l s='Build customer loyalty' mod='amazonpay'}</li>
                <li>{l s='Attract new customers' mod='amazonpay'}</li>
                <li>{l s='Improve your conversion rate' mod='amazonpay'}</li>
                <li>{l s='Reduce fraud' mod='amazonpay'}</li>
            </ul>
            </p>
            <p><strong>{l s='Simply follow these 4 steps:' mod='amazonpay'}</strong> &nbsp;
                <a style="color:#FF9900;" id="showvideoprestashopyoutube" title="{l s='Watch our video' mod='amazonpay'}"><i class="fa fa-file-video-o" aria-hidden="true"></i>&nbsp;{l s='Watch our video' mod='amazonpay'}</a> <br />
            <ol>
                <li>
                    <a id="simplepathRegTrigger" href='#' onclick="jQuery('#amazonRegForm').submit();">{l s='[1]Registration:[/1] Sign up for an Amazon Payments merchant account.' tags=['<span>'] mod='amazonpay'}</a>
                </li>
                <li>
                    <a id="showstepsetup" href="#" onclick="jQuery('.nav-tabs a[href=#amazonpay_authentication]').tab('show');">{l s='[1]Connection:[/1] Connect your Amazon Payments account with PrestaShop.' tags=['<span>'] mod='amazonpay'}</a>
                </li>
                <li>
                    <a id="showstepconfiguration" href="#" onclick="jQuery('.nav-tabs a[href=#amazonpay_configuration]').tab('show');">{l s='[1]Configuration:[/1] Configure and activate the plugin.' tags=['<span>'] mod='amazonpay'}</a>
                </li>
                <li>
                    <a id="showstepconfiguration" href="#" onclick="jQuery('.nav-tabs a[href=#amazonpay_expertmode]').tab('show');">{l s='[1]Promotion:[/1] Promote Amazon Pay on your website.' tags=['<span>'] mod='amazonpay'}</a>
                </li>
            </ol>
            <p>
                <br />
                <strong>{l s='Important note, before you sign up:' mod='amazonpay'}</strong><br />
                <i class="material-icons mi-announcement" aria-hidden="true" style="vertical-align: text-bottom">announcement</i>&nbsp;
                <span>{l s='Before you start the registration, make sure you sign out of all Amazon accounts you might have.' mod='amazonpay'}</span>
                <br />
                {l s='Use an email address that you have never used for any Amazon account.' mod='amazonpay'}
                <br />
                <span>{l s='If you have an Amazon Seller account (Selling on Amazon), sign out and use a different address to register your Amazon Payments account.' mod='amazonpay'}</span>
            </p>
        </div>
        <div class="col-xs-12 col-md-6">
            <div id="video">
                <table>
                    <tr>
                        <td>
                            <div class="responsive-video" style="display: none;">
                                <iframe id="videoprestashopyoutube" style="vertical-align:top;margin-left:30px;float:left;" width="640" height="360" src="{$youtube_video_embed_link|escape:'html':'UTF-8'}" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>
                            </div>
                            <div id="carrouselAmazonPay" style="vertical-align:top;margin-left:30px;float:left;min-width:205px;max-height:365px;" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    <li data-target="#carrouselAmazonPay" data-slide-to="0" class="active"></li>
                                    <li data-target="#carrouselAmazonPay" data-slide-to="1"></li>
                                    <li data-target="#carrouselAmazonPay" data-slide-to="2"></li>
                                    <li data-target="#carrouselAmazonPay" data-slide-to="3"></li>
                                    <li data-target="#carrouselAmazonPay" data-slide-to="4"></li>
                                </ol>
                                <div class="carousel-inner">
                                    <div class="item active">
                                        <img class="img-fluid" src="{$imgdir|escape:'html':'UTF-8'}{$langdir|escape:'htmlall':'UTF-8'}/carousel/01.jpg" style="min-width:205px;max-height:365px;">
                                    </div>
                                    <div class="item">
                                        <img class="img-fluid" src="{$imgdir|escape:'html':'UTF-8'}{$langdir|escape:'htmlall':'UTF-8'}/carousel/02.jpg" style="min-width:205px;max-height:365px;">
                                    </div>
                                    <div class="item">
                                        <img class="img-fluid" src="{$imgdir|escape:'html':'UTF-8'}{$langdir|escape:'htmlall':'UTF-8'}/carousel/03.jpg" style="min-width:205px;max-height:365px;">
                                    </div>
                                    <div class="item">
                                        <img class="img-fluid" src="{$imgdir|escape:'html':'UTF-8'}{$langdir|escape:'htmlall':'UTF-8'}/carousel/04.jpg" style="min-width:205px;max-height:365px;">
                                    </div>
                                    <div class="item">
                                        <div class="responsive-video"><iframe id="videoamazonyoutube" width="480" height="288" src="{$videoamazonyoutube|escape:'htmlall':'UTF-8'}" frameborder="0" gesture="media" allowfullscreen></iframe></div>
                                    </div>
                                </div>
                                <a class="carousel-control left" href="#carrouselAmazonPay" data-slide="prev">
                                    <span class="glyphicon glyphicon-chevron-left"></span>
                                </a>
                                <a class="carousel-control right" href="#carrouselAmazonPay" data-slide="next">
                                    <span class="glyphicon glyphicon-chevron-right"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="margin-left:30px;margin-top:10px;">
                                <span style="font-size:medium">
                                    <img onclick="jQuery('#amazonRegForm').submit();" src="{$imgdir|escape:'html':'UTF-8'}{$langdir|escape:'htmlall':'UTF-8'}/subscribe.jpg" style="cursor: pointer; height:30px;" />
                                </span>&nbsp; <span style="font-weight: bold; text-transform: uppercase">{l s='or' mod='amazonpay'}</span> &nbsp;
                                <a id="showstepconnect" style="color:#FF9900;cursor: pointer;" aria-hidden="true" onclick="jQuery('#amazontabs a[href=#amazonpay_authentication]').tab('show');">
                                    {l s='Click here if you already have an Amazon Pay account' mod='amazonpay'}
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <form method="POST" action="{$simplepath_form_url}" target="_blank" id="amazonRegForm">
        {foreach from=$simple_path key=spKey item=sp}
            {if is_array($sp)}
                {foreach from=$sp item=subSp}
                    <input type="hidden" value="{$subSp|escape:'htmlall':'UTF-8'}" name="{$spKey|escape:'htmlall':'UTF-8'}[]" />
                {/foreach}
            {else}
                <input type="hidden" value="{$sp|escape:'htmlall':'UTF-8'}" name="{$spKey|escape:'htmlall':'UTF-8'}" />
            {/if}
        {/foreach}
    </form>
</div>
