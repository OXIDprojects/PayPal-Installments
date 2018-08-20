<?php
/**
 * This file is part of OXID eSales PayPal Plus module.
 *
 * OXID eSales PayPal Plus module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal Plus module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal Plus module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

if (getenv("oxPATH")) {
    define("oxPATH", rtrim(getenv("oxPATH"), "/") . "/");
} else {
    if (!defined("oxPATH")) {
        die("oxPATH is not defined");
    }
}

if (!defined("OXID_VERSION_SUFIX")) {
    define("OXID_VERSION_SUFIX", "");
}

// setting the include path
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

require_once "unit/test_config.inc.php";
require_once "unit/OxidTestCase.php";
require_once "additional.inc.php";

define('oxADMIN_LOGIN', oxDb::getDb()->getOne("select OXUSERNAME from oxuser where oxid='oxdefaultadmin'"));
define ('oxADMIN_PASSWD', getenv('oxADMIN_PASSWD') ? getenv('oxADMIN_PASSWD') : 'admin');
