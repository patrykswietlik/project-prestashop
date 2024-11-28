<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayPostalCodesHelper
{

    /**
     * @param $postcode
     * @param $iso_code
     * @return bool|int
     */
    public static function getIdByPostalCodeAndCountry($postcode, $iso_code)
    {
        if ($iso_code == 'IT') {
            $province = self::getItalianProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        } elseif ($iso_code == 'ES') {
            $province = self::getSpanishProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        } elseif ($iso_code == 'UK' || $iso_code == 'GB') {
            $province = self::getUKProvince($postcode);
            if ($province) {
                return State::getIdByName($province);
            }
        }
        return false;
    }

    /**
     * @param $state
     * @return bool|int
     */
    public static function getIdByFuzzyName($state)
    {
        if (empty($state)) {
            return false;
        }
        if ($state == 'Beds' || $state == 'Beds.') {
            $state = 'Bedfordshire';
        } else if ($state == 'Dev' || $state == 'Dev.') {
            $state = 'Devon';
        } else if ($state == 'Dor' || $state == 'Dor.') {
            $state = 'Dorset';
        } else if ($state == 'Co Dur' || $state == 'Co Dur.') {
            $state = 'Durham';
        } else if ($state == 'Glos' || $state == 'Glos.' || $state == 'Gloucs' || $state == 'Gloucs.') {
            $state = 'Gloucestershire';
        } else if ($state == 'Hants' || $state == 'Hants.') {
            $state = 'Hampshire';
        } else if ($state == 'Mx' || $state == 'Middx' || $state == 'Mddx' || $state == 'Mx.' || $state == 'Middx.' || $state == 'Mddx.') {
            $state = 'Middlesex';
        } else if ($state == 'Northants' || $state == 'Northants.') {
            $state = 'Northamptonshire';
        } else if ($state == 'Northumb' || $state == 'Northd' || $state == 'Northumb.' || $state == 'Northd.') {
            $state = 'Northumberland';
        } else if ($state == 'Oxon' || $state == 'Oxon.') {
            $state = 'Oxfordshire';
        } else if ($state == 'Rut' || $state == 'Rut.') {
            $state = 'Rutland';
        } else if ($state == 'Salop' || $state == 'Salop.') {
            $state = 'Shropshire';
        } else if ($state == 'Som' || $state == 'Som.') {
            $state = 'Somerset';
        } else if ($state == 'Sy' || $state == 'Sy.') {
            $state = 'Surrey';
        } else if ($state == 'Sx' || $state == 'Ssx' || $state == 'Sx.' || $state == 'Ssx.') {
            $state = 'Sussex';
        } else if ($state == 'Warks' || $state == 'War' || $state == 'Warks.' || $state == 'War.') {
            $state = 'Warwickshire';
        } else if ($state == 'Worcs' || $state == 'Worsts' || $state == 'Worcs.' || $state == 'Worsts.') {
            $state = 'Worcestershire';
        } else if ($state == 'Cthen' || $state == 'Cthen.') {
            $state = 'Carmarthenshire';
        } else if ($state == 'Mon' || $state == 'Mon.') {
            $state = 'Monmouthshire';
        } else if ($state == 'Pem' || $state == 'Pem.') {
            $state = 'Pembrokeshire';
        } else if ($state == 'County Antrim' || $state == 'Co Antrim' || $state == 'Co. Antrim') {
            $state = 'Antrim';
        } else if ($state == 'County Armagh' || $state == 'Co Armagh' || $state == 'Co. Armagh') {
            $state = 'Armagh';
        } else if ($state == 'County Down' || $state == 'Co Down' || $state == 'Co. Down') {
            $state = 'Down';
        } else if ($state == 'County Fermanagh' || $state == 'Co Fermanagh' || $state == 'Co. Fermanagh') {
            $state = 'Fermanagh';
        } else if ($state == 'Derry' || $state == 'County Londonderry' || $state == 'Co Londonderry' || $state == 'Co. Londonderry') {
            $state = 'Londonderry';
        } else if ($state == 'County Tyrone' || $state == 'Co Tyrone' || $state == 'Co. Tyrone') {
            $state = 'Tyrone';
        }
        if ($state == 'Londonderry') {
            $sstate = Tools::substr($state, 0, 8);
        } else {
            $sstate = Tools::substr($state, 0, 4);
        }
        if ($sstate == 'East') {
            $sstate = Tools::substr($state, 0, 8);
        }
        $result = (int)Db::getInstance()->getValue('
			SELECT `id_state`
			FROM `' . _DB_PREFIX_ . 'state`
			WHERE `name` LIKE \'' . pSQL($sstate) . '%\'
		');
        return $result;
    }

    /**
     * @param string PostalCode $pc
     */
    public static function getItalianProvince($pc)
    {
        $pc = (int)$pc;
        if ($pc >= 15121 && $pc <= 15122) {
            return 'Alessandria';
        } elseif ($pc >= 60121 && $pc <= 60131) {
            return 'Acona';
        } elseif ($pc == 11100) {
            return 'Aosta';
        } elseif ($pc == 52100) {
            return 'Arezzo';
        } elseif ($pc == 63100) {
            return 'Ascoli Piceno';
        } elseif ($pc == 14100) {
            return 'Asti';
        } elseif ($pc == 83100) {
            return 'Avellino';
        } elseif ($pc >= 70121 && $pc <= 70132) {
            return 'Bari';
        } elseif (in_array($pc, array(76123,76011,76016,76017,76125,76121,76012,76013,76014,76015))) {
            return 'Barletta-Andria-Trani';
        } elseif ($pc == 32100) {
            return 'Belluno';
        } elseif ($pc == 82100) {
            return 'Beneveto';
        } elseif ($pc >= 24121 && $pc <= 24129) {
            return 'Bergamo';
        } elseif ($pc == 13900) {
            return 'Biella';
        } elseif ($pc >= 40121 && $pc <= 40141) {
            return 'Bologna';
        } elseif ($pc == 39100) {
            return 'Bolzano';
        } elseif ($pc >= 25121 && $pc <= 25136) {
            return 'Brescia';
        } elseif ($pc == 72100) {
            return 'Brindisi';
        } elseif ($pc >= /*0*/9121 && $pc <= /*0*/9134) {
            return 'Cagliari';
        } elseif ($pc == 93100) {
            return 'Caltanissetta';
        } elseif ($pc == 86100) {
            return 'Campobasso';
        } elseif ($pc == /*0*/9013) {
            return 'Carbonia-Iglesias';
        } elseif ($pc == 81100) {
            return 'Caserta';
        } elseif ($pc >= 95121 && $pc <= 95131) {
            return 'Catania';
        } elseif ($pc == 88100) {
            return 'Catanzaro';
        } elseif ($pc == 66100) {
            return 'Chieti';
        } elseif ($pc == 22100) {
            return 'Como';
        } elseif ($pc == 87100) {
            return 'Cosenza';
        } elseif ($pc == 26100) {
            return 'Cremona';
        } elseif ($pc == 88900) {
            return 'Crontone';
        } elseif ($pc == 12100) {
            return 'Cuneo';
        } elseif ($pc == 94100) {
            return 'Enna';
        } elseif ($pc == 63900) {
            return 'Fermo';
        } elseif ($pc >= 44121 && $pc <= 44124) {
            return 'Ferrara';
        } elseif ($pc >= 50121 && $pc <= 50145) {
            return 'Firenze';
        } elseif ($pc >= 71121 && $pc <= 71122) {
            return 'Foggia';
        } elseif (in_array($pc, array(47121,47122,47021,47032,47030,47011,47521,47522,47042,47012,47013,47034,47010,47035,47043,47020,47014,47025,47015,47020,47010,47016,47017,47030,47018,47027,47039,47019,47028))) {
            return 'Forli-Cesena';
        } elseif ($pc == /*0*/3100) {
            return 'Frosinone';
        } elseif ($pc >= 16121 && $pc <= 16167) {
            return 'Genova';
        } elseif ($pc == 34170) {
            return 'Gorizia';
        } elseif ($pc == 58100) {
            return 'Grosetto';
        } elseif ($pc == 18100) {
            return 'Imperia';
        } elseif ($pc == 86170) {
            return 'Isernia';
        } elseif ($pc == 67100) {
            return 'L\'Aquila';
        } elseif ($pc >= 19121 && $pc <= 19137) {
            return 'La Spezia';
        } elseif ($pc == /*0*/4100) {
            return 'Latina';
        } elseif ($pc == 73100) {
            return 'Lecce';
        } elseif ($pc == 23900) {
            return 'Lecco';
        } elseif ($pc >= 57121 && $pc <= 57128) {
            return 'Livorno';
        } elseif ($pc == 26900) {
            return 'Lodi';
        } elseif ($pc == 55100) {
            return 'Lucca';
        } elseif ($pc == 62100) {
            return 'Macerata';
        } elseif ($pc == 46100) {
            return 'Mantova';
        } elseif ($pc == 54100) {
            return 'Massa';
        } elseif ($pc == 75100) {
            return 'Matera';
        } elseif (in_array($pc, array(/*0*/9020,/*0*/9021,/*0*/9022,/*0*/9025,/*0*/9027,/*0*/9029,/*0*/9030,/*0*/9031,/*0*/9035,/*0*/9036,/*0*/9037,/*0*/9038,/*0*/9039,/*0*/9040))) {
            return 'Medio Campidano';
        } elseif ($pc >= 98121 && $pc <= 98168) {
            return 'Messina';
        } elseif ($pc >= 20121 && $pc <= 20162) {
            return 'Milano';
        } elseif ($pc >= 41121 && $pc <= 41126) {
            return 'Modena';
        } elseif ($pc == 20900) {
            return 'Monza e della Brianza';
        } elseif ($pc >= 80121 && $pc <= 80147) {
            return 'Napoli';
        } elseif ($pc == 28100) {
            return 'Novara';
        } elseif ($pc == /*0*/8100) {
            return 'Nuoro';
        } elseif ($pc == 84061) {
            return 'Ogliastra';
        } elseif ($pc == /*0*/7026) {
            return 'Olbia-Tempio';
        } elseif ($pc == /*0*/9170) {
            return 'Oristano';
        } elseif ($pc >= 35121 && $pc <= 35143) {
            return 'Padova';
        } elseif ($pc >= 90121 && $pc <= 90151) {
            return 'Palermo';
        } elseif ($pc >= 43121 && $pc <= 43126) {
            return 'Parma';
        } elseif ($pc == 27100) {
            return 'Pavia';
        } elseif ($pc >= /*0*/6121 && $pc <= /*0*/6135) {
            return 'Perugia';
        } elseif ($pc >= 61121 && $pc <= 61122) {
            return 'Pesaro-Urbino';
        } elseif ($pc >= 65121 && $pc <= 65129) {
            return 'Pescara';
        } elseif ($pc >= 29121 && $pc <= 29122) {
            return 'Piacenza';
        } elseif ($pc >= 56121 && $pc <= 56128) {
            return 'Pisa';
        } elseif ($pc == 51100) {
            return 'Pistoia';
        } elseif ($pc == 33170) {
            return 'Pordenone';
        } elseif ($pc == 85100) {
            return 'Potenza';
        } elseif ($pc == 59100) {
            return 'Prato';
        } elseif ($pc == 97100) {
            return 'Ragusa';
        } elseif ($pc >= 48121 && $pc <= 48125) {
            return 'Ravenna';
        } elseif ($pc >= 89121 && $pc <= 89135) {
            return 'Reggio Calabria';
        } elseif ($pc >= 42121 && $pc <= 42124) {
            return 'Reggio Emilia';
        } elseif ($pc == /*0*/2100) {
            return 'Rieti';
        } elseif ($pc >= 47921 && $pc <= 47924) {
            return 'Rimini';
        } elseif ($pc >= /*00*/118 && $pc <= /*00*/199) {
            return 'Roma';
        } elseif ($pc == 45100) {
            return 'Rovigo';
        } elseif ($pc >= 84121 && $pc <= 84135) {
            return 'Salerno';
        } elseif ($pc == /*0*/7100) {
            return 'Sassari';
        } elseif ($pc == 17100) {
            return 'Savona';
        } elseif ($pc == 53100) {
            return 'Siena';
        } elseif ($pc == 96100) {
            return 'Siracusa';
        } elseif ($pc == 23100) {
            return 'Sondrio';
        } elseif ($pc >= 74121 && $pc <= 74123) {
            return 'Taranto';
        } elseif ($pc == 64100) {
            return 'Teramo';
        } elseif ($pc == /*0*/5100) {
            return 'Terni';
        } elseif ($pc >= 10121 && $pc <= 10156) {
            return 'Torino';
        } elseif ($pc == 91100) {
            return 'Trapani';
        } elseif ($pc >= 38121 && $pc <= 38123) {
            return 'Trento';
        } elseif ($pc == 31100) {
            return 'Treviso';
        } elseif ($pc >= 34121 && $pc <= 34151) {
            return 'Trieste';
        } elseif ($pc == 33100) {
            return 'Udine';
        } elseif ($pc == 21100) {
            return 'Varese';
        } elseif ($pc >= 30121 && $pc <= 30176) {
            return 'Venezia';
        } elseif (in_array($pc, array(28921,28922,28923,28924,28925,28877,28899,28861,28831,28832,28842,28833,28814,28822,28881,28875,28801,28865,28827,28853,28863,28823,28883,28816,28876,28854,28895,28817,28843,28824,28877,28885,28818,28803,28896,28804,28838,28826,28859,28879,28819,
            28856,28841,28813,28851,28846,28873,28821,28815,28825,28891,28852,28862,28845,28827,28887,28836,28828,28893,28894,28855,28802,28864,28891,28887,28884,28886,28866,28898,28856,28857,58858,28868,28897,28868,28844,28805))) {
            return 'Verbano-Cusio-Ossola';
        } elseif ($pc == 13100) {
            return 'Vercelli';
        } elseif ($pc >= 37121 && $pc <= 37142) {
            return 'Verona';
        } elseif ($pc == 89900) {
            return 'Vibo Valentia';
        } elseif ($pc == 36100) {
            return 'Vicenza';
        } elseif ($pc == /*0*/1100) {
            return 'Viterbo';
        }
        return false;
    }

    /**
     * @param string PostalCode $pc
     */
    public static function getSpanishProvince($pc)
    {
        $pc = Tools::substr($pc, 0, 2);
        switch ($pc) {
            case '01':
                return 'Álava';
            case '02':
                return 'Albacete';
            case '03':
                return 'Alacant';
            case '04':
                return 'Almería';
            case '33':
                return 'Asturias';
            case '05':
                return 'Ávila';
            case '06':
                return 'Badajoz';
            case '07':
                return 'Balears';
            case '08':
                return 'Barcelona';
            case '09':
                return 'Burgos';
            case '10':
                return 'Cáceres';
            case '11':
                return 'Cádiz';
            case '39':
                return 'Cantabria';
            case '12':
                return 'Castelló';
            case '13':
                return 'Ciudad Real';
            case '14':
                return 'Córdoba';
            case '16':
                return 'Cuenca';
            case '17':
                return 'Girona';
            case '18':
                return 'Granada';
            case '19':
                return 'Guadalajara';
            case '20':
                return 'Gipuzkoa';
            case '21':
                return 'Huelva';
            case '22':
                return 'Huesca';
            case '23':
                return 'Jaén';
            case '26':
                return 'La Rioja';
            case '35':
                return 'Las Palmas';
            case '24':
                return 'León';
            case '25':
                return 'Lleida';
            case '27':
                return 'Lugo';
            case '28':
                return 'Madrid';
            case '29':
                return 'Málaga';
            case '30':
                return 'Murcia';
            case '31':
                return 'Nafarroa';
            case '32':
                return 'Ourense';
            case '34':
                return 'Palencia';
            case '36':
                return 'Pontevedra';
            case '37':
                return 'Salamanca';
            case '38':
                return 'Santa Cruz de Tenerife';
            case '40':
                return 'Segovia';
            case '41':
                return 'Sevilla';
            case '42':
                return 'Soria';
            case '43':
                return 'Tarragona';
            case '44':
                return 'Teruel';
            case '45':
                return 'Toledo';
            case '46':
                return 'València';
            case '47':
                return 'Valladolid';
            case '48':
                return 'Bizkaia';
            case '49':
                return 'Zamora';
            case '50':
                return 'Zaragoza';
            case '51':
                return 'Ceuta';
            case '52':
                return 'Melilla';
        }
        return false;
    }

    /**
     * @param $pc
     */
    public static function getUKProvince($pc)
    {

        $pc = strstr($pc, ' ', true);

        if (in_array($pc, array('AB10', 'AB11', 'AB12', 'AB15', 'AB16'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB21', 'AB22', 'AB23', 'AB24', 'AB25'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB99'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB13'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB14'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB32'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB33'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB34'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB35'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB36'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB41'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB42'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB43'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB51'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB52'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB53'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('AB54'))) {
            return 'Aberdeenshire';
        } elseif (in_array($pc, array('DD1', 'DD2', 'DD3', 'DD4', 'DD5'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD7'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD8'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD8'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD9'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD10'))) {
            return 'Angus';
        } elseif (in_array($pc, array('DD11'))) {
            return 'Angus';
        } elseif (in_array($pc, array('BT1', 'BT2', 'BT3', 'BT4', 'BT5', 'BT6', 'BT7', 'BT8', 'BT9'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT10', 'BT11', 'BT12', 'BT13', 'BT14', 'BT15', 'BT16', 'BT17'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT29'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT27', 'BT28'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT29'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT36', 'BT37'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT58'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT38'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT39'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT40'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT41'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT42', 'BT43', 'BT44'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT53'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT54'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT56'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('BT57'))) {
            return 'Antrim';
        } elseif (in_array($pc, array('PA21'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA22'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA23'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA24', 'PA25', 'PA26', 'PA27'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA28'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA29'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA30', 'PA31'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA32'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA33'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA34', 'PA37'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA80'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA35'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA36'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PA38'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PH36'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PH49'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('PH50'))) {
            return 'Argyll';
        } elseif (in_array($pc, array('BT60', 'BT61'))) {
            return 'Armagh';
        } elseif (in_array($pc, array('BT62', 'BT63', 'BT64', 'BT65', 'BT66', 'BT67'))) {
            return 'Armagh';
        } elseif (in_array($pc, array('BA1', 'BA2'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BA3'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS0', 'BS1', 'BS2', 'BS3', 'BS4', 'BS5', 'BS6', 'BS7', 'BS8', 'BS9'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS10', 'BS11', 'BS13', 'BS14', 'BS15', 'BS16'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS20'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS30', 'BS31', 'BS32', 'BS34', 'BS35', 'BS36', 'BS37', 'BS39'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS40', 'BS41', 'BS48', 'BS49'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS80'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS98', 'BS99'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS21'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS22', 'BS23', 'BS24'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS25'))) {
            return 'Avon';
        } elseif (in_array($pc, array('BS29'))) {
            return 'Avon';
        } elseif (in_array($pc, array('GL9'))) {
            return 'Avon';
        } elseif (in_array($pc, array('KA1', 'KA2', 'KA3'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA4'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA5'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA6', 'KA7', 'KA8'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA9'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA10'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA11', 'KA12'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA13'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA14', 'KA15'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA16'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA17'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA18'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA19'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA20'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA21'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA22'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA23'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA24'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA25'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA26'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA29'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('KA30'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('PA17'))) {
            return 'Ayrshire';
        } elseif (in_array($pc, array('AB37'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('AB38'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('AB44'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('AB45'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('AB55'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('AB56'))) {
            return 'Banffshire';
        } elseif (in_array($pc, array('LU1', 'LU2', 'LU3', 'LU4'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('LU5', 'LU6'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('LU7'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('MK40', 'MK41', 'MK42', 'MK43', 'MK44', 'MK45'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('SG15'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('SG16'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('SG17'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('SG18'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('SG19'))) {
            return 'Bedfordshire';
        } elseif (in_array($pc, array('GU47'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG1', 'RG2', 'RG4', 'RG5', 'RG6', 'RG7', 'RG8'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG10', 'RG19'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG30', 'RG31'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG12'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG42'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG14'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG20'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG17'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG18', 'RG19'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG40', 'RG41'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('RG45'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL1', 'SL2', 'SL3'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL95'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL4'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL5'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL6'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('SL60'))) {
            return 'Berkshire';
        } elseif (in_array($pc, array('TD2'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD3'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD4'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD10', 'TD11'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD12'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD13'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('TD14'))) {
            return 'Berwickshire';
        } elseif (in_array($pc, array('HP5'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP6', 'HP7'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP8'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP9'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP10', 'HP11', 'HP12', 'HP13', 'HP14', 'HP15'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP16'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP17', 'HP18', 'HP19'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP20', 'HP21', 'HP22'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('HP22', 'HP27'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK1', 'MK2', 'MK3', 'MK4', 'MK5', 'MK6', 'MK7', 'MK8', 'MK9'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK10', 'MK11', 'MK12', 'MK13', 'MK14', 'MK15', 'MK17', 'MK19'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK77'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK16'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK18'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('MK46'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('SL0'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('SL7'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('SL8'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('SL9'))) {
            return 'Buckinghamshire';
        } elseif (in_array($pc, array('KW1'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW2', 'KW3'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW5'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW6'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW7'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW12'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('KW14'))) {
            return 'Caithness';
        } elseif (in_array($pc, array('CB1', 'CB2', 'CB3', 'CB4', 'CB5'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('CB21', 'CB22', 'CB23', 'CB24', 'CB25'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('CB6', 'CB7'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE1', 'PE2', 'PE3', 'PE4', 'PE5', 'PE6', 'PE7', 'PE8'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE99'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE13', 'PE14'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE15'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE16'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE19'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE26', 'PE28', 'PE29'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('PE27'))) {
            return 'Cambridgeshire';
        } elseif (in_array($pc, array('GY1', 'GY2', 'GY3', 'GY4', 'GY5', 'GY6', 'GY7', 'GY8', 'GY9'))) {
            return 'Channel Islands';
        } elseif (in_array($pc, array('GY10'))) {
            return 'Channel Islands';
        } elseif (in_array($pc, array('JE1', 'JE2', 'JE3', 'JE4', 'JE5'))) {
            return 'Channel Islands';
        } elseif (in_array($pc, array('CH1', 'CH2', 'CH3', 'CH4'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CH70'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CH88'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CH99'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW1', 'CW2', 'CW3', 'CW4'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW98'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW5'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW6'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW7'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW8', 'CW9'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW10'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW11'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('CW12'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('M33'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK1', 'SK2', 'SK3', 'SK4', 'SK5', 'SK6', 'SK7'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK12'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK8'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK9'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK9'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK10', 'SK11'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK14'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK15'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SK16'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('SY14'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA1', 'WA2', 'WA3', 'WA4', 'WA5'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA55'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA6'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA7'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA8'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA88'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA13'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA14', 'WA15'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('WA16'))) {
            return 'Cheshire';
        } elseif (in_array($pc, array('FK10'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('FK10'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('FK11'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('FK12'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('FK13'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('FK14'))) {
            return 'Clackmannan';
        } elseif (in_array($pc, array('TS1', 'TS2', 'TS3', 'TS4', 'TS5', 'TS6', 'TS7', 'TS8', 'TS9'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS10', 'TS11'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS12', 'TS13'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS14'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS15'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS16', 'TS17', 'TS18', 'TS19'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS20', 'TS21'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS22', 'TS23'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('TS24', 'TS25', 'TS26', 'TS27'))) {
            return 'Cleveland';
        } elseif (in_array($pc, array('CH5'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('CH6'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('CH6'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('CH7'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('CH7'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('CH8'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL11', 'LL12', 'LL13', 'LL14'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL15'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL16'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL17', 'LL18'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL18'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL19'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL20'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL21'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL22'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('LL28', 'LL29'))) {
            return 'Clwyd';
        } elseif (in_array($pc, array('EX23'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL10', 'PL11'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL12'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL13'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL14'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL15'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL17'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL18'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL18'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL22'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL23'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL24'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL25', 'PL26'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL27'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL28'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL29'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL30', 'PL31'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL32'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL33'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL34'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('PL35'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR1', 'TR2', 'TR3', 'TR4'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR5'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR6'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR7', 'TR8'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR9'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR10'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR11'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR12', 'TR13'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR14'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR15', 'TR16'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR17'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR18', 'TR19'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR20'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR26'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('TR27'))) {
            return 'Cornwall';
        } elseif (in_array($pc, array('Postcodes'))) {
            return 'County/State';
        } elseif (in_array($pc, array('CA1', 'CA2', 'CA3', 'CA4', 'CA5', 'CA6'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA99'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA7'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA8'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA9'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA10', 'CA11'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA12'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA13'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA14'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA95'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA15'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA16'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA17'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA18'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA19'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA20'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA21'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA22'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA23'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA24'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA25'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA26'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA27'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('CA28'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA7'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA8', 'LA9'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA10'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA11'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA12'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA13', 'LA14'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA14', 'LA15'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA16'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA17'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA18', 'LA19'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA20'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA21'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA22'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('LA23'))) {
            return 'Cumbria';
        } elseif (in_array($pc, array('DE1', 'DE3'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE21', 'DE22', 'DE23', 'DE24'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE65'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE72', 'DE73', 'DE74'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE99'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE4'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE5'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE6'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE7'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE11', 'DE12'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE45'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE55'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE56'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('DE75'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('S18'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('S32', 'S33'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('S40', 'S41', 'S42', 'S43', 'S44', 'S45', 'S49'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('SK13'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('SK17'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('SK22', 'SK23'))) {
            return 'Derbyshire';
        } elseif (in_array($pc, array('EX1', 'EX2', 'EX3', 'EX4', 'EX5', 'EX6'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX7'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX8'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX9'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX10'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX11'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX12'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX13'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX14'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX15'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX16'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX17'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX18'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX19'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX20'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX20'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX21'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX22'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX24'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX31', 'EX32'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX33'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX34'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX34'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX35'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX35'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX36'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX37'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX38'))) {
            return 'Devon';
        } elseif (in_array($pc, array('EX39'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL1', 'PL2', 'PL3', 'PL4', 'PL5', 'PL6', 'PL7', 'PL8', 'PL9'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL95'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL16'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL19'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL20'))) {
            return 'Devon';
        } elseif (in_array($pc, array('PL21'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ1', 'TQ2'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ3', 'TQ4'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ5'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ6'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ7'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ8'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ9'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ9', 'TQ10'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ11'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ12', 'TQ13'))) {
            return 'Devon';
        } elseif (in_array($pc, array('TQ14'))) {
            return 'Devon';
        } elseif (in_array($pc, array('BH1', 'BH2', 'BH3', 'BH4', 'BH5', 'BH6', 'BH7', 'BH8', 'BH9'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH10', 'BH11'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH12', 'BH13', 'BH14', 'BH15', 'BH16', 'BH17'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH18'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH19'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH20'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH21'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH22'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH23'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BH31'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT1', 'DT2'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT3', 'DT4'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT5'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT6'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT7'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT8'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT9'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT10'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('DT11'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('SP7'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('SP8'))) {
            return 'Dorset';
        } elseif (in_array($pc, array('BT18'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT19'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT20'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT21'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT22', 'BT23'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT24'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT25'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT26'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT30'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT31'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT32'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT33'))) {
            return 'Down';
        } elseif (in_array($pc, array('BT34', 'BT35'))) {
            return 'Down';
        } elseif (in_array($pc, array('DG1', 'DG2'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG3'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG4'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG10'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG11'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG12'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG13'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG14'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('DG16'))) {
            return 'Dumfries and Galloway';
        } elseif (in_array($pc, array('G81'))) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, array('G82'))) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, array('G83'))) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, array('G83'))) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, array('G84'))) {
            return 'Dunbartonshire';
        } elseif (in_array($pc, array('DH1', 'DH6', 'DH7', 'DH8'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DH97', 'DH98', 'DH99'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DH2', 'DH3'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DH8'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DH8', 'DH9'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL1', 'DL2', 'DL3'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL98'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL4'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL5'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL12'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL13', 'DL14'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL15'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL16'))) {
            return 'Durham';
        } elseif (in_array($pc, array('DL16', 'DL17'))) {
            return 'Durham';
        } elseif (in_array($pc, array('SR7'))) {
            return 'Durham';
        } elseif (in_array($pc, array('SR8'))) {
            return 'Durham';
        } elseif (in_array($pc, array('TS28'))) {
            return 'Durham';
        } elseif (in_array($pc, array('TS29'))) {
            return 'Durham';
        } elseif (in_array($pc, array('SA14', 'SA15'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA16'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA17'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA17'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA18'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA19'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA19'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA19'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA20'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA31', 'SA32', 'SA33'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA34'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA35'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA36'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA37'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA38'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA39'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA40'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA41'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA42'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA43'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA44'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA45'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA46', 'SA48'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA47'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA48'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA61', 'SA62'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA63'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA64'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA65'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA66'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA67'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA68'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA69'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA70'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA71', 'SA72'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA72'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SA73'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY23'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY23'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY23'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY24'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY24'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY24'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY25'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('SY25'))) {
            return 'Dyfed';
        } elseif (in_array($pc, array('EH31'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH32'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH32'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH33', 'EH34', 'EH35'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH36'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH39'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH40'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH41'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('EH42'))) {
            return 'East Lothian';
        } elseif (in_array($pc, array('BN1', 'BN2'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN41', 'BN42', 'BN45'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN50', 'BN51'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN88'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN3'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN52'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN7', 'BN8'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN9'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN10'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN20', 'BN21', 'BN22', 'BN23'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN24'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN25'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN26'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('BN27'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('RH18'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN2', 'TN5'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN6'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN7'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN19'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN20'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN21'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN22'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN31'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN32'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN33'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN34', 'TN35'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN36'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN37', 'TN38'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN39'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('TN40'))) {
            return 'East Sussex';
        } elseif (in_array($pc, array('CB10', 'CB11'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM0'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM0'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM1', 'CM2', 'CM3'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM92', 'CM98', 'CM99'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM4'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM5'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM6', 'CM7'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM7'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM77'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM8'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM11', 'CM12'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM13', 'CM14', 'CM15'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM16'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM17', 'CM18', 'CM19'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM20'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CM24'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO1', 'CO2', 'CO3', 'CO4', 'CO5', 'CO6', 'CO7'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO11'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO12'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO13'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO14'))) {
            return 'Essex';
        } elseif (in_array($pc, array('CO15', 'CO16'))) {
            return 'Essex';
        } elseif (in_array($pc, array('EN9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG1', 'IG2', 'IG3', 'IG4', 'IG5', 'IG6'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG7', 'IG8'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG8'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG10'))) {
            return 'Essex';
        } elseif (in_array($pc, array('IG11'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM1', 'RM2', 'RM3', 'RM4', 'RM5', 'RM6', 'RM7'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM8', 'RM9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM10'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM11', 'RM12'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM13'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM14'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM15'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM16', 'RM17'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM20'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM18'))) {
            return 'Essex';
        } elseif (in_array($pc, array('RM19'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS0', 'SS1'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS1', 'SS2', 'SS3'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS22'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS99'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS4'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS5'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS6'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS7'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS8'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS9'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS11', 'SS12'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS13', 'SS14', 'SS15', 'SS16'))) {
            return 'Essex';
        } elseif (in_array($pc, array('SS17'))) {
            return 'Essex';
        } elseif (in_array($pc, array('BT74'))) {
            return 'Fermanagh';
        } elseif (in_array($pc, array('BT92', 'BT93', 'BT94'))) {
            return 'Fermanagh';
        } elseif (in_array($pc, array('DD6'))) {
            return 'Fife';
        } elseif (in_array($pc, array('DD6'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY1', 'KY2'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY3'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY4'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY4'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY5'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY6', 'KY7'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY8', 'KY9'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY10'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY11', 'KY12'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY99'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY11'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY14', 'KY15'))) {
            return 'Fife';
        } elseif (in_array($pc, array('KY16'))) {
            return 'Fife';
        } elseif (in_array($pc, array('GL1', 'GL2', 'GL3', 'GL4'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL19'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL5', 'GL6'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL7'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL7'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL7'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL8'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL10'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL11'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL11', 'GL12'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL13'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL14'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL14'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL14'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL15'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL15'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL16'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL17'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL17'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL17'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL17'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL17'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL18'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL18'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL20'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL50', 'GL51', 'GL52', 'GL53', 'GL54'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL55'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('GL56'))) {
            return 'Gloucestershire';
        } elseif (in_array($pc, array('NP4'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP7'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP10', 'NP11', 'NP18', 'NP19'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP20'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP12'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP13'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP15'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP16'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP22'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP23'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP24'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP25'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP26'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('NP44'))) {
            return 'Gwent';
        } elseif (in_array($pc, array('LL23'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL24'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL25'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL26'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL27'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL30'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL31', 'LL32'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL31'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL33'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL34'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL35'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL36'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL37'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL38'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL39'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL40'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL41'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL42'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL43'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL44'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL45'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL46'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL47'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL48'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL49'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL51'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL52'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL53'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL54', 'LL55'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL56'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL57'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL58'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL59'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL60'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL61'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL62'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL63'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL64'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL77'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL65'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL66'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL67'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL68'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL69'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL70'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL71'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL72'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL73'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL74'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL75'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL76'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL77'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('LL78'))) {
            return 'Gwynedd';
        } elseif (in_array($pc, array('BH24'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('BH25'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU11', 'GU12'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU14'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU30'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU31', 'GU32'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU33'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU34'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU35'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU46'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('GU51', 'GU52'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO1', 'PO2', 'PO3', 'PO6'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO4', 'PO5'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO7', 'PO8'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO9'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO9'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO10'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO11'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO12', 'PO13'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO12', 'PO13'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('PO14', 'PO15', 'PO16', 'PO17'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('RG21', 'RG22', 'RG23', 'RG24', 'RG25', 'RG28'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('RG26'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('RG27', 'RG29'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('RG28'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO14', 'SO15', 'SO16', 'SO17', 'SO18', 'SO19'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO30', 'SO31', 'SO32'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO40', 'SO45'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO52'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO97'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO20'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO21', 'SO22', 'SO23', 'SO25'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO24'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO40', 'SO43'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO41'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO42'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO50', 'SO53'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SO51'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SP6'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('SP10', 'SP11'))) {
            return 'Hampshire';
        } elseif (in_array($pc, array('HR1', 'HR2', 'HR3', 'HR4'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('HR5'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('HR6'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('HR7'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('HR8'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('HR9'))) {
            return 'Herefordshire';
        } elseif (in_array($pc, array('AL1', 'AL2', 'AL3', 'AL4'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('AL5'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('AL6', 'AL7'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('AL7', 'AL8'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('AL9'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('AL10'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('CM21'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('CM22', 'CM23'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN4', 'EN5'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN6'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN7', 'EN8'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN77'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN10', 'EN11'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('EN11'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('HP1', 'HP2', 'HP3'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('HP4'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('HP23'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG1', 'SG2'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG3'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG4', 'SG5', 'SG6'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG6'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG7'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG8'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG9'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG10'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG11', 'SG12'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('SG13', 'SG14'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD3'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD4', 'WD18'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD5'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD6'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD7'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD17', 'WD18', 'WD19'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD24', 'WD25'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD99'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('WD23'))) {
            return 'Hertfordshire';
        } elseif (in_array($pc, array('HS8'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('IV1', 'IV2', 'IV3', 'IV5'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('IV13'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('IV63'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('IV99'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('IV4'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH19'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH20'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH21'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH22'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH23'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH24'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH25'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH30'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH31'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH32'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH33'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH34'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH35'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH37'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH38'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH39'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('PH40', 'PH41'))) {
            return 'Inverness-shire';
        } elseif (in_array($pc, array('KA27'))) {
            return 'Isle of Arran';
        } elseif (in_array($pc, array('HS9'))) {
            return 'Isle of Barra';
        } elseif (in_array($pc, array('HS7'))) {
            return 'Isle of Benbecula';
        } elseif (in_array($pc, array('PA20'))) {
            return 'Isle of Bute';
        } elseif (in_array($pc, array('PH44'))) {
            return 'Isle of Canna';
        } elseif (in_array($pc, array('PA78'))) {
            return 'Isle of Coll';
        } elseif (in_array($pc, array('PA61'))) {
            return 'Isle of Colonsay';
        } elseif (in_array($pc, array('KA28'))) {
            return 'Isle of Cumbrae';
        } elseif (in_array($pc, array('PH42'))) {
            return 'Isle of Eigg';
        } elseif (in_array($pc, array('PA41'))) {
            return 'Isle of Gigha';
        } elseif (in_array($pc, array('HS3', 'HS5'))) {
            return 'Isle of Harris';
        } elseif (in_array($pc, array('PA76'))) {
            return 'Isle of Iona';
        } elseif (in_array($pc, array('PA42', 'PA43', 'PA44', 'PA45', 'PA46', 'PA47', 'PA48', 'PA49'))) {
            return 'Isle of Islay';
        } elseif (in_array($pc, array('PA60'))) {
            return 'Isle of Jura';
        } elseif (in_array($pc, array('HS1'))) {
            return 'Isle of Lewis';
        } elseif (in_array($pc, array('HS2'))) {
            return 'Isle of Lewis';
        } elseif (in_array($pc, array('IM1', 'IM2', 'IM3', 'IM4', 'IM5', 'IM6', 'IM7', 'IM8', 'IM9'))) {
            return 'Isle of Man';
        } elseif (in_array($pc, array('IM99'))) {
            return 'Isle of Man';
        } elseif (in_array($pc, array('PA62', 'PA63', 'PA64', 'PA65', 'PA66', 'PA67', 'PA68', 'PA69'))) {
            return 'Isle of Mull';
        } elseif (in_array($pc, array('PA70', 'PA71', 'PA72', 'PA73', 'PA74', 'PA75'))) {
            return 'Isle of Mull';
        } elseif (in_array($pc, array('HS6'))) {
            return 'Isle of North Uist';
        } elseif (in_array($pc, array('PH43'))) {
            return 'Isle of Rum';
        } elseif (in_array($pc, array('HS4'))) {
            return 'Isle of Scalpay';
        } elseif (in_array($pc, array('IV41', 'IV42', 'IV43', 'IV44', 'IV45', 'IV46', 'IV47', 'IV48', 'IV49'))) {
            return 'Isle of Skye';
        } elseif (in_array($pc, array('IV55', 'IV56'))) {
            return 'Isle of Skye';
        } elseif (in_array($pc, array('IV51'))) {
            return 'Isle of Skye';
        } elseif (in_array($pc, array('PA77'))) {
            return 'Isle of Tiree';
        } elseif (in_array($pc, array('PO30'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO30'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO41'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO31'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO32'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO33'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO34'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO35'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO36'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO36', 'PO37'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO38'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO39'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('PO40'))) {
            return 'Isle of Wight';
        } elseif (in_array($pc, array('TR21', 'TR22', 'TR23', 'TR24', 'TR25'))) {
            return 'Isles of Scilly';
        } elseif (in_array($pc, array('BR1', 'BR2'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR2'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR3'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR4'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR5', 'BR6'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR7'))) {
            return 'Kent';
        } elseif (in_array($pc, array('BR8'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT1', 'CT2', 'CT3', 'CT4'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT5'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT6'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT7', 'CT9'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT8'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT9'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT10'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT11', 'CT12'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT13'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT14'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT15', 'CT16', 'CT17'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT18', 'CT19'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT20'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT50'))) {
            return 'Kent';
        } elseif (in_array($pc, array('CT21'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA1', 'DA2', 'DA4'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA10'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA3'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA5'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA6', 'DA7'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA7'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA16'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA8'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA18'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA9'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA10'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA11', 'DA12', 'DA13'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA14', 'DA15'))) {
            return 'Kent';
        } elseif (in_array($pc, array('DA17'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME1', 'ME2', 'ME3'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME4', 'ME5'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME6'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME20'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME6'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME6'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME19'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME7', 'ME8'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME9'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME10'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME11'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME12'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME13'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME14', 'ME15', 'ME16', 'ME17', 'ME18'))) {
            return 'Kent';
        } elseif (in_array($pc, array('ME99'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN1', 'TN2', 'TN3', 'TN4'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN8'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN9'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN10', 'TN11', 'TN12'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN13', 'TN14', 'TN15'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN16'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN17', 'TN18'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN23', 'TN24', 'TN25', 'TN26', 'TN27'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN28'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN29'))) {
            return 'Kent';
        } elseif (in_array($pc, array('TN30'))) {
            return 'Kent';
        } elseif (in_array($pc, array('AB30'))) {
            return 'Kincardineshire';
        } elseif (in_array($pc, array('AB31'))) {
            return 'Kincardineshire';
        } elseif (in_array($pc, array('AB39'))) {
            return 'Kincardineshire';
        } elseif (in_array($pc, array('DG5'))) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, array('DG6'))) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, array('DG7'))) {
            return 'Kirkcudbrightshire';
        } elseif (in_array($pc, array('G1', 'G2', 'G3', 'G4', 'G5', 'G9'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G11', 'G12', 'G13', 'G14', 'G15'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G20', 'G21', 'G22', 'G23'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G31', 'G32', 'G33', 'G34'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G40', 'G41', 'G42', 'G43', 'G44', 'G45', 'G46'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G51', 'G52', 'G53', 'G58'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G60', 'G61', 'G62', 'G63', 'G64', 'G65', 'G66', 'G67', 'G68', 'G69'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G70', 'G71', 'G72', 'G73', 'G74', 'G75', 'G76', 'G77', 'G78', 'G79'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('G90'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML1'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML2'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML3'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML4'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML5'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML6'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML7'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML8'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML9'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML10'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML11'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('ML12'))) {
            return 'Lanarkshire';
        } elseif (in_array($pc, array('BB1', 'BB2', 'BB6'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB3'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB4'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB5'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB8'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB9'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB10', 'BB11', 'BB12'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB18'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BB94'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BL0', 'BL8', 'BL9'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BL1', 'BL2', 'BL3', 'BL4', 'BL5', 'BL6', 'BL7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BL11'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('BL78'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('FY0', 'FY1', 'FY2', 'FY3', 'FY4'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('FY5'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('FY6'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('FY7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('FY8'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('L39'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('L40'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('LA1', 'LA2'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('LA3', 'LA4'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('LA5', 'LA6'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M1', 'M2', 'M3', 'M4', 'M8', 'M9'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M11', 'M12', 'M13', 'M14', 'M15', 'M16', 'M17', 'M18', 'M19'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M20', 'M21', 'M22', 'M23', 'M24', 'M25', 'M26', 'M27', 'M28', 'M29'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M30', 'M31', 'M32', 'M34', 'M35', 'M38'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M40', 'M41', 'M43', 'M44', 'M45', 'M46'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M60', 'M61'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M90', 'M99'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M3', 'M5', 'M6', 'M7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M50'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('M60'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL1', 'OL2', 'OL3', 'OL4', 'OL8', 'OL9'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL95'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL5', 'OL6', 'OL7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL10'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL11', 'OL12', 'OL16'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL13'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL14'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('OL15', 'OL16'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('PR0', 'PR1', 'PR2', 'PR3', 'PR4', 'PR5'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('PR6', 'PR7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('PR25', 'PR26'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('WN1', 'WN2', 'WN3', 'WN4', 'WN5', 'WN6', 'WN8'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('WN7'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('WN8'))) {
            return 'Lancashire';
        } elseif (in_array($pc, array('LE1', 'LE2', 'LE3', 'LE4', 'LE5', 'LE6', 'LE7', 'LE8', 'LE9'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE19'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE21'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE41'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE55'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE87'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE94', 'LE95'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE10'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE11', 'LE12'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE13', 'LE14'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE17'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE18'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE65'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE67'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE67'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('LE67'))) {
            return 'Leicestershire';
        } elseif (in_array($pc, array('DN21'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN1', 'LN2', 'LN3', 'LN4', 'LN5', 'LN6'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN7', 'LN8'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN9'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN10'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN11'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN12'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('LN13'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('NG31', 'NG32', 'NG33'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('NG34'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE9'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE10'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE11', 'PE12'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE20', 'PE21', 'PE22'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE23'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('PE24', 'PE25'))) {
            return 'Lincolnshire';
        } elseif (in_array($pc, array('E1', 'E1W'))) {
            return 'London';
        } elseif (in_array($pc, array('E2', 'E3', 'E4', 'E5', 'E6', 'E7', 'E8', 'E9'))) {
            return 'London';
        } elseif (in_array($pc, array('E10', 'E11', 'E12', 'E13', 'E14', 'E15', 'E16', 'E17', 'E18'))) {
            return 'London';
        } elseif (in_array($pc, array('E20'))) {
            return 'London';
        } elseif (in_array($pc, array('E77'))) {
            return 'London';
        } elseif (in_array($pc, array('E98'))) {
            return 'London';
        } elseif (in_array($pc, array('EC1A', 'EC1M', 'EC1N', 'EC1P', 'EC1R', 'EC1V', 'EC1Y'))) {
            return 'London';
        } elseif (in_array($pc, array('EC2A', 'EC2M', 'EC2N', 'EC2P', 'EC2R', 'EC2V', 'EC2Y'))) {
            return 'London';
        } elseif (in_array($pc, array('EC3A', 'EC3M', 'EC3N', 'EC3P', 'EC3R', 'EC3V'))) {
            return 'London';
        } elseif (in_array($pc, array('EC4A', 'EC4M', 'EC4N', 'EC4P', 'EC4R', 'EC4V', 'EC4Y'))) {
            return 'London';
        } elseif (in_array($pc, array('EC50'))) {
            return 'London';
        } elseif (in_array($pc, array('N1', 'N1C', 'N1P'))) {
            return 'London';
        } elseif (in_array($pc, array('N2', 'N3', 'N4', 'N5', 'N6', 'N7', 'N8', 'N9'))) {
            return 'London';
        } elseif (in_array($pc, array('N10', 'N11', 'N12', 'N13', 'N14', 'N15', 'N16', 'N17', 'N18', 'N19'))) {
            return 'London';
        } elseif (in_array($pc, array('N20', 'N21', 'N22'))) {
            return 'London';
        } elseif (in_array($pc, array('N81'))) {
            return 'London';
        } elseif (in_array($pc, array('NW1', 'NW1W'))) {
            return 'London';
        } elseif (in_array($pc, array('NW2', 'NW3', 'NW4', 'NW5', 'NW6', 'NW7', 'NW8', 'NW9'))) {
            return 'London';
        } elseif (in_array($pc, array('NW10', 'NW11'))) {
            return 'London';
        } elseif (in_array($pc, array('NW26'))) {
            return 'London';
        } elseif (in_array($pc, array('SE1', 'SE1P'))) {
            return 'London';
        } elseif (in_array($pc, array('SE2', 'SE3', 'SE4', 'SE5', 'SE6', 'SE7', 'SE8', 'SE9'))) {
            return 'London';
        } elseif (in_array($pc, array('SE10', 'SE11', 'SE12', 'SE13', 'SE14', 'SE15', 'SE16', 'SE17', 'SE18', 'SE19'))) {
            return 'London';
        } elseif (in_array($pc, array('SE20', 'SE21', 'SE22', 'SE23', 'SE24', 'SE25', 'SE26', 'SE27', 'SE28'))) {
            return 'London';
        } elseif (in_array($pc, array('SW1A', 'SW1E', 'SW1H', 'SW1P', 'SW1V', 'SW1W', 'SW1X', 'SW1Y'))) {
            return 'London';
        } elseif (in_array($pc, array('SW2', 'SW3', 'SW4', 'SW5', 'SW6', 'SW7', 'SW8', 'SW9'))) {
            return 'London';
        } elseif (in_array($pc, array('SW10', 'SW11', 'SW12', 'SW13', 'SW14', 'SW15', 'SW16', 'SW17', 'SW18', 'SW19'))) {
            return 'London';
        } elseif (in_array($pc, array('SW20'))) {
            return 'London';
        } elseif (in_array($pc, array('SW95'))) {
            return 'London';
        } elseif (in_array($pc, array('W1A', 'W1B', 'W1C', 'W1D', 'W1F', 'W1G', 'W1H', 'W1J', 'W1K', 'W1S', 'W1T', 'W1U', 'W1W'))) {
            return 'London';
        } elseif (in_array($pc, array('W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9'))) {
            return 'London';
        } elseif (in_array($pc, array('W10', 'W11', 'W12', 'W13', 'W14'))) {
            return 'London';
        } elseif (in_array($pc, array('WC1A', 'WC1B', 'WC1E', 'WC1H', 'WC1N', 'WC1R', 'WC1V', 'WC1X'))) {
            return 'London';
        } elseif (in_array($pc, array('WC2A', 'WC2B', 'WC2E', 'WC2H', 'WC2N', 'WC2R'))) {
            return 'London';
        } elseif (in_array($pc, array('BT45'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('BT46'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('BT47', 'BT48'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('BT49'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('BT51', 'BT52'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('BT55'))) {
            return 'Londonderry';
        } elseif (in_array($pc, array('CH25'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH41', 'CH42'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH26'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH43'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH27'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH44', 'CH45'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH28', 'CH29'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH30', 'CH31', 'CH32'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH46', 'CH47', 'CH48', 'CH49'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH60', 'CH61', 'CH62', 'CH63'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH33'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH64'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH34'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CH65', 'CH66'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L1', 'L2', 'L3', 'L4', 'L5', 'L6', 'L7', 'L8', 'L9'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L10', 'L11', 'L12', 'L13', 'L14', 'L15', 'L16', 'L17', 'L18', 'L19'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L20', 'L21', 'L22', 'L23', 'L24', 'L25', 'L26', 'L27', 'L28', 'L29'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L31', 'L32', 'L33', 'L36', 'L37', 'L38'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L67', 'L68', 'L69'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L70', 'L71', 'L72', 'L73', 'L74', 'L75'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L20'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L30'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L69'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L80'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('GIR'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('L34', 'L35'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('PR8', 'PR9'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('WA9'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('WA10', 'WA11'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('WA12'))) {
            return 'Merseyside';
        } elseif (in_array($pc, array('CF31', 'CF32', 'CF33', 'CF35'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF34'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF36'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF37', 'CF38'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF39'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF40'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF41'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF42'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF43'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF44'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF45'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF46'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF47', 'CF48'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF72'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF81'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF82'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('CF83'))) {
            return 'Mid Glamorgan';
        } elseif (in_array($pc, array('EN1', 'EN2', 'EN3'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA0', 'HA9'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA1', 'HA2', 'HA3'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA4'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA5'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA6'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA7'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('HA8'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW1', 'TW2'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW3', 'TW4', 'TW5', 'TW6'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW7'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW8'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW11'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW12'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW13', 'TW14'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW15'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW16'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW17'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('TW18', 'TW19'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB1', 'UB2', 'UB3'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB3', 'UB4'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB5'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB5', 'UB6'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB18'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB7', 'UB8'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB8', 'UB9'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('UB10', 'UB11'))) {
            return 'Middlesex';
        } elseif (in_array($pc, array('EH1', 'EH2', 'EH3', 'EH4', 'EH5', 'EH6', 'EH7', 'EH8', 'EH9'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH10', 'EH11', 'EH12', 'EH13', 'EH14', 'EH15', 'EH16', 'EH17'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH91', 'EH95', 'EH99'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH14'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH14'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH14'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH18'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH19'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH20'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH21'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH22'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH23'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH24'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH25'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH26'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH27'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH28'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH37'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('EH38'))) {
            return 'Midlothian';
        } elseif (in_array($pc, array('IV30'))) {
            return 'Moray';
        } elseif (in_array($pc, array('IV31'))) {
            return 'Moray';
        } elseif (in_array($pc, array('IV32'))) {
            return 'Moray';
        } elseif (in_array($pc, array('IV36'))) {
            return 'Moray';
        } elseif (in_array($pc, array('PH26'))) {
            return 'Moray';
        } elseif (in_array($pc, array('IV12'))) {
            return 'Nairnshire';
        } elseif (in_array($pc, array('IP20'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('IP21', 'IP22'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('IP98'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('IP24', 'IP25', 'IP26'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR1', 'NR2', 'NR3', 'NR4', 'NR5', 'NR6', 'NR7', 'NR8', 'NR9'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR10', 'NR11', 'NR12', 'NR13', 'NR14', 'NR15', 'NR16', 'NR18', 'NR19'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR26', 'NR28'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR99'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR17'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR18'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR19'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR20'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR21'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR22'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR23'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR24'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR25'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR26'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR27'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR28'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR29'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('NR30', 'NR31'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('PE30', 'PE31', 'PE32', 'PE33', 'PE34'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('PE35'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('PE36'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('PE37'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('PE38'))) {
            return 'Norfolk';
        } elseif (in_array($pc, array('DN14'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU1', 'HU2', 'HU3', 'HU4', 'HU5', 'HU6', 'HU7', 'HU8', 'HU9'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU10', 'HU11', 'HU12'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU13'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU14'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU15'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU16'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU20'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU17'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU18'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('HU19'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('YO15', 'YO16'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('YO25'))) {
            return 'North Humberside';
        } elseif (in_array($pc, array('BD23', 'BD24'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('BD24'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL6', 'DL7'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL8'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL8'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL8'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL9'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('DL10', 'DL11'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('HG1', 'HG2', 'HG3'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('HG4'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('HG5'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('LS24'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO1'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO10', 'YO19'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO23', 'YO24', 'YO26'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO30', 'YO31', 'YO32'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO41', 'YO42', 'YO43'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO51'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO60', 'YO61', 'YO62'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO90', 'YO91'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO7'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO8'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO11', 'YO12', 'YO13'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO14'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO17'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO18'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('YO21', 'YO22'))) {
            return 'North Yorkshire';
        } elseif (in_array($pc, array('NN1', 'NN2', 'NN3', 'NN4', 'NN5', 'NN6', 'NN7'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN8', 'NN9'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN29'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN10'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN11'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN12'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN13'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN14', 'NN15', 'NN16'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NN17', 'NN18'))) {
            return 'Northamptonshire';
        } elseif (in_array($pc, array('NE22'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE23'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE24'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE41'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE42'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE43'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE44'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE45'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE46', 'NE47', 'NE48'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE49'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE61', 'NE65'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE62'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE63'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE64'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE66'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE66', 'NE69'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE67'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE68'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE70'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('NE71'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('TD12'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('TD12'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('TD15'))) {
            return 'Northumberland';
        } elseif (in_array($pc, array('DN22'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG1', 'NG2', 'NG3', 'NG4', 'NG5', 'NG6', 'NG7', 'NG8', 'NG9'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG10', 'NG11', 'NG12', 'NG13', 'NG14', 'NG15', 'NG16', 'NG17'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG80'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG90'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG17'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG18', 'NG19'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG20', 'NG21'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG70'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG22', 'NG23', 'NG24'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('NG25'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('S80', 'S81'))) {
            return 'Nottinghamshire';
        } elseif (in_array($pc, array('KW15'))) {
            return 'Orkney';
        } elseif (in_array($pc, array('KW16'))) {
            return 'Orkney';
        } elseif (in_array($pc, array('KW17'))) {
            return 'Orkney';
        } elseif (in_array($pc, array('OX1', 'OX2', 'OX3', 'OX4'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX33'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX44'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX5'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX7'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX9'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX10'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX11'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX12'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX13', 'OX14'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX15', 'OX16', 'OX17'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX18'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX18'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX18'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX20'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX25', 'OX26', 'OX27'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX28', 'OX29'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX39'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('OX49'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('RG9'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('SN7'))) {
            return 'Oxfordshire';
        } elseif (in_array($pc, array('EH43'))) {
            return 'Peeblesshire';
        } elseif (in_array($pc, array('EH44'))) {
            return 'Peeblesshire';
        } elseif (in_array($pc, array('EH45'))) {
            return 'Peeblesshire';
        } elseif (in_array($pc, array('EH46'))) {
            return 'Peeblesshire';
        } elseif (in_array($pc, array('KY13'))) {
            return 'Perthshire and Kinross';
        } elseif (in_array($pc, array('FK15'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('FK16'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('FK17', 'FK18'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('FK19'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('FK20'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('FK21'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH1', 'PH2'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH14'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH3', 'PH4'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH5', 'PH6', 'PH7'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH8'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH9'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH16', 'PH17', 'PH18'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH10', 'PH11', 'PH12', 'PH13'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('PH15'))) {
            return 'Perthshire and Kinross ';
        } elseif (in_array($pc, array('LD1'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD2'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD3'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD4'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD5'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD6'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD7'))) {
            return 'Powys';
        } elseif (in_array($pc, array('LD8'))) {
            return 'Powys';
        } elseif (in_array($pc, array('NP7', 'NP8'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY15'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY16'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY17'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY17'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY18'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY19'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY20'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY21'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY22'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY22'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY22'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY22'))) {
            return 'Powys';
        } elseif (in_array($pc, array('SY22'))) {
            return 'Powys';
        } elseif (in_array($pc, array('PA1', 'PA2', 'PA3'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA4'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA5', 'PA6', 'PA9'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA10'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA7'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA8'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA11'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA12'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA13'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA14'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA15', 'PA16'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA18'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('PA19'))) {
            return 'Renfrewshire';
        } elseif (in_array($pc, array('IV6'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV7'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV15', 'IV16'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV8'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV9'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV10'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV11'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV14'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV17'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV18'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV19'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV20'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV21'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV22'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV23'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV26'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV40'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV52'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV53'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('IV54'))) {
            return 'Ross-shire';
        } elseif (in_array($pc, array('TD5'))) {
            return 'Roxburghshire';
        } elseif (in_array($pc, array('TD6'))) {
            return 'Roxburghshire';
        } elseif (in_array($pc, array('TD8'))) {
            return 'Roxburghshire';
        } elseif (in_array($pc, array('TD9'))) {
            return 'Roxburghshire';
        } elseif (in_array($pc, array('TD9'))) {
            return 'Roxburghshire';
        } elseif (in_array($pc, array('LE15'))) {
            return 'Rutland';
        } elseif (in_array($pc, array('LE16'))) {
            return 'Rutland';
        } elseif (in_array($pc, array('TD1'))) {
            return 'Selkirkshire';
        } elseif (in_array($pc, array('TD7'))) {
            return 'Selkirkshire';
        } elseif (in_array($pc, array('ZE1', 'ZE2', 'ZE3'))) {
            return 'Shetland';
        } elseif (in_array($pc, array('SY1', 'SY2', 'SY3', 'SY4', 'SY5'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY99'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY6'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY7'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY7'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY7'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY8'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY9'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY10', 'SY11'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY12'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('SY13'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF1', 'TF2', 'TF3', 'TF4', 'TF5', 'TF6', 'TF7', 'TF8'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF9'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF10'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF11'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF12'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('TF13'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('WV15', 'WV16'))) {
            return 'Shropshire';
        } elseif (in_array($pc, array('BA4'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA5'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA6'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA7'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA8'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA9'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA9'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA10'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA11'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA16'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BA20', 'BA21', 'BA22'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BS26'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BS27'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('BS28'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA1', 'TA2', 'TA3', 'TA4'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA5', 'TA6', 'TA7'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA8'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA9'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA10'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA11'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA12'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA13'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA14'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA15'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA16'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA17'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA18'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA19'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA20'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA21'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA22'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA23'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('TA24'))) {
            return 'Somerset';
        } elseif (in_array($pc, array('CF3', 'CF5'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF10', 'CF11', 'CF14', 'CF15'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF23', 'CF24'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF30'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF91', 'CF95', 'CF99'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF61'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF71'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF62', 'CF63'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF64'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF64'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('CF71'))) {
            return 'South Glamorgan';
        } elseif (in_array($pc, array('DN15', 'DN16', 'DN17'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN18'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN19'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN20'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN31', 'DN32', 'DN33', 'DN34', 'DN36', 'DN37'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN41'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN35'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN38'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN39'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN40'))) {
            return 'South Humberside';
        } elseif (in_array($pc, array('DN1', 'DN2', 'DN3', 'DN4', 'DN5', 'DN6', 'DN7', 'DN8', 'DN9'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('DN10', 'DN11', 'DN12'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('DN55'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S10', 'S11', 'S12', 'S13', 'S14', 'S17'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S20', 'S21', 'S25', 'S26'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S35', 'S36'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S95', 'S96', 'S97', 'S98', 'S99'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S60', 'S61', 'S62', 'S63', 'S65', 'S66'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S64'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('S70', 'S71', 'S72', 'S73', 'S74', 'S75'))) {
            return 'South Yorkshire';
        } elseif (in_array($pc, array('B77', 'B78', 'B79'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('DE13', 'DE14', 'DE15'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST1', 'ST2', 'ST3', 'ST4', 'ST6', 'ST7', 'ST8', 'ST9'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST5'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST13'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST14'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST15'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST16', 'ST17', 'ST18', 'ST19'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('ST20', 'ST21'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('WS7'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('WS11', 'WS12'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('WS13', 'WS14'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('WS15'))) {
            return 'Staffordshire';
        } elseif (in_array($pc, array('FK1', 'FK2'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('FK3'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('FK4'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('FK5'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('FK6'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('FK7', 'FK8', 'FK9'))) {
            return 'Sterling';
        } elseif (in_array($pc, array('CB8'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('CB9'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('CO8'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('CO10'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP1', 'IP2', 'IP3', 'IP4', 'IP5', 'IP6', 'IP7', 'IP8', 'IP9'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP10'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP11'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP12', 'IP13'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP14'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP15'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP16'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP17'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP18'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP19'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP21', 'IP23'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP27'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP28', 'IP29'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('IP30', 'IP31', 'IP32', 'IP33'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('NR32', 'NR33'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('NR34'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('NR35'))) {
            return 'Suffolk';
        } elseif (in_array($pc, array('CR0', 'CR9'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR44'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR90'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR2'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR3'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR3'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR4'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR5'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR6'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR7'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('CR8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU1', 'GU2', 'GU3', 'GU4', 'GU5'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU6'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU7', 'GU8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU9'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU10'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU15', 'GU16', 'GU17'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU95'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU18'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU19'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU20'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU21', 'GU22', 'GU23', 'GU24'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU25'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU26', 'GU27'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('GU27'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT1', 'KT2'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT3'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT4'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT5', 'KT6'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT7'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT9'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT10'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT11'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT12'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT13'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT14'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT15'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT16'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT17', 'KT18', 'KT19'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT20'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT21'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('KT22', 'KT23', 'KT24'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH1'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH2'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH3', 'RH4'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH4', 'RH5'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH6'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH7'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH8'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('RH9'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('SM1', 'SM2', 'SM3'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('SM4'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('SM5'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('SM6'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('SM7'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('TW9'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('TW10'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('TW20'))) {
            return 'Surrey';
        } elseif (in_array($pc, array('IV24'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('IV25'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('IV27'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('IV28'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('KW8'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('KW9'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('KW10'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('KW11'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('KW13'))) {
            return 'Sutherland';
        } elseif (in_array($pc, array('DH4', 'DH5'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE1', 'NE2', 'NE3', 'NE4', 'NE5', 'NE6', 'NE7'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE12', 'NE13', 'NE15', 'NE16', 'NE17', 'NE18', 'NE19'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE20', 'NE27'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE82', 'NE83', 'NE85', 'NE88'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE98', 'NE99'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE8', 'NE9'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE10', 'NE11'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE92'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE21'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE25', 'NE26'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE28'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE29'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE30'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE31'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE32'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE33', 'NE34'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE35'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE36'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE37', 'NE38'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE39'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('NE40'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('SR1', 'SR2', 'SR3', 'SR4', 'SR5', 'SR6', 'SR9'))) {
            return 'Tyne and Wear';
        } elseif (in_array($pc, array('BT68'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT69'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT70', 'BT71'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT75'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT76'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT77'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT78', 'BT79'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT80'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT81'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('BT82'))) {
            return 'Tyrone';
        } elseif (in_array($pc, array('B49'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('B50'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('B80'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV8'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV9'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV10', 'CV11', 'CV13'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV12'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV21', 'CV22', 'CV23'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV31', 'CV32', 'CV33'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV34', 'CV35'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV36', 'CV37'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV37'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('CV47'))) {
            return 'Warwickshire';
        } elseif (in_array($pc, array('SA1', 'SA2', 'SA3', 'SA4', 'SA5', 'SA6', 'SA7', 'SA8', 'SA9'))) {
            return 'West Glamorgan';
        } elseif (in_array($pc, array('SA80'))) {
            return 'West Glamorgan';
        } elseif (in_array($pc, array('SA99'))) {
            return 'West Glamorgan';
        } elseif (in_array($pc, array('SA10', 'SA11'))) {
            return 'West Glamorgan';
        } elseif (in_array($pc, array('SA12', 'SA13'))) {
            return 'West Glamorgan';
        } elseif (in_array($pc, array('EH29'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH30'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH47', 'EH48'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH49'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH51'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH52'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH53', 'EH54'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('EH55'))) {
            return 'West Lothian';
        } elseif (in_array($pc, array('B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B10', 'B11', 'B12', 'B13', 'B14', 'B15', 'B16', 'B17', 'B18', 'B19'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B20', 'B21', 'B23', 'B24', 'B25', 'B26', 'B27', 'B28', 'B29'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B30', 'B31', 'B32', 'B33', 'B34', 'B35', 'B36', 'B37', 'B38'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B40', 'B42', 'B43', 'B44', 'B45', 'B46', 'B47', 'B48'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B99'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B62', 'B63'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B64'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B65'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B66', 'B67'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B68', 'B69'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B70', 'B71'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B72', 'B73', 'B74', 'B75', 'B76'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B90', 'B91', 'B92', 'B93', 'B94'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('B95'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('CV1', 'CV2', 'CV3', 'CV4', 'CV5', 'CV6', 'CV7', 'CV8'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('DY1', 'DY2', 'DY3'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('DY4'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('DY5'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('DY6'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('DY7', 'DY8', 'DY9'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('WS1', 'WS2', 'WS3', 'WS4', 'WS5', 'WS6', 'WS8', 'WS9'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('WS10'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('WV1', 'WV2', 'WV3', 'WV4', 'WV5', 'WV6', 'WV7', 'WV8', 'WV9'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('WV1'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('WV14'))) {
            return 'West Midlands';
        } elseif (in_array($pc, array('BN5'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN6'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN11', 'BN12', 'BN13', 'BN14'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN91', 'BN99'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN15'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN99'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN16', 'BN17'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN18'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN43'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BN44'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('GU28'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('GU29'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('PO18', 'PO19'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('PO20'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('PO21', 'PO22'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH6'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH10', 'RH11'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH77'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH12', 'RH13'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH14'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH15'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH16', 'RH17'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH19'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('RH20'))) {
            return 'West Sussex';
        } elseif (in_array($pc, array('BD1', 'BD2', 'BD3', 'BD4', 'BD5', 'BD6', 'BD7', 'BD8', 'BD9'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD10', 'BD11', 'BD12', 'BD13', 'BD14', 'BD15'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD98', 'BD99'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD16'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD97'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD17', 'BD18'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD98'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD19'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('BD20', 'BD21', 'BD22'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HD1', 'HD2', 'HD3', 'HD4', 'HD5', 'HD7', 'HD8'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HD6'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HD9'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HX1', 'HX2', 'HX3', 'HX4'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HX1', 'HX5'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HX6'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('HX7'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS1', 'LS2', 'LS3', 'LS4', 'LS5', 'LS6', 'LS7', 'LS8', 'LS9'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS10', 'LS11', 'LS12', 'LS13', 'LS14', 'LS15', 'LS16', 'LS17', 'LS18', 'LS19'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS20', 'LS25', 'LS26', 'LS27'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS88'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS98', 'LS99'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS21'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS22', 'LS23'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS28'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('LS29'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF1', 'WF2', 'WF3', 'WF4'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF90'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF5'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF6'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF10'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF7', 'WF8', 'WF9'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF10'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF11'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF12', 'WF13'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF14'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF15', 'WF16'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF16'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('WF17'))) {
            return 'West Yorkshire';
        } elseif (in_array($pc, array('DG8'))) {
            return 'Wigtownshire';
        } elseif (in_array($pc, array('DG9'))) {
            return 'Wigtownshire';
        } elseif (in_array($pc, array('BA12'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('BA13'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('BA14'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('BA15'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN1', 'SN2', 'SN3', 'SN4', 'SN5', 'SN6'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN25', 'SN26'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN38'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN99'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN8'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN9'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN10'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN11'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN12'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN13', 'SN15'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN14', 'SN15'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SN16'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SP1', 'SP2', 'SP3', 'SP4', 'SP5'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('SP9'))) {
            return 'Wiltshire';
        } elseif (in_array($pc, array('B60', 'B61'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('B96', 'B97', 'B98'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('DY10', 'DY11', 'DY14'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('DY12'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('DY13'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR1', 'WR2', 'WR3', 'WR4', 'WR5', 'WR6', 'WR7', 'WR8'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR78'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR99'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR9'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR10'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR11'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR11', 'WR12'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR13', 'WR14'))) {
            return 'Worcestershire';
        } elseif (in_array($pc, array('WR15'))) {
            return 'Worcestershire';
        }
        return false;
    }
}
