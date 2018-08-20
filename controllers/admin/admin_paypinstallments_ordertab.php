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
 * Class Admin_paypInstallments_orderTab
 *
 * @desc Admin Order PayPal Plus tab controller.
 *
 * Collects and previews PayPal Plus payments data and controls.
 * the actions with them.
 *
 * Admin menu: Administer Orders -> Orders -> PayPal Plus
 */
class Admin_paypInstallments_orderTab extends oxAdminDetails
{

    const KEY_ORDER = 'order';
    const KEY_PAYMENT = 'payment';
    const KEY_REFUND = 'refund';
    const KEY_REMAINING_REFUND = 'remainingRefund';
    const KEY_ERROR = 'error';

    const PARAM_ORDER_ID = 'orderId';
    const PARAM_AMOUNT = 'refundAmount';

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'paypinstallmentsorder.tpl';

    /**
     * OXID eShop order model object.
     *
     * @var null|paypInstallmentsOxOrder|oxOrder
     */
    protected $_oOrder = null;

    /**
     * PayPal Installments Payment model object.
     *
     * @var null|paypInstallmentsPaymentData
     */
    protected $_oOrderPayment = null;

    /** @var  paypInstallmentsRefundList */
    protected $_oRefundList;

    /**
     * A number of remaining, possible refunds to make for current order payment.
     *
     * @var null|int
     */
    protected $_iRemainingRefunds = null;

    /**
     * An amount still possible to refund for current order payment.
     *
     * @var null|double
     */
    protected $_dRemainingRefundAmount = null;

    /** @var  paypInstallmentsFormatter */
    protected $_oFormatter;

    /** @var paypInstallmentsRefundHandler */
    protected $_oRefundHandler;
    /** @var  string */
    protected $_sErrorRefund;

    /**
     * Get data ready for template.
     *
     * @return array
     */
    public function getRenderData()
    {
        return array(
            static::KEY_ORDER            => $this->getFormatter()->formatOrder($this->getOrder()),
            static::KEY_PAYMENT          => $this->getFormatter()->formatPayment($this->getOrderPayment(), $this->getOrder()),
            static::KEY_REFUND           => $this->getFormatter()->formatRefundList(
                $this->getRefundList()->loadRefundsByTransactionId(),
                $this->getOrder()
            ),
            static::KEY_REMAINING_REFUND => $this->getFormatter()->formatPrice(
                $this->_paypInstallments_GetRemainingRefundableAmount(),$this->getOrder()->getOrderCurrency()
            ),
            static::KEY_ERROR            => oxRegistry::getLang()->translateString($this->getError()),
        );
    }

    /**
     * @throws oxException
     */
    public function refund()
    {
        try {
            $this->_paypInstallments_IsRefundable();
            $aRefundData = $this->getRefundHandler()
                ->doRequest();
            $this->buildRefund($aRefundData)->save();

            $blRefundIsDiscountable = $this->_paypInstallments_IsDiscountable();
            if ($blRefundIsDiscountable) {
                /** @var paypInstallmentsOxOrder|oxOrder $oOrder */
                $oOrder = $this->getOrder();
                $oOrder->paypInstallments_DiscountRefund($aRefundData['GrossRefundAmount']);
            }
        } catch (Exception $oException) {
            $this->_handleRefundException($oException);
        }
    }

    /**
     * Check order is payed by PayPalInstallment method.
     *
     * @return bool
     */
    public function isPayPalInstallmentOrder()
    {
        return $this->getOrder()->getFieldData('oxpaymenttype') == paypInstallmentsConfiguration::getPaymentId();
    }

    /**
     * Get OXID eShop order object if it's loaded.
     *
     * @return null|oxOrder|paypInstallmentsOxOrder
     * @throws oxException
     */
    public function getOrder()
    {
        if (is_null($this->_oOrder)) {
            /** @var paypInstallmentsOxOrder|oxOrder $oOrder */
            $oOrder = $this->_fetchOrder();

            if (!$oOrder->isLoaded()) {
                $sMessage = 'PAYP_INSTALLMENTS_REFUND_ERR_ORDER_NOT_LOADED';
                $oException = oxNew('oxException');
                $oException->setMessage($sMessage);

                $this->_handleRefundException($oException);
            }
            $this->setOrder($oOrder);
        }

        return $this->_oOrder;
    }

    /**
     * Order setter. Default value unsets attribute. Method chain supported.
     *
     * @param oxOrder $oOrder
     *
     * @return $this
     */
    public function setOrder(oxOrder $oOrder = null)
    {
        $this->_oOrder = $oOrder;

        return $this;
    }

    /**
     * Refund list getter.
     *
     * @return paypInstallmentsRefundList
     */
    public function getRefundList()
    {
        if (is_null($this->_oRefundList)) {
            $this->setRefundList($this->_fetchRefundList());
        }

        return $this->_oRefundList;
    }

    /**
     * Refund list setter. Default value unsets attribute. Method chain supported.
     *
     * @param paypInstallmentsRefundList $oRefundList
     *
     * @return $this
     */
    public function setRefundList(paypInstallmentsRefundList $oRefundList = null)
    {
        $this->_oRefundList = $oRefundList;

        return $this;
    }

    /**
     * Get PayPal Plus payment data object related to current order.
     *
     * @return paypInstallmentsPaymentData
     */
    public function getOrderPayment()
    {
        if (is_null($this->_oOrderPayment)) {
            $this->setOrderPayment($this->_fetchOrderPayment());
        }

        return $this->_oOrderPayment;
    }

    /**
     * Order payment setter. Default value unsets attribute. Method chain supported.
     *
     * @param paypInstallmentsPaymentData $oOrderPayment
     *
     * @return $this
     */
    public function setOrderPayment(paypInstallmentsPaymentData $oOrderPayment = null)
    {
        $this->_oOrderPayment = $oOrderPayment;

        return $this;
    }

    /**
     * Formatter getter.
     *
     * @return paypInstallmentsFormatter
     */
    public function getFormatter()
    {
        if (is_null($this->_oFormatter)) {
            $this->_oFormatter = oxNew('paypInstallmentsFormatter');
        }

        return $this->_oFormatter;
    }

    /**
     * Refund handler getter. Creates one if needed.
     *
     * @return paypInstallmentsRefundHandler
     */
    public function getRefundHandler()
    {
        if (is_null($this->_oRefundHandler)) {
            $this->setRefundHandler($this->_fetchRefundHandler());
        }

        return $this->_oRefundHandler;
    }

    /**
     * Refund handler setter. Default value unsets attribute. Method chains supported.
     *
     * @param paypInstallmentsRefundHandler $oRefundHandler
     *
     * @return $this
     */
    public function setRefundHandler(paypInstallmentsRefundHandler $oRefundHandler = null)
    {
        $this->_oRefundHandler = $oRefundHandler;

        return $this;
    }

    /**
     * Error message getter.
     *
     * @return string
     */
    public function getError()
    {
        return $this->_sErrorRefund;
    }

    /**
     * Error message setter. Default value unsets attribute.
     *
     * @param null|string $sErrorMessage
     *
     * @return $this
     */
    public function setError($sErrorMessage = null)
    {
        is_null($sErrorMessage) or $sErrorMessage = (string) $sErrorMessage;

        $this->_sErrorRefund = $sErrorMessage;

        return $this;
    }

    /**
     * Build refund of an data array.
     *
     * @param array $aRefundData
     *
     * @return paypInstallmentsRefund
     */
    public function buildRefund(array $aRefundData)
    {
        /** @var paypInstallmentsRefund $oRefund */
        $oRefund = oxNew('paypInstallmentsRefund');
        $oRefund->setTransactionId($aRefundData['TransactionId']);
        $oRefund->setMemo($aRefundData['Memo']);
        $oRefund->setRefundId($aRefundData['RefundId']);
        $oRefund->setAmount($aRefundData['GrossRefundAmount']);
        $oRefund->setCurrency($aRefundData['GrossRefundAmountCurrency']);
        $oRefund->setStatus($aRefundData['Status']);
        $oRefund->setResponse($aRefundData['Response']);
        $oRefund->setDateCreated(date('Y-m-d H:i:s'));

        return $oRefund;
    }

    /**
     * Get selected order for the first time.
     *
     * @return oxOrder
     */
    protected function _fetchOrder()
    {
        /** @var oxOrder $oOrder */
        $oOrder = oxNew('oxOrder');
        $oOrder->load($this->getEditObjectId());

        return $oOrder;
    }

    /**
     * Get refund list for the first time.
     *
     * @return paypInstallmentsRefundList
     */
    protected function _fetchRefundList()
    {
        return oxNew('paypInstallmentsRefundList', $this->getOrder()->getFieldData('oxtransid'));
    }

    /**
     * Get order payment for the first time.
     *
     * @return paypInstallmentsPaymentData
     */
    protected function _fetchOrderPayment()
    {
        /** @var paypInstallmentsPaymentData $oPaymentData */
        $oPaymentData = oxNew('paypInstallmentsPaymentData');
        $oPaymentData->loadByOrderId($this->getOrder()->getId());

        return $oPaymentData;
    }

    /**
     * Get refund handler for the first time.
     *
     * @return paypInstallmentsRefundHandler
     */
    protected function _fetchRefundHandler()
    {
        return oxNew(
            'paypInstallmentsRefundHandler',
            $this->getOrderPayment()->getTransactionId(),
            paypInstallmentsRefundHandler::REFUND_PARTIAL,
            $this->getOrderPayment()->getCurrencyCode(),
            (float) str_replace(',', '.', oxRegistry::getConfig()->getRequestParameter(static::PARAM_AMOUNT)),
            $this->_paypInstallments_GetRemainingRefundableAmount()
        );
    }

    /**
     * Handle refund exceptions. Method chain supported.
     *
     * @param Exception $oException
     *
     * @return $this
     */
    protected function _handleRefundException(Exception $oException)
    {
        $this->setError($oException->getMessage());

        return $this;
    }

    /**
     * @throws oxException
     */
    protected function _paypInstallments_IsRefundable()
    {
        $blIsRefundable = true;

        if (!$blIsRefundable) {
            $sMessage = 'PAYP_INSTALLMENTS_REFUND_ERR_NOT_REFUNDABLE';
            $oException = oxNew('oxException');
            $oException->setMessage($sMessage);

            throw $oException;
        }
    }

    /**
     * @return bool
     */
    protected function _paypInstallments_IsDiscountable()
    {
        $blRefundIsDiscountable = true;

        return $blRefundIsDiscountable;
    }


    /**
     * The refunds are discounted off the total order sum in paypInstallmentsOxOrder::paypInstallments_DiscountRefund
     * @return float
     */
    protected function _paypInstallments_GetRemainingRefundableAmount()
    {
        $fTotalOrderSum = (float) $this->getOrder()->getFieldData('oxtotalordersum');

        $fRefundableAmount = $fTotalOrderSum;

        return $fRefundableAmount;
    }
}
