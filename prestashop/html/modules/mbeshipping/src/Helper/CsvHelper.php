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

namespace PrestaShop\Module\Mbeshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CsvHelper
{
    protected $csvHeaderKnowDefinitions = array(
        'country'       => 'country',
        'region'        => 'region',
        'city'          => 'city',
        'zip'           => 'zip',
        'zip_to'        => 'zip_to',
        'weight_from'   => 'weight_from',
        'weight_to'     => 'weight_to',
        'price'         => 'price',
        'delivery_type' => 'delivery_type',
    );

    public function getCsvHeaderKnowDefinitions()
    {
        return $this->csvHeaderKnowDefinitions;
    }

    public function readFile($importFilePath)
    {
        $file = fopen($importFilePath, "r");
        $i = 0;
        $header = null;
        $result = array();

        while (!feof($file)) {
            //$this->log("Reading row " . $i . " ...");
            $currentRow = fgetcsv($file, 0, ",", '"');
            if ($i == 0) {
                //File heading
                $header = $currentRow;
            } else {
                $current = $this->readCsvRowToArray($header, $currentRow);
                $result[] = $current;
            }
            $i++;
        }
        return $result;
    }

    private function readCsvRowToArray($header, $row)
    {
        $result = array();
        if (is_array($row)) {
            foreach ($row as $index => $currentRowValue) {
                $headerName = $this->cleanString($header[$index]);
                $currentKey = $headerName;
                if ( isset($this->csvHeaderKnowDefinitions[$headerName])) {
                    $currentKey = $this->csvHeaderKnowDefinitions[$headerName];
                }
                $currentRowValue = trim($currentRowValue);
                /*
                $currentRowValue = str_replace("  ", " ", $currentRowValue);
                $currentRowValue = str_replace("  ", " ", $currentRowValue);
                $currentRowValue = str_replace("  ", " ", $currentRowValue);
                $currentRowValue = str_replace("„", "\"", $currentRowValue);
                $currentRowValue = str_replace("“", "\"", $currentRowValue);
                $currentRowValue = str_replace("”", "\"", $currentRowValue);
                */
                $result[$currentKey] = $currentRowValue;
            }
        }
        return $result;
    }

    private function cleanString($str)
    {
        $result = $str;
        $result = trim($result);
        $result = \Tools::strtolower($result);
        $result = str_replace(" ", "_", $result);
        $result = str_replace(chr(160), "_", $result);
        $result = str_replace(chr(194), "_", $result);
        $result = str_replace(chr(195), "_", $result);
        $result = str_replace(",", "_", $result);
        $result = str_replace("/", "", $result);
        $result = str_replace("(", "", $result);
        $result = str_replace(")", "", $result);
        $result = str_replace("__", "_", $result);
        return $result;
    }
}
