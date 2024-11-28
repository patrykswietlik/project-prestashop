<?php
/**
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServicePaymentOptions
 */
class Przelewy24PaymentData
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * Cached order for the cart.
     *
     * This variable should be used by dedicated setter.
     * It is lazy loaded.
     *
     * @var array|null
     */
    private $firstOrder;

    /**
     * Cached currency for the cart.
     *
     * This variable should be used by dedicated setter.
     * It is lazy loaded.
     *
     * @var Currency|null
     */
    private $currency;

    /**
     * @var Przelewy24ServicePaymentOptions|null
     */
    private $paymentOptions;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Get cart.
     *
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Check if cart is in database.
     *
     * @return bool
     */
    public function isCartInDb()
    {
        if (!isset($this->cart->id)) {
            return false;
        }

        return (bool) $this->cart->id;
    }

    /**
     * Check if order match module.
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleOfOrderMatched($moduleName)
    {
        $order = $this->getFirstOrder();
        if ($order) {
            return $order['module'] === $moduleName;
        }

        return false;
    }

    /**
     * Check if order has already been placed
     *
     * @return bool result
     */
    public function orderExists()
    {
        $sql = 'SELECT count(*) FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` = ' . (int) $this->cart->id;
        $result = (bool) Db::getInstance()->getValue($sql);

        return $result;
    }

    /**
     * Get the first order bind to the selected cart.
     *
     * @return Order|null Order details
     */
    public function getFirstOrderObject()
    {
        $this->getFirstOrder();

        return new Order($this->getFirstOrderId());
    }

    /**
     * Get the first order bind to the selected cart.
     *
     * @return array|null Order details
     */
    private function getFirstOrder()
    {
        if (!$this->firstOrder) {
            $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `id_cart` = ' . (int) $this->cart->id
                . Shop::addSqlRestriction()
                . ' ORDER BY `id_order` ASC';
            $data = Db::getInstance()->getRow($sql);

            if (!$data) {
                $this->firstOrder = null;
            } else {
                $this->firstOrder = $data;
            }
        }

        return $this->firstOrder;
    }

    /**
     * Get order id.
     *
     * @return int|null
     */
    public function getFirstOrderId()
    {
        $order = $this->getFirstOrder();
        if ($order && $order['id_order']) {
            return (int) $order['id_order'];
        }

        return null;
    }

    /**
     * The reference is the same for all orders from the single cart.
     *
     * @return string|null
     */
    public function getOrderReference()
    {
        $order = $this->getFirstOrder();
        if ($order && $order['reference']) {
            return $order['reference'];
        }

        return null;
    }

    /**
     * Get all orders for the single cart.
     *
     * Returns null if there are no orders.
     *
     * @return PrestaShopCollection|null
     */
    public function getAllOrders()
    {
        $orders = new PrestaShopCollection('Order');
        $orders->where('id_cart', '=', $this->cart->id);
        if (count($orders)) {
            return $orders;
        }

        return null;
    }

    /**
     * Get payments.
     *
     * @return PrestaShopCollection|null
     */
    public function getPayments()
    {
        /* Any order from the same cart is fine. */
        $orderId = $this->getFirstOrderId();
        if ($orderId) {
            $order = new Order($orderId);
            $payments = $order->getOrderPaymentCollection();
        } else {
            $payments = null;
        }

        return $payments;
    }

    /**
     * Return currency for the cart.
     *
     * @return CurrencyCore
     */
    public function getCurrency()
    {
        if (!$this->currency) {
            $this->currency = new Currency($this->cart->id_currency);
        }

        return $this->currency;
    }

    /**
     * Get extracharge from database.
     *
     * @return Przelewy24Extracharge|null
     */
    public function getExtrachargeFromDatabase()
    {
        if ($this->orderExists()) {
            /* For now bind it to the first order in group. */
            return Przelewy24Extracharge::prepareByOrderId($this->getFirstOrderId());
        }

        return null;
    }

    /**
     * Get p24 rounded amount.
     *
     * @param float $amount
     *
     * @return int
     */
    public function formatAmount($amount)
    {
        $currency = $this->getCurrency();
        if ('0' === (string) $currency->decimals && Configuration::hasKey('PS_PRICE_ROUND_MODE')) {
            switch (Configuration::get('PS_PRICE_ROUND_MODE')) {
                case 0:
                    $amount = ceil($amount);
                    break;
                case 1:
                    $amount = floor($amount);
                    break;
                case 2:
                    $amount = round($amount);
                    break;
            }
        }

        return (int) round($amount * 100);
    }

    /**
     * Get extra charge amount.
     *
     * @return float
     */
    public function computeExtrachargeAmount()
    {
        $currency = $this->getCurrency();
        $currencySuffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;
        $amount = $this->cart->getOrderTotal(true, Cart::BOTH);
        $extrachargeStatic = Przelewy24ServicePaymentOptions::getExtrachargeStatic($amount, $currencySuffix);

        return $extrachargeStatic;
    }

    /**
     * Add extra charge.
     *
     * @return bool
     */
    public function addExtracharge()
    {
        if ($this->getExtrachargeFromDatabase()) {
            return false;
        }

        /* This is amount for all orders from the cart. */
        $extrachargeAmount = $this->computeExtrachargeAmount();

        if ($extrachargeAmount) {
            $orderId = $this->getFirstOrderId();
            $order = new Order($orderId);

            $order->total_paid += ($extrachargeAmount / 100);
            $order->total_paid_tax_incl += ($extrachargeAmount / 100);
            $order->total_paid_tax_excl += ($extrachargeAmount / 100);

            try {
                $order->update();

                $extracharge = new Extracharge();
                $extracharge->id_order = (int) $order->id;
                $extracharge->extracharge_amount = ($extrachargeAmount / 100);
                $extracharge->add();

                return true;
            } catch (Exception $e) {
                Przelewy24Logger::addLog(__CLASS__ . ' ' . __METHOD__ . ' ' . $e->getMessage(), 1);
            }
        }

        return false;
    }

    /**
     * Return total paid with extracharge.
     *
     * @return float
     */
    public function getTotalAmountWithExtraCharge()
    {
        if ($this->orderExists()) {
            /* Extracharge should be included. */
            $orders = $this->getAllOrders();
            $totalPaid = 0.0;
            foreach ($orders as $order) {
                $totalPaid = round($totalPaid + $order->total_paid, 2);
            }
        } else {
            $extrachargeAmount = $this->computeExtrachargeAmount();
            $cartTotal = $this->cart->getOrderTotal(true, Cart::BOTH);
            $totalPaid = round($extrachargeAmount + $cartTotal, 2);
        }

        return $totalPaid;
    }

    /**
     * Get total without extra charge
     *
     * @return false|float
     */
    public function getTotalAmountWithoutExtraCharge()
    {
        if ($this->orderExists()) {
            $totalPaid = $this->getTotalAmountWithExtraCharge();
            $extraCharge = $this->getExtrachargeFromDatabase();
            if ($extraCharge) {
                $extraAmount = $extraCharge->extra_charge_amount / 100;
                $amountWithout = round($totalPaid - $extraAmount, 2);
            } else {
                $amountWithout = $totalPaid;
            }

            /* Extracharge should be included. */
            $orders = $this->getAllOrders();
            $totalPaid = 0.0;
            foreach ($orders as $order) {
                $totalPaid = round($totalPaid + $order->total_paid, 2);
            }
        } else {
            $amountWithout = $this->cart->getOrderTotal(true, Cart::BOTH);
        }

        return $amountWithout;
    }

    /**
     * Return shipping costs.
     *
     * @return float
     */
    public function getShippingCosts()
    {
        if ($this->orderExists()) {
            $orders = $this->getAllOrders();
            $shippingCosts = 0.0;
            foreach ($orders as $order) {
                $shippingCosts = round($shippingCosts + $order->total_shipping_tax_incl, 2);
            }
        } else {
            $shippingCostsRaw = $this->cart->getPackageShippingCost((int) $this->cart->id_carrier);
            $shippingCosts = round($shippingCostsRaw, 2);
        }

        return $shippingCosts;
    }

    /**
     * Get sum of discounts.
     *
     * @return float
     */
    public function getDiscounts()
    {
        if ($this->orderExists()) {
            $orders = $this->getAllOrders();
            $totalDiscounts = 0.0;
            foreach ($orders as $order) {
                $totalDiscounts = round($totalDiscounts + $order->total_discounts_tax_incl, 2);
            }
        } else {
            $totalDiscountsRaw = $this->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
            $totalDiscounts = round($totalDiscountsRaw, 2);
        }

        return $totalDiscounts;
    }

    /**
     * Get products.
     *
     * @return array
     */
    public function getProducts()
    {
        if ($this->orderExists()) {
            $orders = $this->getAllOrders();
            $products = [];
            foreach ($orders as $order) {
                $products += $order->getProducts();
            }
        } else {
            $products = $this->cart->getProducts();
        }

        return $products;
    }

    /**
     * Try to get extracharge amount for order in question.
     *
     * Return 0 if order is from different cart.
     *
     * @param $reference
     *
     * @return float
     */
    public function getExtrachargeForOrderReference($reference)
    {
        /* Accept first or only */
        $rex = '/^([^\\#]+)(\\#1)?$/';
        if (preg_match($rex, $reference, $m)) {
            $order = $this->getFirstOrder();
            if ($order['reference'] === $m[1]) {
                $extraCharge = $this->getExtrachargeFromDatabase();

                return $extraCharge->extracharge_amount;
            }
        }

        return 0.0;
    }

    /**
     * Fix extracharge in temporal order representations.
     *
     * In few steps there are temporal order objects without extracharge.
     * This method fix them.
     *
     * @param Order $tmpOrder
     */
    public function fixExtrachargeInTmpOrder(Order $tmpOrder)
    {
        $orderId = (int) $tmpOrder->id;
        if (!$orderId) {
            throw new LogicException('Cannot fix orders without id.');
        }
        $order = new Order($orderId);
        $tmpOrder->total_paid = $order->total_paid;
        $tmpOrder->total_paid_tax_incl = $order->total_paid_tax_incl;
        $tmpOrder->total_paid_tax_excl = $order->total_paid_tax_excl;
    }

    /**
     * @param Przelewy24ServicePaymentOptions $paymentOptions
     */
    public function setPaymentOptons(Przelewy24ServicePaymentOptions $paymentOptions)
    {
        $this->paymentOptions = $paymentOptions;
    }

    /**
     * @return Przelewy24ServicePaymentOptions|null
     */
    public function getPaymentOptons()
    {
        return $this->paymentOptions;
    }

    /**
     * Check if there's more than one order.
     *
     * @return bool
     */
    public function isMultiOrder()
    {
        $orders = $this->getAllOrders();

        if (null === $orders) {
            return false;
        }

        return $orders->count() > 1;
    }

    /**
     * Update statuses on every order that belongs to this particular cart.
     * Returns first order for compatibility
     *
     * @param int $status
     *
     * @return Order
     */
    public function setStateOnOrderCollection($status)
    {
        $firstOrder = null;
        /** @var Order $orderPaid */
        foreach ($this->getAllOrders() as $orderPaid) {
            $orderPaid->setCurrentState($status);
            if ($orderPaid->id === $this->getFirstOrderId()) {
                $firstOrder = $orderPaid;
            }
        }

        return $firstOrder;
    }

    /**
     * Rough but working solution to check if cart has extracharge.
     */
    public function hasExtracharge()
    {
        $extraCharges = [];
        foreach ($this->getAllOrders() as $order) {
            $extraCharge = Przelewy24Extracharge::prepareByOrderId($order->id);
            $extraCharges[] = !empty($extraCharge->extra_charge_amount) ? $extraCharge->extra_charge_amount : 0;
        }

        return array_sum($extraCharges) > 0;
    }
}
