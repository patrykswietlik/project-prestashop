<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

if (!defined('_PS_VERSION_')) {
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/AuthAPI.php';
require_once __DIR__ . '/classes/custom/models/MbePickupAddressHelper.php';
require_once __DIR__ . '/classes/custom/models/MbeRatesCacheHelper.php';
require_once __DIR__ . '/classes/custom/models/MbeShippingDPHelper.php';

class Mbeshipping extends CarrierModule
{
    /* GTM */
    private const GTM_ID = 'GTM-526FFFR';
    //private const GTM_ID = 'GTM-NL3V2W7J';

    public $carriers = array(); // not change
    public $id_carrier = null;
    protected $module_name = 'mbeshipping';
    protected $upload_dir = _PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    protected $module_url = __PS_BASE_URI__ . 'modules/mbeshipping/';
    protected $logger = null;
    private $pricesMbeLoaded = false;
    private $conf_tabs = null;
    private $active_tab;
    private $is_direct_channel_user = false;
    private $third_party_pickups_allowed = false;

    public $ps_version;

    private $numeric_fields = [
        'default_width',
        'default_height',
        'default_length',
        'max_package_weight',
        'max_shipment_weight',
        'handling_fee',
        'handling_fee_rounding_amount',
        'handling_fee_rounding',
        'mbe_shipments_csv_insurance_per',
        'mbe_shipments_csv_insurance_min'
    ];

    private $numeric_field_labels;

    public function __construct()
    {
        $this->initializeLogger();

        $this->name = 'mbeshipping';
        $this->module_key = 'e127bd423c8ec24900475202bb4a131a';
        $this->tab = 'shipping_logistics';
        $this->version = '2.2.2';
        $this->author = 'MBE Worldwide S.p.A.';

        $this->bootstrap = true;

        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('eShip for PrestaShop');
        $this->description = $this->l(
            'eShip for PrestaShop automatically creates shipments of products sold through your e-commerce, allowing you to focus on issues closely related to your business'
        );

        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);  //create directory if not exist
        }

        $this->ps_version = Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2);
        // Check compatibility for PS 8.x
        if ((int) $this->ps_version[0] === 8) {
            $this->ps_version = '8';
        }
    }

    ###########################################################################
    ## Installation
    ###########################################################################

    public function initializeLogger()
    {
        if (!class_exists('LoggerHelper')) {
            require_once _PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Helper'. DIRECTORY_SEPARATOR .'LoggerHelper.php';
        }
        $this->logger = new PrestaShop\Module\Mbeshipping\Helper\LoggerHelper();
    }

    public function initNumericFieldLabels()
    {
        $this->numeric_field_labels = [
            'default_width' => $this->l('Standard package width'),
            'default_height' => $this->l('Standard package height'),
            'default_length' => $this->l('Standard package length'),
            'max_package_weight' => $this->l('Maximum package weight'),
            'max_shipment_weight' => $this->l('Maximum shipping weight'),
            'handling_fee' => $this->l('Amount'),
            'handling_fee_rounding_amount' => $this->l('Rounding amount (in €)'),
            'handling_fee_rounding' => $this->l('Rounding application'),
            'mbe_shipments_csv_insurance_per' => $this->l('Custom Shipping Prices (CSV) - Percentage for insurance price calculation'),
            'mbe_shipments_csv_insurance_min' => $this->l('Custom Shipping Prices (CSV) - Minimum price for insurance')
        ];

        foreach ($this->renderFreeShippingThresholdsAndServicesDescriptions() as $input) {
            if (substr($input['name'], 0, 9) === 'mbelimit_') {
                $this->numeric_field_labels[$input['name']] = $input['label'];
            }
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->installTab()
            || !$this->installOverride()
            || !$this->installHook()
            || !$this->installRatesTable()
            || !$this->installCsvPackageTable()
            || !$this->installCsvPackageProductTable()
            || !$this->installMOrderTable()
            || !$this->installPickupAddressTable()
            || !$this->installRatesCacheTable()
            || !$this->installPickupBatchTable()
            || !$this->installDeliveryPointTable()
        ) {
            return false;
        }

        if (file_exists(_PS_ROOT_DIR_ . '/cache/class_index.php')) {
            unlink(_PS_ROOT_DIR_ . '/cache/class_index.php');
        }

        $this->presetValues();

        $this->fixCarrierOverride();

        return true;
    }

    private function fixCarrierOverride()
    {
        $find = "require_once(_PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'Ws.php');";
        $replace = "\nuse PrestaShop\Module\Mbeshipping\Lib\MbeWs;\nuse PrestaShop\Module\Mbeshipping\Helper\DataHelper;\nuse PrestaShop\Module\Mbeshipping\Helper\RatesHelper;\n";

        $file = _PS_OVERRIDE_DIR_ . '/classes/Carrier.php';
        if (!file_exists($file)) {
            return;
        }

        $file_content = Tools::file_get_contents($file);
        if (strpos($file_content, $find) === false) {
            return;
        }

        file_put_contents($file, str_replace($find, $replace, $file_content));
    }

    public function installTab()
    {
        $result = true;

        // AdminMbeConfigurationController
        $result &= $this->installSingleTab(
            $this->l('Configuration'),
            'AdminMbeConfiguration',
            (int)Tab::getIdFromClassName('AdminParentShipping')
        );

        // AdminMbePickupAddressHelper
        $result &= $this->installSingleTab(
            $this->l('Manage pickup addresses'),
            'AdminMbePickupAddressHelper'// not visible
        );

        // AdminMbePickupOrders
        $result &= $this->installSingleTab(
            $this->l('MBE - Pickup orders'),
            'AdminMbePickupOrders'
        );

        // AdminMbeShippingController
        $result &= $this->installSingleTab(
            $this->l('MBE shipments list'),
            'AdminMbeShipping',
            (int)Tab::getIdFromClassName('AdminParentShipping')
        );

        // AdminMbePersonalAreaController
        $result &= $this->installSingleTab(
            $this->l('Private area'),
            'AdminMbePrivateArea',
            (int)Tab::getIdFromClassName('AdminParentShipping'),
            0 // hidden by default
        );

        // AdminMbePackageHelperController
        $result &= $this->installSingleTab(
            $this->l('Manage packages'),
            'AdminMbePackageHelper'
        );

        // AdminMbePackageProductHelperController
        $result &= $this->installSingleTab(
            $this->l('Manage product packages'),
            'AdminMbePackageProductHelper'
        );

        // AdminMbeChecklistController
        $result &= $this->installSingleTab(
            $this->l('Check module functionality'),
            'AdminMbeChecklist'
        );

        return $result;
    }

    private function getTabName($tab_class_name, $lang_iso_code)
    {
        $tab_name_lang = [
            'AdminMbeConfiguration' => [
                'de' => 'MBE - Konfiguration',
                'en' => 'MBE - Configuration',
                'es' => 'MBE - Configuración',
                'fr' => 'MBE - Configuration',
                'it' => 'MBE - Configurazione',
                'pl' => 'MBE - Konfiguracja',
            ],
            'AdminMbeShipping' => [
                'de' => 'MBE - Sendungsliste',
                'en' => 'MBE - Shipments',
                'es' => 'MBE - Listado de envíos',
                'fr' => 'MBE - Expéditions',
                'it' => 'MBE - Spedizioni',
                'pl' => 'MBE - Przesyłki',
            ],
            'AdminMbePrivateArea' => [
                'de' => 'MBE - Sperrgebiet',
                'en' => 'MBE - Private area',
                'es' => 'MBE - Área privada',
                'fr' => 'MBE - Zone privée',
                'it' => 'MBE - Area riservata',
                'pl' => 'MBE - Teren prywatny',
            ],
            'AdminMbePackageHelper' => [
                'de' => 'Pakete verwalten',
                'en' => 'Parcel management',
                'es' => 'Gestión de paquetes',
                'fr' => 'Gérer les colis par défaut',
                'it' => 'Gestione pacchi',
                'pl' => 'Zarządzaj pakietami',
            ],
            'AdminMbePackageProductHelper' => [
                'de' => 'Produktpakete verwalten',
                'en' => 'Package management for products',
                'es' => 'Gestión de paquetes para productos',
                'fr' => 'Gérer les colis par défaut à associer aux colis par défaut',
                'it' => 'Gestione pacchi per prodotti',
                'pl' => 'Zarządzaj pakietami produktów',
            ],
            'AdminMbeChecklist' => [
                'de' => 'Modulfunktion prüfen',
                'en' => 'Check module functionality',
                'es' => 'Comprobar la funcionalidad del módulo',
                'fr' => 'Vérifier la fonctionnalité du module',
                'it' => 'Verifica funzionalità del modulo',
                'pl' => 'Sprawdź funkcjonalność modułu',
            ],
            // TODO: add translations
            'AdminMbePickupAddressHelper' => [
                'de' => 'MBE - Abholadresse',
                'en' => 'MBE - Pickup address',
                'es' => 'MBE - Dirección de recogida',
                'fr' => 'MBE - Adresses d\'enlèvement',
                'it' => 'MBE - Indirizzo di ritiro',
                'pl' => 'MBE - Adres odbioru',
            ],
            'AdminMbePickupOrders' => [
                'de' => 'MBE - Abholaufträge',
                'en' => 'MBE - Pickup orders',
                'es' => 'MBE - Recogidas de pedidos',
                'fr' => 'MBE - Demandes d\'enlèvement',
                'it' => 'MBE - Ordini pickup',
                'pl' => 'MBE - Zamówienia do odbioru',
            ],
        ];

        if (!isset($tab_name_lang[$tab_class_name])) {
            return $tab_class_name;
        }

        if (isset($tab_name_lang[$tab_class_name][$lang_iso_code])) {
            return $tab_name_lang[$tab_class_name][$lang_iso_code];
        }

        return $tab_name_lang[$tab_class_name]['en'];
    }

    public function installSingleTab($name, $class_name, $id_parent = -1, $active = 1)
    {
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $class_name;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->getTabName($class_name, $lang['iso_code']);
        }
        $tab->id_parent = $id_parent;
        $tab->module = $this->name;
        return $tab->add();
    }

    public function installOverride()
    {
        return true;
    }

    public function installHook()
    {
        if (!$this->registerHook('actionOrderStatusPostUpdate')) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            /* ACCESS POINT */
            if (!$this->registerHook('extraCarrier')) {
                return false;
            }
            if (!$this->registerHook('displayCarrierList')) {
                return false;
            }
        } else {
            /* ACCESS POINT */
            if (!$this->registerHook('displayCarrierExtraContent')) {
                return false;
            }

            /* GTM (HEAD) */
            if (!$this->registerHook('displayBackOfficeHeader')) {
                return false;
            }

            /* GTM (BODY) */
            if (!$this->registerHook('displayBackOfficeTop')) {
                return false;
            }
        }

        if (!$this->registerHook('actionValidateOrder')) {
            return false;
        }

        if (!$this->registerHook('actionDispatcher')) {
            return false;
        }

        if (!$this->registerHook('header')) {
            return false;
        }

        if (!$this->registerHook('displayHeader')) {
            return false;
        }

        if (!$this->registerHook('displayOrderConfirmation')) {
            return false;
        }

        return true;
    }

    public function installRatesTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\RatesHelper();
        return $helper->installRatesTable();
    }

    public function installCsvPackageTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PackageHelper();
        return $helper->installCsvPackageTable();
    }

    public function installCsvPackageProductTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PackageProductHelper();
        return $helper->installCsvPackageProductTable();
    }

    public function installMdpTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\MdpHelper();
        return $helper->installMdpTable();
    }

    public function installMOrderTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\MOrderHelper();
        return $helper->installMOrderTable();
    }

    public function installPickupAddressTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PickupAddressHelper();
        return $helper->installPickupAddressTable();
    }

    public function installRatesCacheTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\RatesCacheHelper();
        return $helper->installRatesCacheTable();
    }

    public function installPickupBatchTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PickupBatchHelper();
        return $helper->installPickupBatchTable();
    }

    public function installDeliveryPointTable(){
        $helper = new PrestaShop\Module\Mbeshipping\Helper\DeliveryPointHelper();
        return $helper->installDeliveryPointTable();
    }

    public function presetValues()
    {
        Configuration::updateValue('MBESHIPPING_SHOP_EDITION', $this->isShopEdition());
        Configuration::updateValue('MBESHIPPING_INITIAL_CONF', 1);
        Configuration::updateValue('default_width', 10);
        Configuration::updateValue('default_height', 10);
        Configuration::updateValue('default_length', 10);
        Configuration::updateValue('max_package_weight', 0.5);
        Configuration::updateValue('max_shipment_weight', 0.5);
    }

    public function isShopEdition()
    {
        return Module::isEnabled('smb_edition');
    }

    ################################################################################
    ## Uninstallation
    #################################################################################

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTab()
            || !$this->deleteCarriers()
            || !$this->uninstallRatesTable()
            || !$this->uninstallCsvPackageTable()
            || !$this->uninstallCsvPackageProductTable()
            || !$this->uninstallMOrderTable()
            || !$this->uninstallPickupAddressTable()
            || !$this->uninstallRatesCacheTable()
            || !$this->uninstallPickupBatchTable()
            || !$this->uninstallDeliveryPointTable()
        ) {
            return false;
        }
        $this->uninstallOverride();

        /*ACCESS POINT*/
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (!$this->unregisterHook('extraCarrier')) {
                return false;
            }
        } else {

            if (!$this->unregisterHook('displayCarrierExtraContent')) {
                return false;
            }
        }
        /**/

        $this->clearConfigs();

        return true;
    }

    public function uninstallTab()
    {
        $result = true;

        $class_list= [
            'AdminMbeShipping',
            'AdminMbePackageHelper',
            'AdminMbePackageProductHelper',
            'AdminMbeChecklist',
            'AdminMbeConfiguration',
            'AdminMbePrivateArea',
            'AdminMbePickupAddressHelper',
            'AdminMbePickupOrders'
        ];

        foreach ($class_list as $class_name) {
            $result &= $this->uninstallSingleTab($class_name);
        }

        return $result;
    }

    public function uninstallSingleTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    protected function deleteCarriers()
    {
        $result = true;
        $results = Db::getInstance()->executeS('SELECT id_carrier FROM `' . _DB_PREFIX_ .
            'carrier` where external_module_name = "' . pSQL($this->module_name) . '"');
        $idCarriers = array();
        foreach ($results as $r) {
            $idCarriers[] = $r['id_carrier'];
        }
        if (!empty($idCarriers)) {
            $result &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ .
                'carrier` where id_carrier IN (' . pSQL(implode(',', array_map('intval', $idCarriers))) . ')');
            $result &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ .
                'carrier_zone` where id_carrier IN (' . pSQL(implode(',', array_map('intval', $idCarriers))) . ')');
            $result &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ .
                'delivery` where id_carrier IN (' . pSQL(implode(',', array_map('intval', $idCarriers))) . ')');
            $result &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ .
                'range_price` where id_carrier IN (' . pSQL(implode(',', array_map('intval', $idCarriers))) . ')');
            $result &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ .
                'range_weight` where id_carrier IN (' . pSQL(implode(',', array_map('intval', $idCarriers))) . ')');
        }
        return $result;
    }

    public function uninstallRatesTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\RatesHelper();
        return $helper->uninstallRatesTable();
    }

    public function uninstallCsvPackageTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PackageHelper();
        return $helper->uninstallCsvPackageTable();
    }

    public function uninstallCsvPackageProductTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PackageProductHelper();
        return $helper->uninstallCsvPackageProductTable();
    }

    public function uninstallMdpTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\MdpHelper();
        return $helper->uninstallMdpTable();
    }

    public function uninstallMOrderTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\MOrderHelper();
        return $helper->uninstallMOrderTable();
    }

    public function uninstallPickupAddressTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PickupAddressHelper();
        return $helper->uninstallPickupAddressTable();
    }

    public function uninstallRatesCacheTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\RatesCacheHelper();
        return $helper->uninstallRatesCacheTable();
    }

    public function uninstallPickupBatchTable()
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\PickupBatchHelper();
        return $helper->uninstallPickupBatchTable();
    }

    public function uninstallDeliveryPointTable(){
        $helper = new PrestaShop\Module\Mbeshipping\Helper\DeliveryPointHelper();
        return $helper->uninstallDeliveryPointTable();
    }

    public function uninstallOverride()
    {
        $result = true;
        if (file_exists(_PS_ROOT_DIR_ . '/cache/class_index.php')) {
            unlink(_PS_ROOT_DIR_ . '/cache/class_index.php');
        }
        return $result;
    }

    private function clearConfigs()
    {
        AuthAPI::clearAuth();
        Configuration::deleteByName('mbe_active');
        Configuration::deleteByName('MBESHIPPING_INITIAL_CONF');
        Configuration::deleteByName('MBESHIPPING_CREDENTIALS');
        Configuration::deleteByName('allowed_shipment_services');
    }

    ###########################################################################
    ## Plugin configuration
    ###########################################################################

    public function getContent()
    {
        if (Tools::getIsset('force_advanced') && Configuration::get('MBESHIPPING_INITIAL_CONF')) {
            Configuration::updateValue('MBESHIPPING_INITIAL_CONF', 0);
            Configuration::updateValue('MBESHIPPING_ADVANCED_AUTH_CONF', 1);
            Configuration::updateValue('mbe_active', 1);
        }

        if (Tools::getIsset('active_tab')) {
            $this->active_tab = Tools::getValue('active_tab');
        }

        $this->initNumericFieldLabels();

        // Verify if is a Direct Channel User
        $this->is_direct_channel_user = AuthAPI::isDirectChannelUser();

        $result = $this->postProcess();

        // + Check auth credentials
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $customer = $ws->getCustomer(true);

        if (AuthAPI::hasEnteredCredentials() || Configuration::get('MBESHIPPING_ADVANCED_AUTH_CONF')) {
            !empty($customer) || $result .= $this->displayError($this->l('The credentials entered are incorrect'));
        }
        // - Check auth credentials

        if (!empty($customer) && $this->is_direct_channel_user && !Configuration::get('MBESHIPPING_ADVANCED_AUTH_CONF')) {
            // Check if exists payment
            AuthAPI::existsPayment() || $result .= $this->displayWarning(
                $this->l('Payment method not configured') . '. ' .
                "<a href=\"{$this->context->link->getAdminLink('AdminMbePrivateArea')}\">{$this->l('Add a payment method to create shipments and automatically settle your invoices')}</a>."
            );
        }

        // Check if third party pickups are enabled
        $this->third_party_pickups_allowed = AuthAPI::thirdPartyPickupsAllowed();

        $this->updateJsDefs();

        if (!(int)Configuration::get('MBESHIPPING_INITIAL_CONF')) {
            $result .= $this->checkConfiguration();
        }

        if (Tools::getIsset('login_post_registration')) {
            $result = $this->displayConfirmation($this->l('Registration successful. You now have access to MBE services, log in now.'));
        }

        return $this->displayConfiguration($result);
    }

    public function postProcess()
    {
        // download logs
        if (Tools::getIsset('downloadlogs') && Tools::getValue('downloadlogs') === "1") {
            $this->active_tab = 'debug_settings';

            $this->downloadLogs();
        }

        // delete logs
        if (Tools::getIsset('deletelogs') && Tools::getValue('deletelogs') === "1") {
            $this->active_tab = 'debug_settings';

            $this->clearLogs();
            return $this->displayConfirmation($this->l('Debug logs have been cleared.'));
        }

        // tab1 - login
        if (Tools::isSubmit('mbeLogin')) {
            return $this->processAuth();
        }

        // tab1 - advanced login
        if (Tools::isSubmit('mbeAdvAuth')) {
            Configuration::updateValue('MBESHIPPING_ADVANCED_AUTH_CONF', 1);
        }

        // tab1 - auth reset
        if (Tools::isSubmit('mbeAuthReset')) {
            AuthAPI::clearAuth();
            $this->is_direct_channel_user = AuthAPI::isDirectChannelUser();
            return $this->displayWarning($this->l('Authentication has been cleared'));
        }

        // tab1 - save adv auth settings
        if (Tools::isSubmit('submitAdvAuth')) {
            return $this->processAdvancedAuth();
        }

        // tab1 settings
        if (Tools::isSubmit('submitTab1')) {
            Configuration::updateValue('mbe_active', Tools::getValue('mbe_active'));
            return $this->displayConfirmation($this->l('Configuration updated successfully'));
        }

        // tab2 - change conf mode
        if (Tools::isSubmit('mbeChangeConfigMode')) {
            $this->active_tab = 'couriers_services_settings';

            $this->clearConfigsTab2();
            $configMode = (int)Tools::getValue('mbe_couriers_services_mode');
            if (Configuration::updateValue('MBESHIPPING_COURIERS_SERVICES_CONF_MODE', $configMode)) {
                switch ($configMode) {
                    case 1:
                        Configuration::updateValue('shipments_csv_mode', PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CSV_MODE_TOTAL);
                        Configuration::updateValue('mbe_enable_custom_mapping', 0);
                        break;
                    case 2:
                        Configuration::updateValue('shipments_csv_mode', PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CSV_MODE_DISABLED);
                        Configuration::updateValue('mbe_enable_custom_mapping', 1);
                        $this->toggleTaxDutyService(false);
                        break;
                    case 3:
                        Configuration::updateValue('shipments_csv_mode', PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CSV_MODE_DISABLED);
                        Configuration::updateValue('mbe_enable_custom_mapping', 0);
                        break;
                }
            }
        }

        // tab2 - option 1
        if (Tools::isSubmit('submitConf1Tab2')) {
            $this->active_tab = 'couriers_services_settings';

            $output = null;

            // allowed shipment services
            $mbe_allowed_shipment_services = Tools::getValue('mbe_allowed_shipment_services_1');
            Configuration::updateValue('allowed_shipment_services', implode('-', $mbe_allowed_shipment_services));

            // shipments csv upload
            if ($_FILES['shipments_csv'] && $_FILES['shipments_csv']['name'] && $_FILES['shipments_csv']['tmp_name']) {
                $path = $_FILES['shipments_csv']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext == "csv") {
                    $nowDate = new DateTime();
                    $fileName = $nowDate->format('Y-m-d_H-i-s') . '_' . 'mbe_csv.csv';
                    $filePath = $this->upload_dir . $fileName;

                    if (!file_exists($this->upload_dir)) {
                        if (!mkdir($this->upload_dir, 0777, true)) {
                            $output .= $this->displayError($this->l('The "upload" folder is not created'));
                        }
                    }

                    move_uploaded_file($_FILES['shipments_csv']['tmp_name'], $filePath);

                    // VALIDATE AND INSERT FILE
                    $this->validateCsvFileAndInsert($filePath, $output);

                    //save configuration containing the file uploaded and saved into database
                    Configuration::updateValue("shipments_csv", $fileName);

                } else {
                    $output .= $this->displayError($this->l('File upload error: only CSV format is allowed'));
                }
            }

            // csv mode
            $mbe_shipments_csv_mode = Tools::getValue('shipments_csv_mode');
            Configuration::updateValue('shipments_csv_mode', $mbe_shipments_csv_mode);

            // insurance minimum price
            $mbe_shipments_csv_insurance_min = Tools::getValue('mbe_shipments_csv_insurance_min');
            if (!is_numeric($mbe_shipments_csv_insurance_min)) {
                $output .= $this->displayError($this->numeric_field_labels['mbe_shipments_csv_insurance_min'] . ': ' . $this->l('please enter only numbers 0-9'));
            } else {
                Configuration::updateValue('mbe_shipments_csv_insurance_min', $mbe_shipments_csv_insurance_min);
            }

            // insurance percentage
            $mbe_shipments_csv_insurance_per = Tools::getValue('mbe_shipments_csv_insurance_per');
            if (!is_numeric($mbe_shipments_csv_insurance_per)) {
                $output .= $this->displayError($this->numeric_field_labels['mbe_shipments_csv_insurance_per'] . ': ' . $this->l('please enter only numbers 0-9'));
            } else {
                Configuration::updateValue('mbe_shipments_csv_insurance_per', $mbe_shipments_csv_insurance_per);
            }

            // insurance mode
            $mbe_shipments_ins_mode = Tools::getValue('mbe_shipments_ins_mode');
            Configuration::updateValue('mbe_shipments_ins_mode', $mbe_shipments_ins_mode);

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab2 - option 2
        if (Tools::isSubmit('submitConf2Tab2')) {
            $this->active_tab = 'couriers_services_settings';

            $output = null;

            $mbe_allowed_shipment_services = Tools::getValue('mbe_allowed_shipment_services_2');
            Configuration::updateValue('allowed_shipment_services', implode('-', $mbe_allowed_shipment_services));

            foreach ($this->getCarrierIds() as $id_carrier) {
                Configuration::updateValue('mbe_custom_mapping_' . $id_carrier, Tools::getValue('mbe_custom_mapping_' . $id_carrier));
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab2 - option 3
        if (Tools::isSubmit('submitConf3Tab2')) {
            $this->active_tab = 'couriers_services_settings';

            $output = null;

            $mbe_allowed_shipment_services = Tools::getValue('mbe_allowed_shipment_services_3');
            $allowed_shipment_services = is_array($mbe_allowed_shipment_services) ? implode('-', $mbe_allowed_shipment_services) : '';
            Configuration::updateValue('allowed_shipment_services', $allowed_shipment_services);

            $services = (new PrestaShop\Module\Mbeshipping\Ws)->getAllowedShipmentServices();
            //Push Merged Delivery Point Service
            $isServiceNumeric = false;
            if(is_numeric($services[0]['value'])) {
                $isServiceNumeric = true;
            }
            array_push($services,
                [
                    'label' => $this->l('Delivery Point'),
                    'value' => $isServiceNumeric ? '1112' : 'NMDP-GPP',
                ]
            );

            foreach ($services as $service) {
                $id_service = Tools::strtolower(str_replace('+', 'p', $service['value']));
                Configuration::updateValue('mbe_custom_label_' . $id_service, Tools::getValue('mbe_custom_label_' . $id_service));
                Configuration::updateValue('mbe_tax_rule_' . $id_service, (int)Tools::getValue('mbe_tax_rule_' . $id_service));
                $this->updateCarrierTaxRulesGroup(Tools::getValue('mbe_custom_label_' . $id_service), (int)Tools::getValue('mbe_tax_rule_' . $id_service));
                $this->updateCarrierDelay($service);
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab3 - Standard packages
        if (Tools::isSubmit('submitForm1Tab3')) {
            $this->active_tab = 'packages_settings';

            $output = '';

            foreach ($this->getForm1Tab3()['form']['input'] as $input) {
                $val = Tools::getValue($input['name']);
                if (in_array($input['name'], $this->numeric_fields) && !is_numeric($val)) {
                    $output .= $this->displayError($this->numeric_field_labels[$input['name']] . ': ' . $this->l(' please enter only numbers 0-9'));
                } else {
                    Configuration::updateValue($input['name'], $val);
                }
            }

            if ((int)Configuration::get('mbe_shipping_use_packages_csv')) {
                Configuration::updateValue('shipment_configuration_mode', 2);
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab3 - Standard packages CSV
        if (Tools::isSubmit('submitForm2Tab3')) {
            $this->active_tab = 'packages_settings';

            $output = '';

            Configuration::updateValue('mbe_shipping_use_packages_csv', Tools::getValue('mbe_shipping_use_packages_csv'));

            if (array_key_exists('mbe_shipping_packages_csv', $_FILES) && $_FILES['mbe_shipping_packages_csv'] &&
                $_FILES['mbe_shipping_packages_csv']['name'] && $_FILES['mbe_shipping_packages_csv']['tmp_name']) {
                $path = $_FILES['mbe_shipping_packages_csv']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext == "csv") {
                    $fileName = date('Y-m-d_H-i-s') . '_' . 'mbe_shipping_packages_csv.csv';
                    $filePath = $this->upload_dir . $fileName;

                    if (!file_exists($this->upload_dir)) {
                        if (!mkdir($this->upload_dir, 0777, true)) {
                            $output .= $this->displayError($this->l('The "upload" folder is not created'));
                        }
                    }

                    move_uploaded_file($_FILES['mbe_shipping_packages_csv']['tmp_name'], $filePath);

                    // VALIDATE AND INSERT FILE
                    $this->validateCsvPackageFileAndInsert($filePath, $output);

                    //save configuration containing the file uploaded and saved into database
                    Configuration::updateValue("mbe_shipping_packages_csv", $fileName);

                } else {
                    $output .= $this->displayError($this->l('File upload error: only CSV format is allowed'));
                }
            }

            if (array_key_exists('mbe_shipping_packages_product_csv', $_FILES) &&
                $_FILES['mbe_shipping_packages_product_csv'] && $_FILES['mbe_shipping_packages_product_csv']['name'] &&
                $_FILES['mbe_shipping_packages_product_csv']['tmp_name']) {
                $path = $_FILES['mbe_shipping_packages_product_csv']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext == "csv") {
                    $fileName = date('Y-m-d_H-i-s') . '_' . 'mbe_shipping_packages_product_csv.csv';
                    $filePath = $this->upload_dir . $fileName;

                    if (!file_exists($this->upload_dir)) {
                        if (!mkdir($this->upload_dir, 0777, true)) {
                            $output .= $this->displayError($this->l('The "upload" folder is not created'));
                        }
                    }

                    move_uploaded_file($_FILES['mbe_shipping_packages_product_csv']['tmp_name'], $filePath);

                    // VALIDATE AND INSERT FILE
                    $this->validateCsvPackageProductFileAndInsert($filePath, $output);

                    //save configuration containing the file uploaded and saved into database
                    Configuration::updateValue("mbe_shipping_packages_product_csv", $fileName);

                } else {
                    $output .= $this->displayError($this->l('File upload error: only CSV format is allowed'));
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab4 - Shipping
        if (Tools::isSubmit('submitTab4')) {
            $this->active_tab = 'shipping_settings';

            $output = '';

            foreach ($this->getFormTab4()['form']['input'] as $input) {
                if (in_array($input['name'], $this->numeric_fields)) {
                    $val = Tools::getValue($input['name']);
                    if (!is_numeric($val)) {
                        $output .= $this->displayError($this->numeric_field_labels[$input['name']] . ': ' . $this->l(' please enter only numbers 0-9'));
                    } else {
                        Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                    }
                } else {
                    switch ($input['name']) {
                        case 'specificcountry[]':
                            $specificcountry = Tools::getValue('specificcountry', '');
                            $specificcountry = is_array($specificcountry) ? implode('-', $specificcountry) : '';
                            Configuration::updateValue('specificcountry', $specificcountry);
                            break;
                        case 'shipments_creation_mode':
                            $shipments_creation_mode = Tools::getValue('shipments_creation_mode', '');
                            Configuration::updateValue('shipments_creation_mode', $shipments_creation_mode);
                            //Configuration::updateValue('MBESHIPPING_PICKUP_REQUEST_MODE', $shipments_creation_mode); Riga #2 File Excel
                            $this->setShippingAndPickupConditions();
                            break;
                        default:
                            Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                    }
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab - tax duty service
        if (Tools::isSubmit('submitTabTaxDutyService')) {
            $this->active_tab = 'tax_duty_service';

            foreach ($this->getFormTabTaxDutyService()['form']['input'] as $input) {
                Configuration::updateValue($input['name'], Tools::getValue($input['name']));
            }

            return $this->displayConfirmation($this->l('Settings updated'));
        }

        // tab - pickup management
        if (Tools::isSubmit('submitTabPickupManagement')) {
            $this->active_tab = 'pickup_management';

            $output = '';

            $values = Tools::getAllValues();

            $pickup_request_mode = Configuration::get('MBESHIPPING_PICKUP_REQUEST_MODE');
            if($pickup_request_mode == 'manually') {
                $values['MBESHIPPING_PICKUP_CUTOFF_PERIOD'] = Configuration::get('MBESHIPPING_PICKUP_CUTOFF_PERIOD');
            }

            $ws = new PrestaShop\Module\Mbeshipping\Ws();
            $ws_result = $ws->setPickupDefaultData(
                $values['MBESHIPPING_PICKUP_CUTOFF_PERIOD'],
                $values['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM'],
                $values['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO'],
                $values['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM'],
                $values['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO'],
                $values['MBESHIPPING_PICKUP_NOTES']
            );

            if (!$ws_result) {
                $output .= $this->displayError($this->l('Error while saving pickup default data'));
            }

            $skip_inputs = ['pickup_address', 'alert'];
            foreach ($this->getFormTabPickupManagement()['form']['input'] as $input) {
                if (in_array($input['type'], $skip_inputs)) {
                    continue;
                }

                Configuration::updateValue($input['name'], Tools::getValue($input['name']));

                if ($input['name'] === 'MBESHIPPING_PICKUP_REQUEST_MODE' &&
                    Tools::getValue($input['name']) === 'manually'
                ) {
                    $this->toggleTaxDutyService(false);
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab5 - Recharge settings
        if (Tools::isSubmit('submitForm1Tab5')) {
            $this->active_tab = 'recharge_settings';

            $output = '';

            foreach ($this->getForm1Tab5()['form']['input'] as $input) {
                if (in_array($input['name'], $this->numeric_fields)) {
                    $val = Tools::getValue($input['name']);
                    if (!is_numeric($val)) {
                        $output .= $this->displayError($this->numeric_field_labels[$input['name']] . ': ' . $this->l(' please enter only numbers 0-9'));
                    } else {
                        Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                    }
                } else {
                    Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab5 - MBE Shipments
        if (Tools::isSubmit('submitForm2Tab5')) {
            $this->active_tab = 'recharge_settings';

            $output = '';

            foreach ($this->getForm2Tab5()['form']['input'] as $input) {
                if (substr($input['name'], 0, 9) === 'mbelimit_') {
                    $val = Tools::getValue($input['name']);
                    if (!empty($val) && !is_numeric($val)) {
                        $output .= $this->displayError($this->numeric_field_labels[$input['name']] . ': ' . $this->l(' please enter only numbers 0-9'));
                    } else {
                        Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                    }
                } else {
                    Configuration::updateValue($input['name'], Tools::getValue($input['name']));
                }
            }

            if (empty($output)) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

            return $output;
        }

        // tab6 - Debug
        if (Tools::isSubmit('submitTab6')) {
            $this->active_tab = 'debug_settings';

            Configuration::updateValue('mbe_debug', Tools::getValue('mbe_debug'));
            return $this->displayConfirmation($this->l('Settings updated'));
        }

        return '';
    }

    private function disableServicesForMapping() {
        $allowed_shipment_services = explode('-', Configuration::get('allowed_shipment_services'));

        $allowed_shipment_services = $this->deleteServicesForMapping($allowed_shipment_services);

        Configuration::UpdateValue('allowed_shipment_services', implode('-', $allowed_shipment_services));
    }

    private function deleteServicesForMapping($services)
    {
        foreach ($services as $key => $service) {
            if (is_string($service)) {
                // for disableServicesForMapping

                if (strstr($service, "NMDP") || strstr($service, "GPP")) {
                    unset($services[$key]);
                }
            } else if (isset($service['id_option'])) {
                // for getServiceOptions

                if (strstr($service['id_option'], "NMDP") || strstr($service['id_option'], "GPP")) {
                    unset($services[$key]);
                } else {
                    // for number services

                    if ($service['id_option'] == 11 || $service['id_option'] == 12) {
                        unset($services[$key]);
                    }
                }
            }
        }

        return $services;
    }

    private function downloadLogs()
    {
        $logs_path = $this->local_path . 'log/';
        $path = $logs_path . 'compressed.zip';
        if (file_exists($path)) {
            unlink($path);
        }
        $values = array();
        $files = glob($logs_path . '/*');
        foreach ($files as $f) {
            $paths = explode('/', $f);
            $values[] = end($paths);
        }
        chdir($logs_path);
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            $zipArchive = new ZipArchive();
            if (!$zipArchive->open($path, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE)) {
                die("[ERROR] ZipArchive");
            }
            if(sizeof($values)) {
                foreach($values as $file) {
                    $zipArchive->addFile($this->local_path . 'log/'.$file, $file);
                }
            }
            $zipArchive->close();
        } else {
            $archive = new PclZip($path);
            $response = $archive->create($values);
            if ($response == 0) {
                die("[ERROR] PclZip : " . $archive->errorInfo(true));
            }
        }

        if (headers_sent()) {
            echo 'HTTP header already sent';
        } else {
            if (!is_file($path)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                echo 'File not found';
            } else {
                if (!is_readable($path)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                    echo 'File not readable';
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($path));
                    header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
                    ob_clean();
                    flush();
                    readfile($path);
                    exit;
                }
            }
        }
        die;
    }

    private function clearLogs()
    {
        $keepFiles = ['index.php', '.htaccess'];

        $logs_path = $this->local_path . 'log/';
        $files = glob($logs_path . '/*');
        foreach ($files as $file) {
            if (is_file($file) && !in_array(basename($file), $keepFiles)) {
                unlink($file);
            }
        }
    }

    public function processAuth()
    {
        Configuration::updateValue('MBESHIPPING_ADVANCED_AUTH_CONF', 0);

        $mbe_credentials['mbecountry'] = Tools::getValue('mbecountry');
        $mbe_credentials['mbe_user'] = Tools::getValue('mbe_user');
        $mbe_credentials['mbe_pass'] = Tools::getValue('mbe_pass');

        Configuration::updateValue('MBESHIPPING_CREDENTIALS', json_encode($mbe_credentials));

        $api = new AuthAPI();
        if ($api->retrieveAPIKeys() === true) {
            Configuration::updateValue('mbe_active', 1);
            $is_initial_configuration = Configuration::get('MBESHIPPING_INITIAL_CONF');

            if ($is_initial_configuration) {
                Configuration::updateValue('MBESHIPPING_INITIAL_CONF', false);
            }

            $is_direct_channel_user = AuthAPI::isDirectChannelUser();
            if ($is_initial_configuration && $is_direct_channel_user) {
                AuthAPI::presetDirectChannelUserServices();
            }

            $this->is_direct_channel_user = $is_direct_channel_user;
            return $this->displayConfirmation($this->l('Login successful'));
        }

        $this->is_direct_channel_user = AuthAPI::isDirectChannelUser();
        return $this->displayError($this->l('Login error, please check your credentials'));
    }

    public function processAdvancedAuth()
    {
        Configuration::updateValue('MBESHIPPING_INITIAL_CONF', 0);
        Configuration::updateValue('username', Tools::getValue('username'));
        Configuration::updateValue('password', Tools::getValue('password'));
        Configuration::updateValue('mbecountry', Tools::getValue('mbecountry'));
        Configuration::updateValue('url', Tools::getValue('url'));

        return $this->displayConfirmation($this->l('Settings updated'));
    }

    public function clearConfigsTab2()
    {
        $config_list = [
            'shipments_csv',
            'shipments_csv_mode',
            'mbe_shipments_csv_insurance_min',
            'mbe_shipments_csv_insurance_per',
            'mbe_shipments_ins_mode',
        ];

        foreach ($this->renderCouriersServicesMapping() as $carrier) {
            $config_list[] = $carrier['name'];
        }

        foreach ($this->renderServicesCustomDescription() as $service) {
            $config_list[] = $service['name'];
        }

        foreach ($config_list as $config) {
            Configuration::deleteByName($config);
        }
    }

    private function getCarrierIds() {
        return array_map(function ($carrier) {
            return (int)$carrier['id_carrier'];
         }, Carrier::getCarriers($this->context->language->id));
    }

    private function updateCarrierTaxRulesGroup($carrier_name, $id_tax_rules_group)
    {
        $id_carrier = (int)Db::getInstance()->getValue('SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'carrier` where name = "' . pSQL($carrier_name) . '" AND deleted="0"');
        if ($id_carrier > 0) {
            $carrier = new Carrier($id_carrier);
            $carrier->setTaxRulesGroup($id_tax_rules_group);
            foreach (Language::getLanguages() as $lang) {
                if (empty($carrier->delay[$lang['id_lang']])) {
                    $carrier->delay[$lang['id_lang']] = ' ';
                }
            }
            $carrier->save();
        }
    }

    private function updateCarrierDelay($service)
    {
        $defaultValuesNumericKey = [
            '2' => [
                'it' => 'Consegna in 2-5 giorni',
                'es' => 'Entrega en 2-5 días',
                'fr' => 'Livraison en 2-5 jours',
                'en' => 'Delivery in 2-5 days',
                'pl' => 'Dostawa w 2-5 dni',
                'de' => 'Lieferung in 2-5 Tagen',
            ],
            '4' => [
                'it' => $this->is_direct_channel_user ? 'Consegna in 1-2 giorni' : 'Consegna in 1-5 giorni',
                'es' => $this->is_direct_channel_user ? 'Livraison en 1-2 jours' : 'Livraison en 1-5 jours',
                'fr' => $this->is_direct_channel_user ? 'Entrega en 1-2 días' : 'Entrega en 1-5 días',
                'en' => $this->is_direct_channel_user ? 'Delivery in 1-2 days' : 'Delivery in 1-5 days',
                'pl' => $this->is_direct_channel_user ? 'Dostawa w 1-2 dni' : 'Dostawa w 1-5 dni',
                'de' => $this->is_direct_channel_user ? 'Lieferung in 1-2 Tagen' : 'Lieferung in 1-5 Tagen',
            ],
            '11' => [
                'it' => 'Consegna in 2-3 giorni (punto di ritiro)',
                'es' => 'Livraison en 2-3 jours (point de collecte)',
                'fr' => 'Entrega en 2-3 días (punto de recogida)',
                'en' => 'Delivery in 2-3 days (collection point)',
                'pl' => 'dostawa w 2-3 (punktach odbioru)',
                'de' => 'Lieferung an 2-3 (Abholstellen)',
            ],
        ];

        $defaultValuesAlphanumericKey = [
            'SSE' => [
                'it' => 'Consegna in 2-5 giorni',
                'es' => 'Entrega en 2-5 días',
                'fr' => 'Livraison en 2-5 jours',
                'en' => 'Delivery in 2-5 days',
                'pl' => 'Dostawa w 2-5 dni',
                'de' => 'Lieferung in 2-5 Tagen',
            ],
            'SAR' => [
                'it' => $this->is_direct_channel_user ? 'Consegna in 1-2 giorni' : 'Consegna in 1-5 giorni',
                'es' => $this->is_direct_channel_user ? 'Livraison en 1-2 jours' : 'Livraison en 1-5 jours',
                'fr' => $this->is_direct_channel_user ? 'Entrega en 1-2 días' : 'Entrega en 1-5 días',
                'en' => $this->is_direct_channel_user ? 'Delivery in 1-2 days' : 'Delivery in 1-5 days',
                'pl' => $this->is_direct_channel_user ? 'Dostawa w 1-2 dni' : 'Dostawa w 1-5 dni',
                'de' => $this->is_direct_channel_user ? 'Lieferung in 1-2 Tagen' : 'Lieferung in 1-5 Tagen',
            ],
            'MDP' => [
                'it' => 'Consegna in 2-3 giorni (punto di ritiro)',
                'es' => 'Livraison en 2-3 jours (point de collecte)',
                'fr' => 'Entrega en 2-3 días (punto de recogida)',
                'en' => 'Delivery in 2-3 days (collection point)',
                'pl' => 'dostawa w 2-3 (punktach odbioru)',
                'de' => 'Lieferung an 2-3 (Abholstellen)',
            ],
            'SSE_INSURANCE' => [
                'it' => 'Consegna in 2-5 giorni',
                'es' => 'Entrega en 2-5 días',
                'fr' => 'Livraison en 2-5 jours',
                'en' => 'Delivery in 2-5 days',
            ],
            'SAR_INSURANCE' => [
                'it' => $this->is_direct_channel_user ? 'Consegna in 1-2 giorni' : 'Consegna in 1-5 giorni',
                'es' => $this->is_direct_channel_user ? 'Livraison en 1-2 jours' : 'Livraison en 1-5 jours',
                'fr' => $this->is_direct_channel_user ? 'Entrega en 1-2 días' : 'Entrega en 1-5 días',
                'en' => $this->is_direct_channel_user ? 'Delivery in 1-2 days' : 'Delivery in 1-5 days',
            ],
            'MDP_INSURANCE' => [
                'it' => 'Consegna in 2-3 giorni (punto di ritiro)',
                'es' => 'Livraison en 2-3 jours (point de collecte)',
                'fr' => 'Entrega en 2-3 días (punto de recogida)',
                'en' => 'Delivery in 2-3 days (collection point)',
            ],
        ];

        $defaultValues = $defaultValuesAlphanumericKey;
        if(is_numeric($service['value'])) {
            $defaultValues = $defaultValuesNumericKey;
        }

        if(isset($defaultValues[$service['value']])) {
            foreach (Language::getLanguages() as $lang) {
                $key = 'mbeshippingdelay_' . Tools::substr(md5($service['label'] . '_' .$lang['iso_code']), 0, 15);
                $existing_value = Configuration::get($key);
                if(empty($existing_value)) {
                    Configuration::updateValue($key, $defaultValues[$service['value']][$lang['iso_code']]);
                }
            }
        }
    }

    public function renderCouriersServicesMapping()
    {
        $result = [];
        foreach (Carrier::getCarriers($this->context->language->id) as $carrier) {
            $result[] = [
                'type' => 'select',
                'label' => $this->l('Custom mapping for') . ' "' . $carrier['name'] . '"',
                'name' => 'mbe_custom_mapping_' . Tools::strtolower($carrier['id_carrier']),
                'options' => $this->getServiceOptions(),
                'desc' => $this->l('Select the custom mapping for the default shipping method') . '. ' . $this->l('Leave blank if you don\'t want to map it'),
            ];
        }
        return $result;
    }

    public function getServiceOptions()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $availableShipping = $ws->getAllowedShipmentServices();
        if ($ws->isCustomerActive()) {
            if ($availableShipping) {
                $result = [];

                // for the value is NMDP or GPP not create INSURANCE
                foreach ($availableShipping as $key => $array) {
                    if($array['value'] !== "NMDP_INSURANCE" &&  $array['value'] !== "GPP_INSURANCE"){
                        $result[$key] = ['id_option' => $array['value'], 'name' => $array['label']];
                    }
                }

                array_unshift($result, ['id_option' => ' ', 'name' => ' ']);

                //if the mapping are selected disable all the MDP service
                if (Configuration::get('mbe_enable_custom_mapping')) {
                    $result = $this->deleteServicesForMapping($result);
                }

                return ['query' => $result, 'id' => 'id_option', 'name' => 'name'];

            } else {
                return [
                    'query' => [
                        ['id_option' => 'express', 'name' => $this->l('Set ws parameters and save to retrieve available shipment types')]
                    ],
                    'id' => 'id_option',
                    'name' => 'name',
                ];
            }
        }

        return [
            'query' => [
                ['id_option' => '', 'name' => $this->l('Your user is not active')]
            ],
            'id' => 'id_option',
            'name' => 'name',
        ];
    }

    public function renderServicesCustomDescription()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $available_services = $ws->getAllowedShipmentServices();

        $result = [];

        //new "input" if the service recovery is active
        if (Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE') == 3) {
            //Push Merged Delivery Point Service
            if(!empty($available_services)) {
                $isServiceNumeric = false;
                if(is_numeric($available_services[0]['value'])) {
                    $isServiceNumeric = true;
                }
                array_push($available_services,
                    [
                        'label' => $this->l('Delivery Point'),
                        'value' => $isServiceNumeric ? '1112' : 'NMDP-GPP',
                    ]
                );
            }
        }

        foreach ($available_services as $service) {
            array_push($result,
                [
                    'type' => 'text',
                    'label' => $this->l('Custom description for service') . ' "' . $service['label'] . '"',
                    'id' => 'mbe_custom_label_' .
                        Tools::strtolower(str_replace('+', 'p', $service['value'])),
                    'name' => 'mbe_custom_label_' .
                        Tools::strtolower(str_replace('+', 'p', $service['value'])),
                    'class' => 'fixed-width-xxl',
                    'desc' => $this->l('Insert the custom name for the shipment method. Leave blank if you don\'t want to change the default value'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('VAT for') . ' "' . $service['label'] . '"',
                    'name' => 'mbe_tax_rule_' . Tools::strtolower(str_replace('+', 'p', $service['value'])),
                    'options' => [
                        'query' => TaxRulesGroup::getTaxRulesGroups(),
                        'id' => 'id_tax_rules_group',
                        'name' => 'name',
                        'default' => [
                            'label' => $this->l('No tax'),
                            'value' => 0,
                        ],
                    ],
                    'desc' => $this->l('Select the tax to be applied for the shipping method'),
                ]);
        }
        return $result;
    }

    public function validateCsvFileAndInsert($filePath, &$output)
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\CsvHelper();

        $rates = $helper->readFile($filePath);

        //VALIDATE

        $errors = false;
        $i = 1;

        $allowedShipmentServices = Configuration::get('allowed_shipment_services');
        $allowedShipmentServicesArray = explode('-', $allowedShipmentServices);
        $maxShipmentWeight = Configuration::get('max_shipment_weight');


        foreach ($rates as $rate) {
            if (Tools::strlen($rate["country"]) > 2) {
                $output .= $this->displayError(sprintf($this->l('File upload error: row %d: "%s", COUNTRY column. 
                Use destination Country in 2 character ISO format (e.g. IT for Italy, ES for Spain, DE for Germany)"'),
                    $i, $rate["country"]));
                $errors = true;
            }

            if (!in_array($rate["delivery_type"], $allowedShipmentServicesArray)) {
                $output .= $this->displayError(sprintf($this->l('File upload error: row %d: "%s", 
                SHIPMENT TYPE column. Input code is not a valid MBE Service'), $i, $rate["delivery_type"]));
                $errors = true;
            }

            if ($maxShipmentWeight) {
                if ($rate["weight_from"] > $maxShipmentWeight) {
                    $output .= $this->displayError(sprintf($this->l('File upload error: row %d: "%s", 
                    WEIGHT column. Input weight exceeds allowed'), $i, $rate["weight_from"]));
                    $errors = true;
                }
                if ($rate["weight_to"] > $maxShipmentWeight) {
                    $output .= $this->displayError(sprintf($this->l('File upload error: row %d: "%s", 
                    WEIGHT column. Input weight exceeds allowed'), $i, $rate["weight_to"]));
                    $errors = true;
                }
            }
            $i++;
        }

        if ($errors) {
            return false;
        }

        //INSERT
        $ratesHelper = new PrestaShop\Module\Mbeshipping\Helper\RatesHelper();
        $truncateResult = $ratesHelper->truncate();

        if (!$truncateResult) {
            $this->displayError($this->l('Error executing truncate query '));
        }

        foreach ($rates as $rate) {
            $insertResult = $ratesHelper->insertRate($rate["country"], $rate["region"], $rate["city"], $rate["zip"],
                $rate["zip_to"], $rate["weight_from"], $rate["weight_to"], $rate["price"], $rate["delivery_type"]);

            if (!$insertResult) {
                $this->displayError($this->l('Error executing query '));
            }
        }

    }

    public function validateCsvPackageFileAndInsert($filePath, &$output)
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\CsvHelper();

        $packages = $helper->readFile($filePath);

        $PackageHelper = new PrestaShop\Module\Mbeshipping\Helper\PackageHelper();
        $truncateResult = $PackageHelper->truncate();

        if (!$truncateResult) {
            $this->displayError($this->l('Error executing truncate query '));
        }

        foreach ($packages as $package) {
            $insertResult = $PackageHelper->insertCsvPackage($package["max_weight"], $package["length"],
                $package["width"], $package["height"], $package["package_label"], $package["package_code"]);

            if (!$insertResult) {
                $this->displayError($this->l('Error executing query '));
            }
        }
    }

    public function validateCsvPackageProductFileAndInsert($filePath, &$output)
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\CsvHelper();

        $packageProducts = $helper->readFile($filePath);

        $PackageProductHelper = new PrestaShop\Module\Mbeshipping\Helper\PackageProductHelper();
        $truncateResult = $PackageProductHelper->truncate();

        if (!$truncateResult) {
            $this->displayError($this->l('Error executing truncate query '));
        }

        foreach ($packageProducts as $packageProduct) {
            $insertResult = $PackageProductHelper->insertCsvPackageProduct($packageProduct["custom_package"],
                $packageProduct["single_parcel"], $packageProduct["product_sku"], $packageProduct["package_code"]);

            if (!$insertResult) {
                $this->displayError($this->l('Error executing query '));
            }
        }
    }

    public function getFormTab4()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $customer = $ws->getCustomer();

        $shipmentTypeOptions = [
            'query' => [
                ['id_option' => 'GENERIC', 'name' => $this->l('Generic')]],
            'id' => 'id_option',
            'name' => 'name',
        ];

        if ($customer && $customer->Permissions->canChooseMBEShipType) {
            $shipmentTypeOptions['query'][] = ['id_option' => 'ENVELOPE',
                'name' => $this->l('Envelope')];
        }

        $closureModeOptions = [
            'query' => [
                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CLOSURE_MODE_MANUALLY,
                    'name' => $this->l('Manually')],
                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CLOSURE_MODE_AUTOMATICALLY,
                    'name' => $this->l('Automatically')],
            ],
            'id' => 'id_option',
            'name' => 'name',
        ];

        $creationModeOptions = [
            'query' => [
                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CREATION_MODE_AUTOMATICALLY,
                    'name' => $this->l('Automatically')],
                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CREATION_MODE_MANUALLY,
                    'name' => $this->l('Manually')],
            ],
            'id' => 'id_option',
            'name' => 'name',
        ];

        $use_packages_csv = (int)Configuration::get('mbe_shipping_use_packages_csv');

        $form_inputs = [];
        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->is_direct_channel_user ? $this->l('Chart and shipment configuration') : $this->l('Choose a default mode'),
            'name' => 'shipment_configuration_mode',
            'options' => [
                'query' => [
                    // array('id_option' => '1', 'name' => $this->l('Create one Shipment per Item')),
                    ['id_option' => '2', 'name' => $this->l('Create one Shipment per shopping cart (parcels calculated based on weight)')],
                    ['id_option' => '3', 'name' => $this->l('Create one Shipment per shopping cart with one parcel per Item')]],
                'id' => 'id_option',
                'name' => 'name',
            ],
            'disabled' => $use_packages_csv,
            'desc' => $this->is_direct_channel_user ? $this->l('Not editable: only one shipment will be created per completed order cart and the number of packages will be calculated automatically based on the weight value declared in the appropriate section of your Prestashop site.') : $this->l('WARNING: Using the option "A different shipment for each product in the cart", in the case of cash on delivery, the total amount to be paid will be divided evenly on each shipment (i.e. based on the number of items in the cart and not according to their value)')
        ];

        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->is_direct_channel_user ? $this->l('Shipment creation on MBE systems') : $this->l('Creating shipments - Methods'),
            'name' => 'shipments_creation_mode',
            'options' => $creationModeOptions,
            //'disabled' => $this->is_direct_channel_user,
            'desc' => $this->is_direct_channel_user ? $this->l('You can select automatic or manual. Automatic means a shipment will be created on the MBE systems when the order is received. The "manual" option allows you to create them whenever you want, but you will have todo do this for each order received. Please note that the closing operation is essential for MBE to fake charge of the shipment.') : $this->l('With the "Automated Closure", the order will be automatically created on the MBE systems once the payment status on PrestaShop is "Accepted."'),
        ];

        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->is_direct_channel_user ? $this->l('Indicates "automatically" the order as delivered') : $this->l('Automatically set as shipped'),
            'name' => 'mbe_auto_change_order_status',
            'options' => [
                'query' => [
                    ['id_option' => 0, 'name' => $this->l('No')],
                    ['id_option' => 1, 'name' => $this->l('Yes')]
                ],
                'id' => 'id_option',
                'name' => 'name',
            ],
            'desc' => $this->is_direct_channel_user ? $this->l('If the setting choice is "yes", then when the order is in "payment accepted" status it will automatically be marked as "delivered". Should you choose "no" the you will have to do this manually for each order.') : $this->l('At the MBE shipment closure, if the order is in the status of "Payment accepted" will automatically be set as "Shipped" and the notification will be sent to the customer')
        ];

        foreach ($form_inputs as $input) {
            if (isset($input['disabled']) && $input['disabled']) {
                $form_inputs[] = $this->createHiddenFormInput($input['name']);
            }
        }

        // Hidden for DIRECT_CHANNEL_USER
        if (!$this->is_direct_channel_user) {
            array_splice($form_inputs, 0, 0, [
                [
                    'type' => 'select',
                    'label' => $this->l('Shipping countries'),
                    'name' => 'sallowspecific',
                    'options' => [
                        'query' => [
                            ['id_option' => '0', 'name' => $this->l('All countries available')],
                            ['id_option' => '1', 'name' => $this->l('Specific countries')]],
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                    'desc' => $this->l('Choose the countries in which you want to enable shipping')
                ]
            ]);

            array_splice($form_inputs, 1, 0, [
                [
                    'type' => 'select',
                    'label' => $this->l('Countries'),
                    'name' => 'specificcountry[]',
                    'default_value' => (int)$this->context->country->id,
                    'options' => [
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'iso_code',
                        'name' => 'name',
                    ],
                    'multiple' => true,
                    'desc' => $this->l('Choose at least one of the countries listed above')
                ]
            ]);

            array_splice($form_inputs, 3, 0, [
                [
                    'type' => 'select',
                    'label' => $this->l('Default goods type'),
                    'name' => 'default_shipment_type',
                    'options' => $shipmentTypeOptions,
                    'desc' => $this->l('Select at least one of the types of goods available')
                ]
            ]);
        }

        array_splice($form_inputs, 4, 0, [
            [
                'type' => 'select',
                'label' => $this->l('End of Day process of shipment - Methods'),
                'name' => 'shipments_closure_mode',
                'options' => $closureModeOptions,
                'desc' => $this->l('To close the shipments, select the preferred method'),
                'default_value' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CLOSURE_MODE_AUTOMATICALLY,
            ],
        ]);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Shipping'),
                ],
                'input' => $form_inputs,
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getForm1Tab5()
    {
        $form_inputs = [
            [
                'type' => 'select',
                'label' => $this->l('Calculation method'),
                'name' => 'handling_type',
                'options' => [
                    'query' => [
                        ['id_option' => 'P', 'name' => $this->l('Percentage')],
                        ['id_option' => 'F', 'name' => $this->l('Fixed amount')]],
                    'id' => 'id_option',
                    'name' => 'name',
                ],
                'desc' => $this->l('Select one of the preferred methods for applying the markup on the shipping price')
            ],
            [
                'type' => 'select',
                'label' => $this->l('Applied for'),
                'name' => 'handling_action',
                'options' => [
                    'query' => [
                        ['id_option' => 'S', 'name' => $this->l('Shipment')],
                        ['id_option' => 'P', 'name' => $this->l('Parcel')]],
                    'id' => 'id_option',
                    'name' => 'name',
                ],
                'desc' => $this->l('Indicate whether to apply the markup for the entire shipment or for a single parcel shipped')
            ],
            [
                'type' => 'text',
                'label' => $this->numeric_field_labels['handling_fee'],
                'name' => 'handling_fee',
                'class' => 'fixed-width-xl',
                'desc' => $this->l('Indicate the value to be applied as a possible markup on the shipment to the customer')
            ]
        ];

        // Hidden for DIRECT_CHANNEL_USER
        if (!$this->is_direct_channel_user) {
            array_push($form_inputs, [
                'type' => 'select',
                'label' => $this->numeric_field_labels['handling_fee_rounding'],
                'name' => 'handling_fee_rounding',
                'options' => [
                    'query' => [
                        ['id_option' => '1', 'name' => $this->l('No rounding')],
                        ['id_option' => '2', 'name' => $this->l('Round up or down automatically')],
                        ['id_option' => '3', 'name' => $this->l('Always round down')],
                        ['id_option' => '4', 'name' => $this->l('Always round up')]],
                    'id' => 'id_option',
                    'name' => 'name',
                ],
                'desc' => $this->l('Select the rounding method to apply to the shipping price')
            ], [
                'type' => 'select',
                'label' => $this->numeric_field_labels['handling_fee_rounding_amount'],
                'name' => 'handling_fee_rounding_amount',
                'options' => [
                    'query' => [
                        ['id_option' => '1', 'name' => '1'],
                        ['id_option' => '2', 'name' => '0.5']],
                    'id' => 'id_option',
                    'name' => 'name',
                ],
                'desc' => $this->l('Select the maximum rounding unit applied, choosing between €0.5 and €1')
            ]);
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Recharge'),
                ],
                'input' => $form_inputs,
                'submit' => [
                    'name' => 'submitForm1Tab5',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getForm2Tab5()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('MBE Shipments - Free shipping thresholds & Service description'),
                ],
                'input' => $this->renderFreeShippingThresholdsAndServicesDescriptions(),
                'submit' => [
                    'name' => 'submitForm2Tab5',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function renderFreeShippingThresholdsAndServicesDescriptions()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $available_services = $ws->getAllowedShipmentServices();

        //Push Merged Delivery Point Service
        $isServiceNumeric = false;
        if(!empty($available_services)) {
            if(is_numeric($available_services[0]['value'])) {
                $isServiceNumeric = true;
            }
            array_push($available_services,
                [
                    'label' => $this->l('Delivery Point').' ('.($isServiceNumeric ? '1112' : 'NMDP-GPP').')',
                    'value' => $isServiceNumeric ? '1112' : 'NMDP-GPP',
                ]
            );
        }

        $result = [];
        $active_services = explode('-', Configuration::get('allowed_shipment_services'));
        if(sizeof($active_services)) {
            $verify_selected = 0;
            foreach($active_services as $active_service) {

                if($verify_selected == 2) {
                    break;
                }

                if($isServiceNumeric) {
                    if($active_service == '11' || $active_service == '12') {
                        $verify_selected++;
                    }
                } else {
                    if($active_service == 'NMDP' || $active_service == 'GPP') {
                        $verify_selected++;
                    }
                }
            }
            if($verify_selected == 2) {
                $active_services[] = $isServiceNumeric ? '1112' : 'NMDP-GPP';
                foreach($active_services as $k => $active_service) {
                    if(in_array($active_service, array('11', '12', 'NMDP', 'GPP'))) {
                        unset($active_services[$k]);
                    }
                }
            }
        }
        foreach ($available_services as $service) {
            if (in_array($service['value'], $active_services)) {

                $val = str_replace('+', 'plus', $service['value']);
                $result[] = [
                    'type' => 'text',
                    'label' => $service['label'] . ' ' . $this->l('free shipping threshold') . ' ' . $this->l('Domestic'),
                    'placeholder' => $this->l('ex. 1'),
                    'name' => 'mbelimit_' . Tools::strtolower($val),
                    'class' => 'fixed-width-xl',
                ];
                $result[] = [
                    'type' => 'text',
                    'label' => $service['label'] . ' ' . $this->l('free shipping threshold') . ' ' . $this->l('Rest of the world'),
                    'placeholder' => $this->l('ex. 1'),
                    'name' => 'mbelimit_' . Tools::strtolower($val) . '_ww',
                    'class' => 'fixed-width-xl',
                ];
                foreach (Language::getLanguages() as $lang) {
                    $result[] = [
                        'type' => 'text',
                        'label' => $service['label'] . ' ' . $this->l('Delay') . ' ' . $lang['name'],
                        'placeholder' => $this->l('ex. 1-3 working days'),
                        'name' => 'mbeshippingdelay_' . Tools::substr(md5($service['label'] . '_' .
                                $lang['iso_code']), 0, 15),
                        'class' => 'fixed-width-xl',
                        'max-length' => 128,
                    ];
                }
            }
        }

        return $result;
    }

    public function initJsStrings() {
        $this->l('The module is not compatible with your PrestaShop\'s version. The plugin is not guaranteed to work for this version.');
        $this->l('The module does not appear to be installed correctly, try reinstalling the plugin');
        $this->l('Signing in...');
        $this->l('The field must be a valid URL');
    }

    public function updateJsDefs()
    {
        Media::addJsDef(
            [
                'mbe_ajax_check_version_error' => $this->transJsString('The module is not compatible with your PrestaShop\'s version. The plugin is not guaranteed to work for this version.'),
                'mbe_ajax_check_generic_error' => $this->transJsString('The module does not appear to be installed correctly, try reinstalling the plugin'),
                'mbe_ajax_check_controller_url' => $this->context->link->getAdminLink('AdminMbeChecklist'),
                'tab2_conf_mode' => (string)Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE'),
                'active_tab' => $this->active_tab
            ]
        );
    }

    public function displayConfiguration($result = '')
    {
        $this->context->controller->addJS($this->_path . 'views/js/select2.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/select2.min.css');
        $this->context->controller->addCSS($this->_path . 'views/css/common.css');
        if (version_compare(_PS_VERSION_, '1.7.8', '<')) {
            $this->context->controller->addCSS($this->_path . 'views/css/back_16.css');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }

        // links
        $links = $this->getLinks();

        // configuration tabs
        $this->conf_tabs = [];
        if ((int)Configuration::get('MBESHIPPING_INITIAL_CONF')) {
            $this->context->smarty->assign([
                'banner_eship' => $links['banner_eship'],
                'banner_background' => $links['background'],
                'banner_advantage' => $links['advantage'],
                'checklist' => $links['checklist'],
//                'banner_packing' => $links['banner_packing'],
                'link_contact' => $links['contact']['mbe'],
                'logo_welcome_first' => $links['logo_welcome_first'],
                'illustration_welcome_first' => $links['illustration_welcome_first'],
                'carriers_icon' => $links['carriers_icon'],
                'configuration_icon' => $links['configuration_icon'],
                'customer_service_icon' => $links['customer_service_icon'],
                'returns_icon' => $links['returns_icon'],
            ]);

            $this->conf_tabs['welcome'] = [
                'id' => 'welcome',
                'label' => $this->l('Welcome'),
                'icon_class' => '',
                'content' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/welcome.tpl'),
                'show_this' => !Tools::getIsset('login_post_registration'),
            ];
            // initial login
            $this->conf_tabs['initial_login'] = [
                'id' => 'initial_login',
                'label' => '',
                'icon_class' => '',
                'content' => $this->displayInitialLogin(),
                'show_this' => Tools::getIsset('login_post_registration'),
            ];
            // registration iframe
            $this->context->smarty->assign([
                'registration_iframe_url' => 'https://www.mbe-hub.com/direct-channel-registration/registration',
                'registration_iframe_lang' => Language::getIsoById($this->context->employee->id_lang),
                'registration_iframe_login_url' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&login_post_registration=1'
            ]);
            $this->conf_tabs['registration'] = [
                'id' => 'registration',
                'label' => '',
                'icon_class' => '',
                'content' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/registration.tpl'),
                'show_this' => false,
            ];
        }
        $this->conf_tabs['general_settings'] = [
            'id' => 'general_settings',
            'label' => $this->l('General'),
            'icon_class' => '',
            'content' => $this->displayTab1(),
            'show_this' => !(int)Configuration::get('MBESHIPPING_INITIAL_CONF'),
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=5',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=5',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=5',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=5',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=5',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=5'
            ]
        ];
        $this->conf_tabs['couriers_services_settings'] = [
            'id' => 'couriers_services_settings',
            'label' => $this->l('Couriers and services'),
            'icon_class' => '',
            'content' => $this->displayTab2(),
            'show_this' => false,
            'desc' => $this->l('This screen allows you to configure your shipping services. The services that can be activated have already been entered in the "MBE services&" box, so you can only customize the descriptions of the activated services and possibly delete those you do not want to offer.'),
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=6',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=6',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=6',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=6',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=6',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=6'
            ]
        ];
        $this->conf_tabs['packages_settings'] = [
            'id' => 'packages_settings',
            'label' => $this->l('Packages'),
            'icon_class' => '',
            'content' => $this->displayTab3(),
            'show_this' => false,
            'desc' => $this->l('This screen allows you to define and configure your "standard" parcels, i.e. the parcels you use most frequently. In the first part, you can define the "standard" parcel, while below you can activate the "Customised Parcel Configuration" at the bottom of the page to recall the settings made within the "Parcel Management" section of the Prestashop site.'),
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=7',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=7',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=7',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=7',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=7',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=7'
            ]
        ];
        $this->conf_tabs['shipping_settings'] = [
            'id' => 'shipping_settings',
            'label' => $this->l('Shipping'),
            'icon_class' => '',
            'content' => $this->displayTab4(),
            'show_this' => false,
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=8',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=8',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=8',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=8',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=8',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=8'
            ]
            //'desc' => $this->l('In this section you will have the possibility to define and configure your favorite packages, in order to assign the relative reference package to each product')
        ];
        $this->conf_tabs['tax_duty_service'] = [
            'id' => 'tax_duty_service',
            'label' => $this->l('Tax & Duties service'),
            'icon_class' => '',
            'content' => $this->displayTabTaxDutyService(),
            'show_this' => false,
			'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=10',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=10',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=10',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=10',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=10',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=10'
            ]
        ];
        if ($this->third_party_pickups_allowed) {
            $this->conf_tabs['pickup_management'] = [
                'id' => 'pickup_management',
                'label' => $this->l('Pickup management'),
                'icon_class' => '',
                'content' => $this->displayTabPickupManagement(),
                'show_this' => false,
                'guide' => [
                    'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=12',
                    'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=12',
                    'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=12',
                    'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=12',
                    'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=12',
                    'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=12'
                ],
                'desc' => ''
            ];
        }
        $this->conf_tabs['recharge_settings'] = [
            'id' => 'recharge_settings',
            'label' => $this->l('Recharge'),
            'icon_class' => '',
            'content' => $this->displayTab5(),
            'show_this' => false,
            'desc' => $this->l('In this screen, you can set whether to enter a mark-up on the shipping price and whether to apply it to each one or the parcel. You can also set when to "offer" customers "Free" shipping.'),
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=15',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=15',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=15',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=15',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=15',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=15'
            ]
        ];
        $this->conf_tabs['debug_settings'] = [
            'id' => 'debug_settings',
            'label' => $this->l('Debug'),
            'icon_class' => '',
            'content' => $this->displayTab6(),
            'show_this' => false,
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=17',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=17',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=17',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=17',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=17',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=17'
            ]
        ];
        $this->conf_tabs['checkup_settings'] = [
            'id' => 'checkup_settings',
            'label' => $this->l('Check-up'),
            'icon_class' => 'icon-caret-right',
            'content' => $this->displayTab7(),
            'show_this' => false,
            'desc' => $this->l('The section is useful for checking that the plugin is compatible with your version of Prestashop. If you want to check the plugin\'s status, you can click on "Start".'),
            'guide' => [
                'it' => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf#page=18',
                'en' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=18',
                'es' => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf#page=18',
                'de' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=18',
                'fr' => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf#page=18',
                'pl' => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf#page=18'
            ]
        ];

        $this->filterConfTabsForDirectChannel();

        // Employee Iso Code Lang
        $employee_iso_code = Language::getIsoById(Context::getContext()->employee->id_lang);

        $guide = $links['guide']['mbe'];
        $contact = $links['contact']['mbe'];
        $phone = $links['phone']['mbe'];
        $info = $links['info'];
        if($this->is_direct_channel_user || empty($links['customer_id'])) {
            $guide = $links['guide']['direct'];
            $contact = $links['contact']['direct'];
            $phone = $links['phone']['direct'];
            $info = $links['contact']['direct'];
        }

        if (!empty($errors = $this->getErrors())) {
            $result .= $this->displayError($errors);
        }

        // smarty vars
        $this->context->smarty->assign([
            'show_side_menu' => !(int)Configuration::get('MBESHIPPING_INITIAL_CONF'),
            'link_info' => $info,
            'link_guide' => $guide,
            'link_contact' => $contact,
            'link_phone' => $phone,
            'link_support' => $links['support'],
            'link_portal' => $links['portal'],
            'conf_tabs' => $this->conf_tabs,
            'module_version' => $this->version,
            'module_dir' => _PS_BASE_URL_ . __PS_BASE_URI__ . '/modules/' . $this->name,
            'result' => $result,
            'employee_iso_code' => $employee_iso_code,
            'is_direct_channel_user' => $this->is_direct_channel_user,
            'customer_id' => $links['customer_id']
        ]);

        // js vars
        Media::addJsDef([
            'text_signing_in' => $this->transJsString('Signing in...'),
            'url_field_not_valid' => $this->transJsString('The field must be a valid URL'),
            'is_direct_channel_user' => $this->is_direct_channel_user
        ]);

        $conf_header = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration_header.tpl');
        $conf_tabs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration.tpl');

        return $conf_header . $conf_tabs;
    }

    public function displayInitialLogin()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTab1';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab1(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getInitialLoginForm(),
        ]);
    }

    public function getInitialLoginForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('MBE Services'),
                ],
                'input' => [
                    [
                        'type' => 'custom_text',
                        'text' => $this->l('Log in now to the MBE eShip module and give your e-commerce a boost: with MBE\'s solutions for digitising shipping and logistics you have a reliable partner who can support you with your online business. Also available for large marketplaces (Amazon and e-Bay).'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Country'),
                        'name' => 'mbecountry',
                        'options' => [
                            'query' => [
                                ['id_option' => 'IT', 'name' => $this->l('Italy')],
                                ['id_option' => 'ES', 'name' => $this->l('Spain')],
                                ['id_option' => 'DE', 'name' => $this->l('Germany')],
                                ['id_option' => 'FR', 'name' => $this->l('France')],
                                ['id_option' => 'AT', 'name' => $this->l('Austria')],
                                ['id_option' => 'PL', 'name' => $this->l('Poland')],
                                ['id_option' => 'HR', 'name' => $this->l('Croatia')],
                                ['id_option' => 'UK', 'name' => $this->l('United Kingdom')],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                        'class' => 'mw-100',
                        'col' => 4,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('User'),
                        'placeholder' => $this->l('User'),
                        'name' => 'mbe_user',
                        'col' => 4,
                    ],
                    [
                        'type' => 'password',
                        'label' => $this->l('Password'),
                        'placeholder' => $this->l('Password'),
                        'name' => 'mbe_pass',
                        'col' => 4,
                    ],
                    [
                        'type' => 'custom_button',
                        'text' => $this->l('Login'),
                        'name' => 'mbeLogin',
                        'class' => 'btn btn-primary',
                        'submit' => 1
                    ],
                    [
                        'type' => 'custom_button',
                        'text' => $this->l('Back to the welcome page'),
                        'onclick' => 'backToWelcomePage()',
                        'class' => 'btn btn-link'
                    ]
                ]
            ],
        ];

        return $form;
    }

    public function displayTab1()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTab1';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab1(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getForm1Tab1(),
            $this->getForm2Tab1(),
        ]);
    }

    public function getValuesTab1()
    {
        $inputs = $this->getInitializedInputValues(
            $this->getForm1Tab1(),
            $this->getForm2Tab1()
        );

        $mbe_credentials = json_decode(Configuration::get('MBESHIPPING_CREDENTIALS'));

        if (!AuthAPI::isAuthenticated()) {
            $inputs['mbe_user'] = isset($mbe_credentials->mbe_user) ? $mbe_credentials->mbe_user : '';
            $inputs['mbe_pass'] = isset($mbe_credentials->mbe_pass) ? $mbe_credentials->mbe_pass : '';
            $inputs['mbe_active'] = Configuration::get('mbe_active');
        } else {
            $inputs['mbecountry'] = Configuration::get('mbecountry');
            $inputs['url'] = Configuration::get('url');
            $inputs['username'] = Configuration::get('username');
            $inputs['password'] = Configuration::get('password');
            $inputs['mbe_active'] = Configuration::get('mbe_active');
        }

        return $inputs;
    }

    public function getForm1Tab1()
    {
        $is_advanced = (int)Configuration::get('MBESHIPPING_ADVANCED_AUTH_CONF');

        if (!AuthAPI::isAuthenticated() && !$is_advanced) {
            $form = [
                'form' => [
                    'legend' => [
                        'title' => $this->l('MBE Services'),
                    ],
                    'input' => [
                        [
                            'type' => 'access_form',
                            'name' => 'mbe_access_form',
                            'text1' => $this->l('The MBE e-LINK module, free, easy to install and configure, connects directly to your e-commerce, allowing you to offer different types of shipping and service levels to your customers, all characterized by the quality of Mail Boxes Etc.'),
                            'text2' => $this->l('Enter your credentials to be able to access the plug-in configuration.'),
                            'select' => [
                                'label' => $this->l('Country'),
                                'name' => 'mbecountry',
                                'options' => [
                                    'query' => [
                                        ['id_option' => 'IT', 'name' => $this->l('Italy')],
                                        ['id_option' => 'ES', 'name' => $this->l('Spain')],
                                        ['id_option' => 'DE', 'name' => $this->l('Germany')],
                                        ['id_option' => 'FR', 'name' => $this->l('France')],
                                        ['id_option' => 'AT', 'name' => $this->l('Austria')],
                                        ['id_option' => 'PL', 'name' => $this->l('Poland')],
                                        ['id_option' => 'HR', 'name' => $this->l('Croatia')],
                                        ['id_option' => 'UK', 'name' => $this->l('United Kingdom')],
                                    ],
                                    'id' => 'id_option',
                                    'name' => 'name',
                                ],
                            ]
                        ],
                    ]
                ],
            ];
        } else {
            if (!$is_advanced) {
                $form = [
                    'form' => [
                        'legend' => [
                            'title' => $this->l('MBE Services'),
                        ],
                        'input' => [
                            [
                                'type' => 'select',
                                'label' => $this->l('Country'),
                                'id' => 'mbecountry',
                                'name' => 'mbecountry',
                                'options' => [
                                    'query' => [
                                        ['id_option' => 'IT', 'name' => $this->l('Italy')],
                                        ['id_option' => 'ES', 'name' => $this->l('Spain')],
                                        ['id_option' => 'DE', 'name' => $this->l('Germany')],
                                        ['id_option' => 'FR', 'name' => $this->l('France')],
                                        ['id_option' => 'AT', 'name' => $this->l('Austria')],
                                        ['id_option' => 'PL', 'name' => $this->l('Poland')],
                                        ['id_option' => 'HR', 'name' => $this->l('Croatia')],
                                        ['id_option' => 'UK', 'name' => $this->l('United Kingdom')],
                                    ],
                                    'id' => 'id_option',
                                    'name' => 'name',
                                ],
                                'disabled' => true,
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('URL Web-Service MBE'),
                                'name' => 'url',
                                'disabled' => true,
                                'class' => 'fixed-width-xxl',
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('Login MBE eShip'),
                                'name' => 'username',
                                'disabled' => true,
                                'class' => 'fixed-width-xxl',
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('Passphrase MBE eShip'),
                                'name' => 'password',
                                'disabled' => true,
                                'class' => 'fixed-width-xxl',
                            ],
                            [
                                'type' => 'auth_reset',
                                'name' => 'mbe_auth_reset',
                                'btn_name' => 'mbeAuthReset',
                                'isAdvanced' => 0
                            ]
                        ],
                    ],
                ];
            } else {
                $form = [
                    'form' => [
                        'legend' => [
                            'title' => $this->l('MBE Services'),
                        ],
                        'input' => [
                            [
                                'type' => 'select',
                                'label' => $this->l('Country'),
                                'id' => 'mbecountry',
                                'name' => 'mbecountry',
                                'options' => [
                                    'query' => [
                                        ['id_option' => 'IT', 'name' => $this->l('Italy')],
                                        ['id_option' => 'ES', 'name' => $this->l('Spain')],
                                        ['id_option' => 'DE', 'name' => $this->l('Germany')],
                                        ['id_option' => 'FR', 'name' => $this->l('France')],
                                        ['id_option' => 'AT', 'name' => $this->l('Austria')],
                                        ['id_option' => 'PL', 'name' => $this->l('Poland')],
                                        ['id_option' => 'HR', 'name' => $this->l('Croatia')],
                                        ['id_option' => 'UK', 'name' => $this->l('United Kingdom')],
                                    ],
                                    'id' => 'id_option',
                                    'name' => 'name',
                                ],
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('URL Web-Service MBE'),
                                'name' => 'url',
                                'class' => 'fixed-width-xxl',
                                'desc' => $this->l('The address to be used is standard for the entire MBE network')
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('Login MBE eShip'),
                                'name' => 'username',
                                'class' => 'fixed-width-xxl',
                                'desc' => $this->l('Enter the username/login used to access MBE Online, provided by the reference MBE Center')
                            ],
                            [
                                'type' => 'text',
                                'label' => $this->l('Passphrase MBE eShip'),
                                'name' => 'password',
                                'class' => 'fixed-width-xxl',
                                'desc' => $this->l('Enter the passphrase provided by the reference MBE Center or generated on your MBE Online platform')
                            ],
                            [
                                'type' => 'auth_reset',
                                'name' => 'mbe_auth_reset',
                                'btn_name' => 'mbeAuthReset',
                                'isAdvanced' => 1
                            ]
                        ],
                        'submit' => [
                            'name' => 'submitAdvAuth',
                            'title' => $this->l('Save')
                        ]
                    ],
                ];
            }
        }

        return $form;
    }

    public function getForm2Tab1()
    {
        // Different Legend Title For Two Type of User
        $legend_title = $this->l('Configuration preferences');
        if($this->is_direct_channel_user) {
            $legend_title = $this->l('MBE e-Ship activation');
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $legend_title,
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'mbe_active',
                        'desc' => $this->l('Setting to "Enabled" will enable MBE shipping options for the buyers of your eCommerce.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                            ],
                        ],
                    ]
                ],
                'submit' => [
                    'name' => 'submitTab1',
                    'icon' => 'icon-save',
                    'title' => $this->l('Save'),
                ]
            ]
        ];
    }

    public function displayTab2()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab2(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $form_conf_tabs = [
            $this->getConf1Tab2(),
            $this->getConf2Tab2(),
            $this->getConf3Tab2()
        ];

        // Hidden for DIRECT_CHANNEL_USER
        if (!$this->is_direct_channel_user) {
            array_unshift($form_conf_tabs, $this->selectConfTab2());
        }

        return $helper->generateForm($form_conf_tabs);
    }

    public function getValuesTab2()
    {
        $inputs = $this->getInitializedInputValues(
            $this->selectConfTab2(),
            $this->getConf1Tab2(),
            $this->getConf2Tab2(),
            $this->getConf3Tab2()
        );

        $inputs['mbe_allowed_shipment_services_1[]'] = explode('-', Configuration::get('allowed_shipment_services'));
        $inputs['mbe_allowed_shipment_services_2[]'] = explode('-', Configuration::get('allowed_shipment_services'));
        $inputs['mbe_allowed_shipment_services_3[]'] = explode('-', Configuration::get('allowed_shipment_services'));
        $inputs['shipments_csv'] = Configuration::get('shipments_csv');
        $inputs['shipments_csv_mode'] = Configuration::get('shipments_csv_mode');
        $inputs['mbe_shipments_csv_insurance_min'] = Configuration::get('mbe_shipments_csv_insurance_min');
        $inputs['mbe_shipments_csv_insurance_per'] = Configuration::get('mbe_shipments_csv_insurance_per');
        $inputs['mbe_shipments_ins_mode'] = Configuration::get('mbe_shipments_ins_mode');

        foreach ($this->getCarrierIds() as $id_carrier) {
            $inputs['mbe_custom_mapping_' . $id_carrier] = Configuration::get('mbe_custom_mapping_' . $id_carrier);
        }

        $services = (new PrestaShop\Module\Mbeshipping\Ws)->getAllowedShipmentServices();
        //Push Merged Delivery Point Service
        $isServiceNumeric = false;
        if(!empty($services)) {
            if(is_numeric($services[0]['value'])) {
                $isServiceNumeric = true;
            }
            array_push($services,
                [
                    'label' => $this->l('Delivery Point'),
                    'value' => $isServiceNumeric ? '1112' : 'NMDP-GPP',
                ]
            );
        }

        foreach ($services as $service) {
            $id_service = Tools::strtolower(str_replace('+', 'p', $service['value']));
            $inputs['mbe_custom_label_' . $id_service] = Configuration::get('mbe_custom_label_' . $id_service);
            $inputs['mbe_tax_rule_' . $id_service] = Configuration::get('mbe_tax_rule_' . $id_service);
        }

        return $inputs;
    }

    public function selectConfTab2()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Couriers and services'),
                ],
                'input' => [
                    [
                        'type' => 'custom_text',
                        'name' => 'mbe_couriers_services_description',
                        'text' => $this->l('For the plugin to work correctly, at least one option must be selected, and the services available are those set by the MBE Center on the MOL user page on HUB. Subsequently, it is possible to define a custom name for each MBE service selected in the field seen above. This set of fields is automatically generated dynamically, based on the values selected in the "Enabled MBE Services" list') .
                            (Configuration::get('mbe_enable_custom_mapping') ? '<br><br> <b>' . $this->l('N.B: If you choose this mode, PUDO-related MBE services cannot be used') . '</b>' : ''),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Configuration mode'),
                        'id' => 'mbe_couriers_services_mode',
                        'name' => 'mbe_couriers_services_mode',
                        'options' => [
                            'query' => [
                                ['id_option' => 'default', 'name' => $this->l('--- Select a mode ---')],
                                ['id_option' => 1, 'name' => $this->l('Custom pricing (CSV file)')],
                                ['id_option' => 2, 'name' => $this->l('Mapping of Couriers and Shipping services')],
                                ['id_option' => 3, 'name' => $this->l('MBE services recovery')],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Select the desired configuration mode')
                    ],
                    [
                        'type' => 'change_conf_mode',
                        'name' => 'mbe_change_conf_mode',
                        'btn_name' => 'mbeChangeConfigMode',
                        'text' => $this->l('You\'re about to change configuration mode. Please confirm to proceed.')
                    ]
                ]
            ],
        ];
    }

    public function getConf1Tab2()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration preferences'),
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Select one or more services you intend to offer for shipping'),
                        'id' => 'mbe_allowed_shipment_services_1',
                        'name' => 'mbe_allowed_shipment_services_1[]',
                        'options' => $this->getServiceOptions(),
                        'multiple' => true,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Select all MBE services that you intend to offer to the buyers of your eCommerce for shipping. For the plugin to work correctly, at least one option must be selected')
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Custom prices loaded from CSV files'),
                        'name' => 'shipments_csv',
                        'files' => Configuration::get("mbe_shipments_csv") ? [
                            'file' => Configuration::get("mbe_shipments_csv"),
                            'download_url' => $this->module_url . 'uploads/' . Configuration::get("mbe_shipments_csv"),
                        ] : [],
                        'desc' => $this->l('You can upload your packages informations directly here via a CSV file.'),
                        'desc_link' => [
                            'url' => $this->module_url . 'uploads/mbe_csv_template.csv',
                            'text' => $this->l('Download template file (click here)')
                        ],
                        'col' => 8
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Custom Pricing (CSV) - File Usage Mode'),
                        'name' => 'shipments_csv_mode',
                        'options' => [
                            'query' => [
                                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CSV_MODE_TOTAL, 'name' => $this->l('Total')],
                                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CSV_MODE_PARTIAL, 'name' => $this->l('Partial')],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Indicate how to use the file by choosing one of the options above'),
                        'col' => 8
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['mbe_shipments_csv_insurance_min'],
                        'name' => 'mbe_shipments_csv_insurance_min',
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Specify the minimum value of the surcharge for this service')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['mbe_shipments_csv_insurance_per'],
                        'name' => 'mbe_shipments_csv_insurance_per',
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Specify the percentage value that you intend to use for the calculation of the surcharge for this service')
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Insurance - Declared value'),
                        'name' => 'mbe_shipments_ins_mode',
                        'class' => 'fixed-width-xxl',
                        'options' => [
                            'query' => [
                                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_INSURANCE_WITH_TAXES,
                                    'name' => $this->l('With Taxes')],
                                ['id_option' => PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_INSURANCE_WITHOUT_TAXES,
                                    'name' => $this->l('Without Taxes')],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Choose whether to indicate the amount of the order including VAT or excluding VAT as the value of the goods')
                    ],
                ],
                'submit' => [
                    'name' => 'submitConf1Tab2',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getConf2Tab2()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration preferences'),
                ],
                'input' => array_merge(
                    [
                        [
                            'type' => 'select',
                            'label' => $this->l('Select one or more services you intend to offer for shipping'),
                            'id' => 'mbe_allowed_shipment_services_2',
                            'name' => 'mbe_allowed_shipment_services_2[]',
                            'options' => $this->getServiceOptions(),
                            'multiple' => true,
                            'desc' => $this->l('Select all MBE services that you intend to offer to the buyers of your eCommerce for shipping. For the plugin to work correctly, at least one option must be selected')
                        ]
                    ],
                    $this->renderCouriersServicesMapping()
                ),
                'submit' => [
                    'name' => 'submitConf2Tab2',
                    'title' => $this->l('Save')
                ]
            ],
        ];
    }

    public function getConf3Tab2()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration preferences'),
                ],
                'input' => array_merge(
                    [
                        [
                            'type' => 'custom_text',
                            'name' => 'mbe_shipments_description',
                            'text' => $this->l('Introductory description of MBE shipments:'),
                            'list' => [
                                $this->l('MBE Standard: is a service that offers you the possibility to ship in Italy and throughout Europe and is the ideal solution for individuals and companies who want to guarantee their customers reliability and punctuality.'),
                                $this->l('MBE Express: is a service that guarantees the delivery of your shipments, in Italy, on average in two working days (within 48 hours of collection)'),
                                $this->l('MBE Delivery Point: it is a service that allows you to send objects, packages, documents and much more, in a convenient and fast way from an MBE Center of your choice, to one of the many authorized and authorized collection points, both in Italy than abroad.')
                            ],
                        ],
                        [
                            'type' => 'select',
                            'label' => $this->is_direct_channel_user ? $this->l('Your activated MBE services') :  $this->l('Select one or more services you intend to offer for shipping'),
                            'id' => 'mbe_allowed_shipment_services_3',
                            'name' => 'mbe_allowed_shipment_services_3[]',
                            'options' => $this->getServiceOptions(),
                            'multiple' => true,
                            'desc' => $this->is_direct_channel_user ? $this->l('Select one or more MBE services you wish to offer for the shipping of your e-Commerce. Please note: For the plugin to be configured correctly, there must be at least one "activated" service.') : $this->l('Select all MBE services that you intend to offer to the buyers of your eCommerce for shipping. For the plugin to work correctly, at least one option must be selected')
                        ]
                    ],
                    $this->renderServicesCustomDescription()
                ),
                'submit' => [
                    'name' => 'submitConf3Tab2',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function displayTab3()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab3(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $forms = [
            $this->getForm1Tab3()
        ];

        if ((int)Configuration::get('mbe_shipping_use_packages_csv')) {
            // Hidden for DIRECT_CHANNEL_USER
            if (!$this->is_direct_channel_user) {
                $forms[] = $this->getForm2Tab3();
            }
            $forms[] = $this->getAdvancedConfTab3();
        }

        return $helper->generateForm($forms);
    }

    public function getValuesTab3()
    {
        $inputs = $this->getInitializedInputValues(
            $this->getForm1Tab3(),
            $this->getForm2Tab3(),
            $this->getAdvancedConfTab3()
        );

        foreach ($this->getForm1Tab3()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        foreach ($this->getForm2Tab3()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        return $inputs;
    }

    public function getForm1Tab3()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Standard configuration'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['default_length'] . ' (cm)',
                        'placeholder' => $this->l('ex. 20'),
                        'name' => 'default_length',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Refer to the average length of shipments normally made. It can contain decimal numbers')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['default_width'] . ' (cm)',
                        'placeholder' => $this->l('ex. 20'),
                        'name' => 'default_width',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Refer to the average width of shipments normally made. It can contain decimal numbers')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['default_height'] . ' (cm)',
                        'placeholder' => $this->l('ex. 20'),
                        'name' => 'default_height',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Refer to the average height of shipments normally made. It can contain decimal numbers')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['max_package_weight'] . ' (kg)',
                        'placeholder' => $this->l('ex. 1.5'),
                        'name' => 'max_package_weight',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Indicate the maximum weight in kg of each package to be shipped and check any limitations with your MBE Center')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->numeric_field_labels['max_shipment_weight'] . ' (kg)',
                        'placeholder' => $this->l('ex. 1.5'),
                        'name' => 'max_shipment_weight',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Indicate the maximum weight of the shipment in kg, intended as the sum of the weights of all packages shipped. In case of Envelope shipping, a default value will be applied 0.5 kg (not editable).')
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use custom packages configuration'),
                        'id' => 'mbe_shipping_use_packages_csv',
                        'name' => 'mbe_shipping_use_packages_csv',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                            ],
                        ],
                         'desc' => $this->is_direct_channel_user ? $this->l('Use the "Customised Parcel Configuration" click on the button below and press "Save".') : "",
                    ],
                ],
                'submit' => [
                    'name' => 'submitForm1Tab3',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getForm2Tab3()
    {
        $csvPackageFiles = null;
        if (Configuration::get("mbe_shipping_packages_csv")) {
            $csvPackageFiles[] = [
                'file' => Configuration::get("mbe_shipping_packages_csv"),
                'download_url' => $this->module_url . 'uploads/' . Configuration::get("mbe_shipping_packages_csv"),
            ];
        }

        $csvPackageProductFiles = null;
        if (Configuration::get("mbe_shipping_packages_product_csv")) {
            $csvPackageProductFiles[] = [
                'file' => Configuration::get("mbe_shipping_packages_product_csv"),
                'download_url' => $this->module_url . 'uploads/' .
                    Configuration::get("mbe_shipping_packages_product_csv"),
            ];
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Standard packages CSV'),
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $this->l('Packages CSV'),
                        'name' => 'mbe_shipping_packages_csv',
                        'files' => $csvPackageFiles,
                        'desc' => $this->l('You can upload your package information directly here via a CSV file.'),
                        'desc_link' => [
                            'url' => $this->module_url . 'uploads/mbe_package_csv_template.csv',
                            'text' => $this->l('Download template file (click here)')
                        ]
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Product packages CSV'),
                        'name' => 'mbe_shipping_packages_product_csv',
                        'files' => $csvPackageProductFiles,
                        'desc' => $this->l('You can upload your package information directly here via a CSV file.'),
                        'desc_link' => [
                            'url' => $this->module_url . 'uploads/mbe_package_product_csv_template.csv',
                            'text' => $this->l('Download template file (click here)')
                        ]
                    ],
                ],
                'submit' => [
                    'name' => 'submitForm2Tab3',
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getAdvancedConfTab3()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Custom packages configuration'),
                ],
                'input' => [
                    [
                        'type' => 'custom_text',
                        'name' => 'mbe_advanced_configuration_description',
                        'text' => $this->l('In this section it is possible to configure the CSV files for your standard packages and for your products and also download the template for the configuration.'),
                    ],
                    [
                        'type' => 'advanced_conf',
                        'name' => 'mbe_advanced_conf',
                        'admin_package' => $this->context->link->getAdminLink('AdminMbePackageHelper'),
                        'admin_product_package' => $this->context->link->getAdminLink('AdminMbePackageProductHelper'),
                    ],
                ]
            ]
        ];
    }

    public function displayTab4()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTab4';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab4(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormTab4(),
        ]);
    }

    public function getValuesTab4()
    {
        $inputs = $this->getInitializedInputValues(
            $this->getFormTab4()
        );

        foreach ($this->getFormTab4()['form']['input'] as $input) {
            switch ($input['name']) {
                case 'specificcountry[]':
                    $inputs[$input['name']] = explode('-', Configuration::get('specificcountry'));
                    break;
                case 'shipment_configuration_mode':
                    $use_packages_csv = (int)Configuration::get('mbe_shipping_use_packages_csv');
                    $use_packages_csv ?
                        $inputs[$input['name']] = '2' :
                        $inputs[$input['name']] = Configuration::get($input['name']);
                    break;
                /*case 'MBESHIPPING_PICKUP_REQUEST_MODE':
                    // TODO: handle manual pickup request mode
                    $inputs[$input['name']] = 'automatically';
                    break;*/
                default:
                    $inputs[$input['name']] = Configuration::get($input['name']);
            }
        }

        return $inputs;
    }

    public function displayTabTaxDutyService()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTabTaxDutyService';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTabTaxDutyService(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormTabTaxDutyService(),
        ]);
    }

    public function getFormTabTaxDutyService()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $is_enabled_tax_duties = (bool)$ws->getCustomerPermission('enabledTaxAndDuties');
        $is_pickup_incompatible = (bool)Configuration::get('MBESHIPPING_PICKUP_MODE') === true && Configuration::get('MBESHIPPING_PICKUP_REQUEST_MODE') !== 'automatically';
        $is_conf_mode_incompatible = (int)Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE') === 2;

        if(!$is_enabled_tax_duties) {
            $form_inputs[] =[
                'type' => 'custom_text',
                'name' => 'mbe_ted_alert',
                'class' => 'alert alert-warning',
                'text' => $this->l('At the moment this feature is not active, you will receive detailed information as soon as it is released.'),
            ];
        }

        $form_inputs[] = [
            'type' => 'switch',
            'label' => $this->l('Tax & Duties service'),
            'name' => 'MBESHIPPING_TAX_DUTY_SERVICE',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                ],
            ],
            'disabled' => (!$is_enabled_tax_duties || $is_pickup_incompatible || $is_conf_mode_incompatible),
            'desc' => $is_pickup_incompatible ?
                $this->l('If you want to enable Tax and Duties service you need to set the "automatic" pickup management mode') :
                $this->l('By enabling this option you can compute T&D price in case of Worldwide shipments and display the total amount on checkout phase'),
        ];

        // + MBE CR DDP/DAP
        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->l('Default Intercom'),
            'name' => 'MBESHIPPING_TAX_DUTY_INTERCOM',
            'options' => [
                'query' => [
                    [
                        'id' => 'DAP',
                        'name' => $this->l('DAP')
                    ],
                    [
                        'id' => 'DDP',
                        'name' => $this->l('DDP')
                    ],
                ],
                'id' => 'id',
                'name' => 'name'
            ],
            'value' => 'DAP',
            'disabled' => (!$is_enabled_tax_duties || $is_pickup_incompatible || $is_conf_mode_incompatible),
            'desc' => $this->l('If you choose to work of DAP by default, a forecast of how much customs will charge the consignee to clear the goods will be shown at checkout. If you choose to work in DDP by default, the calculation of the customs clearance cost will be added at checkout and guaranteed via MBE, to have a fast and smooth shipping flow. In any case, these features will only be used if the shipment is international and involves customs in its path to the destination.')
        ];
        // - MBE CR DDP/DAP

        foreach ($form_inputs as $input) {
            if (isset($input['disabled']) && $input['disabled']) {
                $form_inputs[] = $this->createHiddenFormInput($input['name']);
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Tax & Duties service'),
                ],
                'input' => $form_inputs,
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getValuesTabTaxDutyService() {
        $inputs = $this->getInitializedInputValues(
            $this->getFormTabTaxDutyService()
        );

        foreach ($this->getFormTabTaxDutyService()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        return $inputs;
    }

    public function displayTabPickupManagement()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTabPickupManagement';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTabPickupManagement(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormTabPickupManagement(),
        ]);
    }

    public function getFormTabPickupManagement()
    {
        $pickup_request_mode = Configuration::get('MBESHIPPING_PICKUP_REQUEST_MODE');

        $is_enabled_tax_duty_service = Configuration::get('MBESHIPPING_TAX_DUTY_SERVICE');

        $form_inputs = [];

        $form_inputs[] = [
            'type' => 'switch',
            'label' => $this->l('Pickup'),
            'name' => 'MBESHIPPING_PICKUP_MODE',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                ],
            ],
            'disabled' => $this->is_direct_channel_user,
            'desc' => $this->l('By enabling this option you will be able to handle courier pickup requests directly through the plugin. Before enabling this option, consult your MBE center of choice'),
        ];

        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->l('Pickup Request - Mode'),
            'name' => 'MBESHIPPING_PICKUP_REQUEST_MODE',
            'options' => [
                'query' => [
                    [
                        'id_option' => 'automatically',
                        'name' => $this->l('Automatically')
                    ],
                    [
                        'id_option' => 'manually',
                        'name' => $this->l('Manual')
                    ]
                ],
                'id' => 'id_option',
                'name' => 'name',
            ],
            'disabled' => $this->setPickupModeVisibility() || $is_enabled_tax_duty_service,
            'desc' => $is_enabled_tax_duty_service ?
                $this->l('By choosing the Manual mode, it will be possible to route a pickup request manually, associating a specific pickup address and pickup data with one or more shipments. The Manual mode is not configurable when T&D service is active. By choosing the Automatic mode, pickup requests will be routed to a default address and with default pickup data') :
                $this->l('By choosing the Manual mode, it will be possible to route a pickup request manually, associating a specific pickup address and pickup data with one or more shipments. By choosing the Automatic mode, pickup requests will be routed to a default address and with default pickup data')
        ];

        if($pickup_request_mode === 'automatically') {
            $form_inputs[] = [
                'type' => 'select',
                'label' => $this->l('Pickup Cutoff - Period'),
                'name' => 'MBESHIPPING_PICKUP_CUTOFF_PERIOD',
                'options' => [
                    'query' => [
                        [
                            'id_option' => 'MORNING',
                            'name' => $this->l('Same day pickup')
                        ],
                        [
                            'id_option' => 'AFTERNOON',
                            'name' => $this->l('Following day pickup')
                        ],
                    ],
                    'id' => 'id_option',
                    'name' => 'name',
                ],
                'required' => true,
                'desc' => $this->l('By choosing \'Same day pickup\', pickup requests will be automatically routed to couriers for same day pickup, for all shipments created by 8 a.m. By choosing \'Following day pickup\', pickup requests will be automatically routed to couriers for next (business) day pickup, for all shipments created by 10 p.m.'),
            ];
        }

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->l('Pickup Time - Preferred from'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            'required' => true,
            //'disabled' => true,
            'desc' => $this->l('Minimum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->l('Pickup Time - Preferred to'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO',
            'placeholder' => 'HH:MM',
            'class' => 'test fixed-width-xxl',
            'required' => true,
            //'disabled' => true,
            'desc' => $this->l('Maximum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->l('Pickup Time - Alternative from'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            //'disabled' => true,
            'desc' => $this->l('Alternative minimum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->l('Pickup Time - Alternative to'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            //'disabled' => true,
            'desc' => $this->l('Alternative maximum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'text',
            'label' => $this->l('Pickup notes'),
            'name' => 'MBESHIPPING_PICKUP_NOTES',
            'class' => 'fixed-width-xxl',
            'desc' => $this->l('Notes to be included within the pickup request and that will be forwarded to the final carrier'),
        ];

        $form_inputs[] = [
            'type' => 'pickup_address',
            'name' => 'mbe_pickup_address',
            'admin_pickup_address' => $this->context->link->getAdminLink('AdminMbePickupAddressHelper'),
        ];

        foreach ($form_inputs as $input) {
            if (isset($input['disabled']) && $input['disabled']) {
                $form_inputs[] = $this->createHiddenFormInput($input['name']);
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Pickup management'),
                ],
                'input' => array_merge(
                    [
                        [
                            'type' => 'custom_text',
                            'name' => 'mbe_pickup_alert',
                            'class' => 'alert alert-warning',
                            'text' => $this->l('Pickup for PUDO shipments will be booked automatically after shipments closure for the next working day.'),
                        ]
                    ],
                    $form_inputs
                ),
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function getValuesTabPickupManagement() {
        $inputs = $this->getInitializedInputValues(
            $this->getFormTabPickupManagement()
        );

        $pickupDataInputs = [
            'Cutoff' => 'MBESHIPPING_PICKUP_CUTOFF_PERIOD',
            'PreferredFrom' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM',
            'PreferredTo' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO',
            'AlternativeFrom' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM',
            'AlternativeTo' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO',
            'Notes' => 'MBESHIPPING_PICKUP_NOTES',
        ];

        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $defaultPickupData = $ws->getPickupDefaultData();

        if (!$defaultPickupData) {
            $this->_errors[] = $this->l('Error while retrieving pickup default data');
        } else {
            foreach ($defaultPickupData as $key => $value) {
                if (!isset($pickupDataInputs[$key])) {
                    continue;
                }

                Configuration::updateValue($pickupDataInputs[$key], $value);
            }
        }

        foreach ($this->getFormTabPickupManagement()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        return $inputs;
    }

    private function disablePickupManagement()
    {
        Configuration::updateValue('MBESHIPPING_PICKUP_MODE', false);
        Configuration::updateValue('MBESHIPPING_PICKUP_REQUEST_MODE', 'automatically');
    }

    public function displayTab5()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab5(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );


        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $available_services = $ws->getAllowedShipmentServices();

        if (!empty($available_services)) {
            return $helper->generateForm([
                $this->getForm1Tab5(),
                $this->getForm2Tab5(),
            ]);
        }

        return $helper->generateForm([
            $this->getForm1Tab5()
        ]);
    }

    public function getValuesTab5()
    {
        $inputs = $this->getInitializedInputValues(
            $this->getForm1Tab5(),
            $this->getForm2Tab5()
        );

        foreach ($this->getForm1Tab5()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        foreach ($this->getForm2Tab5()['form']['input'] as $input) {
            $inputs[$input['name']] = Configuration::get($input['name']);
        }

        return $inputs;
    }

    public function displayTab6()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTab6';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValuesTab6(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormTab6()
        ]);
    }

    public function getValuesTab6()
    {
        $inputs = $this->getInitializedInputValues(
            $this->getFormTab6()
        );

        $inputs['mbe_debug'] = Configuration::get('mbe_debug');

        return $inputs;
    }

    public function getFormTab6()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Debug'),
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Debug'),
                        'name' => 'mbe_debug',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                            ],
                        ],
                        'desc' => $this->l('This feature is used to collect logs of problems generated during application crashes.'),
                    ],
                    [
                        'type' => 'link_button',
                        'label' => $this->l('Download'),
                        'name' => 'download',
                        'link' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&downloadlogs=1',
                        'text' => $this->l('Download log files'),
                        'icon' => 'icon-download',
                        'desc' => $this->l('To download the crash log files, click here'),
                        'class' => 'btn btn-primary'
                    ],
                    [
                        'type' => 'link_button',
                        'label' => $this->l('Delete'),
                        'name' => 'delete',
                        'link' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&deletelogs=1',
                        'text' => $this->l('Delete log files'),
                        'icon' => 'icon-remove-circle',
                        'desc' => $this->l('To delete the crash log files, click here'),
                        'class' => 'btn btn-primary'
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
    }

    public function displayTab7()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => '',
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormTab7()
        ]);
    }

    public function getFormTab7()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Check-up'),
                ],
                'input' => [
                    [
                        'type' => 'checklist',
                        'name' => 'mbe_checklist'
                    ]
                ]
            ],
        ];
    }

    public function getLinks()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        $customer_id = 0;
        if(isset($ws->getCustomer()->Login)) {
            $customer_id = $ws->getCustomer()->Login;
        }

        $base_url_banners = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/banners/';
        $base_url_icons = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/icons/';
        $links = [
            'it' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_it.jpg',
                'banner_packing' => $base_url_banners . 'packing_it.jpg',
                'info' => 'https://www.mbe.it/it/mbe-plugin-ecommerce',
                'guide' => [
                  'mbe' => 'https://www.mbe.it/downloads/3233/425/425_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20MANUALE%20UTENTE%20-%20%20Guida%20Installazione%20-%20IT.pdf',
                  'direct'  => 'https://www.mbe.it/downloads/3233/484/484_eShip_For_PrestasShop_Installation_Guide_IT_v2.2%20%5BCANALE%20%20DIRETTO%5D.pdf'
                ],
                'contact' => [
                  'mbe' => 'https://www.mbe.it/it/mbe-plugin-ecommerce',
                  'direct' => 'mailto:eshipforprestashop.support@mbe.it?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:00390249525880'
                ],
                'support' => 'https://www.mbe.it/it/mbe-plugin-ecommerce',
                'portal' => 'https://www.mbe.it/it/mbe-plugin-ecommerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp'
            ],
            'en' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_en.jpg',
                'banner_packing' => $base_url_banners . 'packing_en.jpg',
                'info' => 'https://www.mbe.it/en/mbe-plugin-ecommerce',
                'guide' => [
                    'mbe' => 'https://www.mbe.it/downloads/3233/428/428_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20USER%20MANUAL%20-%20%20Installation%20Guide%20-%20Eng.pdf',
                    'direct'  => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf'
                ],
                'contact' => [
                    'mbe' => 'https://www.mbe.it/en/mbe-plugin-ecommerce',
                    'direct' => 'mailto:eshipforprestashop.support@mbe.it?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:00390249525880'
                ],
                'support' => 'https://www.mbe.it/en/mbe-plugin-ecommerce',
                'portal' => 'https://www.mbe.it/en/mbe-plugin-ecommerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp'
            ],
            'fr' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_fr.jpg',
                'banner_packing' => $base_url_banners . 'packing_fr.jpg',
                'info' => 'https://www.mbefrance.fr/fr/solutions-e-commerce',
                'guide' => [
                    'mbe' => 'https://www.mbe.it/downloads/3233/486/486_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20-%20%20Installation%20Guide%20FR.pdf',
                    'direct'  => 'https://www.mbe.it/downloads/3233/488/488_ReadME_FR_v2.2.pdf'
                ],
                'contact' => [
                    'mbe' => 'https://www.mbefrance.fr/fr/solutions-e-commerce',
                    'direct' => 'mailto:eshipforprestashop.support@mbefrance.fr?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:0033170393328'
                ],
                'support' => 'https://www.mbefrance.fr/fr/solutions-e-commerce',
                'portal' => 'https://www.mbefrance.fr/fr/solutions-e-commerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp'
            ],
            'de' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_de.jpg',
                'banner_packing' => $base_url_banners . 'packing_de.jpg',
                'info' => 'https://www.mbe.de/de/mbe-e-link-automatisierte-versandl%C3%B6sung-f%C3%BCr-e-commerce',
                'guide' => [
                    'mbe' => 'https://www.mbe.it/downloads/3233/428/428_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20USER%20MANUAL%20-%20%20Installation%20Guide%20-%20Eng.pdf',
                    'direct'  => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf'
                ],
                'contact' => [
                    'mbe' => 'https://www.mbe.de/de/mbe-e-link-automatisierte-versandl%C3%B6sung-f%C3%BCr-e-commerce',
                    'direct' => 'mailto:eshipforprestashop.support@mbe.it?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:00390249525880'
                ],
                'support' => 'https://www.mbe.de/de/mbe-e-link-automatisierte-versandl%C3%B6sung-f%C3%BCr-e-commerce',
                'portal' => 'https://www.mbe.de/de/mbe-e-link-automatisierte-versandl%C3%B6sung-f%C3%BCr-e-commerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp'
            ],
            'es' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_es.jpg',
                'banner_packing' => $base_url_banners . 'packing_es.jpg',
                'info' => 'https://www.mbe.es/es/mbe-plugin-ecommerce',
                'guide' => [
                    'mbe' => 'https://www.mbe.it/downloads/3233/485/485_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20-%20%20Gu%C3%ADa%20Instalaci%C3%B3n_ESP.pdf',
                    'direct'  => 'https://www.mbe.it/downloads/3233/487/487_ReadME_ESP_v2.2.pdf'
                ],
                'contact' => [
                    'mbe' => 'https://www.mbe.es/es/mbe-plugin-ecommerce',
                    'direct' => 'mailto:eshipforprestashop.support@mbe.es?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:0034934921192'
                ],
                'support' => 'https://www.mbe.es/es/mbe-plugin-ecommerce',
                'portal' => 'https://www.mbe.es/es/mbe-plugin-ecommerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp',
            ],
            'pl' => [
                'banner_eship' => $base_url_icons . 'logo_eship_for_prestashop.png',
                'banner_elink' => $base_url_banners . 'elink_pl.jpg',
                'banner_packing' => $base_url_banners . 'packing_pl.jpg',
                'info' => 'https://www.mbe.pl/pl/mbe-elink-plugin-ecommerce',
                'guide' => [
                    'mbe' => 'https://www.mbe.it/downloads/3233/428/428_MBE%20eShip%20v.%202.2%20-%20PrestaShop%20USER%20MANUAL%20-%20%20Installation%20Guide%20-%20Eng.pdf',
                    'direct'  => 'https://www.mbe.it/downloads/3233/490/490_ReadME_EN_v2.2.pdf'
                ],
                'contact' => [
                    'mbe' => 'https://www.mbe.pl/pl/mbe-elink-plugin-ecommerce',
                    'direct' => 'mailto:eshipforprestashop.support@mbe.it?subject=' . "[$customer_id] " . $this->l('I need support.')
                ],
                'phone' => [
                    'mbe' => '#',
                    'direct' => 'tel:00390249525880'
                ],
                'support' => 'https://www.mbe.pl/pl/mbe-elink-plugin-ecommerce',
                'portal' => 'https://www.mbe.pl/pl/mbe-elink-plugin-ecommerce',
                'background' => $base_url_banners . 'background_banner.png',
                'advantage' => $base_url_banners . 'banner_advantage.jpg',
                'checklist' => $base_url_banners . 'checklist.png',
                'customer_id' => $customer_id,
                'logo_welcome_first' => $base_url_icons . '/webp/eship_logo.webp',
                'illustration_welcome_first' => $base_url_icons . '/webp/illustration_welcome_first.webp',
                'carriers_icon' => $base_url_icons . '/webp/carriers_icon.webp',
                'configuration_icon' => $base_url_icons . '/webp/configuration_icon.webp',
                'customer_service_icon' => $base_url_icons . '/webp/customer_service_icon.webp',
                'returns_icon' => $base_url_icons . '/webp/returns_icon.webp',
            ]
        ];

        if (array_key_exists($this->context->language->iso_code, $links)) {
            return $links[$this->context->language->iso_code];
        }

        return $links['en'];
    }

    public function checkConfiguration()
    {
        $warnings = [];
        $additional_warnings = [];

        $ws = new PrestaShop\Module\Mbeshipping\Ws();

        $auth_configurations = [
            'mbecountry' => Configuration::get('mbecountry'),
            'url' => Configuration::get('url'),
            'username' => Configuration::get('username'),
            'password' => Configuration::get('password'),
        ];

        foreach ($auth_configurations as $conf) {
            if (empty($conf)) {
                $warnings[] = ($this->l('General') . ' > ' . $this->l('MBE Services') . ': ' . $this->l('please log in or proceed with advanced configuration'));
                break;
            }
        }

        if (empty($conf_mode = (int)Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE'))) {
            $warnings[] = $this->l('Couriers and services') . ': ' . $this->l('please choose a configuration mode');
        } else {
            if ($conf_mode === 2 && (bool) $ws->getCustomerPermission('enabledTaxAndDuties')) {
                $additional_warnings[] = $this->l('Couriers and services') . ' > ' . $this->l('Configuration preferences') . ': ' . sprintf('"%s" cannot be enabled with "%s" configuration mode', $this->l('Tax & Duties service'), $this->l('Mapping of Couriers and Shipping services'));
            }

            if (empty(explode('-', Configuration::get('allowed_shipment_services')))) {
                $warnings[] = $this->l('Couriers and services') . ' > ' . $this->l('Configuration preferences') . ': ' . $this->l('please choose one or more services');
            }
        }

        if (!(int)Configuration::get('mbe_shipping_use_packages_csv')) {
            foreach ($this->getForm1Tab3()['form']['input'] as $input) {
                if (in_array($input['name'], $this->numeric_fields, true) && empty(Configuration::get($input['name']))) {
                    $warnings[] = $this->l('Packages') . ' > ' . $this->l('Standard configuration') . ': ' . $this->l('please submit your configuration');
                    break;
                }
            }
        }

        if ($this->third_party_pickups_allowed) {
            $pickup = Configuration::get('MBESHIPPING_PICKUP_MODE');
            if ($pickup) {
                $pickup_addresses = (int)MbePickupAddressHelper::getPickupAddresses(true, true);
                if (isset($pickup_addresses) && $pickup_addresses == 0) {
                    $warnings[] = $this->l('Pickup Management') . ' > ' . $this->l('Pickup Addresses') . ': ' . $this->l('Insert at least one address, otherwise all the pickups requests will fail');
                }

                $pickup_mode = Configuration::get('MBESHIPPING_PICKUP_REQUEST_MODE');
                $default_exsist = false;
                $pickup_address_default = \MbePickupAddressHelper::getDefaultPickupAddress();

                if(!empty($pickup_address_default)) {
                    $default_exsist = true;
                }

                if ($pickup && (empty($pickup_mode) || $pickup_mode == 'automatically') && !$default_exsist) {
                    $warnings[] = $this->l('Pickup Management') . ' : ' . $this->l('You have activated the automatic pickup mode, it is necessary to configure a default collection address for the courier, otherwise shipments will not be created');
                }
            }
        }

        $output = '';
        if (!empty($warnings)) {
            $output .= $this->displayConfigurationWarnings($warnings);
        }


        if (!empty($additional_warnings)) {
            $output .= $this->displayWarning($additional_warnings);
        }

        return $output;
    }

    public function displayConfigurationWarnings($warning)
    {
        $this->context->smarty->assign([
            'warning_title' => $this->l('In order to use this module, you must complete the following steps:'),
            'warning_content' => $warning
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration_warning.tpl');
    }

    ###################################################################
    ## Price management
    ###################################################################

    public function getOrderShippingCostExternal($cart)
    {
        return $this->getOrderShippingCost($cart, 0);
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {
        if (!$this->pricesMbeLoaded) {
            $this->getPricesMbe($cart);
            $this->pricesMbeLoaded = true;
        }

        $result = 0;
        if (!empty($this->carriers)) {
            if (array_key_exists($this->id_carrier, $this->carriers)) {
                $price_in_euro = $this->carriers[(string)$this->id_carrier]['price'];
                $currency = new Currency($cart->id_currency);
                $result = Tools::convertPriceFull($price_in_euro, null, $currency);
            } else {
                return false;
            }
        }
        return $result;
    }

    public function getPricesMbe($cart)
    {
        if (!isset($_SESSION['loggedin']) && (isset($cart->id_address_delivery) ||
                isset($cart['cart']) && isset($cart['cart']->id_address_delivery))) {

            $carriers = Carrier::getCarriers($this->context->language->id, false, false, false,
                null, CarrierCore::PS_CARRIERS_ONLY, $cart);

            foreach ($carriers as $carrier) {
                $this->carriers[$carrier['id_carrier']] = $carrier;
            }
            return true;
        }
    }

    public function hookPostUpdateOrderStatus($params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        // 2 è lo stato del pagamento accettato. (Inserire qui altri stati) todo: fix with constant if exists
        $helper = new PrestaShop\Module\Mbeshipping\Helper\DataHelper();

        if (
            $helper->isEnabled() &&
            Configuration::get('shipments_creation_mode') === PrestaShop\Module\Mbeshipping\Helper\DataHelper::MBE_CREATION_MODE_AUTOMATICALLY
        ) {
            $order_id = (int)$params['id_order'];
            $order = new Order($order_id);

            $cashOnDeliveryModuleName = $helper->getCashOnDeliveryModuleName();
            if ((in_array($params['newOrderStatus']->id, array(2)) && $order->module != $cashOnDeliveryModuleName) ||
                (in_array($params['newOrderStatus']->id, array(3)) && $order->module == $cashOnDeliveryModuleName)) {
                $cart = new Cart($order->id_cart);
                $carrier = new Carrier($order->id_carrier);

                if ($helper->isMbeShipping($order)) {
                    $this->getPricesMbe($cart);
                    $carrierCheck = $this->carriers[$order->id_carrier];
                    if (is_array($carrierCheck)) {
                        $service = Configuration::get('carrier_' . $order->id_carrier);
                        //$service empty custom mapping is enabled. Get the service by ctsom mapping
                        if (!$service || $service === '') {
                            $service = $helper->getCustomMappingService($carrier->id);
                        }
                        $orderHelper = new PrestaShop\Module\Mbeshipping\Helper\OrderHelper();
                        $orderHelper->addShipping($order, $service);
                    }
                }

            }
        }
    }

    private function getInitializedInputValues(...$arrays){
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array['form']['input'] as $input) {
                if (isset($input['name'])) {
                    $result[$input['name']] = '';
                }
            }
        }

        return $result;
    }

    private function transJsString($string) {
        return Translate::getModuleTranslation(
            $this,
            $string,
            $this->name,
            null,
            true
        );
    }

    public function hookExtraCarrier($params)
    {
        $this->createContent($params);
        return $this->display(__FILE__, 'mbe_access_point_shipping-16.tpl');
    }

    public function hookActionValidateOrder($params)
    {
        $this->logger->logDebug(__METHOD__ . ' - VALIDATE ORDER');

        /** @var Cart $cart **/
        $cart = $params['cart'];

        /** @var Order $order **/
        $order = $params['order'];

        /** @var string|false $service **/
        $service = Configuration::get("carrier_{$cart->id_carrier}");

        /** @var array $cached_response **/
        $cached_response = \MbeRatesCacheHelper::getByCartId($cart->id);
        if (empty($cached_response)) {
            $this->logger->logDebug(__METHOD__ . ' - RATES CACHE RESPONSE IS EMPTY');
        }

        $service_objects = null;
        if (!empty($cached_response)) {
            $service_objects = array_filter($cached_response, static function ($value) use ($service) {
                return (string)$value->Service === $service;
            });
        } else {
            $this->logger->logDebug(__METHOD__ . ' - SERVICE NOT FOUND IN RATES CACHE RESPONSE');
        }

        $service_object = null;
        if(!empty($service_objects)) {
            $service_object = array_shift($service_objects);
        }

        $mOrderHelper = new PrestaShop\Module\Mbeshipping\Helper\MOrderHelper();
        $mOrderHelper->insertOrder(
            $order->id,
            false,
            false,
            0,
            isset($service_object->NetTaxAndDutyTotalPrice) ? $service_object->NetTaxAndDutyTotalPrice : null,
            isset($service_object->CustomDutiesGuaranteed) ? $service_object->CustomDutiesGuaranteed : null
        );

        try {
            $this->logger->logDebug($cart->id, 'ID CART');

            $dp_helper = new MbeShippingDPHelper();

            if ($dp_helper->isDeliveryPointByIdCarrier($cart->id_carrier)) {
                $payloadPoint = json_decode($dp_helper->getPayloadByPsIdCart($cart->id));
                if(!empty($payloadPoint)){
                    $address_delivery = new Address($cart->id_address_delivery);

                    $dp_address = clone $address_delivery;
                    $dp_address->id = null;

                    $dp_address->company = $payloadPoint->description;
                    $dp_address->address1 = $payloadPoint->address;
                    $dp_address->city = $payloadPoint->city;
                    $dp_address->id_state = \State::getIdByIso(strtolower($payloadPoint->department));
                    $dp_address->postcode = $payloadPoint->zipCode;
                    $dp_address->phone = strlen($address_delivery->phone) > 0 ? $address_delivery->phone : $address_delivery->phone_mobile;
                    $dp_address->alias = 'DP-Cart' . $cart->id;
                    $dp_address->id_country = \Country::getByIso($payloadPoint->country);
                    $dp_address->save();

                    $cart->id_address_delivery = $dp_address->id;
                    $cart->update();

                    $order->id_address_delivery = $dp_address->id;
                    $order->update();

                    $id_dp = \MbeShippingDPHelper::getIdMbeShippingDp($cart->id);
                    $dp_helper = new MbeShippingDPHelper($id_dp);
                    $dp_helper->id_order = $order->id;
                    $dp_helper->update();

                    $type = $dp_helper->getTypeByPudo($cart->id);
                    $mOrderHelper->setDeliveryPointByIdOrder($order->id, $type);

                    $dp_address->delete();
                }
            }

        } catch (Exception $e) {
            $this->logger->logDebug('VALIDATE ORDER EXCEPTION');
            $this->logger->logDebug($e->getMessage());
        } finally {
            if ($cart) {
                MbeRatesCacheHelper::clearByCartId($cart->id);
            }
            MbeRatesCacheHelper::clearOlderThanMonths();
        }
    }

    public function hookActionDispatcher($params)
    {
        $courier_service_mode = (int)Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE');
        if ($params['controller_class'] === 'AdminCarrierWizardController' && $courier_service_mode !== 1) {
            $id_carrier = Tools::getValue('id_carrier');
            $carrier = new Carrier($id_carrier);

            if ($carrier->external_module_name === 'mbeshipping') {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&active_tab=couriers_services_settings');
            }
        }
    }

    private function filterConfTabsForDirectChannel()
    {
        if(!$this->is_direct_channel_user) {
            return;
        }

        // Tab General Settins
        $this->conf_tabs['general_settings']['label'] = $this->l('General Configurations');
        $this->conf_tabs['general_settings']['desc'] = $this->l('This screen is dedicated to the general configurations of the plugin,
login data, and "activation" of the plugin and configurations on your e-Commerce site.').'<br /><br />'.$this->l('The upper part of the page, which cannot be changed, is only there in case you want to reset the
API Key retrieved from the system. It is recommended that you do not click unless you have a pressing need.').'<br /><br />'.$this->l('The second section, allows you to "activate" the plugin and thus display the various
configurations within your site\'s shopping cart. By clicking on the button, you will be able to activate the plugin and the configurations will be visible to the user who is placing the orders.');

        // Tab Courier and Services
        $this->conf_tabs['couriers_services_settings']['label'] = $this->l('MBEServices');

        // Tab Packages
        $this->conf_tabs['packages_settings']['label'] = $this->l('Parcels');

        // Tab Shipping
        $this->conf_tabs['shipping_settings']['label'] = $this->l('Shipment');
        $this->conf_tabs['shipping_settings']['desc'] = $this->l('In this screen, you can set the creation type of shipments on the MBE systems and define when to "set" the shipment as "delivered".');

        // Tab Markup
        $this->conf_tabs['recharge_settings']['label'] = $this->l('Mark-up');

        // Tab Debug
        $this->conf_tabs['debug_settings']['desc'] = $this->l('The section is useful for checking what anomalies are present in the event of a malfunction. It is therefore recommended to keep this functionality active.');
    }

    private function createHiddenFormInput($input_name) {
        return [
            'type' => 'hidden',
            'name' => $input_name,
        ];
    }

    /* GTM (HEAD) */
    public function hookDisplayBackOfficeHeader()
    {
        if (!(Tools::getValue('configure') === $this->name)) {
            return '';
        }

        $this->context->smarty->assign([
            'gtm_id' => self::GTM_ID,
        ]);

        $this->context->controller->addJS($this->_path . 'views/js/gtm_events.js');

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/gtm_tag.tpl');
    }

    /* GTM (BODY) */
    public function hookDisplayBackOfficeTop()
    {
        if (!(Tools::getValue('configure') === $this->name)) {
            return '';
        }

        $this->context->smarty->assign([
            'gtm_id' => self::GTM_ID,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/gtm_tag_noscript.tpl');
    }

    private function setShippingAndPickupConditions()
    {
        $shipments_creation_mode = Configuration::get('shipments_creation_mode');
        if($shipments_creation_mode === 'automatically') {
            Configuration::updateValue('MBESHIPPING_PICKUP_REQUEST_MODE', 'automatically');
        }
    }

    private function setPickupModeVisibility()
    {
        if($this->is_direct_channel_user) {
            return true;
        }

        $shipments_creation_mode = Configuration::get('shipments_creation_mode');
        return $shipments_creation_mode === 'automatically';
    }

    public function getCheckoutPlugin()
    {
        $checkoutPlugin = 'standard';
        $checkoutPluginsToCheck = ['thecheckout']; // TODO: add other plugins
        foreach ($checkoutPluginsToCheck as $checkoutPluginToCheck) {
            if (Module::isInstalled($checkoutPluginToCheck) && Module::isEnabled($checkoutPluginToCheck)) {
                $checkoutPlugin = $checkoutPluginToCheck;
            }
        }

        return $checkoutPlugin;
    }

    public function hookHeader()
    {
        $this->hookDisplayHeader();
    }

    public function hookDisplayHeader()
    {
        $mbe_dp_carrier_id = Configuration::get('MBE_SHIPPING_DP_CARRIER_ID');
        $mbe_db_type = Configuration::get('MBE_SHIPPING_DP_TYPE');
        $mbe_dp_apikey = Configuration::get('MBE_SHIPPING_DP_APIKEY');
        $mbe_dp_mcode = Configuration::get('MBE_SHIPPING_DP_MCODE');
        $mbe_dp_gel_net = Configuration::get('MBE_SHIPPING_DP_GNET');
        $mbe_dp_mbe_net = Configuration::get('MBE_SHIPPING_DP_MNET');

        if(empty($mbe_dp_carrier_id) || empty($mbe_dp_apikey) || empty($mbe_db_type) || empty($mbe_dp_mcode)) {
            return;
        }

        if(empty($mbe_dp_gel_net) && empty($mbe_dp_mbe_net)) {
            return;
        }

        $network_code = $mbe_dp_mbe_net.','.$mbe_dp_gel_net;
        if($mbe_db_type == 'GEL') {
            if(empty($mbe_dp_gel_net)) {
                return;
            }
            $network_code = $mbe_dp_gel_net;
        } elseif($mbe_db_type == 'MBE') {
            if(empty($mbe_dp_mbe_net)) {
                return;
            }
            $network_code = $mbe_dp_mbe_net;
        }

        $this->context->controller->addJquery();
        $this->context->controller->addJS(
            _PS_MODULE_DIR_ . $this->name . '/views/js/' . $this->ps_version . '/front.js'
        );

        $locale = MbeShippingDPHelper::MBE_GELPRX_CONFIG_DEFAULT_LOCALE;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if(isset($this->context->language->locale)) {
                $locale = str_replace('-', '_', $this->context->language->locale);
            }
            $this->context->controller->registerJavascript(
                'gel-sdk-js',
                'https://platform.gelproximity.com/sdk/latest.js', // Env Endpoint
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 150]
            );
        } else {
            if(isset($this->context->language->language_code)) {
                $locale = str_replace('-', '_', $this->context->language->language_code);
            }
            $this->context->controller->addJS('https://code.gelproximity.com/sdk/latest.js');
        }

        $this->context->controller->addCSS(_PS_MODULE_DIR_ . $this->name . '/views/css/front.css');
        $urlEndUser = 'https://platform.gelproximity.com/gel-enduser-client/'; // Env Endpoint
        $checkoutPlugin = $this->getCheckoutPlugin();

        $service_mode = (int)Configuration::get('MBESHIPPING_COURIERS_SERVICES_CONF_MODE');
        $prices = array();
        if($service_mode === 3) {
            $prices = PrestaShop\Module\Mbeshipping\Helper\DeliveryPointHelper::getDefaultEstimates();
        }

        Media::addJsDef([
            'gelprx_conf' => [
                'id_carrier' => $mbe_dp_carrier_id,
                'ajax_url' => $this->context->link->getModuleLink($this->name, 'mbedp'),
                'ajax_token' => MbeShippingDPHelper::MBE_GELPRX_CONFIG_AJAX_TOKEN,
                'isSandboxMode' => 0, // Env Flag
                'apiKey' => $mbe_dp_apikey,
                'merchantCode' => $mbe_dp_mcode,
                'reference' => 'CartId-' . $this->context->cart->id,
                'redirectMode' => MbeShippingDPHelper::MBE_GELPRX_API_REDIRECT_MODE,
                'locale' => $locale,
                'urlEndUser' => $urlEndUser,
                'isReturnOrder' => false,
                'elementSelector' => 'gelproximity-app__selection-button-sdk-wrapper',
                'buttonText' => $this->l('Select the pick-up point'),
                'buttonEditText' => $this->l('Change the pick-up point'),
                'defaultCarrierCostLabel' => '',
                'checkoutPlugin' => $checkoutPlugin,
                'exactPsVersion' => _PS_VERSION_,
                'mainPsVersion' => $this->ps_version,
                'networkCode' => explode(',', $network_code),
                'prices' => $prices
            ],
        ]);
    }

    private function renderPickUpPointSelectionButton(Carrier $carrier)
    {
        // Verifica sui plugin relativi al checkout
        $checkoutPlugin = $this->getCheckoutPlugin();

        $ps_version = '';

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $ps_version = '17';
        }

        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $ps_version = '16';
        }

        if (Tools::version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $ps_version = '15';
        }

        $this->smarty->assign([
            'carrier' => $carrier,
            'prestashop_version' => $ps_version,
            'is_sandbox_mode' => 0, // Env Flag
            'checkoutPlugin' => $checkoutPlugin,
        ]);

        $id_cart = $this->context->cart->id;

        if (!empty($id_cart)) {
            $payload = MbeShippingDPHelper::getPayloadByPsIdCart($id_cart);

            if ($payload !== false) {
                $this->context->smarty->assign(
                    'payload',
                    MbeShippingDPHelper::doUnserialize($payload, 'json', ['assoc' => true])
                );

                return $this->display(
                    __FILE__,
                    'views/templates/hook/carrier-selection-full-with-details.tpl'
                );
            }
        }

        return $this->display(
            __FILE__,
            'views/templates/hook/carrier-selection.tpl'
        );
    }

    /*ACCESS POINT*/

    public function hookDisplayCarrierExtraContent($params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return '';
        }

        $carrier = isset($params['carrier']) ? new Carrier($params['carrier']['id']) : null;
        $cart = isset($params['cart']) ? $params['cart'] : null;
        if ($cart === null || $carrier === null || !$cart->id_address_delivery) {
            return '';
        }

        $td_info = $this->displayTaxAndDutyCarrier(is_array($carrier) ? $carrier['id'] : $carrier->id);

        if($carrier->id == Configuration::get('MBE_SHIPPING_DP_CARRIER_ID')){
            return $td_info . $this->renderPickUpPointSelectionButton($carrier);
        }

        return $td_info;
    }

    private function createContent($params)
    {
        try {
            $helper = new PrestaShop\Module\Mbeshipping\Helper\DataHelper();
            $dataHelper = new PrestaShop\Module\Mbeshipping\Helper\DataHelper();

            $context = $this->context;

            $cart = isset($params['cart']) ? $params['cart'] : $context->cart;

            $carrier = isset($params['carrier']) ? $params['carrier'] : new Carrier($cart->id_carrier);

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $carrierName = $carrier->name;
                $carrierId = $carrier->id;
            } else {
                $carrierName = $carrier['name'];
                $carrierId = $carrier['id'];
            }

            $uapList = null;
            $mbe_uap = false;

            if (strpos(Tools::strtolower($carrierName), '(mdp)') !== false ||
                $helper->isMBEDeliveryPoint($carrierName)) {
                $address = new Address($cart->id_address_delivery);
                $iso_code = Country::getIsoById($address->id_country);
                $mbe_uap = true;
                $uapList = PrestaShop\Module\Mbeshipping\Helper\UpsUapHelper::getUapList(array(
                    'AddressLine1' => $address->address1,
                    'PostcodePrimaryLow' => $address->postcode,
                    'PoliticalDivision2' => $address->city,
                    'PoliticalDivision1' => State::getNameById($address->id_state),
                    'CountryCode' => $iso_code,
                    'language' => 'IT',
                    'MaximumListSize' => '20',
                    'SearchRadius' => '20'
                ));
            }

            $this->context->smarty->assign(
                [
                    'mbe_uap' => $mbe_uap,
                    'uapList' => $uapList,
                    'carrierId' => $carrierId,
                    'suap' => base64_encode($dataHelper->doSerialize($uapList)),
                    'taxAndDutyMessage'
                ]);
        } catch (Exception $e) {
            //$this->log('CREATE CONTENT SMARTY EXCEPTION');
            //$this->log($e->getMessage());
        }
    }

    public function hookDisplayCarrierList(array $params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return '';
        }

        $carrier = isset($params['carrier']) ? new Carrier($params['carrier']['id']) : null;
        $cart = isset($params['cart']) ? $params['cart'] : null;

        if (!$cart || !$cart->id_address_delivery) {
            return '';
        }

        $td_info = $this->displayTaxAndDutyCarrier(is_array($carrier) ? $carrier['id'] : $carrier->id);

        if($carrier->id == Configuration::get('MBE_SHIPPING_DP_CARRIER_ID')){
            return $td_info . $this->renderPickUpPointSelectionButton($carrier);
        }

        return $td_info;
    }

    private function toggleTaxDutyService(bool $enable = true)
    {
        Configuration::updateValue('MBESHIPPING_TAX_DUTY_SERVICE', $enable);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        /** @var Order $order **/
        $order = $params['order'];
        $mOrderHelper = new PrestaShop\Module\Mbeshipping\Helper\MOrderHelper();
        $mOrder = $mOrderHelper->getByOrderId($order->id);

        $net_tax_and_duty_total_price = isset($mOrder['net_tax_duty_total_price']) ? $mOrder['net_tax_duty_total_price'] : null;

        if (!$net_tax_and_duty_total_price || (float)$net_tax_and_duty_total_price <= 0) {
            return '';
        }

        $custom_duties_guaranteed = isset($mOrder['custom_duties_guaranteed']) ? $mOrder['custom_duties_guaranteed'] : null;

        $this->context->smarty->assign([
            'custom_duties_guaranteed' => $custom_duties_guaranteed,
            'net_tax_and_duty_total_price' => $net_tax_and_duty_total_price,
        ]);

        return $this->display(__FILE__, 'mbe_tax_duty_info.tpl');
    }

    private function displayTaxAndDutyCarrier(int $id_carrier)
    {
        if (!$this->isEnabledTaxAndDutyService()) {
            return '';
        }

        if (!$service = $this->getCachedMbeService($id_carrier)) {
            return '';
        }

        $net_tax_and_duty_total_price = isset($service->NetTaxAndDutyTotalPrice) ? $service->NetTaxAndDutyTotalPrice : null;

        /*
        if (!$net_tax_and_duty_total_price || (float)$net_tax_and_duty_total_price <= 0) {
            return '';
        }
        */
        if (!isset($net_tax_and_duty_total_price)) {
            return '';
        }

        $custom_duties_guaranteed = isset($service->CustomDutiesGuaranteed) ? $service->CustomDutiesGuaranteed : null;

        $this->context->smarty->assign([
            'custom_duties_guaranteed' => $custom_duties_guaranteed,
            'net_tax_and_duty_total_price' => $net_tax_and_duty_total_price,
        ]);

        return $this->display(__FILE__, 'mbe_tax_duty_info.tpl');
    }

    private function getCachedMbeService(int $id_carrier) {
        /** @var string|false $service **/
        $service = Configuration::get("carrier_{$id_carrier}");

        /** @var array $cached_response **/
        $cached_response = \MbeRatesCacheHelper::getByCartId($this->context->cart->id);
        if (empty($cached_response)) {
            $this->logger->logDebug(__METHOD__ . ' - RATES CACHE RESPONSE IS EMPTY');
            return false;
        }

        $service_objects = array_filter($cached_response, static function ($value) use ($service) {
            return $value->Service === $service;
        });
        if (empty($service_objects)) {
            $this->logger->logDebug(__METHOD__ . ' - SERVICE NOT FOUND IN RATES CACHE RESPONSE');
            return false;
        }

        return array_shift($service_objects);
    }


    /**
     * Check if "Tax and Duty" service is enabled
     *
     * @return bool
     */
    private function isEnabledTaxAndDutyService()
    {
        $ws = new PrestaShop\Module\Mbeshipping\Ws();
        return (bool)$ws->getCustomerPermission('enabledTaxAndDuties') === true &&
            (bool)\Configuration::get('MBESHIPPING_TAX_DUTY_SERVICE') === true;
    }
}
