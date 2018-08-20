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
 * Class paypInstallmentFormatterTest
 *
 * @desc
 */
class paypInstallmentFormatterTest extends OxidTestCase
{

    public function testFormatPayment()
    {
        $sTransactionId = 'test-pa-transaction-id';
        $sStatus = 'test-pa-status';
        $sRefundable = 'test-pa-refundable';

        /** @var paypInstallmentsPaymentData|PHPUnit_Framework_MockObject_MockObject $oPayment */
        $oPayment = $this->getMockBuilder('paypInstallmentsPaymentData')
            ->disableOriginalConstructor()
            ->setMethods(array('getTransactionId', 'getStatus', 'isRefundable'))
            ->getMock();
        $oPayment->expects($this->once())
            ->method('getTransactionId')
            ->will($this->returnValue($sTransactionId));
        $oPayment->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($sStatus));
        $oPayment->expects($this->once())
            ->method('isRefundable')
            ->will($this->returnValue($sRefundable));

        $oSubjectUnderTest = new paypInstallmentsFormatter();

        $this->assertEquals(
            array(
                'transactionId' => $sTransactionId,
                'status'        => $sStatus,
                'refundable'    => $sRefundable,
            ),
            $oSubjectUnderTest->formatPayment($oPayment)
        );
    }

    /**
     * @dataProvider dataProviderTestFormat
     */
    public function testFormatRefundList($sCurrency, $sExpectedFormattedTotal)
    {
        $fTotal = 12345.67;
        $sRefund1 = new paypInstallmentsRefund();
        $sRefund2 = new paypInstallmentsRefund();

        $sRefundFormatted1 = 'test-pa-refund-formatted-1';
        $sRefundFormatted2 = 'test-pa-refund-formatted-2';

        /** @var paypInstallmentsRefundList|PHPUnit_Framework_MockObject_MockObject $oRefundList */
        $oRefundList = $this->getMockBuilder('paypInstallmentsRefundList')
            ->disableOriginalConstructor()
            ->setMethods(array('getRefundedSumByTransactionId', 'getArray'))
            ->getMock();
        $oRefundList->expects($this->once())
            ->method('getRefundedSumByTransactionId')
            ->will($this->returnValue($fTotal));
        $oRefundList->expects($this->once())
            ->method('getArray')
            ->will($this->returnValue(array($sRefund1, $sRefund2)));

        /** @var paypInstallmentsFormatter|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('formatRefund'))
            ->getMock();
        $oSubjectUnderTest->expects($this->at(0))
            ->method('formatRefund')
            ->will($this->returnValue($sRefundFormatted1));
        $oSubjectUnderTest->expects($this->at(1))
            ->method('formatRefund')
            ->will($this->returnValue($sRefundFormatted2));

        $oOrder = new oxOrder();
        $oOrder->oxorder__oxcurrency = new oxField($sCurrency);

        $aActualFormattedRefundList = $oSubjectUnderTest->formatRefundList($oRefundList, $oOrder);

        $this->assertEquals($sExpectedFormattedTotal, $aActualFormattedRefundList['total'], 'Formatted total matches');
        $this->assertEquals([$sRefundFormatted1, $sRefundFormatted2], $aActualFormattedRefundList['list'], 'Formatted refund list matches');
    }

    public function dataProviderTestFormat()
    {
        return array(
            // array($sCurrency, $sExpectedFormattedTotal)
            array('USD', '12345.67'),
            array('GBP', '12345.67'),
            array('EUR', '12.345,67'),
        );
    }

    /**
     * @dataProvider dataProviderTestFormat
     */
    public function testFormatRefund($sCurrency, $sExpectedFormattedTotal)
    {
        $sDate = 'test-pa-refund-date';
        $fAmount = 12345.67;
        $sStatus = 'test-pa-status';


        /** @var paypInstallmentsRefund|PHPUnit_Framework_MockObject_MockObject $oRefund */
        $oRefund = $this->getMockbuilder('paypInstallmentsRefund')
            ->disableOriginalConstructor()
            ->setMethods(array('getDateCreated', 'getAmount', 'getCurrency', 'getStatus'))
            ->getMock();
        $oRefund->expects($this->once())->method('getDateCreated')->will($this->returnValue($sDate));
        $oRefund->expects($this->once())->method('getAmount')->will($this->returnValue($fAmount));
        $oRefund->expects($this->once())->method('getCurrency')->will($this->returnValue($sCurrency));
        $oRefund->expects($this->once())->method('getStatus')->will($this->returnValue($sStatus));


        $oSubjectUnderTest = new paypInstallmentsFormatter();
        $oOrder = new oxOrder();
        $oOrder->oxorder__oxcurrency = new oxField($sCurrency);
        $aActualFormattedRefund = $oSubjectUnderTest->formatRefund($oRefund, $oOrder);

        $this->assertEquals($sDate, $aActualFormattedRefund[paypInstallmentsFormatter::KEY_DATE], 'Formatted date matches');
        $this->assertEquals($sExpectedFormattedTotal, $aActualFormattedRefund[paypInstallmentsFormatter::KEY_TOTAL], 'Formatted total matches');
        $this->assertEquals($sCurrency, $aActualFormattedRefund[paypInstallmentsFormatter::KEY_CURRENCY], 'Formatted currency matches');
        $this->assertEquals($sStatus, $aActualFormattedRefund[paypInstallmentsFormatter::KEY_STATUS], 'Formatted status matches');
    }

    /**
     * @dataProvider dataProviderTestFormat
     */
    public function testFormatOrder($sCurrency, $sExpectedFormattedTotal)
    {
        $sOrderId = 'test-pa-order-id';
        $fOrderSum = 12345.67;

        $oOrder = new oxOrder();
        $oOrder->setId($sOrderId);
        $oOrder->oxorder__oxtotalordersum = new oxField($fOrderSum);
        $oOrder->oxorder__oxcurrency = new oxField($sCurrency);

        $oSubjectUnderTest = new paypInstallmentsFormatter();

        $this->assertEquals(
            array(
                'id'       => $sOrderId,
                'total'    => $sExpectedFormattedTotal,
                'currency' => $sCurrency,
            ),
            $oSubjectUnderTest->formatOrder($oOrder)
        );
    }

    /**
     * @param $sExpected
     * @param $mValue
     *
     * @dataProvider testFormatPriceDataProvider
     */
    public function testFormatPrice($oCurrency, $sExpected, $mValue)
    {
        $oSubjectUnderTest = new paypInstallmentsFormatter();

        $this->assertSame($sExpected, $oSubjectUnderTest->formatPrice($mValue, $oCurrency));
    }

    public function testFormatPriceDataProvider()
    {
        $this->getCurrencyObjectByName('EUR');

        $fAmount = 12345.67;
        return array(
            array($this->getCurrencyObjectByName('EUR'), '12.345,67', $fAmount),
            array($this->getCurrencyObjectByName('EUR'), '12.345,67', $fAmount),
            array($this->getCurrencyObjectByName('EUR'), '12.345,67', 12345.67000),
            array($this->getCurrencyObjectByName('EUR'), '12.345,00', 12345),
            array($this->getCurrencyObjectByName('EUR'), '0,00', null),
            array($this->getCurrencyObjectByName('EUR'), '0,00', ''),
            array($this->getCurrencyObjectByName('EUR'), '0,00', false),
        );
    }

    protected function getCurrencyObjectByName($sName)
    {
        $oOrder = new oxOrder();
        $oOrder->oxorder__oxcurrency = new oxField($sName);
        $oCurrency = $oOrder->getOrderCurrency();

        return $oCurrency;
    }
}
