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
 * Class paypinstallmentssdkobjectgeneratorTest
 *
 * Test the public interface of the PayPal Installments Object Generator
 */
class paypinstallmentssdkobjectgeneratorTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var paypInstallmentsSdkObjectGenerator
     */
    protected $SUT;


    /**
     * @inheritDoc
     *
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock('paypInstallmentsSdkObjectGenerator', array('__call'));

        $aProvidedData = $this->dataProviderPaymentDetails();
        $oDataProvider = $this->getMockDataProvider($aProvidedData[0][0]);
        $oConfig = $this->getMockConfiguration();

        $this->SUT->setDataProvider($oDataProvider);
        $this->SUT->setConfiguration($oConfig);
    }

    /**
     * test whether getFundingSourceDetailsObject returns an object of type FundingSourceDetailsType
     */
    public function testGetFundingSourceDetailsObject()
    {
        $oFundingSourceDetails = $this->SUT->getFundingSourceDetailsObject();
        $this->assertValidFundingSource($oFundingSourceDetails);
    }

    /**
     * test whether getAddressObject returns an object of type AddressType
     */
    public function testGetAddressObject()
    {
        $oAddress = $this->SUT->getAddressObject();
        $this->assertValidAddress($oAddress);
    }

    /**
     * Test whether getPaymentDetailsObject returns an correct object of type PaymentDetailsType
     *
     * @dataProvider dataProviderPaymentDetails
     *
     * @param $aProvidedData
     * @param $aExpectedData
     */
    public function testGetPaymentDetailsObject($aProvidedData, $aExpectedData)
    {
        $oDataProvider = $this->getMockDataProvider($aProvidedData);
        $this->SUT->setDataProvider($oDataProvider);
        $oPaymentDetails = $this->SUT->getPaymentDetailsObject();
        $this->assertValidPaymentDetails($oPaymentDetails, $aExpectedData);
    }

    public function dataProviderPaymentDetails()
    {
        return array(
            // Simple data set with no extra costs
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 15.00,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 15.00,
                    'fExpectedItemTotal'            => 15.00,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            // Simple data set with no extra costs, but exacter amounts
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 15.001,
                    'fItemTotal'             => 15.001,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 15.001,
                    'fExpectedItemTotal'            => 15.001,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            // Simple data set with no extra costs, but exacter amounts
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 14.999,
                    'fItemTotal'             => 14.999,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 14.999,
                    'fExpectedItemTotal'            => 14.999,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            /**
             * Data set with order total greater than item total.
             * We expect the difference to be added to the handling costs
             */
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 15.01,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 15.01,
                    'fExpectedItemTotal'            => 15.00,
                    'fExpectedHandlingTotal'        => 0.01,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            /**
             * Data set with order total greater than item total.
             * We expect the difference not to be added to the handling costs as the difference is too small
             */
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 15.001,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 15.001,
                    'fExpectedItemTotal'            => 15.00,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            /**
             * Data set with order total smaller than item total.
             * We expect the difference to be added as discount as an item with negative amount to the Payment details
             */
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 14.00,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 14.00,
                    'fExpectedItemTotal'            => 14.00,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.0,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 2,
                ),
            ),
            /**
             * Data set with order total smaller than item total.
             * We expect the difference to not to be added to the shipping discount as the difference is too small
             */
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 14.999,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.00,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 14.999,
                    'fExpectedItemTotal'            => 15.00,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.00,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
            /**
             * Data set with TSProtection costs set
             * We expect the same amount in InsuranceTotal
             */
            array(
                // Provided data
                array(
                    'sCurrency'              => 'EUR',
                    'fOrderTotal'            => 15.98,
                    'fItemTotal'             => 15.00,
                    'fHandlingTotal'         => 0.00,
                    'fTSProtectionCosts'     => 0.98,
                    'fPaymentCosts'          => 0.00,
                    'fShippingCosts'         => 0.00,
                    'fShippingDiscount'      => 0.00,
                    'blIsPriceViewModeNetto' => true
                ),
                // Expected Data
                array(
                    'sExpectedCurrency'             => 'EUR',
                    'fExpectedOrderTotal'           => 15.98,
                    'fExpectedItemTotal'            => 15.00,
                    'fExpectedHandlingTotal'        => 0.00,
                    'fExpectedInsuranceTotal'       => 0.98,
                    'fExpectedShippingTotal'        => 0.00,
                    'fExpectedShippingDiscount'     => 0.00,
                    'fExpectedTaxTotal'             => 0.00,
                    'iExpectedNrPaymentDetailsItem' => 1,
                ),
            ),
        );
    }

    /**
     * test whether getSetExpressCheckoutRequestDetailsObject returns an object of type SetExpressCheckoutRequestDetailsType
     */
    public function testGetSetExpressCheckoutRequestDetailsObject()
    {
        $oSetExpressCheckoutRequestDetails = $this->SUT->getSetExpressCheckoutRequestDetailsObject();
        $this->assertInstanceOf(
            'PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType',
            $oSetExpressCheckoutRequestDetails
        );
        $this->assertValidCheckoutRequestDetails($oSetExpressCheckoutRequestDetails);
    }

    /**
     * test whether getDoExpressCheckoutPaymentRequestDetailsObject returns an object of type DoExpressCheckoutPaymentRequestDetailsType
     */
    public function testGetDoExpressCheckoutPaymentRequestDetailsObject()
    {
        $oDoExpressCheckoutPaymentRequestDetails =
            $this->SUT->getDoExpressCheckoutPaymentRequestDetailsObject("sPayerID", "sAuthToken");
        $this->assertValidPaymentRequestDetails($oDoExpressCheckoutPaymentRequestDetails);
    }

    /**
     * test whether getSetExpressCheckoutRequestObject returns an object of type SetExpressCheckoutRequestType
     */
    public function testGetSetExpressCheckoutRequestObject()
    {
        $oSetExpressCheckoutRequest = $this->SUT->getSetExpressCheckoutRequestObject();
        $this->assertValidSetExpressCheckoutRequest($oSetExpressCheckoutRequest);
    }

    /**
     * test whether getGetExpressCheckoutDetailsRequestObject returns an object of type GetExpressCheckoutDetailsRequestType
     */
    public function testGetGetExpressCheckoutDetailsRequestObject()
    {
        $oGetExpressCheckoutDetailsRequest = $this->SUT->getGetExpressCheckoutDetailsRequestObject("sAuthToken");
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType',
            $oGetExpressCheckoutDetailsRequest
        );
    }

    /**
     * test whether getDoExpressCheckoutPaymentRequestObject returns an object of type DoExpressCheckoutPaymentRequestType
     */
    public function testGetDoExpressCheckoutPaymentRequestObject()
    {
        $oDoExpressCheckoutPaymentRequest = $this->SUT->getDoExpressCheckoutPaymentRequestObject("sPayerID", "sAuthToken");
        $this->assertValidDoExpressCheckoutPaymentRequest($oDoExpressCheckoutPaymentRequest);
    }

    /**
     * test whether getRefundTransactionRequestObject returns an object of type RefundTransactionRequestType
     */
    public function testGetRefundTransactionRequestObject()
    {
        $aParams = array('sCurrency'      => 'EUR',
                         'dAmount'        => 15,
                         'sRefundType'    => 'Partial',
                         'sTransactionId' => 'sTransactionId',
                         'sMemo'          => 'You get ut soo much cheaper man');
        $oRefundTransactionRequest = $this->SUT->getRefundTransactionRequestObject($aParams);
        $this->assertValidRefundTransactionRequest($oRefundTransactionRequest);
    }

    /**
     * test whether getSetExpressCheckoutReqObject returns an object of type SetExpressCheckoutReq
     */
    public function testGetSetExpressCheckoutReqObject()
    {
        $oSetExpressCheckoutReq = $this->SUT->getSetExpressCheckoutReqObject();
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\SetExpressCheckoutReq',
            $oSetExpressCheckoutReq
        );
        $this->assertValidSetExpressCheckoutRequest($oSetExpressCheckoutReq->SetExpressCheckoutRequest);
    }

    /**
     * test whether getGetExpressCheckoutReqObject returns an object of type GetExpressCheckoutDetailsReq
     */
    public function testGetGetExpressCheckoutReqObject()
    {
        $oGetExpressCheckoutReq = $this->SUT->getGetExpressCheckoutReqObject("sAuthToken");
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\GetExpressCheckoutDetailsReq',
            $oGetExpressCheckoutReq
        );
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType',
            $oGetExpressCheckoutReq->GetExpressCheckoutDetailsRequest
        );
    }

    /**
     * test whether getDoExpressCheckoutReqObject returns an object of type DoExpressCheckoutPaymentReq
     */
    public function testGetDoExpressCheckoutReqObject()
    {
        $oDoExpressCheckoutReq = $this->SUT->getDoExpressCheckoutReqObject("sPayerID", "sAuthToken");
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\DoExpressCheckoutPaymentReq',
            $oDoExpressCheckoutReq
        );
        $this->assertValidDoExpressCheckoutPaymentRequest($oDoExpressCheckoutReq->DoExpressCheckoutPaymentRequest);
    }

    /**
     * tests whether getRefundTransactionReqObject returns an object of type RefundTransactionReq
     */
    public function testGetRefundTransactionReqObject()
    {
        $aParams = array('sCurrency'      => 'EUR',
                         'dAmount'        => 15,
                         'sRefundType'    => 'Partial',
                         'sTransactionId' => 'sTransactionId',
                         'sMemo'          => 'You get ut soo much cheaper man');
        $oRefundTransactionReq = $this->SUT->getRefundTransactionReqObject($aParams);
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\RefundTransactionReq',
            $oRefundTransactionReq
        );
        $this->assertValidRefundTransactionRequest($oRefundTransactionReq->RefundTransactionRequest);
    }

    /**
     * tests whether getPayPalServiceObject returns an object of type PayPalAPIInterfaceServiceService
     */
    public function testGetPayPalServiceObject()
    {
        $oPayPalService = $this->SUT->getPayPalServiceObject();
        $this->assertInstanceOf(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            $oPayPalService
        );
    }

    /**
     * tests whether createPaymentDetailsObjectFor returns an object of type PaymentDetailsType
     */
    public function testCreatePaymentDetailsObject()
    {
        $oBasketItem = $this->getBasketItemData();

        $oPaymentDetailsObject = $this->SUT->createPaymentDetailsObjectFor($oBasketItem);
        $this->assertInstanceOf(
            'PayPal\EBLBaseComponents\PaymentDetailsItemType',
            $oPaymentDetailsObject
        );
        $this->assertValidPaymentDetailsItem($oPaymentDetailsObject);
    }

    /**
     * Create and return a stubbed DataProvider
     *
     * @param $aProvidedData
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDataProvider($aProvidedData)
    {
        $oDataProvider = $this->getMock(
            'paypInstallmentsCheckoutDataProvider',
            array(
                '__call',
                'getBasketItemDataList',
                'getCurrency',
                'getItemTotal',
                'getOrderTotal',
                'getHandlingTotal',
                'getTSProtectionCosts',
                'getShippingCosts',
                'getShippingDiscount',
                'getShippingAddressData',
                '_isPriceViewModeNetto',
                'getProductVatTotal',
                'getCostsVatTotal',
            )
        );

        $oAddressData = $this->getAddressData();

        $oBasketItemData = $this->getBasketItemData();

        $oBasketItemDataList = array(
            $oBasketItemData
        );

        $oDataProvider->expects($this->any())->method('getBasketItemDataList')
            ->will($this->returnValue($oBasketItemDataList));

        $oDataProvider->expects($this->any())->method('getCurrency')
            ->will($this->returnValue($aProvidedData['sCurrency']));

        $oDataProvider->expects($this->any())->method('getItemTotal')
            ->will($this->returnValue($aProvidedData['fItemTotal']));

        $oDataProvider->expects($this->any())->method('getOrderTotal')
            ->will($this->returnValue($aProvidedData['fOrderTotal']));

        $oDataProvider->expects($this->any())->method('getHandlingTotal')
            ->will($this->returnValue($aProvidedData['fHandlingTotal']));

        $oDataProvider->expects($this->any())->method('getTSProtectionCosts')
            ->will($this->returnValue($aProvidedData['fTSProtectionCosts']));

        $oDataProvider->expects($this->any())->method('getPaymentCosts')
            ->will($this->returnValue($aProvidedData['fPaymentCosts']));

        $oDataProvider->expects($this->any())->method('getShippingCosts')
            ->will($this->returnValue($aProvidedData['fShippingCosts']));

        $oDataProvider->expects($this->any())->method('getShippingDiscount')
            ->will($this->returnValue($aProvidedData['fShippingDiscount']));

        $oDataProvider->expects($this->any())->method('getShippingAddressData')
            ->will($this->returnValue($oAddressData));

        $oDataProvider->expects($this->any())->method('_isPriceViewModeNetto')
            ->will($this->returnValue($aProvidedData['blIsPriceViewModeNetto']));

        return $oDataProvider;
    }

    /**
     * Create and return a stubbed out configuration object
     *
     * @return mixed
     */
    protected function getMockConfiguration()
    {
        $oConfiguration = $this->getMock(
            'paypInstallmentsConfiguration',
            array(
                '__call',
                'getSoapApiConfiguration'
            )
        );

        $oSoapApiConfiguration = array(
            'mode'            => "sandbox",
            'log.LogEnabled'  => true,
            'log.FileName'    => "filename",
            'log.LogLevel'    => "info",
            'acct1.UserName'  => "username",
            'acct1.Password'  => "password",
            'acct1.Signature' => "signature",
        );

        $oConfiguration->expects($this->any())->method('getSoapApiConfiguration')
            ->will($this->returnValue($oSoapApiConfiguration));

        return $oConfiguration;
    }

    /**
     * Prepare and return some Basket Item data, we can use for testing the SOAP API
     *
     * @return stdClass
     */
    protected function getBasketItemData()
    {
        $oBasketItemData = new stdClass();
        $oBasketItemData->sName = "Product 01";
        $oBasketItemData->fPrice = 30.00;
        $oBasketItemData->fUnitPrice = 15.00;
        $oBasketItemData->sCurrency = "EUR";
        $oBasketItemData->iQuantity = 2;
        $oBasketItemData->sItemCategory = "Physical";

        return $oBasketItemData;
    }

    /**
     * Prepare Address Data we will use as Input for the SOAP API
     *
     * @return stdClass
     */
    protected function getAddressData()
    {
        $oAddressData = new stdClass();
        $oAddressData->sFirstname = "John";
        $oAddressData->sLastname = "Johnson";
        $oAddressData->sStreet = "Somestreet 1";
        $oAddressData->sCity = "Cityburg";
        $oAddressData->sState = "That one State";
        $oAddressData->sZip = "79100";
        $oAddressData->sCountry = "Djermahnie";

        return $oAddressData;
    }

    /**
     * Make several assertions concerning oAddresses validity
     *
     * @param $oAddress - the Address we want to test
     */
    protected function assertValidAddress($oAddress)
    {
        $oAddressData = $this->getAddressData();
        $sName = $oAddressData->sFirstname . " " . $oAddressData->sLastname;

        $this->assertInstanceOf('PayPal\EBLBaseComponents\AddressType', $oAddress);
        $this->assertSame($oAddressData->sStreet, $oAddress->Street1);
        $this->assertSame($oAddressData->sCity, $oAddress->CityName);
        $this->assertSame($oAddressData->sCountry, $oAddress->Country);
        $this->assertSame($sName, $oAddress->Name);
        $this->assertSame($oAddressData->sZip, $oAddress->PostalCode);
        $this->assertSame($oAddressData->sState, $oAddress->StateOrProvince);
    }

    /**
     * Make sure that the passed PaymentDetailsItem is valid
     *
     * @param $oPaymentDetailsItem
     */
    protected function assertValidPaymentDetailsItem($oPaymentDetailsItem)
    {
        $oBasketItemData = $this->getBasketItemData();
        $this->assertInstanceOf('PayPal\EBLBaseComponents\PaymentDetailsItemType', $oPaymentDetailsItem);
        $this->assertSame($oPaymentDetailsItem->Name, $oBasketItemData->sName);
        $this->assertSame($oPaymentDetailsItem->Quantity, $oBasketItemData->iQuantity);
        $this->assertSame($oPaymentDetailsItem->Amount->value, 15.0);
        $this->assertSame($oPaymentDetailsItem->Amount->currencyID, "EUR");
        $this->assertSame($oPaymentDetailsItem->ItemCategory, $oBasketItemData->sItemCategory);
    }

    /**
     * Check whether the passed PaymentDetails Object is valid
     *
     * @param $oPaymentDetails
     * @param $aExpectedData
     */
    protected function assertValidPaymentDetails($oPaymentDetails, $aExpectedData)
    {
        $this->assertInstanceOf('PayPal\EBLBaseComponents\PaymentDetailsType', $oPaymentDetails);
        $this->assertSame(sizeof($oPaymentDetails->PaymentDetailsItem), $aExpectedData['iExpectedNrPaymentDetailsItem']);
        $this->assertSame($oPaymentDetails->PaymentAction, "Sale");

        $aTotals = array("ItemTotal", "OrderTotal", "HandlingTotal", "InsuranceTotal", "ShippingTotal", "TaxTotal");
        foreach ($aTotals as $sTotal) {
            $this->assertInstanceOf('PayPal\CoreComponentTypes\BasicAmountType', $oPaymentDetails->$sTotal);
            $this->assertSame($aExpectedData['sExpectedCurrency'], $oPaymentDetails->$sTotal->currencyID);
        }

        $this->assertSame($aExpectedData['fExpectedItemTotal'], $oPaymentDetails->ItemTotal->value, 'Item total matches expected value');
        $this->assertSame($aExpectedData['fExpectedOrderTotal'], $oPaymentDetails->OrderTotal->value, 'Order total matches expected value');
        $this->assertSame($aExpectedData['fExpectedHandlingTotal'], $oPaymentDetails->HandlingTotal->value, 'Handling total matches expected value');
        $this->assertSame($aExpectedData['fExpectedInsuranceTotal'], $oPaymentDetails->InsuranceTotal->value, 'Insurance total matches expected value');
        $this->assertSame($aExpectedData['fExpectedShippingTotal'], $oPaymentDetails->ShippingTotal->value, 'Shipping total matches expected value');
        $this->assertEquals($aExpectedData['fExpectedShippingDiscount'], $oPaymentDetails->ShippingDiscount->value, 'Shipping discount matches expected value');
        $this->assertEquals($aExpectedData['fExpectedTaxTotal'], $oPaymentDetails->TaxTotal->value, 'Tax total matches expected value');

        $this->assertValidPaymentDetailsItem($oPaymentDetails->PaymentDetailsItem[0]);
        $this->assertValidAddress($oPaymentDetails->ShipToAddress);
    }

    /**
     * make sure the passed FundingSourceDetails Object is valid
     *
     * @param $oFundingSourceDetails
     */
    protected function assertValidFundingSource($oFundingSourceDetails)
    {
        $this->assertInstanceOf('PayPal\EBLBaseComponents\FundingSourceDetailsType', $oFundingSourceDetails);
        $this->assertSame($oFundingSourceDetails->UserSelectedFundingSource, "Finance");
    }

    /**
     * check if the passed SetExpressCheckoutRequestDetails Object is a valid one
     *
     * @param $oSetExpressCheckoutRequestDetails
     */
    protected function assertValidCheckoutRequestDetails($oSetExpressCheckoutRequestDetails)
    {
        $aProvidedData = $this->dataProviderPaymentDetails();

        $this->assertInstanceOf(
            'PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType',
            $oSetExpressCheckoutRequestDetails
        );

        $this->assertSame(sizeof($oSetExpressCheckoutRequestDetails->PaymentDetails), 1);
        $this->assertValidPaymentDetails($oSetExpressCheckoutRequestDetails->PaymentDetails[0], $aProvidedData[0][1]);

        $this->assertValidFundingSource($oSetExpressCheckoutRequestDetails->FundingSourceDetails);

        $this->assertSame($oSetExpressCheckoutRequestDetails->LandingPage, "Billing");

        $this->assertEquals($oSetExpressCheckoutRequestDetails->AddressOverride, 0);
        $this->assertEquals($oSetExpressCheckoutRequestDetails->ReqConfirmShipping, 0);
        $this->assertEquals($oSetExpressCheckoutRequestDetails->NoShipping, 2);
        $this->assertEquals($oSetExpressCheckoutRequestDetails->AllowNote, 0);
    }

    /**
     * check whether the passed DoExpressCheckoutPaymentRequestDetails Object is valid
     *
     * @param $oDoExpressCheckoutPaymentRequestDetails
     */
    protected function assertValidPaymentRequestDetails($oDoExpressCheckoutPaymentRequestDetails)
    {
        $aProvidedData = $this->dataProviderPaymentDetails();

        $this->assertInstanceOf(
            'PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType',
            $oDoExpressCheckoutPaymentRequestDetails
        );
        $this->assertSame($oDoExpressCheckoutPaymentRequestDetails->PayerID, "sPayerID");
        $this->assertSame($oDoExpressCheckoutPaymentRequestDetails->Token, "sAuthToken");
        $this->assertSame($oDoExpressCheckoutPaymentRequestDetails->PaymentAction, "Sale");

        $this->assertSame(sizeof($oDoExpressCheckoutPaymentRequestDetails->PaymentDetails), 1);
        $this->assertValidPaymentDetails($oDoExpressCheckoutPaymentRequestDetails->PaymentDetails[0], $aProvidedData[0][1]);
    }

    /**
     * make sure the passed SetExpressCheckoutRequest is valid
     *
     * @param $oSetExpressCheckoutRequest
     */
    protected function assertValidSetExpressCheckoutRequest($oSetExpressCheckoutRequest)
    {
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\SetExpressCheckoutRequestType',
            $oSetExpressCheckoutRequest
        );
        $this->assertValidCheckoutRequestDetails($oSetExpressCheckoutRequest->SetExpressCheckoutRequestDetails);
    }

    /**
     * ensure the validity of the passed DoExpressCheckoutPaymentRequest Object
     *
     * @param $oDoExpressCheckoutPaymentRequest
     */
    protected function assertValidDoExpressCheckoutPaymentRequest($oDoExpressCheckoutPaymentRequest)
    {
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType',
            $oDoExpressCheckoutPaymentRequest
        );
        $this->assertValidPaymentRequestDetails($oDoExpressCheckoutPaymentRequest->DoExpressCheckoutPaymentRequestDetails);
    }

    /**
     * Make assertions in order to prove the passed RefundTransactionRequest Object's validity
     *
     * @param $oRefundTransactionRequest
     */
    protected function assertValidRefundTransactionRequest($oRefundTransactionRequest)
    {
        $this->assertInstanceOf(
            'PayPal\PayPalAPI\RefundTransactionRequestType',
            $oRefundTransactionRequest
        );
        $this->assertSame($oRefundTransactionRequest->RefundType, "Partial");
        $this->assertSame($oRefundTransactionRequest->TransactionID, "sTransactionId");
        $this->assertSame($oRefundTransactionRequest->Memo, "You get ut soo much cheaper man");
        $this->assertEquals($oRefundTransactionRequest->Amount->value, 15.0);
        $this->assertSame($oRefundTransactionRequest->Amount->currencyID, "EUR");
    }
}
