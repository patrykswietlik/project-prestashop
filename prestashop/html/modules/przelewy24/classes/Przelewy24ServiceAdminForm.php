<?php
/**
 * Class Przelewy24ServiceAdminForm
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServiceAdminForm
 */
class Przelewy24ServiceAdminForm extends Przelewy24Service
{
    const P24_PAYMETHOD_LIST_PROMOTE = 'p24_paymethod_list_promote';
    const P24_PAYMETHOD_LIST_PROMOTE_2 = 'p24_paymethod_list_promote_2';
    const FORM_TYPE = 'type';
    const FORM_LABEL = 'label';
    const FORM_NAME = 'name';
    const FORM_CLASS = 'class';
    const FORM_REQUIRED = 'required';
    const FORM_OPTIONS = 'options';
    const FORM_DESC = 'desc';
    const FORM_VALUES = 'values';
    const FORM_VALUE = 'value';
    const FORM_ID = 'id';
    const FORM_QUERY = 'query';

    const DOC_URL = 'https://developers.przelewy24.pl/modules/';

    /**
     * Configuration parameters.
     *
     * @var array
     */
    private $parameters = [
        'P24_MERCHANT_ID',
        'P24_SHOP_ID',
        'P24_SALT',
        'P24_TEST_MODE',
        'P24_API_KEY',
        'P24_VERIFYORDER',
        'P24_USE_STANDARD_RETURN_PAGE',
        'P24_PAYMENT_METHOD_CHECKOUT_LIST',
        'P24_PAYMENT_METHOD_CONFIRM_LIST',
        'P24_GRAPHICS_PAYMENT_METHOD_LIST',
        'P24_PAYMENTS_ORDER_LIST_FIRST',
        'P24_PAYMENTS_ORDER_LIST_SECOND',
        'P24_PAYMENTS_PROMOTE_LIST',
        'P24_SKIP_CONFIRMATION_ENABLE',
        'P24_ONECLICK_ENABLE',
        'P24_ACCEPTINSHOP_ENABLE',
        'P24_WAIT_FOR_RESULT',
        'P24_INSTALMENT_ENABLED',
        'P24_INSTALMENT_PROMOTE',
        'P24_BLIK_UID_ENABLE',
        'P24_PAY_CARD_INSIDE_ENABLE',
        'P24_BLIK_INSIDE_ENABLE',
        'P24_BLIK_SHOW_TO_CUSTOMER',
        'P24_PAYMENTS_DESCRIPTION',
        'P24_EXTRA_CHARGE_ENABLED',
        'P24_EXTRA_CHARGE_AMOUNT',
        'P24_EXTRA_CHARGE_PERCENT',
        'P24_INTRO_DISABLED',
        'P24_ORDER_TITLE_ID',
    ];

    private function generateLinkToDocumentation($lang)
    {
        return '<a href="' . self::DOC_URL . '">' . $lang['Link to documentation'] . '</a>';
    }

    private static function isConfiguredForSuffix($suffix)
    {
        if ((int) Configuration::get('P24_MERCHANT_ID' . $suffix) <= 0) {
            return false;
        } elseif ((int) Configuration::get('P24_SHOP_ID' . $suffix) <= 0) {
            return false;
        } elseif (empty(Configuration::get('P24_SALT' . $suffix))) {
            return false;
        }

        return true;
    }

    /**
     * Display P24 form.
     *
     * @param array $lang Array of translations
     *
     * @return string
     *
     * @throws Exception
     */
    public function displayForm($lang)
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $output = '';

        $fieldsForm = $testApi = [];
        foreach (CurrencyCore::getCurrencies() as $currency) {
            $currencyCode = $currency['iso_code'];
            $suffix = Przelewy24Helper::getSuffix($currencyCode);

            if (self::isConfiguredForSuffix($suffix)) {
                $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
                $testApiAccess = $restApi->apiTestAccess();
            }

            if (isset($testApiAccess) && $testApiAccess) {
                $blikEnable = true;
                $oneClickEnable = true;
            } else {
                $testApiAccess = false;
                $blikEnable = false;
                $oneClickEnable = false;
            }

            $testApi[$currencyCode] = [
                'testApi' => $testApiAccess,
                'P24_BLIK_UID_ENABLE' => $blikEnable,
                'P24_ONECLICK_ENABLE' => $oneClickEnable,
            ];
        }

        $fieldsForm[-1]['form'] = [
            'legend' => [
                'title' => $lang['Documentation'],
                'image' => $this->getPrzelewy24()->getPath() . 'logo.png',
            ],
            'input' => [
                [
                    self::FORM_TYPE => 'html',
                    self::FORM_NAME => $this->generateLinkToDocumentation($lang),
                ],
            ],
        ];

        $orderStates = OrderState::getOrderStates(
            $this->getPrzelewy24()->getContext()->cookie->id_lang
        );
        $fieldsForm[1]['form'] = [
            'legend' => [
                'title' => $lang['Select currency for which you want to configure your merchant'],
                'image' => $this->getPrzelewy24()->getPath() . 'logo.png',
            ],
            'input' => [
                [
                    self::FORM_TYPE => 'radio',
                    self::FORM_LABEL => $lang['Currency'],
                    self::FORM_NAME => 'currency',
                    self::FORM_REQUIRED => true,
                    self::FORM_VALUES => [],
                ],
            ],
        ];

        $fieldsForm[2]['form'] = [
            'legend' => [
                'title' => $lang['Settings for all currencies'],
                'image' => $this->getPrzelewy24()->getPath() . 'logo.png',
            ],
            'input' => [
                [
                    self::FORM_TYPE => 'select',
                    self::FORM_LABEL => $lang['Status before completing payment'],
                    self::FORM_NAME => 'P24_ORDER_STATE_1',
                    self::FORM_REQUIRED => true,
                    self::FORM_OPTIONS => [
                        self::FORM_QUERY => $orderStates,
                        self::FORM_ID => 'id_order_state',
                        self::FORM_NAME => 'name',
                    ],
                ],
                [
                    self::FORM_TYPE => 'select',
                    self::FORM_LABEL => $lang['Status after completing payment'],
                    self::FORM_NAME => 'P24_ORDER_STATE_2',
                    self::FORM_REQUIRED => true,
                    self::FORM_OPTIONS => [
                        self::FORM_QUERY => $orderStates,
                        self::FORM_ID => 'id_order_state',
                        self::FORM_NAME => 'name',
                    ],
                ],
                [
                    self::FORM_TYPE => 'switch',
                    self::FORM_LABEL => $lang['After selecting products to refund, alter stock quantity'],
                    self::FORM_NAME => 'P24_REFUND_WITH_ALTER_STOCK',
                    self::FORM_REQUIRED => true,
                    self::FORM_VALUES => [
                        [
                            self::FORM_ID => 'active_on',
                            self::FORM_VALUE => 1,
                        ],
                        [
                            self::FORM_ID => 'active_off',
                            self::FORM_VALUE => 0,
                        ],
                    ],
                ],
            ],
        ];

        foreach (CurrencyCore::getCurrencies() as $currency) {
            array_push(
                $fieldsForm[1]['form']['input'][0]['values'],
                [
                    self::FORM_ID => $currency['iso_code'],
                    self::FORM_VALUE => $currency['iso_code'],
                    self::FORM_LABEL => $currency['iso_code'],
                ]
            );
        }

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $lang['Settings'],
                'image' => $this->getPrzelewy24()->getPath() . 'logo.png',
            ],
            'input' => [
                [
                    self::FORM_TYPE => 'html',
                    self::FORM_NAME => $lang['Rembember to configure external IP address on Przelewy24 panel.'],
                ],
                [
                    self::FORM_TYPE => 'text',
                    self::FORM_LABEL => $lang['Merchant ID'],
                    self::FORM_NAME => 'P24_MERCHANT_ID',
                    self::FORM_REQUIRED => true,
                ],
                [
                    self::FORM_TYPE => 'text',
                    self::FORM_LABEL => $lang['Shop ID'],
                    self::FORM_NAME => 'P24_SHOP_ID',
                    self::FORM_REQUIRED => true,
                ],
                [
                    self::FORM_TYPE => 'text',
                    self::FORM_LABEL => $lang['CRC Key'],
                    self::FORM_NAME => 'P24_SALT',
                    self::FORM_REQUIRED => true,
                ],
                [
                    self::FORM_TYPE => 'radio',
                    self::FORM_LABEL => $lang['Module mode'],
                    'desc' => $lang['Choose module mode.'],
                    self::FORM_NAME => 'P24_TEST_MODE',
                    self::FORM_REQUIRED => true,
                    self::FORM_CLASS => 't',
                    'is_bool' => true,
                    self::FORM_VALUES => [
                        [
                            self::FORM_ID => 'active_test',
                            self::FORM_VALUE => 1,
                            self::FORM_LABEL => $lang['Test (Sandbox)'],
                        ],
                        [
                            self::FORM_ID => 'active_prod',
                            self::FORM_VALUE => 0,
                            self::FORM_LABEL => $lang['Normal/production'],
                        ],
                    ],
                ],
                [
                    self::FORM_TYPE => 'text',
                    self::FORM_LABEL => $lang['API Key'],
                    self::FORM_NAME => 'P24_API_KEY',
                    self::FORM_REQUIRED => true,
                    'desc' => $lang['API key allow access to additional functions, e.g. graphics list of payment methods.'] .
                        ' ' . $lang['You can get API key from Przelewy24 dashboard, from my data tab.'],
                ],
            ],
            'submit' => [
                'title' => $lang['Save'],
                self::FORM_CLASS => 'btn btn-default pull-right',
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'select',
            self::FORM_LABEL => $lang['Stage of creating the order:'],
            self::FORM_NAME => 'P24_VERIFYORDER',
            self::FORM_OPTIONS => [
                self::FORM_QUERY => [
                    [
                        'id_option' => 0,
                        self::FORM_NAME => $lang['After choosing Przelewy24 as a payment gateway'],
                    ],
                    [
                        'id_option' => 1,
                        self::FORM_NAME => $lang['After payment'],
                    ],
                    [
                        'id_option' => 2,
                        self::FORM_NAME => $lang['After click "Confirm" button'],
                    ],
                ],
                self::FORM_ID => 'id_option',
                self::FORM_NAME => 'name',
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Use standard return page'],
            self::FORM_DESC => $lang['This option is ignored, if an order is created at a later stage.'],
            self::FORM_NAME => 'P24_USE_STANDARD_RETURN_PAGE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Skip order confirmation'],
            self::FORM_DESC => $lang['Skip order confirmation if it is possible.'],
            self::FORM_NAME => 'P24_SKIP_CONFIRMATION_ENABLE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Oneclick payments'],
            'desc' => $lang['Allows you to order products with on-click'],
            self::FORM_NAME => 'P24_ONECLICK_ENABLE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Show accept button in shop'],
            self::FORM_NAME => 'P24_ACCEPTINSHOP_ENABLE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Wait for a result of the transaction'],
            self::FORM_NAME => 'P24_WAIT_FOR_RESULT',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        // ajax form
        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Card payments inside shop'],
            'desc' => $lang['Allows to pay by credit/debit card without leaving the store website'],
            self::FORM_NAME => 'P24_PAY_CARD_INSIDE_ENABLE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['BLIK payments inside shop'],
            self::FORM_DESC => $lang['Allows to pay by BLIK without leaving the store website'],
            self::FORM_NAME => 'P24_BLIK_INSIDE_ENABLE',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Show BLIK to customers'],
            self::FORM_NAME => 'P24_BLIK_SHOW_TO_CUSTOMER',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Show available payment methods in shop on confirmation page'],
            'desc' => $lang['Customer can chose payment method on confirmation page.'],
            self::FORM_NAME => 'P24_PAYMENT_METHOD_CONFIRM_LIST',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Promote eRaty Santander Consumer Bank S.A. payment'],
            self::FORM_NAME => 'P24_INSTALMENT_ENABLED',
            'desc' => $lang['Customer will see eRaty Santander Consumer Bank S.A. as a promoted option']
                . ', <a href="https://www.przelewy24.pl/metody-platnosci/e-raty">' . $lang['What is eRaty SCB'] . '?</a>',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Promote the eRaty Santander Consumer Bank S.A. payment in methods'],
            self::FORM_NAME => 'P24_INSTALMENT_PROMOTE',
            'desc' => $lang['The eRaty Santander Consumer Bank S.A. module will be promoted as main payment option']
                . ', <a href="https://www.przelewy24.pl/metody-platnosci/e-raty">' . $lang['What is eRaty SCB'] . '?</a>',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Use graphics list of payment methods'],
            self::FORM_NAME => 'P24_GRAPHICS_PAYMENT_METHOD_LIST',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        foreach (CurrencyCore::getCurrencies() as $currency) {
            $currencyCode = $currency['iso_code'];
            $suffix = Przelewy24Helper::getSuffix($currencyCode);
            $altSuffix = strtr($suffix, '_', '-');
            $paymentCache = Przelewy24CachedPaymentListFactory::buildForSuffix($suffix);
            $apiLang = Context::getContext()->language->iso_code;

            if (self::isConfiguredForSuffix($suffix)) {
                $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
                $restApi->setPaymentCache($paymentCache);
                $paymethodList = $restApi->getFirstAndSecondPaymentList($currencyCode);

                $p24PaymethodDescription = [];
                foreach ($restApi->availablePaymentMethods() as $bankId => $bankName) {
                    if ($value = Configuration::get("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}")) {
                        Configuration::updateValue("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}", $value);
                    } else {
                        $value = $bankName;
                    }
                    $p24PaymethodDescription[$bankId] = $value;
                }

                $paymethodList['p24_paymethod_description'] = $p24PaymethodDescription;
            } else {
                $paymethodList = [
                    'p24_paymethod_list_first' => [],
                    'p24_paymethod_list_second' => [],
                    'p24_paymethod_description' => [],
                ];
            }

            $this->getPrzelewy24()->getSmarty()->assign(['suffix' => $suffix]);
            $this->getPrzelewy24()->getSmarty()->assign(['altSuffix' => $altSuffix]);

            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_list_first' . $suffix => $paymethodList['p24_paymethod_list_first']]
            );
            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_list_second' . $suffix => $paymethodList['p24_paymethod_list_second']]
            );

            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_description' . $suffix => $paymethodList['p24_paymethod_description']]
            );

            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_cache' . $suffix => $paymentCache->getList($apiLang)]
            );

            $fieldsForm[0]['form']['input'][] = [
                self::FORM_TYPE => 'html',
                self::FORM_LABEL => $lang['Show available payment methods in confirm'],
                self::FORM_NAME => $this->getPrzelewy24()->display(
                    $this->getPrzelewy24()->getBaseFile(),
                    'views/templates/admin/sortable_payments.tpl'
                ),
            ];
        }

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'hidden',
            self::FORM_NAME => 'P24_PAYMENTS_ORDER_LIST_FIRST',
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'hidden',
            self::FORM_NAME => 'P24_PAYMENTS_ORDER_LIST_SECOND',
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Show promoted payment methods'],
            self::FORM_NAME => 'P24_PAYMENT_METHOD_CHECKOUT_LIST',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        foreach (CurrencyCore::getCurrencies() as $currency) {
            $currencyCode = $currency['iso_code'];
            $suffix = Przelewy24Helper::getSuffix($currencyCode);
            $altSuffix = strtr($suffix, '_', '-');
            $paymentCache = Przelewy24CachedPaymentListFactory::buildForSuffix($suffix);
            $apiLang = Context::getContext()->language->iso_code;

            // promote
            if (self::isConfiguredForSuffix($suffix)) {
                $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
                $restApi->setPaymentCache($paymentCache);
                $promotePaymethodList = $restApi->getPromotedPaymentList($currencyCode);
            } else {
                $promotePaymethodList = [
                    self::P24_PAYMETHOD_LIST_PROMOTE => [],
                    self::P24_PAYMETHOD_LIST_PROMOTE_2 => [],
                ];
            }
            if ($currency['iso_code'] === 'PLN') {
                if (Configuration::get('P24_INSTALMENT_ENABLED')) {
                    if (array_key_exists(303, $promotePaymethodList['p24_paymethod_list_promote_2'])) {
                        $promotePaymethodList['p24_paymethod_list_promote'][303] =
                            $promotePaymethodList['p24_paymethod_list_promote_2'][303];
                        unset($promotePaymethodList['p24_paymethod_list_promote_2'][303]);
                    }
                } else {
                    if (array_key_exists(303, $promotePaymethodList['p24_paymethod_list_promote'])) {
                        $promotePaymethodList['p24_paymethod_list_promote_2'][303] =
                            $promotePaymethodList['p24_paymethod_list_promote'][303];
                        unset($promotePaymethodList['p24_paymethod_list_promote'][303]);
                    }
                }
            }

            $this->getPrzelewy24()->getSmarty()->assign(['suffix' => $suffix]);
            $this->getPrzelewy24()->getSmarty()->assign(['altSuffix' => $altSuffix]);

            $this->getPrzelewy24()->getSmarty()->assign(
                [
                    self::P24_PAYMETHOD_LIST_PROMOTE . $suffix => $promotePaymethodList[self::P24_PAYMETHOD_LIST_PROMOTE],
                ]
            );
            $this->getPrzelewy24()->getSmarty()->assign(
                [
                    self::P24_PAYMETHOD_LIST_PROMOTE_2 . $suffix => $promotePaymethodList[self::P24_PAYMETHOD_LIST_PROMOTE_2],
                ]
            );
            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_cache' . $suffix => $paymentCache->getList($apiLang)]
            );

            $fieldsForm[0]['form']['input'][] = [
                self::FORM_TYPE => 'html',
                self::FORM_LABEL => $lang['Promote some payment methods'],
                self::FORM_NAME => $this->getPrzelewy24()->display(
                    $this->getPrzelewy24()->getBaseFile(),
                    'views/templates/admin/sortable_promote_payments.tpl'
                ),
            ];
        }

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'hidden',
            self::FORM_NAME => 'P24_PAYMENTS_PROMOTE_LIST',
        ];

        foreach (CurrencyCore::getCurrencies() as $currency) {
            $currencyCode = $currency['iso_code'];
            $suffix = Przelewy24Helper::getSuffix($currencyCode);
            $altSuffix = strtr($suffix, '_', '-');
            $paymentCache = Przelewy24CachedPaymentListFactory::buildForSuffix($suffix);
            $apiLang = Context::getContext()->language->iso_code;

            // promote
            if (self::isConfiguredForSuffix($suffix)) {
                $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
                $promotePaymethodList = $restApi->getPromotedPaymentList($currencyCode);
            } else {
                $promotePaymethodList = [
                    self::P24_PAYMETHOD_LIST_PROMOTE => [],
                    self::P24_PAYMETHOD_LIST_PROMOTE_2 => [],
                ];
            }

            if ($currency['iso_code'] === 'PLN') {
                if (Configuration::get('P24_INSTALMENT_ENABLED')) {
                    if (array_key_exists(303, $promotePaymethodList['p24_paymethod_list_promote_2'])) {
                        $promotePaymethodList['p24_paymethod_list_promote'][303] =
                            $promotePaymethodList['p24_paymethod_list_promote_2'][303];
                        unset($promotePaymethodList['p24_paymethod_list_promote_2'][303]);
                    }
                } else {
                    if (array_key_exists(303, $promotePaymethodList['p24_paymethod_list_promote'])) {
                        $promotePaymethodList['p24_paymethod_list_promote_2'][303] =
                            $promotePaymethodList['p24_paymethod_list_promote'][303];
                        unset($promotePaymethodList['p24_paymethod_list_promote'][303]);
                    }
                }
            }
            $this->getPrzelewy24()->getSmarty()->assign(
                ['p24_paymethod_cache' . $suffix => $paymentCache->getList($apiLang)]
            );

            $this->getPrzelewy24()->getSmarty()->assign(
                [
                    'suffix' => $suffix,
                    'altSuffix' => $altSuffix,
                    self::P24_PAYMETHOD_LIST_PROMOTE . $suffix => $promotePaymethodList[self::P24_PAYMETHOD_LIST_PROMOTE],
                    self::P24_PAYMETHOD_LIST_PROMOTE_2 . $suffix => $promotePaymethodList[self::P24_PAYMETHOD_LIST_PROMOTE_2],
                ]
            );

            $fieldsForm[0]['form']['input'][] = [
                self::FORM_TYPE => 'html',
                self::FORM_LABEL => $lang['Payment descriptions'],
                self::FORM_NAME => $this->getPrzelewy24()->display(
                    $this->getPrzelewy24()->getBaseFile(),
                    'views/templates/admin/payments_description.tpl'
                ),
            ];
        }

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'hidden',
            self::FORM_NAME => 'P24_PAYMENTS_DESCRIPTION',
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Enable extra charge'],
            self::FORM_NAME => 'P24_EXTRA_CHARGE_ENABLED',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];
        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'text',
            self::FORM_LABEL => $lang['Increase payment (amount)'],
            self::FORM_NAME => 'P24_EXTRA_CHARGE_AMOUNT',
            self::FORM_CLASS => 'input fixed-width-sm',
            self::FORM_REQUIRED => false,
        ];
        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'text',
            self::FORM_LABEL => $lang['Increase payment (percent)'],
            self::FORM_NAME => 'P24_EXTRA_CHARGE_PERCENT',
            self::FORM_CLASS => 'input fixed-width-sm',
            self::FORM_REQUIRED => false,
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'switch',
            self::FORM_LABEL => $lang['Disable intro text'],
            self::FORM_NAME => 'P24_INTRO_DISABLED',
            self::FORM_VALUES => [
                [
                    self::FORM_ID => 'active_on',
                    self::FORM_VALUE => 1,
                ],
                [
                    self::FORM_ID => 'active_off',
                    self::FORM_VALUE => 0,
                ],
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            self::FORM_TYPE => 'select',
            self::FORM_LABEL => $lang['Order id in title'],
            self::FORM_NAME => 'P24_ORDER_TITLE_ID',
            self::FORM_DESC => $lang['Requires setting ‘Creating the order’ to ‘After choosing Przelewy24 as a payment gateway’.'],
            self::FORM_OPTIONS => [
                self::FORM_QUERY => [
                    [
                        'id_option' => 0,
                        'name' => $lang['Order id in database (e.g. 1, 2, 3)'],
                    ],
                    [
                        'id_option' => 1,
                        'name' => $lang['Masked order id (e.g. QYTUVLHOW)'],
                    ],
                ],
                'id' => 'id_option',
                self::FORM_NAME => 'name',
            ],
        ];

        $this->getPrzelewy24()->getContext()->controller->addCSS(
            $this->getPrzelewy24()->getPath() . 'views/css/admin.css',
            'all'
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this->getPrzelewy24();
        $helper->name_controller = $this->getPrzelewy24()->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->getPrzelewy24()->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->getPrzelewy24()->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // true - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->getPrzelewy24()->name;
        $toolbarBtnHref = AdminController::$currentIndex .
            '&configure=' . $this->getPrzelewy24()->name .
            '&save' . $this->getPrzelewy24()->name .
            '&token=' . Tools::getAdminTokenLite('AdminModules')
        ;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $lang['Save'],
                'href' => $toolbarBtnHref,
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $lang['Back to list'],
            ],
        ];

        // Load current value

        foreach (CurrencyCore::getCurrencies() as $currency) {
            $currencyCode = $currency['iso_code'];
            $suffix = Przelewy24Helper::getSuffix($currencyCode);

            foreach ($this->parameters as $param) {
                $fieldsForm[0]['form']['tabs'][$currencyCode][$param] = Configuration::get($param . $suffix);
            }
            $fieldsForm[0]['form']['tabs'][$currencyCode]['P24_ADDITIONAL_SETTINGS'] = $testApi[$currencyCode];
        }

        $defaultCurrency = new CurrencyCore(Configuration::get('PS_CURRENCY_DEFAULT'));
        $suffix = Przelewy24Helper::getSuffix($defaultCurrency->iso_code);

        foreach ($this->parameters as $param) {
            $helper->fields_value[$param] = Configuration::get($param . $suffix);
        }
        $helper->fields_value['currency'] = $defaultCurrency->iso_code;

        $helper->fields_value['P24_ORDER_STATE_1'] = (int) Configuration::get('P24_ORDER_STATE_1');
        $helper->fields_value['P24_ORDER_STATE_2'] = (int) Configuration::get('P24_ORDER_STATE_2');
        $helper->fields_value['P24_REFUND_WITH_ALTER_STOCK'] = (bool) Configuration::get('P24_REFUND_WITH_ALTER_STOCK');

        return $output . $helper->generateForm($fieldsForm);
    }

    /**
     * Submit action in admin.
     *
     * @param string $lang
     *
     * @return string
     *
     * @throws Exception
     */
    public function processSubmit($lang)
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->getPrzelewy24()->name)) {
            $isValid = true;
            $suffix = Przelewy24Helper::getSuffix(Tools::getValue('currency'));

            $merchantId = trim(Tools::getValue('P24_MERCHANT_ID'));
            if (empty($merchantId) || !Validate::isInt($merchantId) || ($merchantId <= 0)) {
                $isValid = false;
                $output .= $this->getPrzelewy24()->displayError($lang['Invalid merchant ID']);
            }

            $shopId = trim(Tools::getValue('P24_SHOP_ID'));
            if (empty($shopId) || !Validate::isInt($shopId) || ($shopId <= 0)) {
                $isValid = false;
                $output .= $this->getPrzelewy24()->displayError($lang['Invalid shop ID']);
            }

            $salt = trim(Tools::getValue('P24_SALT'));
            if (empty($salt)) {
                $isValid = false;
                $output .= $this->getPrzelewy24()->displayError($lang['Invalid CRC key']);
            }

            $apiKey = trim(Tools::getValue('P24_API_KEY'));

            // Try create object only for valid credentials.
            if ($isValid) {
                $testMode = (bool) Tools::getValue('P24_TEST_MODE');
                $restApi = Przelewy24RestBigFactory::bulidForParams(
                    $shopId,
                    $apiKey,
                    $salt,
                    $testMode
                );
                $isValid = $restApi->apiTestAccess();
            }

            if ($isValid) {
                Configuration::updateValue('P24_MERCHANT_ID' . $suffix, $merchantId);
                Configuration::updateValue('P24_SHOP_ID' . $suffix, $shopId);
                Configuration::updateValue('P24_SALT' . $suffix, $salt);
                Configuration::updateValue('P24_API_KEY' . $suffix, $apiKey);
                Configuration::updateValue('P24_TEST_MODE' . $suffix, trim(Tools::getValue('P24_TEST_MODE')));
                Configuration::updateValue('P24_ORDER_TITLE_ID' . $suffix, trim(Tools::getValue('P24_ORDER_TITLE_ID')));

                Configuration::updateValue(
                    'P24_PAYMENT_METHOD_CHECKOUT_LIST' . $suffix,
                    (int) Tools::getValue('P24_PAYMENT_METHOD_CHECKOUT_LIST')
                );

                Configuration::updateValue(
                    'P24_PAYMENT_METHOD_CONFIRM_LIST' . $suffix,
                    (int) Tools::getValue('P24_PAYMENT_METHOD_CONFIRM_LIST')
                );

                Configuration::updateValue(
                    'P24_GRAPHICS_PAYMENT_METHOD_LIST' . $suffix,
                    (int) Tools::getValue('P24_GRAPHICS_PAYMENT_METHOD_LIST')
                );

                if (Tools::getValue('P24_INSTALMENT_ENABLED') != Configuration::get('P24_INSTALMENT_ENABLED')) {
                    $switch = true;
                } else {
                    $switch = false;
                }

                Configuration::updateValue(
                    'P24_INSTALMENT_ENABLED' . $suffix,
                    trim(Tools::getValue('P24_INSTALMENT_ENABLED'))
                );

                Configuration::updateValue(
                    'P24_INSTALMENT_PROMOTE' . $suffix,
                    trim(Tools::getValue('P24_INSTALMENT_PROMOTE'))
                );

                Configuration::updateValue(
                    'P24_PAYMENTS_ORDER_LIST_FIRST' . $suffix,
                    trim(Tools::getValue('P24_PAYMENTS_ORDER_LIST_FIRST'))
                );

                Configuration::updateValue(
                    'P24_PAYMENTS_ORDER_LIST_SECOND' . $suffix,
                    trim(Tools::getValue('P24_PAYMENTS_ORDER_LIST_SECOND'))
                );

                if ($switch == true) {
                    if (Configuration::get('P24_INSTALMENT_ENABLED')) {
                        Configuration::updateValue(
                            'P24_PAYMENTS_PROMOTE_LIST' . $suffix,
                            trim(Tools::getValue('P24_PAYMENTS_PROMOTE_LIST')) . 303
                        );
                    } else {
                        Configuration::updateValue(
                            'P24_PAYMENTS_PROMOTE_LIST' . $suffix,
                            trim(Tools::getValue('P24_PAYMENTS_PROMOTE_LIST'))
                        );
                    }
                } else {
                    $methods = explode(',', Tools::getValue('P24_PAYMENTS_PROMOTE_LIST'));
                    if (in_array(303, $methods)) {
                        Configuration::updateValue('P24_INSTALMENT_ENABLED', 1);
                    } else {
                        Configuration::updateValue('P24_INSTALMENT_ENABLED', 0);
                    }
                    Configuration::updateValue(
                        'P24_PAYMENTS_PROMOTE_LIST' . $suffix,
                        trim(Tools::getValue('P24_PAYMENTS_PROMOTE_LIST'))
                    );
                }

                $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
                $p24PaymethodDescription = $restApi->availablePaymentMethods();

                foreach ($p24PaymethodDescription as $bankId => $bankName) {
                    $value = trim(Tools::getValue("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}"));
                    if ($value && $value !== $bankName) {
                        Configuration::updateValue("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}", $value);
                    } else {
                        Configuration::updateValue("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}", null);
                    }
                }

                Configuration::updateValue(
                    'P24_SKIP_CONFIRMATION_ENABLE',
                    trim(trim(Tools::getValue('P24_SKIP_CONFIRMATION_ENABLE')))
                );
                Configuration::updateValue(
                    'P24_ONECLICK_ENABLE' . $suffix,
                    trim(Tools::getValue('P24_ONECLICK_ENABLE'))
                );
                Configuration::updateValue(
                    'P24_ACCEPTINSHOP_ENABLE' . $suffix,
                    trim(Tools::getValue('P24_ACCEPTINSHOP_ENABLE'))
                );
                Configuration::updateValue(
                    'P24_WAIT_FOR_RESULT' . $suffix,
                    trim(Tools::getValue('P24_WAIT_FOR_RESULT'))
                );
                Configuration::updateValue(
                    'P24_BLIK_UID_ENABLE' . $suffix,
                    trim(Tools::getValue('P24_BLIK_UID_ENABLE'))
                );
                Configuration::updateValue(
                    'P24_VERIFYORDER' . $suffix,
                    trim(Tools::getValue('P24_VERIFYORDER'))
                );
                Configuration::updateValue(
                    'P24_USE_STANDARD_RETURN_PAGE' . $suffix,
                    trim(Tools::getValue('P24_USE_STANDARD_RETURN_PAGE'))
                );
                Configuration::updateValue(
                    'P24_PAY_CARD_INSIDE_ENABLE' . $suffix,
                    trim(Tools::getValue('P24_PAY_CARD_INSIDE_ENABLE'))
                );
                Configuration::updateValue(
                    'P24_BLIK_INSIDE_ENABLE' . $suffix,
                    trim(Tools::getValue('P24_BLIK_INSIDE_ENABLE'))
                );
                Configuration::updateValue(
                    'P24_BLIK_SHOW_TO_CUSTOMER' . $suffix,
                    trim(Tools::getValue('P24_BLIK_SHOW_TO_CUSTOMER'))
                );
                Configuration::updateValue(
                    'P24_EXTRA_CHARGE_ENABLED' . $suffix,
                    trim(Tools::getValue('P24_EXTRA_CHARGE_ENABLED'))
                );
                Configuration::updateValue(
                    'P24_INTRO_DISABLED' . $suffix,
                    trim(Tools::getValue('P24_INTRO_DISABLED'))
                );
                Configuration::updateValue(
                    'P24_EXTRA_CHARGE_AMOUNT' . $suffix,
                    str_replace(',', '.', trim(Tools::getValue('P24_EXTRA_CHARGE_AMOUNT')))
                );
                Configuration::updateValue(
                    'P24_EXTRA_CHARGE_PERCENT' . $suffix,
                    str_replace(',', '.', trim(Tools::getValue('P24_EXTRA_CHARGE_PERCENT')))
                );
                Configuration::updateValue(
                    'P24_ORDER_STATE_1',
                    trim(Tools::getValue('P24_ORDER_STATE_1'))
                );
                Configuration::updateValue(
                    'P24_ORDER_STATE_2',
                    trim(Tools::getValue('P24_ORDER_STATE_2'))
                );
                Configuration::updateValue(
                    'P24_REFUND_WITH_ALTER_STOCK',
                    trim(Tools::getValue('P24_REFUND_WITH_ALTER_STOCK'))
                );

                Configuration::updateValue('P24_CONFIGURATION_VALID' . $suffix, 1);
                $output .= $this->getPrzelewy24()->displayConfirmation($lang['Settings saved.']);
            } else {
                Configuration::updateValue('P24_CONFIGURATION_VALID' . $suffix, 0);
                $errorMsg = $lang['Przelewy24 module settings are not configured correctly.'] . ' ' .
                    $lang['Przelewy24 payment method does not appear in the list in order.'];
                $output .= $this->getPrzelewy24()->displayError($errorMsg);
            }
        }

        $output .= $this->getPrzelewy24()->display(
            $this->getPrzelewy24()->getBaseFile(),
            'views/templates/admin/config_intro.tpl'
        );

        if ((int) Configuration::get('P24_CONFIGURATION_VALID') < 1) {
            $output .= $this->getPrzelewy24()->display(
                $this->getPrzelewy24()->getBaseFile(),
                'views/templates/admin/config_register_info.tpl'
            );
        }

        return $output;
    }
}
