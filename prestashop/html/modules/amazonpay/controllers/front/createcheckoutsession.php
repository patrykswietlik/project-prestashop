<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazonpayCreatecheckoutsessionModuleFrontController extends ModuleFrontController
{

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        $amazonPayCheckoutSession = new AmazonPayCheckoutSession();
        if ($amazonPayCheckoutSession->checkStatus()) {
            if ($amazonPayCheckoutSession->isInCanceledState($amazonPayCheckoutSession->jsonReturn())) {
                $amazonPayCheckoutSession->createNewCheckoutSession();
            }
            header("Content-type:application/json; charset=utf-8");
            echo $amazonPayCheckoutSession->jsonReturn();
        } else {
            http_response_code(500);
        }
        die();
    }
}
