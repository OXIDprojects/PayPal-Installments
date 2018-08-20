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
 * Class paypInstallmentsPresentmentValidatorTest
 *
 * @desc Unit tests for paypInstallmentsPresentmentValidator
 */
class paypInstallmentsPresentmentValidatorTest extends OxidTestCase
{

    public function testGetConfiguration()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentmentValidator $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $this->assertInstanceOf(
            'paypInstallmentsConfiguration',
            $oSubjectUnderTest->getConfiguration()
        );
    }

    public function testSetGetLogger()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentmentValidator $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('__construct'))
            ->getMock();

        $this->assertInstanceOf(
            '\Psr\Log\LoggerInterface',
            $oSubjectUnderTest->getLogger()
        );

        $oLogger = new Psr\Log\NullLogger();
        $oSubjectUnderTest->setLogger($oLogger);
        $this->assertSame($oLogger, $oSubjectUnderTest->getLogger());
    }

    /**
     * @param paypInstallmentsPresentment $oInstallment
     *
     * @dataProvider testValidate_success_dataProvider
     */
    public function testValidate_success(paypInstallmentsPresentment $oInstallment)
    {
        $oLogger = $this->getMockBuilder('Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info'))
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentmentValidator $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->setConstructorArgs(array($oInstallment))
            ->setMethods(array('getLogger'))
            ->getMock();
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));
        $this->assertSame($oSubjectUnderTest, $oSubjectUnderTest->validate());
    }

    /**
     * @return array array(array($oInstallment), ...)
     */
    public function testValidate_success_dataProvider()
    {
        return array(
            array(
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('EUR')
                    ->setCountryCode('DE')
            ),
        );
    }


    /**
     *
     * @param string                         $sExpectedMessage
     * @param paypInstallmentsPresentment $oInstallment
     *
     * @dataProvider testValidate_invalid_dataProvider
     */
    public function testValidate_invalid($sExpectedMessage, paypInstallmentsPresentment $oInstallment)
    {
        $oLogger = $this->getMockBuilder('Psr\Log\NullLogger')
            ->disableOriginalConstructor()
            ->setMethods(array('info'))
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentmentValidator $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->setConstructorArgs(array($oInstallment))
            ->setMethods(array('getLogger'))
            ->getMock();
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getLogger')
            ->will($this->returnValue($oLogger));

        try {
            $oSubjectUnderTest->validate();
        } catch (paypInstallmentsPresentmentValidationException $oEx) {
            $this->assertEquals($sExpectedMessage, $oEx->getMessage());
        }
    }


    /**
     * @return array array(array($oInstallment), ...)
     */
    public function testValidate_invalid_dataProvider()
    {
        return array(
            array(
                'PAYP_ERR_VALIDATION_INVALID_AMOUNT',
                oxNew('paypInstallmentsPresentment')->setAmount(0)
                    ->setCurrency('EUR')
                    ->setCountryCode('DE')
            ),
            array(
                'PAYP_ERR_VALIDATION_INVALID_AMOUNT',
                oxNew('paypInstallmentsPresentment')->setAmount(-10)
                    ->setCurrency('EUR')
                    ->setCountryCode('DE')
            ),
            array(
                'PAYP_ERR_VALIDATION_UNSUPPORTED_CURRENCY',
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('eur')
                    ->setCountryCode('DE')
            ),
            array(
                'PAYP_ERR_VALIDATION_UNSUPPORTED_CURRENCY',
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('CHF')
                    ->setCountryCode('DE')
            ),
            array(
                'PAYP_ERR_VALIDATION_UNSUPPORTED_COUNTRY',
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('EUR')
                    ->setCountryCode('DEU')
            ),
            array(
                'PAYP_ERR_VALIDATION_UNSUPPORTED_COUNTRY',
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('EUR')
                    ->setCountryCode('de')
            ),
            array(
                'PAYP_ERR_VALIDATION_UNSUPPORTED_COUNTRY',
                oxNew('paypInstallmentsPresentment')->setAmount(10.00)
                    ->setCurrency('EUR')
                    ->setCountryCode('')
            ),
        );
    }
}
