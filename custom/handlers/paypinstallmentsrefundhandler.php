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
 * Class paypInstallmentsRefundHandler
 * Class to send a refund to PayPal
 */
class paypInstallmentsRefundHandler extends paypInstallmentsHandlerBase
{

    const REFUND_FULL = 'Full';
    const REFUND_PARTIAL = 'Partial';

    /** @var  string */
    protected $_sTransactionId;
    /** @var  string */
    protected $_sRefundType;
    /** @var  string */
    protected $_sCurrency;
    /** @var  float Amount to be refunded */
    protected $_dAmount;
    /** @var  float */
    protected $_dRefundableAmount;
    /** @var  string */
    protected $_sMemo;

    /**
     * Handler requires transactionId.
     * Default refund type is "Full".
     * "Partial" refund requires currency and amount too.
     * Memo is optional variable.
     *
     * @param string      $sTransactionId
     * @param string      $sRefundType
     * @param null|string $sCurrency
     * @param null|float  $dAmount
     * @param null|string $sMemo
     */
    public function __construct($sTransactionId, $sRefundType = self::REFUND_FULL, $sCurrency = null, $dAmount = null, $dRefundableAmount = null, $sMemo = null )
    {
        $this->setTransactionId($sTransactionId)
            ->setRefundType($sRefundType)
            ->setCurrency($sCurrency)
            ->setAmount($dAmount)
            ->setRefundableAmount($dRefundableAmount)
            ->setMemo($sMemo);
    }

    /**
     * Inits connection to PayPal and tries to set up a refund for given transaction
     * returns refund object on success
     *
     * @return array Returns the data to be stored in the database.
     *
     * @throws Exception
     */
    public function doRequest()
    {
        //set up refund validator
        /** @var paypInstallmentsRefundValidator $oValidator */
        $oValidator = $this->_getValidator('paypInstallmentsRefundValidator');

        //set up refund parser
        /** @var paypInstallmentsRefundParser $oParser */
        $oParser = $this->_getParser('paypInstallmentsRefundParser');
        $oValidator->setParser($oParser);

        $oLogger = $this->getLogger();
        $oValidator->setLogger($oLogger);
        $oParser->setLogger($oLogger);

        $aParams = $this->_buildParams();

        $oLogger->info("paypInstallmentsRefundHandler doRequest", array("params" => $aParams));

        /** @var paypInstallmentsSdkObjectGenerator $sdk */
        $oSdk = oxNew('paypInstallmentsSdkObjectGenerator');

        /** @var \PayPal\Service\PayPalAPIInterfaceServiceService $oPayPalService */
        $oPayPalService = $this->_getPayPalServiceObject($oSdk);

        //check if request params are valid
        $oValidator->setRequestParams($aParams);
        $oValidator->setRefundableAmount($this->getRefundableAmount());
        $oValidator->validateRequest();

        /** @var \PayPal\PayPalAPI\RefundTransactionReq $oRequest */
        $oRequest = $this->_getRequestObject($oSdk, $aParams);

        //Call RefundTransaction
        //Rethrow Exception, so we can handle it in our own way
        try {
            $oResponse = $oPayPalService->RefundTransaction($oRequest);

            //setting response to validator
            $oValidator->setResponse($oResponse);

            //check if response is valid
            $oValidator->validateResponse();

        } catch (Exception $oPayPalException) {
            $sCode = $oPayPalException->getCode();
            if ('10001' == $sCode) {
                $sMessage = 'PAYP_INSTALLMENTS_REFUND_ERR_10001';
            } else {
                $sMessage = $oPayPalException->getMessage();
            }
            $oLogger->error("paypInstallmentsRefundHandler doRequest exception", array("message" => $oPayPalException->getMessage()));
            $this->_throwRefundTransactionException($sMessage);
        }

        $oLogger->info("paypInstallmentsRefundHandler doRequest", array("response" => $oResponse));


        /** Get and the refund data to be stored from the parser  */
        $oParser->setRequest($oRequest);
        $aRefundData = $oParser->getRefundData();

        return $aRefundData;
    }

    /**
     * returns the paypal service object
     *
     * @param paypInstallmentsSdkObjectGenerator $oSdk
     *
     * @return \PayPal\Service\PayPalAPIInterfaceServiceService
     */
    public function _getPayPalServiceObject($oSdk)
    {
        $oSdk->setConfiguration(oxNew("paypInstallmentsConfiguration"));
        $oPayPalServiceObject = $oSdk->getPayPalServiceObject();

        return $oPayPalServiceObject;
    }

    /**
     * Params builder.
     *
     * @return array The following keys will be processed:
     *                       - $aParams['sTransactionId'] : required
     *                       - $aParams['sRefundType'] : required ( Full | Partial )
     *                       - $aParams['sCurrency'] : required for sRefundType 'Partial'
     *                       - $aParams['dAmount'] : required for sRefundType 'Partial'
     *                       - $aParams['sMemo'] : optional
     */
    protected function _buildParams()
    {
        $aParams['sTransactionId'] = $this->getTransactionId();
        $aParams['sRefundType'] = $this->getRefundType();

        if ($this->_isRefundPartial()) {
            $aParams['sCurrency'] = $this->getCurrency();
            $aParams['dAmount'] = $this->getAmount();
            $aParams['sMemo'] = $this->getMemo();
        }

        return $aParams;
    }

    /**
     * Check refund is partial.
     *
     * @return bool
     */
    protected function _isRefundPartial()
    {
        return $this->getRefundType() === static::REFUND_PARTIAL;
    }


    /**
     * TransactionId getter.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_sTransactionId;
    }

    /**
     * TransactionId getter. TransactionId is mandatory attribute. Method chain supported.
     *
     * @param string $sTransactionId
     *
     * @return $this
     */
    public function setTransactionId($sTransactionId)
    {
        $this->_sTransactionId = (string) $sTransactionId;

        return $this;
    }

    /**
     * RefundType getter.
     *
     * @return string
     */
    public function getRefundType()
    {
        return $this->_sRefundType;
    }

    /**
     * Refund type setter. RefundType is mandatory attribute. Method chain supported.
     *
     * @param string $sRefundType
     *
     * @return $this
     */
    public function setRefundType($sRefundType)
    {
        $this->_sRefundType = $sRefundType;

        return $this;
    }

    /**
     * Currency getter.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_sCurrency;
    }

    /**
     * Currency setter. Method china supported.
     *
     * @param $sCurrency
     *
     * @return $this
     */
    public function setCurrency($sCurrency)
    {
        $this->_sCurrency = $sCurrency;

        return $this;
    }

    /**
     * Amount getter.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_dAmount;
    }

    /**
     * Amount setter. Method chain supported.
     *
     * @param $dAmount
     *
     * @return $this
     */
    public function setAmount($dAmount)
    {
        $this->_dAmount = $dAmount;

        return $this;
    }

    /**
     * RefundableAmount getter.
     *
     * @return float
     */
    public function getRefundableAmount()
    {
        return $this->_dRefundableAmount;
    }

    /**
     * RefundableAmount setter. Method chain supported.
     *
     * @param $dRefundableAmount
     *
     * @return $this
     */
    public function setRefundableAmount($dRefundableAmount)
    {
        $this->_dRefundableAmount = $dRefundableAmount;

        return $this;
    }

    /**
     * Memo getter.
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->_sMemo;
    }

    /**
     * Memo setter. Method chain supported.
     *
     * @param $sMemo
     *
     * @return $this
     */
    public function setMemo($sMemo)
    {
        $this->_sMemo = $sMemo;

        return $this;
    }

    /**
     * returns request object for refund call
     *
     * @param paypInstallmentsSdkObjectGenerator $oSdk
     * @param                                        $aParams
     *
     * @return \PayPal\PayPalAPI\RefundTransactionReq
     */
    protected function _getRequestObject($oSdk, $aParams)
    {
        $oRequest = $oSdk->getRefundTransactionReqObject($aParams);

        return $oRequest;
    }

    /**
     * throw this exception if something went wrong on request to PayPal
     *
     * @param $sMessage
     *
     * @throws Exception
     */
    protected function _throwRefundTransactionException($sMessage)
    {
        /** @var oxException $oEx */
        $oEx = oxNew('paypInstallmentsRefundTransactionException');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }
}
