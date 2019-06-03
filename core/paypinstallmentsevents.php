<?php

/**
 * This file is part of PayPal Installments module.
 *
 * PayPal Installments module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PayPal Installments module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PayPal Installments module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          https://www.paypal.com
 * @copyright (C) PayPal (Europe) S.à r.l. et Cie, S.C.A. 2015
 */
class paypInstallmentsEvents extends oxSuperCfg
{

    /**
     * On module activation callback
     */
    public static function onActivate()
    {
        if (!self::_isInstalled()) {
            self::_createPaymentMethod();
            self::_assignGermanyToPaymentMethod();
            self::_assignAllUserGroupsToPaymentMethod();
            self::_assignAllDeliveryMethodsToPaymentMethod();
            self::_createPaymentSpecificTables();
            self::_createCmsContent();
        }
        self::_togglePaymentMethod(true);
        self::_enablePaymentMethod();
        self::_clearTmp();
        self::_updateViews();
    }

    /**
     * On module deactivation callback
     */
    public static function onDeactivate()
    {
        if (self::_isInstalled()) {
            self::_togglePaymentMethod(false);
        }

        self::_disablePaymentMethod();
        self::_clearTmp();
        self::_updateViews();
    }

    /**
     * Create a new PayPal Installments Payment Method
     */
    protected static function _createPaymentMethod()
    {
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
        $oPayment = oxNew('oxPayment');
        if (!$oPayment->load($sPaymentMethodOxid)) {
            $oPayment->setId($sPaymentMethodOxid);
            $oPayment->oxpayments__oxactive = new oxField(1);
            $oPayment->oxpayments__oxaddsum = new oxField(0);
            $oPayment->oxpayments__oxaddsumtype = new oxField('abs');
            $oPayment->oxpayments__oxfromboni = new oxField(0);
            $oPayment->oxpayments__oxfromamount = new oxField(paypInstallmentsConfiguration::getPaymentMethodMinAmount());
            $oPayment->oxpayments__oxtoamount = new oxField(paypInstallmentsConfiguration::getPaymentMethodMaxAmount());
            $oPayment->oxpayments__oxsort = new oxField(-999); // Make the method topmost

            /** @var oxLang $oLanguage */
            $oLanguage = oxRegistry::getLang();
            $aLanguages = $oLanguage->getLanguageIds();

            foreach ($aLanguages as $iLanguageId => $sAbbreviation) {
                $sDesc = $oLanguage->translateString("PAYP_INSTALLMENTS_MODULE_DESC", $iLanguageId);
                $sLongDesc = $oLanguage->translateString("PAYP_INSTALLMENTS_MODULE_LONGDESC", $iLanguageId);

                $oPayment->setLanguage($iLanguageId);
                $oPayment->oxpayments__oxdesc = new oxField($sDesc);
                $oPayment->oxpayments__oxlongdesc = new oxField($sLongDesc);
                $oPayment->save();
            }
        }
    }

    /**
     * Create CMS content
     *
     * @return object
     */
    protected static function _createCmsContent()
    {
        $sShopId = oxRegistry::getConfig()->getShopId();

        $sQuery = "REPLACE INTO `oxcontents` VALUES ('98713214ae673be3fb6b741f80b03399', 'paypinstallmentssidebar', '$sShopId', 1, 0, 1, 1, '', 'Ratenzahlung Powered by PayPal', 'Zahlen Sie bei uns bequem und einfach mit', 'Installments Powered by PayPal', 'Simple and easy payment with', 0, '', '', 0, '', '', '', '', '', NOW())";
        $oDb = oxDb::getInstance()->getDb();
        $blResult = $oDb->execute($sQuery);

        return $blResult;
    }

    /**
     * Create tables needed by the module
     */
    protected static function _createPaymentSpecificTables()
    {
        $sSql = static::_getFileContents('install.sql');
        if ($sSql) {
            $oDb = oxDb::getInstance()->getDb();
            $sUncommentedSqlQuery = self::_uncommentSqlQueryString($sSql);
            $aSQLCommands = explode(';', $sUncommentedSqlQuery);
            foreach ($aSQLCommands as $sQuery) {
                if ($sQuery) {
                    try {
                        $oDb->Execute($sQuery);
                    } catch (Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * Create a new Assignment for Germany to the PayPal Payment Method
     */
    protected static function _assignGermanyToPaymentMethod()
    {
        $oCountryPaymentAssignment = oxNew('oxbase');
        $oCountryPaymentAssignment->init('oxobject2payment');

        $oDb = oxDb::getInstance()->getDb();
        $sGermanyCountryId = $oDb->getOne("select oxid from oxcountry where oxisoalpha2 = " . $oDb->quote('DE'));

        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
        $oCountryPaymentAssignment->oxobject2payment__oxpaymentid = new oxField($sPaymentMethodOxid);
        $oCountryPaymentAssignment->oxobject2payment__oxobjectid = new oxField($sGermanyCountryId);
        $oCountryPaymentAssignment->oxobject2payment__oxtype = new oxField("oxcountry");
        $blResult = $oCountryPaymentAssignment->save();

        return $blResult;
    }

    /**
     * For each group, add a Payment to Group Assignment to the database
     */
    protected static function _assignAllUserGroupsToPaymentMethod()
    {
        $oDb = oxDb::getInstance()->getDb();

        $aGroupIds = $oDb->getAll("SELECT OXID FROM oxgroups");
        foreach ($aGroupIds as $aGroupIdRow) {
            $sGroupId = $aGroupIdRow[0];
            $oGroupPaymentAssignment = oxNew("oxobject2group");
            $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
            $oGroupPaymentAssignment->oxobject2group__oxobjectid = new oxField($sPaymentMethodOxid);
            $oGroupPaymentAssignment->oxobject2group__oxgroupsid = new oxField($sGroupId);
            $oGroupPaymentAssignment->save();
        }
    }

    /**
     * For each DeliverySet, create a Payment to DeliverySet Assignment
     */
    protected static function _assignAllDeliveryMethodsToPaymentMethod()
    {
        $oDb = oxDb::getInstance()->getDb();

        $aDeliverySetIds = $oDb->getAll("SELECT OXID FROM oxdeliveryset");
        foreach ($aDeliverySetIds as $aDeliverySetRow) {
            $sDeliverySetId = $aDeliverySetRow[0];

            $oShippingPaymentAssignment = oxNew('oxbase');
            $oShippingPaymentAssignment->init('oxobject2payment');
            $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
            $oShippingPaymentAssignment->oxobject2payment__oxpaymentid = new oxField($sPaymentMethodOxid);
            $oShippingPaymentAssignment->oxobject2payment__oxobjectid = new oxField($sDeliverySetId);
            $oShippingPaymentAssignment->oxobject2payment__oxtype = new oxField("oxdelset");
            $oShippingPaymentAssignment->save();
        }
    }

    /**
     * Delete all entries from oxobject2payment and oxobject2group related to Payment Method
     */
    protected static function _removePaymentAssignments()
    {
        $oDb = oxDb::getInstance()->getDb();
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();

        $sRemoveGroupAssignmentsSQL = "DELETE FROM oxobject2group WHERE oxobjectid = '" . $sPaymentMethodOxid . "'";
        $oDb->Execute($sRemoveGroupAssignmentsSQL);
    }

    /**
     * Activate/Disable PayPal Installments payment method in current shop.
     *
     * @param bool $blIsActive
     */
    protected static function _togglePaymentMethod($blIsActive)
    {
        /** @var oxPayment $oPayment */
        $oPayment = oxNew('oxPayment');

        if ($oPayment->load(paypInstallmentsConfiguration::getPaymentId())) {
            oxRegistry::getConfig()->setConfigParam('paypInstallmentsActive', (int) $blIsActive);
        }
    }

    /**
     * Disables PayPal Installments payment method
     */
    public static function _disablePaymentMethod()
    {
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
        $payment = oxNew('oxPayment');
        $payment->load($sPaymentMethodOxid);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(0);
        $payment->save();
    }

    /**
     * Activates PayPal Installments payment method
     */
    public static function _enablePaymentMethod()
    {
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
        $payment = oxNew('oxPayment');
        $payment->load($sPaymentMethodOxid);
        $payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $payment->save();
    }



    /**
     * Check if PayPal is used for sub-shops.
     *
     * @return bool
     */
    public static function isPayPalActiveOnSubShops()
    {
        $active = false;
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $extensionChecker = oxNew(\OxidEsales\PayPalModule\Core\ExtensionChecker::class);
        $shops = $config->getShopIds();
        $activeShopId = $config->getShopId();

        foreach ($shops as $shopId) {
            if ($shopId != $activeShopId) {
                $extensionChecker->setShopId($shopId);
                $extensionChecker->setExtensionId('oepaypal');
                if ($extensionChecker->isActive()) {
                    $active = true;
                    break;
                }
            }
        }

        return $active;
    }

    /**
     * Update database views.
     */
    protected static function _updateViews()
    {
        /** @var oxDbMetaDataHandler $oDbHandler */
        $oDbHandler = oxNew('oxDbMetaDataHandler');
        $oDbHandler->updateViews();
    }

    /**
     * Clean cache folder content.
     *
     * @param string $sClearFolderPath Sub-folder path to delete from. Should be a full, valid path inside temp folder.
     *
     * @return boolean
     */
    protected static function _clearTmp($sClearFolderPath = '')
    {
        $sFolderPath = self::_getFolderToClear($sClearFolderPath);
        $hDirHandler = opendir($sFolderPath);

        if (!empty($hDirHandler)) {
            while (false !== ($sFileName = readdir($hDirHandler))) {
                $sFilePath = $sFolderPath . DIRECTORY_SEPARATOR . $sFileName;
                self::_deleteFile($sFileName, $sFilePath);
            }

            closedir($hDirHandler);
        }

        return true;
    }

    /**
     * Check if module is already installed.
     *
     * @return bool
     */
    protected static function _isInstalled()
    {
        /** @var oxPayment $oPayment */
        $oPayment = oxNew('oxPayment');

        $sPaymentMethodId = paypInstallmentsConfiguration::getPaymentId();

        return (bool) $oPayment->load($sPaymentMethodId);
    }

    /**
     * Check if resource could be deleted and
     * delete it if it's a file or call recursive folder deletion if it is a directory.
     *
     * @param string $sFileName
     * @param string $sFilePath
     */
    protected static function _deleteFile($sFileName, $sFilePath)
    {
        if (!in_array($sFileName, array('.', '..', '.htaccess'))) {
            if (is_file($sFilePath)) {
                @unlink($sFilePath);
            } else {
                self::_clearTmp($sFilePath);
            }
        }
    }

    /**
     * Check if provided path is inside eShop tpm/ folder or use the tmp/ folder path.
     *
     * @param string $sClearFolderPath
     *
     * @return string
     */
    protected static function _getFolderToClear($sClearFolderPath = '')
    {
        $sTempFolderPath = oxRegistry::getConfig()->getConfigParam('sCompileDir');

        if (!empty($sClearFolderPath) and (strpos($sClearFolderPath, $sTempFolderPath) !== false)) {
            $sFolderPath = $sClearFolderPath;
        } else {
            $sFolderPath = $sTempFolderPath;
        }

        return $sFolderPath;
    }

    /**
     * Remove comments from a SQL query string
     *
     * @param $sSqlQuery
     *
     * @return string
     */
    protected static function _uncommentSqlQueryString($sSqlQuery)
    {
        $sqlComments = '@(([\'"`]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms';
        $uncommentedSQL = trim(preg_replace($sqlComments, '$1', $sSqlQuery));

        return $uncommentedSQL;
    }

    /**
     * Get contents of a file relative to this modules docs directory as a string.
     * Returns false if the file path does not exist
     *
     * @param string $sFileName relative to the modules docs directory
     *
     * @return string|false
     */
    protected static function _getFileContents($sFileName)
    {
        $oModule = oxNew('oxModule');
        $sModuleFullPath = $oModule->getModuleFullPath('paypinstallments');
        $sFilePath = $sModuleFullPath . '/docs/' . $sFileName;
        $sFileContents = file_get_contents($sFilePath);

        return $sFileContents;
    }

}
