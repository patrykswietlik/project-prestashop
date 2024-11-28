<?php
/**
 * Class Przelewy24ErrorResult
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ErrorResult
 */
class Przelewy24ErrorResult
{
    /**
     * Error code.
     *
     * @var int
     */
    private $errorCode;

    /**
     * Error message.
     *
     * @var string
     */
    private $errorMessage;

    /**
     * Error type.
     *
     * @var string
     */
    private $errorType;

    /**
     * Przelewy24ErrorResult constructor.
     *
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $errorType
     */
    public function __construct($errorCode = 0, $errorMessage = '', $errorType = 'general')
    {
        $this->errorCode = (int) $errorCode;
        $this->errorMessage = $errorMessage;
        $this->errorType = $errorType;
    }

    /**
     * Get error code.
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Get error type.
     *
     * @return string
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'errorCode' => $this->getErrorCode(),
            'errorMessage' => $this->getErrorMessage(),
            'errorType' => $this->getErrorType(),
        ];
    }
}
