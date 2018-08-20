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
 * Class paypInstallments_oxPaymentTest
 */
class paypInstallments_oxPaymentTest extends OxidTestCase
{
    /**
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxPayment
     */
    protected $_SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsOxPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_callParentIsValidPaymentMethod',
                      '_paypInstallments_isValidPayPalInstallmentsPayment',
                      '_paypInstallments_getRequirementsValidator'
                )
            )
            ->getMock();
    }

    public function testisValidPayment_callsIsVaiidPaymentParent()
    {

        $this->_SUT->expects($this->once())->method('_paypInstallments_callParentIsValidPaymentMethod');

        $this->_SUT->isValidPayment(null, null, null, null, null);
    }

    public function testIsValidPayment_returnsFalse_withNoStubbedMethods() {
        $blExpectedResult = false;

        /** @var paypInstallmentsOxPayment|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsOxPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                )
            )
            ->getMock();

        $blActualResult = $SUT->isValidPayment(null, null, null, null, null);

        $this->assertSame($blExpectedResult, $blActualResult);
    }

    public function testisValidPayment_callsIsValidPayPalInstallmentsPayment_onCorrectPaymentId()
    {

        $this->_SUT->oxpayments__oxid = new oxField(paypInstallmentsConfiguration::getPaymentId());

        $this->_SUT->expects($this->once())->method('_paypInstallments_callParentIsValidPaymentMethod')->will($this->returnValue(true));
        $this->_SUT->expects($this->once())->method('_paypInstallments_isValidPayPalInstallmentsPayment');

        $this->_SUT->isValidPayment(null, null, null, null, null);
    }

    public function testisValidPayment_doesNotCallIsValidPayPalInstallmentsPayment_onWrongPaymentId()
    {

        $this->_SUT->oxpayments__oxid = new oxField('someid');

        $this->_SUT->expects($this->once())->method('_paypInstallments_callParentIsValidPaymentMethod')->will($this->returnValue(true));
        $this->_SUT->expects($this->never())->method('_paypInstallments_isValidPayPalInstallmentsPayment');

        $this->_SUT->isValidPayment(null, null, null, null, null);
    }

    /**
     * Test
     * @dataProvider   dataProviderExceptions
     *
     * @param string $sException
     */
    public function testIsValidPayment_returnsFalse_onpaypInstallmentssWrongCountryException($sException)
    {

        $blExpectedResult = false;

        $oUserMock = $this
            ->getMockBuilder('oxUser')
            ->setMethods(
                array('__call',
                      '__construct',
                )
            )
            ->getMock();;
        $oBasketMock = $this->getMockBuilder('oxBasket')->setMethods(array('getBasketUser'))->getMock();
        $oBasketMock->expects($this->once())->method('getBasketUser')->will($this->returnValue($oUserMock));

        /** @var paypInstallmentsOxPayment|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsOxPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_callParentIsValidPaymentMethod',
                      '_paypInstallments_getBasketFromSession',
                      '_paypInstallments_getRequirementsValidator'
                )
            )
            ->getMock();

        $SUT->oxpayments__oxid = new oxField(paypInstallmentsConfiguration::getPaymentId());

        $SUT->expects($this->once())->method('_paypInstallments_callParentIsValidPaymentMethod')->will($this->returnValue(true));

        $SUT->expects($this->once())->method('_paypInstallments_getBasketFromSession')->will($this->returnValue($oBasketMock));

        $oRequirementsValidatorMock = $this
            ->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(array('validateRequirements'))
            ->getMock();
        $oRequirementsValidatorMock
            ->expects($this->once())
            ->method('validateRequirements')
            ->will(
                $this->throwException(new $sException)
            );

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getRequirementsValidator')
            ->will($this->returnValue($oRequirementsValidatorMock));

        $blActualResult = $SUT->isValidPayment(null, null, null, null, null);

        $this->assertEquals($blExpectedResult, $blActualResult);
    }



    public function dataProviderExceptions()
    {
        return array(
            array('paypInstallmentsInvalidBillingCountryException'),
            array('paypInstallmentsInvalidShippingCountryException'),

        );
    }
}
