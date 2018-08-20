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
 * Class paypInstallmentsRefundHandlerTest
 * Tests for paypInstallmentsRefundHandlerTest model.
 *
 * @see paypInstallmentsRefundHandlerTest
 */
class paypInstallmentsRefundHandlerTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var paypInstallmentsRefundHandler
     */
    protected $SUT;

    /**
     * @inheritDoc
     *
     * Set SUT state before test.
     * Import data to test loading methods
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock('paypInstallmentsRefundHandler', array('__call'), array(array()));
    }

    /**
     * method to enable calling of protected functions within object to test
     *
     * @param       $obj
     * @param       $name
     * @param array $args
     *
     * @return mixed
     */
    public static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    protected function _getTestObjects($sObjectsFor = null)
    {
        //set up some test data
        $sTransactionId = 'TRANSACTION_ID' . rand(100, 999);
        $sMemo = 'MEMO' . rand(100, 999);
        $sRefundId = 'REFUND_ID' . rand(100, 999);
        $dAmount = 10;
        $sCurrency = 'EUR';
        $sStatus = 'Instant';
        $sResponse = 'RESPONSE' . rand(100, 999);
        $sDateTime = '2015-09-09T12:04:28Z';

        //set up "mocked" stuff
        $oRequest = new PayPal\PayPalAPI\RefundTransactionReq();
        $oRequest->RefundTransactionRequest = new StdClass();
        $oRequest->RefundTransactionRequest->TransactionID = $sTransactionId;
        $oRequest->RefundTransactionRequest->Memo = $sMemo;

        $oParser = $this->getMock(
            'paypInstallmentsRefundParser', array(
                '__call',
                'getRefundTransactionId',
                'getTotalRefundedAmountValue',
                'getTotalRefundedAmountCurrency',
                'getRefundStatus',
                'getTimestamp',
                'getResponse',
            )
        );
        $oParser->expects($this->any())
            ->method('getRefundTransactionId')
            ->will($this->returnValue($sRefundId));

        $oParser->expects($this->any())
            ->method('getTotalRefundedAmountValue')
            ->will($this->returnValue($dAmount));
        $oParser->expects($this->any())
            ->method('getTotalRefundedAmountCurrency')
            ->will($this->returnValue($sCurrency));

        $oStatusObject = new StdClass();
        $oStatusObject->RefundStatus = $sStatus;
        $oParser->expects($this->any())
            ->method('getRefundInfo')
            ->will($this->returnValue($oStatusObject));

        $oParser->expects($this->any())
            ->method('getRefundStatus')
            ->will($this->returnValue($sStatus));

        $oParser->expects($this->any())
            ->method('getTimestamp')
            ->will($this->returnValue($sDateTime));

        $oParser->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($sResponse));

        $oRefund = new paypInstallmentsRefund();
        $aTestValues['transactionid'] = $sTransactionId;
        $aTestValues['memo'] = $sMemo;
        $aTestValues['refundid'] = $sRefundId;
        $aTestValues['amount'] = $dAmount;
        $aTestValues['currency'] = $sCurrency;
        $aTestValues['status'] = $sStatus;
        $aTestValues['datetime_created'] = date('Y-m-d h:i:s', strtotime($sDateTime));
        $aTestValues['response'] = serialize($sResponse);
        $oRefund->assign($aTestValues);

        $oResponse_invalid = new PayPal\PayPalAPI\RefundTransactionResponseType();
        $oResponse_invalid->RefundTransactionID = null;
        $oResponse_invalid->NetRefundAmount = null;
        $oResponse_invalid->FeeRefundAmount = null;
        $oResponse_invalid->GrossRefundAmount = null;
        $oResponse_invalid->TotalRefundedAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse_invalid->TotalRefundedAmount->currencyID = 'EUR';
        $oResponse_invalid->TotalRefundedAmount->value = 0.00;
        $oResponse_invalid->RefundInfo = new PayPal\EBLBaseComponents\RefundInfoType();
        $oResponse_invalid->RefundInfo->RefundStatus = 'None';
        $oResponse_invalid->RefundInfo->PendingReason = 'none';
        $oResponse_invalid->ReceiptData = null;
        $oResponse_invalid->MsgSubID = null;
        $oResponse_invalid->Timestamp = $sDateTime;
        $oResponse_invalid->Ack = 'Failure';
        $oResponse_invalid->CorrelationID = '36989928a0316';

        $oError_invalid = new PayPal\EBLBaseComponents\ErrorType();
        $oError_invalid->ShortMessage = 'Internal Error';
        $oError_invalid->LongMessage = 'Internal Error';
        $oError_invalid->ErrorCode = '10001';
        $oError_invalid->SeverityCode = 'Error';
        $oError_invalid->ErrorParameters = null;
        $oResponse_invalid->Errors[] = $oError_invalid;

        $oResponse_invalid->Version = '124.0';
        $oResponse_invalid->Build = '000000';

        //----------------------------------------------------------------------------------------------

        $oResponse_valid = new PayPal\PayPalAPI\RefundTransactionResponseType();
        $oResponse_valid->RefundTransactionID = $sRefundId;

        $oResponse_valid->NetRefundAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse_valid->NetRefundAmount->currencyID = $sCurrency;
        $oResponse_valid->NetRefundAmount->value = $dAmount;

        $oResponse_valid->FeeRefundAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse_valid->FeeRefundAmount->currencyID = $sCurrency;
        $oResponse_valid->FeeRefundAmount->value = $dAmount;

        $oResponse_valid->GrossRefundAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse_valid->GrossRefundAmount->currencyID = $sCurrency;
        $oResponse_valid->GrossRefundAmount->value = $dAmount;

        $oResponse_valid->TotalRefundedAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse_valid->TotalRefundedAmount->currencyID = $sCurrency;
        $oResponse_valid->TotalRefundedAmount->value = $dAmount;

        $oResponse_valid->RefundInfo = new PayPal\EBLBaseComponents\RefundInfoType();
        $oResponse_valid->RefundInfo->RefundStatus = $sStatus;
        $oResponse_valid->RefundInfo->PendingReason = 'none';

        $oResponse_valid->ReceiptData = null;
        $oResponse_valid->MsgSubID = null;
        $oResponse_valid->Timestamp = $sDateTime;
        $oResponse_valid->Ack = 'Success';
        $oResponse_valid->CorrelationID = '36989928a0316';
        $oResponse_valid->Version = '124.0';
        $oResponse_valid->Build = '000000';

        if ($sObjectsFor == 'doRequest_valid') {
            $oRefund->assign(array('response' => serialize($oResponse_valid)));
        }

        return array(
            'oRefund'           => $oRefund,
            'oParser'           => $oParser,
            'oRequest'          => $oRequest,
            'oResponse_invalid' => $oResponse_invalid,
            'oResponse_valid'   => $oResponse_valid,
        );
    }

    public function test_getPayPalServiceObject()
    {
        $oSdk = $this->getMock('paypInstallmentsSdkObjectGenerator', array('__call', 'getPayPalServiceObject'));
        $oSdk->expects($this->any())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue(new PayPal\Service\PayPalAPIInterfaceServiceService()));

        $this->assertInstanceOf('PayPal\Service\PayPalAPIInterfaceServiceService', self::callMethod($this->SUT, '_getPayPalServiceObject', array($oSdk)));
    }

    public function test_getRequestObject()
    {
        $oSdk = $this->getMock('paypInstallmentsSdkObjectGenerator', array('__call'));

        $this->assertInstanceOf('PayPal\PayPalAPI\RefundTransactionReq', self::callMethod($this->SUT, '_getRequestObject', array($oSdk, array())));
    }

    public function test_throwRefundTransactionException()
    {
        $this->setExpectedException('paypInstallmentsRefundTransactionException', 'MESSAGE');
        self::callMethod($this->SUT, '_throwRefundTransactionException', array('MESSAGE'));
    }

    /**
     * @expectedException paypInstallmentsRefundRequestParameterValidationException
     * @expectedExceptionMessage PAYP_ERR_VALIDATION_REFUND_AMOUNT_MISSING_IN_PARTIAL_REFUND
     */
    public function testDoRequest_invalid()
    {
        $aTestObjects = $this->_getTestObjects();

        $sRefundType = 'Partial';
        $sTransactionId = 'TID';

        $oPayPalService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('RefundTransaction',)
        );

        //backup current state of SUT, because we need to mock another function for this test
        $oBakSUT = clone $this->SUT;
        $this->SUT = $this->getMock(
            'paypInstallmentsRefundHandler',
            array('__call', '_getPayPalServiceObject'),
            array($sTransactionId, $sRefundType)
        );

        $this->SUT->expects($this->any())
            ->method('_getPayPalServiceObject')
            ->will($this->returnValue($oPayPalService));

        $oPayPalService->expects($this->any())
            ->method('RefundTransaction')
            ->will($this->returnValue($aTestObjects['oResponse_invalid']));

        //$this->setExpectedException('paypInstallmentsNoAckSuccessException', 'Internal Error');

        $this->SUT->doRequest();

        //writing back SUT
        $this->SUT = $oBakSUT;
    }

    public function testDoRequest_errorOnRequest()
    {
        $sRefundType = 'Full';
        $sTransactionId = 'TID';

        $oPayPalService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('RefundTransaction', $sRefundType)
        );

        //backup current state of SUT, because we need to mock another function for this test
        $oBakSUT = clone $this->SUT;
        $this->SUT = $this->getMock(
            'paypInstallmentsRefundHandler',
            array('__call', '_getPayPalServiceObject'),
            array($sTransactionId, $sRefundType)
        );

        $this->SUT->expects($this->any())
            ->method('_getPayPalServiceObject')
            ->will($this->returnValue($oPayPalService));

        $oEx = new paypInstallmentsRefundTransactionException();
        $oEx->setMessage('TEST');

        $oPayPalService->expects($this->any())
            ->method('RefundTransaction')->will($this->throwException($oEx));

        $this->setExpectedException('paypInstallmentsRefundTransactionException', 'TEST');

        $this->SUT->doRequest();

        //writing back SUT
        $this->SUT = $oBakSUT;
    }

    public function testDoRequest_valid()
    {
        $aTestObjects = $this->_getTestObjects('doRequest_valid');

        $sTransactionId = 'TID';
        $sRefundType = 'Full';
        $sCurrency = 'EUR';
        $sMemo = 'Some remarks';

        $oPayPalService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('RefundTransaction',)
        );

        //backup current state of SUT, because we need to mock another function for this test
        $oBakSUT = clone $this->SUT;
        $this->SUT = $this->getMock(
            'paypInstallmentsRefundHandler',
            array('__call', '_getPayPalServiceObject', '_getRequestObject'),
            array($sTransactionId, $sRefundType, $sCurrency, $sMemo)
        );

        $this->SUT->expects($this->any())
            ->method('_getPayPalServiceObject')
            ->will($this->returnValue($oPayPalService));

        $this->SUT->expects($this->any())
            ->method('_getRequestObject')
            ->will($this->returnValue($aTestObjects['oRequest']));

        $oPayPalService->expects($this->any())
            ->method('RefundTransaction')
            ->will($this->returnValue($aTestObjects['oResponse_valid']));


        //get refund from code to test
        $aActualRefundData = $this->SUT->doRequest();

        $oExpectedRefundData = $aTestObjects['oRefund'];

        //check if both objects have the same values
        //$oRefund === $oRefundToTest not possible because $oRefundToTest is saved to DB and because of that has generated OXID
        $this->assertEquals($oExpectedRefundData->getTransactionId(), $aActualRefundData['TransactionId']);
        $this->assertEquals($oExpectedRefundData->getRefundId(), $aActualRefundData['RefundId']);
        $this->assertEquals($oExpectedRefundData->getMemo(), $aActualRefundData['Memo']);
        $this->assertEquals($oExpectedRefundData->getAmount(), $aActualRefundData['GrossRefundAmount']);
        $this->assertEquals($oExpectedRefundData->getCurrency(), $aActualRefundData['GrossRefundAmountCurrency']);
        $this->assertEquals($oExpectedRefundData->getStatus(), $aActualRefundData['Status']);
        $this->assertEquals($oExpectedRefundData->getResponse(), $aActualRefundData['Response']);
    }

    public function testDoRequest_throwsExpectedException_forPayPalError10001()
    {
        $this->setExpectedException('paypInstallmentsRefundTransactionException', 'PAYP_INSTALLMENTS_REFUND_ERR_10001');

        $aTestObjects = $this->_getTestObjects('doRequest_valid');

        $sTransactionId = 'TID';
        $sRefundType = 'Full';
        $sCurrency = 'EUR';
        $sMemo = 'Some remarks';

        $oException = new Exception('message', 10001);

        $oPayPalService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('RefundTransaction',)
        );
        $oPayPalService
            ->expects($this->once())
            ->method('RefundTransaction')
            ->will($this->throwException($oException));

        $oSUT = $this->getMock(
            'paypInstallmentsRefundHandler',
            array('__call', '_getPayPalServiceObject', '_getRequestObject'),
            array($sTransactionId, $sRefundType, $sCurrency, $sMemo)
        );

        $oSUT->expects($this->any())
            ->method('_getPayPalServiceObject')
            ->will($this->returnValue($oPayPalService));

        $oSUT->expects($this->any())
            ->method('_getRequestObject')
            ->will($this->returnValue($aTestObjects['oRequest']));

        $oPayPalService->expects($this->any())
            ->method('RefundTransaction')
            ->will($this->returnValue($aTestObjects['oResponse_valid']));


        // do request
        $aActualRefundData = $oSUT->doRequest();
    }
}
