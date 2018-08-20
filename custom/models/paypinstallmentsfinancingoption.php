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
 * Class paypInstallmentsFinancingOption
 *
 * @desc Contains financing options data structure.
 */
class paypInstallmentsFinancingOption extends oxSuperCfg
{

    /** @var  int contract length in month */
    protected $_iNumMonthlyPayments;
    /** @var  float monthly payment */
    protected $_fMonthlyPayment;
    /** @var  float service fee */
    protected $_fFinancingFee;
    /** @var  float total price (includes service fee ) */
    protected $_fTotalPayment;
    /** @var  float min amount to qualify for service */
    protected $_fMinAmount;
    /** @var  string currency code */
    protected $_sCurrency;
    /** @var float Annual Percentage Rate */
    protected $_fAnnualPercentageRate;
    /** @var  float Nominal Rate */
    protected $_fNominalRate;
    /** @var  float original requested amount to be financed */
    protected $_fAmount;

    /**
     * Getter for numMonthlyPayments.
     *
     * @return int
     */
    public function getNumMonthlyPayments()
    {
        return $this->_iNumMonthlyPayments;
    }

    /**
     * Setter for numMonthlyPayments.
     *
     * @param int $iNumMonthlyPayments
     *
     * @return $this
     */
    public function setNumMonthlyPayments($iNumMonthlyPayments)
    {
        $this->_iNumMonthlyPayments = $iNumMonthlyPayments;

        return $this;
    }

    /**
     * Getter for MonthlyPayment.
     *
     * @return float
     */
    public function getMonthlyPayment()
    {
        return $this->_fMonthlyPayment;
    }

    /**
     * Setter for monthly payment.
     *
     * @param mixed $fMonthlyPayment
     *
     * @return $this
     */
    public function setMonthlyPayment($fMonthlyPayment)
    {
        $this->_fMonthlyPayment = $fMonthlyPayment;

        return $this;
    }

    /**
     * Getter for financing fee.
     *
     * @return float
     */
    public function getFinancingFee()
    {
        return $this->_fFinancingFee;
    }

    /**
     * Setter for financing fee.
     *
     * @param float $fFinancingFee
     *
     * @return $this
     */
    public function setFinancingFee($fFinancingFee)
    {
        $this->_fFinancingFee = $fFinancingFee;

        return $this;
    }

    /**
     * getter for total payment amount.
     *
     * @return float
     */
    public function getTotalPayment()
    {
        return $this->_fTotalPayment;
    }

    /**
     * Setter for total payment amount.
     *
     * @param float $fTotalPayment
     *
     * @return $this
     */
    public function setTotalPayment($fTotalPayment)
    {
        $this->_fTotalPayment = $fTotalPayment;

        return $this;
    }

    /**
     * MinAmount getter.
     *
     * @return float
     */
    public function getMinAmount()
    {
        return $this->_fMinAmount;
    }

    /**
     * MinAmount setter. Method chain supported.
     *
     * @param float $minAmount
     *
     * @return $this
     */
    public function setMinAmount($minAmount)
    {
        $this->_fMinAmount = $minAmount;

        return $this;
    }

    /**
     * Currency getter.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_sCurrency;
    }

    /**
     * Currency setter. Method chain supported.
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->_sCurrency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnnualPercentageRate()
    {
        return str_replace('.',',',oxRegistry::getUtils()->fRound($this->_fAnnualPercentageRate));
    }

    /**
     * @param mixed $fAnnualPercentageRate
     */
    public function setAnnualPercentageRate($fAnnualPercentageRate)
    {
        $this->_fAnnualPercentageRate = $fAnnualPercentageRate;
    }

    /**
     * @return mixed
     */
    public function getNominalRate()
    {
        return str_replace('.',',',oxRegistry::getUtils()->fRound($this->_fNominalRate));
    }

    /**
     * @param mixed $fNominalRate
     */
    public function setNominalRate($fNominalRate)
    {
        $this->_fNominalRate = $fNominalRate;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->_fAmount;
    }

    /**
     * @param float $fAmount
     */
    public function setAmount($fAmount)
    {
        $this->_fAmount = $fAmount;
    }
}
