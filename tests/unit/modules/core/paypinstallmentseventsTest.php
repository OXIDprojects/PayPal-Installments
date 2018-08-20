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
class paypInstallmentsEventsTest extends OxidTestCase
{

    /**
     * Make sure, that after activating the module, the necessary data is
     * available in the DB
     */
    public function testModuleActivation()
    {
        $this->loadModuleDeactivatedQuery();

        paypInstallmentsEvents::onActivate();

        $this->assertPaymentMethodCompletelyInstalled();
    }

    /**
     * Test that module is activated for current shop, but that payment method is still present in DB
     */

    public function testModuleDeActivation()
    {
        $blIsActive = (int)  $this->getConfigParam('paypInstallmentsActive');
        $this->assertEquals(1, $blIsActive);

        paypInstallmentsEvents::onDeactivate();

        $blIsActive = (int)  $this->getConfigParam('paypInstallmentsActive');
        $this->assertEquals(0, $blIsActive);

        // Test that Installments Payment Method is present in the DB
        $oPayment = new oxPayment();
        $blIsLoaded = $oPayment->load(paypInstallmentsConfiguration::getPaymentId());

        $this->assertTrue($blIsLoaded);

    }

    /**
     * Since deactivating the module should remove all trace data from the DB,
     * we need to make sure that all data is recreated, when a module is reactivated
     */
    public function testModuleReActivation()
    {
        paypInstallmentsEvents::onDeactivate();
        paypInstallmentsEvents::onActivate();

        $this->assertPaymentMethodCompletelyInstalled();
    }

    public function _testCreatePaymentSpecificTables_nastyInstallSqlFileDoesNotProduceMysqlError()
    {
        /** Set module to deactivated stat so it can be activated */
        $this->loadModuleDeactivatedQuery();

        /**
         * System under test
         *
         * @var paypInstallmentsEvents $SUT
         */
        $SUT = $this->getMock(
            'paypInstallmentsEvents',
            array('__call', '_getFileContents')
        );

        $SUT::staticExpects($this->once())
            ->method('_getFileContents')
            ->with('install.sql')
            ->will(
                $this->returnValue(
                    file_get_contents(getTestsBasePath() . '/unit/testdata/nasty_install.sql')
                )
            );

        $SUT::onActivate();
    }

    public function _testCreatePaymentSpecificTables_brokenInstallSqlFileProducesMysqlError()
    {

        $this->setExpectedException('oxAdoDbException');

        /** Set module to deactivated stat so it can be activated */
        $this->loadModuleDeactivatedQuery();

        /**
         * System under test
         *
         * @var paypInstallmentsEvents $SUT
         */
        $SUT = $this->getMock(
            'paypInstallmentsEvents',
            array('__call', '_getFileContents')
        );

        $SUT::staticExpects($this->once())
            ->method('_getFileContents')
            ->with('install.sql')
            ->will(
                $this->returnValue(
                    file_get_contents(getTestsBasePath() . '/unit/testdata/broken_install.sql')
                )
            );

        $SUT::onActivate();
    }

    /**
     * Test whether all required data is within the DB
     */
    protected function assertPaymentMethodCompletelyInstalled()
    {
        // make sure the payment method is present in the DB
        $oPayment = oxNew("oxpayment");
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();
        $oPayment->load($sPaymentMethodOxid);
        $this->assertTrue($oPayment !== null);

        // make sure that germany is the only country assigned to the payment method
        $aPaymentCountries = $oPayment->getCountries();
        $oDb = oxDb::getInstance()->getDb();
        $sGermanyCountryId = $oDb->getOne("select oxid from oxcountry where oxisoalpha2 = " . $oDb->quote('DE'));
        $this->assertEquals(array($sGermanyCountryId), $aPaymentCountries);

        // make sure, that all groups are assigned to the payment method
        $oDb = oxDb::getInstance()->getDb();
        $aAllGroupIdRows = $oDb->getAll('SELECT oxid FROM oxgroups');
        $aPaymentGroupIdRows = $oDb->getAll('SELECT oxgroupsid FROM oxobject2group WHERE oxobjectid = ' . $oDb->quote($sPaymentMethodOxid));
        $this->assertEquals(
            $this->extractActualDataFromDBResponse($aAllGroupIdRows),
            $this->extractActualDataFromDBResponse($aPaymentGroupIdRows)
        );
    }

    /**
     * Test if all Installment Payment related Data was deleted from the DB
     */
    protected function assertNoPaymentMethodRelatedDataIsPersisted()
    {
        $oDb = oxDb::getInstance()->getDb();
        $sPaymentMethodOxid = paypInstallmentsConfiguration::getPaymentId();

        // Test if no group is assigned to the installments payment method
        $iNumAssignedGroups = $oDb->getOne(
            "SELECT COUNT(oxid)
                FROM oxobject2group
                WHERE oxobjectid='$sPaymentMethodOxid'"
        );
        $this->assertEquals(0, $iNumAssignedGroups);

        // Test if Installments Payment Method is not in the DB
        $oPayment = new oxPayment();
        $oPayment->load($sPaymentMethodOxid);
        $this->assertFalse($oPayment->getId() == $sPaymentMethodOxid);
    }

    /**
     * @param $aDataRows the response from an oxLegacyDB request
     *
     * Extract only the actual data from a DB response and return it
     *
     * @return array
     */
    protected function extractActualDataFromDBResponse($aDataRows)
    {
        $aData = array_map(
            function ($aDataRow) {
                return $aDataRow[0];
            }, $aDataRows
        );

        return asort($aData);
    }

    /**
     * In order to assure that there are no false positives,
     * load a DB dump in which the module was not previously activated
     */
    protected function loadModuleDeactivatedQuery()
    {
        importTestdataFile('deactivate_module.sql');
    }

}
