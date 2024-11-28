<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

namespace BlueMedia\OnlinePayments\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BlueMedia\OnlinePayments\Gateway;
use BlueMedia\OnlinePayments\Util\Sorter;
use BlueMedia\OnlinePayments\Util\Translations;
use BlueMedia\OnlinePayments\Util\Validator;

class TransactionStandard extends TransactionInit
{
    /**
     * Transaction customer bank account number.
     *
     * @var string
     */
    protected $customerNrb = '';

    /**
     * Transaction tax country.
     *
     * @var string
     */
    protected $taxCountry = '';

    /**
     * Transaction receiver name.
     *
     * @var string
     */
    protected $receiverName = '';

    /**
     * BLIK Alias UID key.
     *
     * @var string
     */
    protected $blikUIDKey = '';

    /**
     * BLIK Alias UID label.
     *
     * @var string
     */
    protected $blikUIDLabel = '';

    /**
     * BLIK banks mobile application key.
     *
     * @var string
     */
    protected $blikAMKey = '';

    /**
     * Language used in html form with redirect to BlueMedia paywall
     */
    protected $htmlFormLanguage = 'pl';

    /**
     * Language used in transaction
     */
    protected $language = 'pl';

    /**
     * Set customerNrb.
     *
     * @param string $customerNrb
     *
     * @return $this
     */
    public function setCustomerNrb($customerNrb)
    {
        Validator::validateNrb($customerNrb);
        $this->customerNrb = (string) $customerNrb;

        return $this;
    }

    /**
     * Return customerNrb.
     *
     * @return string
     */
    public function getCustomerNrb()
    {
        return $this->customerNrb;
    }

    /**
     * Set receiverName.
     *
     * @param string $receiverName
     *
     * @return $this
     */
    public function setReceiverName($receiverName)
    {
        Validator::validateReceiverName($receiverName);
        $this->receiverName = (string) $receiverName;

        return $this;
    }

    /**
     * Return receiverName.
     *
     * @return string
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * Set taxCountry.
     *
     * @param string $taxCountry
     *
     * @return $this
     */
    public function setTaxCountry($taxCountry)
    {
        Validator::validateTaxCountry($taxCountry);
        $this->taxCountry = (string) $taxCountry;

        return $this;
    }

    /**
     * Return taxCountry.
     *
     * @return string
     */
    public function getTaxCountry()
    {
        return $this->taxCountry;
    }

    /**
     * @return string
     */
    public function getBlikUIDKey()
    {
        return $this->blikUIDKey;
    }

    /**
     * @param string $blikUIDKey
     *
     * @return TransactionStandard
     */
    public function setBlikUIDKey($blikUIDKey)
    {
        $this->blikUIDKey = $blikUIDKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getBlikUIDLabel()
    {
        return $this->blikUIDLabel;
    }

    /**
     * @param string $blikUIDLabel
     *
     * @return TransactionStandard
     */
    public function setBlikUIDLabel($blikUIDLabel)
    {
        $this->blikUIDLabel = $blikUIDLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getBlikAMKey()
    {
        return $this->blikAMKey;
    }

    /**
     * @param string $blikAMKey
     *
     * @return TransactionStandard
     */
    public function setBlikAMKey($blikAMKey)
    {
        $this->blikAMKey = $blikAMKey;

        return $this;
    }

    /**
     * @param $htmlFormLanguage
     *
     * @return $this
     */
    public function setHtmlFormLanguage($htmlFormLanguage)
    {
        $this->htmlFormLanguage = $htmlFormLanguage;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlFormLanguage()
    {
        return $this->htmlFormLanguage;
    }

    /**
     * @param $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Return object data as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        if (!empty($this->getCustomerNrb())) {
            $result['CustomerNRB'] = $this->getCustomerNrb();
        }

        if (!empty($this->getTaxCountry())) {
            $result['TaxCountry'] = $this->getTaxCountry();
        }

        if (!empty($this->getReceiverName())) {
            $result['ReceiverName'] = $this->getReceiverName();
        }

        if (!empty($this->getBlikUIDKey())) {
            $result['BlikUIDKey'] = $this->getBlikUIDKey();
        }

        if (!empty($this->getBlikUIDLabel())) {
            $result['BlikUIDLabel'] = $this->getBlikUIDLabel();
        }

        if (!empty($this->getBlikAMKey())) {
            $result['BlikAMKey'] = $this->getBlikAMKey();
        }

        if ($this->getValidityTime() instanceof \DateTime) {
            $result['ValidityTime'] = $this->getValidityTime()->format('Y-m-d H:i:s');
        }

        if ($this->getLinkValidityTime() instanceof \DateTime) {
            $result['LinkValidityTime'] = $this->getLinkValidityTime()->format('Y-m-d H:i:s');
        }

        if (!empty($this->getLanguage())) {
            $result['Language'] = strtoupper($this->getLanguage());
        }

        return Sorter::sortTransactionParams($result);
    }

    /**
     * Return HTML form.
     *
     * @return string
     */
    public function getHtmlForm()
    {
        $translation = (new Translations())->getTranslation(
            $this->getHtmlFormLanguage()
        );

        $result = '<p>' . $translation['form.paywall.redirect'] . '</p>' . PHP_EOL;
        $result .= sprintf('<form action="%s" method="post" id="BlueMediaPaymentForm" name="BlueMediaPaymentForm">', Gateway::getActionUrl(Gateway::PAYMENT_ACTON_PAYMENT)) . PHP_EOL;
        foreach ($this->toArray() as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                continue;
            }
            $result .= sprintf('<input type="hidden" name="%s" value="%s" />', $fieldName, $fieldValue) . PHP_EOL;
        }
        $result .= '<input type="submit" />' . PHP_EOL;
        $result .= '</form>' . PHP_EOL;
        $result .= '<script type="text/javascript">document.BlueMediaPaymentForm.submit();</script>';
        $result .= '<noscript><p>' . $translation['form.paywall.javascript_disabled'] . '<br>';
        $result .= $translation['form.paywall.javascript_required'] . '</p></noscript>' . PHP_EOL;

        return $result;
    }
}
