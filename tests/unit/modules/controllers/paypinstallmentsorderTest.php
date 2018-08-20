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
 * Class paypInstallmentsOrderTest
 */
class paypInstallmentsOrderTest extends OxidTestCase
{

    /**
     * System under test
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOrder $_SUT
     */
    protected $_SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->_SUT = $this
            ->getMockBuilder('paypinstallmentsorder')
            ->setMethods(
                array(
                    '__construct',
                    '__call',
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_throwInvalidTokenException',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getGetExpressCheckoutDetailsHandler',
                    '_paypInstallments_handlePayPalInstallmentsDoRequestException'
                )
            )
            ->getMock();
    }

    public function testRender_callsRenderParent()
    {
        $sExpectedResult = 'someTemplateName';

        $this->_SUT
            ->expects($this->once())
            ->method('_paypInstallments_callRenderParent')
            ->will($this->returnValue($sExpectedResult));

        $sActualResult = $this->_SUT->render();

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testRender_returnsParentResult_onSuccessParameterBeingPartofRequest()
    {
        $sExpectedResult = 'someTemplateName';

        /** Arrange */
        $this->_letSuccessParameterBePartOfRequest();

        /** Let the exception to be thrown to see if it will be catched */
        $this->_letTheInvalidTokenExceptionBeExpectedAndThrown();

        /** Let the parent renderer return an expected string */
        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult);

        /** Act */
        $sActualResult = $this->_SUT->render();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    /**
     * @dataProvider dataProviderInvalidToken
     *
     * @param $sTokenInSession
     * @param $sTokenInRequest
     */
    public function testRender_throwsInvalidTokenException_onInvalidToken($sTokenInSession, $sTokenInRequest)
    {
        $sExpectedResult = 'someTemplateName';

        /** Arrange */
        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession);
        $this->_letTheInvalidTokenExceptionBeExpectedAndThrown();
        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult);

        /** Act */
        $sActualResult = $this->_SUT->render();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    /**
     * @dataProvider dataProviderInvalidToken
     *
     * @param $sTokenInSession
     * @param $sTokenInRequest
     */
    public function testRender_throwsInvalidTokenException_onInvalidTokenCodeCoverage($sTokenInSession, $sTokenInRequest)
    {
        $handler = $this->getMockbuilder('paypInstallmentsGetExpressCheckoutDetailsHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('setBasket', 'doRequest'))
            ->getMock();
        $handler->expects($this->any())->method('doRequest');

        $basket = $this->getMockbuilder('oxBasket')
            ->disableOriginalConstructor()
            ->setMethods(array('calculateBasket'))
            ->getMock();

        $session = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsSetPayPalInstallmentsRegistryValueByKey', 'getBasket', 'setBasket'))
            ->getMock();
        $session->expects($this->any())
            ->method('paypInstallmentsSetPayPalInstallmentsRegistryValueByKey');
        $session->expects($this->any())
            ->method('setBasket');
        $session->expects($this->atLeastOnce())
            ->method('getBasket')
            ->will($this->returnValue($basket));

        $subjectUnderTest = $this
            ->getMockBuilder('paypInstallmentsOrder')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    '__call',
                    'getSession',
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_throwInvalidTokenException',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getGetExpressCheckoutDetailsHandler',
                    '_paypInstallments_handlePayPalInstallmentsDoRequestException',
                )
            )
            ->getMock();

        /** Arrange */
        $subjectUnderTest->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($session));

        $subjectUnderTest
            ->expects($this->once())
            ->method('_paypInstallments_throwInvalidTokenException');

        $subjectUnderTest->expects($this->once())
            ->method('_paypInstallments_getGetExpressCheckoutDetailsHandler')
            ->will($this->returnValue($handler));


        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession, $subjectUnderTest);

        /** Act */
        $subjectUnderTest->render();
    }

    public function testRender_willCallExceptionHandler_onDoRequestThrowsException()
    {
        $oException = new InvalidArgumentException();

        $sExpectedResult = 'someTemplateName';
        $sTokenInSession = $sTokenInRequest = 'TheSameToken';

        /** Arrange */
        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession);
        $this->_letTheInvalidTokenExceptionNotBeExpected();

        $oHandler = $this->_getGetExpressCheckoutDetailsHandler();
        $this->_letGetExpressCheckoutHandlerThrowGivenException($oHandler, $oException);

        $this->_letGetGetExpressCheckoutHandlerReturnAGivenHandler($oHandler);
        $this->_SUT->expects($this->once())->method('_paypInstallments_handlePayPalInstallmentsDoRequestException');
        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult);

        /** Act */
        $sActualResult = $this->_SUT->render();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    /**
     * @dataProvider dataProviderDoRequestExceptions
     *
     * @param $oException
     */
    public function testRender_exceptionHandlerWillHandle_onDoRequestThrowsException($oException)
    {
        $sExpectedResult = 'someTemplateName';
        $sTokenInSession = $sTokenInRequest = 'TheSameToken';
        /**  @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOrder $SUT */
        $SUT = $this
            ->getMockBuilder('paypinstallmentsorder')
            ->setMethods(
                array(
                    '__construct',
                    '__call',
                    '_paypInstallments_callRenderParent',
                    '_paypInstallments_throwInvalidTokenException',
                    '_paypInstallments_getPayPalTokenFromSession',
                    '_paypInstallments_getGetExpressCheckoutDetailsHandler',
                )
            )
            ->getMock();

        /** Arrange */
        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession, $SUT);
        $this->_letTheInvalidTokenExceptionNotBeExpected($SUT);

        $oHandler = $this->_getGetExpressCheckoutDetailsHandler();
        $this->_letGetExpressCheckoutHandlerThrowGivenException($oHandler, $oException);

        $this->_letGetGetExpressCheckoutHandlerReturnAGivenHandler($oHandler, $SUT);
        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult, $SUT);

        /** Act */
        $sActualResult = $SUT->render();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    /**
     * @dataProvider dataProviderInvalidResponseData
     */
    public function testRender_handlesException_onInvalidParsedResponseData($oReturnData)
    {
        $sExpectedResult = 'someTemplateName';
        $sTokenInSession = $sTokenInRequest = 'TheSameToken';

        /** Arrange */
        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession);
        $this->_letTheInvalidTokenExceptionNotBeExpected();

        $oHandler = $this->_getGetExpressCheckoutDetailsHandler();
        $this->_letGetExpressCheckoutHandlerReturnGivenReturnDataObject($oReturnData, $oHandler);

        $this->_letGetGetExpressCheckoutHandlerReturnAGivenHandler($oHandler);

        $this->_SUT->expects($this->once())->method('_paypInstallments_handlePayPalInstallmentsDoRequestException');
        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult);

        /** Act */
        $sActualResult = $this->_SUT->render();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testRender_willStoreFinancingDetailsInSession_onDoRequestSuccess()
    {
        $sExpectedResult = 'someTemplateName';
        $sExpectedFinancingDetailsClass = 'paypInstallmentsFinancingDetails';
        $fExpectedAmount = 10000.0;
        $iExpectedFinancingDetailsTerm = 6;

        $oExpectedFinancingDetailsFeeAmount = $this->_getPriceForAmount($fExpectedAmount);
        $oExpectedFinancingDetailsTotalCost = $this->_getPriceForAmount($fExpectedAmount);
        $oExpectedFinancingDetailsMonthlyPayment = $this->_getPriceForAmount($fExpectedAmount);

        $sTokenInSession = $sTokenInRequest = 'TheSameToken';

        /** Arrange */
        $oResponseDataMock = $this->_getResponseDataMockAndLetItReturnGivenValues($fExpectedAmount, $iExpectedFinancingDetailsTerm);

        $this->_letSuccessParameterBePartOfRequest();
        $this->_letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest);
        $this->_letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession);
        $this->_letTheInvalidTokenExceptionNotBeExpected();

        $oHandler = $this->_getGetExpressCheckoutDetailsHandler();
        $this->_letGetExpressCheckoutHandlerReturnGivenReturnDataObject($oResponseDataMock, $oHandler);

        $this->_letGetGetExpressCheckoutHandlerReturnAGivenHandler($oHandler);

        $this->_letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult);

        /** Act */
        $sActualResult = $this->_SUT->render();
        $oActualFinancingDetailsInstance = $this->_SUT->paypInstallments_getFinancingDetailsFromSession();

        /** Assert */
        $this->assertEquals($sExpectedResult, $sActualResult);
        $this->assertInstanceOf($sExpectedFinancingDetailsClass, $oActualFinancingDetailsInstance);
        $this->assertEquals($oExpectedFinancingDetailsFeeAmount, $oActualFinancingDetailsInstance->getFinancingFeeAmount());
        $this->assertEquals($oExpectedFinancingDetailsTotalCost, $oActualFinancingDetailsInstance->getFinancingTotalCost());
        $this->assertEquals($iExpectedFinancingDetailsTerm, $oActualFinancingDetailsInstance->getFinancingTerm());
        $this->assertEquals($oExpectedFinancingDetailsMonthlyPayment, $oActualFinancingDetailsInstance->getFinancingMonthlyPayment());
    }

    public function dataProviderInvalidToken()
    {
        return array(
            array(null, null),
            array('', ''),
            array(null, 'TokenB'),
            array('', 'TokenB'),
            array('TokenA', null),
            array('TokenA', ''),
            array('TokenA', 'TokenB'),
        );
    }

    public function dataProviderDoRequestExceptions()
    {
        return array(
            array(new InvalidArgumentException),
            array(new paypInstallmentsMalformedRequestException),
            array(new paypInstallmentsMalformedResponseException),
            array(new paypInstallmentsSetExpressCheckoutRequestValidationException),
            array(new paypInstallmentsVersionMismatchException),
            array(new paypInstallmentsNoAckSuccessException),
        );
    }

    public function dataProviderInvalidResponseData()
    {
        return array(
            array(null),
            array(''),
            array(array()),
            array(new StdClass()),
        );
    }

    /** Let success parameter be part or the request params  */
    protected function _letSuccessParameterBePartOfRequest()
    {
        $oModuleConfig = new paypInstallmentsConfiguration;
        $successParam = $oModuleConfig->getPayPalInstallmentsSuccessParameter();
        $this->setRequestParam($successParam, 1);
    }

    /** Let the exception to be thrown to see, if it will be catched */
    protected function _letTheInvalidTokenExceptionBeExpectedAndThrown($SUT = null)
    {
        if (is_null($SUT)) {
            $SUT = $this->_SUT;
        }
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_throwInvalidTokenException')
            ->will($this->throwException(new Exception()));
    }

    protected function _letTheInvalidTokenExceptionNotBeExpected($SUT = null)
    {
        if (is_null($SUT)) {
            $SUT = $this->_SUT;
        }
        $SUT
            ->expects($this->never())
            ->method('_paypInstallments_throwInvalidTokenException');
    }

    /**
     * @param $sExpectedResult
     */
    protected function _letTheParentRenderCallBeExpectedAndReturnAString($sExpectedResult, $SUT = null)
    {
        if (is_null($SUT)) {
            $SUT = $this->_SUT;
        }
        $SUT
            ->expects($this->any())
            ->method('_paypInstallments_callRenderParent')
            ->will($this->returnValue($sExpectedResult));
    }

    /**
     * @param $sTokenInSession
     */
    protected function _letTheGivenPayPalTokenToBeReturnedFromSessionRegistry($sTokenInSession, $SUT = null)
    {
        if (is_null($SUT)) {
            $SUT = $this->_SUT;
        }
        $SUT
            ->expects($this->any())
            ->method('_paypInstallments_getPayPalTokenFromSession')
            ->will($this->returnValue($sTokenInSession));
    }

    /**
     * @param $sTokenInRequest
     */
    protected function _letTheGivenPayPalTokenBePartOfTheRequest($sTokenInRequest)
    {
        $this->setRequestParam('token', $sTokenInRequest);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getGetExpressCheckoutDetailsHandler()
    {
        /** @var  $oGetExpressCheckoutDetailsHandlerMock */
        $oGetExpressCheckoutDetailsHandlerMock = $this
            ->getMockBuilder('paypInstallmentsGetExpressCheckoutDetailsHandler')
            ->setMethods(array('__construct', 'doRequest'))
            ->setConstructorArgs(array('AuthToken'))
            ->getMock();

        return $oGetExpressCheckoutDetailsHandlerMock;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $oHandler
     * @param Exception                               $oException
     */
    protected function _letGetExpressCheckoutHandlerThrowGivenException($oHandler, $oException)
    {
        $oHandler
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->throwException($oException));
    }

    /**
     * @param $oHandler
     */
    protected function _letGetGetExpressCheckoutHandlerReturnAGivenHandler($oHandler, $SUT = null)
    {
        if (is_null($SUT)) {
            $SUT = $this->_SUT;
        }
        $SUT
            ->expects($this->once())
            ->method('_paypInstallments_getGetExpressCheckoutDetailsHandler')
            ->will($this->returnValue($oHandler));
    }

    /**
     * @param $oReturnData
     * @param $oHandler
     */
    protected function _letGetExpressCheckoutHandlerReturnGivenReturnDataObject($oReturnData, $oHandler)
    {
        $oHandler
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($oReturnData));
    }

    /**
     * @param $fAmount
     *
     * @return oxPrice
     */
    protected function _getPriceForAmount($fAmount)
    {
        $oPrice = new oxPrice ();
        $oPrice->setBruttoPriceMode();
        $oPrice->add($fAmount);

        return $oPrice;
    }

    /**
     * @param $fExpectedAmount
     * @param $iExpectedFinancingDetailsTerm
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResponseDataMockAndLetItReturnGivenValues($fExpectedAmount, $iExpectedFinancingDetailsTerm, $sCurrency = 'EUR')
    {
        $oResponseDataArray =
            array(
                'PayerId'                         => 'PAY-ID',
                'FinancingFeeAmountValue'         => $fExpectedAmount,
                'FinancingFeeAmountCurrency'      => $sCurrency,
                'FinancingMonthlyPaymentValue'    => $fExpectedAmount,
                'FinancingMonthlyPaymentCurrency' => $sCurrency,
                'FinancingTotalCostValue'         => $fExpectedAmount,
                'FinancingTotalCostCurrency'      => $sCurrency,
                'FinancingTerm'                   => $iExpectedFinancingDetailsTerm,

            );

        return $oResponseDataArray;
    }

    public function testpaypInstallmentss_getFinancingOptionsRenderData_returnExpectedRenderData()
    {
        $fMonthlyPayment = 12345.67;
        $iExpectedFinancingTerm = 6;
        $sExpectedMonthlyPayment = '12.345,67';
        $sExpectedCurrency = 'EUR';

        $oFinancingDetails = new paypInstallmentsFinancingDetails();
        $oFinancingDetails->setFinancingTerm($iExpectedFinancingTerm);
        $oFinancingDetails->setFinancingMonthlyPayment($fMonthlyPayment);
        $oFinancingDetails->setFinancingCurrency($sExpectedCurrency);

        $aExpectedRenderData = array(
            $iExpectedFinancingTerm,
            $sExpectedMonthlyPayment,
            $sExpectedCurrency,
        );
        /** @var paypInstallmentsOrder|PHPUnit_Framework_MockObject_MockObject $oSUT */
        $oSUT = $this->getMockBuilder('paypInstallmentsOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallments_getFinancingDetailsFromSession',))
            ->getMock();

        $oSUT->expects($this->once())
            ->method('paypInstallments_getFinancingDetailsFromSession')
            ->will($this->returnValue($oFinancingDetails));


        $aActualRenderData = $oSUT->paypInstallments_getFinancingOptionsRenderData();

        $this->assertSame($aExpectedRenderData, $aActualRenderData);
    }

}
