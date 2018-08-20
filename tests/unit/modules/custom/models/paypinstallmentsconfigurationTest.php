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
 * Class paypinstallmentsconfigurationTest
 */
class paypinstallmentsconfigurationTest extends OxidTestCase
{
    /**
     * System Under Test
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsConfiguration $_SUT
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->setMethods(array('__construct'))
            ->getMock();
    }

    public function testIsSandboxMode_returns_trueForSandboxApiEnabled()
    {
        $blExpectedResult = true;
        $this->setConfigParam('paypInstallmentsSandboxApi', $blExpectedResult);

        $blActualResult = $this->_SUT->isSandboxMode();

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testIsSandboxMode_returns_FalseForSandboxApiDisabled()
    {
        $blExpectedResult = false;
        $this->setConfigParam('paypInstallmentsSandboxApi', $blExpectedResult);

        $blActualResult = $this->_SUT->isSandboxMode();

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testIsLoggingEnabled_returns_ExpectedValues()
    {
        $blExpectedResult = false;
        $this->setConfigParam('paypInstallmentsLogging', $blExpectedResult);

        $blActualResult = $this->_SUT->isLoggingEnabled();

        $this->assertEquals($blExpectedResult, $blActualResult);

        $blExpectedResult = true;
        $this->setConfigParam('paypInstallmentsLogging', $blExpectedResult);

        $blActualResult = $this->_SUT->isLoggingEnabled();

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    public function testGetRestLogFile_returnsExpectedValue() {
        $blExpectedResult = '/path/to/file';
        $this->setConfigParam('paypInstallmentsLoggingFile', $blExpectedResult);

        $blActualResult = $this->_SUT->getLogFilePath();

        $this->assertEquals($blExpectedResult, $blActualResult);
    }

    /**
     * @dataProvider dataProviderSandboxSettings
     *
     * @param $blSandboxEnabled
     * @param $sEnvironment
     */
    public function testGetSoapApiConfiguration_withSandboxSettingsSet($blSandboxEnabled, $sEnvironment)
    {
        $sLogDir = $this->getConfig()->getLogsDir();
        $sLogEnabled = true;
        $sLogFile = 'paypal.log';
        $sLogLevel = 'INFO';
        $sApiUsername = 'user';
        $sApiPassword = 'password';
        $sApiSignature = 'secret';
        $this->setConfigParam('paypInstallmentsSandboxApi', $blSandboxEnabled);
        $this->setConfigParam('paypInstallments' . $sEnvironment . 'SoapUsername', $sApiUsername);
        $this->setConfigParam('paypInstallments' . $sEnvironment . 'SoapPassword', $sApiPassword);
        $this->setConfigParam('paypInstallments' . $sEnvironment . 'SoapSignature', $sApiSignature);
        $this->setConfigParam('paypInstallmentsLogging', $sLogEnabled);
        $this->setConfigParam('paypInstallmentsLoggingFileSoap', $sLogFile);
        $this->setConfigParam('paypInstallmentsLoggingLevelSoap', $sLogLevel);
        $sPayPalInstallmentsSoapApiEndpoint = $this->_SUT->getPayPalInstallmentsSoapApiEndpoint();

        $aExpectedResult = array(
            'mode'                         => strtolower($sEnvironment),
            'log.LogEnabled'               => $sLogEnabled,
            'log.FileName'                 => $sLogDir . $sLogFile,
            'log.LogLevel'                 => $sLogLevel,
            'acct1.UserName'               => $sApiUsername,
            'acct1.Password'               => $sApiPassword,
            'acct1.Signature'              => $sApiSignature,
            'service.EndPoint.PayPalAPIAA' => $sPayPalInstallmentsSoapApiEndpoint,
            'service.EndPoint.PayPalAPI'   => $sPayPalInstallmentsSoapApiEndpoint,
        );

        $aActualResult = $this->_SUT->getSoapApiConfiguration();

        $this->assertEquals($aExpectedResult, $aActualResult);
    }

    public function dataProviderSandboxSettings() {

        return array(
            // $blSandboxEnabled, $sEnvironment
            array(true, 'SB'),
            array(false, ''),
        );
    }

    public function testGetPayPalInstallmentsRedirectUrl_appendsTokenToRedirectBaseUrl() {
        $sToken = 'token';

        $sExpectedValue = $this->_SUT->getPayPalInstallmentsRedirectBaseUrl() . $sToken;
        $sActualValue = $this->_SUT->getPayPalInstallmentsRedirectUrl($sToken);

        $this->assertEquals($sExpectedValue, $sActualValue);
    }

    public function testGetRestFinancingOptionsRequestUrl()
    {
        $oMockedConfig = $this->getMock("paypInstallmentsConfiguration", array("getRestEndpointUrl"));
        $oMockedConfig->expects($this->any())
            ->method("getRestEndpointUrl")
            ->will($this->returnValue("Endpoint/"));

        $sRequestURL = $oMockedConfig->getRestFinancingOptionsRequestUrl();
        $this->assertSame($sRequestURL, "Endpoint/v1/credit/calculated-financing-options");
    }

    public function testGetRestEndpointUrl()
    {
        $oMockedConfig = $this->getMock("paypInstallmentsConfiguration", array("isSandboxMode"));
        $oMockedConfig->expects($this->any())
            ->method("isSandboxMode")
            ->will($this->returnValue(true));

        $sEndpointUrl = $oMockedConfig->getRestEndpointUrl();
        $this->assertSame($sEndpointUrl, "https://api.sandbox.paypal.com/");
    }

    public function testGetPayPalRestClientId()
    {
        $oMockedOxConfig = $this->getMock("oxConfig", array("getConfigParam"));
        $oMockedOxConfig->expects($this->any())
            ->method("getConfigParam")
            ->will($this->returnValue("ClientId"));

        $oMockedConfig = $this->getMock("paypInstallmentsConfiguration", array("isSandboxMode", "getConfig"));
        $oMockedConfig->expects($this->any())
            ->method("isSandboxMode")
            ->will($this->returnValue(true));
        $oMockedConfig->expects($this->any())
            ->method("getConfig")
            ->will($this->returnValue($oMockedOxConfig));

        $sClientId = $oMockedConfig->getPayPalRestClientId();
        $this->assertSame("ClientId", $sClientId);
    }

    public function testGetPayPalRestSecret()
    {
        $oMockedOxConfig = $this->getMock("oxConfig", array("getConfigParam"));
        $oMockedOxConfig->expects($this->any())
            ->method("getConfigParam")
            ->will($this->returnValue("Secret"));

        $oMockedConfig = $this->getMock("paypInstallmentsConfiguration", array("isSandboxMode", "getConfig"));
        $oMockedConfig->expects($this->any())
            ->method("isSandboxMode")
            ->will($this->returnValue(true));
        $oMockedConfig->expects($this->any())
            ->method("getConfig")
            ->will($this->returnValue($oMockedOxConfig));

        $sClientId = $oMockedConfig->getPayPalRestSecret();
        $this->assertSame("Secret", $sClientId);
    }

    /**
     * @param $sExpectedUrl
     * @param $blSandbox
     *
     * @dataProvider testGetRestEndpointUrlEnvDataProvider
     */
    public function testGetRestEndpointUrlEnv($sExpectedUrl, $blSandbox)
    {
        /** @var paypInstallmentsConfiguration|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'isSandboxMode'))
            ->getMock();
        $SUT->expects($this->once())
            ->method('isSandboxMode')
            ->will($this->returnValue($blSandbox));

        $this->assertEquals($sExpectedUrl, $SUT->getRestEndpointUrl());
    }

    /**
     * @return array array(array($sExpectedUrl, $blSandbox), ...)
     */
    public function testGetRestEndpointUrlEnvDataProvider()
    {
        return array(
            array('https://api.sandbox.paypal.com/', true),
            array('https://api.paypal.com/', false),
        );
    }

    /**
     * @param $sExpected
     * @param $blSandbox
     *
     * @dataProvider testGetPayPalRestClientIdEnvDataProvider
     */
    public function testGetPayPalRestClientIdEnv($sExpected, $blSandbox)
    {
        $oConfig = $this->getMockBuilder('oxConfig')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfigParam'))
            ->getMock();
        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->with($this->equalTo($sExpected))
            ->will($this->returnArgument(0));

        /** @var paypInstallmentsConfiguration|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'isSandboxMode', 'getConfig'))
            ->getMock();
        $SUT->expects($this->once())
            ->method('isSandboxMode')
            ->will($this->returnValue($blSandbox));
        $SUT->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $this->assertEquals($sExpected, $SUT->getPayPalRestClientId());
    }

    /**
     * @return array array(array($sExpected, $blSandbox), ....)
     */
    public function testGetPayPalRestClientIdEnvDataProvider()
    {
        return array(
            array('paypInstallmentsSBRestClientId', true),
            array('paypInstallmentsRestClientId', false),
        );
    }

    /**
     * @param $sExpected
     * @param $blSandbox
     *
     * @dataProvider testGetPayPalRestRestSecretEnvDataProvider
     */
    public function testGetPayPalRestRestSecretEnv($sExpected, $blSandbox)
    {
        $oConfig = $this->getMockBuilder('oxConfig')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfigParam'))
            ->getMock();
        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->with($this->equalTo($sExpected))
            ->will($this->returnArgument(0));

        /** @var paypInstallmentsConfiguration|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'isSandboxMode', 'getConfig'))
            ->getMock();
        $SUT->expects($this->once())
            ->method('isSandboxMode')
            ->will($this->returnValue($blSandbox));
        $SUT->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $this->assertEquals($sExpected, $SUT->getPayPalRestSecret());
    }

    /**
     * @return array array(array($sExpected, $blSandbox), ....)
     */
    public function testGetPayPalRestRestSecretEnvDataProvider()
    {
        return array(
            array('paypInstallmentsSBRestSecret', true),
            array('paypInstallmentsRestSecret', false),
        );
    }

    /**
     * @dataProvider dataProviderTestGetPayPalInstallmentsRedirectBaseUrl
     *
     * @param $blSandbox
     * @param $sExpectedValue
     * @param $sMessage
     */
    public function testGetPayPalInstallmentsRedirectBaseUrl_returnsExpectedValue($blSandbox, $sExpectedValue, $sMessage) {
        /** @var paypInstallmentsConfiguration|PHPUnit_Framework_MockObject_MockObject $SUT */
        $SUT = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'isSandboxMode', 'getConfig'))
            ->getMock();
        $SUT->expects($this->once())
            ->method('isSandboxMode')
            ->will($this->returnValue($blSandbox));
        $sActualValue = $SUT->getPayPalInstallmentsRedirectBaseUrl();

        $this->assertEquals($sExpectedValue, $sActualValue, $sMessage);
    }

    public function dataProviderTestGetPayPalInstallmentsRedirectBaseUrl() {
        return array(
            // array($blSandbox, $sExpectedValue, $sMessage),
            array(true, 'https://www.sandbox.paypal.com/checkoutnow/2?token=', 'PayPalInstallmentsSandboxRedirectBaseUrl is returned, when Sandbox mode is enabled'),
            array(false, 'https://www.paypal.com/checkoutnow/2?token=', 'PayPalInstallmentsRedirectBaseUrl is returned, when Production mode is enabled'),
        );
    }
}
