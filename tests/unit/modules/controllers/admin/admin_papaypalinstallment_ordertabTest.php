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
 * Class Admin_paypInstallments_orderTabTest
 *
 * @desc Unit test for controller Admin_paypInstallments_orderTab
 */
class Admin_paypInstallments_orderTabTest extends OxidTestCase
{

    public function testGetRenderData_returnsExpectedValues()
    {

        $testPaOrderFormatted = 'test-pa-order-formatted';
        $testPaRefundListFormatted = 'test-pa-refund-list-formatted';
        $testPaOrderPaymentFormatted = 'test-pa-order-payment-formatted';
        $testPaRemainingRefund = 'test-pa-remaining-refund';
        $testPaError = 'test-pa-error';

        /** @var paypInstallmentFormatter|PHPUnit_Framework_MockObject_MockObject $oFormatterMock */
        $oFormatterMock = $this->getMockBuilder('paypInstallmentsFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('formatOrder', 'formatPayment', 'formatRefundList', 'formatPrice'))
            ->getMock();
        $oFormatterMock->expects($this->once())
            ->method('formatOrder')
            ->will($this->returnValue($testPaOrderFormatted));
        $oFormatterMock->expects($this->once())
            ->method('formatPayment')
            ->will($this->returnValue($testPaOrderPaymentFormatted));
        $oFormatterMock->expects($this->once())
            ->method('formatRefundList')
            ->will($this->returnValue($testPaRefundListFormatted));
        $oFormatterMock->expects($this->once())
            ->method('formatPrice')
            ->will($this->returnValue($testPaRemainingRefund));

        /** @var Admin_paypInstallments_orderTab|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('getFormatter', 'getError'))
            ->getMock();
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getError')
            ->will($this->returnValue($testPaError));
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getFormatter')
            ->will($this->returnValue($oFormatterMock));

        $this->assertEquals(
            array(
                'order'           => $testPaOrderFormatted,
                'payment'         => $testPaOrderPaymentFormatted,
                'refund'          => $testPaRefundListFormatted,
                'remainingRefund' => $testPaRemainingRefund,
                'error'           => $testPaError
            ),
            $oSubjectUnderTest->getRenderData(),
            'Template data matches expected one.'
        );
    }

    public function testRefund_success()
    {
        $aResponseData = array(
            'TransactionId'             => '1',
            'Memo'                      => '2',
            'RefundId'                  => '3',
            'GrossRefundAmount'         => '4',
            'GrossRefundAmountCurrency' => '5',
            'Status'                    => '6',
            'Response'                  => '7'
        );

        /** @var paypInstallmentsRefundHandler|PHPUnit_Framework_MockObject_MockObject $oRefundHandler */
        $oRefundHandler = $this->getMockBuilder('paypInstallmentsRefundHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('doRequest'))
            ->getMock();
        $oRefundHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($aResponseData));

        /** @var paypInstallmentsRefund|PHPUnit_Framework_MockObject_MockObject $oRefund */
        $oRefund = $this->getMockBuilder('paypInstallmentsRefund')
            ->disableOriginalConstructor()
            ->setMethods(array('save'))
            ->getMock();
        $oRefund->expects($this->once())
            ->method('save');

        $oOrderMock = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Admin_paypInstallments_orderTab|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('getRefundHandler', 'buildRefund', 'getOrder'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('getRefundHandler')
            ->will($this->returnValue($oRefundHandler));
        $oSubjectUnderTest->expects($this->once())
            ->method('buildRefund')
            ->will($this->returnValue($oRefund));
        $oSubjectUnderTest->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($oOrderMock));

        $oSubjectUnderTest->refund();
        $this->assertNull($oSubjectUnderTest->getError(), 'No errors after successful refund.');
    }

    public function testRefund_fail()
    {
        $testPaError = 'test-pa-exception';

        /** @var paypInstallmentsRefundHandler|PHPUnit_Framework_MockObject_MockObject $oRefundHandler */
        $oRefundHandler = $this->getMockBuilder('paypInstallmentsRefundHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('doRequest'))
            ->getMock();
        $oRefundHandler->expects($this->once())
            ->method('doRequest')
            ->will($this->throwException(new Exception($testPaError)));

        /** @var Admin_paypInstallments_orderTab|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('getRefundHandler'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('getRefundHandler')
            ->will($this->returnValue($oRefundHandler));

        $oSubjectUnderTest->refund();
        $this->assertEquals($testPaError, $oSubjectUnderTest->getError(), 'Errors after successful refund.');
    }

    /**
     * @param $blExpected
     * @param $fieldValue
     * @param $sConditionDescription
     *
     * @dataProvider testIsPayPalInstallmentOrderDataProvider
     */
    public function testIsPayPalInstallmentOrder($blExpected, $fieldValue, $sConditionDescription)
    {
        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldData'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue($fieldValue));

        /** @var Admin_paypInstallments_orderTab|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('getOrder'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($oOrder));

        $this->assertSame($blExpected, $oSubjectUnderTest->isPayPalInstallmentOrder(), $sConditionDescription);
    }

    /**
     * @return array array(array($blExpected, $fieldValue, $sConditionDescription), ...)
     */
    public function testIsPayPalInstallmentOrderDataProvider()
    {
        return array(
            array(false, 'any-payment-type', 'Payment is not PayPalInstallment.'),
            array(true, 'paypinstallments', 'Payment is PayPalInstallment.'),
        );
    }

    /**
     * @param $mExpectedType
     * @param $blOrderLoadResult
     * @param $sConditionDescription
     *
     * @dataProvider testGetOrderDataProvider
     */
    public function testGetOrder($mExpectedType, $blOrderLoadResult, $sConditionDescription)
    {
        $sOrderId = 'test-pa-order-id';

        $oOrder = $this->getMockBuilder('oxOrder')
            ->setMethods(array('isLoaded'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('isLoaded')
            ->will($this->returnValue($blOrderLoadResult));

        /** @var Admin_paypInstallments_orderTab|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('_fetchOrder'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('_fetchOrder')
            ->will($this->returnValue($oOrder));

        $mExpectedType and $this->assertInstanceOf(
            $mExpectedType,
            $oSubjectUnderTest->getOrder(),
            $sConditionDescription
        );
        $mExpectedType or $this->assertNull($oSubjectUnderTest->getOrder(), $sConditionDescription);
    }

    /**
     * @return array array(array($mExpectedType, $blOrderLoadResult, $sConditionDescription), ...)
     */
    public function testGetOrderDataProvider()
    {
        return array(
            array('oxOrder', false, 'Order does not exist.'),
            array('oxOrder', true, 'Order does exist.'),
        );
    }

    /**
     * @param string $sExpectedClass
     * @param string $sGetter
     *
     * @dataProvider testGetSetMethodsDataProvider
     */
    public function testGetSetMethods($sExpectedClass, $sGetter)
    {
        $oSubjectUnderTest = $this->getMockBuilder('Admin_paypInstallments_orderTab')
            ->disableOriginalConstructor()
            ->setMethods(array('getOrder'))
            ->getMock();
        $oSubjectUnderTest->expects($this->any())->method('getOrder')->will($this->returnValue(new oxOrder()));

        $oDependency = $oSubjectUnderTest->$sGetter();

        $this->assertInstanceOf($sExpectedClass, $oSubjectUnderTest->$sGetter());
        $this->assertSame($oDependency, $oSubjectUnderTest->$sGetter(), 'Reusing the same dependency.');
    }

    /**
     * @return array array(array($sExpectedClass, $sGetter), ...)
     */
    public function testGetSetMethodsDataProvider()
    {
        return array(
            array('paypInstallmentsRefundList', 'getRefundList'),
            array('paypInstallmentsPaymentData', 'getOrderPayment'),
            array('paypInstallmentsFormatter', 'getFormatter'),
            array('paypInstallmentsRefundHandler', 'getRefundHandler'),
        );
    }

    public function testSetGetError()
    {
        $oSubjectUnderTest = new Admin_paypInstallments_orderTab();

        $this->assertNull($oSubjectUnderTest->getError(), 'Initially there are not error.');
        $this->assertSame('1', $oSubjectUnderTest->setError(1)->getError(), 'Errors are converted to string.');
        $this->assertSame('1', $oSubjectUnderTest->getError(), 'Errors are retained.');
    }

    public function testBuildRefund()
    {
        $sDateStart = date('Y-m-d H:i:s');

        $aResponseData = array(
            'TransactionId'             => 'test-pa-transaction-id',
            'Memo'                      => 'test-pa-memo',
            'RefundId'                  => 'test-pa-refund-id',
            'GrossRefundAmount'         => '123.45',
            'GrossRefundAmountCurrency' => 'test-pa-currency',
            'Status'                    => 'test-pa-status',
            'Response'                  => 'test-pa-response',
        );

        $oSubjectUnderTest = new Admin_paypInstallments_orderTab();

        $oRefund = $oSubjectUnderTest->buildRefund($aResponseData);

        $this->assertEquals('test-pa-transaction-id', $oRefund->getTransactionId());
        $this->assertEquals('test-pa-memo', $oRefund->getMemo());
        $this->assertEquals('test-pa-refund-id', $oRefund->getRefundId());
        $this->assertEquals('123.45', $oRefund->getAmount());
        $this->assertEquals('test-pa-currency', $oRefund->getCurrency());
        $this->assertEquals('test-pa-status', $oRefund->getStatus());
        $this->assertEquals('test-pa-response', $oRefund->getResponse());
        $this->assertGreaterThanOrEqual($sDateStart, $oRefund->getDateCreated());
        $this->assertLessThanOrEqual(date('Y-m-d H:i:s'), $oRefund->getDateCreated());
    }
}
