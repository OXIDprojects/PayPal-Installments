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
 * class paypInstallments_thankyouTest
 *
 * @covers paypInstallmentsThankyou
 */
class paypInstallments_thankyouTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsThankyou
     */
    protected $_SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->setMethods(
                array(
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_getFinancingDetailsFromSession'
                )
            )
            ->getMock();
    }

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

    public function testClassImplementsLoggerAwareInterface()
    {
        $this->assertInstanceOf('\Psr\Log\LoggerAwareInterface', $this->_SUT);
    }

    public function testRender_callsRenderParent()
    {
        $sExpectedResult = 'someTemplateName';

        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_callRenderParent')
            ->will($this->returnValue($sExpectedResult));

        $sActualResult = $this->_SUT->render();

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    /**
     * @param $mFinancingDetails
     *
     * @dataProvider dataProviderInvalidFinancingDetails
     *
     */
    public function testRender_addDisplayError_onInvalidFinancingDetails($mFinancingDetails)
    {
        $sExpectedResult = 'someTemplateName';

        /** @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsThankyou */
        $SUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->setMethods(
                array(
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_deletePayPalRegistryFromSession',
                )
            )
            ->getMock();

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_callRenderParent')
            ->will($this->returnValue($sExpectedResult));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));

        /**
         * TODO Test for errors in session here
         */
        $sActualResult = $SUT->render();

        $this->assertEquals($sExpectedResult, $sActualResult);
    }


    /**
     * @param $mFinancingDetails
     *
     * @dataProvider dataProviderAnyFinancingDetails
     */
    public function testRender_deletesSessionRegistry_onAnyFinancingDetails($mFinancingDetails)
    {
        $sExpectedResult = 'someTemplateName';
        /** @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsThankyou */
        $SUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->setMethods(
                array(
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_deletePayPalRegistryFromSession',
                )
            )
            ->getMock();

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_callRenderParent')
            ->will($this->returnValue($sExpectedResult));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue(new oxBasket()));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($mFinancingDetails));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_deletePayPalRegistryFromSession');

        /**
         * TODO Test for errors in session here
         */
        $sActualResult = $SUT->render();

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function dataProviderInvalidFinancingDetails()
    {
        return array(
            array(''),
            array(null),
            array(array()),
            array(new StdClass()),
            array('1 beer'),
        );
    }

    public function dataProviderValidFinancingDetails()
    {
        return array(
            array(new paypInstallmentsFinancingDetails())
        );
    }

    public function dataProviderAnyFinancingDetails()
    {
        return array_merge(
            $this->dataProviderValidFinancingDetails(),
            $this->dataProviderInvalidFinancingDetails()
        );
    }

    public function testpaypInstallmentss_getFinancingDetails()
    {
        $oFinancingDetails = new paypInstallmentsFinancingDetails();
        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->setMethods(
                array(
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_getFinancingDetailsFromSession',
                    '_paypInstallments_getBasketFromSession',
                    '_paypInstallments_deletePayPalRegistryFromSession',
                    '_paypInstallments_callRenderParent'
                )
            )
            ->getMock();

        $oSUT->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));

        $oSUT->expects($this->once())
            ->method('_paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($oFinancingDetails));

        $oSUT->expects($this->once())
            ->method('_paypInstallments_getBasketFromSession')
            ->will($this->returnValue(new oxBasket()));

        $this->assertNull($oSUT->paypInstallments_getFinancingDetails());
        $oSUT->render();

        $this->assertSame($oFinancingDetails, $oSUT->paypInstallments_getFinancingDetails());
    }

    public function testpaypInstallmentss_getBasket_returnsInstanceOfBasket()
    {
        $oBasket = new oxBasket();
        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->setMethods(array('__construct'))
            ->getMock();
        $oSUT->paypInstallments_setBasket($oBasket);

        $oActualObject = $oSUT->paypInstallments_getBasket();

        $this->assertInstanceOf('oxBasket', $oActualObject);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetBasket_ThrowsInvalidArgumentException_onInvalidArgument()
    {
        $oBasket = null;
        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')->getMock();
        $oSUT->paypInstallments_setBasket($oBasket);
    }

    public function testPaypInstallmentss_getFinancingOptionsRenderData_returnExpectedRenderData()
    {
        $fMonthlyPayment = 12345.67;
        $iExpectedFinancingTerm = 6;
        $sExpectedMonthlyPayment = '12.345,67';
        $sExpectedCurrency = 'EUR';

        $oFinancingDetails = new paypInstallmentsFinancingDetails();
        $oFinancingDetails->setFinancingTerm($iExpectedFinancingTerm);
        $oFinancingDetails->setFinancingMonthlyPayment($fMonthlyPayment);
        $oFinancingDetails->setFinancingCurrency($sExpectedCurrency);

        $aExpectedRenderData = array(
            $iExpectedFinancingTerm,
            $sExpectedMonthlyPayment,
            $sExpectedCurrency,
        );

        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallments_getFinancingDetails',))
            ->getMock();

        $oSUT->expects($this->once())
            ->method('paypInstallments_getFinancingDetails')
            ->will($this->returnValue($oFinancingDetails));


        $aActualRenderData = $oSUT->paypInstallments_getFinancingOptionsRenderData();

        $this->assertSame($aExpectedRenderData, $aActualRenderData);
    }

    public function testRender_callsSetPropertiesAndCleanup_onIsPayPalInstallmentsPayment()
    {
        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_setPropertiesAndCleanup',
                )
            )
            ->getMock();

        $oSUT->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(true));
        $oSUT->expects($this->once())
            ->method('_paypInstallments_setPropertiesAndCleanup');

        $oSUT->render();
    }

    public function testRender_doesNotCallSetPropertiesAndCleanup_onIsNotPayPalInstallmentsPayment()
    {
        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_isPayPalInstallmentsPayment',
                    '_paypInstallments_setPropertiesAndCleanup',
                )
            )
            ->getMock();

        $oSUT->expects($this->once())
            ->method('_paypInstallments_isPayPalInstallmentsPayment')
            ->will($this->returnValue(false));
        $oSUT->expects($this->never())
            ->method('_paypInstallments_setPropertiesAndCleanup');

        $oSUT->render();
    }

    public function testIsPayPalInstallmentsPayment_returnsTrue_onIsPayPalInstallment() {

        $blExpectedResult = true;

        /** Get ID of PayPal Installments payment method */
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
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

        $blActualResult = paypInstallments_thankyouTest::callMethod($oSUT, '_paypInstallments_isPayPalInstallmentsPayment', array() );

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testIsPayPalInstallmentsPayment_returnsFalse_onIsNotPayPalInstallment() {

        $blExpectedResult = false;

        /** @var paypInstallmentsThankyou|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsThankyou')
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

        $blActualResult = paypInstallments_thankyouTest::callMethod($oSUT, '_paypInstallments_isPayPalInstallmentsPayment', array() );

        $this->assertEquals($blExpectedResult, $blActualResult);
    }
}
