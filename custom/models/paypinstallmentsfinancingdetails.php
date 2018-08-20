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
 * Class paypInstallmentsFinancingDetails
 *
 * This class holds the PayPal Installments financing details in OXID template friendly way.
 * All prices are instances of oxPrice.
 */
class paypInstallmentsFinancingDetails
{

    /**
     * The currency as ISO 4217 code
     *
     * @var string
     */
    protected $sFinancingCurrency;

    /**
     * The amount of the financing fee.
     *
     * @var oxPrice
     */
    protected $oFinancingFeeAmount;

    /**
     * The total costs of the financing = total order amount + financing fee.
     *
     * @var  oxPrice
     */
    protected $oFinancingTotalCost;

    /**
     * The amount of the monthly payments.
     *
     * @var oxPrice
     */
    protected $oFinancingMonthlyPayment;

    /**
     * Financing term as number of scheduled payments
     *
     * @var int
     */
    protected $iFinancingTerm;

    /**
     * @return string Currency as ISO 4217 Code
     */
    public function getFinancingCurrency()
    {
        return $this->sFinancingCurrency;
    }

    /**
     * @param string $sCurrency as ISO 4217 Code
     */
    public function setFinancingCurrency($sCurrency)
    {
        $this->sFinancingCurrency = $sCurrency;
    }

    /**
     * @return oxPrice
     */
    public function getFinancingFeeAmount()
    {
        return is_null($this->oFinancingFeeAmount) ? $this->_getPriceObjectFromFloat(0.0) : $this->oFinancingFeeAmount;
    }

    /**
     * @param float $fAmount
     */
    public function setFinancingFeeAmount($fAmount)
    {
        $this->oFinancingFeeAmount = $this->_getPriceObjectFromFloat($fAmount);
    }

    /**
     * @return oxPrice
     */
    public function getFinancingTotalCost()
    {
        return is_null($this->oFinancingTotalCost) ? $this->_getPriceObjectFromFloat(0.0) : $this->oFinancingTotalCost;
    }

    /**
     * @param float $fAmount
     */
    public function setFinancingTotalCost($fAmount)
    {
        $this->oFinancingTotalCost = $this->_getPriceObjectFromFloat($fAmount);
    }

    /**
     * @return oxPrice
     */
    public function getFinancingMonthlyPayment()
    {
        return is_null($this->oFinancingMonthlyPayment) ? $this->_getPriceObjectFromFloat(0.0) : $this->oFinancingMonthlyPayment;
    }

    /**
     * @param float $fAmount
     */
    public function setFinancingMonthlyPayment($fAmount)
    {
        $this->oFinancingMonthlyPayment = $this->_getPriceObjectFromFloat($fAmount);
    }

    /**
     * @return int
     */
    public function getFinancingTerm()
    {
        return is_null($this->iFinancingTerm) ? 0 : $this->iFinancingTerm;
    }

    /**
     * @param int $iFinancingTerm
     */
    public function setFinancingTerm($iFinancingTerm)
    {
        $this->iFinancingTerm = $iFinancingTerm;
    }

    /**
     * @param float $fAmount
     *
     * @return oxPrice
     */
    protected function _getPriceObjectFromFloat($fAmount)
    {
        $oPrice = oxNew('oxPrice');
        $oPrice->setBruttoPriceMode();
        $oPrice->add($fAmount);

        return $oPrice;
    }
}