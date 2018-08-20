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
 * Class paypInstallmentsDoExpressCheckoutPaymentValidator
 *
 * @desc Payment validator on the checkout.
 */
class paypInstallmentsDoExpressCheckoutPaymentValidator extends paypInstallmentsSoapValidator
{

    /**
     * @var oxBasket
     */
    protected $_oBasket;

    /**
     * setter for Request Object
     *
     * @param \PayPal\PayPalAPI\DoExpressCheckoutPaymentReq $oRequest
     */
    public function setRequest(\PayPal\PayPalAPI\DoExpressCheckoutPaymentReq $oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Property setter
     *
     * @param $oResponse PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType
     */
    public function setResponse(PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType $oResponse)
    {
        $this->_oResponse = $oResponse;
        $this->_oParser->setResponse($this->_oResponse);
    }

    /**
     * setter for basket
     *
     * @param oxBasket $oxBasket
     * @codeCoverageIgnore
     */
    public function setBasket(oxBasket $oxBasket)
    {
        $this->_oBasket = $oxBasket;
    }

    /**
     * throw exceptions in case the response is not what we expect
     */
    public function validateResponse()
    {
        $this->getLogger()->info("DoExpressCheckoutPaymentValidator validateResponse",
            array("response" => $this->_oResponse));
        parent::validateResponse();
        $this->validateTransactionId();
    }

    /**
     * make sure we have a TransactionId
     */
    public function validateTransactionId()
    {
        $this->getLogger()->info("DoExpressCheckoutPaymentValidator validateTransactionId", array());
        $sTransactionid = $this->_oParser->getTransactionId();
        if(empty($sTransactionid))
        {
            $ex = new paypInstallmentsDoExpressCheckoutValidationException();
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('EMPTY_TRANSACTION_ID');
            $this->getLogger()->error("DoExpressCheckoutPaymentValidator validateTransactionId",
                array("error" => "No TransactionId was returned by Paypal", "response" => $this->_oResponse));
            $ex->setMessage($sMessage);
            throw $ex;
        }
    }

    /**
     * make sure we are allowed to perform the request
     */
    public function validateRequest()
    {
        $this->getLogger()->info("DoExpressCheckoutPaymentValidator validateRequest",
            array("request" => $this->_oRequest));
        $this->getLogger()->info("DoExpressCheckoutPaymentValidator validateRequest",
            array("status" => "beginning request validation", "request" => $this->_oRequest));
        parent::validateRequest();
        $this->_validateBasketIntegrity();
    }

    /**
     * the sole purpose of this method is to make sure, we did not announce an incorrect order amount to paypal
     *
     * @throws paypInstallmentsBasketIntegrityLostException
     */
    protected function _validateBasketIntegrity()
    {
        $this->getLogger()->info("DoExpressCheckoutPaymentValidator _validateBasketIntegrity", array());
        $sNewFingerprint = $this->_oBasket->paypInstallments_GetBasketItemsFingerprint();
        $sOldFingerprint = $this->_getSession()->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sBasketFingerprintKey);
        if($sNewFingerprint != $sOldFingerprint) {
            $ex = new paypInstallmentsBasketIntegrityLostException();
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('BASKET_INTEGRITY_LOST');
            $this->getLogger()->error("DoExpressCheckoutPaymentValidator _validateBasketIntegrity",
                array("error" => "Basket Contents changed between setCheckout and DoCheckout",
                      "request" => $this->_oRequest));
            $ex->setMessage($sMessage);
            throw $ex;
        }
    }

    /**
     * getter for current session
     *
     * @return oxSession
     * @codeCoverageIgnore
     */
    protected function _getSession()
    {
        return oxRegistry::getSession();
    }
}
