/**
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
 */

$(window).load(function() {
   if ($('.js-address-form form').length > 0) {
      $('.js-address-form form').attr('action', amazonpay.amazonPayCheckoutAddressFormAction).attr('data-refresh-url', amazonpay.amazonPayCheckoutRefreshAddressFormURL)
   }
   if ($('form#js-delivery').length > 0) {
      $('form#js-delivery').attr('data-url-update', amazonpay.amazonPayCheckoutSetDeliveryOptionURL);
   }
   if ($('.js-edit-addresses').length > 0) {
      amazon.Pay.bindChangeAction('.js-edit-addresses', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changeAddress'
      });
   }
   if ($('#payment-option-1').length > 0) {
      $('#payment-option-1').trigger('click');
   }
   if ($("#checkout-payment-step .step-edit.text-muted").length > 0) {
      $("#checkout-payment-step .step-edit.text-muted").addClass('js-edit-amazon-payment').show();
      amazon.Pay.bindChangeAction('.js-edit-amazon-payment', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changePayment'
      });
   }

   if (amazonpay.isInAmazonPayCheckout == 'true') {
      if ($("body").attr('id') == 'checkout') {
         if ($("#ps_checkout-loader").length > 0) {
            $("#ps_checkout-loader").hide();
         }
      }
   }

   /*
    * PS1.6
    */
   if ($('#address_delivery .address_update a').length > 0) {
      $('#address_delivery .address_update a').attr('href', '#');
      amazon.Pay.bindChangeAction('#address_delivery .address_update a', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changeAddress'
      });
   }
   if ($('#address_invoice .address_update a').length > 0) {
      $('#address_invoice .address_update a').attr('href', '#');
      amazon.Pay.bindChangeAction('#address_invoice .address_update a', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changeAddress'
      });
   }
   if ($('#order .address_add').length > 0) {
      $('#order .address_add').attr('href', '#');
      amazon.Pay.bindChangeAction('#order .address_add', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changeAddress'
      });
   }
   if ($('#order-opc .address_add a.button').length > 0) {
      $('#order-opc .address_add a.button').attr('href', '#');
      amazon.Pay.bindChangeAction('#order-opc .address_add a.button', {
         amazonCheckoutSessionId: amazonpay.amazonCheckoutSessionId,
         changeAction: 'changeAddress'
      });
   }
   if (amazonpay.isInAmazonPayCheckout == 'true') {
      if ($('#opc_account .address_delivery.select.form-group.selector1').length > 0) {
         $('#opc_account .address_delivery.select.form-group.selector1').hide();
         $('#opc_account #address_invoice_form').hide();
         $('.addressesAreEquals').hide();
      }
   }

});
