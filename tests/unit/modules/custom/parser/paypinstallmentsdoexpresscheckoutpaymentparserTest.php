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
 * Class paypInstallmentsDoExpressCheckoutPaymentParserTest
 *
 * @desc
 */
class paypInstallmentsDoExpressCheckoutPaymentParserTest extends OxidTestCase
{

    /**
     * @param $sExpectedMessage
     * @param $oResponse
     *
     * @dataProvider testGetPaymentStatus_exception_dataProvider
     */
    public function testGetPaymentStatus_exception($sExpectedMessage, $oResponse)
    {
        $oException = null;

        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsDoExpressCheckoutPaymentParser')
            ->disableOriginalConstructor()
            ->setMethods(array('validateResponseType', 'getResponse'))
            ->getMock();
        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($oResponse));

        try {
            $oSubjectUnderTest->getPaymentStatus();
        } catch (Exception $oException) {
        }

        $this->assertInstanceOf(
            'paypInstallmentsDoExpressCheckoutParseException',
            $oException,
            'Parse exception caught.'
        );
        $this->assertSame($sExpectedMessage, $oException->getMessage());
    }

    public function testGetPaymentStatus_exception_dataProvider()
    {
        return array(
            array(
                'PAYP_ERR_PARSE_MISSING_PAYMENT_RESPONSE_DETAILS',
                new stdClass(),
            ),
            array(
                'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO',
                (object) array(
                    'DoExpressCheckoutPaymentResponseDetails' => (object) array(
                        'PaymentInfo' => array(),
                    ),
                ),
            ),
            array(
                'PAYP_ERR_PARSE_MISSING_PAYMENT_STATUS',
                (object) array(
                    'DoExpressCheckoutPaymentResponseDetails' => (object) array(
                        'PaymentInfo' => array(
                            (object) array()
                        ),
                    ),
                ),
            ),
        );
    }
}
