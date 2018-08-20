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
class paypInstallmentsGetFinancingOptionsHandlerTest extends OxidTestCase
{

    /**
     *@var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $SUT
     */
    protected $SUT;

    /**
     * make sure no exception is thrown, if we successfully authenticate with paypal
     */
    public function testSuccessfulAuthentication()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->access_token = "not empty";
        $oMockedResponse->token_type = "Bearer";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 200));

        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * make sure the correct exception is thrown, when we try to authenticate ourselfs with an incorrect secret
     */
    public function testWrongPasswordAuthenticationRequest()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->error_description = "Client secret does not match for this client";
        $oMockedResponse->access_token = "not empty";
        $oMockedResponse->token_type = "Bearer";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 401));

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_INCORRECT_CLIENT_SECRET');

        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * test if we throw the right exception when PayPal complains about an unknown client id
     */
    public function testWrongClientIdAuthenticationRequest()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->error_description = "The client credentials are invalid";
        $oMockedResponse->access_token = "not empty";
        $oMockedResponse->token_type = "Bearer";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 401));

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_INCORRECT_CLIENT_ID');

        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * is the right exception thrown, if PayPal returns an empty access token
     */
    public function testMissingAccessToken()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->access_token = "";
        $oMockedResponse->token_type = "Bearer";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 501));

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_MISSING_ACCESS_TOKEN');
        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * test if the correct exception is thrown when a token type other than Bearer is returned to our PP Authentication call
     */
    public function testNonBearerTokenType()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->access_token = "not empty";
        $oMockedResponse->token_type = "Definitely not Bearer";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 501));

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_NON_BEARER_TOKEN');
        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * Make sure the right exception is thrown, in case PayPal returns no tokenType to our authentication call
     */
    public function testMissingTokenType()
    {
        $oMockedResponse = new stdClass();
        $oMockedResponse->access_token = "not empty";
        $oMockedResponse->token_type = "";

        $this->prepareAuthenticationSUT();
        $this->mockCurlRequest($oMockedResponse, array("http_code" => 501));

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_MISSING_TOKEN_TYPE');

        $this->SUT->authenticate(new paypInstallmentsConfiguration());
    }

    /**
     * Make sure financing options are retrieved correctly and that they are sorted by term in ascending order
     */
    public function testFinancingOptionsSorting()
    {
        $oMockedResponse = $this->prepareMockFinancingOptionsResponse();

        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oHandler */
        $oHandler = $this->prepareFinancingOptionsMock($oMockedResponse, array("http_code" => 200));
        $aFinancingOptions = $oHandler->doRequest();

        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', null);

        $this->assertEquals(sizeof($aFinancingOptions), 4);
        $this->assertEquals($aFinancingOptions[0]->getNumMonthlyPayments(), 6);
        $this->assertEquals($aFinancingOptions[1]->getNumMonthlyPayments(), 12);
        $this->assertEquals($aFinancingOptions[2]->getNumMonthlyPayments(), 18);
    }

    /**
     * make sure the right exception is thrown, if a negative value is passed to PayPal for financing
     */
    public function testNegativeAmount()
    {
        $oHandler = new paypInstallmentsGetFinancingOptionsHandler(-15.0, "EUR", "DE");

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_NEGATIVE_AMOUNT');

        $oHandler->doRequest();
    }

    /**
     * test whether the correctr exception is thrown in case we pass a bad currency to PayPal
     */
    public function testIncorrectCurrency()
    {
        $oHandler = new paypInstallmentsGetFinancingOptionsHandler(15.0, "USD", "DE");

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_WRONG_CURRENCY');

        $oHandler->doRequest();
    }

    /**
     * Make sure the correct exception is thrown, if we pass an incorrect payment location to paypal
     */
    public function testWrongPaymentLocation()
    {
        $oHandler = new paypInstallmentsGetFinancingOptionsHandler(15.0, "EUR", "UK");

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_ORDER_NOT_FROM_GERMANY');

        $oHandler->doRequest();
    }

    /**
     * Make sure the correct exception is thrown when PayPal returns an inputValidationError
     */
    public function testPayPalValidationError()
    {

        $oMockedResponse = (object) array(
            "name"    => "inputValidationError",
            "message" => "Errormessage"
        );

        $aCurlInformation = array("http_code" => 401);

        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oHandler */
        $oHandler = $this->prepareFinancingOptionsMock($oMockedResponse, $aCurlInformation);

        $this->setExpectedException('paypInstallmentsFinancingOptionsException', 'PAYP_ERR_VALIDATION_PAYPAL_VALIDATION_ERROR Errormessage');

        $oHandler->doRequest();

        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', null);
    }

    /**
     * make sure the correct exceptions are thrown in case of a PayPal error
     */
    public function testPayPalRequestError()
    {

        $oMockedResponse = (object) array(
            "name" => "Some other name"
        );

        $aCurlInformation = array("http_code" => 401);

        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oHandler */
        $oHandler = $this->prepareFinancingOptionsMock($oMockedResponse, $aCurlInformation);

        $sMockedResponseJson = json_encode($oMockedResponse);
        $this->setExpectedException('paypInstallmentsFinancingOptionsException', "PAYP_ERR_VALIDATION_PAYPAL_REQUEST_ERROR $sMockedResponseJson");

        $oHandler->doRequest();

        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', null);
    }

    public function testDoRequestFlow()
    {
        $mResponse = 'test-pa-response';
        $mRequestInfo = 'test-pa-request-info';
        $mQualifiedOptions = array('test-pa-qualified-options');
        $mUnQualifiedOptions = array('test-pa-un-qualified-options');

        $oLogger = $this->getMockBuilder('Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info'))
            ->getMock();
        $oParser = $this->getMockBuilder('paypInstallmentsGetFinancingOptionsParser')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setResponse',
                    'setRequestInformation',
                    'extractFinancingOptions',
                    'extractFinancingOptionsNotQualified',
                    'validateResponseType'
                )
            )
            ->getMock();
        $oParser->expects($this->once())
            ->method('setResponse')
            ->with($mResponse);
        $oParser->expects($this->once())
            ->method('setRequestInformation')
            ->with($mRequestInfo);
        $oParser->expects($this->once())
            ->method('extractFinancingOptions')
            ->will($this->returnValue($mQualifiedOptions));
        $oParser->expects($this->once())
            ->method('extractFinancingOptionsNotQualified')
            ->will($this->returnValue($mUnQualifiedOptions));
        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsParser', $oParser);

        $oValidator = $this->getMockBuilder('paypInstallmentsGetFinancingOptionsValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('setLogger', 'validateFinancingOptionsArguments', 'validateFinancingOptionsResponse'))
            ->getMock();
        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', $oValidator);

        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsGetFinancingOptionsHandler')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'authenticate',
                    'getLogger',
                    '_performCurlRequest',
                    '_getCurlRequestInformation',
                    'setQualifiedOptions',
                    'setUnQualifiedOptions'
                )
            )
            ->getMock();
        $oSubjectUnderTest->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $oSubjectUnderTest->expects($this->once())
            ->method('_performCurlRequest')
            ->will($this->returnValue($mResponse));
        $oSubjectUnderTest->expects($this->once())
            ->method('_getCurlRequestInformation')
            ->will($this->returnValue($mRequestInfo));
        $oSubjectUnderTest->expects($this->once())
            ->method('_getCurlRequestInformation')
            ->will($this->returnValue($mQualifiedOptions));
        $oSubjectUnderTest->expects($this->once())
            ->method('_getCurlRequestInformation')
            ->will($this->returnValue($mUnQualifiedOptions));

        $oSubjectUnderTest->doRequest();

        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsParser', null);
        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', null);
    }

    /**
     * @param mixed  $mExpected
     * @param string $sSetter
     * @param string $sGetter
     * @param string $mParam
     *
     * @dataProvider testSetGetOptionsDataProvider
     */
    public function testSetGetOptions($mExpected, $sSetter, $sGetter, $mParam)
    {
        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsGetFinancingOptionsHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $this->assertSame($mExpected, $oSubjectUnderTest->$sSetter($mParam)->$sGetter());
    }

    /**
     * @return array array(array($mExpected, $sSetter, $sGetter, $mParam), ...)
     */
    public function testSetGetOptionsDataProvider()
    {
        return array(
            array(array('test-pa-option'), 'setQualifiedOptions', 'getQualifiedOptions', array('test-pa-option')),
            array(array('test-pa-option-unq'), 'setUnQualifiedOptions', 'getUnQualifiedOptions', array('test-pa-option-unq')),
        );
    }

    /**
     * make sure getConfig throws no exceptions and returns a configuration object
     */
    public function testGetConfig()
    {
        $oHandler = new paypInstallmentsGetFinancingOptionsHandler(200.00, "EUR", "DE");
        $oConfig = paypInstallmentsGetFinancingOptionsHandlerTest::callMethod($oHandler, "_getConfig", array());
        $this->assertInstanceOf('paypInstallmentsConfiguration', $oConfig);
    }

    /**
     * Test admin state setter/getter.
     */
    public function testSetGetAdmin()
    {
        $oHandler = new paypInstallmentsGetFinancingOptionsHandler(15.0, "USD", "DE");

        $this->assertEmpty($oHandler->isBlIsAdmin(), 'Initial state is not admin.');

        $oHandler->setBlIsAdmin(true);
        $this->assertTrue($oHandler->isBlIsAdmin(), 'Set admin state.');

        $oHandler->setBlIsAdmin();
        $this->assertNull($oHandler->isBlIsAdmin(), 'UsSet admin state.');

        $oHandler->setBlIsAdmin(false);
        $this->assertFalse($oHandler->isBlIsAdmin(), 'Disable admin state.');
    }

    /**
     * mock out the curl related methods to isolate our tests against change
     *
     * @param $oMockedResponse
     * @param $aRequestInformation
     */
    private function mockCurlRequest($oMockedResponse, $aRequestInformation)
    {
        $this->SUT->expects($this->any())
            ->method('_performCurlRequest')
            ->will($this->returnValue($oMockedResponse));

        $this->SUT->expects($this->any())
            ->method('_getCurlRequestInformation')
            ->will($this->returnValue($aRequestInformation));
    }

    /**
     * Mock a FinancingOptionsHelper to test the authentication method
     */
    private function prepareAuthenticationSUT()
    {
        /** @var paypInstallmentsGetFinancingOptionsHandler|PHPUnit_Framework_MockObject_MockObject $oHandler */
        $this->SUT = $this->getMock(
            'paypInstallmentsGetFinancingOptionsHandler',
            array('_performCurlRequest', '_getCurlRequestInformation'),
            array(200.00, "EUR", "DE")
        );
    }

    /**
     * return examplary json data to test our handling of paypals data
     *
     * @return stdClass
     */
    protected function prepareMockFinancingOptionsResponse()
    {
        $oMockedResponse = new stdClass();

        $oMockedResponse->financing_options = array(
            0 => (object) array(
                "qualifying_financing_options" => array(
                    0 => (object) array(
                        "credit_financing" => (object) array(
                            "term" => 12
                        ),
                        "monthly_payment"  => (object) array(
                            "value" => 18.00
                        ),
                        "total_interest"   => (object) array(
                            "value" => 16.00
                        ),
                        "total_cost"       => (object) array(
                            "value" => 216.00
                        )
                    ),
                    1 => (object) array(
                        "credit_financing" => (object) array(
                            "term" => 6
                        ),
                        "monthly_payment"  => (object) array(
                            "value" => 35.00
                        ),
                        "total_interest"   => (object) array(
                            "value" => 10.00
                        ),
                        "total_cost"       => (object) array(
                            "value" => 210.00
                        )
                    ),
                    2 => (object) array(
                        "credit_financing" => (object) array(
                            "term" => 18
                        ),
                        "monthly_payment"  => (object) array(
                            "value" => 13.00
                        ),
                        "total_interest"   => (object) array(
                            "value" => 34.00
                        ),
                        "total_cost"       => (object) array(
                            "value" => 234.00
                        )
                    ),
                    3 => (object) array(
                        "credit_financing" => (object) array(
                            "term" => 18
                        ),
                        "monthly_payment"  => (object) array(
                            "value" => 12.00
                        ),
                        "total_interest"   => (object) array(
                            "value" => 16.00
                        ),
                        "total_cost"       => (object) array(
                            "value" => 216.00
                        )
                    ),
                )
            )
        );

        return $oMockedResponse;
    }

    /**
     * Set up all mocks we need to test the financing options call
     *
     * @param $oMockedResponse
     * @param $aCurlInformation
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareFinancingOptionsMock($oMockedResponse, $aCurlInformation)
    {
        $oHandler = $this->getMock(
            'paypInstallmentsGetFinancingOptionsHandler',
            array('_getConfig', 'authenticate', '_performCurlRequest', '_getCurlRequestInformation', 'getParser'),
            array(200.00, "EUR", "DE")
        );

        $oHandler->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue('AuthToken'));

        $oHandler->expects($this->once())
            ->method('_performCurlRequest')
            ->will($this->returnValue($oMockedResponse));

        $oHandler->expects($this->once())
            ->method('_getCurlRequestInformation')
            ->will($this->returnValue($aCurlInformation));

        $oMockConfig = $this->getMock(
            'paypInstallmentsConfiguration',
            array(
                'getRestFinancingOptionsRequestUrl'
            )
        );

        $oMockConfig->expects($this->any())
            ->method('getRestFinancingOptionsRequestUrl')
            ->will($this->returnValue('https://api.sandbox.paypal.com/v1/credit/calculated-financing-options'));

        $oHandler->expects($this->any())
            ->method('_getConfig')
            ->will($this->returnValue($oMockConfig));

        $oMockParser = $this->getMock(
            'paypInstallmentsGetFinancingOptionsParser',
            array(
                'getResponse',
                'getHttpCode',
                'getName',
                'getMessage'
            )
        );

        $oMockParser
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(json_encode($oMockedResponse)));
        $oMockParser
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($oMockedResponse->name));
        $oMockParser
            ->expects($this->any())
            ->method('getHttpCode')
            ->will($this->returnValue($aCurlInformation['http_code']));
        $oMockParser
            ->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($oMockedResponse->message));

        $oMockValidator = $this->getMockBuilder('paypInstallmentsGetFinancingOptionsValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('setLogger', 'validateFinancingOptionsArguments', 'getParser'))
            ->getMock();
        $oMockValidator->expects($this->any())->method('getParser')->will($this->returnValue($oMockParser));

        oxUtilsObject::setClassInstance('paypInstallmentsGetFinancingOptionsValidator', $oMockValidator);

        return $oHandler;
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
}
