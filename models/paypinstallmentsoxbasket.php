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
class paypInstallmentsOxBasket extends paypInstallmentsOxBasket_parent
{

    /**
     * Get a fingerprint of the basket items.
     *
     * @return string
     */
    public function paypInstallments_GetBasketItemsFingerprint()
    {
        $sBasketItemsIdentifier = '';
        $aItems = $this->getContents();
        foreach ($aItems as $oBasketItem) {
            /** @var oxBasketItem $oBasketItem */
            $sBasketItemsIdentifier .= (string) $oBasketItem->getProductId() . '-' .
                                       (string) $oBasketItem->getAmount() . ';';
        }

        return md5($sBasketItemsIdentifier);
    }

    /**
     * @inheritdoc
     *
     * If _sTsProductId is not set, try to get it from session
     * This is probably a bug in oxBasket::getTsProductId()
     *
     * @see oxBasket::getTsProductId()
     *
     * @return string
     */
    public function getTsProductId()
    {
        $this->_sTsProductId = parent::getTsProductId();

        if (!$this->_sTsProductId) {
            $this->_sTsProductId = oxRegistry::getSession()->getVariable('stsprotection');
        }

        return $this->_sTsProductId;
    }

    /**
     * Template helper function to be accessed via
     * $oxcmp->paypInstallments_GetBasketGrandTotal() or $oxcmp->paypInstallments_GetBasketGrandTotal()->getPrice()
     *
     * @return oxPrice
     */
    public function paypInstallments_GetBasketGrandTotal()
    {
        /** @var oxPrice $fGetBasketGrandTotal */
        $oGetBasketGrandTotal = $this->getPrice();

        return $oGetBasketGrandTotal;
    }

    /* Compatibility methods (missing on older shops) - START */

    /**
     * @inheritdoc
     *
     * Overloaded parent method for newer shops (with no changes) - missing method in older shops.
     * Returns payment costs.
     *
     * @return oxPrice
     */
    public function getPaymentCost()
    {
        /** @var oxBasket $this */
        return $this->getCosts('oxpayment');
    }

    /**
     *
     * @inheritdoc
     *
     * Overloaded parent method for newer shops (with no changes) - missing method in older shops.
     * Gets total discount sum.
     *
     * @return float|int
     */
    public function getTotalDiscountSum()
    {
        /** @var oxBasket $this */

        $dPrice = 0;

        // subtracting total discount
        if ($oTotalDiscountPrice = $this->getTotalDiscount()) {
            $dPrice += $oTotalDiscountPrice->getPrice();
        }

        if ($oVoucherDiscountPrice = $this->getVoucherDiscount()) {
            $dPrice += $oVoucherDiscountPrice->getPrice();
        }

        return $dPrice;
    }
    /* Compatibility methods (missing on older shops) - END */
}