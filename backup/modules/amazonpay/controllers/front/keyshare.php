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

class AmazonpayKeyshareModuleFrontController extends ModuleFrontController
{

    /**
     * @var string[]
     */
    protected $response = [
        'result' => ''
    ];

    /**
     * @var string[]
     */
    protected $allowed_origin_urls = [
        'payments.amazon.com',
        'payments-eu.amazon.com',
        'sellercentral.amazon.com',
        'sellercentral-europe.amazon.com'
    ];

    public function init()
    {
        $originHeader = AmazonPayVendorOrigin::get();
        if (!empty($originHeader) && $host = parse_url($originHeader, PHP_URL_HOST)) {
            if (in_array($host, $this->allowed_origin_urls)) {
                header('Access-Control-Allow-Origin: https://' . $host);
            }
        }
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: Content-Type");
        header("Vary: Origin");
        parent::init();
    }

    /**
     * Handle KeyShare automatically
     */
    public function postProcess()
    {
        AmazonPayLogger::getInstance()->addLog(
            'keyShareHandlerURL called',
            3
        );
        $keyShareHandler = new AmazonPayKeyShareHandler();

        if ($keyShareHandler->isValid()) {
            if ($keyShareHandler->decrypt()) {
                if ($keyShareHandler->store()) {
                    $this->response['result'] = 'success';
                } else {
                    $this->setError('Storage of configuration not successful');
                }
            } else {
                $this->setError('Decryption of payload not successful');
            }
        } else {
            $this->setError('Payload not valid');
        }

        if ($this->response['result'] == 'success') {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        header('Content-Type: application/json');
        AmazonPayLogger::getInstance()->addLog(
            'keyShareHandler response',
            3,
            false,
            $this->response
        );
        echo json_encode($this->response);
        die();
    }

    /**
     * @param $string
     */
    private function setError($string)
    {
        $this->response['result'] = 'error';
        $this->response['message'] = $string;
    }
}
