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
 * Class paypInstallmentsPresentmentValidator
 *
 * @desc Inspects Presentment is called with supported parameters. (DE, EUR, positive amount)
 */
class paypInstallmentsPresentmentValidator extends paypInstallmentsValidatorBase
{

    /**  @var paypInstallmentsPresentment */
    protected $_oPresentment;

    /**
     * Validator requires paypInstallmentsPresentment.
     *
     * @param paypInstallmentsPresentment $oPresentment
     */
    public function __construct(paypInstallmentsPresentment $oPresentment)
    {
        $this->setPresentment($oPresentment);
    }

    /**
     * Validate parameters of presentment widget.
     *
     * @return $this
     * @throws paypInstallmentsPresentmentValidationException
     */
    public function validate()
    {
        $this->_validateCurrency()
            ->_validateCountryCode()
            ->_validateAmount();

        return $this;
    }

    /**
     * Validate Currency.
     *
     * @return $this
     * @throws paypInstallmentsPresentmentValidationException
     */
    protected function _validateCurrency()
    {
        $this->getLogger()->info(
            "paypInstallmentsPresentmentValidator _validateCurrency",
            array(
                'currency' => $this->getPresentment()->getCurrency(),
            )
        );

        if ($this->getPresentment()->getCurrency()
            !== $this->getConfiguration()->getRequiredOrderTotalCurrency()
        ) {
            $this->getLogger()->info(
                "paypInstallmentsPresentmentValidator _validateCurrency ValidationFailure",
                array()
            );
            /** @var paypInstallmentsPresentmentValidationException $oEx */
            $oEx = oxNew(
                'paypInstallmentsPresentmentValidationException',
                paypInstallmentsConfiguration::getValidationErrorMessage('UNSUPPORTED_CURRENCY')
            );
            throw $oEx;
        }

        return $this;
    }

    /**
     * Validate Country code.
     *
     * @return $this
     * @throws paypInstallmentsPresentmentValidationException
     */
    protected function _validateCountryCode()
    {
        $this->getLogger()->info(
            "paypInstallmentsPresentmentValidator _validateCountryCode",
            array(
                'country' => $this->getPresentment()->getCountryCode(),
            )
        );
        if ($this->getPresentment()->getCountryCode()
            !== $this->getConfiguration()->getRequiredShippingCountry()
        ) {
            $this->getLogger()->info(
                "paypInstallmentsPresentmentValidator _validateCountryCode ValidationFailure",
                array()
            );

            /** @var paypInstallmentsPresentmentValidationException $oEx */
            $oEx = oxNew(
                'paypInstallmentsPresentmentValidationException',
                paypInstallmentsConfiguration::getValidationErrorMessage('UNSUPPORTED_COUNTRY')
            );
            throw $oEx;
        }

        return $this;
    }

    /**
     * Validate amount.
     *
     * @return $this
     * @throws paypInstallmentsPresentmentValidationException
     */
    protected function _validateAmount()
    {
        $this->getLogger()->info(
            "paypInstallmentsPresentmentValidator _validateAmount",
            array(
                'amount' => $this->getPresentment()->getAmount(),
            )
        );
        if ($this->getPresentment()->getAmount() <= 0) {
            $this->getLogger()->info(
                "paypInstallmentsPresentmentValidator _validateAmount ValidationFailure",
                array()
            );
            /** @var paypInstallmentsPresentmentValidationException $oEx */
            $oEx = oxNew(
                'paypInstallmentsPresentmentValidationException',
                paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_AMOUNT')
            );
            throw $oEx;
        }

        return $this;
    }

    /**
     * Presentment getter.
     *
     * @return paypInstallmentsPresentment
     */
    public function getPresentment()
    {
        return $this->_oPresentment;
    }

    /**
     * Presentment setter. Default value unSets attribute. Method chain supported.
     *
     * @param paypInstallmentsPresentment $presentment
     *
     * @return $this
     */
    public function setPresentment($presentment = null)
    {
        $this->_oPresentment = $presentment;

        return $this;
    }
}
