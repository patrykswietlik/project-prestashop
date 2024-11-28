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

use BlueMedia\OnlinePayments\Util\Formatter;
use BlueMedia\OnlinePayments\Util\Sorter;
use BlueMedia\OnlinePayments\Util\Validator;

/**
 * Model for init transaction
 */
class TransactionInit extends AbstractModel
{
    /**
     * Service id.
     *
     * @var int
     */
    protected $serviceId;

    /**
     * Transaction order id.
     *
     * @var string
     */
    protected $orderId = '';

    /**
     * Transaction amount.
     *
     * @var float
     */
    protected $amount;

    /**
     * Transaction description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Transaction gateway id.
     *
     * @var int|null
     */
    protected $gatewayId;

    /**
     * @var
     */
    protected $acceptanceTime;

    /**
     * @var
     */
    protected $acceptanceState;

    /**
     * @var
     */
    protected $regulationId;

    /**
     * Transaction currency.
     *
     * @var string
     */
    protected $currency = '';

    /**
     * Transaction customer e-mail address.
     *
     * @var string
     */
    protected $customerEmail = '';

    /**
     * Customer IP address.
     *
     * @var string
     */
    protected $customerIp = '';

    /**
     * Transaction title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Hash.
     *
     * @var string
     */
    protected $hash = '';

    /**
     * Transaction validity time.
     *
     * @var \DateTime
     */
    protected $validityTime;

    /**
     * Transaction link validity time.
     *
     * @var \DateTime
     */
    protected $linkValidityTime;

    /**
     * Transaction authorization code.
     *
     * @var string
     */
    protected $authorizationCode = '';

    /**
     * @var string
     */
    protected $screenType = '';

    /**
     * @var string
     */
    protected $platformName = '';

    /**
     * @var string
     */
    protected $platformVersion = '';

    /**
     * @var string
     */
    protected $platformPluginVersion = '';

    public function __construct()
    {
        $this->setPlatformName('PrestaShop');
        $this->setPlatformVersion(_PS_VERSION_);
        $bluepayment = \Module::getInstanceByName('bluepayment');
        $this->setPlatformPluginVersion($bluepayment->version);
    }

    /**
     * @return string
     */
    public function getScreenType(): string
    {
        return $this->screenType;
    }

    /**
     * @param string $screenType
     *
     * @return $this
     */
    public function setScreenType(string $screenType): self
    {
        $this->screenType = $screenType;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string $authorizationCode
     *
     * @return $this
     */
    public function setAuthorizationCode($authorizationCode): self
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Set amount.
     *
     * @param float|number $amount
     *
     * @return $this
     */
    public function setAmount($amount): self
    {
        $amount = (float) Formatter::formatAmount($amount);
        Validator::validateAmount($amount);
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * Return amount.
     *
     * @return string
     */
    public function getAmount(): string
    {
        return Formatter::formatAmount($this->amount);
    }

    /**
     * Set currency.
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency): self
    {
        Validator::validateCurrency($currency);
        $this->currency = (string) mb_strtoupper($currency);

        return $this;
    }

    public function setDefaultRegulationAcceptanceState($state): self
    {
        Validator::validateState($state);
        $this->acceptanceState = (string) mb_strtoupper($state);

        return $this;
    }

    public function getDefaultRegulationAcceptanceState()
    {
        return $this->acceptanceState;
    }

    public function setDefaultRegulationAcceptanceID($state): self
    {
        Validator::validateAcceptanceId($state);
        $this->regulationId = (string) mb_strtoupper($state);

        return $this;
    }

    public function getDefaultRegulationAcceptanceID()
    {
        return $this->regulationId;
    }

    public function setDefaultRegulationAcceptanceTime($time): self
    {
        Validator::validateAcceptanceTime($time);
        $this->acceptanceTime = (string) mb_strtoupper($time);

        return $this;
    }

    public function getDefaultRegulationsAcceptanceTime()
    {
        return $this->acceptanceTime;
    }

    /**
     * Return currency.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set customerEmail.
     *
     * @param string $customerEmail
     *
     * @return $this
     */
    public function setCustomerEmail($customerEmail): self
    {
        Validator::validateEmail($customerEmail);
        $this->customerEmail = (string) mb_strtolower($customerEmail);

        return $this;
    }

    /**
     * Return customerEmail.
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * Set customerIp.
     *
     * @param string $customerIp
     *
     * @return $this
     */
    public function setCustomerIp($customerIp): self
    {
        Validator::validateIP($customerIp);
        $this->customerIp = (string) $customerIp;

        return $this;
    }

    /**
     * Return customerIp.
     *
     * @return string
     */
    public function getCustomerIp(): string
    {
        return $this->customerIp;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description): self
    {
        $description = Formatter::formatDescription($description);
        Validator::validateDescription($description);
        $this->description = (string) $description;

        return $this;
    }

    /**
     * Return description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set gatewayId.
     *
     * @param string $gatewayId
     *
     * @return $this
     */
    public function setGatewayId($gatewayId): self
    {
        Validator::validateGatewayId($gatewayId);
        $this->gatewayId = (string) $gatewayId;

        return $this;
    }

    /**
     * Return gatewayId.
     *
     * @return string|null
     */
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * Set hash.
     *
     * @param string $hash
     *
     * @return $this
     */
    public function setHash($hash): self
    {
        Validator::validateHash($hash);
        $this->hash = (string) $hash;

        return $this;
    }

    /**
     * Return hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Set orderId.
     *
     * @param string $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId): self
    {
        Validator::validateOrderId($orderId);
        $this->orderId = (string) $orderId;

        return $this;
    }

    /**
     * Return orderId.
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Set serviceId.
     *
     * @param int $serviceId
     *
     * @return $this
     */
    public function setServiceId($serviceId): self
    {
        Validator::validateServiceId($serviceId);
        $this->serviceId = (int) $serviceId;

        return $this;
    }

    /**
     * Return serviceId.
     *
     * @return int|null
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title): self
    {
        Validator::validateTitle($title);
        $this->title = (string) $title;

        return $this;
    }

    /**
     * Return title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set linkValidityTime.
     *
     * @param \DateTime $linkValidityTime
     *
     * @return $this
     */
    public function setLinkValidityTime(\DateTime $linkValidityTime): self
    {
        $this->linkValidityTime = $linkValidityTime;

        return $this;
    }

    /**
     * Return linkValidityTime.
     *
     * @return \DateTime
     */
    public function getLinkValidityTime()
    {
        return $this->linkValidityTime;
    }

    /**
     * Set validityTime.
     *
     * @param \DateTime $validityTime
     *
     * @return $this
     */
    public function setValidityTime(\DateTime $validityTime): self
    {
        $this->validityTime = $validityTime;

        return $this;
    }

    /**
     * Return validityTime.
     *
     * @return \DateTime
     */
    public function getValidityTime()
    {
        return $this->validityTime;
    }

    /**
     * Validates model.
     *
     * @return void
     */
    public function validate()
    {
        if (empty($this->serviceId)) {
            throw new \DomainException('ServiceId cannot be empty');
        }
        if (empty($this->orderId)) {
            throw new \DomainException('OrderId cannot be empty');
        }
        if (empty($this->amount)) {
            throw new \DomainException('Amount cannot be empty');
        }
        if (empty($this->hash)) {
            throw new \DomainException('Hash cannot be empty');
        }
    }

    /**
     * @return string
     */
    public function getPlatformName(): string
    {
        return $this->platformName;
    }

    /**
     * @param string $platformName
     */
    public function setPlatformName(string $platformName): self
    {
        $this->platformName = $platformName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformVersion(): string
    {
        return $this->platformVersion;
    }

    /**
     * @param string $platformVersion
     */
    public function setPlatformVersion(string $platformVersion): self
    {
        $this->platformVersion = $platformVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformPluginVersion(): string
    {
        return $this->platformPluginVersion;
    }

    /**
     * @param string $platformPluginVersion
     */
    public function setPlatformPluginVersion(string $platformPluginVersion): self
    {
        $this->platformPluginVersion = $platformPluginVersion;

        return $this;
    }

    /**
     * Return object data as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $result['ServiceID'] = $this->getServiceId();
        $result['OrderID'] = $this->getOrderId();
        $result['Amount'] = $this->getAmount();

        if (!empty($this->getDescription())) {
            $result['Description'] = $this->getDescription();
        }

        if ($this->getGatewayId() !== null) {
            $result['GatewayID'] = $this->getGatewayId();
        }

        if (!empty($this->getCurrency())) {
            $result['Currency'] = $this->getCurrency();
        }

        if (!empty($this->getCustomerEmail())) {
            $result['CustomerEmail'] = $this->getCustomerEmail();
        }

        if (!empty($this->getCustomerIp())) {
            $result['CustomerIP'] = $this->getCustomerIp();
        }

        if (!empty($this->getTitle())) {
            $result['Title'] = $this->getTitle();
        }

        if (!empty($this->getAuthorizationCode())) {
            $result['AuthorizationCode'] = $this->getAuthorizationCode();
        }

        if (!empty($this->getScreenType())) {
            $result['ScreenType'] = $this->getScreenType();
        }

        if (!empty($this->getDefaultRegulationAcceptanceID())) {
            $result['DefaultRegulationAcceptanceID'] = $this->getDefaultRegulationAcceptanceID();
        }

        if (!empty($this->getDefaultRegulationAcceptanceState())) {
            $result['DefaultRegulationAcceptanceState'] = $this->getDefaultRegulationAcceptanceState();
        }

        if (!empty($this->getDefaultRegulationsAcceptanceTime())) {
            $result['DefaultRegulationAcceptanceTime'] = $this->getDefaultRegulationsAcceptanceTime();
        }

        if (!empty($this->getPlatformName())) {
            $result['PlatformName'] = $this->getPlatformName();
        }

        if (!empty($this->getPlatformVersion())) {
            $result['PlatformVersion'] = $this->getPlatformVersion();
        }

        if (!empty($this->getPlatformPluginVersion())) {
            $result['PlatformPluginVersion'] = $this->getPlatformPluginVersion();
        }

        $result['Hash'] = $this->getHash();

        return Sorter::sortTransactionParams($result);
    }
}
