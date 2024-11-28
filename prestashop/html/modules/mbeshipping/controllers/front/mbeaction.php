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

use PrestaShop\Module\Mbeshipping\Helper\DataHelper;
use PrestaShop\Module\Mbeshipping\Ws;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MbeshippingMbeactionModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        if (\Tools::getIsset('files')) {
            $files = \Tools::getValue('files');

            if (count($files) > 1) {
                $values = [];
                foreach ($files as $file) {
                    // retrieve file path infos
                    $pathInfo = pathinfo($file);

                    // check whether file path is allowed or not
                    if (strpos($pathInfo['dirname'] . DIRECTORY_SEPARATOR, (new DataHelper())->getMediaPath()) !== 0) {
                        http_response_code(403);
                        die('Forbidden');
                    }

                    // check whether file extension is allowed or not
                    if (!in_array($pathInfo['extension'], ['gif', 'html', 'pdf'])) {
                        http_response_code(403);
                        die('Forbidden');
                    }

                    // check whether file path is readable
                    if (!is_readable($file)) {
                        http_response_code(403);
                        die('Forbidden');
                    }

                    $values[] = $pathInfo['basename'];
                }

                $dir = (new DataHelper())->getMediaPath();
                $path = $dir . 'compressed.zip';

                if (file_exists($path)) {
                    unlink($path);
                }

                chdir($dir);
                $archive = new PclZip($path);
                $response = $archive->create($values);

                if ($response == 0) {
                    die('[ERROR] PclZip : ' . $archive->errorInfo(true));
                }

                if (headers_sent()) {
                    die('HTTP header already sent');
                }

                if (!is_file($path)) {
                    http_response_code(404);
                    die('File not found');
                }

                if (!is_readable($path)) {
                    http_response_code(403);
                    die('File not readable');
                }

                http_response_code(200);
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($path));
                header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
                if (ob_get_length() > 0) {
                    ob_clean();
                }
                flush();
                readfile($path);
                exit;

            } else {
                $file = $files[0];

                // retrieve file path infos
                $pathInfo = pathinfo($file);

                // check whether file path is allowed or not
                if (strpos($pathInfo['dirname'] . DIRECTORY_SEPARATOR, (new DataHelper())->getMediaPath()) !== 0) {
                    http_response_code(403);
                    die('Forbidden');
                }

                // check whether file extension is allowed or not
                if (!in_array($pathInfo['extension'], ['gif', 'html', 'pdf'])) {
                    http_response_code(403);
                    die('Forbidden');
                }

                // check whether file path is readable
                if (!is_readable($file)) {
                    http_response_code(403);
                    die('Forbidden');
                }

                switch ($pathInfo['extension']) {
                    case 'html':
                        header("Content-Disposition:inline;filename=\"" . $pathInfo['basename'] . "\"");
                        header("Content-length: " . filesize($file));
                        if (ob_get_length() > 0) {
                            ob_clean();
                        }
                        flush();
                        readfile($file);
                        exit;
                    case 'gif':
                        header("Content-type:image/gif");
                        header("Content-Disposition:inline;filename=\"" . $pathInfo['basename'] . "\"");
                        header("Content-length: " . filesize($file));
                        if (ob_get_length() > 0) {
                            ob_clean();
                        }
                        flush();
                        readfile($file);
                        exit;
                    default:
                        header("Content-type:application/pdf");
                        header("Content-Disposition:inline;filename=\"" . $pathInfo['basename'] . "\"");
                        header("Content-length: " . filesize($file));
                        if (ob_get_length() > 0) {
                            ob_clean();
                        }
                        flush();
                        readfile($file);
                        exit;
                }
            }
        }

        if (\Tools::getIsset('close')) {
            if (\Configuration::get('shipments_closure_mode') == DataHelper::MBE_CLOSURE_MODE_AUTOMATICALLY) {
                $ws = new Ws();
                if ($ws->mustCloseShipments()) {
                    define('PS_MBE_SCRIPT', true);
                    $toClosedIds = array();
                    $alreadyClosedIds = array();
                    $withoutTracking = array();

                    $helper = new DataHelper();

                    if ($helper->isEnabledCustomMapping()) {
                        $customMapping = $helper->getCustomMappingCarriers();
                        $sql = 'SELECT o.`id_order` FROM ' . _DB_PREFIX_ . 'orders as a LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`) LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = a.`id_order`) INNER JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (o.`id_order` = oc.`id_order`) AND (oc.`id_carrier` IN (' . implode(array_map('pSQL', $customMapping), ',') . ') OR oc.`id_carrier` IN (SELECT `id_carrier` from `' . _DB_PREFIX_ . 'carrier` where `external_module_name` = "mbeshipping")) INNER JOIN `' . _DB_PREFIX_ . 'carrier` ca ON (ca.`id_carrier` = oc.`id_carrier`)';
                    } else {
                        $sql = 'SELECT o.`id_order` FROM ' . _DB_PREFIX_ . 'orders as a LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`) LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = a.`id_order`) INNER JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (o.`id_order` = oc.`id_order`) AND oc.`id_carrier` IN (SELECT `id_carrier` from `' . _DB_PREFIX_ . 'carrier` where `external_module_name` = "mbeshipping") INNER JOIN `' . _DB_PREFIX_ . 'carrier` ca ON (ca.`id_carrier` = oc.`id_carrier`)';
                    }

                    $results = \Db::getInstance()->executeS($sql);

                    $boxes = array();
                    foreach ($results as $r) {
                        $boxes[] = $r['id_order'];
                    }

                    foreach ($boxes as $shipmentId) {
                        if (!$helper->hasTracking($shipmentId)) {
                            $withoutTracking[] = $shipmentId;
                        } elseif ($helper->isShippingOpen($shipmentId)) {
                            $toClosedIds[] = $shipmentId;
                        } else {
                            $alreadyClosedIds[] = $shipmentId;
                        }
                    }

                    $ws->closeShipping($toClosedIds);

                    if (count($withoutTracking) > 0) {
                        echo sprintf(('%s - Total of %d order(s) without tracking number yet.'), date('Y-m-d H:i:s'), count($withoutTracking)) . "\n";
                    }

                    if (count($toClosedIds) > 0) {
                        echo sprintf(('%s - Total of %d order(s) have been closed.'), date('Y-m-d H:i:s'), count($toClosedIds)) . "\n";
                    }

                    if (count($alreadyClosedIds) > 0) {
                        echo sprintf(('%s - Total of %d order(s) was already closed'), date('Y-m-d H:i:s'), count($alreadyClosedIds)) . "\n";
                    }
                }
            } else {
                echo 'You selected manually mode in the configuration';
            }
        }
    }
}
