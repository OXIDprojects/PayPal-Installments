<?php
/**
 * Class paypInstallmentsPdfArticleSummary
 *
 * Extend PdfArticleSummary to be able to display additional payment instructions after the article summary.
 *
 * Third party integration and not testable in all shop versions
 *
 */
// @codeCoverageIgnoreStart
if (class_exists('PdfArticleSummary')) {
    class paypInstallmentsPdfArticleSummary extends PdfArticleSummary
    {

        /**
         * Add financing information to the invoice
         *
         * @inheritdoc
         *
         * @param int $iStartPos
         */
        protected function _setGrandTotalPriceInfo(&$iStartPos)
        {
            parent::_setGrandTotalPriceInfo($iStartPos);

            $oOrder = $this->_getOrder();

            $sPaymentType = $oOrder->getFieldData('oxpaymenttype');

            // Add financing costs lines
            if (paypInstallmentsConfiguration::getPaymentId() == $sPaymentType) {
                /** Retrieve values */
                $fFinancingFee = $oOrder->getFieldData('paypinstallments_financingfee');
                $fGrandOrderTotal = $oOrder->getFieldData('oxtotalordersum');
                $fGrandOrderTotalWithFee = $fGrandOrderTotal + $fFinancingFee;

                /** Format values */
                $oLang = oxRegistry::getLang();
                $sFormattedFinancingFee = $oLang->formatCurrency($fFinancingFee, $this->_oData->getCurrency()) . ' ' . $this->_oData->getCurrency()->name;
                $sFormattedGrandOrderTotalWithFee = $oLang->formatCurrency($fGrandOrderTotalWithFee, $this->_oData->getCurrency()) . ' ' . $this->_oData->getCurrency()->name;

                /** Add a line separator */
                $this->line(45, $iStartPos, 195, $iStartPos);
                /** Add some space */
                $iStartPos += 7;

                /** Set font weight to normal, as parent::_setGrandTotalPriceInfo sets it to bold  */
                $this->font($this->getFont(), '', 10);

                /** Add the financing fee line */
                $iStartPos += 4;
                $this->text(45, $iStartPos, $this->_oData->translate('PAYP_INSTALLMENTS_INVOICE_FINANCING_FEE_AMOUNT'));
                $this->text(195 - $this->_oPdf->getStringWidth($sFormattedFinancingFee), $iStartPos, $sFormattedFinancingFee);

                /** Add a line separator */
                $iStartPos++;
                $this->line(45, $iStartPos, 195, $iStartPos);

                /** Add the "grand order total with fee" line */
                $iStartPos += 4;
                $this->text(45, $iStartPos, $this->_oData->translate('PAYP_INSTALLMENTS_INVOICE_FINANCING_TOTAL'));
                $this->text(195 - $this->_oPdf->getStringWidth($sFormattedGrandOrderTotalWithFee), $iStartPos, $sFormattedGrandOrderTotalWithFee);

                $iStartPos++;
            }
        }

        /**
         * Only add "pay until info" if order was not paid with PayPal Installment
         *
         * @inheritdoc
         *
         * @param int $iStartPos
         */
        protected function _setPayUntilInfo(&$iStartPos)
        {
            $oOrder = $this->_getOrder();

            $sPaymentType = $oOrder->getFieldData('oxpaymenttype');
            if (paypInstallmentsConfiguration::getPaymentId() != $sPaymentType) {
                parent::_setPayUntilInfo($iStartPos);
            }
        }

        /**
         * Return an instance of the related order.
         *
         * @return oxOrder
         */
        protected function _getOrder()
        {
            $sOrderId = $this->_getOrderId();
            $oOrder = oxNew('oxOrder');
            $oOrder->load($sOrderId);

            return $oOrder;
        }

        /**
         * Return the ID or the current order.
         * Needed for testing.
         *
         * @codeCoverageIgnore
         *
         * @return mixed
         */
        protected function _getOrderId()
        {
            $sOrderId = $this->_oData->getId();

            return $sOrderId;
        }
    }
}
// @codeCoverageIgnoreEnd