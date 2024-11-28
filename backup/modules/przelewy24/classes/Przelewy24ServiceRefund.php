<?php
/**
 * Class Przelewy24ServiceRefund
 *
 * This service has the features required for cash returns.
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServiceRefund
 */
class Przelewy24ServiceRefund extends Przelewy24Service
{
    /**
     * Currency suffix.
     *
     * @var string
     */
    private $suffix = '';

    /**
     * P24 REST api client.
     *
     * @var Przelewy24RestRefund
     */
    private $restApi;

    /**
     * Array of allowed refund statuses.
     *
     * @var array
     */
    private $status = [
        0 => 'Refund error',
        1 => 'Refund done',
        3 => 'Awaiting for refund',
        4 => 'Refund rejected',
    ];

    /**
     * Default refund status.
     *
     * @var string
     */
    private $statusDefault = 'Unknown status of refund';

    /**
     * Przelewy24ServiceRefund constructor.
     *
     * @param Przelewy24 $przelewy24
     * @param string $suffix
     * @param Przelewy24RestRefund $restApi
     */
    public function __construct(Przelewy24 $przelewy24, $suffix, $restApi)
    {
        $this->setSuffix($suffix);
        $this->restApi = $restApi;
        parent::__construct($przelewy24);
    }

    /**
     * Set suffix.
     *
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * Get status message.
     *
     * @param int $status
     *
     * @return string
     */
    public function getStatusMessage($status)
    {
        $status = (int) $status;
        $return = $this->statusDefault;
        if (isset($this->status[$status])) {
            $return = $this->status[$status];
        }

        return $return;
    }

    private function extractDate($input)
    {
        $rx = '/^(?P<Y>\\d{4})(?P<m>\\d{2})(?P<d>\\d{2})/';
        if (preg_match($rx, $input, $m)) {
            return $m['Y'] . '-' . $m['m'] . '-' . $m['d'];
        } else {
            return '';
        }
    }

    /**
     * Get refund data from Przelewy24.
     *
     * @param array $refundDataFromDb
     *
     * @return array
     */
    public function getDataToRefund(array $refundDataFromDb)
    {
        $return = [];
        $order = $this->restApi->transactionBySessionId($refundDataFromDb['sessionId']);
        $refunds = $this->restApi->refundByOrderId($refundDataFromDb['p24OrderId']);
        if (isset($order['data'])) {
            $return = [
                'sessionId' => (string) $order['data']['sessionId'],
                'p24OrderId' => (int) $order['data']['orderId'],
                'originalAmount' => (int) $order['data']['amount'],
                'amount' => null, /* Reserve field position. */
                'refunded' => 0,
                'refunds' => [],
            ];
            if (isset($refunds['data'])) {
                foreach ($refunds['data']['refunds'] as $refund) {
                    $amountRefunded = (int) $refund['amount'];
                    $return['refunded'] += $amountRefunded;
                    $return['refunds'][] = [
                        'amount_refunded' => $amountRefunded,
                        'created' => $this->extractDate($refund['date']),
                        'status' => $this->status[$refund['status']],
                    ];
                }
            }
            $return['amount'] = $return['originalAmount'] - $return['refunded'];
        }

        return $return;
    }

    /**
     * Gets refund data from database.
     *
     * @param $orderId
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public function getRefundDataFromDB($orderId)
    {
        $return = [];

        $orderId = (int) $orderId;
        $przelewy24Order = new Przelewy24Order();
        $result = $przelewy24Order->getByPshopOrderId($orderId);
        if ($result) {
            $return = [
                'sessionId' => $result->p24_session_id,
                'p24OrderId' => $result->p24_order_id,
            ];
        }

        return $return;
    }

    /**
     * Requests refund by Przelewy24.
     *
     * @param string $sessionId
     * @param int $p24OrderId
     * @param int $amountToRefund
     *
     * @return false|stdClass
     */
    public function refundProcess($sessionId, $p24OrderId, $amountToRefund)
    {
        $sessionId = (string) $sessionId;
        $p24OrderId = (int) $p24OrderId;
        $amountToRefund = (int) $amountToRefund;

        $response = $this->restApi->transactionRefund($p24OrderId, $sessionId, $amountToRefund);
        if (isset($response['data'][0])) {
            return $response['data'][0]['amount'] === $amountToRefund;
        }

        return false;
    }

    /**
     * Update products for a refund.
     *
     * @param Order $order A order
     * @param array $products List of products to refund
     * @return bool
     */
    public function updateProductsForRefund($order, $products)
    {
        $ok = true;
        if (Configuration::get('P24_REFUND_WITH_ALTER_STOCK')) {
            $ok &= $this->updateStock($order, $products);
        }
        $ok &= $this->addOrderSlip($order, $products);
        $ok &= $this->updateOrderDetails($order, $products);

        return (bool) $ok;
    }

    /**
     * Update stock.
     *
     * @param Order $order A order
     * @param array $products List of products to refund
     * @return float|null
     */
    private function updateStock($order, $products)
    {
        $totalPrice = 0.0;
        $removed = false;

        $orderProducts = $order->getProductsDetail();

        foreach ($products as $productId => $quantity) {
            if (!$quantity) {
                continue;
            }
            foreach ($orderProducts as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $productId) {
                    $productObject = new Product($orderProduct['product_id']);
                    $stock = new StockAvailable();
                    $stock->updateQuantity(
                        $orderProduct['product_id'],
                        $orderProduct['product_attribute_id'],
                        $quantity
                    );
                    $totalPrice += round($productObject->price * $quantity, 2);
                    $totalPrice = round($totalPrice, 2);
                    $removed = true;

                    break;
                }
            }
        }

        if ($removed) {
            $order->update();
            return $totalPrice;
        } else {
            return null;
        }
    }

    /**
     * Update a order slip.
     *
     * @param Order $order A order
     * @param array $products List of products to refund
     * @return bool
     */
    private function addOrderSlip(Order $order, $products)
    {
        $orderProducts = $order->getProductsDetail();
        $productsToPass = [];
        foreach ($products as $productId => $quantity) {
            if (!$quantity) {
                continue;
            }
            foreach ($orderProducts as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $productId) {
                    $quantity = min((int) $quantity, (int) $orderProduct['product_quantity']);
                    $orderProduct['quantity'] = $quantity;
                    $orderProduct['unit_price'] = $orderProduct['unit_price_tax_excl'];
                    $productsToPass[] = $orderProduct;
                    break;
                }
            }
        }

        return OrderSlip::create($order, $productsToPass);
    }

    /**
     * Update all order dtails.
     *
     * @param Order $order A order
     * @param array $products Array of products to update
     * @return bool True on cucess
     */
    private function updateOrderDetails(Order $order, $products)
    {
        $orderProducts = $order->getProductsDetail();
        $success = true;

        foreach ($products as $productId => $quantity) {
            if (!$quantity) {
                continue;
            }
            $productFound = false;
            foreach ($orderProducts as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $productId) {
                    $quantity = min((int) $quantity, (int) $orderProduct['product_quantity']);
                    if ($quantity) {
                        $this->updateOneLineOfOrderDetails($productId, $quantity);
                    }
                    $productFound = true;
                    break;
                }
            }
            if (!$productFound) {
                $success = false;
            }
        }

        $order->update();
        return $success;
    }

    /**
     * Internal update one order detail.
     *
     * @param int $id_order_detail Id of a order detail
     * @param int $quantity Quantity of a product to refund
     * @return void
     */
    private function updateOneLineOfOrderDetails($id_order_detail, $quantity)
    {
        $orderDetail = new OrderDetail($id_order_detail);

        if (!property_exists($orderDetail, 'total_refunded_tax_incl')) {
            /* Old version. Nothing to do. */
            return;
        }
        if (!property_exists($orderDetail, 'total_refunded_tax_excl')) {
            /* Old version. Nothing to do. */
            return;
        }

        $withTaxUnit = round($orderDetail->unit_price_tax_incl, 2);
        $withoutTaxUnit = round($orderDetail->unit_price_tax_excl, 2);
        $withTax = round($withTaxUnit * $quantity, 2);
        $withoutTax = round($withoutTaxUnit * $quantity, 2);
        $withTaxRefunded = round($orderDetail->total_refunded_tax_incl, 2);
        $withoutTaxRefunded = round($orderDetail->total_refunded_tax_excl, 2);
        $withTaxRefunded = round($withTaxRefunded + $withTax, 2);
        $withoutTaxRefunded = round($withoutTaxRefunded + $withoutTax, 2);
        $orderDetail->total_refunded_tax_incl = $withTaxRefunded;
        $orderDetail->total_refunded_tax_excl = $withoutTaxRefunded;
        $orderDetail->update();
    }

    /**
     * Get list of products that may be refunded.
     *
     * @param Order $order Order that may be refunded
     * @return array List of products
     */
    public function extractProductsDetails($order)
    {
        $context = Context::getContext();
        if (method_exists($context, 'getCurrentLocale')) {
            return $this->extractProductsDetailsModern($order, $context);
        } else {
            return $this->extractProductsDetailsLegacy($order, $context);
        }
    }

    /**
     * Get list of products that may be refunded. Modern version.
     *
     * @param Order $order Order that may be refunded
     * @param Context $context Context of request
     * @return array List of products
     */
    private function extractProductsDetailsModern($order, $context)
    {
        $currency = Currency::getCurrencyInstance($order->id_currency);
        $locale = $context->getCurrentLocale();
        $products = $order->getProductsDetail();

        $ret = [];

        foreach ($products as $product) {
            $line = [
                'productId' => $product['id_order_detail'],
                'name' => $product['product_name'],
                'quantity' => $product['product_quantity'] - $product['product_quantity_refunded'],
                'price' => $product['unit_price_tax_incl'],
                'priceFormatted' => $locale->formatPrice($product['unit_price_tax_incl'], $currency->iso_code),
            ];
            $ret[] = $line;
        }

        return $ret;
    }

    /**
     * Get list of products that may be refunded. Legacy version.
     *
     * It is used for versions older than 1.7.6.
     *
     * @param Order $order Order that may be refunded
     * @param Context $context Context of request
     * @return array List of products
     */
    private function extractProductsDetailsLegacy($order, $context)
    {
        $currency = Currency::getCurrencyInstance($order->id_currency);
        $products = $order->getProductsDetail();

        $ret = [];

        foreach ($products as $product) {
            $line = [
                'productId' => $product['id_order_detail'],
                'name' => $product['product_name'],
                'quantity' => $product['product_quantity'] - $product['product_quantity_refunded'],
                'price' => $product['unit_price_tax_incl'],
                'priceFormatted' => Tools::displayPrice($product['unit_price_tax_incl'], $currency, false, $context),
            ];
            $ret[] = $line;
        }

        return $ret;
    }
}
