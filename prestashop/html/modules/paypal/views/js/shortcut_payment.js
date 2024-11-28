/*! For license information please see shortcut_payment.js.LICENSE.txt */
$(document).ready((function(){if($("section#checkout-payment-step").hasClass("js-current-step")){var e=$('input[data-module-name="'.concat(paypalCheckedMethod,'"]'));e.length>0&&(e.click(),$(".payment-options div").hide(),$(".payment-options").append(scPaypalCheckedMsg))}}));
//# sourceMappingURL=shortcut_payment.js.map