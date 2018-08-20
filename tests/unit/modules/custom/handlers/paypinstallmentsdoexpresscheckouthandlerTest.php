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
class paypInstallmentsDoExpressCheckoutHandlerTest extends OxidTestCase
{

    /**
     * @var paypInstallmentsDoExpressCheckoutPaymentHandler
     */
    protected $SUT;

    /**
     * prepare a Subject under Test
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock(
            'paypInstallmentsDoExpressCheckoutPaymentHandler',
            array('prepareObjectGenerator', '_getValidator'),
            array('AuthToken', 'PayerId')
        );
    }

    /**
     * make sure we react accordingly to a successfull response
     */
    public function testSuccessfullRequest()
    {
        $this->prepareUnchangedBasket();
        $oResponse = $this->getSuccessfullResponse();
        $this->mockRequest($this->getResponseReturnerService($oResponse));
        $this->SUT->doRequest();
    }

    /**
     * test, if the right kind of exception is thrown, if the response
     * contains no DoExpressCheckoutPaymentResponseDetails.
     */
    public function testMissingPaymentResponseDetails()
    {
        $this->prepareUnchangedBasket();
        $oResponse = $this->getSuccessfullResponse();
        $oResponse->DoExpressCheckoutPaymentResponseDetails = null;
        $this->mockRequest($this->getResponseReturnerService($oResponse));

        $this->setExpectedException(
            'paypInstallmentsDoExpressCheckoutParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_RESPONSE_DETAILS'
        );

        $this->SUT->doRequest();
    }

    /**
     * ensure the correct exception is thrown, if no payment info was provided from paypal
     */
    public function testMissingPaymentInfo()
    {
        $this->prepareUnchangedBasket();
        $oResponse = $this->getSuccessfullResponse();
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo = null;
        $this->mockRequest($this->getResponseReturnerService($oResponse));

        $this->setExpectedException(
            'paypInstallmentsDoExpressCheckoutParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO'
        );

        $this->SUT->doRequest();
    }

    /**
     * Make sure we encapsulate paypal exceptions
     */
    public function testPayPalException()
    {
        $this->prepareUnchangedBasket();
        $this->mockRequest($this->getExceptionThrowerService());

        $this->setExpectedException('paypInstallmentsException');

        $this->SUT->doRequest();
    }

    /**
     * we test if the correct exception is thrown, when no transaction ID is returned from paypal.
     */
    public function testMissingTransactionId()
    {
        $this->prepareUnchangedBasket();
        $oResponse = $this->getSuccessfullResponse();
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID = null;
        $this->mockRequest($this->getResponseReturnerService($oResponse));

        $this->setExpectedException(
            'paypInstallmentsDoExpressCheckoutParseException',
            'PAYP_ERR_PARSE_MISSING_TRANSACTION_ID'
        );

        $this->SUT->doRequest();
    }

    /**
     * we test if the correct exception is thrown, when an empty string is returned as the transaction ID
     */
    public function testEmptyTransactionId()
    {
        $this->prepareUnchangedBasket();
        $oResponse = $this->getSuccessfullResponse();
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID = '';
        $this->mockRequest($this->getResponseReturnerService($oResponse));

        $this->setExpectedException(
            'paypInstallmentsDoExpressCheckoutValidationException',
            'PAYP_ERR_VALIDATION_EMPTY_TRANSACTION_ID'
        );

        $this->SUT->doRequest();
    }

    /**
     * Make sure prepareObjectGenerator works without throwing an exception
     */
    public function testPrepareObjectGenerator()
    {
        // For some reason this does not work:
        // $oHandler = $this->getMock('paypInstallmentsDoExpressCheckoutPaymentHandler', array());
        $oHandler = new paypInstallmentsDoExpressCheckoutPaymentHandler("AuthToken", "PayerId");
        $oBasket = oxNew("oxBasket");
        $oHandler->setBasket($oBasket);
        $oObjectGenerator = $oHandler->prepareObjectGenerator();
        $this->assertInstanceOf('paypInstallmentsSdkObjectGenerator', $oObjectGenerator);
    }

    /**
     * Make certain an integrityLost Exception is thrown, if the oxbasket changed between
     * telling paypal about the order and finalizing it
     */
    public function testChangedBasketProducts()
    {
        // Mock Basket
        $oMockBasket = $this->getMock(
            'paypInstallmentsOxBasket',
            array('paypInstallments_GetBasketItemsFingerprint')
        );

        $oMockBasket->expects($this->any())->method('paypInstallments_GetBasketItemsFingerprint')
            ->will($this->returnValue("newFingerprint"));

        $this->SUT->setBasket($oMockBasket);

        //Mock Session
        $oMockedSession = $this->getMock(
            'paypInstallmentsOxSession',
            array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
        );

        $oMockedSession->expects($this->any())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->will($this->returnValue("oldFingerprint"));

        //Mock Validator
        $oMockedValidator = $this->getMock(
            'paypInstallmentsDoExpressCheckoutPaymentValidator',
            array('_getSession')
        );

        $oMockedValidator->expects($this->any())->method('_getSession')
            ->will($this->returnValue($oMockedSession));

        $this->SUT->expects($this->any())->method('_getValidator')
            ->will($this->returnValue($oMockedValidator));

        $this->setExpectedException(
            'paypInstallmentsBasketIntegrityLostException',
            'PAYP_ERR_VALIDATION_BASKET_INTEGRITY_LOST'
        );

        $oResponse = $this->getSuccessfullResponse();
        $this->mockRequest($this->getResponseReturnerService($oResponse));
        $this->SUT->doRequest();
    }

    /**
     * prepare a mocked response, representing a successfull call.
     */
    private function getSuccessfullResponse()
    {
        $oResponse = $this->getMock('PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType', array());
        $oResponse->Version = paypInstallmentsConfiguration::getServiceVersion();
        $oResponse->Ack = paypInstallmentsConfiguration::getResponseAckSuccess();
        $oResponse->Timestamp = '2015-01-01T01:01:01Z';
        $oResponse->DoExpressCheckoutPaymentResponseDetails =
            $this->getMock('PayPal\EBLBaseComponents\DoExpressCheckoutPaymentResponseDetailsType', array());
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo = array(
            $this->getMock('PayPal\EBLBaseComponents\PaymentInfoType', array())
        );
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID = "TransactionID";
        $oResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->PaymentStatus = "Completed";

        return $oResponse;
    }

    /**
     * Mock the PayPal Service in a way, that it returns the response we want to test
     *
     * @param $oResponse
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponseReturnerService($oResponse)
    {
        $oMockedService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('DoExpressCheckoutPayment')
        );
        $oMockedService->expects($this->any())->method('DoExpressCheckoutPayment')
            ->will($this->returnValue($oResponse));

        return $oMockedService;
    }

    /**
     * Mock the PayPal service so that we can test how we handle paypal exceptions
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getExceptionThrowerService()
    {
        $oMockedService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('DoExpressCheckoutPayment')
        );
        $oMockedService->expects($this->any())->method('DoExpressCheckoutPayment')
            ->will($this->throwException(new Exception("Some Kind of error happend")));

        return $oMockedService;
    }

    /**
     * Set up the entire mocked objectGenerator Hierarchy
     *
     * @param $oMockedService - the service we want the getservice call to return
     */
    private function mockRequest($oMockedService)
    {

        //Set up basic handler mocking
        $oObjectGenerator = $this->getMock(
            'paypInstallmentsSdkObjectGenerator',
            array(
                '__call',
                'getPayPalServiceObject',
                'getDoExpressCheckoutReqObject'
            )
        );

        $oMockedRequest = $this->getMock(
            '\PayPal\PayPalAPI\DoExpressCheckoutPaymentReq',
            array()
        );

        $oObjectGenerator->expects($this->any())->method('getDoExpressCheckoutReqObject')
            ->will($this->returnValue($oMockedRequest));

        $oObjectGenerator->expects($this->any())->method('getPayPalServiceObject')
            ->will($this->returnValue($oMockedService));

        $this->SUT->expects($this->any())->method('prepareObjectGenerator')
            ->will($this->returnValue($oObjectGenerator));
    }

    /**
     * Mock basket and session so that no exception gets thrown by the handler
     */
    private function prepareUnchangedBasket()
    {
        // Mock Basket
        $sFingerprint = "FIngerprinty";

        $oMockBasket = $this->getMock(
            'paypInstallmentsOxBasket',
            array('paypInstallments_GetBasketItemsFingerprint')
        );

        $oMockBasket->expects($this->any())->method('paypInstallments_GetBasketItemsFingerprint')
            ->will($this->returnValue($sFingerprint));

        $this->SUT->setBasket($oMockBasket);

        //Mock Session
        $oMockedSession = $this->getMock(
            'paypInstallmentsOxSession',
            array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
        );

        $oMockedSession->expects($this->any())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->will($this->returnValue($sFingerprint));

        //Mock Validator
        $oMockedValidator = $this->getMock(
            'paypInstallmentsDoExpressCheckoutPaymentValidator',
            array('_getSession')
        );

        $oMockedValidator->expects($this->any())->method('_getSession')
            ->will($this->returnValue($oMockedSession));

        $this->SUT->expects($this->any())->method('_getValidator')
            ->will($this->returnValue($oMockedValidator));
    }
}
