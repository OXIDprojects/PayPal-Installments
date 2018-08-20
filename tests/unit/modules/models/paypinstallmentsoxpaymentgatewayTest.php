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
 * Class paypInstallments_oxPaymentGatewayTest
 *
 * @desc Unit tests for paypInstallmentsOxPaymentGateway.
 */
class paypInstallments_oxPaymentGatewayTest extends OxidtestCase
{

    /**
     * this is needed to test protected methods
     *
     * @param       $obj
     * @param       $name
     * @param array $args
     *
     * @return mixed
     */
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public function testSetGetLogger()
    {
        $oSubjectUnderTest = new paypInstallmentsOxPaymentGateway();
        $oLogger1 = $oSubjectUnderTest->getLogger();
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $oLogger1, 'Logger is created automatically.');
        $this->assertSame($oLogger1, $oSubjectUnderTest->getLogger(), 'Logger is reused later.');

        $oLogger2 = new \Psr\Log\NullLogger();
        $oSubjectUnderTest->setLogger($oLogger2);
        $this->assertSame($oLogger2, $oSubjectUnderTest->getLogger(), 'Use optional logger.');
    }

    public function testExecutePayment()
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('_construct'))
            ->getMock();

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->never())
            ->method('_paypInstallments_paymentExceptionHandler');

        $this->assertTrue($oSubjectUnderTest->executePayment($dAmount, $oOrder));
    }

    /**
     * @param $blExpectedResult
     * @param $mBasket
     *
     * @dataProvider testGetBasketFromSessionDataProvider
     */
    public function testGetBasketFromSession($blExpectedResult, $mBasket)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('getBasket'))
            ->getMock();
        $oSession->expects($this->atLeastOnce())
            ->method('getBasket')
            ->will($this->returnValue($mBasket));

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->any())
            ->method('setBasket')
            ->with($mBasket);
        $oHandler->expects($this->any())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($oSession));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');

        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder));
    }

    /**
     * @return array array(array($blExpectedResult, $mBasket), ...)
     */
    public function testGetBasketFromSessionDataProvider()
    {
        $mBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('_construct'))
            ->getMock();

        return array(
            array(true, $mBasket),
            array(false, new stdClass()),
            array(false, array()),
            array(false, null),
            array(false, true),
            array(false, false),
        );
    }

    /**
     * @param $blExpectedResult
     * @param $mToken
     *
     * @dataProvider testGetTokenFromSessionDataProvider
     */
    public function testGetTokenFromSession($blExpectedResult, $mToken)
    {
        $dAmount = 'test-pa-amount';
        $sPayerId = 'test-pa-payer-id';
        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey'))
            ->getMock();
        $oSession->expects($this->any())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->with('Token')
            ->will($this->returnValue($mToken));

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($mToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->any())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->any())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($oSession));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');

        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder));
    }

    /**
     * @return array array(array($blExpectedResult, $mToken) ,..)
     */
    public function testGetTokenFromSessionDataProvider()
    {
        return array(
            array(true, 1),
            array(true, 'test-pa-token'),
            array(false, 0),
            array(false, ''),
            array(false, false),
            array(false, null),
            array(false, array()),
        );
    }

    /**
     * @param $blExpectedResult
     * @param $mPayerId
     *
     * @dataProvider testGetPayerIdDataProvider
     */
    public function testGetPayerId($blExpectedResult, $mPayerId)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey'))
            ->getMock();
        $oSession->expects($this->any())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->with('PayerId')
            ->will($this->returnValue($mPayerId));

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $mPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->any())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->any())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($oSession));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');

        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder));
    }

    /**
     * @return array array(array($blExpectedResult, $sPayerId) ,..)
     */
    public function testGetPayerIdDataProvider()
    {
        return array(
            array(true, 1),
            array(true, 'test-pa-payer-id'),
            array(false, 0),
            array(false, ''),
            array(false, false),
            array(false, null),
            array(false, array()),
        );
    }

    /**
     * @param $blExpectedResult
     * @param $mFinancingDetails
     *
     * @dataProvider testGetFinancingDetailsDataProvider
     */
    public function testGetFinancingDetails($blExpectedResult, $mFinancingDetails)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';
        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey'))
            ->getMock();
        $oSession->expects($this->any())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->with('FinancingDetails')
            ->will($this->returnValue($mFinancingDetails));

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->any())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->any())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($oSession));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');

        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder));
    }

    /**
     * @return array array(array($blExpectedResult, $sPayerId) ,..)
     */
    public function testGetFinancingDetailsDataProvider()
    {
        return array(
            array(true, new paypInstallmentsFinancingDetails()),
            array(false, new stdClass()),
            array(false, 'test-pa-financing-details'),
            array(false, 1),
            array(false, 0),
            array(false, ''),
            array(false, false),
            array(false, null),
            array(false, array()),
        );
    }

    public function testGetOrderNr()
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('_construct'))
            ->getMock();

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetOrderNr'))
            ->getMock();
        $oSession->expects($this->any())
            ->method('paypInstallmentsGetOrderNr')
            ->will($this->returnValue($sOrderNr));

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($oSession));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_persistOrderData')
            ->with($oOrder, $aDataToBePersisted);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->never())
            ->method('_paypInstallments_paymentExceptionHandler');

        $oSubjectUnderTest->executePayment($dAmount, $oOrder);
    }

    /**
     * @param bool   $blExpectedResult
     * @param mixed  $mExpectedPayedTimestamp
     * @param string $sPaymentStatus
     * @param bool   $blOrderSaved
     * @param string $sConditionDesc
     *
     * @dataProvider testPersistOrderDataDataProvider
     */
    public function testPersistOrderData($blExpectedResult, $mExpectedPayedTimestamp, $sPaymentStatus, $blOrderSaved, $sConditionDesc)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $mResponse = 'test-pa-response';
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );
        $aDataToBePersisted = array(
            'OrderId'          => $sOrderId,
            'OrderNr'          => $sOrderNr,
            'Timestamp'        => $sTimeStamp,
            'TransactionId'    => $sTransactionId,
            'PaymentStatus'    => $sPaymentStatus,
            'Response'         => $mResponse,
            'FinancingDetails' => $mFinancingDetails,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->setMethods(array('getId', 'save'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));
        $oOrder->expects($this->once())
            ->method('save')
            ->will($this->returnValue($blOrderSaved));

        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_persistPaymentData')
            ->with($aDataToBePersisted);
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));

        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');/**/
        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder), $sConditionDesc);

        $this->assertSame($sTransactionId, $oOrder->getFieldData('oxtransid'));
        $this->assertSame($sOrderNr, $oOrder->getFieldData('oxordernr'));
        $this->assertSame($mExpectedPayedTimestamp, $oOrder->getFieldData('oxpaid'), $sConditionDesc);
    }

    /**
     * @return array array(array($blExpectedResult, $mExpectedPayedTimestamp, $sPaymentStatus, $blOrderSaved, $sConditionDesc), ...)
     */
    public function testPersistOrderDataDataProvider()
    {
        return array(
            array(true, 'test-pa-timestamp', 'Completed', true, 'Order completed and saved to DB.'),
            array(true, null, 'test-pa-status', true, 'Order not completed but saved to DB.'),
            array(false, 'test-pa-timestamp', 'Completed', false, 'Order completed but not saved to DB.'),
            array(false, null, 'test-pa-status', false, 'Order not completed and not saved to DB.'),
        );
    }

    /**
     * @param bool   $blExpectedResult
     * @param bool   $blPaymentSaved
     * @param string $sConditionDesc
     *
     * @dataProvider testPersistPaymentDataDataProvider
     */
    public function testPersistPaymentData($blExpectedResult, $blPaymentSaved, $sConditionDesc)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';
        $sCurrency = 'test-pa-currency';
        $sFinancingFeePrice = 'test-pa-fee-price';
        $sFinancingTotalPrice = 'test-pa-total-price';
        $sFinancingMonthlyPrice = 'test-pa-monthly-price';
        $sFinancingTerm = 'test-pa-term';

        $oFeePrice = $this->getMock('oxPrice', array('getBruttoPrice'));
        $oFeePrice->expects($this->once())
            ->method('getBruttoPrice')
            ->will($this->returnValue($sFinancingFeePrice));
        $oTotalPrice = $this->getMock('oxPrice', array('getBruttoPrice'));
        $oTotalPrice->expects($this->once())
            ->method('getBruttoPrice')
            ->will($this->returnValue($sFinancingTotalPrice));
        $oMonthlyPrice = $this->getMock('oxPrice', array('getBruttoPrice'));
        $oMonthlyPrice->expects($this->once())
            ->method('getBruttoPrice')
            ->will($this->returnValue($sFinancingMonthlyPrice));

        $mFinancingDetails = $this->getMockBuilder('paypInstallmentsFinancingDetails')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getFinancingCurrency',
                    'getFinancingFeeAmount',
                    'getFinancingTotalCost',
                    'getBruttoPrice',
                    'getFinancingMonthlyPayment',
                    'getFinancingTerm'
                )
            )
            ->getMock();
        $mFinancingDetails->expects($this->once())
            ->method('getFinancingCurrency')
            ->will($this->returnValue($sCurrency));
        $mFinancingDetails->expects($this->once())
            ->method('getFinancingFeeAmount')
            ->will($this->returnValue($oFeePrice));
        $mFinancingDetails->expects($this->once())
            ->method('getFinancingTotalCost')
            ->will($this->returnValue($oTotalPrice));
        $mFinancingDetails->expects($this->once())
            ->method('getFinancingMonthlyPayment')
            ->will($this->returnValue($oMonthlyPrice));
        $mFinancingDetails->expects($this->once())
            ->method('getFinancingTerm')
            ->will($this->returnValue($sFinancingTerm));


        $sOrderId = 'test-pa-order-id';
        $sOrderNr = 'test-pa-order-nb';
        $sTimeStamp = 'test-pa-timestamp';
        $sTransactionId = 'test-pa-transaction-id';
        $sPaymentStatus = 'test-pa-payment-status';
        $mResponse = new \PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType();
        $aParsedResponseData = array(
            'Timestamp'     => $sTimeStamp,
            'TransactionId' => $sTransactionId,
            'PaymentStatus' => $sPaymentStatus,
            'Response'      => $mResponse,
        );

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($aParsedResponseData));

        $oPaymentData = $this->getMockBuilder('paypInstallmentsPaymentData')
            ->setMethods(
                array(
                    'save',
                    'setOrderId',
                    'setTransactionId',
                    'setStatus',
                    'setResponse',
                    'setFinancingFeeCurrency',
                    'setFinancingFeeAmount',
                    'setFinancingTotalCostAmount',
                    'setFinancingMonthlyPaymentAmount',
                    'setFinancingTerm'
                )
            )
            ->getMock();
        $oPaymentData->expects($this->once())
            ->method('setOrderId')
            ->with($sOrderId);
        $oPaymentData->expects($this->once())
            ->method('setTransactionId')
            ->with($sTransactionId);
        $oPaymentData->expects($this->once())
            ->method('setStatus')
            ->with($sPaymentStatus);
        $oPaymentData->expects($this->once())
            ->method('setResponse')
            ->with($mResponse);
        $oPaymentData->expects($this->once())
            ->method('setFinancingFeeCurrency')
            ->with($sCurrency);
        $oPaymentData->expects($this->once())
            ->method('setFinancingFeeAmount')
            ->with($sFinancingFeePrice);
        $oPaymentData->expects($this->once())
            ->method('setFinancingTotalCostAmount')
            ->with($sFinancingTotalPrice);
        $oPaymentData->expects($this->once())
            ->method('setFinancingMonthlyPaymentAmount')
            ->with($sFinancingMonthlyPrice);
        $oPaymentData->expects($this->once())
            ->method('setFinancingTerm')
            ->with($sFinancingTerm);
        $oPaymentData->expects($this->once())
            ->method('save')
            ->will($this->returnValue($blPaymentSaved));
        oxUtilsObject::setClassInstance('paypInstallmentsPaymentData', $oPaymentData);

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    'getSession',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    //'_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                    '_paypInstallments_paymentExceptionHandler',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getOrderNrFromSession')
            ->will($this->returnValue($sOrderNr));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_persistOrderData');
        $oSubjectUnderTest->expects($this->any())
            ->method('_paypInstallments_CallExecutePaymentParent')
            ->will($this->returnValue(true));

        $oSubjectUnderTest->expects($this->exactly((int) !$blExpectedResult))
            ->method('_paypInstallments_paymentExceptionHandler');/**/
        $this->assertSame($blExpectedResult, $oSubjectUnderTest->executePayment($dAmount, $oOrder), $sConditionDesc);

        oxUtilsObject::setClassInstance('paypInstallmentsPaymentData', null);
    }

    /**
     * @return array array(array($blExpectedResult, $blPaymentSaved, $sConditionDesc), ...)
     */
    public function testPersistPaymentDataDataProvider()
    {
        return array(
            array(true, true, 'Payment saved to DB.'),
            array(false, false, 'Payment not saved to DB.'),
        );
    }

    /**
     * @param $iExpectedErrorCode
     * @param $sExpectedMessage
     * @param $aExpectedContext
     * @param $oException
     *
     * @dataProvider testExceptionHandlerDataProvider
     */
    public function testExceptionHandler($iExpectedErrorCode, $sExpectedMessage, $aExpectedContext, $oException)
    {
        $dAmount = 'test-pa-amount';
        $sToken = 'test-pa-token';
        $sPayerId = 'test-pa-payer-id';

        $mFinancingDetails = new paypInstallmentsFinancingDetails();
        $sOrderId = 'test-pa-order-id';

        $oLogger = $this->getMockBuilder('\Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info', 'error'))
            ->getMock();
        $oLogger->expects($this->once())
            ->method('error')
            ->with($sExpectedMessage, $aExpectedContext);

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oBasket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('_construct'))
            ->getMock();

        $oHandler = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentHandler')
            ->setConstructorArgs(array($sToken, $sPayerId))
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $oHandler->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);
        $oHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->throwException($oException));

        /** @var paypInstallmentsOxPaymentGateway|oxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLogger',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getPayPalPayerIdFromSession',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getDoExpressCheckoutPaymentHandler',
                    '_paypInstallments_getOrderNrFromSession',
                    '_paypInstallments_persistOrderData',
                    '_paypInstallments_persistPaymentData',
                    '_paypInstallments_CallExecutePaymentParent',
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue($oBasket));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sToken));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPayPalPayerIdFromSession')
            ->will($this->returnValue($sPayerId));
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('_paypInstallments_getDoExpressCheckoutPaymentHandler')
            ->will($this->returnValue($oHandler));

        $this->assertFalse($oSubjectUnderTest->executePayment($dAmount, $oOrder));
        $this->assertSame($iExpectedErrorCode, $oSubjectUnderTest->getLastErrorNo());
    }

    /**
     * @return array array(array($iExpectedErrorCode, $sExpectedMessage, $aExpectedContext, $oException), ...)
     */
    public function testExceptionHandlerDataProvider()
    {
        $sMessage = 'paypInstallmentsOxPaymentGateway::_paypInstallments_doExpressCheckout DoExpressCheckout failed';
        $oExceptionSession = new paypInstallmentsCorruptSessionException();
        $oExceptionInstallments = new paypInstallmentsException();
        $oException = new oxException();

        return array(
            array(
                1101,
                $sMessage,
                array('Exception' => $oExceptionSession),
                $oExceptionSession,
            ),
            array(
                1100,
                $sMessage,
                array('Exception' => $oExceptionInstallments),
                $oExceptionInstallments,
            ),
            array(
                1100,
                $sMessage,
                array('Exception' => $oException),
                $oException,
            ),
        );
    }

    public function testIsPayPalInstallmentsPayment_returnsTrue_onIsPayPalInstallment()
    {

        $blExpectedResult = true;

        /** Get ID of PayPal Installments payment method */
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '_paypInstallments_getPaymentIdFromSession',
                )
            )
            ->getMock();
        $oSUT->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue($sPayPalInstallmentsPaymentId));

        $blActualResult = paypInstallments_oxPaymentGatewayTest::callMethod($oSUT, '_paypInstallments_isPayPalInstallmentsPayment', array());

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testIsPayPalInstallmentsPayment_returnsFalse_onIsNotPayPalInstallment()
    {
        $blExpectedResult = false;

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '_paypInstallments_getPaymentIdFromSession',
                )
            )
            ->getMock();
        $oSUT->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue('oxidcashondel'));

        $blActualResult = paypInstallments_oxPaymentGatewayTest::callMethod($oSUT, '_paypInstallments_isPayPalInstallmentsPayment', array());

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testgetPaymentIdFromSession_returnsPaymentId()
    {
        $sExpectedPaymentId = 'expected-paymentid';

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('getVariable'))
            ->getMock();
        $oSession->expects($this->atLeastOnce())
            ->method('getVariable')
            ->with('paymentid')
            ->will($this->returnValue($sExpectedPaymentId));

        /** @var paypInstallmentsOxPaymentGateway|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsOxPaymentGateway')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getSession',
                )
            )
            ->getMock();
        $oSUT->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($oSession));

        $sActualPaymentId = paypInstallments_oxPaymentGatewayTest::callMethod($oSUT, '_paypInstallments_getPaymentIdFromSession', array());

        $this->assertEquals($sExpectedPaymentId, $sActualPaymentId);

    }
}
