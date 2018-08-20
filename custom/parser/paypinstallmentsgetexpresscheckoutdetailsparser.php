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
 * Class paypInstallmentsGetExpressCheckoutDetailsParser
 *
 * @desc Data parser for express checkout.
 */
class paypInstallmentsGetExpressCheckoutDetailsParser extends paypInstallmentsSoapParser
{

    const RESPONSE_TYPE = '\PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType';

    /**
     * extract the payerId from paypals response if possible
     *
     * @return mixed
     */
    public function getPayerId()
    {
        $oClass = $this->getResponse()->GetExpressCheckoutDetailsResponseDetails->PayerInfo;
        $sProperty = 'PayerID';

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

    public function getFinancingFeeAmountValue()
    {
        return $this->_getPaymentInfo('FinancingFeeAmount')->value;
    }

    public function getFinancingFeeAmountCurrency()
    {
        return $this->_getPaymentInfo('FinancingFeeAmount')->currencyID;
    }

    public function getFinancingMonthlyPaymentValue()
    {
        return $this->_getPaymentInfo('FinancingMonthlyPayment')->value;
    }

    public function getFinancingMonthlyPaymentCurrency()
    {
        return $this->_getPaymentInfo('FinancingMonthlyPayment')->currencyID;
    }

    public function getFinancingTotalCostValue()
    {
        return $this->_getPaymentInfo('FinancingTotalCost')->value;
    }

    public function getFinancingTotalCostCurrency()
    {
        return $this->_getPaymentInfo('FinancingTotalCost')->currencyID;
    }

    public function getFinancingTerm()
    {
        return $this->_getPaymentInfo('FinancingTerm');
    }

    protected function _getPaymentInfo($sProperty)
    {
        $oClass = $this->getResponse()
            ->GetExpressCheckoutDetailsResponseDetails
            ->PaymentInfo[0];

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

    /**
     * validate and extract the payment info object out of the given response
     *
     * @return mixed
     */
    public function getPaymentInfo()
    {
        $this->validatePaymentInfo();

        return $this->getResponse()->GetExpressCheckoutDetailsResponseDetails->PaymentInfo[0];
    }

    /**
     * make sure the PaymentInfo Data exists
     *
     * @throws paypInstallmentsGetExpressCheckoutDetailsParseException
     */
    protected function validatePaymentInfo()
    {
        $this->getLogger()->info("GetExpressCheckoutParser validatePaymentInfo", array());
        $oPaymentInfo = $this->getResponse()->GetExpressCheckoutDetailsResponseDetails->PaymentInfo[0];
        if ($oPaymentInfo === null) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "No PaymentInfo was returned by PayPal",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($oPaymentInfo->IsFinancing === null) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO_IS_FINANCING');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "Missing IsFinancing Property in PaymentInfo",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        } // @codeCoverageIgnoreEnd
        else if ($oPaymentInfo->IsFinancing !== "true" && $oPaymentInfo->IsFinancing !== "false") {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('PAYMENT_INFO_IS_FINANCING_NOT_BOOL');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "The IsFinancing Property in PaymentInfo is neither true nor false",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (empty($oPaymentInfo->FinancingFeeAmount)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO_FINANCING_FEE');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "The FinancingFeeAmount Property in PaymentInfo is missing",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (empty($oPaymentInfo->FinancingMonthlyPayment)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO_MONTHLY_PAYMENT');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "The FinancingMonthlyPayment Property in PaymentInfo is missing",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (empty($oPaymentInfo->FinancingTerm)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO_FINANCING_TERM');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "The FinancingTerm Property in PaymentInfo is missing",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (empty($oPaymentInfo->FinancingTotalCost)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO_TOTAL_COST');
            $this->getLogger()->error(
                "GetExpressCheckoutParser validatePaymentInfo",
                array(
                    "error"    => "The FinancingTotalCost Property in PaymentInfo is missing",
                    "response" => $this->getResponse()
                )
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Helper function to return the value of a class property.
     * Throws an exception if the class is not a class or the property does not exist.
     *
     * @param object $oClass    Instance of a class to get the property value from
     * @param string $sProperty Property name
     * @param string $sMessage  Exception Error message
     *
     * @return mixed
     * @throws paypinstallmentsmalformedrequestexception
     */
    protected function _getValueByClassAndProperty($oClass, $sProperty, $sMessage)
    {
        $this->getLogger()->info(
            "GetExpressCheckoutParser _getValueByClassAndProperty",
            array("class" => $oClass, "property" => $sProperty, "message" => $sMessage)
        );
        if (!is_object($oClass)) {
            $this->_throwParseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!property_exists($oClass, $sProperty) || !isset($oClass->$sProperty)) {
            $this->_throwParseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }

        // @codeCoverageIgnoreEnd


        return $oClass->$sProperty;
    }

    /**
     * throw a validation exception using the given message
     *
     * @param $sMessage
     *
     * @throws paypInstallmentsGetExpressCheckoutDetailsParseException
     */
    protected function _throwParseException($sMessage)
    {
        $ex = new paypInstallmentsGetExpressCheckoutDetailsParseException();
        $ex->setMessage($sMessage);
        throw $ex;
    }
}
