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
 * Class paypInstallmentsSetExpressCheckoutValidator
 *
 * Validate all data, that has to do with the SetExpressCheckout API call request and response
 *
 * @todo reuse setters/getters for parser, request, response
 */
class paypInstallmentsSetExpressCheckoutValidator extends paypInstallmentsSoapValidator
{

    /** @var paypInstallmentsSetExpressCheckoutParser */
    protected $_oParser = null;

    /**
     * Property setter
     *
     * @param $oRequest \PayPal\PayPalAPI\SetExpressCheckoutReq
     */
    public function setRequest(\PayPal\PayPalAPI\SetExpressCheckoutReq $oRequest)
    {
        $this->_oParser->setRequest($oRequest);
    }

    /**
     * property setter
     *
     * @param PayPal\PayPalAPI\SetExpressCheckoutResponseType $oResponse
     */
    public function setResponse(PayPal\PayPalAPI\SetExpressCheckoutResponseType $oResponse)
    {
        $this->_oResponse = $oResponse;
        $this->_oParser->setResponse($oResponse);
    }

    /**
     * Validate the response of the SetExpressCheckout API call by calling all required validation methods.
     * Throws an instance of paypInstallmentsException if validations fails.
     *
     * @throws paypInstallmentsException
     */
    public function validateResponse()
    {
        $this->getLogger()->info(
            "SetExpressCheckoutValidator validateResponse",
            array("response" => $this->_oResponse)
        );
        parent::validateResponse();
        $this->_validateToken();
    }

    /**
     * Validates that the token.
     * It must not be empty.
     *
     * @throws paypinstallmentsmalformedresponseexception
     */
    protected function _validateToken()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateToken", array());

        $sToken = $this->_oParser->getToken();
        if (empty($sToken)) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateToken", array("error" => "Empty Authentication Token"));
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('EMPTY_' . strtoupper('token'));
            $this->_throwMalformedResponseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_string($sToken)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('NO_STRING_' . strtoupper('token'));
            $this->_throwMalformedResponseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * Request Part
     */

    /**
     * Validate the request of the SetExpressCheckout API call by calling all required validation methods.
     * Throws an instance of paypInstallmentsSetExpressCheckoutRequestValidationException if validation fails.
     */
    public function validateRequest()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator validateRequest", array("request" => $this->_oRequest));
        parent::validateRequest();
        $this->_validateOrderTotalValue();
        $this->_validateOrderCurrency();
        $this->_validateShippingCountry();
        $this->_validateFundingSource();
        $this->_validateLandingPage();
    }

    /**
     * Validates the value of the order total.
     * This is set from the users basket and must be between a minimum and a maximum amount.
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _validateOrderTotalValue()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateOrderTotalValue", array());
        $fMinQualifyingOrderTotal = paypInstallmentsConfiguration::getPaymentMethodMinAmount();
        $fMaxQualifyingOrderTotal = paypInstallmentsConfiguration::getPaymentMethodMaxAmount();
        $fActualOrderTotal = (float) $this->_oParser->getOrderTotalValue();

        if ($fActualOrderTotal < $fMinQualifyingOrderTotal) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateOrderTotalValue", array("error" => "Order amount below minimum"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MINIMAL_QUALIFYING_ORDER_TOTAL_NOT_MET');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($fActualOrderTotal > $fMaxQualifyingOrderTotal) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateOrderTotalValue", array("error" => "Order amount above maximum"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MAXIMAL_QUALIFYING_ORDER_TOTAL_EXCEEDED');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validates the order currency.
     * This is set from the users basket and only certain currencies can apply for installments.
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _validateOrderCurrency()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateOrderCurrency", array());
        $aExpectedCurrencies = array($this->getConfiguration()->getRequiredOrderTotalCurrency());
        $sActualCurrency = strtoupper($this->_oParser->getOrderTotalCurrency());
        if (!in_array($sActualCurrency, $aExpectedCurrencies)) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateOrderCurrency", array("error" => "Order Currency not allowed"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_ORDER_CURRENCY');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validates the shipping address.
     * This is set from the users basket and only customers from certain countries can apply for installments.
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _validateShippingCountry()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateShippingCountry", array());
        $aExpectedShippingCountries = array($this->getConfiguration()->getRequiredShippingCountry());
        $sActualShippingCountry = strtoupper($this->_oParser->getShippingCountry());
        if (!in_array($sActualShippingCountry, $aExpectedShippingCountries)) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateShippingCountry", array("error" => "Shipping country not allowed"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_SHIPPING_COUNTRY');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validates the funding source.
     * This hardcoded and must match a certain value.
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _validateFundingSource()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateFundingSource", array());
        $sExpectedFundingSource = 'FINANCE'; // TODO Refactor this to configuration
        $sActualFundingSource = strtoupper($this->_oParser->getFundingSource());
        if ($sExpectedFundingSource != $sActualFundingSource) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateFundingSource", array("error" => "Funding Source is not 'Finance'"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_FUNDING_SOURCE');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validates the landing page.
     * This is hardcoded and must match a certain value.
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _validateLandingPage()
    {
        $this->getLogger()->info("SetExpressCheckoutValidator _validateLandingPage", array());
        $sExpectedLandingPage = 'BILLING'; // Todo Refactor this and put it into configuration
        $sActualLandingPage = strtoupper($this->_oParser->getLandingPage());
        if ($sExpectedLandingPage != $sActualLandingPage) {
            $this->getLogger()->error("SetExpressCheckoutValidator _validateLandingPage", array("error" => "Landing Page is not 'Billing'"));
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_LANDING_PAGE');
            $this->_throwSetExpressCheckoutRequestValidationException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Throws an exception if the validation on the API call response fails
     *
     * @param $sMessage
     *
     * @throws paypInstallmentsSetExpressCheckoutRequestValidationException
     */
    protected function _throwSetExpressCheckoutRequestValidationException($sMessage)
    {
        /** @var paypInstallmentsSetExpressCheckoutRequestValidationException $oEx */
        $oEx = oxNew('paypInstallmentsSetExpressCheckoutRequestValidationException');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }
}
