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
 * Class paypInstallmentsFormatter
 *
 * @desc Class responsible to format data to a single array for a template.
 */
class paypInstallmentsFormatter
{

    const KEY_STATUS = 'status';
    const KEY_TOTAL = 'total';
    const KEY_DATE = 'date';
    const KEY_ID = 'id';
    const KEY_TRANSACTION_ID = 'transactionId';
    const KEY_CURRENCY = 'currency';
    const KEY_LIST = 'list';
    const KEY_REFUNDABLE = 'refundable';

    /**
     * Payment formatter.
     *
     * @param paypInstallmentsPaymentData $oPayment
     *
     * @return array
     */
    public function formatPayment(paypInstallmentsPaymentData $oPayment)
    {
        return array(
            static::KEY_TRANSACTION_ID => $oPayment->getTransactionId(),
            static::KEY_STATUS         => $oPayment->getStatus(),
            static::KEY_REFUNDABLE     => $oPayment->isRefundable()

        );
    }

    /**
     * RefundList formatter.
     *
     * @param paypInstallmentsRefundList $oRefundList
     * @param oxOrder                    $oOrder
     *
     * @return array
     */
    public function formatRefundList(paypInstallmentsRefundList $oRefundList, oxOrder $oOrder)
    {
        $aFormattedRefundList = array();
        foreach ($oRefundList->getArray() as $oRefund ) {
            $aFormattedRefundList[] = $this->formatRefund($oRefund, $oOrder);
        }

        return array(
            static::KEY_TOTAL => $this->formatPrice($oRefundList->getRefundedSumByTransactionId(), $oOrder->getOrderCurrency()),
            static::KEY_LIST  => $aFormattedRefundList
        );
    }

    /**
     * Single refund formatter.
     *
     * @param paypInstallmentsRefund $oRefund
     * @param oxOrder                $oOrder
     *
     * @return array
     */
    public function formatRefund(paypInstallmentsRefund $oRefund, oxOrder $oOrder)
    {
        return array(
            static::KEY_DATE     => $oRefund->getDateCreated(),
            static::KEY_TOTAL    => $this->formatPrice($oRefund->getAmount(), $oOrder->getOrderCurrency()),
            static::KEY_CURRENCY => $oRefund->getCurrency(),
            static::KEY_STATUS   => $oRefund->getStatus()
        );
    }


    /**
     * Order formatter.
     *
     * @param oxOrder $oOrder
     *
     * @return array
     */
    public function formatOrder(oxOrder $oOrder)
    {
        return array(
            static::KEY_ID       => $oOrder->getId(),
            static::KEY_TOTAL    => $this->formatPrice($oOrder->getFieldData('OXTOTALORDERSUM'), $oOrder->getOrderCurrency()),
            static::KEY_CURRENCY => $oOrder->getOrderCurrency()->name,
        );
    }

    /**
     * Format price value.
     *
     * @param $mPrice
     * @param $oCurrency
     *
     * @return string
     */
    public function formatPrice($mPrice, $oCurrency)
    {
        $sFormattedCurrency = oxRegistry::getLang()->formatCurrency($mPrice, $oCurrency);

        return $sFormattedCurrency;
    }
}
