<?php
/**
 * Class przelewy24ajaxBlikErrorModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ajaxBlikErrorModuleFrontController
 */
class Przelewy24ajaxBlikErrorModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();
        $output = [];
        if ((Tools::getValue('errorCode') >= 0) || (Tools::getValue('reasonCode') >= 0)) {
            $przelewy24BlikErrorEnum = new Przelewy24BlikErrorEnum($this);

            $errorCode = Tools::getValue('errorCode');
            if (!$errorCode) {
                $errorCode = Tools::getValue('reasonCode');
            }

            /** @var Przelewy24ErrorResult $error */
            $error = $przelewy24BlikErrorEnum->getErrorMessage($errorCode);
            $output['error'] = $error->toArray();
        }

        echo json_encode($output);
        exit;
    }
}
