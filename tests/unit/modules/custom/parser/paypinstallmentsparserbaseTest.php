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
 * Class paypInstallmentsParserBaseTest
 *
 * @desc test base parser methods.
 */
class paypInstallmentsParserBaseTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException paypInstallmentsRefundRequestParameterValidationException
     * @expectedExceptionMessage INVALID_RESPONSE_TYPE
     */
    public function testIsResponseTypeValid_exception()
    {
        $systemUnderTest = $this->getMockbuilder('paypInstallmentsParserBase')
            ->setMethods(array('getValidResponseType'))
            ->getMockForAbstractClass();

        $systemUnderTest->expects($this->atLeastOnce())
            ->method('getValidResponseType')
            ->will($this->returnValue('testPaInvalidClass'));

        $systemUnderTest->setResponse(new stdClass());
    }
}
