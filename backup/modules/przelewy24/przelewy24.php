<?php
/**
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'przelewy24/classes/Przelewy24Loader.php';
require_once _PS_MODULE_DIR_ . 'przelewy24/shared-libraries/autoloader.php';

/**
 * Class Przelewy24
 */
class Przelewy24 extends PaymentModule
{
    const TRANSAKCJA_PHP_ID = 'panel/transakcja.php?id=';
    /**
     * Active cart.
     *
     * @var Cart|null
     */
    private $cart;

    /**
     * Cached payment list.
     *
     * @var array
     */
    private $cachedPaymentList;

    /**
     * Variable to store if there is a refund error.
     *
     * @var bool|null
     */
    private $refundError;

    /**
     * Buffer for rendering one box.
     *
     * @var string|null
     */
    private $adminOrderTabContent;

    /**
     * Przelewy24 constructor.
     */
    public function __construct()
    {
        $this->cart = null;

        $this->name = 'przelewy24';
        $this->tab = 'payments_gateways';
        $this->version = '1.3.107';
        $this->author = 'Przelewy24';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->displayName = $this->l('Przelewy24.pl');
        $this->description = $this->l('Przelewy24.pl - Payment Service');
        $this->module_key = 'c5c0cc074d01e2a3f8bbddc744d60fc9';

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('PRZELEWY24')) {
            $this->warning = $this->l('Module is not configured.');
        }

        if ((string) Configuration::get('P24_PLUGIN_VERSION') !== $this->version) {
            $this->clearCache();
        }

        parent::__construct();

        /* This block has to be executed after parent constructor. */
        if ((string) Configuration::get('P24_PLUGIN_VERSION') !== $this->version) {
            $ok = $this->registerAllHooks();
            if ($ok) {
                Configuration::updateValue('P24_PLUGIN_VERSION', $this->version);
            }
        }

        $this->cachedPaymentList = [];
    }

    /**
     * Install.
     *
     * @return bool
     */
    public function install()
    {
        /** @var Przelewy24ServiceInstall */
        $serviceInstall = new Przelewy24ServiceInstall($this);
        $serviceInstall->execute();

        return parent::install()
            && $this->registerAllHooks()
            && Configuration::updateValue('PRZELEWY24', true);
    }

    /**
     * Register all hooks.
     *
     * @return bool
     */
    private function registerAllHooks()
    {
        return $this->registerHook('displayHeader')
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayPaymentReturn')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('actionObjectOrderUpdateAfter')
            && $this->registerHook('displayAdminOrderContentOrder')
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('actionEmailAddAfterContent')
            && $this->registerHook('displayInvoiceLegalFreeText')
            && $this->registerHook('displayAdminOrderLeft')
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('displayAdminOrderTabLink')
            && $this->registerHook('displayAdminOrderTabContent')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayInstallmentPayment')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionValidateOrder');
    }

    /**
     * Uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        if (strstr($_SERVER['REQUEST_URI'], 'reset')) {
            $this->reset();
        }

        if (!parent::uninstall()
            || !Configuration::deleteByName('PRZELEWY24')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Reset.
     */
    public function reset()
    {
        $sql = new DbQuery();
        $sql->select('`name`');
        $sql->from('configuration');
        $sql->where(' `name` LIKE "P24\\\\_%"');

        $configurationPrzelewy24 = Db::getInstance()->executeS($sql->build());

        foreach ($configurationPrzelewy24 as $r) {
            Configuration::deleteByName($r['name']);
        }
    }

    /**
     * Return content for configuration on admin panel.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getContent()
    {
        /**
         * @var AdminController $controller
         */
        $controller = $this->context->controller;
        $controller->addJS(_MODULE_DIR_ . 'przelewy24/views/js/admin.js', 'all');
        $controller->addCSS(_MODULE_DIR_ . 'przelewy24/views/css/przelewy24.css', 'all');

        $serviceAdminForm = new Przelewy24ServiceAdminForm($this);

        return $serviceAdminForm->processSubmit($this->getLangArray()) .
            $serviceAdminForm->displayForm($this->getLangArray());
    }

    /**
     * Add to invoice note add extra charge.
     *
     * @param array $param
     *
     * @return float|string
     */
    public function hookDisplayInvoiceLegalFreeText($param)
    {
        $name = $param['order']->module;
        $this->cart = Cart::getCartByOrderId($param['order']->id);

        if ('przelewy24' !== $name) {
            return '';
        }

        return $this->getTextExtraCharge();
    }

    /**
     * Add to email html template: 'email_extracharge.tpl' if order have extra charge.
     *
     * IMPORTANT original email template has been cut in first '<tr class="conf_body">'.
     *
     * @param array $params
     *
     * @return array $params
     */
    public function hookActionEmailAddAfterContent($params)
    {
        $cart = $this->context->cart;
        $order = $cart ? Order::getByCartId($cart->id) : null;
        if (!$this->active
            || !$order
            || ($order->module !== $this->name)
            || !empty($this->context->controller->errors)
            || (substr_count($params['template'], 'error') > 0)) {
            return $params;
        }
        $extracharge = $this->getFloatExtraChange();
        $textToSearchFor = false !== strpos(
            $params['template_html'],
            '<tr class="conf_body">'
        ) ? '<tr class="conf_body">' : '<tr class="order_summary">';
        $emailHead = strstr($params['template_html'], $textToSearchFor, true);
        $emailFoot = strstr($params['template_html'], $textToSearchFor);
        if ($emailHead) {
            $priceTotal = $cart->getOrderTotal(true, Cart::BOTH);
            $priceTotal += $extracharge;
            $priceTotal = Tools::displayPrice($priceTotal, $this->context->currency, false);
            $emailFoot = str_replace('{total_paid}', $priceTotal, $emailFoot);
            $params['template_html'] = $emailHead . $this->renderExtraChargeDataInMail($extracharge) . $emailFoot;
        }

        return $params;
    }

    /**
     * Hook affecting checkout.
     *
     * @param $param
     *
     * @return mixed
     */
    public function hookDisplayOrderConfirmation($param)
    {
        if ($this->name !== $param['order']->module) {
            return null;
        }

        $this->cart = Cart::getCartByOrderId($param['order']->id);
        $p24paymentData = new Przelewy24PaymentData($this->cart);

        $orderId = $p24paymentData->getFirstOrderId();
        if ($orderId) {
            $order = new Order($orderId);
            $paid = $order->getCurrentOrderState()->paid;
        } else {
            $paid = false;
        }

        $this->getSmarty()->assign(
            [
                'isMultiOrder' => $p24paymentData->isMultiOrder(),
                'isPaid' => $paid,
            ]
        );

        return $this->fetch('module:przelewy24/views/templates/hook/order_confirmation_order_data.tpl');
    }

    /**
     * Prepares html string containing extracharge data, which has to be send in email to user.
     *
     * @param int $extracharge
     *
     * @return string
     */
    private function renderExtraChargeDataInMail($extracharge)
    {
        if (0 === (int) $extracharge) {
            return '';
        }
        $this->getSmarty()->assign(
            [
                'extracharge' => $extracharge,

                'extracharge_formatted' => Tools::displayPrice($extracharge, $this->context->currency, false),

                'extracharge_text' => $this->getLangString('Extra charge was added to this order by Przelewy24: '),
            ]
        );

        return $this->fetch('module:przelewy24/views/templates/hook/email_extracharge.tpl');
    }

    /**
     * Together in true - return string to invoice.
     * Together in false - return float.
     *
     * @return string
     */
    private function getTextExtraCharge()
    {
        $return = '';
        $getExtraChange = $this->getExtraChange();

        if ($getExtraChange['extraChange'] > 0) {
            $return = $this->l('Extracharge was added to this order by Przelewy24: ') .
                ' ' . number_format($getExtraChange['extraChange'], 2, ',', '.') .
                ' ' . $getExtraChange['sign'];
        }

        return $return;
    }

    /**
     * Get float extra charge.
     *
     * @return float
     */
    private function getFloatExtraChange()
    {
        $extracharge = 0;
        $getExtraChange = $this->getExtraChange();

        if ($getExtraChange['extraChange'] > 0) {
            $extracharge = $getExtraChange['extraChange'];
        }

        return round($extracharge, 2);
    }

    /**
     * This method would work for 1.3.40+ version
     * Get extra charge.
     *
     * @return array
     */
    private function getExtraChange()
    {
        $przelewy24ServiceOrderRepeatPayment = new Przelewy24ServiceOrderRepeatPayment($this);
        $orderInformation = $przelewy24ServiceOrderRepeatPayment->execute();
        $przelewy24ServicePaymentOptions = new Przelewy24ServicePaymentOptions($this);
        if ((int) $orderInformation->id > 0) {
            $currency = new Currency($orderInformation->id_currency);
            $sign = $currency->sign;
            $extracharge = $przelewy24ServicePaymentOptions->getExtraChargeOrder((int) $orderInformation->id);
        } else {
            $sign = $this->getContext()->currency->sign;
            $amountTotal = $this->getTemplateVars()['total'];

            $suffix = Przelewy24Helper::getSuffix($this->getContext()->currency->iso_code);
            $extracharge = $przelewy24ServicePaymentOptions->getExtraCharge($amountTotal, $suffix);
        }
        if ($this->cart) {
            $paymentData = new Przelewy24PaymentData($this->cart);
            $sign = $paymentData->getCurrency()->getSign();
            $extracharge = $paymentData->getExtrachargeFromDatabase()->extra_charge_amount / 100;
        }

        return ['extraChange' => $extracharge, 'sign' => $sign];
    }

    /**
     * Get protocol.
     *
     * @return string
     */
    public function getProtocol()
    {
        return 'http' . (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 's' : '') . '://';
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->registerStylesheet(
            'p24-style-inside',
            'modules/przelewy24/views/css/p24_inside.css'
        );

        $this->context->controller->registerJavascript(
            'p24-script-inside',
            'modules/przelewy24/views/js/p24_inside.js'
        );

        if ($this->context->controller instanceof FrontController) {
            $this->context->controller->registerStylesheet(
                'p24-style-local',
                'modules/przelewy24/views/css/przelewy24.css'
            );
        }

        $this->context->controller->registerJavascript(
            'p24-script-common',
            'modules/przelewy24/views/js/przelewy24Common.js'
        );
    }

    protected function canRegisterRemoteScript($controller)
    {
        switch ($controller) {
            case 'product':
                return true;
            default:
                return false;
        }
    }

    protected function getP24Host()
    {
        $p24c = Przelewy24ClassStaticInterfaceFactory::getDefault();

        return $p24c::getHostForEnvironment(false);
    }

    /**
     * Blik code for confirmation page.
     *
     * @param array $params
     */
    public function hookDisplayPaymentTop($params)
    {
        if (isset($params['cart'])) {
            $cart = $params['cart'];
            $useHttps = '1' === Configuration::get('PS_SSL_ENABLED');
            $currency = new Currency($cart->id_currency);
            $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
            $currentYear = (int) date('Y');

            $data = [
                'p24_blik_inside_enable' => (bool) Configuration::get('P24_BLIK_INSIDE_ENABLE' . $suffix),
                'p24_card_inside_enable' => (bool) Configuration::get('P24_PAY_CARD_INSIDE_ENABLE' . $suffix),
                'p24_charge_blik_url' => $this->context->link->getModuleLink(
                    'przelewy24',
                    'chargeBlik',
                    [],
                    $useHttps
                ),
                'p24_charge_card_url' => $this->context->link->getModuleLink(
                    'przelewy24',
                    'chargeCardExt',
                    [],
                    $useHttps
                ),
                'p24_card_ids_string' => implode(',', Przelewy24OneClickHelper::getCardPaymentIds()),
                'p24_card_years' => range($currentYear, $currentYear + 10),
                'p24_card_translation' => $this->getCardLangArray(),
                'p24_page_type' => 'confirmation',
                'p24_cart_id' => (int) $cart->id,
                'p24_blik_needs_term_accept' => true,
                'p24_card_needs_term_accept' => true,
                'p24_failure_url' => $this->context->link->getModuleLink('przelewy24', 'paymentFailed', [], $useHttps),
                'p24_success_url' => Przelewy24TransactionSupport::generateReturnUrl(new Przelewy24PaymentData($cart), true),
            ];

            $this->context->smarty->assign($data);

            $ret = '';
            $hasInside = false;
            if ($data['p24_blik_inside_enable']) {
                $ret .= $this->fetch('module:przelewy24/views/templates/hook/parts/blik_html_code.tpl');
                $hasInside = true;
            }
            if ($data['p24_card_inside_enable']) {
                $ret .= $this->fetch('module:przelewy24/views/templates/hook/parts/card_html_code.tpl');
                $hasInside = true;
            }
            if ($hasInside) {
                $ret .= $this->fetch('module:przelewy24/views/templates/hook/parts/other_html_code.tpl');
            }

            return $ret;
        }
    }

    /**
     * Get cached payment list.
     *
     * @param $suffix
     *
     * @return Przelewy24CachedPaymentList
     */
    private function getCachedPaymentList($suffix)
    {
        if ($suffix === '') {
            $idx = 'PLN';
        } else {
            $idx = $suffix;
        }

        if (!isset($this->cachedPaymentList[$idx])) {
            $this->cachedPaymentList[$idx] = Przelewy24CachedPaymentListFactory::buildForSuffix($suffix);
        }

        return $this->cachedPaymentList[$idx];
    }

    /**
     * Display przelewy24 on /zamowienie page as payment method.
     */
    public function hookPaymentOptions($params)
    {
        /** @var Przelewy24ServicePaymentOptions */
        $text = $this->getLangString('Pay with Przelewy24');
        $servicePaymentOptions = new Przelewy24ServicePaymentOptions($this);

        $cart = Context::getContext()->cart;
        $currency = new Currency($cart->id_currency);
        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
        $cachedList = $this->getCachedPaymentList($suffix);

        $newOptions = $servicePaymentOptions->execute($params, $text, $cachedList);

        return $newOptions;
    }

    /**
     * Display on /potwierdzenie-zamowienia page as block after order details.
     *
     * The name of hook may be misleading.
     *
     * The hook is used to retry payment too.
     *
     * @param $params
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function hookDisplayPaymentReturn($params)
    {
        $controller = Context::getContext()->controller;

        if (!$controller) {
            throw new LogicException('Misused hook.');
        }

        $controller->registerStylesheet('p24-style-local', 'modules/przelewy24/views/css/przelewy24.css');
        $controller->registerJavascript('p24-script-local', 'modules/przelewy24/views/js/przelewy24.js');

        /** @var Przelewy24ServicePaymentReturn */
        $servicePaymentReturn = new Przelewy24ServicePaymentReturn($this);

        if (!$servicePaymentReturn->canBePaid($params)) {
            return '';
        }

        $data = $servicePaymentReturn->execute($params);
        $this->getSmarty()->assign($data);

        return $this->fetch('module:przelewy24/views/templates/hook/payment_block.tpl');
    }

    /**
     * Hook display customer account.
     *
     * @return string
     *
     * @throws Exception
     */
    public function hookDisplayCustomerAccount()
    {
        if (!Przelewy24HookHelper::isStoreConfiguredForSuffix('')) {
            /* Cannot run this hook. */
            return '';
        }
        $smarty = Context::getContext()->smarty;
        if (Przelewy24OneClickHelper::isOneClickEnable() && (null !== $smarty)) {
            $smarty->assign(
                'my_stored_cards_page',
                $this->context->link->getModuleLink('przelewy24', 'accountMyCards')
            );

            return $this->display(__FILE__, 'account_card_display.tpl');
        }
    }

    /**
     * Hook display order detail.
     *
     * @return string
     */
    public function hookDisplayOrderDetail()
    {
        $przelewy24ServiceOrderRepeatPayment = new Przelewy24ServiceOrderRepeatPayment($this);
        $orderInformation = $przelewy24ServiceOrderRepeatPayment->execute();
        $przelewy24ServicePaymentOptions = new Przelewy24ServicePaymentOptions($this);

        $currency = new Currency($orderInformation->id_currency);
        $extracharge = $przelewy24ServicePaymentOptions->getExtraChargeOrder((int) $orderInformation->id);
        $this->getSmarty()->assign(
            [
                'extracharge' => number_format($extracharge, 2),
                'currencySign' => $currency->sign,
            ]
        );

        if (((int) $orderInformation->current_state === (int) Configuration::get('P24_ORDER_STATE_1'))
            || ($extracharge > 0)) {
            if (!$this->getContext()->customer->id) {
                // guest login for repayment
                $customer = new Customer((int) $orderInformation->id_customer);
                Context::getContext()->updateCustomer($customer);
            }

            return $this->fetch('module:przelewy24/views/templates/hook/repeat_payment_return.tpl');
        }
    }

    /**
     * Hook on order edit. It will fix extracharge.
     *
     * @param array $input Input with an Order instance
     * @return void
     */
    public function hookActionObjectOrderUpdateAfter($input)
    {
        /* Protect from entering this method twice. */
        static $in_hook = false;
        if ($in_hook) {
            return;
        }

        $in_hook = true;

        $order = $input['object'] ?? null;
        if (!$order instanceof Order) {
            return;
        }
        $extracharge = Przelewy24Extracharge::prepareByOrderId($order->id);
        if ($extracharge->extra_charge_amount && $extracharge->total_paid) {
            $extra_total_paid = round($extracharge->total_paid, 2);
            $order_total_paid = round($order->total_paid, 2);
            if ($extra_total_paid !== $order_total_paid) {
                $amount = round($extracharge->extra_charge_amount / 100, 2);
                Przelewy24ServicePaymentOptions::addToOrder($order, $amount);
                Przelewy24ServicePaymentOptions::syncExtraChargeToOrder($order, $extracharge);
            }
        }

        $in_hook = false;
    }

    /**
     * Adding an additional fee to the order view in the admin panel.
     *
     * Orders from other modules are ignored.
     *
     * @return string
     */
    public function hookDisplayAdminOrderContentOrder()
    {
        $przelewy24ServiceOrderRepeatPayment = new Przelewy24ServiceOrderRepeatPayment($this);
        $orderInformation = $przelewy24ServiceOrderRepeatPayment->execute();
        $linkViewPartial = '';
        $module = $orderInformation->module;
        if ($module != 'przelewy24') {
            /* Ignore orders from other modules. */
            return $linkViewPartial;
        }

        $this->context->controller->addJS(_MODULE_DIR_ . 'przelewy24/views/js/przelewy24.js', 'all');

        $przelewy24ServicePaymentOptions = new Przelewy24ServicePaymentOptions($this);
        $currency = new Currency($orderInformation->id_currency);
        $extracharge = $przelewy24ServicePaymentOptions->getExtraChargeOrder((int) $orderInformation->id);

        $przelewy24Order = new Przelewy24Order();
        $p24OrderData = $przelewy24Order->getByPshopOrderId((int) $orderInformation->id);
        $p24FullOrderId = isset($p24OrderData->p24_full_order_id) ? $p24OrderData->p24_full_order_id : '';
        $p24Link = $this->createP24Link($p24OrderData, $currency);

        if ($p24OrderData) {
            $p24OrderId = $p24OrderData->p24_order_id;
            $psOrderId = $p24OrderData->pshop_order_id;
            $p24OrderAmount = $p24OrderData->amount ? number_format($p24OrderData->amount / 100, 2, ',', ' ') : null;
            $p24OrderReceived = $p24OrderData->received;
        } else {
            $p24OrderId = null;
            $psOrderId = null;
            $p24OrderAmount = null;
            $p24OrderReceived = null;
        }

        $this->getSmarty()->assign(
            [
                'extracharge' => round($extracharge, 2),
                'extrachargeFormatted' => number_format($extracharge, 2),
                'currencySign' => $currency->sign,
                'extracharge_text' => $this->getLangString('Extracharge Przelewy24'),
                'p24_order_amount' => $p24OrderAmount,
                'p24_order_received' => $p24OrderReceived,
                'p24_order_id' => $p24OrderId,
                'p24_full_order_id' => $p24FullOrderId,
                'ps_order_id' => $psOrderId,
                'link' => $p24Link,
            ]
        );

        if (!empty($p24Link)) {
            $linkViewPartial = $this->display(__FILE__, 'admin_order_full_order_link_view.tpl');
        }

        if ($extracharge > 0) {
            $linkViewPartial .= $this->display(__FILE__, 'admin_order_view.tpl');
        }

        return $linkViewPartial;
    }

    /**
     * Adding on refunds to the order view in the admin panel for Presta 1.7.7
     *
     * This function is run in PHP 7.1 or greater.
     *
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    public function hookDisplayAdminOrderMain($params)
    {
        $orderId = $params['id_order'];
        if (!$orderId) {
            /* Should not happen. */
            return '';
        }
        $order = new Order($orderId);
        if ($order->module && ($order->module !== $this->name)) {
            return '';
        }
        try {
            $displayRefund = $this->prepDataForRefunds($order);
            if ($this->refundError) {
                return $this->display(__FILE__, 'transaction_refund_conf_error_177.tpl');
            }
        } catch (Throwable $ex) {
            PrestaShopLogger::addLog($ex->getMessage());

            return $this->display(__FILE__, 'transaction_refund_conf_error_177.tpl');
        }

        if ($displayRefund) {
            return $this->display(__FILE__, 'transaction_refund_177.tpl');
        }

        return '';
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrderTabLink($params)
    {
        /* We have to render content fist to check if the title is needed. */

        $przelewy24ServiceOrderRepeatPayment = new Przelewy24ServiceOrderRepeatPayment($this);
        $orderInformation = $przelewy24ServiceOrderRepeatPayment->execute();
        $przelewy24ServicePaymentOptions = new Przelewy24ServicePaymentOptions($this);

        $currency = new Currency($orderInformation->id_currency);
        $extracharge = $przelewy24ServicePaymentOptions->getExtraChargeOrder((int) $orderInformation->id);

        $przelewy24Order = new Przelewy24Order();
        $linkViewPartial = '';

        $extrachargeViewData = $this->buildExtrachargeViewData($extracharge, $currency);
        $fullOrderIdViewData = $this->buildFullOrderIdViewData($przelewy24Order, $orderInformation, $currency);

        $templateVariables = $extrachargeViewData + $fullOrderIdViewData;

        $this->getSmarty()->assign($templateVariables);

        if (!empty($fullOrderIdViewData['link'])) {
            $linkViewPartial = $this->display(__FILE__, 'admin_order_tab_full_177.tpl');
        }

        if ($extracharge > 0) {
            $linkViewPartial .= $this->display(__FILE__, 'admin_order_view.tpl');
        }

        /* Save rendered content to variable, may be empty. */
        $this->adminOrderTabContent = $linkViewPartial;

        if ($this->adminOrderTabContent) {
            return $this->display(__FILE__, 'admin_order_tab_link_177.tpl');
        } else {
            return '';
        }
    }

    /**
     * @param $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrderTabContent($params)
    {
        /* The content is rendered in hookDisplayAdminOrderTabLink. */
        return $this->adminOrderTabContent;
    }

    /**
     * Adding on refunds to the order view in the admin panel.
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws Exception
     */
    public function hookDisplayAdminOrderLeft($params)
    {
        if (isset($params['id_order'])) {
            $orderId = (int) $params['id_order'];
        } else {
            return '';
        }
        $order = new Order($orderId);
        if ($order->module && ($order->module !== $this->name)) {
            return '';
        }

        try {
            $displayRefund = $this->prepDataForRefunds($order);
            if ($this->refundError) {
                return $this->display(__FILE__, 'transaction_refund_conf_error.tpl');
            }
        } catch (Exception $ex) {
            PrestaShopLogger::addLog($ex->getMessage());

            return $this->display(__FILE__, 'transaction_refund_conf_error.tpl');
        }

        /* explicit boolean value is intentional */
        if ($displayRefund) {
            $returnValue = $this->display(__FILE__, 'transactionRefund.tpl');
        } else {
            $returnValue = false;
        }

        return $returnValue;
    }

    /**
     * Prepare data for refunds.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    private function prepDataForRefunds(Order $order)
    {
        $this->refundError = false;

        $cookie = Context::getContext()->cookie;

        $orderId = (int) $order->id;

        if (!isset($order->id_currency)) {
            return false;
        }
        if (!$orderId) {
            return false;
        }
        if (!$this->active) {
            return false;
        }

        $currency = new Currency($order->id_currency);
        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);

        $refundApi = Przelewy24RestRefundFactory::buildForSuffix($suffix);

        $przelewy24ServiceRefund = new Przelewy24ServiceRefund($this, $suffix, $refundApi);

        $productsPossible = $przelewy24ServiceRefund->extractProductsDetails($order);

        $refundDataFromDB = $przelewy24ServiceRefund->getRefundDataFromDB($orderId);
        if ($refundDataFromDB) {
            $dataToRefund = $przelewy24ServiceRefund->getDataToRefund($refundDataFromDB);
            if (!$dataToRefund) {
                $this->refundError = true;

                return false;
            }
        } else {
            return false;
        }

        $needReload = false;
        if (Tools::isSubmit('submitRefund') && isset($cookie->refundToken)) {
            $submittedMaximum = (string) Tools::getValue('refundMaximum');
            $expectedMaximum = (string) ($dataToRefund['amount'] / 100);
            if ($expectedMaximum !== $submittedMaximum) {
                $dataToRefund['refundError'] = 'Refund error';
            } elseif (Tools::getValue('refundToken') === $cookie->refundToken) {
                $amountToRefund = (int) round(Tools::getValue('amountToRefund') * 100);
                $refunded = $przelewy24ServiceRefund->refundProcess(
                    $dataToRefund['sessionId'],
                    $dataToRefund['p24OrderId'],
                    $amountToRefund
                );

                if ($refunded) {
                    $productsToRefund = Tools::getValue('refundProductQuantity');
                    $przelewy24ServiceRefund->updateProductsForRefund($order, $productsToRefund);

                    /* Reload data. */
                    $needReload = true;
                } else {
                    $dataToRefund['refundError'] = 'Refund error';
                }
            }
        }
        if (!isset($dataToRefund['refundError'])) {
            $dataToRefund['refundError'] = '';
        }

        $random = hash('sha224', rand());
        $cookie->__set('refundToken', $random);
        $cookie->write();
        $dataToRefund['refundToken'] = $random;
        $dataToRefund['sign'] = $currency->sign;
        $this->context->smarty->assign($dataToRefund);
        $this->context->smarty->assign($dataToRefund);
        $this->context->smarty->assign([
            'productsPossibleToRefund' => $productsPossible,
            'p24NeedReloadRefund' => $needReload,
        ]);

        return true;
    }

    /**
     * Get price variable on cart.
     *
     * @return array
     */
    public function getTemplateVars()
    {
        if (!$this->cart) {
            $this->cart = $this->context->cart;
        }
        $priceTotal = $this->cart->getOrderTotal(true, Cart::BOTH);
        $total = Tools::displayPrice($this->cart->getOrderTotal(true, Cart::BOTH));

        if ((float) $priceTotal <= 0) {
            $priceTotal = 0;
            $total = '0';
        }

        return [
            'checkTotal' => $total,
            'total' => $priceTotal,
            'tax' => $this->getLangString('(tax incl.)'),
        ];
    }

    /**
     * Hook display right column product.
     *
     * Not used for now.
     *
     * @param array $params
     *
     * @return null
     */
    public function hookDisplayProductPriceBlock($params)
    {
        return null;
    }

    /**
     * Hook display under payment option method przelewy24.
     *
     * Not used for now.
     *
     * @param array $params
     *
     * null
     */
    public function hookdisplayInstallmentPayment($params)
    {
        return null;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $script = $this->getPathUri() . 'views/js/admin_common.js?v=' . $this->version;
        $this->context->controller->addJS($script);
    }

    /**
     * Hook action validate order.
     *
     * @param array $params
     */
    public function hookActionValidateOrder($params)
    {
        if (!$this->active) {
            return false;
        }

        if (isset($params['order'], $params['order']->id)) {
            $order = $params['order'];
            $cart = Cart::getCartByOrderId($order->id);
        } else {
            $cart = isset($params['cart']) ? $params['cart'] : $this->context->cart;
            if (!$cart && isset($params['order']) && ($params['order'] instanceof Order)) {
                $cart = new Cart((int) $params['order']->id_cart);
            }
            if ($cart) {
                $orderId = Order::getOrderByCartId($cart->id);
            }
        }

        if (!isset($order) && !isset($orderId)) {
            return;
        }

        if (!isset($order)) {
            $order = new Order($orderId);
        } else {
            $orderId = $order->id;
        }

        if ($order->module && ($order->module !== $this->name)) {
            return;
        }

        $przelewy24ServicePaymentOptions = new Przelewy24ServicePaymentOptions($this);
        if (!$przelewy24ServicePaymentOptions->hasExtrachargeOrder($orderId)) {
            $przelewy24ServicePaymentOptions->setExtracharge($order);
        }
    }

    /**
     * Get context.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get smarty.
     *
     * @return Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return _MODULE_DIR_ . 'przelewy24/';
    }

    /**
     * Get base file.
     *
     * @return string
     */
    public function getBaseFile()
    {
        return __FILE__;
    }

    /**
     * Clears cache.
     */
    public function clearCache()
    {
        $this->_clearCache('payment_return.tpl');
    }

    /**
     * Get language array (translations).
     *
     * @return array
     */
    public function getLangArray()
    {
        $lang = [
            'After selecting products to refund, alter stock quantity' => $this->l('After selecting products to refund, alter stock quantity'),
            'Allows to pay by BLIK without leaving the store website' => $this->l('Allows to pay by BLIK without leaving the store website'),
            'BLIK payments inside shop' => $this->l('BLIK payments inside shop'),
            'Customer will see eRaty Santander Consumer Bank S.A. as a promoted option' => $this->l('Customer will see eRaty Santander Consumer Bank S.A. as a promoted option'),
            'Documentation' => $this->l('Documentation'),
            'Force confirmation page' => $this->l('Force confirmation page'),
            'Link to documentation' => $this->l('Link to documentation'),
            'Payment descriptions' => $this->l('Custom payment descriptions'),
            'Promote eRaty Santander Consumer Bank S.A. payment' => $this->l('Promote eRaty Santander Consumer Bank S.A. payment'),
            'Promote the eRaty Santander Consumer Bank S.A. payment in methods' => $this->l('Promote the eRaty Santander Consumer Bank S.A. payment in methods'),
            'Rembember to configure external IP address on Przelewy24 panel.' => $this->l('Rembember to configure external IP address on Przelewy24 panel.'),
            'Show available payment methods in shop on confirmation page' => $this->l('Show available payment methods in shop on confirmation page'),
            'Show BLIK to customers' => $this->l('Show BLIK to customers'),
            'Show promoted payment methods' => $this->l('Show promoted payment methods'),
            'Select currency for which you want to configure your merchant' => $this->l('Select currency for which you want to configure your merchant'),
            'Requires setting ‘Creating the order’ to ‘After choosing Przelewy24 as a payment gateway’.' => $this->l('Requires setting ‘Creating the order’ to ‘After choosing Przelewy24 as a payment gateway’.'),
            'The eRaty Santander Consumer Bank S.A. module will be promoted as main payment option' => $this->l('The eRaty Santander Consumer Bank S.A. module will be promoted as main payment option'),
            'This option is ignored, if an order is created at a later stage.' => $this->l('This option is ignored, if an order is created at a later stage.'),
            'Use standard return page' => $this->l('Use standard return page'),
            'What is eRaty SCB' => $this->l('What is  eRaty SCB'),
            'Currency' => $this->l('Currency'),
            'Settings' => $this->l('Settings'),
            'Merchant ID' => $this->l('Merchant ID'),
            'Shop ID' => $this->l('Shop ID'),
            'CRC Key' => $this->l('CRC Key'),
            'Module mode' => $this->l('Module mode'),
            'Choose module mode.' => $this->l('Choose module mode.'),
            'Skip order confirmation' => $this->l('Skip order confirmation'),
            'Skip order confirmation if it is possible.' => $this->l('Skip order confirmation if it is possible.'),
            'Test (Sandbox)' => $this->l('Test (Sandbox)'),
            'Normal/production' => $this->l('Normal/production'),
            'API Key' => $this->l('API Key'),
            'API key allow access to additional functions, e.g. graphics list of payment methods.' => $this->l('API key allow access to additional functions, e.g. graphics list of payment methods.'),
            'You can get API key from Przelewy24 dashboard, from my data tab.' => $this->l('You can get API key from Przelewy24 dashboard, from my data tab.'),
            'Save' => $this->l('Save'),
            'Stage of creating the order:' => $this->l('Stage of creating the order:'),
            'After choosing Przelewy24 as a payment gateway' => $this->l('After choosing Przelewy24 as a payment gateway'),
            'After payment' => $this->l('After payment'),
            'After click "Confirm" button' => $this->l('After click "Confirm" button'),
            'Oneclick payments' => $this->l('Oneclick payments'),
            'Allows you to order products with on-click' => $this->l('Allows you to order products with on-click'),
            'Card payments inside shop' => $this->l('Card payments inside shop'),
            'Allows to pay by credit/debit card without leaving the store website' => $this->l('Allows to pay by credit/debit card without leaving the store website'),
            'Customer can chose payment method on confirmation page.' => $this->l('Customer can chose payment method on confirmation page.'),
            'Wait for a result of the transaction' => $this->l('Wait for a result of the transaction'),
            'What is P24NOW' => $this->l('What is P24NOW'),
            'Customer will see P24NOW as a promoted option' => $this->l('Customer will see P24NOW as a promoted option'),
            'P24NOW module will be promoted as main payment option' => $this->l('P24NOW module will be promoted as main payment option'),
            'Use graphics list of payment methods' => $this->l('Use graphics list of payment methods'),
            'Show available payment methods in confirm' => $this->l('Show available payment methods in confirm'),
            'Promote some payment methods' => $this->l('Promote some payment methods'),
            'Enable extra charge' => $this->l('Enable extra charge'),
            'Increase payment (amount)' => $this->l('Increase payment (amount)'),
            'Increase payment (percent)' => $this->l('Increase payment (percent)'),
            'Disable intro text' => $this->l('Disable intro text'),
            'Promote P24NOW payment' => $this->l('Promote P24NOW payment'),
            'Promote P24NOW payment in methods' => $this->l('Promote P24NOW payment in methods'),
            'Back to list' => $this->l('Back to list'),
            'Invalid merchant ID' => $this->l('Invalid merchant ID'),
            'Invalid shop ID' => $this->l('Invalid shop ID'),
            'Invalid CRC key' => $this->l('Invalid CRC key'),
            'Cannot connect to external service. This could be network error or wrong merchant ID.' => $this->l('Cannot connect to external service. This could be network error or wrong merchant ID.'),
            'Wrong CRC Key for this Merchant / Shop ID and module mode!' => $this->l('Wrong CRC Key for this Merchant / Shop ID and module mode!'),
            'Wrong API key for this Merchant / Shop ID!' => $this->l('Wrong API key for this Merchant / Shop ID!'),
            'Settings saved.' => $this->l('Settings saved.'),
            'Przelewy24 module settings are not configured correctly.' => $this->l('Przelewy24 module settings are not configured correctly.'),
            'Przelewy24 payment method does not appear in the list in order.' => $this->l('Przelewy24 payment method does not appear in the list in order.'),
            'Order' => $this->l('Order'),
            'Cart' => $this->l('Cart'),
            'Extra charge [VAT and discounts]' => $this->l('Extra charge [VAT and discounts]'),
            'Your order' => $this->l('Your order'),
            'Extra charge was added to this order by Przelewy24: ' => $this->l('Extra charge was added to this order by Przelewy24: '),
            'Module is not configured.' => $this->l('Module is not configured.'),
            'Przelewy24.pl' => $this->l('Przelewy24.pl'),
            'Przelewy24.pl - Payment Service' => $this->l('Przelewy24.pl - Payment Service'),
            'Removed successfully' => $this->l('Removed successfully'),
            'Saved successfully' => $this->l('Saved successfully'),
            '(tax incl.)' => $this->l('(tax incl.)'),
            'Extracharge Przelewy24' => $this->l('Extracharge Przelewy24'),
            'Pay with Przelewy24' => $this->l('Pay with Przelewy24'),
            'Order not exist for this customer' => $this->l('Order not exist for this customer'),
            'Failed transaction registration in Przelewy24' => $this->l('Failed transaction registration in Przelewy24'),
            'Pay with Blik' => $this->l('Pay with Blik'),
            'Invalid request' => $this->l('Invalid request'),
            'Invalid cart ID' => $this->l('Invalid cart ID'),
            'Your card is not valid' => $this->l('Your card is not valid'),
            'Your card is expired' => $this->l('Your card is expired'),
            'Success, no error' => $this->l('Success, no error'),
            'Your Blik alias was declined, please provide BlikCode' => $this->l('Your Blik alias was declined, please provide BlikCode'),
            'Identification not possible by given alias' => $this->l('Identification not possible by given alias'),
            'Your Blik alias is incorrect, please provide BlikCode' => $this->l('Your Blik alias is incorrect, please provide BlikCode'),
            'Your Blik alias is not confirmed, please provide BlikCode' => $this->l('Your Blik alias is not confirmed, please provide BlikCode'),
            'Your Blik alias was not found, please provide BlikCode' => $this->l('Your Blik alias was not found, please provide BlikCode'),
            'Alias payments are currently not supported, please provide BlikCode' => $this->l('Alias payments are currently not supported, please provide BlikCode'),
            'Your Blik alias was unregistered, please provide BlikCode' => $this->l('Your Blik alias was unregistered, please provide BlikCode'),
            'Bad PIN provided, please generate new BlikCode' => $this->l('Bad PIN provided, please generate new BlikCode'),
            'Blik service unavailable' => $this->l('Blik service unavailable'),
            'Your BlikCode was rejected, please generate new BlikCode' => $this->l('Your BlikCode was rejected, please generate new BlikCode'),
            'Insufficient funds' => $this->l('Insufficient funds'),
            'Limit exceeded' => $this->l('Limit exceeded'),
            'Your PIN was rejected' => $this->l('Your PIN was rejected'),
            'Transaction timeout' => $this->l('Transaction timeout'),
            'Your BlikCode has expired, please generate another' => $this->l('Your BlikCode has expired, please generate another'),
            'Incorrect BlikCode format, please generate another' => $this->l('Incorrect BlikCode format, please generate another'),
            'Your BlikCode is incorrect, please generate another' => $this->l('Your BlikCode is incorrect, please generate another'),
            'Your BlikCode was already used, please generate another' => $this->l('Your BlikCode was already used, please generate another'),
            'Transaction failed, incorrect alias' => $this->l('Transaction failed, incorrect alias'),
            'Blik payment error' => $this->l('Blik payment error'),
            'Please accept' => $this->l('Please accept'),
            'the Przelewy24 Terms' => $this->l('the Przelewy24 Terms'),
            'Status before completing payment' => $this->l('Status before completing payment'),
            'Status after completing payment' => $this->l('Status after completing payment'),
            'Settings for all currencies' => $this->l('Settings for all currencies'),
            'Show accept button in shop' => $this->l('Show accept button in shop'),
            'Order id in title' => $this->l('Order id in title'),
            'Order id in database (e.g. 1, 2, 3)' => $this->l('Order id in database (e.g. 1, 2, 3)'),
            'Masked order id (e.g. QYTUVLHOW)' => $this->l('Masked order id (e.g. QYTUVLHOW)'),
            'Multi warehouse order. Cart may contain additional products, not listed below.' => $this->l('Multi warehouse order. Cart may contain additional products, not listed below.'),
        ];

        return $lang;
    }

    public function getCardLangArray()
    {
        $lang = [
            'card_holder_label' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Name and surname')),
            'card_number_label' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Card number')),
            'cvv_label' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('cvv')),
            'exp_date_label' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Expired date')),
            'pay_button_caption' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Pay by card')),
            'description' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Register card and payment')),
            'three_ds_auth_message' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Click to redirect to 3ds payment')),
            'register_card_label' => Przelewy24Tools::HardEscapeForJavaScriptNotation($this->l('Register card')),
        ];

        return $lang;
    }

    /**
     * Get language string.
     *
     * @param string $text
     *
     * @return string
     */
    public function getLangString($text)
    {
        $return = $text;

        $langArray = $this->getLangArray();
        if (array_key_exists($text, $langArray)) {
            $return = $langArray[$text];
        }

        return $return;
    }

    /**
     * @param Przelewy24Order $fulOrderIdData
     * @param Currency $currency
     *
     * @return string
     */
    private function createP24Link($fulOrderIdData, $currency)
    {
        if (!isset($fulOrderIdData->p24_order_id, $currency->iso_code)) {
            return '';
        }

        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
        $testMode = (bool) Configuration::get('P24_TEST_MODE' . $suffix);
        $hostForEnvironment = Przelewy24Class::getHostForEnvironment($testMode);
        $host = str_replace('secure', 'panel', $hostForEnvironment);

        return sprintf('%s' . self::TRANSAKCJA_PHP_ID . '%s', $host, $fulOrderIdData->p24_order_id);
    }

    /**
     * @param Przelewy24Order $przelewy24Order
     * @param $orderInformation
     * @param Currency $currency
     *
     * @return array
     */
    private function buildFullOrderIdViewData(Przelewy24Order $przelewy24Order, $orderInformation, Currency $currency)
    {
        $p24OrderData = $przelewy24Order->getByPshopOrderId((int) $orderInformation->id);
        $p24FullOrderId = isset($p24OrderData->p24_full_order_id) ? $p24OrderData->p24_full_order_id : '';
        $p24Link = $this->createP24Link($p24OrderData, $currency);
        if ($p24OrderData) {
            $p24OrderId = $p24OrderData->p24_order_id;
            $psOrderId = $p24OrderData->pshop_order_id;
            $p24OrderAmount = $p24OrderData->amount ? number_format($p24OrderData->amount / 100, 2, ',', ' ') : null;
            $p24OrderReceived = $p24OrderData->received;
        } else {
            $p24OrderId = null;
            $psOrderId = null;
            $p24OrderAmount = null;
            $p24OrderReceived = null;
        }

        $fullOrderIdViewData = [
            'p24_order_amount' => $p24OrderAmount,
            'p24_order_received' => $p24OrderReceived,
            'p24_full_order_id' => $p24FullOrderId,
            'p24_order_id' => $p24OrderId,
            'ps_order_id' => $psOrderId,
            'link' => $p24Link,
        ];

        return $fullOrderIdViewData;
    }

    /**
     * @param $extracharge
     * @param Currency $currency
     *
     * @return array
     */
    private function buildExtrachargeViewData($extracharge, Currency $currency)
    {
        $extrachargeViewData = [
            'extracharge' => round($extracharge, 2),
            'extrachargeFormatted' => number_format($extracharge, 2),
            'extracharge_text' => $this->getLangString('Extracharge Przelewy24'),
            'currencySign' => $currency->sign,
        ];

        return $extrachargeViewData;
    }
}
