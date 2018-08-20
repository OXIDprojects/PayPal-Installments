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
class paypInstallmentsRequirementsValidator extends paypInstallmentsSoapValidator
{

    /** @var string|null */
    protected $_sRequiredCurrency = null;
    /** @var string|null */
    protected $_sRequiredCountryId = null;
    /** @var oxBasket */
    protected $_oBasket = null;
    /** @var  oxUser */
    protected $_oUser = null;

    /**
     * Property setter
     *
     * @codeCoverageIgnore
     *
     * @param oxUser $oUser
     */
    public function setUser(oxUser $oUser)
    {
        $this->_oUser = $oUser;
    }

    /**
     * Property setter
     *
     * @codeCoverageIgnore
     *
     * @param oxBasket $oBasket
     */
    public function setBasket(oxBasket $oBasket)
    {
        $this->_oBasket = $oBasket;
    }

    public function validateRequirements()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        if (is_null($this->_oUser) || is_null($this->_oBasket)) {
            throw new InvalidArgumentException();
        }

        $this->_validateBasketCurrency();
        $this->_validateMinOrderAmount();
        $this->_validateMaxOrderAmount();
        $this->_validateShippingCountry();
        $this->_validateBillingCountry();

        $this->getLogger()->info("paypInstallmentsRequirementsValidator validateRequirements Result: all requirements met", array());
    }

    /**
     * @throws paypInstallmentsInvalidCurrencyException
     */
    protected function _validateBasketCurrency()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $oBasketCurrency = $this->_oBasket->getBasketCurrency();
        if ($oBasketCurrency->name != $this->_getRequiredCurrency()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_CURRENCY');
            $this->_throwRequirementsValidatiorException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' Result: requirement met', array());
    }

    /**
     * @throws paypInstallmentsInvalidShippingCountryException
     */
    protected function _validateShippingCountry()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $sAddressId = oxRegistry::getSession()->getVariable('deladrid');

        if ($sAddressId) {
            //there is a shipping address set
            $oAddress = oxNew('oxAddress');
            if ($oAddress->load($sAddressId)) {
                $sActualShippingCountyId = $oAddress->oxaddress__oxcountryid->value;
                //check if delivery address is located in germany
                if ($this->_getRequiredCountryId() != $sActualShippingCountyId) {
                    $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_SHIPPING_COUNTRY');
                    $this->_throwRequirementsValidatiorException($sMessage);
                    // @codeCoverageIgnoreStart
                }
                // @codeCoverageIgnoreEnd
            }
        }

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' Result: requirement met', array());
    }

    /**
     * @throws paypInstallmentsRequirementsValidatorException
     */
    protected function _validateBillingCountry()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $sActualBillingCountyId = $this->_oUser->oxuser__oxcountryid->value;
        if ($this->_getRequiredCountryId() != $sActualBillingCountyId) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_BILLING_COUNTRY');
            $this->_throwRequirementsValidatiorException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' Result: requirement met', array());
    }

    /**
     * @throws paypInstallmentsRequirementsValidatorException
     */
    protected function _validateMinOrderAmount()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $fActualOrderTotal = $this->_isPriceViewModeNetto() ? $this->_oBasket->getNettoSum() : $this->_oBasket->getBruttoSum();
        $fMinQualifyingOrderTotal = paypInstallmentsConfiguration::getPaymentMethodMinAmount();

        if ($fActualOrderTotal < $fMinQualifyingOrderTotal) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MINIMAL_QUALIFYING_ORDER_TOTAL_NOT_MET');
            $this->_throwRequirementsValidatiorException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' Result: requirement met', array());
    }

    /**
     * @throws paypInstallmentsRequirementsValidatorException
     */
    protected function _validateMaxOrderAmount()
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $fActualOrderTotal = $this->_isPriceViewModeNetto() ? $this->_oBasket->getNettoSum() : $this->_oBasket->getBruttoSum();
        $fMaxQualifyingOrderTotal = paypInstallmentsConfiguration::getPaymentMethodMaxAmount();

        if ($fActualOrderTotal > $fMaxQualifyingOrderTotal) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MAXIMAL_QUALIFYING_ORDER_TOTAL_EXCEEDED');
            $this->_throwRequirementsValidatiorException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' Result: requirement met', array());
    }

    /**
     * @return array|null|string
     */
    protected function _getRequiredCurrency()
    {
        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__, array());

        if (is_null($this->_sRequiredCurrency)) {
            $this->_sRequiredCurrency = $this->getConfiguration()->getRequiredOrderTotalCurrency();
        }

        return $this->_sRequiredCurrency;
    }

    /**
     * @return null|string
     */
    protected function _getRequiredCountryId()
    {
        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__, array());

        if (is_null($this->_sRequiredCountryId)) {
            $sRequiredCountyCode = paypInstallmentsConfiguration::getRequiredShippingCountry();
            $this->_sRequiredCountryId = $this->_getCountryIdByCountryCode($sRequiredCountyCode);
        }

        return $this->_sRequiredCountryId;
    }

    /**
     * Get OXID of a given country by its IsoAlpha2Code.
     *
     * @param $sCountryCode
     *
     * @return string
     * @throws Exception
     */
    protected function _getCountryIdByCountryCode($sCountryCode)
    {
        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__, array('CountryCode' => $sCountryCode));

        /** @var oxCountry $oCountry */
        $oCountry = oxNew('oxCountry');

        $sCountryId = $oCountry->getIdByCode($sCountryCode);
        if (!$sCountryId) {
            $oException = new InvalidArgumentException('Country ID not found for country code ' . $sCountryCode);
            throw $oException;
        }

        return $sCountryId;
    }

    /**
     * Checks and return true if price view mode is netto
     *
     * @return bool
     */
    protected function _isPriceViewModeNetto()
    {
        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__, array());

        $blResult = (bool) oxRegistry::getConfig()->getConfigParam('blShowNetPrice');
        $oUser = $this->_oBasket->getBasketUser();
        if ($oUser) {
            $blResult = $oUser->isPriceViewModeNetto();
        }

        $this->getLogger()->debug(__CLASS__ . ' ' . __FUNCTION__ . ' ' . $blResult, array());

        return $blResult;
    }

    /**
     * Throws an exception if the requirements for PayPay Installments are not met
     *
     * @param $sMessage
     *
     * @throws paypInstallmentsRequirementsValidatorException
     */
    protected function _throwRequirementsValidatiorException($sMessage)
    {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__ . ' Requirement for PayPal installments not met: ' . $sMessage);

        $oEx = oxNew('paypInstallmentsRequirementsValidatorException');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }
}