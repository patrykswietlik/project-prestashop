<?php
/**
 * Class Przelewy24BlikErrorEnum
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24BlikErrorEnum
 */
class Przelewy24BlikErrorEnum
{
    const ERR_BLIKCODE_REJECTED = 6;
    const ERR_BLIK_DISABLED = 8;
    const ERR_ALIAS_NOT_CONFIRMED = 11;
    const ERR_ALIAS_UNREGISTERED = 12;
    const ERR_ALIAS_EXPIRED = 13;
    const ERR_ALIAS_NOT_FOUND = 15;
    const ERR_ALIAS_INCORRECT = 20;
    const ERR_TRANSACTION_ALIAS_INCORRECT = 21;
    const ERR_TICKET_INCORRECT = 28;
    const ERR_TICKET_FORMAT = 29;
    const ERR_TICKET_EXPIRED = 30;
    const ERR_TICKET_USED = 35;
    const ERR_ALIAS_NOT_SUPPORTED = 49;
    const ERR_ALIAS_IDENTIFICATION = 51;
    const ERR_TRANSACTION_NOT_CONFIRMED = 55;
    const ERR_LIMIT_EXCEEDED = 60;
    const ERR_INSUFFICIENT_FUNDS = 61;
    const ERR_PIN_DECLINED = 65;
    const ERR_BAD_PIN = 66;
    const ERR_ALIAS_DECLINED = 68;
    const ERR_TIMEOUT = 69;
    const ERR_USER_TIMEOUT = 70;

    /**
     * Controller.
     *
     * @var ModuleFrontController
     */
    private $controller;

    /**
     * Main Przelewy24 module class. Will be used to provide translations.
     *
     * @var Przelewy24
     */
    private $przelewy24;

    /**
     * Przelewy24BlikErrorEnum constructor.
     *
     * @param ModuleFrontController $controller
     */
    public function __construct(ModuleFrontController $controller)
    {
        $this->controller = $controller;
        $this->przelewy24 = new Przelewy24();
    }

    /**
     * 'type' parameter indicates which action should be executed to handle this error.
     *  - success - there is no error, no additional action required
     *  - blikcode - error message will be displayed as error of input "blikCode"
     *  - alias - error message will be handled as alias error (user will be asked to provide blikCode)
     *  - wait - shows no additional information, user will have to wait for
     *       GetTransactionStatus to return proper status (or timeout)
     *  - fatal - transaction error screen will be displayed, transaction status will be changed to "Payment error"
     *
     * @param int $errorCode
     *
     * @return Przelewy24ErrorResult
     */
    public function getErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case 0:
                $type = 'success';
                $message = $this->przelewy24
                    ->getLangString('Success, no error');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_DECLINED:
                $type = 'alias';
                $message = $this->przelewy24->getLangString('Your Blik alias was declined, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_EXPIRED:
                $type = 'alias';
                $message = $this->przelewy24
                    ->getLangString('Your Blik alias was declined, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_IDENTIFICATION:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Identification not possible by given alias');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_INCORRECT:
                $type = 'alias';
                $message = $this->przelewy24->getLangString('Your Blik alias is incorrect, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_NOT_CONFIRMED:
                $type = 'alias';
                $message = $this->przelewy24
                    ->getLangString('Your Blik alias is not confirmed, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_NOT_FOUND:
                $type = 'alias';
                $message = $this->przelewy24->getLangString('Your Blik alias was not found, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_NOT_SUPPORTED:
                $type = 'alias';
                $message = $this->przelewy24->getLangString(
                    'Alias payments are currently not supported, please provide BlikCode'
                );
                break;

            case Przelewy24BlikErrorEnum::ERR_ALIAS_UNREGISTERED:
                $type = 'alias';
                $message = $this->przelewy24
                    ->getLangString('Your Blik alias was unregistered, please provide BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_BAD_PIN:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Bad PIN provided, please generate new BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_BLIK_DISABLED:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Blik service unavailable');
                break;

            case Przelewy24BlikErrorEnum::ERR_BLIKCODE_REJECTED:
                $type = 'blikcode';
                $message = $this->przelewy24
                    ->getLangString('Your BlikCode was rejected, please generate new BlikCode');
                break;

            case Przelewy24BlikErrorEnum::ERR_INSUFFICIENT_FUNDS:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Insufficient funds');
                break;

            case Przelewy24BlikErrorEnum::ERR_LIMIT_EXCEEDED:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Limit exceeded');
                break;

            case Przelewy24BlikErrorEnum::ERR_PIN_DECLINED:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Your PIN was rejected');
                break;

            case Przelewy24BlikErrorEnum::ERR_TIMEOUT:
            case Przelewy24BlikErrorEnum::ERR_USER_TIMEOUT:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Transaction timeout');
                break;

            case Przelewy24BlikErrorEnum::ERR_TICKET_EXPIRED:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Your BlikCode has expired, please generate another');
                break;

            case Przelewy24BlikErrorEnum::ERR_TICKET_FORMAT:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Incorrect BlikCode format, please generate another');
                break;

            case Przelewy24BlikErrorEnum::ERR_TICKET_INCORRECT:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Your BlikCode is incorrect, please generate another');
                break;

            case Przelewy24BlikErrorEnum::ERR_TICKET_USED:
                $type = 'blikcode';
                $message = $this->przelewy24->getLangString('Your BlikCode was already used, please generate another');
                break;

            case Przelewy24BlikErrorEnum::ERR_TRANSACTION_ALIAS_INCORRECT:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Transaction failed, incorrect alias');
                break;

            case Przelewy24BlikErrorEnum::ERR_TRANSACTION_NOT_CONFIRMED:
                $type = 'wait';
                $message = '';
                break;

            default:
                $type = 'fatal';
                $message = $this->przelewy24->getLangString('Blik payment error');
                break;
        }

        return new Przelewy24ErrorResult($errorCode, $message, $type);
    }
}
