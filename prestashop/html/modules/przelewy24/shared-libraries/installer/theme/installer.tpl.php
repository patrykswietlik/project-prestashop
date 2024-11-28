<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$content = []; ?>
<style type="text/css">
    .p24-admin-main-config {
        display: none;
    }

    .p24-step {
        height: 300px;
        background: #eee;
    }

    .p24-step-1 {
    }

    .p24-inputs-group p {
        display: inline-block;
        width: 100%;
    }

    .p24-installer-container label.p24-required span::after {
        color: red;
        content: "*";
    }

    .p24-installer-container label.p24-required.p24-error input {
        border: 1px solid red;
    }
</style>

<script type="text/javascript">
    (function ($) {
        /**
         * Global settings.
         */
        var currentStep = 1;
        var sliderSpeed = 300;
        var stepsDiv = '.p24-installer-steps';
        var maxSteps = <?php echo $content['maxSteps']; ?>;

        function contentStep(number) {
            if (number === undefined || number < 1) {
                number = 1
            }
            var html = '';

            switch (number) {
            <?php $i = 0; ?>
            <?php foreach ($content['steps'] as $step) { ?>
                case <?php echo ++$i; ?>:
                    html = '<?php echo $step; ?>';
                    break;
            <?php } ?>
            }

            return html;
        }

        function loadStep(step) {
            var content = contentStep(step);
            jQuery(stepsDiv).fadeOut(sliderSpeed, function () {
                jQuery(this).html(content);
                jQuery(this).fadeIn(sliderSpeed);

                updateStepCounter();
            });
        }

        function skip() {
            jQuery('.p24-installer-container').fadeOut(sliderSpeed, function () {
                jQuery('.p24-admin-main-config').fadeIn(sliderSpeed);
            });
        }

        function saveInputs() {
            var validInputs = true;
            jQuery('.p24-inputs-group input').each(function () {
                var name = jQuery(this).attr('name');
                var val = jQuery(this).val();
                var valid = true;

                if (jQuery(this).hasClass('p24-valid-crc')) {
                    valid = crcValidator(val);
                }
                if (jQuery(this).hasClass('p24-valid-number')) {
                    valid = numberValidator(val);
                }
                if (valid) {
                    jQuery('.p24-admin-main-config input.' + name).val(val);
                    jQuery(this).parents('label').removeClass('p24-error');
                } else {
                    validInputs = false;
                    jQuery(this).parents('label').addClass('p24-error');
                }
            });
            return validInputs;
        }

        function numberValidator(text) {
            if ('' === text || undefined === text) {
                return false;
            }
            if (!isNumeric(text)) {
                return false;
            }
            var length = text.trim().length;

            if (length < 4 || length > 6) {
                return false;
            }

            return true;
        }

        function crcValidator(text) {
            if ('' === text || undefined === text) {
                return false;
            }
            return true;
        }

        function isNumeric(input) {
            var parsed = parseInt(input);
            return parsed + '' === input + '' && parsed === input - 0;
        }

        function updateStepCounter() {
            var wrapper = '.p24-step-counter';
            jQuery(wrapper).find('.p24-step-current').text(currentStep);
            jQuery(wrapper).find('.p24-step-all').text(maxSteps);
        }

        $(document).ready(function () {
            loadStep(currentStep);
            jQuery('.p24-installer-container a.p24-a').click(function () {
                if (jQuery(this).hasClass('p24-a-next')) {
                    var valid = saveInputs();
                    if (!valid) {
                        return false;
                    }
                    currentStep++;
                } else if (jQuery(this).hasClass('p24-a-back')) {
                    currentStep--;
                } else {
                    skip();
                    return false;
                }
                if (currentStep < 1) {
                    currentStep = 1;
                    return false;
                }
                if (currentStep > maxSteps) {
                    currentStep = maxSteps;
                    skip();
                    return false;
                }
                loadStep(currentStep);
                return false;
            });
        });
    })(jQuery);
</script>

<div class="p24-installer-container">
    <?php $content = []; ?>
    <div class="p24-step-counter">
        <span class="p24-step-current"></span>

        <span class="p24-step-all"></span>
    </div>
    <div class="p24-installer-steps">
    </div>
    <div class="p24-installer-nav">
        <a class="p24-a p24-a-back" href="#">
            Back
        </a>
        <a class="p24-a p24-a-next" href="#">
            Next
        </a>
        <a class="p24-a p24-a-skip" href="#">
            Skip
        </a>
    </div>
</div>
