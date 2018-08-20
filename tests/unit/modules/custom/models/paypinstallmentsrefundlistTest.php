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
 * Class paypInstallmentsRefundListTest
 * Tests for paypInstallmentsRefundListTest model.
 *
 * @see paypInstallmentsRefundListTest
 */
class paypInstallmentsRefundListTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var paypInstallmentsRefundList
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

        $this->SUT = $this->getMock('paypInstallmentsRefundList', array('__call'), array(null));
    }

    protected function _createDummyData($aForceData = array())
    {
        $oResponse = new StdClass();
        $oResponse->test_object = new StdClass();
        $oResponse->test_object->value = 'test_object->value';
        $oResponse->test_object2 = 'test_object2';

        $aData = array(
            'sOxid'            => 'test_oxid_' . rand(100, 999),
            'sTransactionId'   => 'test_transaction_' . rand(100, 999),
            'sRefundId'        => 'test_refundid_' . rand(100, 999),
            'sMemo'            => 'test_memo_' . rand(100, 999),
            'fAmount'          => rand(0, 999) / 100,
            'sCurrency'        => 'EUR',
            'sStatus'          => 'test_status_' . rand(100, 999),
            'oResponse'        => clone $oResponse,
            'sDateTimeCreated' => date('Y-m-d h:i:s'),
        );

        $aData = array_merge($aData, $aForceData);

        return $aData;
    }

    /**
     * test functionality of loading refund objects by transaction id
     * refund id is present in DB
     */
    public function testLoadRefundsByTransactionId()
    {
        $x = 3;

        //create $x refunds
        $aForceData = array('sTransactionId' => 'test_transaction_' . rand(100, 999));

        for ($i = 0; $i < $x; $i++) {
            //keep sure every entry is added later then entry before
            $aForceData['sDateTimeCreated'] = date('Y-m-d h:i:s', mktime(0, 0, 0, 1, ($i + 1), 2015));
            $aData = $this->_createDummyData($aForceData);

            //make sure we do not have test date on DB
            oxDb::getDb()->Execute("DELETE FROM `paypinstallmentsrefunds` WHERE OXID ='{$aData['sOxid']}' ");

            $sInsert = "
                INSERT INTO `paypinstallmentsrefunds`
                (OXID, TRANSACTIONID, REFUNDID, MEMO, AMOUNT, CURRENCY, STATUS, RESPONSE, DATETIME_CREATED)
                VALUES ('{$aData['sOxid']}', '{$aData['sTransactionId']}', '{$aData['sRefundId']}', '{$aData['sMemo']}', '{$aData['fAmount']}', '{$aData['sCurrency']}', '{$aData['sStatus']}', '" . serialize($aData['oResponse']) . "', '{$aData['sDateTimeCreated']}')
            ";
            oxDb::getDb()->Execute($sInsert);

            $aOxids[] = $aData['sOxid'];
        }

        $this->SUT->setTransactionId($aForceData['sTransactionId'])->loadRefundsByTransactionId();

        //check if there are $x items in list
        $this->assertEquals($x, count($this->SUT));

        //check if sorting is correct
        $aRefundsDateSortList = $this->SUT->getArray();
        $this->assertEquals(0, count(array_diff_assoc($aOxids, array_keys($aRefundsDateSortList))));

        //check if objects are the same
        $oRefundModel = $this->getMock('paypInstallmentsRefund', array('__call'));
        for ($i = 0; $i < $x; $i++) {
            $oRefundModel->load($aOxids[$i]);

            if ($oRefundModel->getId()) {
                //get correct object from list
                $oRefundLisObject = $this->SUT->offsetGet($aOxids[$i]);

                $this->assertEquals($oRefundModel->getId(), $oRefundLisObject->getId());
                $this->assertEquals($oRefundModel->getTransactionId(), $oRefundLisObject->getTransactionId());
                $this->assertEquals($oRefundModel->getRefundId(), $oRefundLisObject->getRefundId());
                $this->assertEquals($oRefundModel->getMemo(), $oRefundLisObject->getMemo());
                $this->assertEquals($oRefundModel->getAmount(), $oRefundLisObject->getAmount());
                $this->assertEquals($oRefundModel->getCurrency(), $oRefundLisObject->getCurrency());
                $this->assertEquals($oRefundModel->getStatus(), $oRefundLisObject->getStatus());
                $this->assertEquals(serialize($oRefundModel->getResponse()), serialize($oRefundLisObject->getResponse()));
                $this->assertEquals($oRefundModel->getDateCreated(), $oRefundLisObject->getDateCreated());
            }
        }
    }

    /**
     * test if no list object will be initiated if transaction id is not present
     */
    public function testLoadRefundsByTransactionIdNotPresent()
    {
        $sTransactionId = 'test_transaction_' . rand(1000, 2000);
        $this->SUT->setTransactionId($sTransactionId)->loadRefundsByTransactionId();

        $this->assertEquals(0, count($this->SUT));
    }

    /**
     * test if sum of refund amount per transaction is correct
     */
    public function testGetRefundedSumByTransactionId()
    {
        $x = 3;
        $fRefundsAmountInsert = 0;

        //create $x refunds
        $aForceData = array('sTransactionId' => 'test_transaction_' . rand(100, 999));

        for ($i = 0; $i < $x; $i++) {
            $aForceData['fAmount'] = 5;
            $aData = $this->_createDummyData($aForceData);


            //make sure we do not have test date on DB
            oxDb::getDb()->Execute("DELETE FROM `paypinstallmentsrefunds` WHERE OXID ='{$aData['sOxid']}' ");

            $sInsert = "
                INSERT INTO `paypinstallmentsrefunds`
                (OXID, TRANSACTIONID, REFUNDID, MEMO, AMOUNT, CURRENCY, STATUS, RESPONSE, DATETIME_CREATED)
                VALUES ('{$aData['sOxid']}', '{$aData['sTransactionId']}', '{$aData['sRefundId']}', '{$aData['sMemo']}', '{$aData['fAmount']}', '{$aData['sCurrency']}', '{$aData['sStatus']}', '" . serialize($aData['oResponse']) . "', '{$aData['sDateTimeCreated']}')
            ";
            oxDb::getDb()->Execute($sInsert);

            $fRefundsAmountInsert = $fRefundsAmountInsert + $aData['fAmount'];
            $aOxid[] = $aData['sTransactionId'];
        }

        $fRefundsAmountObject = $this->SUT->setTransactionId($aForceData['sTransactionId'])->getRefundedSumByTransactionId();

        //check if sum is correct one
        $this->assertEquals($fRefundsAmountInsert, $fRefundsAmountObject);
    }

    /**
     * test if sum of refund amount is 0 if there is no transaction
     */
    public function testGetRefundedSumByTransactionIdNotPresent()
    {
        $sTransactionId = 'test_transaction_' . rand(1000, 2000);
        $dAmount = $this->SUT->setTransactionId($sTransactionId)->getRefundedSumByTransactionId();

        $this->assertSame((double) 0, $dAmount);
    }
}
