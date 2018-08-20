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
class paypInstallmentsSetExpressCheckoutHandlerTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var $_SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsSetExpressCheckoutHandler
     */
    protected $_SUT;

    /**
     * Values for request validation
     */
    protected $_fValidOrderTotalValue;
    protected $_sValidOrderCurrencyID;
    protected $_sValidAddressCountry;
    protected $_sValidLandingPage;
    protected $_sValidFundingSource;

    protected $_fNotValidOrderTotalValue;
    protected $_sNotValidOrderCurrencyID;
    protected $_sNotValidAddressCountry;
    protected $_sNotValidLandingPage;
    protected $_sNotValidFundingSource;

    /**
     * Values for response validation
     */
    protected $_sValidResponseAck;
    protected $_sValidResponseVersion;
    protected $_sValidResponseToken;

    protected $_sNotValidResponseAck;
    protected $_sNotValidResponseVersion;
    protected $_sNotValidResponseToken;

    protected $_sMissingResponseAck;
    protected $_sMissingResponseVersion;
    protected $_sMissingResponseToken;

    /**
     *
     * Data Providers. These functions are called before setUp()
     *
     */

    /**
     * Provider for Invalid Request objects.
     * See Expected Exception Message for unmet conditions
     *
     * @return array
     */
    public function dataProviderInvalidRequest()
    {

        $this->populateValidationValues();

        $sExpectedExceptionClass = 'paypInstallmentsSetExpressCheckoutRequestValidationException';

        return array(
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    $this->_fNotValidOrderTotalValue,
                    $this->_sNotValidOrderCurrencyID,
                    $this->_sNotValidAddressCountry,
                    $this->_sNotValidLandingPage,
                    $this->_sNotValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('MINIMAL_QUALIFYING_ORDER_TOTAL_NOT_MET')
            ),
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.01,
                    $this->_sNotValidOrderCurrencyID,
                    $this->_sNotValidAddressCountry,
                    $this->_sNotValidLandingPage,
                    $this->_sNotValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('MAXIMAL_QUALIFYING_ORDER_TOTAL_EXCEEDED')
            ),
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    $this->_fValidOrderTotalValue,
                    $this->_sNotValidOrderCurrencyID,
                    $this->_sValidAddressCountry,
                    $this->_sValidLandingPage,
                    $this->_sValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_ORDER_CURRENCY')
            ),
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    $this->_fValidOrderTotalValue,
                    $this->_sValidOrderCurrencyID,
                    $this->_sNotValidAddressCountry,
                    $this->_sValidLandingPage,
                    $this->_sValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_SHIPPING_COUNTRY')
            ),
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    $this->_fValidOrderTotalValue,
                    $this->_sValidOrderCurrencyID,
                    $this->_sValidAddressCountry,
                    $this->_sNotValidLandingPage,
                    $this->_sValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_LANDING_PAGE')
            ),
            array(
                // Mocked Invalid Request
                $this->getSetExpressCheckoutReqMock(
                    $this->_fValidOrderTotalValue,
                    $this->_sValidOrderCurrencyID,
                    $this->_sValidAddressCountry,
                    $this->_sValidLandingPage,
                    $this->_sNotValidFundingSource
                ),
                // Expected Exception Class
                $sExpectedExceptionClass,
                // Expected Exception Message
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_FUNDING_SOURCE')
            ),
        );
    }

    /**
     * Provider for invalid Response Objects
     *
     * @return array
     */
    public function dataProviderMalformedResponse()
    {
        $this->populateValidationValues();

        return array(
            // All properties are null
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sMissingResponseAck,
                    $this->_sMissingResponseVersion,
                    $this->_sMissingResponseToken
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Ack is null
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sMissingResponseAck,
                    $this->_sValidResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Ack is not valid
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sNotValidResponseAck,
                    $this->_sValidResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsNoAckSuccessException'
            ),
            // Response Ack is empty
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    '',
                    $this->_sValidResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsNoAckSuccessException'
            ),
            // Response Ack is object
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    new StdClass(),
                    $this->_sValidResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsNoAckSuccessException'
            ),
            // Response Ack is array
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    array('Success'),
                    $this->_sValidResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsNoAckSuccessException'
            ),
            // Response Version is null
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    $this->_sMissingResponseVersion,
                    $this->_sValidResponseToken
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Version is not valid
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    '106.0',
                    $this->_sValidResponseToken
                ),
                'paypinstallmentsversionmismatchexception'
            ),
            // Response Version is empty
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    '',
                    $this->_sValidResponseToken
                ),
                'paypinstallmentsversionmismatchexception'
            ),
            // Response Version is object
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    new StdClass(),
                    $this->_sValidResponseToken
                ),
                'paypinstallmentsversionmismatchexception'
            ),
            // Response Version is array
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    array('124.0'),
                    $this->_sValidResponseToken
                ),
                'paypinstallmentsversionmismatchexception'
            ),
            // Response Token is null
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    $this->_sValidResponseVersion,
                    $this->_sMissingResponseToken
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Token is empty
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    $this->_sValidResponseVersion,
                    ''
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Token is object
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    $this->_sValidResponseVersion,
                    new StdClass()
                ),
                'paypInstallmentsMalformedResponseException'
            ),
            // Response Token is array
            array(
                $this->getSetExpressCheckoutResponseTypeMock(
                    $this->_sValidResponseAck,
                    $this->_sValidResponseVersion,
                    array('TOKEN')
                ),
                'paypInstallmentsMalformedResponseException'
            ),
        );
    }

    /**
     * Set get a mock of class paypInstallmentsSetExpressCheckoutHandler
     */
    public function setUp()
    {
        parent::setUp();

        $this->populateValidationValues();

        $this->_SUT = $this->getMock(
            'paypInstallmentsSetExpressCheckoutHandler',
            array('_getObjectGenerator',
                  '_getParser',
                  '_getValidator',
                  '_throwSetExpressCheckoutException')
        );
        $oBasketMock = $this->getBasketMock();
        $this->_SUT->setBasket($oBasketMock);
    }

    protected function populateValidationValues()
    {
        $oModuleConfiguration = new paypInstallmentsConfiguration();

        /**
         * Values for request validation
         */
        $this->_fValidOrderTotalValue = paypInstallmentsConfiguration::getPaymentMethodMinAmount();
        $this->_sValidOrderCurrencyID = $oModuleConfiguration->getRequiredOrderTotalCurrency();
        $this->_sValidAddressCountry = $oModuleConfiguration->getRequiredShippingCountry();
        $this->_sValidLandingPage = $oModuleConfiguration->getRequiredLandingPage();
        $this->_sValidFundingSource = $oModuleConfiguration->getRequiredFundingSource();

        $this->_fNotValidOrderTotalValue = paypInstallmentsConfiguration::getPaymentMethodMinAmount() - 0.01;
        $this->_sNotValidOrderCurrencyID = 'INVALID_' . $oModuleConfiguration->getRequiredOrderTotalCurrency();
        $this->_sNotValidAddressCountry = 'INVALID_' . $oModuleConfiguration->getRequiredShippingCountry();
        $this->_sNotValidLandingPage = 'INVALID_' . $oModuleConfiguration->getRequiredLandingPage();
        $this->_sNotValidFundingSource = 'INVALID_' . $oModuleConfiguration->getRequiredFundingSource();

        /**
         * Values for response validation
         */
        $this->_sValidResponseAck = paypInstallmentsConfiguration::getResponseAckSuccess();
        $this->_sValidResponseVersion = paypInstallmentsConfiguration::getServiceVersion();
        $this->_sValidResponseToken = 'TOKEN';

        $this->_sNotValidResponseAck = 'INVALID_' . paypInstallmentsConfiguration::getResponseAckSuccess();
        $this->_sNotValidResponseVersion = 'INVALID_' . paypInstallmentsConfiguration::getServiceVersion();

        $this->_sMissingResponseAck = null;
        $this->_sMissingResponseVersion = null;
        $this->_sMissingResponseToken = null;
    }

    public function testDoRequest_returnsExpectedToken_onSuccess()
    {
        $sExpectedToken = $this->_sValidResponseToken;

        $oValidRequestMock = $this->getSetExpressCheckoutReqMock(
            $this->_fValidOrderTotalValue,
            $this->_sValidOrderCurrencyID,
            $this->_sValidAddressCountry,
            $this->_sValidLandingPage,
            $this->_sValidFundingSource
        );

        /**
         * Mock the PayPalServiceObject so that it does not do real requests to PayPal and does not complain about any
         * wrong
         * params
         */
        $oValidResponseMock = $this->getSetExpressCheckoutResponseTypeMock(
            $this->_sValidResponseAck,
            $this->_sValidResponseVersion,
            $this->_sValidResponseToken
        );

        $oPayPalServiceObjectMock = $this
            ->getMockBuilder('\PayPal\Service\PayPalAPIInterfaceServiceService')
            ->setMethods(array('SetExpressCheckout'))
            ->getMock();
        $oPayPalServiceObjectMock
            ->expects($this->once())
            ->method('SetExpressCheckout')
            ->will($this->returnValue($oValidResponseMock));

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getPayPalServiceObject', 'getSetExpressCheckoutReqObject'))
            ->getMock();
        // $oSdkMock
        //    ->expects($this->once())
        //    ->method('getSetExpressCheckoutReqObject')
        //    ->will($this->returnValue($oRequest));
        $oSdkMock
            ->expects($this->once())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue($oPayPalServiceObjectMock));
        $oSdkMock
            ->expects($this->once())
            ->method('getSetExpressCheckoutReqObject')
            ->will($this->returnValue($oValidRequestMock));
        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        /**
         * Pass the real validator so it will throw an exception
         */
        $oValidator = new paypInstallmentsSetExpressCheckoutValidator();
        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidator));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));


        $sActualToken = $this->_SUT->doRequest();

        $this->assertEquals($sExpectedToken, $sActualToken);
    }

    public function testDoRequest_reThrowsPayPalException_onSetExpressCheckoutFailure()
    {
        $oException = new Exception();
        $this->setExpectedException('paypinstallmentssetexpresscheckoutexception');

        /** @var  $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsSetExpressCheckoutHandler */
        $SUT = $this->getMockBuilder('paypInstallmentsSetExpressCheckoutHandler')
            ->setMethods(
                array(
                    '_getObjectGenerator',
                    '_getParser',
                    '_getValidator',
                )
            )
            ->getMock();
        $oBasketMock = $this->getBasketMock();
        $SUT->setBasket($oBasketMock);

        /**
         * Mock the PayPalServiceObject so that it does not do real requests and does not complain about any wrong params
         */
        $oPayPalServiceObjectMock = $this
            ->getMockBuilder('\PayPal\Service\PayPalAPIInterfaceServiceService')
            ->setMethods(array('SetExpressCheckout'))
            ->getMock();
        $oPayPalServiceObjectMock
            ->expects($this->once())
            ->method('SetExpressCheckout')
            ->will($this->throwException($oException));

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getPayPalServiceObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue($oPayPalServiceObjectMock));
        $SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        /**
         * Mock the validator so it will not throw an exception
         */
        $oValidatorMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutValidator')
            ->setMethods(array('validateRequest'))
            ->getMock();

        $SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidatorMock));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        $SUT->doRequest();
    }

    public function testDoRequest_throwsVersionMismatchException_onWrongResponseVersion()
    {
        $this->setExpectedException('paypInstallmentsVersionMismatchException');

        /**
         * Mock the PayPalServiceObject so that it does not do real requests and does not complain about any wrong params
         */
        $oResponse = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutResponseType');
        $oResponse->Ack = 'Success';
        $oResponse->Version = '106.0';

        $oPayPalServiceObjectMock = $this
            ->getMockBuilder('\PayPal\Service\PayPalAPIInterfaceServiceService')
            ->setMethods(array('SetExpressCheckout'))
            ->getMock();
        $oPayPalServiceObjectMock
            ->expects($this->once())
            ->method('SetExpressCheckout')
            ->will($this->returnValue($oResponse));

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getPayPalServiceObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue($oPayPalServiceObjectMock));

        /**
         * Mock the validator so it will not validate the request
         */
        $oValidatorMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutValidator')
            ->setMethods(array('validateRequest'))
            ->getMock();

        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidatorMock));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        $this->_SUT->doRequest();
    }

    public function testDoRequest_throwsNoAckSuccessException_onAckFailure()
    {
        $this->setExpectedException('paypInstallmentsNoAckSuccessException');

        $oError = new StdClass();
        $oError->ErrorCode = 10000;
        $oError->ShortMessage = 'ShortMessage';
        $oError->LongMessage = 'LongMessage';

        /**
         * Mock the PayPalServiceObject so that it does not do real requests and does not complain about any wrong params
         */
        $oResponse = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutResponseType');
        $oResponse->Ack = 'Failure';
        $oResponse->Version = '124.0';
        $oResponse->Errors = array($oError, $oError, $oError);

        $oPayPalServiceObjectMock = $this
            ->getMockBuilder('\PayPal\Service\PayPalAPIInterfaceServiceService')
            ->setMethods(array('SetExpressCheckout'))
            ->getMock();
        $oPayPalServiceObjectMock
            ->expects($this->once())
            ->method('SetExpressCheckout')
            ->will($this->returnValue($oResponse));

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getPayPalServiceObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue($oPayPalServiceObjectMock));

        /**
         * Mock the validator so it will not validate the request
         */
        $oValidatorMock = $this
            ->getMockBuilder('paypInstallmentsSetExpressCheckoutValidator')
            ->setMethods(array('validateRequest'))
            ->getMock();

        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidatorMock));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        $this->_SUT->doRequest();
    }

    /**
     * @dataProvider dataProviderInvalidRequest
     *
     * @param $oInvalidRequestMock
     * @param $sExpectedExceptionClass
     * @param $sExpectedMessage
     *
     * @throws Exception
     */
    public function testDoRequest_throwsExpectedException_onInvalidRequest($oInvalidRequestMock, $sExpectedExceptionClass,
                                                                 $sExpectedMessage)
    {

        $this->setExpectedException($sExpectedExceptionClass);

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getSetExpressCheckoutReqObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getSetExpressCheckoutReqObject')
            ->will($this->returnValue($oInvalidRequestMock));
        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        /**
         * Pass the real validator so it will throw an exception
         */
        $oValidator = new paypInstallmentsSetExpressCheckoutValidator();
        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidator));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        try {
            $this->_SUT->doRequest();
        } catch (Exception $oEx) {
            $this->assertEquals($sExpectedMessage, $oEx->getMessage());
            throw $oEx;
        }
    }

    public function testDoRequest_malformedRequest_throwsException()
    {
        $this->setExpectedException('paypInstallmentsMalformedRequestException');
        $sExpectedMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_ORDER_TOTAL_VALUE');

        $oMalformedRequestMock = $this->getMalformedSetExpressCheckoutReqMock();
        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getSetExpressCheckoutReqObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getSetExpressCheckoutReqObject')
            ->will($this->returnValue($oMalformedRequestMock));
        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        /**
         * Pass the real validator so it will throw an exception
         */
        $oValidator = new paypInstallmentsSetExpressCheckoutValidator();
        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidator));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        try {
            $this->_SUT->doRequest();
        } catch (Exception $oEx) {
            $this->assertEquals($sExpectedMessage, $oEx->getMessage());
            throw $oEx;
        }
    }

    /**
     * @dataProvider dataProviderMalformedResponse
     *
     * @param $oResponse
     */
    public function testDoRequest_throwsExpectedException_onInvalidResponse($oResponse, $sExpectedException)
    {

        $this->setExpectedException($sExpectedException);

        $oPayPalServiceObjectMock = $this
            ->getMockBuilder('\PayPal\Service\PayPalAPIInterfaceServiceService')
            ->setMethods(array('SetExpressCheckout'))
            ->getMock();
        $oPayPalServiceObjectMock
            ->expects($this->once())
            ->method('SetExpressCheckout')
            ->will($this->returnValue($oResponse));

        /**
         * Mock the SDK so that it does not complain about any wrong params
         */
        $oSdkMock = $this
            ->getMockBuilder('paypInstallmentsSdkObjectGenerator')
            ->setMethods(array('getPayPalServiceObject'))
            ->getMock();
        $oSdkMock
            ->expects($this->once())
            ->method('getPayPalServiceObject')
            ->will($this->returnValue($oPayPalServiceObjectMock));

        /**
         * Mock the validator so it will not throw an exception
         */
        $oValidator = new paypInstallmentsSetExpressCheckoutValidator();
        $this->_SUT
            ->expects($this->once())
            ->method('_getValidator')
            ->will($this->returnValue($oValidator));

        /**
         * Pass the real parser to the function
         */
        $oParser = new paypInstallmentsSetExpressCheckoutParser();
        $this->_SUT
            ->expects($this->once())
            ->method('_getParser')
            ->will($this->returnValue($oParser));

        $this->_SUT
            ->expects($this->once())
            ->method('_getObjectGenerator')
            ->will($this->returnValue($oSdkMock));

        $this->_SUT->doRequest();
    }

    public function getSetExpressCheckoutResponseTypeMock($sAck, $sVersion, $sToken)
    {
        $oResponse = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutResponseType');

        if (!is_null($sAck)) {
            $oResponse->Ack = $sAck;
        }
        if (!is_null($sVersion)) {
            $oResponse->Version = $sVersion;
        }
        if (!is_null($sToken)) {
            $oResponse->Token = $sToken;
        }

        return $oResponse;
    }

    public function getSetExpressCheckoutReqMock($sOrderTotalValue, $sOrderTotalcurrencyID, $sAddressCountry,
                                                 $sLandingPage, $sUserSelectedFundingSource)
    {
        $oRequest = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutReq');
        $oRequest->SetExpressCheckoutRequest = new StdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = new stdClass();

        /**
         * Mock Address
         */
        $oAddress = new StdClass();
        $oAddress->Country = $sAddressCountry;

        /**
         * Mock PaymentDetails
         */
        $oPaymentDetail = new stdClass();
        $oPaymentDetail->OrderTotal = new stdClass();
        $oPaymentDetail->OrderTotal->value = $sOrderTotalValue;
        $oPaymentDetail->OrderTotal->currencyID = $sOrderTotalcurrencyID;
        $oPaymentDetail->ShipToAddress = $oAddress;
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->PaymentDetails = array(
            $oPaymentDetail
        );

        /**
         * Mock LandingPage
         */
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->LandingPage = $sLandingPage;

        /**
         * Mock FoundingSourceDetails
         */
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->FundingSourceDetails = new stdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->FundingSourceDetails
            ->UserSelectedFundingSource = $sUserSelectedFundingSource;

        return $oRequest;
    }

    public function getMalformedSetExpressCheckoutReqMock()
    {
        $oRequest = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutReq');
        $oRequest->SetExpressCheckoutRequest = new StdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = new stdClass();

        return $oRequest;
    }

    /**
     * implemented to provide test data until it is implemented in shop logic
     * //TODO: remove that if it is no more needed
     */
    public function getBasketMock()
    {
        /** @var oxBasket $oBasket */
        $oBasket = new oxBasket;

        //products to add
        //add 1x 05848170643ab0deb9914566391c0c63: Trapez ION MADTRIXX (1402)
        $oBasket->addToBasket('05848170643ab0deb9914566391c0c63', 1);

        //add 2x 6b6b6abed58b118ee988c92856b8b675: Kuyichi Jeans CANDY (Variant 0801-85-874-1-4)
        $oBasket->addToBasket('6b6b6abed58b118ee988c92856b8b675', 2);

        //set user to basket
        /** @var oxUser $oUser */
        $oUser = new oxUser;
        $oUser->load('oxdefaultadmin');
        $oBasket->setBasketUser($oUser);

        $oBasket->calculateBasket();

        //setting up delivery address:
        /** @var oxAddress $oShippingAddress */
        $oShippingAddress = new oxAddress;

        if (!$oShippingAddress->load('oxdefaultadmin_delivery_test')) {
            $oShippingAddress->setId('oxdefaultadmin_delivery_test');

            $oShippingAddress->oxaddress__oxcompany = new oxField('OXID eSales AG', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxfname = new oxField('Roland', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxlname = new oxField('Fesenmayr', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxstreet = new oxField('Bertoldstraße', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxstreetnr = new oxField('48', oxField::T_RAW);

            $oShippingAddress->oxaddress__oxcity = new oxField('Freiburg', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxcountryid = new oxField('a7c40f631fc920687.20179984', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxzip = new oxField('79098', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxfon = new oxField('+49 761 36889-0', oxField::T_RAW);
            $oShippingAddress->oxaddress__oxsal = new oxField('MR', oxField::T_RAW);

            $oShippingAddress->save();
        }

        oxRegistry::getSession()->setVariable('deladrid', 'oxdefaultadmin_delivery_test');

        return $oBasket;
    }
}
