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
 * Class paypInstallmentsGetFinancingOptionsParser
 *
 * @covers paypInstallmentsGetFinancingOptionsParser
 *
 */
class paypInstallmentsGetFinancingOptionsParserTest extends OxidTestCase
{
    public function testGetResponse_returnsJsonEncodedResponse (){
        $oResponse = (object) ['name' => 'test-name', 'message' => 'test-message'];
        $sExpectedResponse = json_encode($oResponse);

        $SUT = new paypInstallmentsGetFinancingOptionsParser();
        $SUT->setResponse($oResponse);

        $sActualResponse = $SUT->getResponse();

        $this->assertEquals($sExpectedResponse, $sActualResponse);
    }

    public function testGetName_returnsExpectedValue (){
        $oResponse = (object) ['name' => 'test-name', 'message' => 'test-message'];
        $sExpectedValue = 'test-name';

        $SUT = new paypInstallmentsGetFinancingOptionsParser();
        $SUT->setResponse($oResponse);

        $sActualValue = $SUT->getName();

        $this->assertEquals($sExpectedValue, $sActualValue);
    }

    public function testGetMessage_returnsExpectedValue (){
        $oResponse = (object) ['name' => 'test-name', 'message' => 'test-message'];
        $sExpectedValue = 'test-message';

        $SUT = new paypInstallmentsGetFinancingOptionsParser();
        $SUT->setResponse($oResponse);

        $sActualValue = $SUT->getMessage();

        $this->assertEquals($sExpectedValue, $sActualValue);
    }
}
