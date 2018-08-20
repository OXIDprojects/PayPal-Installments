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
 * Class paypInstallmentsRefundParser
 *
 * Parser for the request and the response of the PayPal RefundTransaction call.
 */
class paypInstallmentsRefundParser extends paypInstallmentsSoapParser
{

    const RESPONSE_TYPE = '\PayPal\PayPalAPI\RefundTransactionResponseType';

    /**
     * The request to be sent to PayPal.
     *
     * @var \PayPal\PayPalAPI\RefundTransactionReq
     */
    protected $_oRequest;

    /**
     * Property setter
     *
     * @codeCoverageIgnore
     *
     * @param \PayPal\PayPalAPI\RefundTransactionReq $oRequest
     */
    public function setRequest(\PayPal\PayPalAPI\RefundTransactionReq $oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Return the data to be stored in the database.
     * Part of the data is provided by the request, part by the response.
     *
     * @see paypInstallmentsRefund::save()
     *
     * @return array
     */
    public function getRefundData()
    {
        $this->getLogger()->info("paypInstallmentsRefundParser getRefundData", array());
        $aRefundData = array(
            /** Data from the request */
            'TransactionId'             => $this->getTransactionId(),
            'Memo'                      => $this->getMemo(),
            /** Data from the respone */
            'RefundId'                  => $this->getRefundTransactionId(),
            'GrossRefundAmount'         => $this->getGrossRefundAmountValue(),
            'GrossRefundAmountCurrency' => $this->getGrossRefundAmountCurrency(),
            'TotalRefundedAmount'       => $this->getTotalRefundedAmountValue(),
            'TotalRefundedCurrency'     => $this->getTotalRefundedAmountCurrency(),
            'Status'                    => $this->getRefundStatus(),
            /** The response as a whole */
            'Response'                  => $this->getResponse()
        );

        return $aRefundData;
    }

    /**
     * Return the ID of the associated transaction from the request.
     * This must be stored in the database to be able to identify to which transaction the refund belongs.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getTransactionId()
    {
        $oClass = $this->_oRequest->RefundTransactionRequest;
        $sProperty = 'TransactionID';
        $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_TRANSACTION_ID');

        $sTransactionId = $this->_getValueByClassAndProperty($oClass, $sProperty, $sMessage);

        return $sTransactionId;
    }

    /**
     * Return the memo of the original transaction from the request.
     * This must be stroed in the database.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getMemo()
    {
        $oClass = $this->_oRequest->RefundTransactionRequest;
        $sProperty = 'Memo';
        $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_MEMO');

        $sMemo = $this->_getValueByClassAndProperty($oClass, $sProperty, $sMessage);

        return $sMemo;
    }

    /**
     * Return the RefundTransactionID from the response.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getRefundTransactionId()
    {
        return $this->_getValueFromResponse('RefundTransactionID');
    }

    /**
     * Return the refunded amount from the response.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getTotalRefundedAmountValue()
    {
        return (float) $this->_getValueFromResponse('TotalRefundedAmount')->value;
    }

    /**
     * Return the refunded currency from the response.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getTotalRefundedAmountCurrency()
    {
        return $this->_getValueFromResponse('TotalRefundedAmount')->currencyID;
    }

    public function getGrossRefundAmountValue()
    {
        return $this->_getValueFromResponse('GrossRefundAmount')->value;
    }

    public function getGrossRefundAmountCurrency()
    {
        return $this->_getValueFromResponse('GrossRefundAmount')->currencyID;
    }

    /**
     * Return the refund status from the response.
     *
     * @throws paypinstallmentsmalformedresponseexception
     *
     * @return string
     */
    public function getRefundStatus()
    {
        $oClass = $this->getResponse()->RefundInfo;
        $sProperty = 'RefundStatus';
        $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_REFUNDSTATUS_IN_RESPONSE');

        $sRefundStatus = $this->_getValueByClassAndProperty($oClass, $sProperty, $sMessage);

        return $sRefundStatus;
    }
}
