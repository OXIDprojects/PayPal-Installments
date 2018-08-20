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
class paypInstallmentsOxPayment extends paypInstallmentsOxPayment_parent
{
    /**
     * @inheritdoc
     *
     * Additional PayPal Installments requirements are validated.
     *
     * @param $aDynValue
     * @param $sShopId
     * @param $oUser
     * @param $dBasketPrice
     * @param $sShipSetId
     *
     * @return bool
     * @throws Exception
     */
    public function isValidPayment($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        $blIsValidPayment = $this->_paypInstallments_callParentIsValidPaymentMethod(
            $aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId
        );
        if ($blIsValidPayment && $this->oxpayments__oxid->value == paypInstallmentsConfiguration::getPaymentId()) {
            $blIsValidPayment = $this->_paypInstallments_isValidPayPalInstallmentsPayment();
        }

        return $blIsValidPayment;
    }

    /**
     * Call IsValidPayment.
     * Needed for testing.
     *
     * @param $aDynValue
     * @param $sShopId
     * @param $oUser
     * @param $dBasketPrice
     * @param $sShipSetId
     *
     * @return bool
     */
    protected function _paypInstallments_callParentIsValidPaymentMethod($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        return parent::isValidPayment($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId);
    }

    /**
     * Validate PayPal Installments prerequisites
     *
     * @return bool
     */
    protected function _paypInstallments_isValidPayPalInstallmentsPayment()
    {
        $blIsValidPayment = true;
        try {
            //getting current basket
            /** @var oxBasket $oBasket */
            $oBasket = $this->_paypInstallments_getBasketFromSession();

            //getting user
            /** @var oxUser $oUser */
            $oUser = $oBasket->getBasketUser();

            $oPayPalInstallmentsRequirementsValidator = $this->_paypInstallments_getRequirementsValidator();
            $oPayPalInstallmentsRequirementsValidator->setBasket($oBasket);
            $oPayPalInstallmentsRequirementsValidator->setUser($oUser);
            $oPayPalInstallmentsRequirementsValidator->validateRequirements();
        } catch (paypInstallmentsInvalidBillingCountryException $oEx) {
            $blIsValidPayment = false;
            //set up payment error here
            //for now we just remove paypal installments from payment list
        } catch (paypInstallmentsInvalidShippingCountryException $oEx) {
            $blIsValidPayment = false;
            //set up payment error here
            //for now we just remove paypal installments from payment list
        }

        if ($blIsValidPayment == false) {
            $oModuleConfig = oxNew('paypInstallmentsConfiguration');
            $this->_iPaymentError = $oModuleConfig->getPayPalInstallmentsPaymentError();
        }

        return $blIsValidPayment;
    }

    /**
     * Get basket from session.
     * Needed for mocking.
     *
     * @codeCoverageIgnore
     *
     * @return oxBasket
     */
    protected function _paypInstallments_getBasketFromSession()
    {
        /** @var oxBasket $oBasket */
        $oBasket = $this->getSession()->getBasket();

        return $oBasket;
    }

    /**
     * Return an instance of paypInstallmentsRequirementsValidator.
     * Needed for mocking.
     *
     * @codeCoverageIgnore
     *
     * @return paypInstallmentsRequirementsValidator
     */
    protected function _paypInstallments_getRequirementsValidator()
    {
        $oPayPalInstallmentsRequirementsValidator = oxNew('paypInstallmentsRequirementsValidator');

        return $oPayPalInstallmentsRequirementsValidator;
    }
}
