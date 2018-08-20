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
 * Class paypInstallmentsSetExpressCheckoutParser
 *
 * Parses the response of the SetExpressCheckout request
 */
class paypInstallmentsSetExpressCheckoutParser extends paypInstallmentsSoapParser
{

    const RESPONSE_TYPE = '\PayPal\EBLBaseComponents\AbstractResponseType';

    /** @var PayPal\PayPalAPI\SetExpressCheckoutReq */
    protected $_oRequest = null;

    /**
     * Property setter
     *
     * @param \PayPal\PayPalAPI\SetExpressCheckoutReq $oRequest
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }


    /**
     * Response Parsing part
     */

    /**
     * Returns the Token from the response
     *
     * @throws paypInstallmentsMalformedResponseException
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_getValueFromResponse('Token');
    }

    /**
     * Request Parsing part
     */

    /**
     * Returns the order total of the request.
     *
     * @return float
     */
    public function getOrderTotalValue()
    {
        $oClass = $this->_oRequest
                      ->SetExpressCheckoutRequest
                      ->SetExpressCheckoutRequestDetails
                      ->PaymentDetails[0]->OrderTotal;
        $sProperty = 'value';

        $fValue = (float) $this->_getValueByClassAndProperty(
            $oClass,
            $sProperty,
            paypInstallmentsConfiguration::getParseErrorMessage('MISSING_ORDER_TOTAL_' . strtoupper($sProperty))
        );

        return $fValue;
    }

    /**
     * Returns the currency of the request.
     *
     * @return mixed
     */
    public function getOrderTotalCurrency()
    {
        $oClass = $this->_oRequest
                      ->SetExpressCheckoutRequest
                      ->SetExpressCheckoutRequestDetails
                      ->PaymentDetails[0]->OrderTotal;
        $sProperty = 'currencyID';

        $sValue = $this->_getValueByClassAndProperty(
            $oClass,
            $sProperty,
            paypInstallmentsConfiguration::getParseErrorMessage('MISSING_ORDER_TOTAL_' . strtoupper($sProperty))
        );

        return $sValue;
    }

    /**
     * Returns the shipping country of the request.
     *
     * @return mixed
     */
    public function getShippingCountry()
    {
        $oClass = $this->_oRequest
                      ->SetExpressCheckoutRequest
                      ->SetExpressCheckoutRequestDetails
                      ->PaymentDetails[0]->ShipToAddress;
        $sProperty = 'Country';

        $sValue = $this->_getValueByClassAndProperty(
            $oClass,
            $sProperty,
            paypInstallmentsConfiguration::getParseErrorMessage('MISSING_ADDRESS_' . strtoupper($sProperty))
        );

        return $sValue;
    }

    /**
     * Returns the funding source of the request.
     *
     * @return mixed
     */
    public function getFundingSource()
    {
        $oClass = $this->_oRequest
            ->SetExpressCheckoutRequest
            ->SetExpressCheckoutRequestDetails
            ->FundingSourceDetails;
        $sProperty = 'UserSelectedFundingSource';

        $sValue = $this->_getValueByClassAndProperty(
            $oClass,
            $sProperty,
            paypInstallmentsConfiguration::getParseErrorMessage(
                'MISSING_FUNDING_SOURCE_DETAILS' . strtoupper
                (
                    $sProperty
                )
            )
        );

        return $sValue;
    }

    /**
     * Returns the landing page of the request.
     *
     * @return mixed
     */
    public function getLandingPage()
    {
        $oClass = $this->_oRequest
            ->SetExpressCheckoutRequest
            ->SetExpressCheckoutRequestDetails;
        $sProperty = 'LandingPage';

        $sValue = $this->_getValueByClassAndProperty(
            $oClass,
            $sProperty,
            paypInstallmentsConfiguration::getParseErrorMessage(
                'MISSING_' . strtoupper
                (
                    $sProperty
                )
            )
        );

        return $sValue;
    }
}
