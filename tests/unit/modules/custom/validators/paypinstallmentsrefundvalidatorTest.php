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
 * Class paypInstallmentsRefundValidatorTest
 *
 * Test the RefundTransaction validator
 */
class paypInstallmentsRefundValidatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * System under test
     *
     * @var  PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRefundValidator $_SUT
     */
    protected $_SUT;

    /**
     * Set get a mock of class paypInstallmentsRefundValidator
     */
    public function setUp()
    {

        parent::setUp();

        $this->_SUT = $this->getMock(
            'paypInstallmentsRefundValidator',
            array('__construct','getRefundableAmount',)
        );
    }

    /**
     * @dataProvider dataProviderValidRequestParams
     *
     * @param $aParams
     */
    public function testvalidateRequest_doesNotThrowException_forValidRequestParams($aParams)
    {
        $this->_SUT->expects($this->any())->method('getRefundableAmount')->will($this->returnValue(1000.00));
        $this->_SUT->setRequestParams($aParams);
        $this->_SUT->validateRequest();
    }

    public function dataProviderValidRequestParams()
    {
        return array(
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => 100.00,
                    'sMemo'          => 'Some remarks',
                )
            ),
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Full',
                    'sMemo'          => 'Some remarks',
                )
            ),
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Full',
                    'sMemo'          => '漢字編碼方法',
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderInvalidRequestParams
     *
     * @param $aParams
     * @param $sExpectedMessage
     */
    public function testvalidateRequest_throwsException_forInvalidRequestParams($aParams, $sExpectedMessage)
    {
        $this->setExpectedException(
            'paypInstallmentsRefundRequestParameterValidationException',
            $sExpectedMessage
        );

        $this->_SUT->expects($this->any())->method('getRefundableAmount')->will($this->returnValue(1000.00));
        $this->_SUT->setRequestParams($aParams);
        $this->_SUT->validateRequest();
    }

    public function dataProviderInvalidRequestParams()
    {
        return array(
            // Test _validateTransactionId() sTransactionId is not set
            array(
                array(
                    // 'sTransactionId' => '',
                    'sRefundType' => 'Partial',
                    'sCurrency'   => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'       => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_TRANSACTION_ID')
            ),
            // Test _validateTransactionId() sTransactionId is empty
            array(
                array(
                    'sTransactionId' => '',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_TRANSACTION_ID')
            ),
            // Test _validateRefundType() Missing refund type, sRefundType key is not set
            array(
                array(
                    'sTransactionId' => 'TID',
                    // 'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_REFUND_TYPE')
            ),
            // Test _validateRefundType() Missing refund type, sRefundType is empty
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => '',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_REFUND_TYPE')
            ),
            // Test _validateRefundType() Wrong refund sRefundType
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'full',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_REFUND_TYPE')
            ),
            // Test _validateRefundType() Wrong refund sRefundType
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_REFUND_TYPE')
            ),
            // Test _validateRefundType() Wrong refund sRefundType
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Something funny',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_REFUND_TYPE')
            ),
            // Test _validatePartialRefund() dAmount key is missing
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    // 'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_MISSING_IN_PARTIAL_REFUND')
            ),
            // Test _validatePartialRefund() dAmount is  empty
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => '',
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_MISSING_IN_PARTIAL_REFUND')
            ),
            // Test _validatePartialRefund() dAmount is 0
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => 0,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_MISSING_IN_PARTIAL_REFUND')
            ),
            // Test _validatePartialRefund() dAmount is not a float, but a string
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => 'Something funny',
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_NOT_FLOATING_POINT_NUMBER')
            ),
            // Test _validatePartialRefund() dAmount is not negative number
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => -0.01,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('NEGATIVE_REFUND_AMOUNT')
            ),
            // Test _validatePartialRefund() dAmount exceeds maximum refundable amount
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.01,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_GT_REFUNDABLE')
            ),
            // Test _validatePartialRefund() sCurrency key is missing
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    // 'sCurrency'      => '',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_REFUND_CURRENCY')
            ),
            // Test _validatePartialRefund() sCurrency is empty
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => '',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_REFUND_CURRENCY')
            ),
            // Test _validatePartialRefund() sCurrency is wrong
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'Something funny',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_REFUND_CURRENCY')
            ),
            // Test _validateFullRefund() sCurrency is wrong
            array(
                array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Full',
                    'sCurrency'      => 'EUR',
                    'dAmount'     => 1.00,
                    'sMemo'          => 'Some remarks',
                ),
                paypInstallmentsConfiguration::getValidationErrorMessage('REFUND_AMOUNT_PRESENT_IN_FULL_REFUND')
            ),
        );
    }

    public function testValidateResponse_doesNotThrowException_onValidResponse()
    {
        $oResponse = $this->_getResponse();
        /** @var paypInstallmentsRefundParser|PHPUnit_Framework_MockObject_MockObject $oParserMock */
        $oParserMock = $this
            ->getMockBuilder('paypInstallmentsRefundParser')
            ->setMethods(array('__call', '__construct'))
            ->getMock();

        $this->_SUT->setParser($oParserMock);
        $this->_SUT->setRequestParams(
            array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => 100.00,
                    'sMemo'          => 'Some remarks',
                )
        );
        $this->_SUT->setResponse($oResponse);

        $this->_SUT->validateResponse();
    }

    /**
     * @dataProvider dataProviderInvalidResponse
     *
     * @param $oInvalidResponse
     * @param $sExpectedMessage
     */
    public function testValidateResponse_throwsException_onInvalidResponse($oInvalidResponse, $sExpectedMessage) {

        $this->setExpectedException(
            'paypInstallmentsRefundResponseValidationException',
            $sExpectedMessage
        );
        /** @var paypInstallmentsRefundParser|PHPUnit_Framework_MockObject_MockObject $oParserMock */
        $oParserMock = $this
            ->getMockBuilder('paypInstallmentsRefundParser')
            ->setMethods(array('__call', '__construct'))
            ->getMock();

        $this->_SUT->setParser($oParserMock);
        $this->_SUT->setRequestParams(
            array(
                    'sTransactionId' => 'TID',
                    'sRefundType'    => 'Partial',
                    'sCurrency'      => 'EUR',
                    'dAmount'        => 100.00,
                    'sMemo'          => 'Some remarks',
                )
        );
        $this->_SUT->setResponse($oInvalidResponse);

        $this->_SUT->validateResponse();
    }

    public function dataProviderInvalidResponse() {
        return array(
            array(
                $this->_getResponse(''),
                paypInstallmentsConfiguration::getValidationErrorMessage('EMPTY_REPONSE_VALUE_REFUNDTRANSACTIONID')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', ''),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_EMPTY')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 0),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_EMPTY')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', -0.01),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_NEGATIVE')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 'something funny'),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_EMPTY')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 100.00, ''),
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_REFUND_STATUS')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 100.00, 'something funny'),
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_REFUND_STATUS')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 100.00, 'Instant', ''),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_REFUNDEDCURRENCY_EMPTY')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 100.00, 'Instant', 'something funny'),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_WRONG_REFUNDEDCURRENCY')
            ),
            array(
                $this->_getResponse('0PK471234F7771256', 99.00, 'Instant', 'EUR'),
                paypInstallmentsConfiguration::getValidationErrorMessage('REPONSE_TOTALREFUNDEDAMOUNT_SMALLER_THAN_REQUESTED')
            ),
        );
    }

    protected function _getResponse (
        $sRefundTransactionID = '0PK471234F7771256',
        $sTotalRefundedAmount = 388.00,
        $sRefundStatus = 'Instant',
        $sTotalRefundedCurrencyId = 'EUR',
        $sNetRefundAmount = 369.38,
        $sFeeRefundAmount = 18.62,
        $sGrossRefundAmount = 388.00,
        $sPendingReason = 'none',
        $sAck = 'Success',
        $aErrors = array(),
        $sVersion = 124.0
    ) {
        $oResponse = new PayPal\PayPalAPI\RefundTransactionResponseType();
        $oResponse->RefundTransactionID = $sRefundTransactionID;
        $oResponse->NetRefundAmount = $sNetRefundAmount;
        $oResponse->FeeRefundAmount = $sFeeRefundAmount;
        $oResponse->GrossRefundAmount = $sGrossRefundAmount;
        $oResponse->TotalRefundedAmount = new PayPal\CoreComponentTypes\BasicAmountType();
        $oResponse->TotalRefundedAmount->currencyID = $sTotalRefundedCurrencyId;
        $oResponse->TotalRefundedAmount->value = $sTotalRefundedAmount;
        $oResponse->RefundInfo = new PayPal\EBLBaseComponents\RefundInfoType();
        $oResponse->RefundInfo->RefundStatus = $sRefundStatus;
        $oResponse->RefundInfo->PendingReason = $sPendingReason;
        $oResponse->ReceiptData = '';
        $oResponse->MsgSubID = '';
        $oResponse->Timestamp = '2015-09-30T13:41:30';
        $oResponse->Ack = $sAck;
        $oResponse->CorrelationID = 'e589c05fea0c';
        $oResponse->Errors = $aErrors;
        $oResponse->Version = $sVersion;
        $oResponse->Build = 000000;

        return $oResponse;
    }
}
