<?php
/**
 * Class Przelewy24Controller
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24JsonLegacyController
 *
 * The legacy class for code that expext JSON with wrong headers.
 */
class Przelewy24JsonLegacyController extends Przelewy24JsonController
{
    /**
     * Output.
     *
     * @var array
     */
    protected $output = [];

    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();
        $this->output = [];
    }

    /**
     * Legacy response function.
     *
     * The content type is not set to JSON.
     * Some code may require this error.
     *
     * @param int $httpCode
     * @param string $infoMessage
     * @param bool $log
     *
     * @deprecated
     */
    protected function response($httpCode, $infoMessage = '', $log = true)
    {
        http_response_code($httpCode);
        if ($log) {
            Przelewy24Logger::addTruncatedLog($infoMessage);
        }
        echo filter_var(json_encode($this->output), FILTER_SANITIZE_STRING);
        exit;
    }

    /**
     * Logs message.
     *
     * @param int $infoMessage
     */
    protected function log($infoMessage)
    {
        PrestaShopLogger::addLog('Przelewy24Controller - ' . $infoMessage, 1);
    }
}
