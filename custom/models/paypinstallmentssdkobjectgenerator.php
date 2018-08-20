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

use PayPal\PayPalAPI\RefundTransactionReq;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\RefundTransactionRequestType;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\FundingSourceDetailsType;

/**
 * Class paypInstallmentsSdkObjectGenerator
 *
 * This class is used to generate PayPal API Objects to make calls to Paypal
 */
class paypInstallmentsSdkObjectGenerator extends oxSuperCfg implements \Psr\Log\LoggerAwareInterface
{

    /** @var  paypInstallmentsConfiguration */
    protected $_paypInstallmentsConfig;

    /** @var  paypInstallmentsCheckoutDataProvider */
    protected $oDataProvider;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $_oLogger;

    /**
     * Setter for logger. Method chain supported.
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $oLogger)
    {
        $this->_oLogger = $oLogger;

        return $this;
    }

    /**
     * getter for logger
     *
     * @return \Psr\Log\LoggerInterface | Psr\Log\NullLogger
     */
    public function getLogger()
    {
        if ($this->_oLogger === null) {
            $oManager = new paypInstallmentsLoggerManager(oxNew("paypInstallmentsConfiguration"));
            $this->setLogger($oManager->getLogger());
        }

        return $this->_oLogger;
    }

    /**
     * @param $_paypInstallmentsConfig - the configuration we are going to use to build PayPal Request Objects
     */
    public function setConfiguration($_paypInstallmentsConfig)
    {
        $this->_paypInstallmentsConfig = $_paypInstallmentsConfig;
    }

    /**
     * @param $oDataProvider - the DataProvider we are going to use to build PayPal Request Objects
     */
    public function setDataProvider($oDataProvider)
    {
        $this->oDataProvider = $oDataProvider;
    }

    /**
     * create, initialize and return a PayPalAPIInterfaceServiceService
     *
     * @return \PayPal\Service\PayPalAPIInterfaceServiceService
     */
    public function getPayPalServiceObject()
    {
        $oPayPalService = new PayPalAPIInterfaceServiceService(
            $this->_paypInstallmentsConfig->getSoapApiConfiguration()
        );

        return $oPayPalService;
    }

    /**
     * create, initialize and return a Pay Pal RefundTransactionReq
     *
     * @param $aParams - parameters that are necessary for the call
     *
     * @return \PayPal\PayPalAPI\RefundTransactionReq
     */
    public function getRefundTransactionReqObject($aParams)
    {
        $oRefundTransactionReq = new RefundTransactionReq();
        $oRefundTransactionReq->RefundTransactionRequest = $this->getRefundTransactionRequestObject($aParams);

        return $oRefundTransactionReq;
    }

    /**
     * create, initialize and return a Pay Pal RefundTransactionRequestType
     *
     * @param $aParams - parameters that are necessary for the call
     *
     * @return \PayPal\PayPalAPI\RefundTransactionRequestType
     */
    public function getRefundTransactionRequestObject($aParams)
    {
        $oRefundTransactionRequest = new RefundTransactionRequestType();
        $oRefundTransactionRequest->Amount = new BasicAmountType($aParams['sCurrency'], $aParams['dAmount']);
        $oRefundTransactionRequest->RefundType = $aParams['sRefundType'];
        $oRefundTransactionRequest->TransactionID = $aParams['sTransactionId'];
        $oRefundTransactionRequest->Memo = $aParams['sMemo'];

        return $oRefundTransactionRequest;
    }

    /**
     * create, initialize and return a Pay Pal DoExpressCheckoutPaymentReq
     *
     * @param $sPayerID   - encrypted PayPal customer account identification number as
     *                    returned by GetExpressCheckoutDetailsResponse
     * @param $sAuthToken - the authentication token we received from an earlier call to PayPal
     *
     * @return \PayPal\PayPalAPI\DoExpressCheckoutPaymentReq
     */
    public function getDoExpressCheckoutReqObject($sPayerID, $sAuthToken)
    {
        $oDoExpressCheckoutReq = new DoExpressCheckoutPaymentReq();
        $oDoExpressCheckoutReq->DoExpressCheckoutPaymentRequest = $this->getDoExpressCheckoutPaymentRequestObject($sPayerID, $sAuthToken);

        return $oDoExpressCheckoutReq;
    }

    /**
     * create, initialize and return a Pay Pal DoExpressCheckoutPaymentRequestType
     *
     * @param $sPayerID   - encrypted PayPal customer account identification number as
     *                    returned by GetExpressCheckoutDetailsResponse
     * @param $sAuthToken - the authentication token we received from an earlier call to PayPal
     *
     * @return \PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType
     */
    public function getDoExpressCheckoutPaymentRequestObject($sPayerID, $sAuthToken)
    {
        $oDoExpressCheckoutPaymentRequest = new DoExpressCheckoutPaymentRequestType();
        $oDoExpressCheckoutPaymentRequest->DoExpressCheckoutPaymentRequestDetails =
            $this->getDoExpressCheckoutPaymentRequestDetailsObject($sPayerID, $sAuthToken);

        return $oDoExpressCheckoutPaymentRequest;
    }

    /**
     * create, initialize and return a Pay Pal DoExpressCheckoutPaymentRequestDetailsType
     *
     * @param $sPayerID   - encrypted PayPal customer account identification number as
     *                    returned by GetExpressCheckoutDetailsResponse
     * @param $sAuthToken - the authentication token we received from an earlier call to PayPal
     *
     * @return \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType
     */
    public function getDoExpressCheckoutPaymentRequestDetailsObject($sPayerID, $sAuthToken)
    {
        $oDoExpressCheckoutPaymentRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
        $oDoExpressCheckoutPaymentRequestDetails->PayerID = $sPayerID;
        $oDoExpressCheckoutPaymentRequestDetails->Token = $sAuthToken;
        $oDoExpressCheckoutPaymentRequestDetails->PaymentAction = "Sale";
        $oDoExpressCheckoutPaymentRequestDetails->ButtonSource = paypInstallmentsConfiguration::sButtonSource;
        $oDoExpressCheckoutPaymentRequestDetails->PaymentDetails[0] = $this->getPaymentDetailsObject();

        return $oDoExpressCheckoutPaymentRequestDetails;
    }

    /**
     * create, initialize and return a Pay Pal PaymentDetailsType
     *
     * @return \PayPal\EBLBaseComponents\PaymentDetailsType
     */
    public function getPaymentDetailsObject()
    {
        $oLogger = $this->getLogger();
        $oLogger->info(__CLASS__ . ' ' . __FUNCTION__, array());

        $oPaymentDetails = new PaymentDetailsType();

        $fAdditionalHandlingFee = 0.0;
        $fAdditionalShippingDiscount = 0.0;
        $fOrderTotalDiff = $this->determineOrderCostDiff();
        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fOrderTotalDiff' => $fOrderTotalDiff));

        if ($fOrderTotalDiff >= 0.01) {
            $fAdditionalHandlingFee = $fOrderTotalDiff;
        }
        if ($fOrderTotalDiff <= -0.01) {
            $fAdditionalShippingDiscount = $fOrderTotalDiff;
        }

        /**
         * As of now ShippingDiscount must be transmitted as a basket item with negative amount
         * $oPaymentDetails->ShippingDiscount = new BasicAmountType(
         * $this->oDataProvider->getCurrency(),
         * $this->oDataProvider->getShippingDiscount() + $fAdditionalShippingDiscount
         * );
         */
        $oPaymentDetails->ShippingDiscount = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            0.0
        );

        $aBasketItems = $this->oDataProvider->getBasketItemDataList();

        $fDiscountItemPrice = $fDiscountUnitPrice = $this->oDataProvider->getShippingDiscount() + $fAdditionalShippingDiscount;
        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fDiscountItemPrice' => $fDiscountItemPrice));
        if ($fDiscountItemPrice) {
            $sName = 'Rabatt';
            $sCurrency = $this->oDataProvider->getCurrency();
            $iQuantity = 1;
            $oDiscount = $this->oDataProvider->getBasketItemData($sName, $fDiscountItemPrice, $fDiscountUnitPrice, $sCurrency, $iQuantity);
            $aBasketItems[] = $oDiscount;
        }

        $oPaymentDetails->PaymentDetailsItem = array();
        foreach ($aBasketItems as $oBasketItem) {
            $oPaymentDetails->PaymentDetailsItem[] = $this->createPaymentDetailsObjectFor($oBasketItem);
        }

        $oPaymentDetails->ShipToAddress = $this->getAddressObject();

        $fItemTotal = $this->oDataProvider->getItemTotal() + $fDiscountItemPrice;
        $oPaymentDetails->ItemTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $fItemTotal
        );

        $oPaymentDetails->OrderTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $this->oDataProvider->getOrderTotal()
        );

        $fHandlingTotal = $this->oDataProvider->getHandlingTotal() + $fAdditionalHandlingFee;
        $oPaymentDetails->HandlingTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $fHandlingTotal
        );

        $oPaymentDetails->InsuranceTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $this->oDataProvider->getTSProtectionCosts()
        );

        $oPaymentDetails->ShippingTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $this->oDataProvider->getShippingCosts()
        );

        $oPaymentDetails->TaxTotal = new BasicAmountType(
            $this->oDataProvider->getCurrency(),
            $this->oDataProvider->getBasketTotalVat()
        );

        $oPaymentDetails->PaymentAction = "Sale";

        $oPaymentDetails->InvoiceID = $this->oDataProvider->getInvoiceId();

        //$oPaymentDetails->NotifyURL = #TODO;

        return $oPaymentDetails;
    }

    /**
     * Convert a BasketItem into an PayPal API PaymentDetailsType Object
     *
     * @param $oBasketItem - the BasketItem to be converted to a PayPal API PaymentDetailsType Object
     *
     * @return PaymentDetailsItemType
     */
    public function createPaymentDetailsObjectFor($oBasketItem)
    {
        $oPaymentDetailsItem = new PaymentDetailsItemType();
        $oPaymentDetailsItem->Name = $oBasketItem->sName;
        $oPaymentDetailsItem->Amount = new BasicAmountType(
            $oBasketItem->sCurrency,
            $oBasketItem->fUnitPrice
        );
        $oPaymentDetailsItem->Quantity = $oBasketItem->iQuantity;
        $oPaymentDetailsItem->ItemCategory = $oBasketItem->sItemCategory;
        $oPaymentDetailsItem->Tax = new BasicAmountType($oBasketItem->sCurrency, $oBasketItem->fTax);

        return $oPaymentDetailsItem;
    }

    /**
     * create, initialize and return a Pay Pal GetExpressCheckoutDetailsReq
     *
     * @param $sAuthToken - the authentication token we received from an earlier call to PayPal
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsReq
     */
    public function getGetExpressCheckoutReqObject($sAuthToken)
    {
        $oGetExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
        $oGetExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $this->getGetExpressCheckoutDetailsRequestObject($sAuthToken);

        return $oGetExpressCheckoutReq;
    }

    /**
     * create, initialize and return a Pay Pal GetExpressCheckoutDetailsRequestType
     *
     * @param $sAuthToken - the authentication token we received from an earlier call to PayPal
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType
     */
    public function getGetExpressCheckoutDetailsRequestObject($sAuthToken)
    {
        $oGetExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($sAuthToken);

        return $oGetExpressCheckoutDetailsRequest;
    }

    /**
     * create, initialize and return a Pay Pal SetExpressCheckoutReq
     *
     * @return \PayPal\PayPalAPI\SetExpressCheckoutReq
     */
    public function getSetExpressCheckoutReqObject()
    {
        $oSetExpressCheckoutReq = new SetExpressCheckoutReq();
        $oSetExpressCheckoutReq->SetExpressCheckoutRequest = $this->getSetExpressCheckoutRequestObject();

        return $oSetExpressCheckoutReq;
    }

    /**
     * create, initialize and return a Pay Pal SetExpressCheckoutRequestType
     *
     * @return \PayPal\PayPalAPI\SetExpressCheckoutRequestType
     */
    public function getSetExpressCheckoutRequestObject()
    {
        $oSetExpressCheckoutRequest = new SetExpressCheckoutRequestType();
        $oSetExpressCheckoutRequest->SetExpressCheckoutRequestDetails =
            $this->getSetExpressCheckoutRequestDetailsObject();

        return $oSetExpressCheckoutRequest;
    }

    /**
     * create, initialize and return a Pay Pal SetExpressCheckoutRequestDetailsType
     *
     * @return \PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType
     */
    public function getSetExpressCheckoutRequestDetailsObject()
    {
        $oSetExpressCheckoutRequestDetails = new SetExpressCheckoutRequestDetailsType();
        $oSetExpressCheckoutRequestDetails->PaymentDetails[0] = $this->getPaymentDetailsObject();
        $oSetExpressCheckoutRequestDetails->CancelURL = $this->_paypInstallmentsConfig->getCancelUrl();
        $oSetExpressCheckoutRequestDetails->ReturnURL = $this->_paypInstallmentsConfig->getReturnUrl();
        $oSetExpressCheckoutRequestDetails->NoShipping = 2;
        $oSetExpressCheckoutRequestDetails->AddressOverride = 0;
        $oSetExpressCheckoutRequestDetails->ReqConfirmShipping = 0;
        $oSetExpressCheckoutRequestDetails->LocaleCode = $this->_paypInstallmentsConfig->getRequiredShippingCountry();

        // $oSetExpressCheckoutRequestDetails->BillingAgreementDetails = array($this->getBillingAgreementDetailsObject
        //());
        $oSetExpressCheckoutRequestDetails->FundingSourceDetails = $this->getFundingSourceDetailsObject();
        $oSetExpressCheckoutRequestDetails->LandingPage = $this->_paypInstallmentsConfig->getRequiredLandingPage();

        $oSetExpressCheckoutRequestDetails->AllowNote = 0;

        return $oSetExpressCheckoutRequestDetails;
    }

    /**
     * create, initialize and return a Pay Pal AddressType
     *
     * @return \PayPal\EBLBaseComponents\AddressType
     */
    public function getAddressObject()
    {
        $oAddress = new AddressType();
        $oAddressData = $this->oDataProvider->getShippingAddressData();
        $oAddress->CityName = $oAddressData->sCity;
        //TODO: private person OR company
        $oAddress->Name = $oAddressData->sFirstname . " " . $oAddressData->sLastname;
        $oAddress->Street1 = $oAddressData->sStreet;
        $oAddress->StateOrProvince = $oAddressData->sState;
        $oAddress->PostalCode = $oAddressData->sZip;
        $oAddress->Country = $oAddressData->sCountry;
        //TODO: maybe add a phone number to oAddressData
        //$oAddress->Phone = "0761 45875789-0";

        return $oAddress;
    }

    /**
     * create, initialize and return a Pay Pal FundingSourceDetailsType
     *
     * @return \PayPal\EBLBaseComponents\FundingSourceDetailsType
     */
    public function getFundingSourceDetailsObject()
    {
        $oFundingSourceDetails = new FundingSourceDetailsType();
        $oFundingSourceDetails->UserSelectedFundingSource = $this->_paypInstallmentsConfig->getRequiredFundingSource();

        return $oFundingSourceDetails;
    }

    /**
     * Calculate the difference between all positions of our order and the total order cost
     *
     * @return float
     */
    protected function determineOrderCostDiff()
    {
        $fResult = $this->oDataProvider->getOrderTotal();
        $fResult -= $this->oDataProvider->getHandlingTotal();
        $fResult -= $this->oDataProvider->getItemTotal();
        $fResult -= $this->oDataProvider->getTSProtectionCosts();
        $fResult -= $this->oDataProvider->getShippingCosts();
        $fResult -= $this->oDataProvider->getShippingDiscount();
        $fResult -= $this->oDataProvider->getBasketTotalVat();

        return oxRegistry::getUtils()->fRound($fResult);
    }
}
