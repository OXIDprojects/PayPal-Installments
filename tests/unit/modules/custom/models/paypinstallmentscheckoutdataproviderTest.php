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
 * Class paypInstallmentsCheckoutDataProviderTest
 * Tests for paypInstallmentsCheckoutDataProvider model.
 *
 * @see paypInstallmentsCheckoutDataProvider
 */
class paypInstallmentsCheckoutDataProviderTest extends OxidTestCase
{
    /**
     * Subject under the test.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsCheckoutDataProvider $SUT
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

        $this->SUT = $this->getMockBuilder('paypInstallmentsCheckoutDataProvider')
                    ->setMethods(array('__call', '_isPriceViewModeNetto'))
                    ->getMock();
        /** Set System Under Test in brutto price mode, i.e. NOT B2B mode */
        $this->SUT->expects($this->any())->method('_isPriceViewModeNetto')->will($this->returnValue(false));
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
    public static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * helper function to set up basket item data stuff
     *
     * @return array
     */
    protected function _getTestBasketItems()
    {
        $aTestBasketItems = array();

        $oBasketItemData = new oxArticle();
        $oBasketItemData->load('05848170643ab0deb9914566391c0c63');

        $aTestBasketItems[] = $oBasketItemData;

        $oBasketItemData = new oxArticle();
        $oBasketItemData->load('6b6b6abed58b118ee988c92856b8b675');

        $aTestBasketItems[] = $oBasketItemData;

        return $aTestBasketItems;
    }

    /**
     * helper function to get a shipping address for test purposes
     *
     * @return mixed
     */
    protected function _getTestShippingAddress()
    {
        $aShippingAddressData['oxaddress__oxcompany'] = 'OXID eSales AG';
        $aShippingAddressData['oxaddress__oxfname'] = 'Roland';
        $aShippingAddressData['oxaddress__oxlname'] = 'Fesenmayr';
        $aShippingAddressData['oxaddress__oxstreet'] = 'Bertoldstraße';
        $aShippingAddressData['oxaddress__oxstreetnr'] = '48';
        $aShippingAddressData['oxaddress__oxcity'] = 'Freiburg';
        $aShippingAddressData['oxaddress__oxcountryid'] = 'a7c40f631fc920687.20179984';
        $aShippingAddressData['oxaddress__oxstateid'] = 'AB';
        $aShippingAddressData['oxaddress__oxzip'] = '79098';
        $aShippingAddressData['oxaddress__oxfon'] = '+49 761 36889-0';
        $aShippingAddressData['oxaddress__oxsal'] = 'MR';

        return $aShippingAddressData;
    }

    protected function _getTestShippingAddressNonExistingState () {
        $aShippingAddressData = $this->__getTestShippingAddress();
        $aShippingAddressData['oxaddress__oxstateid'] = 'NOTEXISTS';

        return $aShippingAddressData;
    }

    protected function _getTestBillingAddress () {
        $aAddressData['oxuser__oxcompany'] = 'Your Company Name';
        $aAddressData['oxuser__oxfname'] = 'John';
        $aAddressData['oxuser__oxlname'] = 'Doe';
        $aAddressData['oxuser__oxstreet'] = 'Maple Street';
        $aAddressData['oxuser__oxstreetnr'] = '10';
        $aAddressData['oxuser__oxcity'] = 'Any City';
        $aAddressData['oxuser__oxcountryid'] = 'a7c40f631fc920687.20179984';
        $aAddressData['oxuser__oxstateid'] = 'AB';
        $aAddressData['oxuser__oxzip'] = '9041';
        $aAddressData['oxuser__oxfon'] = '+49 761 36889-0';
        $aAddressData['oxuser__oxsal'] = 'MR';

        return $aAddressData;
    }
    /**
     * helper function that will return filled oxBasket object
     *
     * @param bool|true $blSetItems
     *
     * @return oxBasket
     * @throws Exception
     * @throws null
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
     */
    protected function _getTestBasket($blSetItems = true, $blSetShippingAddress = true)
    {
        $oBasket = new oxBasket;

        //products to add
        if ($blSetItems) {
            foreach ($this->_getTestBasketItems() as $oBasketItemData) {
                $oBasket->addToBasket($oBasketItemData->getId(), 2);
            }
        }

        //set user to basket
        $oUser = new oxUser;
        $oUser->load('oxdefaultadmin');
        // Set the state id which is empty by default
        $oUser->oxuser__oxstateid = new oxField('AB');
        $oUser->save();

        $oBasket->setBasketUser($oUser);

        $oBasket->calculateBasket();

        if ($blSetShippingAddress) {
            //setting up delivery address:
            $oShippingAddress = new oxAddress;

            if (!$oShippingAddress->load('oxdefaultadmin_delivery_test')) {
                $oShippingAddress->setId('oxdefaultadmin_delivery_test');
            }
            $oShippingAddress->assign($this->_getTestShippingAddress());
            $oShippingAddress->save();

            $this->setSessionParam('deladrid', 'oxdefaultadmin_delivery_test');
        } else {
            $this->setSessionParam('deladrid', null);
        }

        return $oBasket;
    }

    /**
     * test setter and getter for basket
     */
    public function testSetGetBasket_validbasket()
    {
        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $this->assertInstanceOf('oxBasket', $this->SUT->getBasket());
    }

    /**
     * test setter and getter - invalid basket object
     */
    public function testSetGetBasket_novalidbasket()
    {
        //set expected exception because setBasket parameter should be from type oxBasket
        $this->setExpectedException(get_class(new PHPUnit_Framework_Error("", 0, "", 1)));

        $oBasket = new StdClass();
        $oBasket->whatever = 'I am no basket object';
        $this->SUT->setBasket($oBasket);
    }

    /**
     * test for getBasketItemDataList
     */
    public function testGetBasketItemDataListInNettoPriceMode()
    {

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsCheckoutDataProvider $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsCheckoutDataProvider')
                    ->setMethods(array('__call', '_isPriceViewModeNetto'))
                    ->getMock();
        /** Set System Under Test in brutto price mode, i.e. NOT B2B mode */
        $SUT->expects($this->any())->method('_isPriceViewModeNetto')->will($this->returnValue(true));

        //setting up basket
        $oBasket = $this->_getTestBasket();
        $SUT->setBasket($oBasket);

        $sExpectedCurrency = $oBasket->getBasketCurrency()->name;
        $aExpectedBasketItems = array();
        foreach ($oBasket->getContents() as $key => $oExpectedBasketItem) {
            $aExpectedBasketItems[] = $oExpectedBasketItem;
        }

        $aActualBasketItemDataList = $SUT->getBasketItemDataList();

        $this->assertCount(sizeof($aExpectedBasketItems), $aActualBasketItemDataList);

        foreach ($aActualBasketItemDataList as $key => $oActualBasketItem) {
            /** @var oxBasketItem $oExpectedBasketItem  */
            $oExpectedBasketItem = $aExpectedBasketItems[$key];
            $this->_validateBasketItemDataInNetPriceMode($oExpectedBasketItem, $sExpectedCurrency,
                                                               $oActualBasketItem);
        }
    }

    protected function _validateBasketItemDataInNetPriceMode(oxBasketItem $oExpectedBasketItem, $sExpectedCurrency,
                                                               $oActualBasketItem) {
            $this->assertEquals($oExpectedBasketItem->getTitle(), $oActualBasketItem->sName);
            $this->assertEquals($oExpectedBasketItem->getPrice()->getNettoPrice(), $oActualBasketItem->fPrice);
            $this->assertEquals($oExpectedBasketItem->getUnitPrice()->getNettoPrice(), $oActualBasketItem->fUnitPrice);
            $this->assertEquals($sExpectedCurrency, $oActualBasketItem->sCurrency);
            $this->assertEquals($oExpectedBasketItem->getAmount(), $oActualBasketItem->iQuantity);
    }

    protected function _validateBasketItemDataInBrutPriceMode(oxBasketItem $oExpectedBasketItem, $sExpectedCurrency,
                                                               $oActualBasketItem) {
            $this->assertEquals($oExpectedBasketItem->getTitle(), $oActualBasketItem->sName);
            $this->assertEquals($oExpectedBasketItem->getPrice()->getBruttoPrice(), $oActualBasketItem->fPrice);
            $this->assertEquals($oExpectedBasketItem->getUnitPrice()->getBruttoPrice(), $oActualBasketItem->fUnitPrice);
            $this->assertEquals($sExpectedCurrency, $oActualBasketItem->sCurrency);
            $this->assertEquals($oExpectedBasketItem->getAmount(), $oActualBasketItem->iQuantity);
    }

    /**
     * check if protected function _extractBasketItemInformation will return expected values
     */
    public function test_extractBasketItemInformationInNetPriceMode()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsCheckoutDataProvider $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsCheckoutDataProvider')
                    ->setMethods(array('__call', '_isPriceViewModeNetto'))
                    ->getMock();
        /** Set System Under Test in brutto price mode, i.e. NOT B2B mode */
        $SUT->expects($this->any())->method('_isPriceViewModeNetto')->will($this->returnValue(true));

        /** @var oxBasket $oBasket */
        $oBasket = $this->_getTestBasket();
        $SUT->setBasket($oBasket);

        $sExpectedCurrency = $oBasket->getBasketCurrency()->name;
        foreach ($oBasket->getContents() as $key => $oExpectedBasketItem) {
            $oActualBasketItem = self::callMethod(
                $SUT,
                '_extractBasketItemInformation',
                array($oExpectedBasketItem));

            $this->_validateBasketItemDataInNetPriceMode($oExpectedBasketItem, $sExpectedCurrency,
                                                               $oActualBasketItem);
        }
    }

    /**
     * check if protected function _extractBasketItemInformation will return expected values
     */
    public function test_extractBasketItemInformationInBruttoPriceMode()
    {


        /** @var oxBasket $oBasket */
        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $sExpectedCurrency = $oBasket->getBasketCurrency()->name;
        foreach ($oBasket->getContents() as $key => $oExpectedBasketItem) {
            $oActualBasketItem = self::callMethod(
                $this->SUT,
                '_extractBasketItemInformation',
                array($oExpectedBasketItem));

            /** @var $aExpectedBasketItem oxBasketItem */
            $this->_validateBasketItemDataInBrutPriceMode($oExpectedBasketItem, $sExpectedCurrency,
                                                               $oActualBasketItem);
        }
    }

    /**
     * check if address data is extracted correctly from basket user
     */
    public function testGetShippingAddressData()
    {
        $oTestShippingAddressData = $this->_getTestShippingAddress();
        $oExpectedShippingAddress = new StdClass();
        $oExpectedShippingAddress->sFirstname = $oTestShippingAddressData['oxaddress__oxfname'];
        $oExpectedShippingAddress->sLastname = $oTestShippingAddressData['oxaddress__oxlname'];
        $oExpectedShippingAddress->sStreet = $oTestShippingAddressData['oxaddress__oxstreet'].' '
                                      .$oTestShippingAddressData['oxaddress__oxstreetnr'];
        $oExpectedShippingAddress->sAddinfo = $oTestShippingAddressData['oxaddress__oxaddinfo']?$oTestShippingAddressData
        ['oxaddress__oxaddinfo']:'';
        $oExpectedShippingAddress->sCompany = $oTestShippingAddressData['oxaddress__oxcompany'];
        $oExpectedShippingAddress->sCity = $oTestShippingAddressData['oxaddress__oxcity'];
        $oExpectedShippingAddress->sState = $oTestShippingAddressData['oxaddress__oxstateid'];
        $oExpectedShippingAddress->sZip = $oTestShippingAddressData['oxaddress__oxzip'];
        $oExpectedShippingAddress->sCountry = 'DE';

        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $oActualShippingAddress = $this->SUT->getShippingAddressData();

        $this->assertEquals($oExpectedShippingAddress, $oActualShippingAddress);
    }

    /**
     * nearly the same test as testGetShippingAddressData()
     */
    public function test_getUserShippingAddress()
    {
        $oTestShippingAddressData = $this->_getTestShippingAddress();
        $oExpectedShippingAddress = new StdClass();
        $oExpectedShippingAddress->sFirstname = $oTestShippingAddressData['oxaddress__oxfname'];
        $oExpectedShippingAddress->sLastname = $oTestShippingAddressData['oxaddress__oxlname'];
        $oExpectedShippingAddress->sStreet = $oTestShippingAddressData['oxaddress__oxstreet'].' '.$oTestShippingAddressData['oxaddress__oxstreetnr'];
        $oExpectedShippingAddress->sAddinfo = $oTestShippingAddressData['oxaddress__oxaddinfo']?$oTestShippingAddressData['oxaddress__oxaddinfo']:'';
        $oExpectedShippingAddress->sCompany = $oTestShippingAddressData['oxaddress__oxcompany'];
        $oExpectedShippingAddress->sCity = $oTestShippingAddressData['oxaddress__oxcity'];
        $oExpectedShippingAddress->sState = $oTestShippingAddressData['oxaddress__oxstateid'];
        $oExpectedShippingAddress->sZip = $oTestShippingAddressData['oxaddress__oxzip'];
        $oExpectedShippingAddress->sCountry = 'DE';

        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);
        $oUser = $oBasket->getBasketUser();
        $oActualShippingAddress = self::callMethod($this->SUT, '_getUserShippingAddress', array($oUser));

        $this->assertEquals($oExpectedShippingAddress, $oActualShippingAddress);
    }

    /**
     * nearly the same test as testGetShippingAddressData()
     */
    public function test_getUserShippingAddress_retrunsStateNullForNonExistingState()
    {
        $oTestShippingAddressData = $this->_getTestBillingAddress();
        $oExpectedShippingAddress = new StdClass();
        $oExpectedShippingAddress->sFirstname = $oTestShippingAddressData['oxuser__oxfname'];
        $oExpectedShippingAddress->sLastname = $oTestShippingAddressData['oxuser__oxlname'];
        $oExpectedShippingAddress->sStreet = $oTestShippingAddressData['oxuser__oxstreet'].' '
                                             .$oTestShippingAddressData['oxuser__oxstreetnr'];
        $oExpectedShippingAddress->sAddinfo = $oTestShippingAddressData['oxuser__oxaddinfo']?$oTestShippingAddressData
        ['oxaddress__oxaddinfo']:'';
        $oExpectedShippingAddress->sCompany = $oTestShippingAddressData['oxuser__oxcompany'];
        $oExpectedShippingAddress->sCity = $oTestShippingAddressData['oxuser__oxcity'];
        $oExpectedShippingAddress->sState = null;
        $oExpectedShippingAddress->sZip = $oTestShippingAddressData['oxuser__oxzip'];
        $oExpectedShippingAddress->sCountry = 'DE';

        $oBasket = $this->_getTestBasket(true, false);
        $this->SUT->setBasket($oBasket);
        $oUser = $oBasket->getBasketUser();
        $oUser->oxuser__oxstateid = new oxField('NONEXISTIBG');

        $oActualShippingAddress = self::callMethod($this->SUT, '_getUserShippingAddress', array($oUser));

        $this->assertEquals($oExpectedShippingAddress, $oActualShippingAddress);
    }

    /**
     * test getter for TS protection cost
     */
    public function testGetTSProtectionCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oTSPrice = new oxPrice;
        $oTSPrice->add(10.9);

        $oBasket->setCost('oxtsprotection', $oTSPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(10.9, $this->SUT->getTSProtectionCosts());
    }

    /**
     * test if we get zero costs if there is no TS protection
     */
    public function testGetTSProtectionCosts_noCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oTSPrice = new oxPrice;
        $oTSPrice->add(0);

        $oBasket->setCost('oxtsprotection', $oTSPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getTSProtectionCosts());
    }

    /**
     * test payment costs if there is one, use cashondel because there is some costs
     */
    public function testGetPaymentCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oBasket->setPayment('oxidcashondel');
        $this->SUT->setBasket($oBasket);

        $fPaymentCosts = $oBasket->getPaymentCost()->getBruttoPrice();

        $this->assertEquals($fPaymentCosts, $this->SUT->getPaymentCosts());
    }

    /**
     * test if function works correctly if there are no costs
     */
    public function testGetPaymentCosts_noCosts()
    {
        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getPaymentCosts());
    }

    /**
     * test wrapping costs getter
     */
    public function testGetWrappingCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oWrappingPrice = new oxPrice;
        $oWrappingPrice->add(5.55);

        $oBasket->setCost('oxwrapping', $oWrappingPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(5.55, $this->SUT->getWrappingCosts());
    }

    /**
     * test wrapping costs getter - no costs there
     */
    public function testGetWrappingCosts_noCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oWrappingPrice = new oxPrice;
        $oWrappingPrice->add(0);

        $oBasket->setCost('oxwrapping', $oWrappingPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getWrappingCosts());
    }

    /**
     * test shipping costs getter
     */
    public function testGetShippingCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oShippingPrice = new oxPrice;
        $oShippingPrice->add(6.9);

        $oBasket->setCost('oxdelivery', $oShippingPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(6.9, $this->SUT->getShippingCosts());
    }

    /**
     * test shipping costs getter - no costs there
     */
    public function testGetShippingCosts_noCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oShippingPrice = new oxPrice;
        $oShippingPrice->add(0);

        $oBasket->setCost('oxdelivery', $oShippingPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getShippingCosts());
    }

    /**
     * test return of getCurrency
     */
    public function testGetCurrency()
    {
        $oBasket = $this->_getTestBasket();
        $oCurrency = new StdClass();
        $oCurrency->name = 'EUR';
        $oBasket->setBasketCurrency($oCurrency);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals('EUR', $this->SUT->getCurrency());
        $this->assertNotEquals('USD', $this->SUT->getCurrency());
    }

    /**
     * test item total function
     */
    public function testGetItemTotalInNetMode()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsCheckoutDataProvider $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsCheckoutDataProvider')
                    ->setMethods(array('__call', '_isPriceViewModeNetto'))
                    ->getMock();
        /** Set System Under Test in brutto price mode, i.e. NOT B2B mode */
        $SUT->expects($this->any())->method('_isPriceViewModeNetto')->will($this->returnValue(true));

        $oBasket = $this->_getTestBasket();
        $SUT->setBasket($oBasket);

        $fItemTotal = 0;

        foreach ($oBasket->getContents() as $oBasketItem) {
            /** @var oxBasketItem $oBasketItem  */
            $fItemTotal += $oBasketItem->getPrice()->getNettoPrice();
        }

        $this->assertEquals($fItemTotal, $SUT->getItemTotal());
    }

    /**
     * test item total function
     */
    public function testGetItemTotalInBrutMode()
    {
        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $fItemTotal = 0;

        foreach ($oBasket->getContents() as $oBasketItem) {
            /** @var oxBasketItem $oBasketItem  */
            $fItemTotal += $oBasketItem->getPrice()->getBruttoPrice();
        }

        $this->assertEquals($fItemTotal, $this->SUT->getItemTotal());
    }

    /**
     * test item total function with no items
     */
    public function testGetItemTotal_noItems()
    {
        $oBasket = $this->_getTestBasket(false);
        $this->SUT->setBasket($oBasket);

        $fItemTotal = 0;

        $this->assertEquals($fItemTotal, $this->SUT->getItemTotal());
    }

    /**
     * test order total function
     */
    public function testGetOrderTotalInBrutMode()
    {
        $oBasket = $this->_getTestBasket();
        $this->SUT->setBasket($oBasket);

        $this->assertEquals($oBasket->getBruttoSum(), $this->SUT->getOrderTotal());
    }

    /**
     * Test order total in netto mode.
     */
    public function testGetOrderTotalInNetMode()
    {
        $this->setConfigParam('blEnterNetPrice', true);
        /** @var paypInstallmentsCheckoutDataProvider|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsCheckoutDataProvider')
            ->setMethods(array('__call',))
            ->getMock();

        $oBasket = $this->_getTestBasket();
        $oSUT->setBasket($oBasket);


        $this->assertEquals($oBasket->getBruttoSum(), $oSUT->getOrderTotal());

        $this->setConfigParam('blEnterNetPrice', false);
    }

    /**
     * test get giftcard costs function
     */
    public function testGetGiftcardCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oGiftcardPrice = new oxPrice;
        $oGiftcardPrice->add(20.14);

        $oBasket->setCost('oxgiftcard', $oGiftcardPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(20.14, $this->SUT->getGiftcardCosts());
    }

    /**
     * test get giftcard costs function - no costs set
     */
    public function testGetGiftcardCosts_noCosts()
    {
        $oBasket = $this->_getTestBasket();
        $oGiftcardPrice = new oxPrice;
        $oGiftcardPrice->add(0);

        $oBasket->setCost('oxgiftcard', $oGiftcardPrice);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getGiftcardCosts());
    }

    /**
     * test get total discount sum function
     */
    public function testGetTotalDiscountSum()
    {
        $oBasket = $this->_getTestBasket();

        $oBasket->setTotalDiscount(15);
        $oBasket->setVoucherDiscount(20);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(35, $this->SUT->getTotalDiscountSum());
    }

    /**
     * test get total discount sum function - no discounts set
     */
    public function testGetTotalDiscountSum_noDiscounts()
    {
        $oBasket = $this->_getTestBasket();

        $oBasket->setTotalDiscount(0);
        $oBasket->setVoucherDiscount(0);

        $this->SUT->setBasket($oBasket);

        $this->assertEquals(0, $this->SUT->getTotalDiscountSum());
    }

    /**
     * @dataProvider dataProviderPhysicalArticleParameters
     *
     * @param $blNonMaterial
     * @param $blIsDownloadable
     * @param $blExpectedReturnValue
     */
    public function testIsPhysicalArticle_returnsCorrectValue($blNonMaterial, $blIsDownloadable,
                                                               $blExpectedReturnValue) {
        $oArticle = new oxArticle;
        $oArticle->oxarticles__oxnonmaterial = new oxField($blNonMaterial, oxField::T_RAW);
        $oArticle->oxarticles__oxisdownloadable = new oxField($blIsDownloadable, oxField::T_RAW);

        $oBasketItem = $this->getMock('oxBasketItem', array('__call', 'getArticle'));
        $oBasketItem
            ->expects($this->any())
            ->method('getArticle')
            ->will($this->returnValue($oArticle));

        $this->assertEquals(
            $blExpectedReturnValue,
            self::callMethod($this->SUT, '_isPhysicalArticle', array($oBasketItem))
        );
    }

    public function dataProviderPhysicalArticleParameters() {
        return array(
            // $blNonMaterial, $blIsDownloadable, $blExpectedReturnValue
            array(true,true,false),
            array(true,false,false),
            array(false,true,false),
            array(false,false,true),
        );
    }
}
