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
 * Class PayPalAPIInterfaceServiceServiceTest
 */
class PayPalAPIInterfaceServiceServiceTest extends OxidTestCase
{

    /**
     * System under the test.
     *
     * @var PayPal\Service\PayPalAPIInterfaceServiceService
     */
    protected $SUT;


    /**
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $sClass = 'PayPalExtended\Service\PayPalAPIInterfaceServiceService';
        $this->SUT = $this->getMock(
            $sClass, // Class to mock
            array('callPayPalAPIAAGetExpressCheckoutDetails'), // Stub function
            array(array('name' => 'value')) // Pass needed parameter to constructor
        );
        $this->SUT
            ->expects($this->once())
            ->method('callPayPalAPIAAGetExpressCheckoutDetails')
            ->will($this->returnValue($this->getTestXML()));
    }

    /**
     * Test that the expected Service version is set.
     */
    public function testGetExpressCheckoutDetailsRequest_isOfExpectedVersion()
    {
        $sExpectedVersion = '124.0';

        $sClass = 'PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType';
        $GetExpressCheckoutDetailsRequest = $this->getMock($sClass);

        $sClass = 'PayPal\PayPalAPI\GetExpressCheckoutDetailsReq';
        $getExpressCheckoutDetailsReq = $this->getMock($sClass);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $GetExpressCheckoutDetailsRequest;

        $apiCredentials = array();
        $this->SUT->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq, $apiCredentials);

        $this->assertEquals(
            $sExpectedVersion,
            $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest->Version
        );
    }

    /**
     * Tests that the object, that was added to the SDK is present in the Response and is an object is of the expected
     * type.
     */
    public function testGetExpressCheckoutDetailsResponsePaymentInfo_isInstanceOfPaymentInfoType()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();
        $this->assertInstanceOf(
            'PayPalExtended\EBLBaseComponents\PaymentInfoType',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
        );
    }

    /**
     * Test that FinancingFeeAmount is present and of the expected type and contains the expected values.
     */
    public function testGetExpressCheckoutDetails_containsExpectedFinancingFeeAmount()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();

        $this->assertIsBasicAmountType(
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingFeeAmount
        );

        $this->assertSame(
            "EUR",
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingFeeAmount
                ->currencyID
        );

        $this->assertSame(
            "38.80",
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingFeeAmount
                ->value
        );
    }

    /**
     * Test that FinancingTotalCost is present and of the expected type and contains the expected values.
     */
    public function testGetExpressCheckoutDetails_containsExpectedFinancingTotalCost()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();

        $this->assertIsBasicAmountType(
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingTotalCost
        );

        $this->assertEquals(
            'EUR',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingTotalCost
                ->currencyID
        );

        $this->assertEquals(
            '426.80',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingTotalCost
                ->value
        );
    }

    /**
     * Test that FinancingTerm is present and contains the expected value.
     */
    public function testGetExpressCheckoutDetails_containsExpectedFinancingTerm()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();

        $this->assertEquals(
            '18',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingTerm
        );
    }

    /**
     * Test that FinancingMonthlyPayment is present and of the expected type and contains the expected values.
     */
    public function testGetExpressCheckoutDetails_containsExpectedFinancingMonthlyPayment()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();

        $this->assertIsBasicAmountType(
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingMonthlyPayment
        );

        $this->assertEquals(
            'EUR',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingMonthlyPayment
                ->currencyID
        );

        $this->assertEquals(
            '23.71',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->FinancingMonthlyPayment
                ->value
        );
    }

    /**
     * Test that IsFinancing is present and contains the expected value.
     */
    public function testGetExpressCheckoutDetails_containsExpectedIsFinancing()
    {
        $oResponse = $this->getExpressCheckoutDetailsResponse();

        $this->assertEquals(
            'true',
            $oResponse
                ->GetExpressCheckoutDetailsResponseDetails
                ->PaymentInfo
                ->IsFinancing
        );
    }

    /**
     * Helper function, which calls GetExpressCheckoutDetails and returns the return value of the call.
     *
     * @return \PayPal\Service\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    protected function getExpressCheckoutDetailsResponse()
    {
        $sClass = 'PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType';
        $GetExpressCheckoutDetailsRequest = $this->getMock($sClass);

        $sClass = 'PayPal\PayPalAPI\GetExpressCheckoutDetailsReq';
        $getExpressCheckoutDetailsReq = $this->getMock($sClass);
        $getExpressCheckoutDetailsReq->GetExpressCheckoutDetailsRequest = $GetExpressCheckoutDetailsRequest;

        $apiCredentials = array();
        $oResponse = $this->SUT->GetExpressCheckoutDetails($getExpressCheckoutDetailsReq, $apiCredentials);

        return $oResponse;
    }

    /**
     * Test that the passed object is an instance of PayPal\CoreComponentTypes\BasicAmountType.
     *
     * @param $oResponseDetail
     */
    protected function assertIsBasicAmountType($oResponseDetail)
    {
        $this->assertInstanceof(
            'PayPal\CoreComponentTypes\BasicAmountType',
            $oResponseDetail
        );
    }

    /**
     * Gets the content of the sample data file and returns it as a string.
     *
     * @return string
     * @throws Exception
     */
    protected function getTestXML()
    {
        $sFileName = 'sampleGetExpressCheckoutDetailsSoapResponse.xml';
        $sXMLContent = file_get_contents(getTestsBasePath() . '/unit/testdata/' . $sFileName);
        if (empty($sXMLContent)) {
            throw new Exception('EMPTY TEST DATA FILE');
        }

        return $sXMLContent;
    }
}