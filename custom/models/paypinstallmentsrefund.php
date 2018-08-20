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
 * Class paypInstallmentsRefund
 * Data model to persist refund data received by PayPal in the database.
 */
class paypInstallmentsRefund extends oxBase
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('paypinstallmentsrefunds');
    }

    /**
     * Set PayPal installments transaction ID
     *
     * @param string $sTransactionId
     */
    public function setTransactionId($sTransactionId)
    {
        $this->paypinstallmentsrefunds__transactionid = new oxField($sTransactionId);
    }

    /**
     * Get PayPal installments transaction ID
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->paypinstallmentsrefunds__transactionid->value;
    }

    /**
     * Set PayPal installments refund ID
     * 
     * EXPECTED DB FIELD: PAREFUNDID
     *
     * @param string $sRefundId
     */
    public function setRefundId($sRefundId)
    {
        $this->paypinstallmentsrefunds__refundid = new oxField($sRefundId);
    }

    /**
     * Get PayPal installments refund ID
     *
     * @return string
     */
    public function getRefundId()
    {
        return $this->paypinstallmentsrefunds__refundid->value;
    }

    /**
     * Set PayPal installments memo
     *
     * @param string $sMemo
     */
    public function setMemo($sMemo)
    {
        $this->paypinstallmentsrefunds__memo = new oxField($sMemo);
    }

    /**
     * Get PayPal installments memo
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->paypinstallmentsrefunds__memo->value;
    }

    /**
     * Set PayPal installments (refunded) amount
     *
     * @param double $dAmount
     */
    public function setAmount($dAmount)
    {
        $this->paypinstallmentsrefunds__amount = new oxField((double) $dAmount);
    }

    /**
     * Get PayPal installments (refunded) amount
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->paypinstallmentsrefunds__amount->value;
    }

    /**
     * Set PayPal installments refund currency
     *
     * @param string $sCurrency
     */
    public function setCurrency($sCurrency)
    {
        $this->paypinstallmentsrefunds__currency = new oxField($sCurrency);
    }

    /**
     * Get PayPal installments refund currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->paypinstallmentsrefunds__currency->value;
    }

    /**
     * Set PayPal installments status
     *
     * @param string $sStatus
     */
    public function setStatus($sStatus)
    {
        $this->paypinstallmentsrefunds__status = new oxField($sStatus);
    }

    /**
     * Get PayPal installments status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->paypinstallmentsrefunds__status->value;
    }

    /**
     * Set PayPal Installments response object serialized.
     *
     * @param object $oResponse
     */
    public function setResponse($oResponse)
    {
        $this->paypinstallmentsrefunds__response = new oxField(serialize($oResponse), oxField::T_RAW);
    }

    /**
     * Get PayPal Installments response object un-serialized.
     *
     * @return object
     */
    public function getResponse()
    {
        return unserialize(htmlspecialchars_decode($this->paypinstallmentsrefunds__response->value));
    }

    /**
     * Set PayPal installments date created
     *
     * @param string $sDateCreated
     */
    public function setDateCreated($sDateCreated)
    {
        $this->paypinstallmentsrefunds__datetime_created = new oxField($sDateCreated);
    }

    /**
     * Get PayPal installments date created
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->paypinstallmentsrefunds__datetime_created->value;
    }

    /**
     * Load an instance by refund ID.
     *
     * @param string $sRefundId
     *
     * @return bool
     */
    public function loadByRefundId($sRefundId)
    {
        $sSelect = sprintf(
            "SELECT * FROM `%s` WHERE `REFUNDID` = %s",
            $this->getCoreTableName(),
            oxDb::getDb()->quote($sRefundId)
        );

        return $this->assignRecord($sSelect);
    }
}