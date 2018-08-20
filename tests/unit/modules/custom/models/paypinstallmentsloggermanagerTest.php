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
 * Class paypInstallmentsLoggerManagerTest
 *
 * @desc Unit test for paypInstallmentsLoggerManager.
 */
class paypInstallmentsLoggerManagerTest extends OxidTestCase
{

    /**
     * @param $sExpectedType
     * @param $blIsLoggingEnabled
     * @param $sConditionDescription
     *
     * @dataProvider testGetLoggerDataProvider
     */
    public function testGetLogger($sExpectedType, $blIsLoggingEnabled, $sConditionDescription)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsConfiguration $oConfig */
        $oConfig = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('isLoggingEnabled'))
            ->getMock();
        $oConfig->expects($this->once())
            ->method('isLoggingEnabled')
            ->will($this->returnValue($blIsLoggingEnabled));

        $oSystemUnderTest = new paypInstallmentsLoggerManager($oConfig);

        $this->assertInstanceOf(
            $sExpectedType,
            $oSystemUnderTest->getLogger(),
            $sConditionDescription
        );
    }

    /**
     * @return array array(array($sExpectedType, $blIsLoggingEnabled, $sConditionDescription), ...)
     */
    public function testGetLoggerDataProvider()
    {
        return array(
            array(
                'paypInstallmentsLogger',
                true,
                'Logging enabled - return logger.'
            ),
            array(
                'Psr\Log\NullLogger',
                false,
                'Logging disabled - return Null logger.'
            ),
        );
    }

    /**
     * @param int    $iExpectedLevel    Psr log level
     * @param string $sReadableLogLevel PayPalInstallment log level
     *
     * @dataProvider testGetLogLevelDataProvider
     */
    public function testGetLogLevel($iExpectedLevel, $sReadableLogLevel)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsConfiguration $oConfig */
        $oConfig = $this->getMockBuilder('paypInstallmentsConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('isLoggingEnabled', 'getLogLevel'))
            ->getMock();
        $oConfig->expects($this->once())
            ->method('isLoggingEnabled')
            ->will($this->returnValue(true));
        $oConfig->expects($this->atLeastOnce())
            ->method('getLogLevel')
            ->will($this->returnValue($sReadableLogLevel));

        $oSystemUnderTest = new paypInstallmentsLoggerManager($oConfig);

        /** @var \Monolog\Handler\StreamHandler $oHandler */
        $oHandler = $oSystemUnderTest->getLogger()->popHandler();
        $this->assertInstanceOf('Monolog\Handler\StreamHandler', $oHandler);
        $this->assertSame($iExpectedLevel, $oHandler->getLevel());
    }

    /**
     * @return array array(array($iExpectedLevel, $sReadableLogLevel), ...)
     */
    public function testGetLogLevelDataProvider()
    {
        return array(
            array(Monolog\Logger::DEBUG, 'DEBUG'),
            array(Monolog\Logger::INFO, 'INFO'),
            array(Monolog\Logger::WARNING, 'WARN'),
            array(Monolog\Logger::ERROR, 'ERROR'),
            array(Monolog\Logger::DEBUG, 'test-pa-any-level'),
        );
    }
}
