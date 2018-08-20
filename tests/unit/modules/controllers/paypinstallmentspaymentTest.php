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
 * Class paypInstallments_paymentTest
 *
 * @covers paypInstallmentsPayment
 */
class paypInstallments_paymentTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment
     */
    protected $_SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallInitParent',
                      '_paypInstallments_CallGetPaymentListParent',
                      '_paypInstallments_CallValidatePaymentParent',
                      '_paypInstallments_GetPaymentList',
                      '_paypInstallments_GetSetExpressCheckoutHandler',
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

    public function testInit_callsParentMethod()
    {
        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallInitParent');

        $this->_SUT->init();
    }

    public function testInit_deletesSessionRegistry()
    {

        $this->setSessionParam(paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey, array());

        $SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallInitParent',
                )
            )
            ->getMock();

        $SUT->init();

        $aRegistry = $this->getSessionParam(paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey);

        $this->assertNull($aRegistry);
    }

    public function testGetPaymentList_callsParentMethod()
    {
        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallGetPaymentListParent');

        $this->_SUT->getPaymentList();
    }

    public function testGetPaymentList_returnsParentMethodsPaymentList_onSuccess()
    {

        $aExpectedPaymentList = array(
            paypInstallmentsConfiguration::getPaymentId() => 'value'
        );

        $SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallGetPaymentListParent',
                      '_paypInstallments_GetPaymentList',
                      '_paypInstallments_GetBasketFromSession',
                      '_paypInstallments_GetRequirementsValidator'
                )
            )
            ->getMock();

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetPaymentList')
            ->will($this->returnValue($aExpectedPaymentList));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetBasketFromSession')
            ->will($this->returnValue($this->getBasketMock()));

        $oRequirementsValidatorMock = $this
            ->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(array('validateRequirements'))
            ->getMock();

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetRequirementsValidator')
            ->will($this->returnValue($oRequirementsValidatorMock));

        $aActualPaymentList = $SUT->getPaymentList();
        $this->assertEquals($aExpectedPaymentList, $aActualPaymentList);
    }

    public function testGetPaymentList_unsetsPayPalInstallmentPaymentMethod_onRequirementsUnmet()
    {
        $aInitialPaymentList = array(
            paypInstallmentsConfiguration::getPaymentId() => 'value'
        );
        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetPaymentList')
            ->will($this->returnValue($aInitialPaymentList));

        $aPaymentList = $this->_SUT->getPaymentList();

        $this->assertArrayNotHasKey(paypInstallmentsConfiguration::getPaymentId(), $aPaymentList);
    }

    public function testValidatePayment_callsParentMethod()
    {
        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallValidatePaymentParent');

        $this->_SUT->validatePayment();
    }

    public function testValidatePayment_redirectsToPayPal_onSuccess()
    {
        $this->setRequestParam('paymentid', paypInstallmentsConfiguration::getPaymentId());
        $sExpectedReturnValue = 'order';

        /**
         * System under test
         *
         * @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment
         */
        $SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallValidatePaymentParent',
                      '_paypInstallments_getPaymentIdFromSession',
                      '_paypInstallments_GetSetExpressCheckoutHandler',
                      '_paypInstallments_StorePayPalInstallmentsDataInRegistry',
                      '_paypInstallments_RedirectToPayPal'
                )
            )
            ->getMock();
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallValidatePaymentParent')
            ->will($this->returnValue($sExpectedReturnValue));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue(paypInstallmentsConfiguration::getPaymentId()));

        /**
         * Get Mock for paypInstallmentsSetExpressCheckoutHandler
         */
        $oSetExpressCheckoutHandlerMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutHandler')
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();;
        $oSetExpressCheckoutHandlerMock
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue('token'));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetSetExpressCheckoutHandler')
            ->will(
                $this->returnValue($oSetExpressCheckoutHandlerMock)
            );

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_StorePayPalInstallmentsDataInRegistry');

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_RedirectToPayPal');

        $SUT->validatePayment();
    }

    public function testValidatePayment_storesPayPalInstallmentsDataInRegistry_onSuccess()
    {
        $this->setSessionParam(paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey, array());
        $sExpectedToken = 'TOKEN';
        $sExpectedBasketFingerprint = '0000000000000';
        $sExpectedBasketItemsFingerprint = '0000000000000';
        $sExpectedShippingCountry = 'DE';

        $this->setRequestParam('paymentid', paypInstallmentsConfiguration::getPaymentId());
        $sExpectedReturnValue = 'order';

        $SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallValidatePaymentParent',
                      '_paypInstallments_getPaymentIdFromSession',
                      '_paypInstallments_GetSetExpressCheckoutHandler',
                      '_paypInstallments_GetCheckoutDataProvider',
                      '_paypInstallments_GetBasketFromSession',
                      '_paypInstallments_RedirectToPayPal'
                )
            )
            ->getMock();
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallValidatePaymentParent')
            ->will($this->returnValue($sExpectedReturnValue));
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue(paypInstallmentsConfiguration::getPaymentId()));

        /**
         * Get Mock for paypInstallmentsSetExpressCheckoutHandler
         */
        $oSetExpressCheckoutHandlerMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutHandler')
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();;
        $oSetExpressCheckoutHandlerMock
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($sExpectedToken));
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetSetExpressCheckoutHandler')
            ->will(
                $this->returnValue($oSetExpressCheckoutHandlerMock)
            );

        $oBasketMock = $this->getBasketMock($sExpectedBasketItemsFingerprint);
        $SUT
            ->expects($this->any())
            ->method('_paypInstallments_GetBasketFromSession')
            ->will(
                $this->returnValue($oBasketMock)
            );

        $oCheckoutDataProviderMock = $this
            ->getMockBuilder('paypInstallmentsCheckoutDataProvider')
            ->setMethods(array('getShippingAddressData'))
            ->getMock();

        $oShippingAddress = new StdClass();
        $oShippingAddress->sCountry = $sExpectedShippingCountry;
        $oCheckoutDataProviderMock
            ->expects($this->once())
            ->method('getShippingAddressData')
            ->will($this->returnValue($oShippingAddress));
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetCheckoutDataProvider')
            ->will($this->returnValue($oCheckoutDataProviderMock));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_RedirectToPayPal');

        $SUT->validatePayment();

        $aRegistry = $this->getSessionParam(paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey);

        $sActualToken = $aRegistry[paypInstallmentsOxSession::sPayPalTokenKey];
        $sActualBasketFingerprint = $aRegistry[paypInstallmentsOxSession::sBasketFingerprintKey];
        $sActualShippingCountry = $aRegistry[paypInstallmentsOxSession::sShippingCountryKey];

        $this->assertEquals($sExpectedToken, $sActualToken);
        $this->assertEquals($sExpectedBasketFingerprint, $sActualBasketFingerprint);
        $this->assertEquals($sExpectedShippingCountry, $sActualShippingCountry);
    }

    /**
     * @dataProvider dataProviderDoRequestException
     *
     * @param $oException
     */
    public function testValidatePayment_callsPaHandlePayPalInstallmentsDoRequestException_onError($oException)
    {
        $expectedReturnValue = null;

        $this->setRequestParam('paymentid', paypInstallmentsConfiguration::getPaymentId());
        $SUT = $this->getMockBuilder('paypInstallmentsPayment')
            ->setMethods(
                array('__call',
                      '__construct',
                      '_paypInstallments_CallValidatePaymentParent',
                      '_paypInstallments_getPaymentIdFromSession',
                      '_paypInstallments_GetSetExpressCheckoutHandler',
                      '_paypInstallments_StorePayPalInstallmentsDataInRegistry',
                      '_paypInstallments_RedirectToPayPal',
                )
            )
            ->getMock();
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_CallValidatePaymentParent')
            ->will($this->returnValue(true));
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue(paypInstallmentsConfiguration::getPaymentId()));
        /**
         * Get Mock for paypInstallmentsSetExpressCheckoutHandler
         */
        $oSetExpressCheckoutHandlerMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutHandler')
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();;
        $oSetExpressCheckoutHandlerMock
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->throwException($oException));

        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_GetSetExpressCheckoutHandler')
            ->will(
                $this->returnValue($oSetExpressCheckoutHandlerMock)
            );

        $SUT
            ->expects($this->never())
            ->method('_paypInstallments_StorePayPalInstallmentsDataInRegistry');

        $SUT
            ->expects($this->never())
            ->method('_paypInstallments_RedirectToPayPal');

        $actualReturnValue = $SUT->validatePayment();

        $this->assertSame($expectedReturnValue, $actualReturnValue);
    }

    public function dataProviderDoRequestException()
    {
        return array(
            array(new paypInstallmentsMalformedRequestException()),
            array(new paypInstallmentsMalformedResponseException()),
            array(new paypInstallmentsSetExpressCheckoutRequestValidationException()),
            array(new paypInstallmentsVersionMismatchException()),
            array(new paypInstallmentsNoAckSuccessException()),
        );
    }

    public function testValidatePayment_codeCoverage()
    {
        $this->setRequestParam('paymentid', paypInstallmentsConfiguration::getPaymentId());
        $exception = new Exception('test-pa-exception');
        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(
                array('__call',
                      '_paypInstallments_CallValidatePaymentParent',
                      '_paypInstallments_getPaymentIdFromSession',
                      '_paypInstallments_GetBasketFromSession',
                      '_paypInstallments_GetSetExpressCheckoutHandler',
                      '_paypInstallments_StorePayPalInstallmentsDataInRegistry',
                      '_paypInstallments_RedirectToPayPal',
                      '_paypInstallments_HandlePayPalInstallmentsDoRequestException'
                )
            )
            ->getMock();
        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_CallValidatePaymentParent')
            ->will($this->returnValue(true));
        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getPaymentIdFromSession')
            ->will($this->returnValue(paypInstallmentsConfiguration::getPaymentId()));
        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_GetBasketFromSession')
            ->will($this->returnValue(new oxBasket()));
        $subjectUnderTest->expects($this->any())
            ->method('_paypInstallments_GetSetExpressCheckoutHandler')
            ->will($this->throwException($exception));
        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_HandlePayPalInstallmentsDoRequestException')
            ->with($this->equalTo($exception));
        $subjectUnderTest->expects($this->never())
            ->method('_paypInstallments_StorePayPalInstallmentsDataInRegistry');
        $subjectUnderTest->expects($this->never())
            ->method('_paypInstallments_RedirectToPayPal');

        $subjectUnderTest->validatePayment();
    }

    public function dataProviderForUnmetRequirements()
    {
        return array();
    }

    public function getBasketMock($sExpectedBasketItemsFingerprint = null)
    {
        $oBasketMock = $this->getMockBuilder('oxBasket')
            ->setMethods(array('getBasketUser', 'paypInstallments_GetBasketItemsFingerprint'))
            ->getMock();

        $oBasketMock
            ->expects($this->any())
            ->method('getBasketUser')
            ->will($this->returnValue(new oxUser()));

        $oBasketMock
            ->expects($this->any())
            ->method('paypInstallments_GetBasketItemsFingerprint')
            ->will($this->returnValue($sExpectedBasketItemsFingerprint));

        return $oBasketMock;
    }

    public function testGetCountryService()
    {
        /** @var paypInstallmentsPayment $subjectUnderTest */
        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $this->assertInstanceOf('oxCountry', $subjectUnderTest->getCountryService());
    }

    public function testGetBillingCountryCode()
    {
        $sCountryId = 'test-pa-country-id';
        $sCountryCode = 'test-pa-country-code';

        $oUser = $this->getMockBuilder('oxUser')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldData'))
            ->getMock();
        $oUser->expects($this->once())
            ->method('getFieldData')
            ->with('oxcountryid')
            ->will($this->returnValue($sCountryId));

        $oBasket = $this->getMockBuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('getBasketUser'))
            ->getMock();
        $oBasket->expects($this->once())
            ->method('getBasketUser')
            ->will($this->returnValue($oUser));

        $oCountry = $this->getMockBuilder('oxCountry')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getFieldData'))
            ->getMock();
        $oCountry->expects($this->once())
            ->method('load')
            ->with($sCountryId);
        $oCountry->expects($this->once())
            ->method('getFieldData')
            ->with('OXISOALPHA2')
            ->will($this->returnValue($sCountryCode));

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment $subjectUnderTest */
        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(
                array('__call',
                      '_paypInstallments_GetBasketFromSession',
                      'getCountryService',
                )
            )
            ->getMock();
        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_GetBasketFromSession')
            ->will($this->returnValue($oBasket));
        $subjectUnderTest->expects($this->once())
            ->method('getCountryService')
            ->will($this->returnValue($oCountry));

        $this->assertEquals($sCountryCode, $subjectUnderTest->getBillingCountryCode());
    }

    /**
     * @param $sExpectedClass
     * @param $sGetterFunction
     *
     * @dataProvider dataProviderObjectGetters
     */
    public function testObjectGetters_returnExpectedObjects($sExpectedClass, $sGetterFunction)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(
                array('__call',
                )
            )
            ->getMock();
        $oActualObject = paypInstallments_paymentTest::callMethod($oSubjectUnderTest, $sGetterFunction , array());

        $this->assertInstanceOf($sExpectedClass, $oActualObject);
    }

    public function dataProviderObjectGetters() {
        return array(
            array('oxSession', '_paypInstallments_GetSession'),
            array('paypInstallmentsRequirementsValidator', '_paypInstallments_GetRequirementsValidator'),
            array('paypInstallmentsCheckoutDataProvider', '_paypInstallments_GetCheckoutDataProvider'),
            array('paypInstallmentsSetExpressCheckoutHandler', '_paypInstallments_GetSetExpressCheckoutHandler'),
        );
    }

    public function testGetRequirementsValidator_returnsInstanceOfValidator() {
        $sExcpectedClass = 'paypInstallmentsRequirementsValidator';

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(
                array('__call',
                )
            )
            ->getMock();

        $oActualObject = paypInstallments_paymentTest::callMethod($oSubjectUnderTest,'_paypInstallments_GetRequirementsValidator', array());

        $this->assertInstanceOf($sExcpectedClass, $oActualObject);
    }

    /**
     * @dataProvider dataProviderTestGetCheckedTsProductId
     *
     * @param $sRequestTsProductId
     * @param $sSessionTsProductId
     * @param $sExpectedTsProductId
     * @param $sMessage
     */
    public function testGetCheckedTsProductId_returnsExpectedValue($sRequestTsProductId, $sSessionTsProductId, $sExpectedTsProductId, $sMessage) {

        $this->setRequestParam('stsprotection', $sRequestTsProductId);
        $this->setSessionParam('stsprotection', $sSessionTsProductId);

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPayment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPayment')
            ->disableOriginalConstructor()
            ->setMethods(
                array('__call',
                )
            )
            ->getMock();

        $sActualTsProductId = $oSubjectUnderTest->getCheckedTsProductId();

        $this->assertEquals($sExpectedTsProductId, $sActualTsProductId, $sMessage);
    }

    public function dataProviderTestGetCheckedTsProductId() {
        return array(
            // array($sRequestTsProductId, $sSessionTsProductId, $sExpectedTsProductId, $sMessage),
            array(null, null, false, 'Value is false, if REQUEST variable and SESSION variable are not set'),
            array('sRequestTsProductId', null, 'sRequestTsProductId', 'REQUEST variable is returned, if SESSION variable is NOT set'),
            array('sRequestTsProductId', 'sSessionTsProductId', 'sRequestTsProductId', 'REQUEST variable wins over SESSION variable, if both are set'),
            array(null, 'sSessionTsProductId', 'sSessionTsProductId', 'SESSION variable is used as a fallback, if REQUEST variable is null'),
        );
    }
}
