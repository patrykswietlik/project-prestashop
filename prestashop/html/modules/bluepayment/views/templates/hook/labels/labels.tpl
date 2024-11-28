{*
 * BlueMedia_BluePayment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @category       BlueMedia
 * @package        BlueMedia_BluePayment
 * @copyright      Copyright (c) 2015-2024
 * @license        https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
*}


<div class="bm-promo-icons bm-promo-icons--{$bm_promo_type}">
    {if isset($bm_pay_later) && $bm_pay_later}
		<div class="bm-promo-icons__item">
      <div class="bm-promo-icons__icon">
        <img src="{$bm_assets_images|escape:'html':'UTF-8'}icons/instalments.svg"
             width="25" alt="{l s='INSTALLMENTS' mod='bluepayment'}"/>
      </div>
      <div class="bm-promo-icons__label bm-installments">
        {l s='INSTALLMENTS' mod='bluepayment'}
      </div>
      {if $bm_promo_type != 'sidebar'}
        <div class="bm-promo-icons__desc">
                  {l s='Buy now and pay within 30 days' mod='bluepayment'}
        </div>
      {/if}
		</div>
    {/if}
</div>
