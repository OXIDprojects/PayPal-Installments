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
 * Class paypInstallmentStartTest
 *
 * @desc Show PayPal presentment on start page.
 */
class paypInstallmentStartTest extends OxidTestCase
{

    public function testGetpaypInstallmentsSnippet()
    {
        $sSnippet = 'test-pa-snippet';

        /** @var oxConfig|PHPUnit_Framework_MockObject_MockObject $oConfig */
        $oConfig = $this->getMockBuilder('oxConfig')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfigParam'))
            ->getMock();
        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->with('paypInstallmentsPresentmentOptionsSnippet')
            ->will($this->returnValue($sSnippet));

        /** @var paypInstallmentStart|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsStart')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfig'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $this->assertSame($sSnippet, $oSubjectUnderTest->getpaypInstallmentsSnippet(), 'Get snippet form settings.');
    }

    public function testGetConfig()
    {
        $oSubjectUnderTest = new paypInstallmentsStart();

        $oConfig1 = $oSubjectUnderTest->getConfig();

        $this->assertInstanceOf('oxConfig', $oConfig1, 'Get object of type oxConfig.');
        $this->assertSame($oConfig1, $oSubjectUnderTest->getConfig(), 'Reuse the same oxConfig object.');
    }
}
