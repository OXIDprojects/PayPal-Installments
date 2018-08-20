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
 * Class paypInstallmentsOxEmailTest
 *
 * @desc Unit tests for class paypInstallmentsOxEmail.
 */
class paypInstallmentsOxEmailTest extends OxidTestCase
{

    private $_oSession;

    public function setUp()
    {
        $this->_oSession = oxRegistry::getSession();
    }

    public function tearDown()
    {
        oxRegistry::set('oxSession', $this->_oSession);
    }

    /**
     * Test financing details added to template only for payment 'paypinstallments'.
     *
     * @param $blExpectedFinancingDetails
     * @param $mPaymentId
     *
     * @dataProvider testSendOrderEmailToUserDataProvider
     */
    public function testSendOrderEmailToUser($blExpectedFinancingDetails, $mPaymentId)
    {
        $oPayment = $this->getMockBuilder('oxPayment')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldData'))
            ->getMock();
        $oPayment->expects($this->once())
            ->method('getFieldData')
            ->with('oxpaymentsid')
            ->will($this->returnValue($mPaymentId));

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPayment'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($oPayment));

        $sSubject = 'test-pa-subject';
        $mFinancingDetails = 'test-pa-financing-details';
        $mParentResult = 'test-pa-parent-result';

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey'))
            ->getMock();
        $blExpectedFinancingDetails && $oSession->expects($this->once())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->with('FinancingDetails')
            ->will($this->returnValue($mFinancingDetails));
        oxRegistry::set('oxSession', $oSession);

        /** @var paypInstallmentsOxEmail|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxEmail')
            //->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setViewData',
                    '_paypInstallments_callParent_sendOrderEmailToUser',
                )
            )
            ->getMock();
        $blExpectedFinancingDetails && $oSubjectUnderTest->expects($this->once())
            ->method('setViewData')
            ->with('oFinancingDetails', $mFinancingDetails);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_callParent_sendOrderEmailToUser')
            ->with($oOrder, $sSubject)
            ->will($this->returnValue($mParentResult));

        $this->assertSame(
            $mParentResult,
            $oSubjectUnderTest->sendOrderEmailToUser($oOrder, $sSubject)
        );
    }

    /**
     * @return array array(array($blExpectedFinancingDetails, $mPaymentId), ...)
     */
    public function testSendOrderEmailToUserDataProvider()
    {
        return array(
            array(true, paypInstallmentsConfiguration::getPaymentId()),
            array(false, 'paypInstallments'),
            array(false, ''),
            array(false, '1'),
        );
    }

    /**
     * Test financing details added to template only for payment 'paypinstallments'.
     *
     * @param $blExpectedFinancingDetails
     * @param $mPaymentId
     *
     * @dataProvider testSendOrderEmailToOwnerDataProvider
     */
    public function testSendOrderEmailToOwner($blExpectedFinancingDetails, $mPaymentId)
    {
        $oPayment = $this->getMockBuilder('oxPayment')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldData'))
            ->getMock();
        $oPayment->expects($this->once())
            ->method('getFieldData')
            ->with('oxpaymentsid')
            ->will($this->returnValue($mPaymentId));

        /** @var oxOrder|PHPUnit_Framework_MockObject_MockObject $oOrder */
        $oOrder = $this->getMockBuilder('oxOrder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPayment'))
            ->getMock();
        $oOrder->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($oPayment));

        $sSubject = 'test-pa-subject';
        $mFinancingDetails = 'test-pa-financing-details';
        $mParentResult = 'test-pa-parent-result';

        $oSession = $this->getMockBuilder('oxSession')
            ->disableOriginalConstructor()
            ->setMethods(array('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey'))
            ->getMock();
        $blExpectedFinancingDetails && $oSession->expects($this->once())
            ->method('paypInstallmentsGetPayPalInstallmentsRegistryValueByKey')
            ->with('FinancingDetails')
            ->will($this->returnValue($mFinancingDetails));
        oxRegistry::set('oxSession', $oSession);

        /** @var paypInstallmentsOxEmail|PHPUnit_Framework_MockObject_MockObject $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxEmail')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setViewData',
                    '_paypInstallments_callParent_sendOrderEmailToOwner',
                )
            )
            ->getMock();
        $blExpectedFinancingDetails && $oSubjectUnderTest->expects($this->once())
            ->method('setViewData')
            ->with('oFinancingDetails', $mFinancingDetails);
        $oSubjectUnderTest->expects($this->once())
            ->method('_paypInstallments_callParent_sendOrderEmailToOwner')
            ->with($oOrder, $sSubject)
            ->will($this->returnValue($mParentResult));

        $this->assertSame(
            $mParentResult,
            $oSubjectUnderTest->sendOrderEmailToOwner($oOrder, $sSubject)
        );
    }

    /**
     * @return array array(array($blExpectedFinancingDetails, $mPaymentId), ...)
     */
    public function testSendOrderEmailToOwnerDataProvider()
    {
        return array(
            array(true, paypInstallmentsConfiguration::getPaymentId()),
            array(false, 'paypInstallments'),
            array(false, ''),
            array(false, '1'),
        );
    }
}
