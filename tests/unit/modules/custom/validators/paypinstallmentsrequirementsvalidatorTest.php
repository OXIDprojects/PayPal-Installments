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
 * Class paypInstallmentsRequirementsValidatorTest
 */
class paypInstallmentsRequirementsValidatorTest extends OxidTestCase
{

    /**
     * System under the test.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRequirementsValidator $_SUT
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(array('__construct'))
            ->getMock();
    }

    public function testValidateRequirements_throwsException_withUserAndBasketUnset()
    {
        $sExpectedException = 'InvalidArgumentException';
        $this->setExpectedException($sExpectedException);

        $this->_SUT->validateRequirements();
    }

    public function testValidateRequirements_throwsException_withBasketUnset()
    {
        $sExpectedException = 'InvalidArgumentException';
        $this->setExpectedException($sExpectedException);

        $this->_SUT->setUser(new oxUser());

        $this->_SUT->validateRequirements();
    }

    public function testValidateRequirements_throwsException_withUserUnset()
    {
        $sExpectedException = 'InvalidArgumentException';
        $this->setExpectedException($sExpectedException);

        $this->_SUT->setBasket(new oxBasket());

        $this->_SUT->validateRequirements();
    }

    public function testValidateRequirements_callsAllInternalValidationMethods()
    {
        /**
         * System under the test.
         *
         * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRequirementsValidator $SUT
         */
        $SUT = $this->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(
                array(
                    '__construct',
                    '_validateShippingCountry',
                    '_validateBillingCountry',
                    '_validateMinOrderAmount',
                    '_validateMaxOrderAmount'
                )
            )
            ->getMock();
        $SUT->setBasket(new oxBasket());
        $SUT->setUser(new oxUser());

        $SUT->expects($this->once())->method('_validateShippingCountry');
        $SUT->expects($this->once())->method('_validateBillingCountry');
        $SUT->expects($this->once())->method('_validateMinOrderAmount');
        $SUT->expects($this->once())->method('_validateMaxOrderAmount');

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_doesNotThrowException_onNoShippingCountrySet()
    {

        /**
         * Remove delivery address from session.
         */
        $this->setSessionParam('deladrid', null);

        $SUT = $this->_getValidatorMockToValidateShippingCountry();

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_doesNotThrowException_onNonExistentAddressID()
    {

        $sAddressId = 'NON_EXISTENT_ADDRESS_ID';
        $this->setSessionParam('deladrid', $sAddressId);

        $SUT = $this->_getValidatorMockToValidateShippingCountry();

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_doesNotThrowException_onValidShippingCountry()
    {
        $sAddressId = $this->_getAddressWithCountryCode(paypInstallmentsConfiguration::getRequiredShippingCountry());
        $this->setSessionParam('deladrid', $sAddressId);

        $SUT = $this->_getValidatorMockToValidateShippingCountry();

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_throwsExpectedException_onMissingShippingCountryId()
    {
        $this->setExpectedException('InvalidArgumentException');
        $sExpectedMessage = 'Country ID not found for country code ' . paypInstallmentsConfiguration::getRequiredShippingCountry();

        $sAddressId = $this->_getAddressWithCountryCode(paypInstallmentsConfiguration::getRequiredShippingCountry());
        $this->setSessionParam('deladrid', $sAddressId);

        $SUT = $this->_getValidatorMockToValidateShippingCountry();

        // change the country code of the country so validator will not find it by code
        $this->_updateCountryCode(paypInstallmentsConfiguration::getRequiredShippingCountry(), '--');
        $sCurrentCountryCode = paypInstallmentsConfiguration::getRequiredShippingCountry();
        try {
            $SUT->validateRequirements();
        } catch (Exception $oEx) {
            // Reestablish original country code
            $this->_updateCountryCode('--', paypInstallmentsConfiguration::getRequiredShippingCountry());

            $sActualMessage = $oEx->getMessage();
            $this->assertEquals($sExpectedMessage, $sActualMessage);
            throw new $oEx;
        }
    }

    public function testValidateRequirements_throwsExpectedException_onInvalidShippingCountry()
    {
        $sExpectedException = 'paypInstallmentsRequirementsValidatorException';
        $this->setExpectedException($sExpectedException, 'PAYP_ERR_VALIDATION_WRONG_SHIPPING_COUNTRY');

        $sAddressId = $this->_getAddressWithCountryCode('AT');
        $this->setSessionParam('deladrid', $sAddressId);

        $SUT = $this->_getValidatorMockToValidateShippingCountry();

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_throwsExpectedException_onInvalidBillingCountry()
    {
        $sExpectedException = 'paypInstallmentsRequirementsValidatorException';
        $this->setExpectedException($sExpectedException);

        $oUser = $this->_getUserWithCountryCode('AT');

        $SUT = $this->_getValidatorMockToValidateBillingCountry();
        $SUT->setUser($oUser);

        $SUT->validateRequirements();
    }

    public function testValidateRequirements_doesNotThrowExpectedException_onValidBillingCountry()
    {

        $oUser = $this->_getUserWithCountryCode('DE');

        $SUT = $this->_getValidatorMockToValidateBillingCountry();
        $SUT->setUser($oUser);

        $SUT->validateRequirements();
    }

    /**
     * @dataProvider dataProviderInvalidOrderAmounts
     *
     * @param float  $fAmount
     * @param bool   $blIsNettoMode
     * @param string $sExpectedMessage
     *
     * @throws Exception
     */
    public function testValidateRequirements_throwsExpectedExceptions_onInvalidAmounts_byAmountAndPriceMode($fAmount, $blIsNettoMode, $sExpectedMessage)
    {
        $this->setConfigParam('blShowNetPrice', $blIsNettoMode);

        $sExpectedException = 'paypInstallmentsRequirementsValidatorException';
        $this->setExpectedException($sExpectedException);

        $oBasket = $this->_getBasketMockWithTotalAmount($fAmount, $blIsNettoMode);

        $SUT = $this->_getValidatorMockToValidateAmounts();
        $SUT->setBasket($oBasket);

        try {
            $SUT->validateRequirements();
        } catch (Exception $oEx) {
            $sActualMessage = $oEx->getMessage();
            $this->assertEquals($sExpectedMessage, $sActualMessage);
            throw $oEx;
        }
    }

    /**
     * @dataProvider dataProviderValidOrderAmounts
     *
     * @param float $fAmount
     * @param bool  $blIsNettoMode
     */
    public function testValidateRequirements_doesNotThrowException_onValidAmounts_byAmountAndPriceMode($fAmount, $blIsNettoMode)
    {
        $this->setConfigParam('blShowNetPrice', $blIsNettoMode);

        $oBasket = $this->_getBasketMockWithTotalAmount($fAmount, $blIsNettoMode);

        $SUT = $this->_getValidatorMockToValidateAmounts();
        $SUT->setBasket($oBasket);

        $SUT->validateRequirements();
    }

    public function dataProviderInvalidOrderAmounts()
    {
        return array(
            // $fAmount, $blIsNettoMode, $sExpectedMessage
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount() - 0.01, false, paypInstallmentsConfiguration::getValidationErrorMessage('MINIMAL_QUALIFYING_ORDER_TOTAL_NOT_MET')),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.01, false, paypInstallmentsConfiguration::getValidationErrorMessage('MAXIMAL_QUALIFYING_ORDER_TOTAL_EXCEEDED')),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount() - 0.01, true, paypInstallmentsConfiguration::getValidationErrorMessage('MINIMAL_QUALIFYING_ORDER_TOTAL_NOT_MET')),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.01, true, paypInstallmentsConfiguration::getValidationErrorMessage('MAXIMAL_QUALIFYING_ORDER_TOTAL_EXCEEDED')),
        );
    }

    public function dataProviderValidOrderAmounts()
    {
        return array(
            // $fAmount, $blIsNettoMode, $sExpectedMessage
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount(), false),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount(), false),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount(), true),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount(), true),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount() - 0.001, false),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.001, false),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMinAmount() - 0.001, true),
            array((float) paypInstallmentsConfiguration::getPaymentMethodMaxAmount() + 0.001, true),
        );
    }

    /**
     * @return paypInstallmentsRequirementsValidator|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getValidatorMockToValidateShippingCountry()
    {
        /**
         * System under the test.
         *
         * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRequirementsValidator $SUT
         */
        $SUT = $this->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(
                array(
                    '__construct',
                    '_validateBillingCountry',
                    '_validateMinOrderAmount',
                    '_validateMaxOrderAmount'
                )
            )
            ->getMock();
        $SUT->setBasket(new oxBasket());
        $SUT->setUser(new oxUser());

        $SUT->expects($this->any())->method('_validateBillingCountry');
        $SUT->expects($this->any())->method('_validateMinOrderAmount');
        $SUT->expects($this->any())->method('_validateMaxOrderAmount');

        return $SUT;
    }

    /**
     * @return paypInstallmentsRequirementsValidator|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getValidatorMockToValidateBillingCountry()
    {
        /**
         * System under the test.
         *
         * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRequirementsValidator $SUT
         */
        $SUT = $this->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(
                array(
                    '__construct',
                    '_validateShippingCountry',
                    '_validateMinOrderAmount',
                    '_validateMaxOrderAmount'
                )
            )
            ->getMock();
        $SUT->setBasket(new oxBasket());
        $SUT->setUser(new oxUser());

        $SUT->expects($this->any())->method('_validateShippingCountry');
        $SUT->expects($this->any())->method('_validateMinOrderAmount');
        $SUT->expects($this->any())->method('_validateMaxOrderAmount');

        return $SUT;
    }

    /**
     * @return paypInstallmentsRequirementsValidator|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getValidatorMockToValidateAmounts()
    {
        /**
         * System under the test.
         *
         * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsRequirementsValidator $SUT
         */
        $SUT = $this->getMockBuilder('paypInstallmentsRequirementsValidator')
            ->setMethods(
                array(
                    '__construct',
                    '_validateBillingCountry',
                    '_validateShippingCountry',
                )
            )
            ->getMock();
        $SUT->setBasket(new oxBasket());
        $SUT->setUser(new oxUser());

        $SUT->expects($this->any())->method('_validateShippingCountry');
        $SUT->expects($this->any())->method('_validateBillingCountry');

        return $SUT;
    }

    protected function _getAddressWithCountryCode($sCountryCode)
    {
        $sAddressId = md5('test_address');

        $oCountry = new oxCountry();
        $sCountryId = $oCountry->getIdByCode($sCountryCode);
        if (!$sCountryId) {
            throw new InvalidArgumentException('Non existing country code ' . $sCountryCode);
        }

        $oAddress = new oxAddress();
        if (!$oAddress->load($sAddressId)) {
            $oAddress->setId($sAddressId);
        }
        $oAddress->oxaddress__oxcountryid = new oxField($sCountryId);

        $oAddress->save();

        return $sAddressId;
    }

    protected function _getUserWithCountryCode($sCountryCode)
    {
        $sUserId = md5('test_user');

        $oCountry = new oxCountry();
        $sCountryId = $oCountry->getIdByCode($sCountryCode);
        if (!$sCountryId) {
            throw new InvalidArgumentException('Non existing country code ' . $sCountryCode);
        }

        $oUser = new oxUser();
        if (!$oUser->load($sUserId)) {
            $oUser->setId($sUserId);
        }
        $oUser->oxuser__oxcountryid = new oxField($sCountryId);

        //$oUser->save();

        return $oUser;
    }

    protected function _getBasketMockWithTotalAmount($fAmount, $blIsNettoMode)
    {
        $fRoundedAmount = oxRegistry::getUtils()->fRound($fAmount);

        $oBasketUserMock = $this->getMockBuilder('oxUser')->setMethods(array('__construct', 'isPriceViewModeNetto'))->getMock();
        $oBasketUserMock->expects($this->any())->method('isPriceViewModeNetto')->will($this->returnValue($blIsNettoMode));

        $oBasketMock = $this
            ->getMockBuilder('oxBasket')
            ->setMethods(array('__construct', 'getBruttoSum', 'getNettoSum', 'getBasketUser'))
            ->getMock();
        $oBasketMock->expects($this->any())->method('getNettoSum')->will($this->returnValue($fRoundedAmount));
        $oBasketMock->expects($this->any())->method('getBruttoSum')->will($this->returnValue($fRoundedAmount));
        $oBasketMock->expects($this->any())->method('getBasketUser')->will($this->returnValue($oBasketUserMock));

        return $oBasketMock;
    }

    protected function _updateCountryCode($sCurrentCountryCode, $sNewCountryCode)
    {
        $oCountry = new oxCountry;
        $sCountryId = $oCountry->getIdByCode($sCurrentCountryCode);
        if (!$oCountry->load($sCountryId)) {
            throw new InvalidArgumentException('Country ID not found ' . $sCountryId);
        }
        $oCountry->oxcountry__oxisoalpha2 = new oxField($sNewCountryCode);
        if (!$oCountry->save()) {
            throw new Exception('Country ID not saved ' . $sCountryId);
        }

        if (!$oCountry->load($sCountryId)) {
            throw new InvalidArgumentException('Country ID not found ' . $sCountryId);
        }
    }
}
