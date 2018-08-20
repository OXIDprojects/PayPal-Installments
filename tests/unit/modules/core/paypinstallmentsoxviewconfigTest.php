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
 * Class paypInstallmentsOxViewConfigTest
 *
 * @desc Unit test for paypInstallmentsOxViewConfig.
 */
class paypInstallmentsOxViewConfigTest extends OxidTestCase
{

    public function testGetModuleService()
    {
        $oSubjectUnderTest = new paypInstallmentsOxViewConfig();

        $this->assertInstanceOf('oxModule', $oSubjectUnderTest->getModuleService());
    }

    /**
     * @param $sExpected
     * @param $sResourceRelativePath
     *
     * @dataProvider testGetPayPalInstallmentsUrl_dataProvider
     */
    public function testGetPayPalInstallmentsUrl($sExpected, $sResourceRelativePath)
    {
        $sModulePath = 'test/pa/module/path/';
        $sCalculatedUrl = 'test-pa-calculated-url';

        $oModule = $this->getMockBuilder('oxModule')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getModulePath'))
            ->getMock();
        $oModule->expects($this->once())
            ->method('load')
            ->with('paypinstallments');
        $oModule->expects($this->once())
            ->method('getModulePath')
            ->will($this->returnValue($sModulePath));

        /** @var PHPUnit_Framework_MockObject_MockObject|paypInstallmentsOxViewConfig $oSubjectUnderTest */
        $oSubjectUnderTest = $this->getMockBuilder('paypInstallmentsOxViewConfig')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleService', 'getModuleUrl'))
            ->getMock();
        $oSubjectUnderTest->expects($this->once())
            ->method('getModuleService')
            ->will($this->returnValue($oModule));


        $oSubjectUnderTest->expects($this->once())
            ->method('getModuleUrl')
            ->with($sModulePath, $sExpected)
            ->will($this->returnValue($sCalculatedUrl));

        $this->assertEquals(
            $sCalculatedUrl,
            $oSubjectUnderTest->getPayPalInstallmentsUrl($sResourceRelativePath)
        );
    }

    public function testIsShowAdvertOnSideBar()
    {
        $oSubjectUnderTest = oxNew('paypInstallmentsOxViewConfig');
        $oConfig = oxRegistry::getConfig();
        $oConfig->setConfigParam('paypInstallmentsGenAdvertHome', true);
        $_SESSION['cl'] = 'start';
        $this->assertEquals(true, $oSubjectUnderTest->isShowAdvertOnSideBar());
        $_SESSION['cl'] = 'something-else';
        $this->assertEquals(false, $oSubjectUnderTest->isShowAdvertOnSideBar());
        $_SESSION['cl'] = 'details';
        $oConfig->setConfigParam('paypInstallmentsGenAdvertDetail', false);
        $this->assertEquals(false, $oSubjectUnderTest->isShowAdvertOnSideBar());
        $oConfig->setConfigParam('paypInstallmentsGenAdvertHome', false);
        $oConfig->setConfigParam('paypInstallmentsGenAdvertDetail', true);
        $this->assertEquals(true, $oSubjectUnderTest->isShowAdvertOnSideBar());
        $oConfig->setConfigParam('paypInstallmentsGenAdvertDetail', false);
        $oConfig->setConfigParam('paypInstallmentsGenAdvertCat', true);
        $_SESSION['cl'] = 'alist';
        $this->assertEquals(true, $oSubjectUnderTest->isShowAdvertOnSideBar());
    }

    public function testIsWithCalculatedValue()
    {
        $oSubjectUnderTest = oxNew('paypInstallmentsOxViewConfig');
        $oConfig = oxRegistry::getConfig();
        $oConfig->setConfigParam('paypInstallmentsWithCalcValue', true);
        $this->assertEquals(true, $oSubjectUnderTest->isWithCalculatedValue());
    }

    public function testGetInstallmentsCreditor()
    {
        $oSubjectUnderTest = oxNew('paypInstallmentsOxViewConfig');
        $oShop = oxNew('oxShop');
        $oShop->oxshops__oxcompany = new oxField('MyCompany');
        $oShop->oxshops__oxstreet  = new oxField('MyStreet 13');
        $oShop->oxshops__oxzip     = new oxField('12345');
        $oShop->oxshops__oxcity    = new oxField('MyCity');
        $this->assertEquals('MyCompany, MyStreet 13, 12345 MyCity', $oSubjectUnderTest->getInstallmentsCreditor($oShop));

    }
    /**
     * @return array array(array($sExpected, $sResourceRelativePath), ...)
     */
    public function testGetPayPalInstallmentsUrl_dataProvider()
    {
        return array(
            array('out/src/', ''),
            array('out/src/pa/relative/path', 'pa/relative/path'),
        );
    }
}
