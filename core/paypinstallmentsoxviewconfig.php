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

/**
 * Class paypInstallmentsOxViewConfig
 *
 * @desc Helper class to load assets from module dir.
 */
class paypInstallmentsOxViewConfig extends paypInstallmentsOxViewConfig_parent
{

    /**
     * Get the module resource URL.
     *
     * @param string $sResourceRelativePath Media resource path inside the module `src` folder.
     *
     * @return string
     */
    public function getPayPalInstallmentsUrl($sResourceRelativePath = '')
    {
        /** @var paypInstallmentsOxViewConfig|oxViewConfig $this */
        $oModule = $this->getModuleService();
        $oModule->load('paypinstallments');

        return $this->getModuleUrl($oModule->getModulePath(), 'out/src/' . $sResourceRelativePath);
    }

    /**
     * @return oxModule
     */
    public function getModuleService()
    {
        return new oxModule();
    }

    /**
     * show advertise block on start page?
     *
     * @return bool
     */
    public function isShowGenericAdvert()
    {
        /** @var oxConfig $oConfig */
        $oConfig = oxRegistry::getConfig();
        $sClass = $oConfig->getRequestParameter('cl');
        switch ($sClass) {
            case null:
            case 'start':
                $blShow = $oConfig->getConfigParam('paypInstallmentsGenAdvertHome');
                break;
            case 'details':
                $blShow = $oConfig->getConfigParam('paypInstallmentsGenAdvertDetail');
                break;
            case 'alist':
                $blShow = $oConfig->getConfigParam('paypInstallmentsGenAdvertCat');
                break;
            default:
                $blShow = false;
                break;
        }
        return $blShow;
    }

    /**
     * is option "with calculated value" configured
     */
    public function isWithCalculatedValue()
    {
        /** @var oxConfig $oConfig */
        $oConfig = oxRegistry::getConfig();
        $blWithCalculatedValue = $oConfig->getConfigParam('paypInstallmentsWithCalcValue');
        return $blWithCalculatedValue;
    }

    /**
     * get dealer name
     * @param oxShop $oxShop default null
     */
    public function getInstallmentsCreditor(oxShop $oxShop = null)
    {
        /** @var oxConfig $oConfig */
        $oConfig = oxRegistry::getConfig();
        $oShop = $oxShop ? $oxShop : $oConfig->getActiveShop();
        $sCreditor = $oShop->oxshops__oxcompany->value;
        if ($oShop->oxshops__oxstreet->value) {
            $sCreditor .= ', ' . $oShop->oxshops__oxstreet->value;
        }
        $sCreditor .= ', ' . $oShop->oxshops__oxzip->value . ' ' . $oShop->oxshops__oxcity->value;
        return $sCreditor;
    }
}
