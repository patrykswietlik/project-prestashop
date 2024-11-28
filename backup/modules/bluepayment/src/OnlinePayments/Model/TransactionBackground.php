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

use BlueMedia\OnlinePayments\Util\Validator;

class TransactionBackground extends TransactionInit
{
    /**
     * Receiver bank account number.
     *
     * @required
     *
     * @var string
     */
    protected $receiverNrb = '';

    /**
     * Receiver name.
     *
     * @required
     *
     * @var string
     */
    protected $receiverName = '';

    /**
     * Receiver address.
     *
     * @required
     *
     * @var string
     */
    protected $receiverAddress = '';

    /**
     * Remote order id.
     *
     * @required
     *
     * @var string
     */
    protected $remoteId = '';

    /**
     * Banks system URL.
     *
     * @required
     *
     * @var string
     */
    protected $bankHref = '';

    /**
     * Sets bankHref.
     *
     * @param string $bankHref
     *
     * @return $this
     */
    public function setBankHref($bankHref)
    {
        $this->bankHref = (string) $bankHref;

        return $this;
    }

    /**
     * Returns bankHref.
     *
     * @return string
     */
    public function getBankHref()
    {
        return $this->bankHref;
    }

    /**
     * Sets receiverAddress.
     *
     * @param string $receiverAddress
     *
     * @return $this
     */
    public function setReceiverAddress($receiverAddress)
    {
        $this->receiverAddress = (string) $receiverAddress;

        return $this;
    }

    /**
     * Returns receiverAddress.
     *
     * @return string
     */
    public function getReceiverAddress()
    {
        return $this->receiverAddress;
    }

    /**
     * Sets receiverName.
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
     * Returns receiverName.
     *
     * @return string
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * Sets receiverNrb.
     *
     * @param string $receiverNrb
     *
     * @return $this
     */
    public function setReceiverNrb($receiverNrb)
    {
        Validator::validateNrb($receiverNrb);
        $this->receiverNrb = (string) $receiverNrb;

        return $this;
    }

    /**
     * Returns receiverNrb.
     *
     * @return string
     */
    public function getReceiverNrb()
    {
        return $this->receiverNrb;
    }

    /**
     * Sets remoteId.
     *
     * @param string $remoteId
     *
     * @return $this
     */
    public function setRemoteId($remoteId)
    {
        $this->remoteId = (string) $remoteId;

        return $this;
    }

    /**
     * Returns remoteId.
     *
     * @return string
     */
    public function getRemoteId()
    {
        return $this->remoteId;
    }

    public function validate()
    {
        parent::validate();

        if (empty($this->receiverNrb)) {
            throw new \DomainException('ReceiverNrb cannot be empty');
        }
        if (empty($this->receiverName)) {
            throw new \DomainException('ReceiverName cannot be empty');
        }
        if (empty($this->receiverAddress)) {
            throw new \DomainException('ReceiverAddress cannot be empty');
        }
        if (empty($this->remoteId)) {
            throw new \DomainException('RemoteId cannot be empty');
        }
        if (empty($this->bankHref)) {
            throw new \DomainException('BankHref cannot be empty');
        }
    }

    public function toArray()
    {
        $result = parent::toArray();

        if (!empty($this->getReceiverNrb())) {
            $result['receiverNRB'] = $this->getReceiverNrb();
        }

        if (!empty($this->getReceiverName())) {
            $result['receiverName'] = $this->getReceiverName();
        }

        if (!empty($this->getReceiverAddress())) {
            $result['receiverAddress'] = $this->getReceiverAddress();
        }

        if (!empty($this->getRemoteId())) {
            $result['remoteID'] = $this->getRemoteId();
        }

        if (!empty($this->getBankHref())) {
            $result['bankHref'] = $this->getBankHref();
        }

        return $result;
    }
}
