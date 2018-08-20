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
 * Class paypinstallmentsdeterminetaxtotalTest
 * This class only purpose is to test the determineTaxTotal method
 */
class paypinstallmentsdeterminetaxtotalTest extends OxidTestCase
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

        $oDataProvider = $this->getMock(
            'paypInstallmentsCheckoutDataProvider',
            array(
                '__call',
                'getBasket',
                'getItemTotal',
                'getOrderTotal',
                'getHandlingTotal',
                'getTSProtectionCosts',
                'getShippingCosts',
                'getShippingDiscount',
            )
        );

        $oDataProvider->expects($this->any())->method('getItemTotal')
            ->will($this->onConsecutiveCalls(15.00, 10.00, 10.00, 10.00, 10.00, 5.0, 5.0));

        $oDataProvider->expects($this->any())->method('getOrderTotal')
            ->will($this->returnValue(15.00));

        $oDataProvider->expects($this->any())->method('getHandlingTotal')
            ->will($this->onConsecutiveCalls(0.0, 1.0, 0.0, 0.0, 0.0, 2.0, 0.0));

        $oDataProvider->expects($this->any())->method('getTSProtectionCosts')
            ->will($this->onConsecutiveCalls(0.0 ,0.0, 2.5, 1.0, 0.0, 1.5, 1.0));

        $oDataProvider->expects($this->any())->method('getShippingCosts')
            ->will($this->onConsecutiveCalls(0.0, 0.0, 0.0, 0.0, 1.0, 0.75, 0.0));

        $oDataProvider->expects($this->any())->method('getShippingDiscount')
            ->will($this->onConsecutiveCalls(0.0, 0.0, 0.0, -1.0, 1.0, 0.75, -4.00));

        $this->SUT->setDataProvider($oDataProvider);

    }

    /**
     * This is a helper method to call private or protected functions with name $name on $obj with $args as arguments
     *
     * @param       $obj
     * @param       $name
     * @param array $args
     *
     * @return mixed
     */
    private static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function testTrue () {
        $this->assertTrue(true);
    }

    /**
     * make sure that determineOrderCostDiff works right
     */
    public function _testDetermineOrderCostDiff()
    {
        $fTax = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax, 0.0);
        $fTax2 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax2, 4.0);
        $fTax3 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax3, 2.5);
        $fTax4 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax4, 5.0);
        $fTax5 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax5, 3.0);
        $fTax6 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax6, 5.0);
        $fTax7 = self::callMethod($this->SUT, 'determineOrderCostDiff', array());
        $this->assertSame($fTax7, 13.0);
    }
}