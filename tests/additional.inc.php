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

// including vfsStream library
require_once dirname(__FILE__) . "/libs/vfsStream/vfsStream.php";

// Include a dummy test class for objects data access testing.
require_once dirname(__FILE__) . '/libs/invokeTestClass.php';

// Require the composer autoloader
require_once __DIR__  . "/../vendor/autoload.php";

if (!class_exists('InvoicepdfArticleSummary')) {
    class InvoicepdfArticleSummary {

    }
}

// whether to use the original "aModules" chain from the shop
// methods like "initFromMetadata" and "addChain" will append data to the original chain
oxTestModuleLoader::useOriginalChain(false);

// Loads other module classes as dependencies

// oxTestModuleLoader::addDependencies(array(
//     "path/to/the/module"
// ));

// initiates the module from the metadata file
// does nothing if metadata file is not found
oxTestModuleLoader::initFromMetadata();

// appends the module extension chain with the given module files
oxTestModuleLoader::append(array(//"oxarticle" => "vendor/mymodule/core/myarticle.php",
));