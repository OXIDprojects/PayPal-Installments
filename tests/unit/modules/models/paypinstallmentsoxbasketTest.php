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
 * Class paypInstallmentsOxBasketTest
 *
 * @covers paypInstallmentsOxBasket
 */
class paypInstallmentsOxBasketTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxBasket
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this
            ->getMockBuilder('paypInstallmentsOxBasket')
            ->setMethods(array('__construct', '__call', 'getTotalDiscount', 'getVoucherDiscount'))
            ->getMock();
    }

    public function testPaGetBasketItemsFingerprint_returnsExpectedFingerprint()
    {
        $sExpectedFingerprint = '27464830126450cada1e3b275edd0d5c';

        /** Empty the basket */
        $this->emptyBasket();

        /** Add some articles to basket. fingerprint should match expected fingerprint now */
        $this->_SUT->addToBasket('05848170643ab0deb9914566391c0c63', 5.0);
        $this->_SUT->addToBasket('058e613db53d782adfc9f2ccb43c45fe', 5.0);
        $sActualFingerprint = $this->_SUT->paypInstallments_GetBasketItemsFingerprint();
        $this->assertEquals($sExpectedFingerprint, $sActualFingerprint);

        /** Add some articles to basket. fingerprint should NOT match expected fingerprint any more */
        /** @var oxbasketitem $oLastAddedItem */
        $oLastAddedItem = $this->_SUT->addToBasket('05848170643ab0deb9914566391c0c63', 1.0);
        $sActualFingerprint = $this->_SUT->paypInstallments_GetBasketItemsFingerprint();
        $this->assertNotEquals($sExpectedFingerprint, $sActualFingerprint);

        /** Empty the basket and add the same first articles again. Fingerprint should match expected fingerprint now */
        $this->emptyBasket();
        $this->_SUT->addToBasket('05848170643ab0deb9914566391c0c63', 5.0);
        $this->_SUT->addToBasket('058e613db53d782adfc9f2ccb43c45fe', 5.0);
        $sActualFingerprint = $this->_SUT->paypInstallments_GetBasketItemsFingerprint();
        $this->assertEquals($sExpectedFingerprint, $sActualFingerprint);
    }


    public function testGetTsProductId_returnsExpectedId_whenTsIsSetInSession()
    {
        $sExpectedId = '1234567890';
        oxRegistry::getSession()->setVariable('stsprotection', $sExpectedId);
        $sActualId = $this->_SUT->getTsProductId();

        $this->assertSame($sExpectedId, $sActualId);
    }

    public function testGetTsProductId_returnsNull_whenTsIsNOTSetInSession()
    {
        $sExpectedId = null;
        oxRegistry::getSession()->setVariable('stsprotection', $sExpectedId);
        $sActualId = $this->_SUT->getTsProductId();

        $this->assertSame($sExpectedId, $sActualId);
    }

    public function testGetPaymentCost_returnsCorrectPaymentCost() {

        $fExpectedAmount = 99.99;
        $oPrice = new oxPrice();
        $oPrice->add($fExpectedAmount);
        $this->_SUT->setCost('oxpayment', $oPrice);

        $oPrice = $this->_SUT->getPaymentCost();
        $fActualAmount = $oPrice->getBruttoPrice();

        $this->assertEquals($fExpectedAmount, $fActualAmount);
    }

    public function testGetTotalDiscountSum_returnsCorrectTotalDiscountSum () {

        $fIndividualAmount = 9.99;
        $fExpectedTotalDiscountSum = $fIndividualAmount * 2;

        $oDiscount = new oxPrice();
        $oDiscount->add($fIndividualAmount);

        $this->_SUT
            ->expects($this->once())
            ->method('getTotalDiscount')
            ->will($this->returnValue($oDiscount));
        $this->_SUT
            ->expects($this->once())
            ->method('getVoucherDiscount')
            ->will($this->returnValue($oDiscount));

        $fActualTotalDiscountSum = $this->_SUT->getTotalDiscountSum();

        $this->assertEquals($fExpectedTotalDiscountSum, $fActualTotalDiscountSum);
    }

    public function emptyBasket()
    {
        $aBasketContents = $this->_SUT->getContents();
        foreach ($aBasketContents as $sItemKey => $oBasketItem) {
            $this->_SUT->removeItem($sItemKey);
        }
    }

    public function  testPaypInstallments_GetBasketGrandTotal()
    {
        $fExpectedValue = (double) 123.4567 * 5;
        $sExpectedObject = 'oxPrice';

        $oPrice = new oxPrice();
        $oPrice->setPrice($fExpectedValue);

        $this->_SUT->setPrice($oPrice);

        $oActualObject = $this->_SUT->paypInstallments_GetBasketGrandTotal();
        $fActualValue = $oActualObject->getPrice();

        $this->assertInstanceOf($sExpectedObject, $oActualObject);
        $this->assertSame(round($fExpectedValue,2), round($fActualValue,2));
    }
}
