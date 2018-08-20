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
 * Class paypInstallmentsFinancingOptionTest
 *
 * @desc Unit tests for paypInstallmentsFinancingOption.
 */
class paypInstallmentsFinancingOptionTest extends OxidTestCase
{

    /**
     * Test class setters-getters.
     *
     * @param $mParam
     * @param $sSetter
     * @param $sGetter
     *
     * @dataProvider testSetGetDataProvider
     */
    public function testSetGet($mParam, $sSetter, $sGetter)
    {
        $oSubjectUnderTest = new paypInstallmentsFinancingOption();

        $this->assertNull($oSubjectUnderTest->$sGetter(), 'Initial value is null.');
        $this->assertSame(
            $mParam,
            $oSubjectUnderTest->$sSetter($mParam)->$sGetter(),
            'Set-get tha same value.'
        );
    }

    /**
     * @return array array(array($mParam, $sSetter, $sGetter), ...)
     */
    public function testSetGetDataProvider()
    {
        return array(
            array('test-pa-num-monthly-payment', 'setNumMonthlyPayments', 'getNumMonthlyPayments'),
            array('test-pa-monthly-payment', 'setMonthlyPayment', 'getMonthlyPayment'),
            array('test-pa-finance-fee', 'setFinancingFee', 'getFinancingFee'),
            array('test-pa-total', 'setTotalPayment', 'getTotalPayment'),
            array('test-pa-min-amount', 'setMinAmount', 'getMinAmount'),
            array('test-pa-currency', 'setCurrency', 'getCurrency'),
        );
    }
}
