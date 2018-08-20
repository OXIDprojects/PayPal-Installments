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
class paypInstallmentsPaymentData extends oxBase
{

    protected $TransactionId;
    protected $FinancingFeeAmount;
    protected $FinancingTotalCostAmount;
    protected $FinancingMonthlyPaymentAmount;
    protected $FinancingFeeCurrency;
    protected $FinancingTotalCostCurrency;
    protected $FinancingMonthlyPaymentCurrency;
    protected $FinancingTerm;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('paypinstallmentspayments');
    }

    /**
     * Check if the payment can be refunded.
     *
     * @return bool
     */
    public function isRefundable()
    {
        return $this->getStatus() === oxNew('paypInstallmentsConfiguration')->getRefundablePaymentStatus();
    }

    /**
     * Set PayPal installments payment oxorderid
     *
     * @param string $sOrderId
     */
    public function setOrderId($sOrderId)
    {
        $this->paypinstallmentspayments__oxorderid = new oxField($sOrderId);
    }

    /**
     * Get PayPal installments payment oxorderid
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->paypinstallmentspayments__oxorderid->value;
    }

    /**
     * Set PayPal installments payment status
     *
     * @param string $sStatus
     */
    public function setStatus($sStatus)
    {
        $this->paypinstallmentspayments__status = new oxField($sStatus);
    }

    /**
     * Get PayPal installments payment status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->paypinstallmentspayments__status->value;
    }

    /**
     * Set PayPal Installments payment response object serialized.
     *
     * @param \PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType $oResponse
     */
    public function setResponse(\PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType $oResponse)
    {
        $this->paypinstallmentspayments__response = new oxField(serialize($oResponse), oxField::T_RAW);
    }

    /**
     * Get PayPal Installments payment response object.
     *
     * @return \PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType
     */
    public function getResponse()
    {
        return unserialize($this->paypinstallmentspayments__response->getRawValue());
    }

    /**
     * Get PayPal installments payment date created
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->paypinstallmentspayments__datetime_created->value;
    }

    /**
     * Set PayPal installments transaction ID
     *
     * @param string $sTransactionId
     */
    public function setTransactionId($sTransactionId)
    {
        $this->paypinstallmentspayments__transactionid = new oxField($sTransactionId);
    }

    /**
     * TransactionId getter.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return (string) $this->getFieldData('transactionid');
    }

    /**
     * Currency code getter.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return (string) $this->getFieldData('financingfeecurrency');
    }

    /**
     * Return the financing fee amount
     *
     * @return float
     */
    public function getFinancingFeeAmount()
    {
        return (float) $this->paypinstallmentspayments__financingfeeamount->value;
    }

    /**
     * Set the financing fee amount
     *
     * @param mixed float $fFinancingFeeAmount
     */
    public function setFinancingFeeAmount($fFinancingFeeAmount)
    {
        /** cast to float */
        $fFinancingFeeAmount = (float) $fFinancingFeeAmount;

        $this->paypinstallmentspayments__financingfeeamount = new oxField($fFinancingFeeAmount);
    }

    /**
     * Get the financing fee currency
     *
     * @return string
     */
    public function getFinancingFeeCurrency()
    {
        return $this->paypinstallmentspayments__financingfeecurrency->value;
    }

    /**
     * Set the financing fee currency
     *
     * @param string $sFinancingFeeCurrency
     */
    public function setFinancingFeeCurrency($sFinancingFeeCurrency)
    {
        $this->paypinstallmentspayments__financingfeecurrency = new oxField($sFinancingFeeCurrency);
    }

    /**
     * Get the financing total costs amount
     *
     * @return float
     */
    public function getFinancingTotalCostAmount()
    {
        return (float) $this->paypinstallmentspayments__financingtotalcostamount->value;
    }

    /**
     * Set the financing total costs amount
     *
     * @param float $fFinancingTotalCostAmount
     */
    public function setFinancingTotalCostAmount($fFinancingTotalCostAmount)
    {
        /** cast to float */
        $fFinancingTotalCostAmount = (float) $fFinancingTotalCostAmount;

        $this->paypinstallmentspayments__financingtotalcostamount = new oxField($fFinancingTotalCostAmount);
    }

    /**
     * Get the financing total costs currency
     *
     * @return string
     */
    public function getFinancingTotalCostCurrency()
    {
        return $this->paypinstallmentspayments__financingtotalcostcurrency->value;
    }

    /**
     * Set the financing total costs currency
     *
     * @param string $sFinancingTotalCostCurrency
     */
    public function setFinancingTotalCostCurrency($sFinancingTotalCostCurrency)
    {
        $this->paypinstallmentspayments__financingtotalcostcurrency = new oxField($sFinancingTotalCostCurrency);
    }

    /**
     * Get the financing monthly payment amount
     *
     * @return float
     */
    public function getFinancingMonthlyPaymentAmount()
    {
        return (float) $this->paypinstallmentspayments__financingmonthlypaymentamount->value;
    }

    /**
     * Set the financing monthly payment amount
     *
     * @param float $fFinancingMonthlyPaymentAmount
     */
    public function setFinancingMonthlyPaymentAmount($fFinancingMonthlyPaymentAmount)
    {
        $fFinancingMonthlyPaymentAmount = (float) $fFinancingMonthlyPaymentAmount;

        $this->paypinstallmentspayments__financingmonthlypaymentamount = new oxField($fFinancingMonthlyPaymentAmount);
    }

    /**
     * Get the financing monthly payment currency
     *
     * @return string
     */
    public function getFinancingMonthlyPaymentCurrency()
    {
        return $this->paypinstallmentspayments__financingmonthlypaymentcurrency->value;
    }

    /**
     * Set the financing monthly payment currency
     *
     * @param string $sFinancingMonthlyPaymentCurrency
     */
    public function setFinancingMonthlyPaymentCurrency($sFinancingMonthlyPaymentCurrency)
    {
        $this->paypinstallmentspayments__financingmonthlypaymentcurrency = new oxField($sFinancingMonthlyPaymentCurrency);
    }

    /**
     * Get the financing term. I.e. the number of months a user has to pay the monthly fees
     *
     * @return int
     */
    public function getFinancingTerm()
    {
        return (int) $this->paypinstallmentspayments__financingterm->value;
    }

    /**
     * Get the financing term. I.e. the number of months a user has to pay the monthly fees
     *
     * @param int $iFinancingTerm
     */
    public function setFinancingTerm($iFinancingTerm)
    {
        $this->paypinstallmentspayments__financingterm = new oxField($iFinancingTerm);
    }

    /**
     * Load an instance by oxorder ID
     *
     * @return bool
     */
    public function loadByOrderId($sOrderId)
    {
        $sSelect = sprintf(
            "SELECT * FROM `%s` WHERE `OXORDERID` = %s",
            $this->getCoreTableName(),
            oxDb::getDb()->quote($sOrderId)
        );

        return $this->assignRecord($sSelect);
    }

    /**
     * Sets paymentdata creation date
     * (paypinstallmentspayments__datetime_created). Then executes parent method
     * parent::_insert() and returns insertion status.
     *
     * @return bool
     */
    protected function _insert()
    {
        $iInsertTime = time();
        $sNow = date('Y-m-d H:i:s', $iInsertTime);
        $this->paypinstallmentspayments__datetime_created = new oxField($sNow);

        return parent::_insert();
    }
}
