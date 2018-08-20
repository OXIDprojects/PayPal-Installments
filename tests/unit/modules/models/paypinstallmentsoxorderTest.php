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
 * Class paypInstallments_oxOrderTest
 *
 * @desc Unit tests for paypInstallmentsOxOrder.
 */
class paypInstallments_oxOrderTest extends OxidTestCase
{

    public function tearDown()
    {
        oxUtilsObject::setClassInstance('oxCounter', null);
    }

    public function testpaypInstallments_getOrderNr()
    {
        $mCounterIdent = 'test-pa-counter-ident';
        $mOrderNb = 'test-pa-order-nb';

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxOrder $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('_getCounterIdent'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('_getCounterIdent')
            ->will($this->returnValue($mCounterIdent));

        /** @var PHPUnit_Framework_MockObject_MockObject|oxCounter $oCounter */
        $oCounter = $this->getMockBuilder('oxCounter')
            ->disableOriginalConstructor()
            ->setMethods(array('getNext'))
            ->getMock();
        $oCounter->expects($this->once())
            ->method('getNext')
            ->with($mCounterIdent)
            ->will($this->returnValue($mOrderNb));
        oxUtilsObject::setClassInstance('oxCounter', $oCounter);

        $this->assertEquals($mOrderNb, $oSubjectUnderTest->paypInstallments_getOrderNr());
    }

    /**
     * @return paypInstallmentsPaymentData
     */
    public function testGetPayPalInstallmentsPaymentData_returnsExpectedPaymentData()
    {
        $sOrderId = md5(time());
        $sPaymentDataId = md5(time());

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxOrder $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();

        $oSubjectUnderTest->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sOrderId));

        $oPaymentData = new paypInstallmentsPaymentData();
        $oPaymentData->setId($sPaymentDataId);
        $oPaymentData->setOrderId($sOrderId);
        $oPaymentData->save();

        $oActualObject = $oSubjectUnderTest->paypInstallments_getPayPalInstallmentsPaymentData();
        $this->assertEquals($sPaymentDataId, $oActualObject->getId());

        $oPaymentData->delete();
    }

    /**
     * @expectedException oxException
     * @expectedExceptionMessage PAYP_INSTALLMENTS_REFUND_ERR_DISCOUNT_COULD_NOT_BE_ASSIGNED_TO_ORDER
     */
    public function testPaypInstallments_DiscountRefund_ThrowsExceptionOnParamsNotAssigned()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxOrder $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('assign'))
            ->getMock();

        $oSubjectUnderTest
            ->expects($this->once())
            ->method('assign')
            ->will($this->returnValue(false));

        $oSubjectUnderTest->paypInstallments_DiscountRefund(100.00);
    }

    /**
     * @dataProvider dataProviderTestPaypInstallments_DiscountRefund_willReturnExpectedResult
     */
    public function testPaypInstallments_DiscountRefund_willReturnExpectedResult($blExpectedResult, $fNewTotalDiscount, $fOxDiscount, $sMessage)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxOrder $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxOrder')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '_paypInstallments_GetNewTotalDiscount',
                    '_getFieldData',
                    'assign',
                    'reloadDelivery',
                    'reloadDiscount',
                    'recalculateOrder')
            )
            ->getMock();

        $oSubjectUnderTest
            ->expects($this->once())
            ->method('assign')
            ->will($this->returnValue(true));

        $oSubjectUnderTest
            ->expects($this->once())
            ->method('_paypInstallments_GetNewTotalDiscount')
            ->will($this->returnValue($fNewTotalDiscount));

        $oSubjectUnderTest
            ->expects($this->any())
            ->method('_getFieldData')
            ->with('oxdiscount')
            ->will($this->returnValue($fOxDiscount));

        $blActualResult = $oSubjectUnderTest->paypInstallments_DiscountRefund(10.00);

        $this->assertEquals($blExpectedResult, $blActualResult, $sMessage);
    }

    public function dataProviderTestPaypInstallments_DiscountRefund_willReturnExpectedResult()
    {
        return array(
            // array($blExpectedResult, $fNewTotalDiscount, $fOxDiscount),
            array(true, 20.0, 20.0, 'Discount would have been assigned'),
            array(false, 20.0, 10.0, 'Discount would not have been assigned')
        );
    }

    /**
     * @dataProvider dataProviderTestPaypInstallments_getFormattedFinancingFee
     *
     * @param $fAmount
     * @param $sExpectedResult
     * @param $oCurrency
     * @param $sMessage
     */
    public function testPaypInstallments_getFormattedFinancingFee_returnsExpectedResult($fAmount, $sExpectedResult, $oCurrency, $sMessage )
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxOrder $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxOrder')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getFieldData',
                    'getOrderCurrency'
                )
            )
            ->getMock();

        $oSubjectUnderTest
            ->expects($this->any())
            ->method('getFieldData')
            ->with('paypinstallments_financingfee')
            ->will($this->returnValue($fAmount));

        $oSubjectUnderTest
            ->expects($this->any())
            ->method('getOrderCurrency')
            ->will($this->returnValue($oCurrency));

        $sActualResult = $oSubjectUnderTest->paypInstallments_getFormattedFinancingFee();

        $this->assertEquals($sExpectedResult, $sActualResult, $sMessage);
    }

    public function dataProviderTestPaypInstallments_getFormattedFinancingFee () {
        return array(
            // array($fAmount, $sExpectedResult, $oCurrency, $sMessage),
            array(0, '0,00', $this->_getCurrencyObjectByName('EUR'), 'Currency in EUR'),
            array(12345, '12.345,00', $this->_getCurrencyObjectByName('EUR'), 'Currency in EUR'),
            array(12345.000045, '12.345,00', $this->_getCurrencyObjectByName('EUR'), 'Currency in EUR'),
            array(12345.670000, '12.345,67', $this->_getCurrencyObjectByName('EUR'), 'Currency in EUR'),
            array(12345.67, '12.345,67', $this->_getCurrencyObjectByName('EUR'), 'Currency in EUR'),
            array(12345.67, '12345.67', $this->_getCurrencyObjectByName('USD'), 'Currency in USD'),
        );
    }


    protected function _getCurrencyObjectByName($sName)
    {
        $oOrder = new oxOrder();
        $oOrder->oxorder__oxcurrency = new oxField($sName);
        $oCurrency = $oOrder->getOrderCurrency();

        return $oCurrency;
    }
}
