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
 * Class paypInstallmentsOxEmail
 *
 * @desc Extended methods add installment info to user and owner emails.
 */
class paypInstallmentsOxEmail extends paypInstallmentsOxEmail_parent
{

    /**
     * Sets mailer additional settings and sends ordering mail to user.
     * Returns true on success.
     *
     * @param oxOrder $oOrder   Order object
     * @param string  $sSubject user defined subject [optional]
     *
     * @return bool
     */
    public function sendOrderEmailToUser($oOrder, $sSubject = null)
    {
        if ($this->_isPaymentPayPalInstallments($oOrder)) {
            $this->setViewData(
                "oFinancingDetails",
                $this->_paypInstallments_getFinancingDetailsFromSession()
            );
        }

        return $this->_paypInstallments_callParent_sendOrderEmailToUser(
            $oOrder,
            $sSubject
        );
    }

    /**
     * Sets mailer additional settings and sends ordering mail to shop owner.
     * Returns true on success.
     *
     * @param oxOrder $oOrder   Order object
     * @param string  $sSubject user defined subject [optional]
     *
     * @return bool
     */
    public function sendOrderEmailToOwner($oOrder, $sSubject = null)
    {
        if ($this->_isPaymentPayPalInstallments($oOrder)) {
            $this->setViewData(
                "oFinancingDetails",
                $this->_paypInstallments_getFinancingDetailsFromSession()
            );
        }

        return $this->_paypInstallments_callParent_sendOrderEmailToOwner(
            $oOrder,
            $sSubject
        );
    }

    /**
     * Helper method. Test payment is paypalinstallments.
     *
     * @return bool
     */
    protected function _isPaymentPayPalInstallments(oxOrder $oOrder)
    {
        $sOrderPaymentId = $this->_paypInstallments_getPaymentIdFromOrder($oOrder);
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        return $sOrderPaymentId == $sPayPalInstallmentsPaymentId;
    }

    /**
     * Test helper calls parent method.
     *
     * @param $oOrder
     * @param $sSubject
     *
     * @codeCoverageIgnore Calls parent method.
     *
     * @return bool
     */
    protected function _paypInstallments_callParent_sendOrderEmailToUser($oOrder, $sSubject)
    {
        return parent::sendOrderEmailToUser($oOrder, $sSubject);
    }

    /**
     * Test helper calls parent method.
     *
     * @param $oOrder
     * @param $sSubject
     *
     * @codeCoverageIgnore Calls parent method.
     *
     * @return bool
     */
    protected function _paypInstallments_callParent_sendOrderEmailToOwner($oOrder, $sSubject)
    {
        return parent::sendOrderEmailToOwner($oOrder, $sSubject);
    }

    /**
     * Template getter.
     * Returns the details of the financing agreement.
     *
     * @return null|paypInstallmentsFinancingDetails
     */
    protected function _paypInstallments_getFinancingDetailsFromSession()
    {
        return oxRegistry::getSession()->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey);
    }

    /**
     * @param oxOrder $oOrder
     *
     * @return mixed
     */
    protected function _paypInstallments_getPaymentIdFromOrder(oxOrder $oOrder)
    {
        return $oOrder->getPayment()->getFieldData('oxpaymentsid');
    }
}
