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
 * Class paypInstallmentsSetExpressCheckoutParserTest
 */
class paypInstallmentsSetExpressCheckoutParserTest extends OxidTestCase
{

    /**
     * System under the test.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsSetExpressCheckoutParser $_SUT
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsSetExpressCheckoutParser')
            ->setMethods(array('__construct'))
            ->getMock();
    }

    /**
     * Test special error condition in function _getValueByClassAndProperty.
     * Requested object is missing in the response.
     */
    public function testGetLandingPage_throwsException_forNotExistentClass()
    {
        $this->setExpectedException('paypinstallmentsmalformedrequestexception');

        $oRequest = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutReq');

        $this->_SUT->setRequest($oRequest);

        $this->_SUT->getLandingPage();
    }

    /**
     * Test special error condition in function _getValueByClassAndProperty.
     * Requested object present, but requested property is missing in the response.
     */
    public function testGetLandingPage_throwsException_forNotExistentProperty()
    {
        $this->setExpectedException('paypinstallmentsmalformedrequestexception');

        $oRequest = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutReq');
        $oRequest->SetExpressCheckoutRequest = new StdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = new stdClass();

        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->WRONG_PROPERTY_LandingPage = '';

        $this->_SUT->setRequest($oRequest);

        $this->_SUT->getLandingPage();
    }

    public function testGetValuesFromRequest()
    {
        $sExpectedOrderTotalValue = 100.00;
        $sExpectedOrderTotalcurrencyID = 'EUR';
        $sExpectedAddressCountry = 'DE';
        $sExpectedLandingPage = 'Billing';
        $sExpectedUserSelectedFundingSource = 'Finance';

        $oRequest = $this->_getSetExpressCheckoutRequestMock(
            $sExpectedOrderTotalValue,
            $sExpectedOrderTotalcurrencyID,
            $sExpectedAddressCountry,
            $sExpectedLandingPage,
            $sExpectedUserSelectedFundingSource
        );

        $this->_SUT->setRequest($oRequest);

        $this->assertEquals($sExpectedOrderTotalValue, $this->_SUT->getOrderTotalValue());
        $this->assertEquals($sExpectedOrderTotalcurrencyID, $this->_SUT->getOrderTotalCurrency());
        $this->assertEquals($sExpectedAddressCountry, $this->_SUT->getShippingCountry());
        $this->assertEquals($sExpectedLandingPage, $this->_SUT->getLandingPage());
        $this->assertEquals($sExpectedUserSelectedFundingSource, $this->_SUT->getFundingSource());
    }


    public function testGetToken_returnsToken() {
        $sExpectedToken = 'Token';

        $oResponse = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutResponseType');
        $oResponse->Token = $sExpectedToken;

        $this->_SUT->setResponse($oResponse);

        $sActualToken = $this->_SUT->getToken();

        $this->assertEquals($sExpectedToken, $sActualToken);
    }

    protected function _getSetExpressCheckoutRequestMock($sOrderTotalValue, $sOrderTotalcurrencyID, $sAddressCountry,
                                                 $sLandingPage, $sUserSelectedFundingSource)
    {
        $oRequest = $this->getMock('PayPal\PayPalAPI\SetExpressCheckoutReq');
        $oRequest->SetExpressCheckoutRequest = new StdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails = new stdClass();

        /**
         * Mock Address
         */
        $oAddress = new StdClass();
        $oAddress->Country = $sAddressCountry;

        /**
         * Mock PaymentDetails
         */
        $oPaymentDetail = new stdClass();
        $oPaymentDetail->OrderTotal = new stdClass();
        $oPaymentDetail->OrderTotal->value = $sOrderTotalValue;
        $oPaymentDetail->OrderTotal->currencyID = $sOrderTotalcurrencyID;
        $oPaymentDetail->ShipToAddress = $oAddress;
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->PaymentDetails = array(
            $oPaymentDetail
        );

        /**
         * Mock LandingPage
         */
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->LandingPage = $sLandingPage;

        /**
         * Mock FoundingSourceDetails
         */
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->FundingSourceDetails = new stdClass();
        $oRequest->SetExpressCheckoutRequest->SetExpressCheckoutRequestDetails->FundingSourceDetails
            ->UserSelectedFundingSource = $sUserSelectedFundingSource;

        return $oRequest;
    }
}
