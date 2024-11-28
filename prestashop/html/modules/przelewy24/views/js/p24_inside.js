/*
* @author Przelewy24
* @copyright Przelewy24
* @license https://www.gnu.org/licenses/lgpl-3.0.en.html
*/

var p24GetRawAdditionalFormsContainer = function () {
    var idVar = 'p24-additional-forms';
    var rawContainer = document.getElementById(idVar);
    if (!rawContainer) {
        rawContainer = document.createElement('div');
        rawContainer.id = idVar;
        document.body.appendChild(rawContainer);
    }

    return rawContainer;
};

function redirectAfterCardPayment(url)
{
    setTimeout(function() {
        window.location.href = $('#p24-card-config-element').data(url);
    }, 500);
}

function payInShopSuccess() {
    redirectAfterCardPayment('successUrl');
}

function payInShopFailure() {
    redirectAfterCardPayment('failureUrl');
}

/* BLIK */
$(function(){
    var $configElement;
    var armed = false;

    var commonError = function(selector) {
        var $section = $('#p24-blik-section');
        var $error = $section.find(selector);
        $error.show();
        $error.addClass('animate');
        setTimeout(function () {
            $error.removeClass('animate');
        }, 2000);
    };

    var blikError = function() {
        commonError('.error-code');
    };

    var regulationError = function() {
        commonError('.error-regulation');
    };

    var regulationNoError = function() {
        var $section = $('#p24-blik-section');
        var $error = $section.find('.error-regulation');
        $error.hide();
    };

    var prRegulationError = function() {
        commonError('.error-pr-regulation');
    };

    var prRegulationNoError = function() {
        var $section = $('#p24-blik-section');
        var $error = $section.find('.error-pr-regulation');
        $error.hide();
    };

    var executePaymentByBlikCode = function($form, cartId, blikCode) {
        $form.find('button').prop('disabled', true);
        var request = {
            'action': 'executeBlik',
            'cartId': cartId,
            'blikCode': blikCode
        };
        $.ajax($configElement.data('ajaxurl'), {
            method: 'POST', type: 'POST',
            data: request,
        }).success(function (response) {
            var response = JSON.parse(response);
            if (response.success || response.reload) {
                var returnUrl = response.returnUrl;
                /* We are giving few seconds for user to accept transaction. */
                setTimeout(function() {window.location = returnUrl;}, 3000);
            } else {
                blikError();
                $form.find('button').prop('disabled', false);
            }
        }).error(function () {
            blikError();
            $form.find('button').prop('disabled', false);
        });
    };

    var checkRegulations = function ()
    {
        var $regulation;
        $regulation = $('#p24_regulation_accept');
        if (!$regulation.length) {
            $regulation = $('#p24-blik-regulation-accept');
        }
        if (!$regulation.length) {
            /* Not possible on valid site. */
            return false;
        }
        if ($regulation.prop('checked')) {
            regulationNoError();
            return true;
        } else {
            regulationError();
            return false;
        }
    };

    var checkPrRegulations = function ()
    {
        var $regulation;
        $regulation = $('#conditions_to_approve\\[terms-and-conditions\\]');
        if (!$regulation.length) {
            /* Accepted or not needed. */
            return true;
        }
        if ($regulation.prop('checked')) {
            prRegulationNoError();
            return true;
        } else {
            prRegulationError();
            return false;
        }
    };

    var showBlikSection  = function() {
        var $section = $('#p24-blik-section');
        var $pad = $('#p24-additional-forms');
        $section.appendTo($pad);
        $section.show();
        var cartId = $configElement.data('cartid');
        var $form = $section.find('form');
        $form.on('submit', function (e) {
            e.preventDefault();
            var regulations = checkRegulations();
            var prRegulations = checkPrRegulations();
            if (regulations && prRegulations) {
                var blikCode = $section.find('input[name=blik]').val();
                executePaymentByBlikCode($form, cartId, blikCode);
            }
        });
    };

    var hideBlikSection = function () {
        var $section = $('#p24-blik-section');
        $section.hide();
    }

    var tryArmBlikBoxPayment = function() {
        var $masterMethodId = $('#master-active-payment-method');
        $masterMethodId.on('change', function () {
            var val = parseInt($masterMethodId.val());
            if (181 === val) {
                showBlikSection();
            } else {
                hideBlikSection();
            }
        });
        armed = true;
    };

    var tryArmBlikBoxConfirmation = function() {
        /* The id is too random to use. */
        var $input = $('input[data-module-name=przelewy24-method-181]');
        if ($input.length) {
            var rawFormContainer = p24GetRawAdditionalFormsContainer();
            $input.on('change', function (e) {
                if (!$input.prop('checked')) {
                    /* Nothing to do. */
                    return;
                }

                var randomId = $input.attr('id');
                var $container = $('#' + randomId + '-container');
                var $formContainer = $(rawFormContainer);
                $container.append($formContainer);
                $formContainer.trigger('hide-old');
                var hideOldEvent = new Event('hide-old');
                rawFormContainer.dispatchEvent(hideOldEvent);
                showBlikSection();
            });
            rawFormContainer.addEventListener('hide-old', function() {
                hideBlikSection();
            });

            armed = true;
        }
    };

    var tryArmBlikBox = function(retries) {
        if (armed || retries <= 0) {
            return;
        }

        $configElement = $('#p24-blik-config-element');
        if ($configElement.length) {
            var pageType = $configElement.data('pagetype');
            switch (pageType) {
                case 'payment':
                    tryArmBlikBoxPayment();
                    break;
                case 'confirmation':
                    tryArmBlikBoxConfirmation();
                    break;
            }
        }

        if (!armed) {
            setTimeout(tryArmBlikBox, 1000, retries - 1);
        }
    };

    tryArmBlikBox(10);
});

/* Card */
$(function(){
    var $configElement;
    var armed = false;

    var commonError = function(selector) {
        var $section = $('#p24-card-section');
        var $error = $section.find(selector);
        $error.show();
        $error.addClass('animate');
        setTimeout(function () {
            $error.removeClass('animate');
        }, 2000);
    };

    var otherError = function () {
        commonError('error-other');
    }

    var regulationError = function() {
        commonError('.error-regulation');
    };

    var regulationNoError = function() {
        var $section = $('#p24-card-section');
        var $error = $section.find('.error-regulation');
        $error.hide();
    };

    var prRegulationError = function() {
        commonError('.error-pr-regulation');
    };

    var prRegulationNoError = function() {
        var $section = $('#p24-card-section');
        var $error = $section.find('.error-pr-regulation');
        $error.hide();
    };

    var showCardLoader = function () {
        $('.p24-card-loader').fadeIn(400);
    }

    function hideCardLoader() {
        $('.p24-card-loader').fadeOut(300);
    }

    var register = function (cartId, method) {
        if ('object' === typeof P24_Transaction) {
            return;
        }
        var $formContainer = $('#P24FormContainer');
        var termsAcceptRequired = $configElement.data('termsConfirmRequired');
        if (termsAcceptRequired && !$('#p24-card-regulation-accept').is(':checked')) {
            return;
        }
        showCardLoader();
        $.ajax($configElement.data('ajaxurl'), {
            method: 'POST',
            type: 'POST',
            data: {
                action: 'register',
                cartId: cartId,
                method: method,
            },
        }).success(function (response) {
            var response = JSON.parse(response);
            if (response.script && response.signature) {
                $formContainer.attr('data-sign', response.signature);
                $.getScript(response.script, function () {
                    P24_Transaction.init();
                    hideCardLoader();
                });
            } else {
                hideCardLoader();
                commonError();
            }
        }).error(function () {
            hideCardLoader();
            payInShopFailure();
        });
    };

    var checkRegulations = function ()
    {
        var $regulation;
        $regulation = $('#p24_regulation_accept');
        if (!$regulation.length) {
            $regulation = $('#p24-card-regulation-accept');
        }
        if (!$regulation.length) {
            /* Not possible on valid site. */
            return false;
        }
        if ($regulation.prop('checked')) {
            regulationNoError();
            return true;
        } else {
            regulationError();
            return false;
        }
    };

    var checkPrRegulations = function ()
    {
        var $regulation;
        $regulation = $('#conditions_to_approve\\[terms-and-conditions\\]');
        if (!$regulation.length) {
            /* Accepted or not needed. */
            return true;
        }
        if ($regulation.prop('checked')) {
            prRegulationNoError();
            return true;
        } else {
            prRegulationError();
            return false;
        }
    };

    var getMethodId = function() {
        var $masterMethodId = $('#master-active-payment-method');
        var $primaryMethod = $('[data-module-name^="przelewy24-method-"]:checked');
        if ($masterMethodId.length === 0 && $primaryMethod.length > 0) {
            return parseInt($primaryMethod.data('moduleName').replace('przelewy24-method-', ''));
        }

        return parseInt($masterMethodId.val());
    }

    var showCardSection  = function() {
        var $section = $('#p24-card-section');
        var $pad = $('#p24-additional-forms');
        $section.appendTo($pad);
        $section.show();
        register($configElement.data('cartid'), getMethodId());
    };

    var hideCardSection = function () {
        var $section = $('#p24-card-section');
        $section.hide();
    }

    var tryArmCardBoxPayment = function($configElement) {
        var ids = $configElement.data('ids').split(',');

        var $masterMethodId = $('#master-active-payment-method');
        $masterMethodId.on('change', function () {
            var val = parseInt($masterMethodId.val());
            var notSelected = ids.every(function (id) {
                id = parseInt(id);
                return id !== val;
            });
            if (notSelected) {
                hideCardSection();
            } else {
                showCardSection();
            }
        });

        armed = true;
    }

    var tryArmCardBoxConfirmationOne = function(id) {
        /* It could be armed for different card id. */
        if (armed) {
            return;
        }

        /* The id is too random to use. */
        var $input = $('input[data-module-name=przelewy24-method-' + id + ']');
        if ($input.length) {
            var rawFormContainer = p24GetRawAdditionalFormsContainer();
            $input.on('change', function (e) {
                if (!$input.prop('checked')) {
                    /* Nothing to do. */
                    return;
                }

                var randomId = $input.attr('id');
                var $container = $('#' + randomId + '-container');
                var $formContainer = $(rawFormContainer);
                $container.append($formContainer);
                $formContainer.trigger('hide-old');
                var hideOldEvent = new Event('hide-old');
                rawFormContainer.dispatchEvent(hideOldEvent);
                showCardSection();
            });
            rawFormContainer.addEventListener('hide-old', function() {
                hideCardSection();
            });

            armed = true;
        }
    };

    var tryArmCardBoxConfirmation = function() {
        var ids = $configElement.data('ids').split(',');
        ids.forEach(function (id) {
            tryArmCardBoxConfirmationOne(id);
        });
    };

    var tryArmCardBox = function(retries) {
        if (armed || retries <= 0) {
            return;
        }

        $configElement = $('#p24-card-config-element');
        $(document).on('change', '#p24-card-regulation-accept', function() {
            if ($(this).is(':checked')) {
                $('#P24FormContainer').show();
                register($configElement.data('cartid'), getMethodId());
            } else {
                $('#P24FormContainer').hide();
            }
        });
        if ($configElement.length) {
            var pageType = $configElement.data('pagetype');
            switch (pageType) {
                case 'payment':
                    tryArmCardBoxPayment($configElement);
                    break;
                case 'confirmation':
                    tryArmCardBoxConfirmation();
                    break;
            }
        }

        if (!armed) {
            setTimeout(tryArmCardBox, 1000, retries - 1);
        }
    };

    tryArmCardBox(10);
});

/* Other methods: from Przelewy24 and other vendors */
$(function(){
    var armed = false;

    var checkForBlikOrCard = function ($configElement, number) {
        /* This list is not empty. */
        var ids = $configElement.data('cardIds').split(',');
        ids.push('181'); /* Blik */
        number = number.toString();

        return ids.indexOf(number) >= 0;
    };

    var tryArmOtherConfirmation = function ($configElement) {
        var rawFormContainer = p24GetRawAdditionalFormsContainer();
        var paymentOptions = document.querySelectorAll('input[name=payment-option]');
        paymentOptions.forEach(function (elm) {
            var needArming;
            var result = /^przelewy24-method-(\d+)$/.exec(elm.dataset.moduleName);
            if (result) {
                needArming = !checkForBlikOrCard($configElement, parseInt(result[1]));
            } else {
                /* Different module. */
                needArming = true;
            }

            console.log(elm.dataset.moduleName, needArming)

            if (needArming) {
                elm.addEventListener('change', function (e) {
                    var hideOldEvent = new Event('hide-old');
                    rawFormContainer.dispatchEvent(hideOldEvent);
                });
            }
        });

        armed = true;
    };

    var tryArmOther = function(retries) {
        if (armed || retries <= 0) {
            return;
        }

        $configElement = $('#p24-other-config-element');
        if ($configElement.length) {
            var pageType = $configElement.data('pagetype');
            switch (pageType) {
                case 'confirmation':
                    tryArmOtherConfirmation($configElement);
                    break;
            }
        }

        if (!armed) {
            setTimeout(tryArmOther, 1000, retries - 1);
        }
    };

    tryArmOther(10);
});
