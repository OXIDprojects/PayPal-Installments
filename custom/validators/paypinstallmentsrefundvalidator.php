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
 * Class paypInstallmentsRefundValidator.
 *
 * @todo use setters/getters for requestParams, request, response, parser. Reuse parent methods.
 */
class paypInstallmentsRefundValidator extends paypInstallmentsSoapValidator
{

    /**
     * @var  paypInstallmentsRefundParser The parser object
     */
    protected $_oParser;

    /**
     * @var array Holding the params to be passed do the request object generator
     */
    protected $_aRequestParams;

    protected $_dRefundableAmount;

    /**
     * @var \PayPal\PayPalAPI\RefundTransactionReq  The request to be sent to PayPal
     */
    protected $_oRequest;

    /**
     * Instance of the module configuration
     *
     * @var paypInstallmentsConfiguration
     */
    protected $_oModuleConfiguration;

    /**
     * Property Setter
     *
     * @param $aRequestParams
     */
    public function setRequestParams($aRequestParams)
    {
        $this->_aRequestParams = $aRequestParams;
    }

    public function setRefundableAmount($dRefundableAmount) {
        $this->_dRefundableAmount = $dRefundableAmount;
    }

    public function getRefundableAmount() {
        return $this->_dRefundableAmount;
    }

    public function validateRequest() {
        $this->getLogger()->info(__CLASS__ . ' ' . __FUNCTION__, array());
        $this->_validateRefundAmount();
        $this->_validateRequestParams();
    }

    protected function _validateRefundAmount() {
        $this->getLogger()->info(
            __CLASS__ . ' ' . __FUNCTION__,
            array('RefundableAmount' => $this->getRefundableAmount()));

        if (paypInstallmentsConfiguration::sRefundTypePartial != $this->_aRequestParams['sRefundType']) {
            return;
        }
        if ($this->_aRequestParams['dAmount'] > $this->getRefundableAmount()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_GT_REFUNDABLE');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

    }

    /**
     * Validate the params request to be passed to the request object generator.
     * In this case we do NOT validate the returned request object as we trust in
     * the correct implementation of the object generator.
     *
     * @throws Exception
     */
    protected function _validateRequestParams()
    {
        $this->getLogger()->info(
            "paypInstallmentsRefundValidator validateRequestParams",
            array("request" => $this->_oRequest)
        );
        $this->_validateTransactionId();
        $this->_validateRefundType();
        $this->_validatePartialRefund();
        $this->_validateFullRefund();
    }

    /**
     * Property setter
     *
     * @param $oResponse \PayPal\EBLBaseComponents\AbstractResponseType
     */
    public function setResponse(\PayPal\EBLBaseComponents\AbstractResponseType $oResponse)
    {
        $this->_oResponse = $oResponse;
        $this->_oParser->setResponse($oResponse);
    }

    /**
     * Validate the response.
     */
    public function validateResponse()
    {
        $this->getLogger()->info(
            "paypInstallmentsRefundValidator validateResponse",
            array("response" => $this->_oResponse)
        );
        parent::validateResponse();
        $this->_validateResponseRefundTransactionId();
        $this->_validateResponseRefundedAmountValue();
        $this->_validateResponseRefundedAmountCurrency();
        $this->_validateResponseRefundStatus();
    }

    /**
     * Validate the sTransactionId in the requestParam:
     * - it must not be empty
     *
     * @return mixed
     * @throws Exception
     */
    protected function _validateTransactionId()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateTransactionId", array());
        if (empty($this->_aRequestParams['sTransactionId'])) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_TRANSACTION_ID');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validate the sRefundType in the requestParam:
     * - it must not be empty
     * - it must be a certain string
     *
     * @throws Exception
     */
    protected function _validateRefundType()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateRefundType", array());
        $oModuleConfiguration = $this->_getModuleConfiguration();
        $aAllowedRefundTypes = $oModuleConfiguration->getAllowedRefundTypes();
        if (empty($this->_aRequestParams['sRefundType'])) {
            $sMessage = $oModuleConfiguration->getValidationErrorMessage('MISSING_REFUND_TYPE');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!in_array($this->_aRequestParams['sRefundType'], $aAllowedRefundTypes)) {
            $sMessage = $oModuleConfiguration->getValidationErrorMessage('WRONG_REFUND_TYPE');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This validation will only be applied on partial refunds:
     * - amount must be set and must be a positive float, which must not exceed a certain value.
     * - currency must be set and must contain a certain value.
     *
     * @throws Exception
     */
    protected function _validatePartialRefund()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validatePartialRefund", array());
        if (paypInstallmentsConfiguration::sRefundTypePartial != $this->_aRequestParams['sRefundType']) {
            return;
        }

        $oModuleConfiguration = $this->_getModuleConfiguration();
        $fMaxAmount = paypInstallmentsConfiguration::getPaymentMethodMaxAmount();
        $sRequiredCurrency = $oModuleConfiguration->getRequiredOrderTotalCurrency();

        if (empty($this->_aRequestParams['dAmount'])) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_MISSING_IN_PARTIAL_REFUND');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!is_float($this->_aRequestParams['dAmount'])) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_NOT_FLOATING_POINT_NUMBER');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if ($this->_aRequestParams['dAmount'] <= 0) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('NEGATIVE_REFUND_AMOUNT');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if ($this->_aRequestParams['dAmount'] > $fMaxAmount) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_EXCEEDS_MAXIMUM');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (empty($this->_aRequestParams['sCurrency'])) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_REFUND_CURRENCY');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if ($sRequiredCurrency != $this->_aRequestParams['sCurrency']) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_REFUND_CURRENCY');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This validation will only be applied on full refunds:
     * - dAmount must not be set in the request params
     *
     * @throws Exception
     */
    protected function _validateFullRefund()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateFullRefund", array());
        if (paypInstallmentsConfiguration::sRefundTypeFull != $this->_aRequestParams['sRefundType']) {
            return;
        }

        if ($this->_aRequestParams['dAmount']) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_PRESENT_IN_FULL_REFUND');
            $this->_throwRefundRequestParameterValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validate the RefundTransactionId in the response:
     * - it must not be empty
     *
     * @throws oxException
     */
    protected function _validateResponseRefundTransactionId()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateResponseRefundTransactionId", array());
        $sRefundTransactionID = $this->_oParser->getRefundTransactionId();
        if (empty($sRefundTransactionID)) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('EMPTY_REPONSE_VALUE_REFUNDTRANSACTIONID');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validate RefundedAmountValue in the response
     * - it must not be empty
     * - greater then 0
     * - a floating point number or an integer
     *
     * @throws oxException
     */
    protected function _validateResponseRefundedAmountValue()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateResponseRefundedAmountValue", array());
        $fTotalRefundedAmount = $this->_oParser->getTotalRefundedAmountValue();
        if (empty($fTotalRefundedAmount)) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_EMPTY');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($fTotalRefundedAmount < 0) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_NEGATIVE');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($this->_aRequestParams['dAmount'] > $this->_oParser->getTotalRefundedAmountValue()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_SMALLER_THAN_REQUESTED');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validate the RefundStatus in the response:
     * - it must be a certain string. PayPal says it must be always "Instant", so we validate it here.
     *
     * @throws oxException
     */
    protected function _validateResponseRefundStatus()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateResponseRefundStatus", array());
        $sRefundStatus = $this->_oParser->getRefundStatus();
        if (paypInstallmentsConfiguration::sRefundAllowedStatus != $sRefundStatus) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_REFUND_STATUS');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * @throws oxException
     */
    protected function _validateResponseRefundedAmountCurrency()
    {
        $this->getLogger()->info("paypInstallmentsRefundValidator _validateResponseRefundedAmountCurrency", array());
        $oModuleConfiguration = $this->_getModuleConfiguration();
        $sAllowedCurrency = $oModuleConfiguration->getRequiredOrderTotalCurrency();
        $sRefundedAmountCurrency = $this->_oParser->getTotalRefundedAmountCurrency();

        if (empty($sRefundedAmountCurrency)) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_REFUNDEDCURRENCY_EMPTY');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if ($sAllowedCurrency != $sRefundedAmountCurrency) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_WRONG_REFUNDEDCURRENCY');
            $this->_throwRefundResponseValidationException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param $sMessage
     *
     * @throws oxException
     */
    protected function _throwRefundResponseValidationException($sMessage)
    {
        /** @var oxException $oEx */
        $oEx = oxNew('paypInstallmentsRefundResponseValidationException');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }

    /**
     * @param $sMessage
     *
     * @throws oxException
     */
    protected function _throwRefundRequestParameterValidationException($sMessage)
    {
        /** @var oxException $oEx */
        $oEx = oxNew('paypInstallmentsRefundRequestParameterValidationException');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }

    /**
     * Return an instance of the module configuration
     *
     * @return paypInstallmentsConfiguration
     */
    protected function _getModuleConfiguration()
    {
        if (is_null($this->_oModuleConfiguration)) {
            $this->_oModuleConfiguration = oxNew('paypInstallmentsConfiguration');
        }

        return $this->_oModuleConfiguration;
    }
}
