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
<div class="modal fade" id="bm-helper-alior" tabindex="-1" role="dialog"
     aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2>
            {l s='Alior Raty' mod='bluepayment'}
        </h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body bm-modal-body">

        <div id="blue_payway" class="bluepayment-gateways">
          <div>
            <div class="bm-flex bm-modal-spacing">
                {l s='Pay later' mod='bluepayment'}
              <img width="80" class="img-fluid bm-modal-image"
                   src="/modules/bluepayment/views/img//helpers/switcher1.png">
            </div>
            <p>{l s='Thanks to this payment method, your customers can pay for their purchases in convenient installments, and you will receive the full amount right away. Choosing Alior Installments Your client can decide in how many installments he wants to repay the amount due (from 3 to 48 months).' mod='bluepayment'}</p>

            <p
              class="font-weight-bold">{l s='To be able to promote the Alior Installment payment, you must first activate it' mod='bluepayment'}</p>
          </div>
          <a target="_blank" href="https://developers.autopay.pl/alior-raty#jak-uruchomic-alior-raty" class="btn btn-primary text-uppercase">{l s='Enable the service' mod='bluepayment'}</a>
        </div>

      </div>
    </div>
  </div>
</div>
