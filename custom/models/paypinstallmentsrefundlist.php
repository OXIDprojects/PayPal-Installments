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
 * Class paypInstallmentsRefundList
 * oxList object to get refunds e.g. all refunds for one transaction
 */
class paypInstallmentsRefundList extends oxList
{

    /** @var  string */
    protected $_sTransactionId;

    /**
     * Initialize the list model with base object and table names.
     *
     * @inheritDoc
     */
    public function __construct($sTransactionId)
    {
        parent::__construct();

        $this->init('paypInstallmentsRefund', 'paypinstallmentsrefunds');

        $this->setTransactionId($sTransactionId);
    }

    /**
     * Load PayPal Installments refund models by Transaction ID and orders them by creation date
     */
    public function loadRefundsByTransactionId()
    {
        $sSelect = sprintf(
            "SELECT * FROM `%s` WHERE `TRANSACTIONID` = %s ORDER BY `DATETIME_CREATED`",
            $this->getBaseObject()->getCoreTableName(),
            oxDb::getDb()->quote($this->getTransactionId())
        );

        $this->selectString($sSelect);

        return $this;
    }

    /**
     * Count and return a sum of all totals for refunds related to a given transaction ID.
     * In other words, it counts already refunded total amount for a payment.
     *
     * @return double
     */
    public function getRefundedSumByTransactionId()
    {
        $oDb = oxDb::getDb();

        $sQuery = sprintf(
            "SELECT SUM(`AMOUNT`) FROM `%s` WHERE `TRANSACTIONID` = %s",
            $this->getBaseObject()->getCoreTableName(),
            $oDb->quote($this->getTransactionId())
        );

        return (double) $oDb->getOne($sQuery);
    }

    /**
     * TransactionId getter.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_sTransactionId;
    }

    /**
     * TransactionId setter. Method chain supported.
     *
     * @param $sTransactionId
     *
     * @return $this
     */
    public function setTransactionId($sTransactionId)
    {
        $this->_sTransactionId = $sTransactionId;

        return $this;
    }
}
