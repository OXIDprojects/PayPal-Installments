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
 * Class paypInstallmentsRefundTest
 * Tests for paypInstallmentsRefund model.
 *
 * @see paypInstallmentsRefund
 */
class paypInstallmentsRefundTest extends OxidTestCase
{
    /**
     * Subject under the test.
     *
     * @var paypInstallmentsRefund
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

        $this->SUT = $this->getMock('paypInstallmentsRefund', array('__call'));
    }

    /**
     * test functionality for set and get transaction id
     * both should be use same var
     */
    public function testSetGetTransactionId()
    {
        $sTransactionId = 'test_transactionid_'.rand(100,999);
        $this->SUT->setTransactionId($sTransactionId);

        $this->assertEquals($sTransactionId, $this->SUT->getTransactionId());
    }

    /**
     * test functionality for set and get refund id
     * both should be use same var
     */
    public function testSetGetRefundId()
    {
        $sRefundId = 'test_refundid_'.rand(100,999);
        $this->SUT->setRefundId($sRefundId);

        $this->assertEquals($sRefundId, $this->SUT->getRefundId());
    }

    /**
     * test functionality for set and get memo
     * both should be use same var
     */
    public function testSetGetMemo()
    {
        $sMemo = 'test_memo_'.rand(100,999);
        $this->SUT->setMemo($sMemo);

        $this->assertEquals($sMemo, $this->SUT->getMemo());
    }

    /**
     * test functionality for set and get amount
     * both should be use same var
     */
    public function testSetGetAmount()
    {
        $fAmount = rand(0, 999) / 100;
        $this->SUT->setAmount($fAmount);

        $this->assertEquals($fAmount, $this->SUT->getAmount());
    }

    /**
     * test functionality for set and get currency
     * both should be use same var
     */
    public function testSetGetCurrency()
    {
        $sCurrency = 'EUR';
        $this->SUT->setCurrency($sCurrency);

        $this->assertEquals($sCurrency, $this->SUT->getCurrency());
    }

    /**
     * test functionality for set and get status
     * both should be use same var
     */
    public function testSetGetStatus()
    {
        $sStatus = 'test_status_'.rand(100,999);
        $this->SUT->setStatus($sStatus);

        $this->assertEquals($sStatus, $this->SUT->getStatus());
    }

    /**
     * test functionality for set and get response
     * both should be use same var
     */
    public function testSetGetResponse()
    {
        $oResponse = new StdClass();
        $oResponse->test_object = new StdClass();
        $oResponse->test_object->value = 'test_object->value';
        $oResponse->test_object2 = 'test_object2';

        $this->SUT->setResponse($oResponse);

        $this->assertSame(serialize($oResponse), serialize($this->SUT->getResponse()));
    }

    /**
     * test functionality for set and get date created
     * both should be use same var
     */
    public function testSetGetDateCreated()
    {
        $sDateTimeCreated = date('Y-m-d h:i:s');
        $this->SUT->setDateCreated($sDateTimeCreated);

        $this->assertEquals($sDateTimeCreated, $this->SUT->getDateCreated());
    }

    /**
     * test functionality of loading refund object by refund id
     * refund id is present in DB
     */
    public function testLoadByRefundIdPresent()
    {
        $sOxid = 'test_oxid_'.rand(100,999);
        $sTransactionId = 'test_transaction_'.rand(100,999);
        $sRefundId = 'test_refundid_'.rand(100,999);
        $sMemo = 'test_memo_'.rand(100,999);
        $fAmount = rand(0, 999) / 100;
        $sCurrency = 'EUR';
        $sStatus = 'test_status_'.rand(100,999);

        $oResponse = new StdClass();
        $oResponse->test_object = new StdClass();
        $oResponse->test_object->value = 'test_object->value';
        $oResponse->test_object2 = 'test_object2';

        $sDateTimeCreated = date('Y-m-d h:i:s');

        $sInsert = "
            INSERT INTO `paypinstallmentsrefunds`
            (OXID, TRANSACTIONID, REFUNDID, MEMO, AMOUNT, CURRENCY, STATUS, RESPONSE, DATETIME_CREATED)
            VALUES ('{$sOxid}', '{$sTransactionId}', '{$sRefundId}', '{$sMemo}', '{$fAmount}', '{$sCurrency}', '{$sStatus}', '".serialize($oResponse)."', '{$sDateTimeCreated}')
        ";
        oxDb::getDb()->Execute($sInsert);

        $this->SUT->loadByRefundId($sRefundId);

        $this->assertEquals($sOxid, $this->SUT->getId());
        $this->assertEquals($sTransactionId, $this->SUT->getTransactionId());
        $this->assertEquals($sRefundId, $this->SUT->getRefundId());
        $this->assertEquals($sMemo, $this->SUT->getMemo());
        $this->assertEquals($fAmount, $this->SUT->getAmount());
        $this->assertEquals($sCurrency, $this->SUT->getCurrency());
        $this->assertEquals($sStatus, $this->SUT->getStatus());
        $this->assertEquals(serialize($oResponse), serialize($this->SUT->getResponse()));
        $this->assertEquals($sDateTimeCreated, $this->SUT->getDateCreated());
    }

    /**
     * test if no object will be initiated if refund id is not present
     */
    public function testLoadByRefundIdNotPresent()
    {
        $sRefundId = 'test_refundid_'.rand(1000,2000);
        $this->SUT->loadByRefundId($sRefundId);

        $this->assertNull($this->SUT->getId());
    }
}