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
class Gateway extends AbstractModel
{
    public const GATEWAY_ID_CARD = 1500;
    public const GATEWAY_ID_MTRANSFER = 3;
    public const GATEWAY_ID_MULTITRANSFER = 17;
    public const GATEWAY_ID_BZWBK = 27;
    public const GATEWAY_ID_BPH = 33;
    public const GATEWAY_ID_PEKAO24PRZELEW = 52;
    public const GATEWAY_ID_PEOPAY = 1037;
    public const GATEWAY_ID_CA_ONLINE = 59;
    public const GATEWAY_ID_R_PRZELEW = 76;
    public const GATEWAY_ID_EUROBANK = 79;
    public const GATEWAY_ID_ING = 68;
    public const GATEWAY_ID_MILLENNIUM = 85;
    public const GATEWAY_ID_BOS = 86;
    public const GATEWAY_ID_MERITUM_BANK = 87;
    public const GATEWAY_ID_CITI_HANDLOWY = 90;
    public const GATEWAY_ID_ALIOR_BANK = 95;
    public const GATEWAY_ID_PBS_BANK = 98;
    public const GATEWAY_ID_NETBANK = 99;
    public const GATEWAY_ID_POCZTOWY24 = 108;
    public const GATEWAY_ID_TOYOTA_BANK = 117;
    public const GATEWAY_ID_PLUS_BANK = 131;
    public const GATEWAY_ID_GETIN_BANK = 513;
    public const GATEWAY_ID_DEUTSCHE_BANK = 1002;
    public const GATEWAY_ID_BNP_PARIBAS = 1035;
    public const GATEWAY_ID_IPKO = 1063;
    public const GATEWAY_ID_INTELIGO = 1064;
    public const GATEWAY_ID_IKO = 1065;
    public const GATEWAY_ID_VOLKSWAGEN_BANK = 21;
    public const GATEWAY_ID_SPOLDZIELCZA_GRUPA_BANKOWA = 35;
    public const GATEWAY_ID_BGZ = 71;
    public const GATEWAY_ID_OTHER = 9;
    public const GATEWAY_ID_BLIK = 509;
    public const GATEWAY_ID_BLIK_LATER = 523;
    public const GATEWAY_ID_VISA_CHECKOUT = 1511;
    public const GATEWAY_ID_GOOGLE_PAY = 1512;
    public const GATEWAY_ID_APPLE_PAY = 1513;

    public const GATEWAY_ID_IFRAME = 1506;

    public const GATEWAY_TYPE_PBL = 'PBL';
    public const GATEWAY_TYPE_FAST_TRANSFER = 'Szybki przelew';

    /**
     * Cards gateways.
     *
     * @var array
     */
    private $gatewayTypesCard
        = [
            self::GATEWAY_ID_CARD => 1,
        ];

    /**
     * PBL gateways.
     *
     * @var array
     */
    private $gatewayTypesPbl
        = [
            self::GATEWAY_ID_MTRANSFER => 1,
            self::GATEWAY_ID_MULTITRANSFER => 1,
            self::GATEWAY_ID_BZWBK => 1,
            self::GATEWAY_ID_BPH => 1,
            self::GATEWAY_ID_PEKAO24PRZELEW => 1,
            self::GATEWAY_ID_PEOPAY => 1,
            self::GATEWAY_ID_CA_ONLINE => 1,
            self::GATEWAY_ID_R_PRZELEW => 1,
            self::GATEWAY_ID_EUROBANK => 1,
            self::GATEWAY_ID_ING => 1,
            self::GATEWAY_ID_MILLENNIUM => 1,
            self::GATEWAY_ID_BOS => 1,
            self::GATEWAY_ID_MERITUM_BANK => 1,
            self::GATEWAY_ID_CITI_HANDLOWY => 1,
            self::GATEWAY_ID_ALIOR_BANK => 1,
            self::GATEWAY_ID_PBS_BANK => 1,
            self::GATEWAY_ID_NETBANK => 1,
            self::GATEWAY_ID_POCZTOWY24 => 1,
            self::GATEWAY_ID_TOYOTA_BANK => 1,
            self::GATEWAY_ID_PLUS_BANK => 1,
            self::GATEWAY_ID_GETIN_BANK => 1,
            self::GATEWAY_ID_DEUTSCHE_BANK => 1,
            self::GATEWAY_ID_BNP_PARIBAS => 1,
            self::GATEWAY_ID_IPKO => 1,
            self::GATEWAY_ID_INTELIGO => 1,
            self::GATEWAY_ID_IKO => 1,
            self::GATEWAY_ID_VISA_CHECKOUT => 1,
            self::GATEWAY_ID_GOOGLE_PAY => 1,

            //            self::GATEWAY_ID_SLOVENSKA => 1,
            //            self::GATEWAY_ID_TARTA_BANKA => 1,
            //            self::GATEWAY_ID_VUB_BANKA => 1,
            //            self::GATEWAY_ID_POSTOVA_BANKA => 1,
            //            self::GATEWAY_ID_VIAMO => 1,
        ];

    /**
     * Transfer types.
     *
     * @var array
     */
    private $gatewayTypesTransfer
        = [
            self::GATEWAY_ID_VOLKSWAGEN_BANK => 1,
            self::GATEWAY_ID_SPOLDZIELCZA_GRUPA_BANKOWA => 1,
            self::GATEWAY_ID_BGZ => 1,
            self::GATEWAY_ID_OTHER => 1,
        ];

    /**
     * Gateway id.
     *
     * @var int
     */
    private $gatewayId = 0;

    /**
     * Gateway name.
     *
     * @var string
     */
    private $gatewayName = '';

    /**
     * Gateway type.
     *
     * @var string
     */
    private $gatewayType = '';

    /**
     * Bank name.
     *
     * @var string
     */
    private $bankName = '';

    /**
     * Group.
     *
     * @var string
     */
    private $gatewayPayment = '';

    /**
     * Icon URL.
     *
     * @var string
     */
    private $iconUrl = '';

    /**
     * Status date.
     *
     * @var \DateTime
     */
    private $statusDate;

    /**
     * Min amount.
     *
     * @var float
     */
    private $minAmount;

    /**
     * Max amount.
     *
     * @var float
     */
    private $maxAmount;

    /**
     * Returns gateway id.
     *
     * @return int
     */
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * Sets gateway id.
     *
     * @param int $gatewayId
     *
     * @return $this
     */
    public function setGatewayId($gatewayId)
    {
        $this->gatewayId = (int) $gatewayId;

        return $this;
    }

    /**
     * Returns gateway name.
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * Sets gateway name.
     *
     * @param string $gatewayName
     *
     * @return $this
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = (string) $gatewayName;

        return $this;
    }

    /**
     * Returns gateway type.
     *
     * @return string
     */
    public function getGatewayType()
    {
        return $this->gatewayType;
    }

    /**
     * Sets gateway type.
     *
     * @param string $gatewayType
     *
     * @return $this
     */
    public function setGatewayType($gatewayType)
    {
        $this->gatewayType = (string) $gatewayType;

        return $this;
    }

    /**
     * Returns bank name.
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Sets bank name.
     *
     * @param string $bankName
     *
     * @return $this
     */
    public function setBankName($bankName)
    {
        $this->bankName = (string) $bankName;

        return $this;
    }

    /**
     * Get gateway sub payments.
     *
     * @return string
     */
    public function getGatewayPayment()
    {
        return $this->gatewayPayment;
    }

    /**
     * Sets group
     *
     * @param string $gatewayType
     *
     * @return $this
     */
    public function setGatewayPayment(string $gatewayType)
    {
        $this->gatewayType = (string) $gatewayType;

        return $this;
    }

    /**
     * Returns icon URL.
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->iconUrl;
    }

    /**
     * Sets icon URL.
     *
     * @param string $iconUrl
     *
     * @return $this
     */
    public function setIconUrl($iconUrl)
    {
        $this->iconUrl = (string) $iconUrl;

        return $this;
    }

    /**
     * Returns status date.
     *
     * @return \DateTime|null
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * Sets status date.
     *
     * @param \DateTime $statusDate
     *
     * @return $this
     */
    public function setStatusDate(\DateTime $statusDate)
    {
        $this->statusDate = $statusDate;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * @param float $minAmount
     */
    public function setMinAmount(float $minAmount)
    {
        $this->minAmount = $minAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getMaxAmount()
    {
        return $this->maxAmount;
    }

    /**
     * @param float $maxAmount
     */
    public function setMaxAmount(float $maxAmount)
    {
        $this->maxAmount = $maxAmount;

        return $this;
    }

    /**
     * Is gateway a card.
     *
     * @return bool
     */
    public function isCard()
    {
        return array_key_exists($this->gatewayId, $this->gatewayTypesCard);
    }

    /**
     * Is gateway an PBL.
     *
     * @return bool
     */
    public function isPbl()
    {
        return array_key_exists($this->gatewayId, $this->gatewayTypesPbl);
    }

    /**
     * Is gateway a transfer.
     *
     * @return bool
     */
    public function isTransfer()
    {
        return array_key_exists($this->gatewayId, $this->gatewayTypesTransfer);
    }

    /**
     * Returns information if gateway is given gateway id.
     *
     * @param int $gatewayId
     *
     * @return bool
     */
    public function isGateway($gatewayId)
    {
        return $this->gatewayId === $gatewayId;
    }

    /**
     * Validates model.
     *
     * @throws \DomainException
     */
    public function validate()
    {
        if (empty($this->gatewayId)) {
            throw new \DomainException('GatewayId cannot be empty');
        }
        if (empty($this->gatewayName)) {
            throw new \DomainException('GatewayName cannot be empty');
        }
    }
}
