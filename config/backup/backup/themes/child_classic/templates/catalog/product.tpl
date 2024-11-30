{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file=$layout}

{block name='head' append}
  <meta property="og:type" content="product">
  {if $product.cover}
    <meta property="og:image" content="{$product.cover.large.url}">
  {/if}

  {if $product.show_price}
    <meta property="product:pretax_price:amount" content="{$product.price_tax_exc}">
    <meta property="product:pretax_price:currency" content="{$currency.iso_code}">
    <meta property="product:price:amount" content="{$product.price_amount}">
    <meta property="product:price:currency" content="{$currency.iso_code}">
  {/if}
  {if isset($product.weight) && ($product.weight != 0)}
  <meta property="product:weight:value" content="{$product.weight}">
  <meta property="product:weight:units" content="{$product.weight_unit}">
  {/if}
{/block}

{block name='head_microdata_special'}
  {include file='_partials/microdata/product-jsonld.tpl'}
{/block}

{block name='content'}

  <section id="main">
    
  <div id="myProductPage">
    <div id="myGallery">
          {block name='page_content_container'}
            <section class="page-content" id="content">
              {block name='page_content'}             
                {block name='product_cover_thumbnails'}
                  {include file='catalog/_partials/product-cover-thumbnails.tpl'}
                {/block}
                <!-- <div class="scroll-box-arrows">
                  <i class="material-icons left">&#xE314;</i>
                  <i class="material-icons right">&#xE315;</i>
                </div> -->
              {/block}
            </section>
          {/block}    
    </div>
    <div id="myProductInspector">

              {block name='breadcrumb'}
                {include file='_partials/breadcrumb.tpl'}
              {/block}
              {block name='page_header_container'}
                {block name='page_header'}
                  <h1>{block name='page_title'}{$product.name}{/block}</h1>
                {/block}
              {/block}

              <div style="width: 100%;">
                <div style="color: #707070;">
                  <span class="stars" style="color: #d24b4b; font-weight: 400 !important; font-size: 16px;">
                    &#9733; &#9733; &#9733; &#9733; &#9733;
                  </span>
                  (<span class="count">3</span> customer reviews)
                </div>
              </div>

              {block name='product_prices'}
                {include file='catalog/_partials/product-prices.tpl'}
              {/block}
              <div class="product-information">
                {block name='product_description_short'}
                  <h6 style="color: #777777;">{block name='page_title'}{$product.name}{/block}</h6>
                  <br/>
                  <div id="product-description-short-{$product.id}" class="product-description">{$product.description_short nofilter}</div>
                {/block}

                {if $product.is_customizable && count($product.customizations.fields)}
                  {block name='product_customization'}
                    {include file="catalog/_partials/product-customization.tpl" customizations=$product.customizations}
                  {/block}
                {/if}
              </div>

              <div class="product-actions js-product-actions">
                {block name='product_buy'}
                  <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                    <input type="hidden" name="token" value="{$static_token}">
                    <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                    <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id" class="js-product-customization-id">

                    {block name='product_variants'}
                      {include file='catalog/_partials/product-variants.tpl'}
                    {/block}

                    {block name='product_add_to_cart'}
                      {include file='catalog/_partials/product-add-to-cart.tpl'}
                    {/block}

                    {* Input to refresh product HTML removed, block kept for compatibility with themes *}
                    {block name='product_refresh'}{/block}
                  </form>
                {/block}

              </div>

              </br>

    </div>
    <div id="myDescriptionReview">
          {block name='product_tabs'}
            <div class="tabs">
              <ul class="nav nav-tabs" role="tablist">
                {if $product.description}
                    <li class="nav-item">
                       <a
                         class="nav-link{if $product.description} active js-product-nav-active{/if}"
                         data-toggle="tab"
                         href="#description"
                         role="tab"
                         aria-controls="description"
                         {if $product.description} aria-selected="true"{/if}>Description</a>
                         <!-- {l s='Description' d='Shop.Theme.Catalog'} -->
                    </li>
                {/if}
                <li class="nav-item">
                  <a
                    class="nav-link{if !$product.description} active js-product-nav-active{/if}"
                    data-toggle="tab"
                    href="#product-details"
                    role="tab"
                    aria-controls="product-details"
                    {if !$product.description} aria-selected="true"{/if}>Additionl informations</a>
                    <!-- {l s='Product Details' d='Shop.Theme.Catalog'} -->
                </li>
                {if $product.attachments}
                  <li class="nav-item">
                    <a
                      class="nav-link"
                      data-toggle="tab"
                      href="#attachments"
                      role="tab"
                      aria-controls="attachments">{l s='Attachments' d='Shop.Theme.Catalog'}</a>
                  </li>
                {/if}
                {foreach from=$product.extraContent item=extra key=extraKey}
                  <li class="nav-item">
                    <a
                      class="nav-link"
                      data-toggle="tab"
                      href="#extra-{$extraKey}"
                      role="tab"
                      aria-controls="extra-{$extraKey}">{$extra.title}</a>
                  </li>
                {/foreach}
                  <li class="nav-item">
                    <a
                      class="nav-link"
                      data-toggle="tab"
                      href="#myReviews"
                      role="tab"
                      aria-controls="comments">reviews (x)</a>
                  </li>
                </ul>

                <div class="tab-content" id="tab-content">
                  <div class="tab-pane fade" id="myReviews" role="tabpanel">
                    //todo
                      {hook h='displayFooterProduct' product=$product category=$category}
                    
                  </div>
                  <div class="tab-pane fade in{if $product.description} active js-product-tab-active{/if}" id="description" role="tabpanel">
                    {block name='product_description'}
                      <div class="product-description">{$product.description nofilter}</div>
                    {/block}
                  </div>

                  {block name='product_details'}
                    {include file='catalog/_partials/product-details.tpl'}
                  {/block}

                  {block name='product_attachments'}
                    {if $product.attachments}
                      <div class="tab-pane fade in" id="attachments" role="tabpanel">
                        <section class="product-attachments">
                          <p class="h5 text-uppercase">{l s='Download' d='Shop.Theme.Actions'}</p>
                          {foreach from=$product.attachments item=attachment}
                            <div class="attachment">
                              <h4><a href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">{$attachment.name}</a></h4>
                              <p>{$attachment.description}</p>
                              <a href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">
                                {l s='Download' d='Shop.Theme.Actions'} ({$attachment.file_size_formatted})
                              </a>
                            </div>
                          {/foreach}
                        </section>
                      </div>
                    {/if}
                  {/block}

                {foreach from=$product.extraContent item=extra key=extraKey}
                <div class="tab-pane fade in {$extra.attr.class}" id="extra-{$extraKey}" role="tabpanel" {foreach $extra.attr as $key => $val} {$key}="{$val}"{/foreach}>
                  {$extra.content nofilter}
                </div>
                {/foreach}
              </div>
            </div>
          {/block}
    </div>
    <div id="myRelatedProducts">
      <h3>RELATED PRODUCTS</h3>
    </div>
  </div>

	

  </section>

{/block}
