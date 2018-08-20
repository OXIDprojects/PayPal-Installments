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
 * Class paPaypalInstallmentsLoggerTest
 *
 * @desc Unit test for logger.
 */
class paypInstallmentsLoggerTest extends OxidTestCase
{

    /**
     * @param $aExpectedContext
     * @param $sLogLevel
     * @param $aContexts
     *
     * @dataProvider testpaypInstallmentssLogger_dataProvider
     */
    public function testpaypInstallmentssLogger($aExpectedContext, $sLogLevel, $aContexts)
    {
        $sMessage = 'test-pa-message';

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsLogger $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecord'))
            ->getMock();

        $oSubjectUnderTest->expects($this->once())
            ->method('addRecord')
            ->with(paypInstallmentsLogger::INFO, $sMessage, $aExpectedContext);

        $oSubjectUnderTest->setLogLevel($sLogLevel);

        $oSubjectUnderTest->info($sMessage, $aContexts);
    }

    /**
     * @return array array(array($aExpectedContext, $sLogLevel, $aContexts), ...)
     */
    public function testpaypInstallmentssLogger_dataProvider()
    {
        return array(
            array(
                array(),
                'INFO',
                array('test-pa-context'),
                //'level INFO - log empty context'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::INFO,
                array('test-pa-context'),
                //'level not info - log all context'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::ALERT,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::CRITICAL,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::DEBUG,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::EMERGENCY,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::ERROR,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::NOTICE,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                paypInstallmentsLogger::WARNING,
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
            array(
                array('test-pa-context'),
                'test-pa-level',
                array('test-pa-context'),
                //'level not info - log all context.'
            ),
        );
    }
}
