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
 * Class paypInstallmentsOxPaymentGateway
 *
 * @desc Payment execution class.
 */
class paypInstallmentsOxPaymentGateway extends paypInstallmentsOxPaymentGateway_parent implements \Psr\Log\LoggerAwareInterface
{

    /**
     * A message to be logged.
     * This message will be set before throwing an exception and then captured by the exception handler.
     *
     * @see \paypInstallments_oxPaymentGateway::_paypInstallments_paymentExceptionHandler
     *
     * @var string
     */
    protected $_sLogMessage;

    /**
     * A context to be logged.
     * This context will be set before throwing an exception and then captured by the exception handler.
     *
     * @see \paypInstallments_oxPaymentGateway::_paypInstallments_paymentExceptionHandler
     *
     * @var array
     */
    protected $_aLogContext = array();

    /**
     * @var \Psr\Log\LoggerInterface | null
     */
    protected $_oLogger = null;

    /**
     * getter for logger
     *
     * @return \Psr\Log\LoggerInterface | Psr\Log\NullLogger
     */
    public function getLogger()
    {
        if ($this->_oLogger === null) {
            $oConfig = oxNew('paypInstallmentsConfiguration');
            $oLoggerManager = oxNew('paypInstallmentsLoggerManager', $oConfig);
            $this->setLogger($oLoggerManager->getLogger());
        }

        return $this->_oLogger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return void
     */
    public function setLogger(\Psr\Log\LoggerInterface $oLogger)
    {
        $this->_oLogger = $oLogger;
    }

    /**
     * @inheritdoc
     *
     * Call DoExpressCheckout, if the user chose the payment method PayPal Installments
     *
     * @param float   $dAmount
     * @param oxOrder $oOrder
     *
     * @return bool|mixed
     */
    public function executePayment($dAmount, & $oOrder)
    {
        $blSuccess = $this->_paypInstallments_CallExecutePaymentParent($dAmount, $oOrder);

        $blisPayPalInstallmentsPayment = $this->_paypInstallments_isPayPalInstallmentsPayment();

        /**
         * If the user selected PayPal installments as payment method,
         * a call to PayPal API method DoExpressCheckout will be made.
         */
        if ($blisPayPalInstallmentsPayment) {
            $blSuccess = $this->_paypInstallments_doExpressCheckout($dAmount, $oOrder);
        }

        return $blSuccess;
    }

    /**
     * @inheritdoc
     *
     * @param float   $dAmount
     * @param oxOrder $oOrder
     */
    protected function _paypInstallments_doExpressCheckout($dAmount, & $oOrder)
    {
        $blSuccess = true;

        try {
            /** Initialize logger and say hello */
            $oLoggedOrder = clone $oOrder;
            $oLogger = $this->getLogger();

            $sMessage = __CLASS__ . '::' . __FUNCTION__;
            $aContext = array('amount' => $dAmount, 'oOrder' => $oLoggedOrder);
            $oLogger->info($sMessage, $aContext);

            /** Collect the needed parameters from the different places */
            $sOrderId = $oOrder->getId();
            $oBasket = $this->_paypInstallments_getBasketFromSession();
            $sToken = $this->_paypInstallments_getPayPalTokenFromSession();
            $sPayerId = $this->_paypInstallments_getPayPalPayerIdFromSession();
            $oFinancingDetails = $this->_paypInstallments_getFinancingDetailsFromSession();

            /** Do the request. This may throw exceptions */
            try {
                $oHandler = $this->_paypInstallments_getDoExpressCheckoutPaymentHandler($sToken, $sPayerId);
                $oHandler->setBasket($oBasket);
                $aParsedResponseData = $oHandler->doRequest();
            } catch (Exception $oEx) {
                $this->_sLogMessage = __CLASS__ . '::' . __FUNCTION__ . ' DoExpressCheckout failed';
                $this->_aLogContext = array('Exception' => $oEx);

                /** Re-throw exception to be able to handle it later */
                throw $oEx;
            }

            $aDataToBePersisted = array(
                'OrderId'          => $sOrderId,
                'OrderNr'          => $this->_paypInstallments_getOrderNrFromSession(),
                'Timestamp'        => $aParsedResponseData['Timestamp'],
                'TransactionId'    => $aParsedResponseData['TransactionId'],
                'PaymentStatus'    => $aParsedResponseData['PaymentStatus'],
                'Response'         => $aParsedResponseData['Response'],
                'FinancingDetails' => $oFinancingDetails,
            );

            /** Persist Data */
            $this->_paypInstallments_persistOrderData($oOrder, $aDataToBePersisted);
            $this->_paypInstallments_persistPaymentData($aDataToBePersisted);
        } catch (Exception $oEx) {
            /** Handle all exception here */
            $this->_paypInstallments_paymentExceptionHandler($oEx);

            $blSuccess = false;
        }

        return $blSuccess;
    }

    /**
     * Return an instance of paypInstallmentsDoExpressCheckoutPaymentHandler.
     * Needed for mocking.
     *
     * @codeCoverageIgnore
     *
     * @param $sToken
     * @param $sPayerId
     *
     * @return paypInstallmentsDoExpressCheckoutPaymentHandler
     */
    protected function _paypInstallments_getDoExpressCheckoutPaymentHandler($sToken, $sPayerId)
    {
        return oxNew('paypInstallmentsDoExpressCheckoutPaymentHandler', $sToken, $sPayerId);
    }

    /**
     * Save orderID, transactionID, financing details, payment status and the response object to the PaymentData table
     *
     * @param $aData
     *
     * @throws paypInstallmentsPersistPaymentDataException|oxException
     */

    protected function _paypInstallments_persistPaymentData($aData)
    {
        /** @var paypInstallmentsFinancingDetails $oFinancingDetails */
        $oFinancingDetails = $aData['FinancingDetails'];
        /** @var paypInstallmentsPaymentData $oPaymentData */
        $oPaymentData = oxNew('paypInstallmentsPaymentData');
        $oPaymentData->setOrderId($aData['OrderId']);
        $oPaymentData->setTransactionId($aData['TransactionId']);
        $oPaymentData->setStatus($aData['PaymentStatus']);
        $oPaymentData->setResponse($aData['Response']);
        $oPaymentData->setFinancingFeeCurrency($oFinancingDetails->getFinancingCurrency());
        $oPaymentData->setFinancingFeeAmount($oFinancingDetails->getFinancingFeeAmount()->getBruttoPrice());
        $oPaymentData->setFinancingTotalCostAmount($oFinancingDetails->getFinancingTotalCost()->getBruttoPrice());
        $oPaymentData->setFinancingMonthlyPaymentAmount($oFinancingDetails->getFinancingMonthlyPayment()->getBruttoPrice());
        $oPaymentData->setFinancingTerm($oFinancingDetails->getFinancingTerm());

        if (!$oPaymentData->save()) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' Payment data could not be persisted';
            $aContext = array('dataToBePersisted' => $aData);
            /** Throw exception to be able to handle it later */
            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsPersistPaymentDataException');
        }
    }

    /**
     * Set the Transaction ID to the order and set the order to paid, if the payment was completed.
     *
     * @param oxOrder $oOrder
     * @param         $aDataToBePersisted
     *
     * @return array
     *
     * @throws paypInstallmentsPersistPaymentDataException|oxException
     */
    protected function _paypInstallments_persistOrderData(oxOrder $oOrder, $aDataToBePersisted)
    {
        /** Save TransactionID; Financing Fee and Payment date to the oxorder table */

        /** @var paypInstallmentsFinancingDetails $oFinancingDetails */
        $oFinancingDetails = $aDataToBePersisted['FinancingDetails'];
        $fFinancingFee = $oFinancingDetails->getFinancingFeeAmount()->getBruttoPrice();

        /** @var paypInstallmentsConfiguration $oConfig */
        $oConfig = oxNew('paypInstallmentsConfiguration');
        $sPaymentCompletedStatus = $oConfig->getPayPalInstallmentsPaymentCompletedStatus();
        $oOrder->oxorder__oxtransid = new oxField($aDataToBePersisted['TransactionId']);
        $oOrder->oxorder__paypinstallments_financingfee = new oxField($fFinancingFee);
        $oOrder->oxorder__oxordernr = new oxField($aDataToBePersisted['OrderNr']);
        if ($sPaymentCompletedStatus == $aDataToBePersisted['PaymentStatus']) {
            $oOrder->oxorder__oxpaid = new oxField($aDataToBePersisted['Timestamp']);
        }

        if (!$oOrder->save()) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' Order data could not be persisted';
            $aContext = array(
                'oxOrder'           => $oOrder,
                'dataToBePersisted' => $aDataToBePersisted
            );

            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsPersistPaymentDataException');
        }
    }

    /**
     * Return PayPal Token stored in session.
     *
     * @return string
     *
     * @throws paypInstallmentsCorruptSessionException|oxException
     */
    protected function _paypInstallments_getPayPalTokenFromSession()
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $sToken = $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalTokenKey);

        /** If Token is empty, throw an exception */
        if (empty($sToken)) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' Token not found in session';
            $aContext = array('session' => $_SESSION);

            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsCorruptSessionException');
        }

        return $sToken;
    }

    /**
     * Return PayPal PayerId stored in session
     *
     * @return string
     *
     * @throws paypInstallmentsCorruptSessionException|oxException
     */
    protected function _paypInstallments_getPayPalPayerIdFromSession()
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $sPayerId = $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalPayerIdKey);

        /** If PayerId is empty, throw an exception */
        if (empty($sPayerId)) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' PayerId not found in session';
            $aContext = array('session' => $_SESSION);

            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsCorruptSessionException');
        }

        return $sPayerId;
    }

    /**
     * Return financing details from session
     *
     * @return paypInstallmentsFinancingDetails
     *
     * @throws paypInstallmentsCorruptSessionException|oxException
     */
    protected function _paypInstallments_getFinancingDetailsFromSession()
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $oFinancingDetails = $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey);
        /** If no financing options are set, throw an exception */
        if (!$oFinancingDetails instanceof paypInstallmentsFinancingDetails) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' No financing details found in session';
            $aContext = array('session' => $_SESSION);

            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsCorruptSessionException');
        }

        return $oFinancingDetails;
    }

    /**
     * Get the basket from the session
     *
     * @return oxbasket
     *
     * @throws paypInstallmentsCorruptSessionException|oxException
     */
    protected function _paypInstallments_getBasketFromSession()
    {
        $oBasket = $this->getSession()->getBasket();
        if (!$oBasket instanceof oxBasket) {
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' No basket found in session';
            $aContext = array('session' => $_SESSION);

            throw $this->_paypInstallments_buildException($sMessage, $aContext, 'paypInstallmentsCorruptSessionException');
        }

        return $oBasket;
    }

    /**
     * Order number getter.
     *
     * @return mixed
     */
    protected function _paypInstallments_getOrderNrFromSession()
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $sOrderNr = $oSession->paypInstallmentsGetOrderNr();

        return $sOrderNr;
    }

    /**
     * Call parent::executePayment
     * Needed for testing.
     *
     * @codeCoverageIgnore
     *
     * @param $dAmount
     * @param $oOrder
     *
     * @return bool|mixed
     */
    protected function _paypInstallments_CallExecutePaymentParent($dAmount, $oOrder)
    {
        return parent::executePayment($dAmount, $oOrder);
    }

    /**
     * Central place for handling exceptions.
     * \oxPaymentGateway::$_iLastErrorNo is assigned here.
     * This value is processed later in tpl/page/checkout/payment.tpl
     * There you have the possibility to assign messages to the error numbers.
     *
     * @param Exception $oEx
     */
    protected function _paypInstallments_paymentExceptionHandler(Exception $oEx)
    {
        switch (true) {
            case $oEx instanceof paypInstallmentsCorruptSessionException  :
                $iLastErrorNo = 1101;
                break;
            case $oEx instanceof paypInstallmentsException:
            default:
                $iLastErrorNo = 1100;
        }

        /**
         * Errors will be rendered in the payment template tpl/page/checkout/payment.tpl
         * In this template the error numbers will be mapped to error messages.
         *
         * @see \Payment::getPaymentError
         */
        $this->_iLastErrorNo = $iLastErrorNo;

        $oLogger = $this->getLogger();
        $oLogger->error($this->_sLogMessage, $this->_aLogContext);

        $this->_sLogMessage = null;
        $this->_aLogContext = null;
    }

    /**
     * @param $sMessage
     * @param $aContext
     * @param $sException
     *
     * @return oxException
     */
    protected function _paypInstallments_buildException($sMessage, $aContext, $sException)
    {
        $this->_sLogMessage = $sMessage;
        $this->_aLogContext = $aContext;

        /** @var oxException $oEx */
        $oEx = oxNew($sException);
        $oEx->setMessage($sMessage);
        $oEx->debugOut();

        return $oEx;
    }

    /**
     * Return true, if the selected payment method is PayPal Installments.
     *
     * @return bool
     */
    protected function _paypInstallments_isPayPalInstallmentsPayment()
    {
        $blIsPayPalInstallmentsPayment = false;

        /** Get the ID of the payment the user has selected */
        $sSelectedPaymentId = $this->_paypInstallments_getPaymentIdFromSession();

        /** Get ID of PayPal Installments payment method */
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        /**
         * If the user selected PayPal installments as payment method,
         * a call to PayPal API method DoExpressCheckout will be made.
         */
        if ($sSelectedPaymentId == $sPayPalInstallmentsPaymentId) {
            $blIsPayPalInstallmentsPayment = true;
        }

        return $blIsPayPalInstallmentsPayment;
    }

    /**
     * Return the selected payment ID from the session.
     *
     * @return mixed
     */
    protected function _paypInstallments_getPaymentIdFromSession()
    {
        $oSession = $this->getSession();
        $sSelectedPaymentId = $oSession->getVariable('paymentid');

        return $sSelectedPaymentId;
    }
}
