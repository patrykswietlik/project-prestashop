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
<span class="bm-payment__elm bm-payment__promo" data-open-payment="bliklater">
	<span class="bm-promo-desc">
		{l s='Buy now and pay within 30 days. The service is available to Bank Millennium and VeloBank customers.' mod='bluepayment'}
    <a href="#" class="bm-payment__elm bm-transfer" data-bm-modal-siimple data-open-modal-id="bliklater">
      {l s='Learn more.' mod='bluepayment'}
    </a>
	</span>
</span>
<section>
	<span class="bm-small-info">
      	<p>{l s='You will be redirected to a page where you enter your BLIK code. You generate the BLIK code in your banking app.' mod='bluepayment'}</p>
	</span>
</section>
<div id="bliklater" class="bm-modal bm-fade bm-modal-bliklater" tabindex="-1" aria-hidden="true">
  <div class="bm-modal__dialog bm-modal__dialog--centered">
    <div class="bm-modal__content">
      <button type="button" class="bm-modal__close bm-modal__close--custom" data-dismiss="bm-modal" aria-label="{l s='Close' mod='bluepayment'}">
        <img src="{$bm_dir}views/img/close.svg" width="15" alt="{l s='Close' mod='bluepayment'}">
      </button>
      <div class="bm-modal__body">
      <div class="bliklater-modal-content">
        <img class="bliklater-modal-content--logo" alt="{l s='BLIK Pay later' mod='bluepayment'}" src="{$bm_dir}views/img/bliklater-logo.svg">

        <p class="bliklater-modal-content--header">{l s='Buy now, pay in within 30 days' mod='bluepayment'}</p>

        <div class="bliklater-modal-content--items">
          <div class="bliklater-modal-content--item">
            <div class="bliklater-modal-content--item-img-wrapper">
              <img class="bliklater-modal-content--item-img" src="{$bm_dir}views/img/bliklater-img1.svg">
            </div>
            <div class="bliklater-modal-content--item-content">
              <div class="bliklater-modal-content--item-header">{l s='Activation' mod='bluepayment'}</div>
              <div class="bliklater-modal-content--item-text">
                {l s='You choose the BLIK Pay Later option, enter the BLIK code and we offer you a shopping limit of up to PLN 4,000.' mod='bluepayment'}
              </div>
            </div>
          </div>
          <div class="bliklater-modal-content--item">
            <div class="bliklater-modal-content--item-img-wrapper">
              <img class="bliklater-modal-content--item-img" src="{$bm_dir}views/img/bliklater-img2.svg">
            </div>
            <div class="bliklater-modal-content--item-content">
              <div class="bliklater-modal-content--item-header">{l s='Shopping' mod='bluepayment'}</div>
              <div class="bliklater-modal-content--item-text">
                  {l s='You buy products that you do not pay for at the time of purchase. You have time to check if everything suits you.' mod='bluepayment'}
              </div>
            </div>
          </div>
          <div class="bliklater-modal-content--item">
            <div class="bliklater-modal-content--item-img-wrapper">
              <img class="bliklater-modal-content--item-img" src="{$bm_dir}views/img/bliklater-img3.svg">
            </div>
            <div class="bliklater-modal-content--item-right">
              <div class="bliklater-modal-content--item-header">{l s='After every purchase' mod='bluepayment'}</div>
              <div class="bliklater-modal-content--item-text">
                  {l s='Płacisz w ciągu 30 dni bez dodatkowych kosztów. Możesz też zwrócić zakupy w terminie przewidzianym przez sklep.' mod='bluepayment'}
              </div>
            </div>
          </div>
        </div>

        <div class="bliklater-modal-content--footer">
          <span>{l s='Representative example' mod='bluepayment'}</span>
          <a href="#" role="button" class="btn btn-link bliklater-modal-content--footer-link" data-toggle="button" aria-pressed="false">
              <span class="bliklater-modal-content--footer-link-1">{l s='Show more' mod='bluepayment'}</span>
              <span class="bliklater-modal-content--footer-link-2">{l s='Show less' mod='bluepayment'}</span>
          </a>
          <p class="bliklater-modal-content--footer-example">{l s='A representative example of a credit limit of PLN 2,000, which was used in full at one time:' mod='bluepayment'}</p>
          <p class="bliklater-modal-content--footer-example">{l s='If you repay the loan within 30 days: Actual Annual Interest Rate (APR): 0%, total loan amount: PLN 2,000, total amount to be paid: PLN 2,000, fixed interest rate: 0%, total loan cost: PLN 0. Calculation made as of December 6, 2022 on a representative example.' mod='bluepayment'}</p>
        </div>
      </div>
    </div>
    </div>

  </div>
</div>

