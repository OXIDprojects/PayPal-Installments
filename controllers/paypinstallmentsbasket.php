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
 * Class paypInstallmentsBasket
 *
 * @see basket
 *
 * If the payment method PayPal Installments was selected in a prior step, the were some additional costs added to the basket.
 * These costs have to be removed and the basket has to be recalculated.
 */
class paypInstallmentsBasket extends paypInstallmentsBasket_parent
{

    /**
     * @inheritdoc
     *
     * Whenever we enter the basket page the PayPal specific session registry will be deleted.
     * There is no way to recycle an earlier Express Checkout, we will always start a new one.
     *
     */
    public function init()
    {
        /** @var paypInstallmentsOxSession $oSession */
        $oSession = $this->getSession();
        /** Delete registry here */
        $oSession->paypInstallmentsDeletePayPalInstallmentsRegistry();

        /**
         * Calculate the basket, so that the financing fee gets NOT displayed and is NOT
         * included in the Total Products Gross on checkout step 4.
         *
         * @see \paypinstallments_oxbasket::_calcTotalPrice
         */
        $this->_paypInstallments_recalculateBasket();

        $this->_paypInstallments_CallInitParent();
    }

    /**
     * Call parent::init.
     * Needed for testing.
     *
     * @codeCoverageIgnore
     *
     * @return null|string
     */
    protected function _paypInstallments_CallInitParent()
    {
        parent::init();
    }

    /**
     * Recalculate the basket
     */
    protected function _paypInstallments_recalculateBasket()
    {
        /** @var paypInstallmentsOxSession $oSession */
        $oSession = $this->getSession();
        $oBasket = $oSession->getBasket();
        $oBasket->calculateBasket(true);
        $oSession->setBasket($oBasket);
    }
}