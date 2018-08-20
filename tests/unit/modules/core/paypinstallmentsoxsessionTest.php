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
 * Class paypInstallmentsOxSessionTest
 *
 * @covers paypInstallmentsOxSession
 */
class paypInstallmentsOxSessionTest extends OxidTestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxSession $_SUT
     */
    protected $_SUT;

    public function setUp()
    {
        parent::setUp();

        $this->_SUT = $this->getMockBuilder('paypInstallmentsOxSession')
            ->setMethods(array('__call', '__construct',))
            ->getMock();
    }

    /**
     * @dataProvider dataProviderRegistryKeys
     *
     * @param $sKey
     */
    public function testSetPayPalInstallmentsRegistryValueByKey_canGetCorrectValueByKey($sKey)
    {
        $sExpectedValue = 'VALUE';

        $this->_SUT->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey($sKey, $sExpectedValue);
        $sActualValue = $this->_SUT->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey($sKey);

        $this->assertEquals($sExpectedValue, $sActualValue);
    }

    /**
     * @dataProvider dataProviderRegistryKeys
     *
     * @param $sKey
     */
    public function testDeletePayPalInstallmentsRegistryKey_deletesRegistryKey($sKey)
    {
        $sExpectedValue = 'VALUE';

        $this->_SUT->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey($sKey, $sExpectedValue);
        $sActualValue = $this->_SUT->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey($sKey);

        $this->assertEquals($sExpectedValue, $sActualValue);

        $this->_SUT->paypInstallmentsDeletePayPalInstallmentsRegistryKey($sKey);

        $sActualValue = $this->_SUT->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey($sKey);

        $this->assertNull($sActualValue);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage PAYP_ERR_INVALID_KEY
     */
    public function testSetPayPalInstallmentsRegistryValueByKey_throwsException_onInvalidKey()
    {
        $sInvalidKey = 'InvalidKey';
        $sExpectedValue = 'VALUE';

        $this->_SUT->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey($sInvalidKey, $sExpectedValue);
    }

    public function testSetPayPalInstallmentsRegistryValueByKey_throwsException_onInvalidKey_codeCoverage()
    {
        $sInvalidKey = 'InvalidKey';
        $sExpectedValue = 'VALUE';

        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsOxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'throwInvalidArgumentException'))
            ->getMock();

        $subjectUnderTest->expects($this->once())
            ->method('throwInvalidArgumentException')
            ->with($this->equalTo('PAYP_ERR_INVALID_KEY'));
        $subjectUnderTest->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey($sInvalidKey, $sExpectedValue);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage PAYP_ERR_INVALID_KEY
     */
    public function testGetPayPalInstallmentsRegistryValueByKey_throwsException_onInvalidKey()
    {
        $this->_SUT->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey('InvalidKey');
    }

    public function testGetPayPalInstallmentsRegistryValueByKey_throwsException_onInvalidKey_codeCoverage()
    {
        $sInvalidKey = 'InvalidKey';

        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsOxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'throwInvalidArgumentException'))
            ->getMock();

        $subjectUnderTest->expects($this->once())
            ->method('throwInvalidArgumentException')
            ->with($this->equalTo('PAYP_ERR_INVALID_KEY'));

        $subjectUnderTest->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey($sInvalidKey);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage PAYP_ERR_INVALID_KEY
     */
    public function testDeletePayPalInstallmentsRegistryKey_throwsException_onInvalidKey()
    {
        $this->_SUT->paypInstallmentsDeletePayPalInstallmentsRegistryKey('InvalidKey');
    }

    public function testDeletePayPalInstallmentsRegistryKey_throwsException_onInvalidKey_codeCoverage()
    {
        $sInvalidKey = 'InvalidKey';

        $subjectUnderTest = $this->getMockBuilder('paypInstallmentsOxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('__call', 'throwInvalidArgumentException'))
            ->getMock();

        $subjectUnderTest->expects($this->once())
            ->method('throwInvalidArgumentException')
            ->with($this->equalTo('PAYP_ERR_INVALID_KEY'));
        $subjectUnderTest->paypInstallmentsDeletePayPalInstallmentsRegistryKey($sInvalidKey);
    }

    public function testDeletePayPalInstallmentsRegistry_deletesRegistry()
    {
        $oSession = $this->getSession();
        $sRegistryKey = paypInstallmentsOxSession::aPayPalInstallmentsRegistryKey;

        $this->_SUT->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(
            paypInstallmentsOxSession::sBasketFingerprintKey,
            '00000000'
        );

        $this->assertTrue(is_array($oSession->getVar($sRegistryKey)));
        $this->assertNotEmpty($oSession->getVar($sRegistryKey));

        $this->_SUT->paypInstallmentsDeletePayPalInstallmentsRegistry();

        $this->assertFalse(is_array($oSession->getVar($sRegistryKey)));
        $this->assertNull($oSession->getVar($sRegistryKey));
    }

    public function dataProviderRegistryKeys()
    {
        return array(
            array(paypInstallmentsOxSession::sBasketFingerprintKey),
            array(paypInstallmentsOxSession::sPayPalTokenKey),
            array(paypInstallmentsOxSession::sBillingCountryKey),
            array(paypInstallmentsOxSession::sShippingCountryKey),
        );
    }

    public function testPaGetOrderNr()
    {
        $sOrderNb = 'test-pa-order-nb';

        /** @var paypInstallmentsOxSession|PHPUnit_Framework_MockObject_MockObject $oSut */
        $oSut = $this->getMockBuilder('paypInstallmentsOxSession')
            ->disableOriginalconstructor()
            ->setMethods(
                array(
                    'paypInstallmentsGetPayPalInstallmentsRegistryValueByKey',
                    'paypInstallmentsSetPayPalInstallmentsRegistryValueByKey'
                )
            )
            ->getMock();
        $oSut->expects($this->once())
            ->method('paypInstallmentsSetPayPalInstallmentsRegistryValueByKey')
            ->with('OrderNr', $sOrderNb);
        $oSut->expects($this->atLeastOnce())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->will($this->onConsecutiveCalls('', $sOrderNb, $sOrderNb, $sOrderNb));

        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallments_getOrderNr'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('paypInstallments_getOrderNr')
            ->will($this->returnValue($sOrderNb));
        oxUtilsObject::setClassInstance('oxOrder', $oOrder);

        $this->assertSame($sOrderNb, $oSut->paypInstallmentsGetOrderNr());
        $this->assertSame($sOrderNb, $oSut->paypInstallmentsGetOrderNr());

        oxUtilsObject::getInstance('oxOrder', null);
    }
}
