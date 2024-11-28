/**
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
 * @copyright      Copyright (c) 2015-2023
 * @license        https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */
window.addEventListener("DOMContentLoaded", (event) => {
  const promoPayments = document.querySelectorAll('.js-bm-promo-payment label');
  promoPayments.forEach(promoPayment => {
    promoPayment.addEventListener('click', (e) => {
      $('#'+promoPayment.dataset.modal).modal('show');
    });
  });
});
