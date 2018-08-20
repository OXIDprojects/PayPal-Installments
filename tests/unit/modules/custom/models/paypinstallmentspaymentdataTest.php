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
 * Class paypInstallmentsPaymentDataTest
 * Tests for paypInstallmentsPaymentData model.
 *
 * @see paypInstallmentsPaymentData
 */
class paypInstallmentsPaymentDataTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var paypInstallmentsPaymentData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $SUT;

    /**
     * @inheritDoc
     *
     * Set SUT state before test.
     * Import data to test loading methods
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock('paypInstallmentsPaymentData', array('__call'));
    }

    /**
     * @param bool   $blExpected
     * @param string $sStatus
     *
     * @dataProvider testIsRefundableDataProvider
     */
    public function testIsRefundable($blExpected, $sStatus)
    {
        $this->SUT->setStatus($sStatus);
        $this->assertSame($blExpected, $this->SUT->isRefundable());
    }

    /**
     * @return array array(array($blExpected, $sStatus), ...)
     */
    public function testIsRefundableDataProvider()
    {
        return array(
            array(false, 'test-pa-any-status'),
            array(true, 'Completed'),
            array(false, 'Complete'),
            array(false, ''),
        );
    }

    /**
     * test setter and getter for order id
     */
    public function testSetGetOrderId()
    {
        $sOrderId = 'test_order_id_' . rand(100, 999);
        $this->SUT->setOrderId($sOrderId);

        $this->assertEquals($sOrderId, $this->SUT->getOrderId());
    }

    /**
     * test setter and getter for status
     */
    public function testSetGetStatus()
    {
        $sStatus = 'test_status_' . rand(100, 999);
        $this->SUT->setStatus($sStatus);

        $this->assertEquals($sStatus, $this->SUT->getStatus());
    }

    /**
     * test setter and getter for response - text
     */
    public function testGetResponse_returnsInstanceofExpectedType()
    {
        $sOxid = $sOrderId = $sTransanctionId = md5(time() . rand(), false);

        $this->SUT->setId($sOxid);
        $this->SUT->setOrderId($sOrderId);
        $this->SUT->setTransactionId($sTransanctionId);
        $oExpectedResponse = new PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType();
        $this->SUT->setResponse($oExpectedResponse);
        $this->SUT->save();

        /** get a fresh instance of paypInstallmentsPaymentData */
        $SUT = $this->getMock('paypInstallmentsPaymentData', array('__call'));
        $SUT->load($sOxid);
        $oActualResponse = $SUT->getResponse();
        $SUT->delete($sOxid);

        $this->assertInstanceOf('PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType', $oActualResponse);
    }

    /**
     * test getter for date created
     */
    public function testGetDateCreated_returnsExpectedDate()
    {
        $sOxid = $sOrderId = $sTransanctionId = md5(time() . rand(), false);

        $this->SUT->setId($sOxid);
        $this->SUT->setOrderId($sOrderId);
        $this->SUT->setTransactionId($sTransanctionId);
        /** The date is set by \paypInstallmentsPaymentData::_insert */
        $this->SUT->save();
        /** Store the actual time */
        $oExpectedDateCreated = new DateTime(date('Y-m-d H:i:s'));
        /**
         * Load the saved record and compare DateCreated with actual time.
         * As DateCreated has an accuracy of seconds, there is a chance that the tow timestamps differ by one second
         * So we test the time difference rather than the equality
         */
        /** get a fresh instance of paypInstallmentsPaymentData */
        $SUT = $this->getMock('paypInstallmentsPaymentData', array('__call'));
        $SUT->load($sOxid);
        $oActualDateCreated = new DateTime($SUT->getDateCreated());
        $SUT->delete($sOxid);
        $oDateInterval = $oActualDateCreated->diff($oExpectedDateCreated);
        $iTimeDifferenceInSeconds = $oDateInterval->format('%s');

        $this->assertLessThanOrEqual(1, $iTimeDifferenceInSeconds);
    }

    /**
     * test function to load payment data object by order id
     */
    public function testLoadByOrderId()
    {
        $sOxid = $sOrderId = $sTransactionId = md5(time() . rand(), false);
        $sStatus = 'Completed';
        $fFinancingFeeAmount = 100.00;
        $sFinancingFeeCurrency = 'EUR';
        $fFinancingTotalCostAmount = 1100.00;
        $sFinancingTotalCostCurrency = 'EUR';
        $fFinancingMonthlyPaymentAmount = 100.00;
        $sFinancingMonthlyPaymentCurrency = 'EUR';
        $iFinancingTerm = 10;
        $oResponse = new PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType();
        $sDateTimeCreated = date('Y-m-d H:i:s');

        $sInsert = "
            INSERT INTO `paypinstallmentspayments`
            (`OXID`,
            `OXORDERID`,
            `TRANSACTIONID`,
            `STATUS`,
            `FINANCINGFEEAMOUNT`,
            `FINANCINGFEECURRENCY`,
            `FINANCINGTOTALCOSTAMOUNT`,
            `FINANCINGTOTALCOSTCURRENCY`,
            `FINANCINGMONTHLYPAYMENTAMOUNT`,
            `FINANCINGMONTHLYPAYMENTCURRENCY`,
            `FINANCINGTERM`,
            `RESPONSE`,
            `DATETIME_CREATED`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

        ";
        oxDb::getDb()->execute(
            $sInsert,
            array(
                $sOxid,
                $sOrderId,
                $sTransactionId,
                $sStatus,
                $fFinancingFeeAmount,
                $sFinancingFeeCurrency,
                $fFinancingTotalCostAmount,
                $sFinancingTotalCostCurrency,
                $fFinancingMonthlyPaymentAmount,
                $sFinancingMonthlyPaymentCurrency,
                $iFinancingTerm,
                serialize($oResponse),
                $sDateTimeCreated,
            )
        );

        $this->SUT->loadByOrderId($sOrderId);

        $this->assertEquals($sOrderId, $this->SUT->getOrderId());
        $this->assertEquals($sStatus, $this->SUT->getStatus());
        $this->assertEquals($sTransactionId, $this->SUT->getTransactionId());
        $this->assertEquals($sFinancingFeeCurrency, $this->SUT->getCurrencyCode());
        $this->assertEquals($fFinancingFeeAmount, $this->SUT->getFinancingFeeAmount());
        $this->assertEquals($sFinancingFeeCurrency, $this->SUT->getFinancingFeeCurrency());
        $this->assertEquals($fFinancingTotalCostAmount, $this->SUT->getFinancingTotalCostAmount());
        $this->assertEquals($sFinancingTotalCostCurrency, $this->SUT->getFinancingTotalCostCurrency());
        $this->assertEquals($fFinancingMonthlyPaymentAmount, $this->SUT->getFinancingMonthlyPaymentAmount());
        $this->assertEquals($sFinancingMonthlyPaymentCurrency, $this->SUT->getFinancingMonthlyPaymentCurrency());
        $this->assertEquals($iFinancingTerm, $this->SUT->getFinancingTerm());
        $this->assertInstanceOf('PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType', $this->SUT->getResponse());
        $this->assertEquals($sDateTimeCreated, $this->SUT->getDateCreated());
    }

    public function testSetters_GettersReturnExpectedValues()
    {
        $sOxid = $sOrderId = $sTransactionId = md5(time() . rand(), false);
        $sStatus = 'Completed';
        $fFinancingFeeAmount = 100.00;
        $sFinancingFeeCurrency = 'EUR';
        $fFinancingTotalCostAmount = 1100.00;
        $sFinancingTotalCostCurrency = 'EUR';
        $fFinancingMonthlyPaymentAmount = 100.00;
        $sFinancingMonthlyPaymentCurrency = 'EUR';
        $iFinancingTerm = 10;
        $oResponse = new PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType();
        $oExpectedDateTimeCreated = new DateTime();

        $this->SUT->setId($sOxid);
        $this->SUT->setOrderId($sOrderId);
        $this->SUT->setStatus($sStatus);
        $this->SUT->setResponse($oResponse);
        $this->SUT->setTransactionId($sTransactionId);
        $this->SUT->setFinancingFeeAmount($fFinancingFeeAmount);
        $this->SUT->setFinancingFeeCurrency($sFinancingFeeCurrency);
        $this->SUT->setFinancingTotalCostAmount($fFinancingTotalCostAmount);
        $this->SUT->setFinancingTotalCostCurrency($sFinancingTotalCostCurrency);
        $this->SUT->setFinancingMonthlyPaymentAmount($fFinancingMonthlyPaymentAmount);
        $this->SUT->setFinancingMonthlyPaymentCurrency($sFinancingMonthlyPaymentCurrency);
        $this->SUT->setFinancingTerm($iFinancingTerm);

        $this->SUT->save();

        /** get a fresh instance of paypInstallmentsPaymentData */
        $SUT = $this->getMock('paypInstallmentsPaymentData', array('__call'));
        $SUT->load($sOxid);

        $this->assertEquals($sOrderId, $SUT->getOrderId());
        $this->assertEquals($sStatus, $SUT->getStatus());
        $this->assertEquals($sTransactionId, $SUT->getTransactionId());
        $this->assertEquals($sFinancingFeeCurrency, $SUT->getCurrencyCode());
        $this->assertEquals($fFinancingFeeAmount, $SUT->getFinancingFeeAmount());
        $this->assertEquals($sFinancingFeeCurrency, $SUT->getFinancingFeeCurrency());
        $this->assertEquals($fFinancingTotalCostAmount, $SUT->getFinancingTotalCostAmount());
        $this->assertEquals($sFinancingTotalCostCurrency, $SUT->getFinancingTotalCostCurrency());
        $this->assertEquals($fFinancingMonthlyPaymentAmount, $SUT->getFinancingMonthlyPaymentAmount());
        $this->assertEquals($sFinancingMonthlyPaymentCurrency, $SUT->getFinancingMonthlyPaymentCurrency());
        $this->assertEquals($iFinancingTerm, $SUT->getFinancingTerm());
        $this->assertInstanceOf('PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType', $SUT->getResponse());
        $oActualCreateDate = DateTime::createFromFormat('Y-m-d H:i:s', $SUT->getDateCreated());
        $this->assertLessThanOrEqual(
            1,
            abs($oActualCreateDate->format('U') - $oExpectedDateTimeCreated->format('U'))
        );
    }

    /**
     * test function to get payments data by order id that is not in DB
     */
    public function testLoadByOrderId_notPresent()
    {
        $sOrderId = 'test_orderid_' . rand(100, 999);

        $this->SUT->loadByOrderId($sOrderId);

        $this->assertNull($this->SUT->getId());
    }
}
