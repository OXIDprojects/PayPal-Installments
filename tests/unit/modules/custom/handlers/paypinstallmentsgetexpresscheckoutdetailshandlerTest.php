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

use Psr\Log\LoggerInterface;

class paypInstallmentsGetExpressCheckoutDetailsHandlerTest extends OxidTestCase
{

    /**
     * @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsGetExpressCheckoutDetailsHandler
     */
    protected $SUT;

    /**
     * prepare a Subject under Test
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock(
            'paypInstallmentsGetExpressCheckoutDetailsHandler',
            array('prepareObjectGenerator', 'getDataProvider'),
            array('AuthToken')
        );
    }

    /**
     * make sure no exception is thrown for a successful request
     */
    public function testSuccessfullRequest()
    {
        $oSuccessResponse = $this->getSuccessResponse();
        $this->mockRequest($this->getResponseReturnerService($oSuccessResponse));
        $this->SUT->doRequest();
    }

    /**
     * make sure no exception is thrown for a successful request
     */
    public function testDoRequest_prepareObjectGenerator()
    {
        /**
         * @var $SUT PHPUnit_Framework_MockObject_MockObject|paypInstallmentsGetExpressCheckoutDetailsHandler
         */
        $SUT = $this->getMock(
            'paypInstallmentsGetExpressCheckoutDetailsHandler',
            array(),
            array('AuthToken')
        );

        $oSuccessResponse = $this->getSuccessResponse();
        $this->mockRequest($this->getResponseReturnerService($oSuccessResponse));
        $SUT->doRequest();
    }


    /**
     * make sure a paypinstallmentsnoacksuccessexception is thrown, if paypal does not acknowledge
     * our request
     */
    public function testNoAckRequest()
    {
        $oNoAckResponse = $this->getNoAckResponse();
        $this->mockRequest($this->getResponseReturnerService($oNoAckResponse));
        $this->setExpectedException('paypinstallmentsnoacksuccessexception');
        $this->SUT->doRequest();
    }

    /**
     * make sure an exception is thrown in case we make a request using the wrong PayPal API Version
     */
    public function testWrongVersionResponseRequest()
    {
        $oWrongVersionResponse = $this->getWrongVersionResponse();
        $this->mockRequest($this->getResponseReturnerService($oWrongVersionResponse));
        $this->setExpectedException('paypinstallmentsversionmismatchexception');
        $this->SUT->doRequest();
    }

    /**
     * make sure we covert a PayPal Exception into a module specific exception
     */
    public function testPayPalException()
    {
        $this->mockRequest($this->getExceptionThrowerService());

        $this->setExpectedException('paypInstallmentsException');
        $this->SUT->doRequest();
    }

    /**
     * we want to make sure, the module throws an exception in case paypal does not respond
     * with a payerID
     */
    public function testMissingPayerId()
    {
        $oMissingPayerIdResponse = $this->getMissingPayerIdResponse();
        $this->mockRequest($this->getResponseReturnerService($oMissingPayerIdResponse));
        $this->setExpectedException('paypInstallmentsMalformedResponseException', 'EMPTY_PAYERID');
        $this->SUT->doRequest();
    }

    /**
     * make sure the response parser extracts the response data correctly
     */
    public function testResponseParser()
    {
        $oSuccessResponse = $this->getSuccessResponse();
        $this->mockRequest($this->getResponseReturnerService($oSuccessResponse));
        $aResponseData = $this->SUT->doRequest();

        $this->assertTrue(is_array($aResponseData));
        $this->assertSame('SomeValidPayerID', $aResponseData['PayerId']);
        $this->assertSame(15.0, $aResponseData['FinancingFeeAmountValue']);
        $this->assertSame('EUR', $aResponseData['FinancingFeeAmountCurrency']);
        $this->assertSame(16.0, $aResponseData['FinancingTotalCostValue']);
        $this->assertSame('EUR', $aResponseData['FinancingTotalCostCurrency']);
        $this->assertSame(2.5, $aResponseData['FinancingMonthlyPaymentValue']);
        $this->assertSame('EUR', $aResponseData['FinancingMonthlyPaymentCurrency']);
        $this->assertSame(6.0, $aResponseData['FinancingTerm']);
    }

    /**
     * make sure a ParseException is thrown, if there is no payment data returned from paypal
     */
    public function testMissingPaymentDataResponse()
    {
        $oMissingPaymentDataResponse = $this->getMissingPaymentDataResponse();
        $this->mockRequest($this->getResponseReturnerService($oMissingPaymentDataResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO'
        );
        $this->SUT->doRequest();
    }

    /**
     * test if we get the correct response in case paypal does not return an isFinancing Value
     */
    public function testMissingIsFinancing()
    {
        $oMissingIsFinancingResponse = $this->getPaymentInfoWithoutIsFinancing();
        $this->mockRequest($this->getResponseReturnerService($oMissingIsFinancingResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO_IS_FINANCING'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception if isFinancing has an incorrect value
     */
    public function testIncorrectIsFinancingValue()
    {
        $oIncorrectIsFinancingResponse = $this->getPaymentInfoWithIncorrectIsFinancing();
        $this->mockRequest($this->getResponseReturnerService($oIncorrectIsFinancingResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_PAYMENT_INFO_IS_FINANCING_NOT_BOOL'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception if there is no financing fee value
     */
    public function testMissingFinancingFeeAmount()
    {
        $oMissingFinancingFeeResponse = $this->getMissingFinancingFeeResponse();
        $this->mockRequest($this->getResponseReturnerService($oMissingFinancingFeeResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO_FINANCING_FEE'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception if there is no monthly payment value within the response
     */
    public function testMissingFinancingMonthlyPayment()
    {
        $oMissingFinancingMonthlyPaymentResponse = $this->getMissingFinancingMonthlyPaymentResponse();
        $this->mockRequest($this->getResponseReturnerService($oMissingFinancingMonthlyPaymentResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO_MONTHLY_PAYMENT'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception if there is no financing term value within the response
     */
    public function testMissingFinancingTerm()
    {
        $oMissingFinancingTermResponse = $this->getMissingFinancingTermResponse();
        $this->mockRequest($this->getResponseReturnerService($oMissingFinancingTermResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO_FINANCING_TERM'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception if there is no financing total cost value within the response
     */
    public function testFinancingTotalCost()
    {
        $oMissingFinancingTotalCostResponse = $this->getMissingFinancingTotalCost();
        $this->mockRequest($this->getResponseReturnerService($oMissingFinancingTotalCostResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsParseException',
            'PAYP_ERR_PARSE_MISSING_PAYMENT_INFO_TOTAL_COST'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw the correct exception in case financing fee amount has the wrong type
     */
    public function testIncorrectTypeForFinancingFeeAmount()
    {
        $oIncorrectTypeForFinancingFeeAmountResponse = $this->getIncorrectTypeForFinancingFeeAmount();
        $this->mockRequest($this->getResponseReturnerService($oIncorrectTypeForFinancingFeeAmountResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_FINANCING_FEE_WRONG_TYPE'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure that the correct exception is thrown, when the financing fee amount is below zero
     */
    public function testFinancingFeeAmountValueTooLow()
    {
        $oFinancingFeeAmountValueToLowResponse = $this->getFinancingFeeAmountValueTooLowResponse();
        $this->mockRequest($this->getResponseReturnerService($oFinancingFeeAmountValueToLowResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_NEGATIVE_FINANCING_FEE'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure that the correct exception is thrown, when the financing fee currency is nonexistent
     */
    public function testFinancingFeeAmountNoCurrency()
    {
        $oFinancingFeeAmountNoCurrencyResponse = $this->getFinancingFeeAmountNoCurrencyResponse();
        $this->mockRequest($this->getResponseReturnerService($oFinancingFeeAmountNoCurrencyResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_PAYMENT_FEE_MISSING_CURRENCY'
        );
        $this->SUT->doRequest();
    }

    /**
     * test if we throw the correct exception given an incorrect type for monthly payment
     */
    public function testMonthlyPaymentIncorrectType()
    {
        $oMonthlyPaymentIncorrectTypeResponse = $this->getMonthlyPaymentIncorrectTypeResponse();
        $this->mockRequest($this->getResponseReturnerService($oMonthlyPaymentIncorrectTypeResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_MONTHLY_PAYMENT_WRONG_TYPE'
        );
        $this->SUT->doRequest();
    }

    /**
     * test if we throw the correct exception in case monthly payment is below zero
     */
    public function testMonthlyPaymentBelowZero()
    {
        $oMonthlyPaymentBelowZeroResponse = $this->getMonthlyPaymentBelowZeroResponse();
        $this->mockRequest($this->getResponseReturnerService($oMonthlyPaymentBelowZeroResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_NEGATIVE_MONTHLY_PAYMENT'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure the correct exceptions is thrown when monthly payment has no currency
     */
    public function testMonthlyPaymentWithoutCurrency()
    {
        $oMonthlyPaymentWithoutCurrencyResponse = $this->getMonthlyPaymentWithoutCurrencyResponse();
        $this->mockRequest($this->getResponseReturnerService($oMonthlyPaymentWithoutCurrencyResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_MONTHLY_PAYMENT_MISSING_CURRENCY'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure the correct exception is thrown when total cost has the wrong type
     */
    public function testTotalCostWrongType()
    {
        $oTotalCostWrongTypeResponse = $this->getTotalCostWrongTypeResponse();
        $this->mockRequest($this->getResponseReturnerService($oTotalCostWrongTypeResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_TOTAL_COST_WRONG_TYPE'
        );
        $this->SUT->doRequest();
    }

    /**
     * test if the correct exception is thrown in case the payment total cost has no currency
     */
    public function testTotalCostNoCurrency()
    {
        $oTotalCostNoCurrencyResponse = $this->getTotalCostNoCurrencyResponse();
        $this->mockRequest($this->getResponseReturnerService($oTotalCostNoCurrencyResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_TOTAL_COST_MISSING_CURRENCY'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure we throw an exception in case the total payment price is below the cart price
     */
    public function testTotalCostTooLowResponse()
    {
        $oTotalCostTooLowResponse = $this->getTotalCostTooLowResponse();
        $this->mockRequest($this->getResponseReturnerService($oTotalCostTooLowResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_TOTAL_COST_BELOW_CART_VALUE'
        );
        $this->SUT->doRequest();
    }

    /**
     * test if we throw an exception when financing term is 0 or less
     */
    public function testFinancingTermTooSmall()
    {
        $oFinancingTermTooSmallResponse = $this->getFinancingTermTooSmallResponse();
        $this->mockRequest($this->getResponseReturnerService($oFinancingTermTooSmallResponse));
        $this->setExpectedException(
            'paypInstallmentsGetExpressCheckoutDetailsValidationException',
            'PAYP_ERR_VALIDATION_PAYMENT_INFO_NEGATIVE_FINANCING_TERM'
        );
        $this->SUT->doRequest();
    }

    /**
     * make sure no exception is thrown when calling prepareObjectGenerato
     */
    public function testPrepareObjectGenerator()
    {
        //i need to unmock prepareObjectGenerator
        $this->SUT = $this->getMock(
            'paypInstallmentsGetExpressCheckoutDetailsHandler',
            array('getDataProvider'),
            array('AuthToken')
        );

        $oBasket = $this->getMock('oxBasket', array('__call'));
        $this->SUT->setBasket($oBasket);

        paypInstallmentsGetExpressCheckoutDetailsHandlerTest::callMethod($this->SUT, 'prepareObjectGenerator', array());
        //no exception is enough
    }

    /**
     * prepare a mocked response suggesting a successfull requests
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getSuccessResponse()
    {
        $oMockedResponse = new PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType();
        $oMockedResponse->Version = paypInstallmentsConfiguration::getServiceVersion();
        $oMockedResponse->Ack = paypInstallmentsConfiguration::getResponseAckSuccess();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->Token = "sAuthToken";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID = "SomeValidPayerID";

        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 15.0);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing = "true";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 2.5);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm = 6.0;
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 16.0);

        return $oMockedResponse;
    }

    /**
     * return a response missing an auth token value
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingAuthTokenResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->Token);

        return $oMockedResponse;
    }

    /**
     * remove the IsFinancing property from the successful response
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getPaymentInfoWithoutIsFinancing()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing);

        return $oMockedResponse;
    }

    /**
     * set an incorrect value for isFinancing
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getPaymentInfoWithIncorrectIsFinancing()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing = "Some Random Value I dont care about";

        return $oMockedResponse;
    }

    /**
     * remove FinancingFeeAmount from the successful response
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingFinancingFeeResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount);

        return $oMockedResponse;
    }

    /**
     * remove FinancingMonthlyPayment from the successful response
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingFinancingMonthlyPaymentResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment);

        return $oMockedResponse;
    }

    /**
     * remove FinancingTerm from the successful response
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingFinancingTermResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm);

        return $oMockedResponse;
    }

    /**
     * remove FinancingTotalCost from the successful response
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingFinancingTotalCost()
    {
        $oMockedResponse = $this->getSuccessResponse();
        unset($oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost);

        return $oMockedResponse;
    }

    /**
     * give financing fee an incorrect type
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getIncorrectTypeForFinancingFeeAmount()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = "Butter";

        return $oMockedResponse;
    }

    /**
     * return a payment info object, whose financing fee amount is below zero
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getFinancingFeeAmountValueTooLowResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType("EUR", -15.0);

        return $oMockedResponse;
    }

    /**
     * return a payment info object, without a currencyID
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getFinancingFeeAmountNoCurrencyResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType(null, 15.0);

        return $oMockedResponse;
    }

    /**
     * return a payment info object, whose monthly payment has an incorrect type
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMonthlyPaymentIncorrectTypeResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = "Butter";

        return $oMockedResponse;
    }

    /**
     * return a payment info object, whose monthly payment has an incorrect type
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMonthlyPaymentBelowZeroResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType("EUR", -15.0);

        return $oMockedResponse;
    }

    /**
     * return a payment info object, whose monthly payment has no currency
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMonthlyPaymentWithoutCurrencyResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType(null, 15.0);

        return $oMockedResponse;
    }

    /**
     * return a response, where the total cost is not a BasicAmountType
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getTotalCostWrongTypeResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = "Butter";

        return $oMockedResponse;
    }

    /**
     * return a paymentinfo object, where FinancingTotalCost has no currency
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getTotalCostNoCurrencyResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType(null, 15.0);

        return $oMockedResponse;
    }

    /**
     * return a paymentinfo object, where FinancingTotalCost is below the shopping cart value
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getTotalCostTooLowResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 5.0);

        return $oMockedResponse;
    }

    /**
     * return a paymentinfo object, where financingTerm is zero or less
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getFinancingTermTooSmallResponse()
    {
        $oMockedResponse = $this->getSuccessResponse();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm = -1.0;

        return $oMockedResponse;
    }

    /**
     * return a valid response, where the PaymentInfo Object is missing
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingPaymentDataResponse()
    {
        $oMockedResponse = new PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType();
        $oMockedResponse->Version = paypInstallmentsConfiguration::getServiceVersion();
        $oMockedResponse->Ack = paypInstallmentsConfiguration::getResponseAckSuccess();
        $oMockedResponse->Token = "sAuthToken";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID = "Something";

        return $oMockedResponse;
    }

    /**
     * prepare a mocked response, where PayPal does not acknowledge the request
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getNoAckResponse()
    {
        $oMockedResponse = new PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType();
        $oMockedResponse->Version = paypInstallmentsConfiguration::getServiceVersion();
        $oMockedResponse->Ack = "SomeVeryFalseAckResponse";
        $oMockedResponse->Token = "sAuthToken";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID = "Something";

        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 15.0);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing = "true";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 2.5);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm = 6.0;
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 16.0);

        return $oMockedResponse;
    }

    /**
     * prepare a mocked response using the wrong API Version
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getWrongVersionResponse()
    {
        $oMockedResponse = new PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType();
        $oMockedResponse->Version = "-13.7.0.0.1";
        $oMockedResponse->Ack = paypInstallmentsConfiguration::getResponseAckSuccess();
        $oMockedResponse->Token = "sAuthToken";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID = "Something";

        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 15.0);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing = "true";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 2.5);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm = 6.0;
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 16.0);

        return $oMockedResponse;
    }

    /**
     * prepare a potential paypal response with missing payer Id
     *
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    private function getMissingPayerIdResponse()
    {
        $oMockedResponse = new PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType();
        $oMockedResponse->Version = paypInstallmentsConfiguration::getServiceVersion();
        $oMockedResponse->Ack = paypInstallmentsConfiguration::getResponseAckSuccess();
        $oMockedResponse->Token = "sAuthToken";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID = "";

        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo = new stdClass();
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingFeeAmount = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 15.0);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->IsFinancing = "true";
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingMonthlyPayment = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 2.5);
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTerm = 6.0;
        $oMockedResponse->GetExpressCheckoutDetailsResponseDetails->PaymentInfo->FinancingTotalCost = new PayPal\CoreComponentTypes\BasicAmountType("EUR", 16.0);

        return $oMockedResponse;
    }

    /**
     * Mock the PayPal Service in a way, that it returns the response we want to test
     *
     * @param $oResponse
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponseReturnerService($oResponse)
    {
        $oMockedService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('GetExpressCheckoutDetails')
        );
        $oMockedService->expects($this->any())->method('GetExpressCheckoutDetails')
            ->will($this->returnValue($oResponse));

        return $oMockedService;
    }

    /**
     * Mock the PayPal service so that we can test how we handle paypal exceptions
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getExceptionThrowerService()
    {
        $oMockedService = $this->getMock(
            'PayPal\Service\PayPalAPIInterfaceServiceService',
            array('GetExpressCheckoutDetails')
        );
        $oMockedService->expects($this->any())->method('GetExpressCheckoutDetails')
            ->will($this->throwException(new Exception("Some Kind of error happend")));

        return $oMockedService;
    }

    /**
     * Set up the entire mocked objectGenerator Hierarchy
     *
     * @param $oMockedService - the service we want the getservice call to return
     */
    private function mockRequest($oMockedService)
    {
        $oObjectGenerator = $this->getMock(
            'paypInstallmentsSdkObjectGenerator',
            array(
                '__call',
                'getPayPalServiceObject',
                'getGetExpressCheckoutReqObject'
            )
        );

        $oMockedRequest = $this->getMock(
            '\PayPal\PayPalAPI\GetExpressCheckoutDetailsReq',
            array()
        );

        $oObjectGenerator->expects($this->any())->method('getGetExpressCheckoutReqObject')
            ->will($this->returnValue($oMockedRequest));

        $oObjectGenerator->expects($this->any())->method('getPayPalServiceObject')
            ->will($this->returnValue($oMockedService));

        $oMockDataProvider = $this->getMock('paypInstallmentsCheckoutDataProvider', array('getOrderTotal'));
        $oMockDataProvider->expects($this->any())->method('getOrderTotal')
            ->will($this->returnValue(6.0));

        $this->SUT->expects($this->any())->method('getDataProvider')
            ->will($this->returnValue($oMockDataProvider));

        $this->SUT->expects($this->any())->method('prepareObjectGenerator')
            ->will($this->returnValue($oObjectGenerator));
    }

    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
