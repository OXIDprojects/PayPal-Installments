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
 * Class paypInstallmentsPresentmentTest
 *
 * @desc Unit tests for Installments widget.
 */
class paypInstallmentsPresentmentTest extends PHPUnit_Framework_TestCase
{

    /**
     * Cleanup
     */
    public function tearDown()
    {
        oxRegistry::set('oxUtils', null);
        oxRegistry::set('oxUtilsView', null);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetLogger_throwsException_onWrongParameter()
    {
        /** @var paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = oxNew('paypInstallmentsPresentment');

        $oSubjectUnderTest->setLogger(null);
    }

    public function testGetLogger_returnsExpectedInstance()
    {
        $sExpectedLoggerInstance = '\Psr\Log\LoggerInterface';
        /** @var paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = oxNew('paypInstallmentsPresentment');

        $oActualObject = $oSubjectUnderTest->getLogger();

        $this->assertInstanceOf($sExpectedLoggerInstance, $oActualObject);
    }

    /**
     * @param $sExpected
     * @param $blValidParams
     * @param $sConditionDescriptions
     *
     * @dataProvider testRender_correctTemple_dataProvider
     */
    public function testRender_correctTemple($sExpected, $blValidParams, $sConditionDescriptions)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $oValidator */
        $oValidator = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('validate'))
            ->getMock();
        $blValidParams or $oValidator->expects($this->once())
            ->method('validate')
            ->will($this->throwException(new paypInstallmentsPresentmentValidationException()));

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(array('getViewParameter', 'getValidator'))
            ->getMock();

        $oSubjectUnderTest->expects($this->once())
            ->method('getValidator')
            ->will($this->returnValue($oValidator));

        $this->assertEquals($sExpected, $oSubjectUnderTest->render(), $sConditionDescriptions);
    }

    /**
     * @return array array(array($sExpected, $blValidParams, $sConditionDescriptions), ...)
     */
    public function testRender_correctTemple_dataProvider()
    {
        return array(
            array(
                'widget/presentment/paypinstallmentspresentment.tpl',
                true,
                'Valid parameters - use normal template.'
            ),
            array(
                'widget/presentment/paypinstallmentserror.tpl',
                false,
                'Invalid parameters - Use error template.'
            ),
        );
    }

    /**
     * @param $aExpected
     * @param $aParamMap
     * @param $sConditionDescription
     *
     * @dataProvider testRender_correctParameters_dataProvider
     */
    public function testRender_correctParameters($aExpected, $aParamMap, $sConditionDescription)
    {
        $sTestPaCurrency = 'test-pa-shop-currency-name';
        $sTestPaCountryCode = 'test-pa-shop-country-code';
        $sTestPaRootUrl = 'test-pa-root-url';

        /** @var paypInstallmentsPresentmentValidator|PHPUnit_Framework_MockObject_MockObject $oValidator */
        $oValidator = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(array('validate'))
            ->getMock();
        $oValidator->expects($this->once())
            ->method('validate');

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    'getViewParameter',
                    'getValidator',
                    'getRootUrl',
                    'paypInstallmentPresentment_render_parent',
                    '_fetchCurrency',
                    '_fetchCountryCode',
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())
            ->method('getValidator')
            ->will($this->returnValue($oValidator));
        $oSubjectUnderTest->expects($this->once())
            ->method('getRootUrl')
            ->will($this->returnValue($sTestPaRootUrl));
        $oSubjectUnderTest->expects($this->any())
            ->method('_fetchCurrency')
            ->will($this->returnValue($sTestPaCurrency));
        $oSubjectUnderTest->expects($this->any())
            ->method('_fetchCountryCode')
            ->will($this->returnValue($sTestPaCountryCode));

        $oSubjectUnderTest->expects($this->atLeastOnce())
            ->method('getViewParameter')
            ->will(call_user_func_array(array($this, 'returnValueMap'), array($aParamMap)));
        $oSubjectUnderTest->render();

        $this->assertEquals($aExpected, $oSubjectUnderTest->getRenderData(), $sConditionDescription);
    }

    /**
     * @return array array(array($aExpected, $aParamMap, $sConditionDescription), ...)
     */
    public function testRender_correctParameters_dataProvider()
    {
        return array(
            array(
                array(
                    'amount'   => 'test-pa-amount',
                    'currency' => 'test-pa-currency',
                    'country'  => 'test-pa-country',
                    'root_url' => 'test-pa-root-url',
                ),
                array(
                    array('amount', 'test-pa-amount'),
                    array('currency', 'test-pa-currency'),
                    array('country', 'test-pa-country'),
                ),
                'Get all parameters from viewParameters.'
            ),
            array(
                array(
                    'amount'   => 'test-pa-amount',
                    'currency' => 'test-pa-shop-currency-name',
                    'country'  => 'test-pa-country',
                    'root_url' => 'test-pa-root-url',
                ),
                array(
                    array('amount', 'test-pa-amount'),
                    array('currency', null,),
                    array('country', 'test-pa-country'),
                ),
                'Get missing `currency` parameter from shop.'
            ),
            array(
                array(
                    'amount'   => 'test-pa-amount',
                    'currency' => 'test-pa-currency',
                    'country'  => 'test-pa-shop-country-code',
                    'root_url' => 'test-pa-root-url',
                ),
                array(
                    array('amount', 'test-pa-amount'),
                    array('currency', 'test-pa-currency'),
                    array('country', null),
                ),
                'Get missing parameter `country` from shop.'
            ),
            array(
                array(
                    'amount'   => null,
                    'currency' => 'test-pa-shop-currency-name',
                    'country'  => 'test-pa-shop-country-code',
                    'root_url' => 'test-pa-root-url',
                ),
                array(
                    array('amount', null),
                    array('currency', null),
                    array('country', null),
                ),
                'Get missing parameters `currency` and `country` from shop and `amount` is not set.'
            ),
        );
    }

    public function testRender_CallsSetRenderData_onCacheMiss()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'paypInstallmentPresentment_render_parent',
                    '_paypInstallmentPresentment_setIsCached',
                    '_getCache',
                    'getValidator',
                    'setRenderData'
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())->method('_getCache')->will($this->returnValue(null));

        $this->_letRequestValidate($oSubjectUnderTest);

        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallmentPresentment_setIsCached')
            ->with(
                false
            );

        $oSubjectUnderTest->expects($this->once())->method('setRenderData');

        $oSubjectUnderTest->render();
    }

    public function testRender_setsPaypInstallmentsIsCached_onCacheHit()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'paypInstallmentPresentment_render_parent',
                    '_paypInstallmentPresentment_setIsCached',
                    '_getCache',
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())->method('_getCache')->will($this->returnValue(array('something')));

        $oSubjectUnderTest->expects($this->exactly(2))
            ->method('_paypInstallmentPresentment_setIsCached')
            ->withConsecutive(
                [false], [true]
            );

        $oSubjectUnderTest->render();
    }


    public function testGetCachedPresentmentHtml_rendersExpectedTemplate_onError()
    {
        $sExpectedOutput = 'testGetCachedPresentmentHtml';
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_UNQUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_paypInstallmentPresentment_getTemplateOutput',
                    '_getCache'
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())
            ->method('_getCache')
            ->will($this->returnValue('something-else'));

        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallmentPresentment_getTemplateOutput')
            ->with($sExpectedTemplate)
            ->will($this->returnValue($sExpectedOutput));

        $sActualOutput = $oSubjectUnderTest->getCachedPresentmentHtml();

        $this->assertEquals($sExpectedOutput, $sActualOutput);
    }

    public function testGetCachedPresentmentHtml_rendersExpectedTemplate_onSuccess()
    {
        $sExpectedOutput = 'testGetCachedPresentmentHtml';
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_QUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_paypInstallmentPresentment_getTemplateOutput',
                    '_getCache'
                )
            )
            ->getMock();

        $oFinancingOption = new paypInstallmentsFinancingOption();
        $oFinancingOption->setAnnualPercentageRate(1);

        $oSubjectUnderTest->expects($this->once())
            ->method('_getCache')
            ->will($this->returnValue($oFinancingOption));


        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallmentPresentment_getTemplateOutput')
            ->with($sExpectedTemplate)
            ->will($this->returnValue($sExpectedOutput));

        $sActualOutput = $oSubjectUnderTest->getCachedPresentmentHtml();

        $this->assertEquals($sExpectedOutput, $sActualOutput);
    }

    public function testGetCachedPresentmentHtml_rendersExpectedTemplate_onSuccess_APR0()
    {
        $sExpectedOutput = 'testGetCachedPresentmentHtml';
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_QUALIFIED_OPTIONS_SIMPLE;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_paypInstallmentPresentment_getTemplateOutput',
                    '_getCache'
                )
            )
            ->getMock();

        $oFinancingOption = new paypInstallmentsFinancingOption();
        $oFinancingOption->setAnnualPercentageRate(0);

        $oSubjectUnderTest->expects($this->once())
            ->method('_getCache')
            ->will($this->returnValue($oFinancingOption));


        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallmentPresentment_getTemplateOutput')
            ->with($sExpectedTemplate)
            ->will($this->returnValue($sExpectedOutput));

        $sActualOutput = $oSubjectUnderTest->getCachedPresentmentHtml();

        $this->assertEquals($sExpectedOutput, $sActualOutput);
    }

    public function testGetPresentmentHtml_rendersExpectedTemplate_onSuccessAndQualifiedOptions()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_QUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $aExpectedOptions = $this->_getFinancingOptionsSample();
        $this->_letRequestReturnValue($oSubjectUnderTest, $aExpectedOptions);

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $oSubjectUnderTest->getPresentmentHtml();
    }

    public function testGetPresentmentHtml_rendersExpectedTemplate_onSuccessAndQualifiedOptions_APR0()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_QUALIFIED_OPTIONS_SIMPLE;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $oFinancingOption = new paypInstallmentsFinancingOption();
        $oFinancingOption->setAnnualPercentageRate(0);

        $this->_letRequestReturnValue($oSubjectUnderTest, array($oFinancingOption));

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $oSubjectUnderTest->getPresentmentHtml();
    }


    public function testGetPresentmentHtml_rendersExpectedTemplate_onSuccessAndUnQualifiedOptions()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_UNQUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $this->_letRequestReturnValue($oSubjectUnderTest, 'something-else');

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $oSubjectUnderTest->getPresentmentHtml();
    }

    public function testGetPresentmentHtml_rendersExpectedTemplate_onError()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_ERROR;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $this->_letRequestNotValidateAndCallExceptionDebugOutput($oSubjectUnderTest);

        $oSubjectUnderTest->getPresentmentHtml();
    }

    public function testGetPresentmentInfoHtml_rendersExpectedTemplate_onSuccessAndQualifiedOptions()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $aExpectedOptions = $this->_getFinancingOptionsSample();
        $this->_letRequestReturnValue($oSubjectUnderTest, $aExpectedOptions);

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $oSubjectUnderTest->getPresentmentInfoHtml();
    }

    public function testGetPresentmentInfoHtml_rendersExpectedTemplate_onError()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                )
            )
            ->getMock();

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $this->_letRequestNotValidateAndCallExceptionDebugOutput($oSubjectUnderTest);

        $oSubjectUnderTest->getPresentmentInfoHtml();
    }

    public function testGetPresentmentInfoHtml_rendersExpectedTemplate_onSuccessAndUnQualifiedOptions()
    {
        $sExpectedTemplate = paypInstallmentsPresentment::TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS;

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $this->_letRequestReturnValue($oSubjectUnderTest, 'something-else');

        $oSubjectUnderTest->expects($this->once())->method('_renderAndDisplay')->with($sExpectedTemplate);

        $oSubjectUnderTest->getPresentmentInfoHtml();
    }

    public function testGetFinancingOptionsHandler()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = new paypInstallmentsPresentment();
        $oSubjectUnderTest->setAmount('pa-test-amount');
        $oSubjectUnderTest->setCurrency('pa-test-currency');
        $oSubjectUnderTest->setCountryCode('pa-test-country');

        $oHandler1 = new paypInstallmentsGetFinancingOptionsHandler(null, null, null);
        $oSubjectUnderTest->setFinancingOptionsHandler($oHandler1);
        $this->assertSame($oHandler1, $oSubjectUnderTest->getFinancingOptionsHandler());

        /** @var paypInstallmentsGetFinancingOptionsHandler $oHandler2 */
        $oHandler2 = $oSubjectUnderTest->setFinancingOptionsHandler()
            ->getFinancingOptionsHandler();
        $this->assertNotSame($oHandler1, $oSubjectUnderTest->getFinancingOptionsHandler());
        $this->assertInstanceOf('paypInstallmentsGetFinancingOptionsHandler', $oHandler2);
        $this->assertEquals($oHandler2->getAmount(), $oSubjectUnderTest->getAmount());
        $this->assertEquals($oHandler2->getCurrency(), $oSubjectUnderTest->getCurrency());
        $this->assertEquals($oHandler2->getCountryCode(), $oSubjectUnderTest->getCountryCode());
    }

    public function testGetValidator()
    {
        /** @var|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = new paypInstallmentsPresentment();

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentmentValidator $oValidator1 */
        $oValidator1 = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $oSubjectUnderTest->setValidator($oValidator1);
        $this->assertSame($oValidator1, $oSubjectUnderTest->getValidator());

        $oValidator2 = $oSubjectUnderTest->setValidator()->getValidator();
        $this->assertNotSame($oValidator1, $oValidator2);
        $this->assertInstanceOf('paypInstallmentsPresentmentValidator', $oValidator2);
        $this->assertSame($oSubjectUnderTest, $oValidator2->getPresentment());
    }

    public function testIsBasketController()
    {
        /** @var|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = new paypInstallmentsPresentment();
        $_SESSION['cl'] = 'basket';
        $this->assertEquals(true, $oSubjectUnderTest->isBasketController());
        $_SESSION['cl'] = 'start';
        $this->assertEquals(false, $oSubjectUnderTest->isBasketController());
    }

    public function testGetFinancingOptions()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsPresentment $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsPresentment')
            ->setMethods(
                array(
                    '_renderAndDisplay',
                    'getValidator',
                    'getFinancingOptionsHandler'
                )
            )
            ->getMock();

        $this->_letRequestValidate($oSubjectUnderTest);

        $aExpectedOptions = $this->_getFinancingOptionsSample();
        $this->_letRequestReturnValue($oSubjectUnderTest, $aExpectedOptions);
        $aOptions = $oSubjectUnderTest->getFinancingOptions();

        $this->assertEquals($aExpectedOptions, $aOptions);
        $this->assertFalse($oSubjectUnderTest->isOptionRepresentative(1));
        $this->assertTrue($oSubjectUnderTest->isOptionRepresentative(2));
        $this->assertTrue($oSubjectUnderTest->isOptionRepresentative(3));
    }

    protected function _getFinancingOptionsSample()
    {
        $oFinancingOption1 = new paypInstallmentsFinancingOption();
        $oFinancingOption1->setAnnualPercentageRate(1);
        $oFinancingOption1->setMonthlyPayment(20);
        $oFinancingOption2 = new paypInstallmentsFinancingOption();
        $oFinancingOption2->setAnnualPercentageRate(2);
        $oFinancingOption2->setMonthlyPayment(10);
        $oFinancingOption3 = new paypInstallmentsFinancingOption();
        $oFinancingOption3->setAnnualPercentageRate(3);
        $oFinancingOption3->setMonthlyPayment(30);

        return array($oFinancingOption1, $oFinancingOption2, $oFinancingOption3);
    }

    /**
     * @param $oSubjectUnderTest
     */
    protected function _letRequestValidate(PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest)
    {
        /** @var paypInstallmentsPresentmentValidator|PHPUnit_Framework_MockObject_MockObject $oValidatorMock */
        $oValidatorMock = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'validate',
                )
            )
            ->getMock();
        $oValidatorMock->expects($this->once())->method('validate');
        $oSubjectUnderTest->expects($this->once())->method('getValidator')->will($this->returnValue($oValidatorMock));
    }

    /**
     * @param $oSubjectUnderTest
     */
    protected function _letRequestNotValidateAndCallExceptionDebugOutput(PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest)
    {
        /** @var paypInstallmentsPresentmentValidator|PHPUnit_Framework_MockObject_MockObject $oValidatorMock */
        $oValidatorMock = $this->getMockBuilder('paypInstallmentsPresentmentValidator')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'validate',
                )
            )
            ->getMock();

        /** @var oxException|PHPUnit_Framework_MockObject_MockObject $oExceptionMock */
        $oExceptionMock = $this->getMockBuilder('oxException')->getMock();
        $oExceptionMock->expects($this->once())->method('debugOut');
        $oValidatorMock->expects($this->once())->method('validate')->will($this->throwException($oExceptionMock));

        $oSubjectUnderTest->expects($this->once())->method('getValidator')->will($this->returnValue($oValidatorMock));
    }

    /**
     * @param $oSubjectUnderTest
     */
    protected function _letRequestReturnValue(PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest, $mValue)
    {
        $oFinancingOptionsHandlerMock = $this->getMockBuilder('opaypInstallmentsGetFinancingOptionsHandler')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'doRequest',
                )
            )
            ->getMock();
        $oFinancingOptionsHandlerMock->expects($this->once())->method('doRequest')->will($this->returnValue($mValue));
        $oSubjectUnderTest->expects($this->once())->method('getFinancingOptionsHandler')->will($this->returnValue($oFinancingOptionsHandlerMock));
    }
}
