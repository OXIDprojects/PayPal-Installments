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
 * Class paypInstallmentsParserTest
 */
class paypInstallmentsParserTest extends PHPUnit_Framework_TestCase
{

    /**
     * System under the test.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsSoapParser $_SUT
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsSetExpressCheckoutParser')
            ->setMethods(array('__construct'))
            ->getMock();
    }


    public function testGetValuesFromResponse_returnsExpectedValues()
    {
        $sExpectedAck = 'Success';
        $sExpectedBuild = '00000';
        $sExpectedCorrelationId = 1234567890;
        $aExpectedErrors = array('error' => true);
        $sExpectedTimestamp = '2015-01-01 ';
        $sExpectedVersion = '124.0';

        $oResponse = $this->_getSetExpressCheckoutResponseTypeMock($sExpectedAck, $sExpectedBuild, $sExpectedCorrelationId, $aExpectedErrors, $sExpectedTimestamp, $sExpectedVersion);

        $this->_SUT->setResponse($oResponse);

        $this->assertEquals($sExpectedAck, $this->_SUT->getAck());
        $this->assertEquals($sExpectedBuild, $this->_SUT->getBuild());
        $this->assertEquals($sExpectedCorrelationId, $this->_SUT->getCorrelationId());
        $this->assertEquals($aExpectedErrors, $this->_SUT->getErrors());
        $this->assertEquals($sExpectedTimestamp, $this->_SUT->getTimestamp());
        $this->assertEquals($sExpectedVersion, $this->_SUT->getVersion());
    }

    public function testGetValuesFromResponse_throwsExpectedException_onMissingResponse()
    {

        $this->setExpectedException('paypInstallmentsMalformedResponseException');

        $this->_SUT->getAck();
    }


    public function testGetValuesFromResponse_throwsExpectedException_onMissingProperties()
    {

        $this->setExpectedException('paypInstallmentsMalformedResponseException');

        $oResponse = $this->_getSetExpressCheckoutResponseTypeMock();
        $this->_SUT->setResponse($oResponse);

        $this->_SUT->getAck();
    }

    protected function _getSetExpressCheckoutResponseTypeMock($sAck = null, $sBuild = null, $sCorrelationId = null, $aErrors = null, $sTimestamp = null, $sVersion = null)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsSoapParser|PayPal\PayPalAPI\SetExpressCheckoutResponseType $oResponse */
        $oResponse = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutResponseType');

        if (is_null($sAck)) {
            unset($oResponse->Ack);
        } else {
            $oResponse->Ack = $sAck;
        }

        if (is_null($sBuild)) {
            unset($oResponse->Build);
        } else {
            $oResponse->Build = $sBuild;
        }

        if (is_null($sCorrelationId)) {
            unset($oResponse->CorrelationID);
        } else {
            $oResponse->CorrelationId = $sCorrelationId;
        }

        if (is_null($aErrors)) {
            unset($oResponse->Errors);
        } else {
            $oResponse->Errors = $aErrors;
        }

        if (is_null($sTimestamp)) {
            unset($oResponse->Timestamp);
        } else {
            $oResponse->Timestamp = $sTimestamp;
        }

        if (is_null($sVersion)) {
            unset($oResponse->Version);
        } else {
            $oResponse->Version = $sVersion;
        }

        return $oResponse;
    }
}
