/*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*/
function p24onResize() {
    if ($(window).width() <= 640) {
        $('.pay-method-list').addClass('mobile');
    } else {
        $('.pay-method-list').removeClass('mobile');
    }
}


function p24DisplayOrderDetail(extrachargeText, extracharge)
{
    if ('undefined' !== typeof extracharge && 0 !== $('.line-total').length) {
        $('.line-total').before(
            '<tr class="text-xs-right line-shipping" id="extracharge">' +
            '<td colspan="3"><strong>' + extrachargeText + '</strong></td>' +
            '<td  colspan="1">' + extracharge + '</td>' +
            '</tr>');
    }
}
function p24DisplayExtrachargeReturn(extrachargeText, extrachargeReturnFormatted, currency)
{
    if ('undefined' !== typeof currency && 0 !== $('.order-confirmation-table .font-weight-bold').length) {
        $('.order-confirmation-table .font-weight-bold').before(
            '<tr class="line-shipping" id="extracharge">' +
            '<td colspan="1"><strong>' + extrachargeText + '</strong></td>' +
            '<td  colspan="1" class="text-xs-right">' + extrachargeReturnFormatted + ' '+currency+'</td>' +
            '</tr>');
    }
}

function p24DisplayExtrachargeAdmin(extrachargeText, extrachargeReturnFormatted, currency)
{
    if ('undefined' !== typeof currency && 0 !== $('.panel .panel-total .table #total_order').length) {
        $('.panel .panel-total .table #total_order').before(
            '<tr id="extracharge">' +
            '<td class="text-right" colspan="1"><strong>' + extrachargeText + '</strong></td>' +
            '<td  colspan="1" class="amount text-right nowrap">' + extrachargeReturnFormatted + ' '+currency+'</td>' +
            '</tr>');
    }
}
function p24setMethod(method) {
    $('form#przelewy24Form input[name=p24_method]').val(parseInt(method) > 0 ? parseInt(method) : "");
    if (method == 303) {
        $('form#przelewy24Form input[name=p24_channel]').val(parseInt('2048'));
    }
    var $master = $('#master-active-payment-method');
    $master.val(parseInt(method));
    $master.trigger('change');
}

function p24RememberCard(action, data) {
    jQuery.ajax({
        url: action,
        method: 'POST',
        data: data // like: {order: order}
    });
}
// global settings
var formObject = {
    'formAction': '',
    'btnTextSubmit': ''
};
function formSend()
{
    var actionForm = $('.p24-register-card-wrapper').attr('data-action-payment-check');
    $.ajax(actionForm, {
        method: 'POST',
        data: { cartId : $('.p24-register-card-wrapper').attr('data-card-cart-id') }
    }).done(function (data) {
        if (1 === data) {
            location.reload();
        }
    });
    return true
}
$(document).ready(function () {
    $('#przelewy24Form').on('submit', function(){
        var haveInvoice = formSend();

        if(haveInvoice)
        {
            return true;
        }
        return false;
    });

    if (0 !== $('#extracharge_text').length)
    {
        p24DisplayOrderDetail($('#extracharge_text').val(), $('#extracharge').val());
    }
    if (0 !== $('#extrachargeReturn').length)
    {
        p24DisplayExtrachargeReturn($('#extracharge_text').val(), $('#extrachargeReturnFormatted').val(), $('#currencySign').val());
    }
    if (0 !== $('#extracharge').length)
    {
        p24DisplayExtrachargeAdmin($('#extracharge_text').val(), $('#extrachargeFormatted').val(), $('#currencySign').val());
    }

    $( "#P24FormArea" ).click(function( event ) {
        event.stopPropagation();
    });

    $('.bank-box').click(function () {
        var isSelected = false;
        var $btn = $('form#przelewy24Form button[type="submit"]');

        if ($(this).hasClass('selected')) {
            isSelected = true;
        }
        $('.bank-box').removeClass('selected').addClass('inactive');
        if (isSelected) {
            if (formObject.formAction.length) {
                $("form#przelewy24Form").attr('action', formObject.formAction);
                $btn.text(formObject.btnTextSubmit);
                $('.p24-small-text').show();
            }
            p24setMethod(0);

            $('.bank-box').removeClass('inactive');
        } else {
            if ($(this).attr('data-action')) { //
                if (!formObject.formAction) {
                    formObject.formAction = $("form#przelewy24Form").attr('action');
                }

                $("form#przelewy24Form").attr('action', $(this).attr('data-action'));
                $('form input[name="p24_card_customer_id"]').val($(this).attr('data-card-id'));

                var btnText = $btn.attr('data-text-oneclick');
                if (!formObject.btnTextSubmit) {
                    formObject.btnTextSubmit = $btn.text();
                }
                $btn.text(btnText);
                $('.p24-small-text').hide();
            } else {
                p24setMethod($(this).attr('data-id'));
                if (formObject.formAction.length) {
                    $("form#przelewy24Form").attr('action', formObject.formAction);
                    $btn.text(formObject.btnTextSubmit);
                    $('.p24-small-text').show();
                }
            }
            $(this).addClass('selected').removeClass('inactive');
        }
    });

    // show more / less payments method
    $('.p24-more-stuff').click(function () {
        $(this).fadeOut(100, function () {
            $('.p24-less-stuff').fadeIn();
        });
        $('.pay-method-list-second').slideDown();
    });
    $('.p24-less-stuff').click(function () {
        $(this).fadeOut(100, function () {
            $('.p24-more-stuff').fadeIn();
        });
        $('.pay-method-list-second').slideUp();
    });

    //oneClick
    $(".p24-payment-return-page input.p24-remember-my-card").change(function () {
        var action = $(this).attr('data-action');
        var remember = 0;
        if ($(this).is(':checked')) {
            remember = 1;
        }
        var data = {'remember': remember};
        p24RememberCard(action, data);
        if ($('#P24_registerCard').length) {
            if ($('.p24-remember-my-card').is(':checked')) {
                $('#P24_registerCard').prop('checked', true);
            }
            else{
                $('#P24_registerCard').prop('checked', false);
            }
        }

    });
    if ($(".p24-payment-return-page input.p24-remember-my-card:checked")) {
        var action = $("input.p24-remember-my-card:checked").attr('data-action');
        var data = {'remember': 1};
        if (!!action) {
            p24RememberCard(action, data);
        }
    }

    p24onResize();
    if ($('#P24FormArea').length) {
        var targetNode = document.getElementById('P24FormArea');
        var config = {
            attributes: true,
            childList: true
        };
        var callback = function (mutation) {
            try {
                document.getElementById('P24_registerCard').style.display = 'none';
                document.getElementById('register-text').style.display = 'none';
                document.getElementById("P24_registerCard").checked = document.getElementById("p24-remember-my-card").checked;
            } catch(ex) {
            }
        };
        var observer = new MutationObserver(callback);
        observer.observe(targetNode, config);
    }
    if (0 !== $('.refundAmount').length) {
        $('.refundAmount').submit(function () {
            return confirm($('#refundAmountText').val());
        });
    }

    $('#p24_regulation_accept').on('change', function (ev) {
        var $checkbox = $(this);
        $('#submitButton').prop('disabled', !$checkbox.prop('checked'));
    })
});

$(window).resize(function () {
    p24onResize();
});

function p24showLoader() {
    $('.p24-loader').fadeIn(400);
    $('.p24-loader-bg').fadeIn(300);
}

function p24hideLoader() {
    $('.p24-loader').fadeOut(300);
    $('.p24-loader-bg').fadeOut(300);
}

function formSubmit(description) {
    var form = document.getElementById('przelewy24Form');
    var descriptionInput = $("[name='p24_description']");
    if (undefined !== description && null !== description) {
        descriptionInput.val(description);
    }

    form.submit();
}

/* Fix hardcoded images. */
$(function() {
    var rx = /logo_(\d+)/;
    var $images = $('img[src*=\'/template/201312/bank/logo_\']');
    $images.each(function (idx, elm) {
        var $elm = $(elm);
        var $parent = $elm.parent();
        var src = $elm.attr('src');
        var executedRx = rx.exec(src);
        if (!executedRx) {
            return;
        }
        var $newBox = $('<span>').addClass('bank-logo').addClass('bank-logo-' + executedRx[1]);
        $parent.append($newBox);
        $elm.remove();
    })
});
