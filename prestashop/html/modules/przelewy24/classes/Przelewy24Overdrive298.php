<?php
/**
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class Przelewy24Overdrive298
 */
class Przelewy24Overdrive298
{
    const ID = 298;

    /**
     * Get the translation for this overdrive.
     *
     * This function exists due to limitation of a PrestaShop translation system.
     *
     * @param Module $module The Przelewy24 main module class
     * @return string
     */
    private static function getTranslation(Module $module)
    {
        return $module->l('Dostępne tylko w Bank Millenium i VeloBank');
    }

    /**
     * Update payment option to has additional string.
     *
     * @param Module $module The Przelewy24 main module class
     * @param PaymentOption $paymentOption The PaymentOption to update
     * @return void
     */
    public static function updatePaymentOption(Module $module, PaymentOption $paymentOption)
    {
        $oldTitle = $paymentOption->getCallToActionText();
        $newTitle = $oldTitle . ' ' . self::getTranslation($module);
        $paymentOption->setCallToActionText($newTitle);
    }

    /**
     * Return array with extra data to use in template.
     *
     * @param Module $module The Przelewy24 main module class
     * @return array
     */
    public static function getExtraData(Module $module)
    {
        return [
            'extra_text' => self::getTranslation($module),
        ];
    }
}
