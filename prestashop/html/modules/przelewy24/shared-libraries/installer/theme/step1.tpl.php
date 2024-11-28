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
<div class="p24-step p24-step-1">
    <p>
        Instalator - sprawdzenie wymagań wtyczki Przelewy24
    </p>
    <p>
        Proin nibh augue, suscipit a, scelerisque sed, lacinia
        in, mi. Cras vel lorem. Etiam pellentesque aliquet tellus.
    </p>
    <div class="">
        <?php foreach ($content['requirements'] as $key => $requirement) { ?>
            <p>
                <?php echo $requirement['label']; ?>
                -
                <?php if ($requirement['test']) { ?>
                    <span class="">TAK</span>
                <?php } else { ?>
                    <span class="">NIE</span>
                <?php } ?>
            </p>
        <?php } ?>
    </div>
</div>
