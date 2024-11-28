<?php
/**
 * Class Przelewy24JsonController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24JsonController
 *
 * Common methods for JSON controllers.
 */
class Przelewy24JsonController extends ModuleFrontController
{
    /**
     * Output to be returned by this controller (response method).
     *
     * @var array
     */
    protected $output = [];

    /**
     * Initializes common front page content.
     */
    public function initContent()
    {
        parent::initContent();
        $this->output = [];
    }

    /**
     * Output response.
     *
     * @param int $httpCode
     * @param string $infoMessage
     * @param bool $log
     */
    protected function response($httpCode, $infoMessage = '', $log = true)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json;charset=utf-8');
        if ($log && $infoMessage) {
            Przelewy24Logger::addTruncatedLog($infoMessage);
        }
        $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        echo json_encode($this->output, $options);
        exit;
    }
}
