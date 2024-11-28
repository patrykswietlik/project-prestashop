<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Przelewy24TransactionRegistrationResult
{
    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $signature;

    public function __construct($token = null, $signature = null)
    {
        $this->token = $token;
        $this->signature = $signature;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
