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
 * Class paypInstallmentsGetExpressCheckoutDetailsValidator
 *
 * @desc Validator for express checkout.
 *
 * @todo use getters for dataProvider, response, request, parser
 */
class paypInstallmentsGetExpressCheckoutDetailsValidator extends paypInstallmentsSoapValidator
{

    /**
     * @var paypInstallmentsCheckoutDataProvider
     */
    protected $_oDataProvider;
    
    /**
     * Property setter.
     *
     * @param $oDataProvider paypInstallmentsCheckoutDataProvider
     */
    public function setDataProvider($oDataProvider)
    {
        $this->_oDataProvider = $oDataProvider;
    }
    
    /**
     * Setter for Request Object
     *
     * @param \PayPal\PayPalAPI\GetExpressCheckoutDetailsReq $oRequest
     */
    public function setRequest(\PayPal\PayPalAPI\GetExpressCheckoutDetailsReq $oRequest)
    {
        $this->_oRequest = $oRequest;
    }


    /**
     * setter for response
     *
     * @param PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType $oResponse
     */
    public function setResponse(PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType $oResponse)
    {
        $this->_oResponse = $oResponse;
        $this->_oParser->setResponse($oResponse);
    }

    /**
     * validate the set response
     *
     * @throws paypInstallmentsException
     */
    public function validateResponse()
    {
        $this->getLogger()->info("GetExpressCheckoutValidator validateResponse",
            array("response" => $this->_oResponse));

        parent::validateResponse();
        $this->_validatePayerId();
        $this->_validatePaymentInfo();
    }

    /**
     * make sure we get a PayerID in return from PayPal
     */
    protected function _validatePayerId()
    {
        $this->getLogger()->info("GetExpressCheckoutValidator _validatePayerId", array());
        $sPayerId = $this->_oParser->getPayerId();
        if(empty($sPayerId))
        {
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePayerId",
                array("error" => "PayerId is empty", "response" => $this->_oResponse));
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('EMPTY_' . strtoupper('PayerId'));
            $this->_throwMalformedResponseException($sMessage);
            // @codeCoverageIgnoreStart
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Make sure the response contains a valid PaymentInfo Object
     *
     * @throws paypInstallmentsGetExpressCheckoutDetailsValidationException
     */
    protected function _validatePaymentInfo()
    {
        $this->getLogger()->info("GetExpressCheckoutValidator _validatePaymentInfo", array());
        $oPaymentInfo = $this->_oParser->getPaymentInfo();

        if(! $oPaymentInfo->FinancingFeeAmount instanceof \PayPal\CoreComponentTypes\BasicAmountType)
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_FINANCING_FEE_WRONG_TYPE');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "The FinancingFeeAmount is not an object of BasicAmount Type",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if(floatval($oPaymentInfo->FinancingFeeAmount->value) < 0.0 )
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_NEGATIVE_FINANCING_FEE');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "The FinancingFeeAmount is negative",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if(empty($oPaymentInfo->FinancingFeeAmount->currencyID))
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_PAYMENT_FEE_MISSING_CURRENCY');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "The FinancingFeeAmount provides no currency",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if(! $oPaymentInfo->FinancingMonthlyPayment instanceof \PayPal\CoreComponentTypes\BasicAmountType )
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_MONTHLY_PAYMENT_WRONG_TYPE');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingMonthlyPayment is not an instance of BasicAmountType",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if(floatval($oPaymentInfo->FinancingMonthlyPayment->value) < 0.0 )
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_NEGATIVE_MONTHLY_PAYMENT');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingMonthlyPayment less than zero",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if(empty($oPaymentInfo->FinancingMonthlyPayment->currencyID))
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_MONTHLY_PAYMENT_MISSING_CURRENCY');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingMonthlyPayment provides no currency",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if(! $oPaymentInfo->FinancingTotalCost instanceof \PayPal\CoreComponentTypes\BasicAmountType )
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_TOTAL_COST_WRONG_TYPE');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingTotalCost is not an instance of BasicAmountType",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if(empty($oPaymentInfo->FinancingTotalCost->currencyID))
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_TOTAL_COST_MISSING_CURRENCY');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingTotalCost provides no currency",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        else if($this->_oDataProvider->getOrderTotal() > $oPaymentInfo->FinancingTotalCost->value)
        // @codeCoverageIgnoreEnd
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_TOTAL_COST_BELOW_CART_VALUE');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "FinancingTotalCost is beneath cart value",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if(intval($oPaymentInfo->FinancingTerm) <= 0)
        {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYMENT_INFO_NEGATIVE_FINANCING_TERM');
            $this->getLogger()->error("GetExpressCheckoutValidator _validatePaymentInfo",
                array(
                    "error" => "Number of Monthly Payments is zero or less",
                    "response" => $this->_oResponse
                )
            );
            $this->_throwValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Throw an exception with the given message
     *
     * @param $sMessage string
     *
     * @throws paypInstallmentsGetExpressCheckoutDetailsValidationException
     */
    protected function _throwValidationException($sMessage)
    {
        $ex = new paypInstallmentsGetExpressCheckoutDetailsValidationException();
        $ex->setMessage($sMessage);
        throw $ex;
    }

}
