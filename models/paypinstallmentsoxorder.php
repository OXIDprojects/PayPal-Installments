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
class paypInstallmentsOxOrder extends paypInstallmentsOxOrder_parent
{

    public function paypInstallments_getOrderNr()
    {
        $sOrderNr = oxNew('oxCounter')->getNext($this->_getCounterIdent());

        return $sOrderNr;
    }

    /**
     * @return paypInstallmentsPaymentData
     */
    public function paypInstallments_getPayPalInstallmentsPaymentData()
    {
        $oPaymentData = oxNew('paypInstallmentsPaymentData');
        $oPaymentData->loadByOrderId($this->getId());

        return $oPaymentData;
    }

    /**
     * @param $fAmount
     *
     * @return bool
     * @throws Exception
     */
    public function paypInstallments_DiscountRefund($fAmount)
    {

        $oPaymentData = $this->paypInstallments_getPayPalInstallmentsPaymentData();
        $oFinancingDetails = $this->_paypInstallments_getFinancingDetailsFromPaymentData($oPaymentData);
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey, $oFinancingDetails);

        $aParams = array(
            'oxorder__oxdiscount' => $this->_paypInstallments_GetNewTotalDiscount($fAmount),
        );

        /**  @var false|null $blResult Returns null on success, thus we must test for identical false */
        $blResult = $this->assign($aParams);
        if (false === $blResult) {
            $sMessage = 'PAYP_INSTALLMENTS_REFUND_ERR_DISCOUNT_COULD_NOT_BE_ASSIGNED_TO_ORDER';
            $oException = oxNew('oxException');
            $oException->setMessage($sMessage);

            throw $oException;
        }

        /** Do not reload Delivery costs, this is not necessary */
        $this->reloadDelivery(false);
        /** Do not reload basket discounts, this is not necessary. We have an order discount here */
        $this->reloadDiscount(false);

        /**
         * Recalculation of the order will do all the maths.
         * The new discount will be subtracted from brut and VAT Sum + Net Sum is recalculated
         * This function silently fails on error, but of course we are curious if our request was successful ...
         */
        $this->recalculateOrder();

        /** ... so we check the success like this */
        $blSuccess = $aParams['oxorder__oxdiscount'] == (double) $this->_getFieldData('oxdiscount');

        return $blSuccess;
    }

    /**
     * Helper for testing
     *
     * @codeCoverageIgnore
     *
     * @param $sParam
     *
     * @return mixed
     */
    protected function _getFieldData($sParam) {
        return $this->getFieldData($sParam);
    }

    /*
     * Get a filled financing details object.
     * This translates the API call response data in OXID template compliant objects
     *
     * @param $aResponseData
     *
     * @return paypInstallmentsFinancingDetails
     */
    protected function _paypInstallments_getFinancingDetailsFromPaymentData(paypInstallmentsPaymentData $oPaymentData)
    {
        $oFinancingDetails = oxNew('paypInstallmentsFinancingDetails');
        /** We assume here that all the amounts returned in the response will be in the same currency */
        $oFinancingDetails->setFinancingCurrency($oPaymentData->getCurrencyCode());
        $oFinancingDetails->setFinancingFeeAmount($oPaymentData->getFinancingFeeAmount());
        $oFinancingDetails->setFinancingMonthlyPayment($oPaymentData->getFinancingMonthlyPaymentAmount());
        $oFinancingDetails->setFinancingTerm($oPaymentData->getFinancingTerm());
        $oFinancingDetails->setFinancingTotalCost($this->getFieldData('oxtotalordersum'));

        return $oFinancingDetails;
    }

    /**
     * Get the new total discount of an order taking into account an amount refunded in PayPal Plus.
     *
     * @param $fAmount
     *
     * @return float
     */
    protected function _paypInstallments_GetNewTotalDiscount($fAmount)
    {
        return (double) $fAmount + (double) $this->getFieldData('oxdiscount');
    }


    /**
     * Get wrap cost sum formatted
     *
     * @return string
     */
    public function paypInstallments_getFormattedFinancingFee()
    {
        return oxRegistry::getLang()->formatCurrency($this->getFieldData('paypinstallments_financingfee'), $this->getOrderCurrency());
    }


    /**
     * Override invoicePdf::exportStandart from invoicePdf module to be able to add
     * custom text with custom functions to the generated PDF.
     *
     * @codeCoverageIgnore
     *
     * @see \paypPayPalPlusPdfArticleSummary::_setPayUntilInfo for shop versions CE/PE 4.7, 4.8
     * @see \paypPayPalPlusInvoicePdfArticleSummary::_setPayUntilInfo for shop versions CE/PE 4.9
     *
     * @param object $oPdf
     */
    public function exportStandart($oPdf)
    {
        /**
         * @var PdfBlock|InvoicepdfBlock $sPdfBlockClass
         * Set $sPdfBlockClass according to the OXID eShop version
         */
        $sPdfBlockClass = class_exists('PdfBlock') ? 'PdfBlock' : 'InvoicepdfBlock';

        /**
         * @var paypInstallmentsPdfArticleSummary|paypInstallmentsInvoicePdfArticleSummary $sPdfArticleSummaryClass
         * Set $sPdfArticleSummaryClass according to the OXID eShop version
         */
        $sShopVersion = oxRegistry::getConfig()->getVersion();
        if (version_compare($sShopVersion, "4.9.0", "<")) {
            $sPdfArticleSummaryClass = 'paypInstallmentsPdfArticleSummary';
        } else {
            $sPdfArticleSummaryClass = 'paypInstallmentsInvoicePdfArticleSummary';
        }

        // preparing order curency info
        $myConfig = $this->getConfig();
        /** @var PdfBlock|InvoicepdfBlock $oPdfBlock */
        $oPdfBlock = new $sPdfBlockClass();

        $this->_oCur = $myConfig->getCurrencyObject($this->oxorder__oxcurrency->value);
        if (!$this->_oCur) {
            $this->_oCur = $myConfig->getActShopCurrencyObject();
        }

        // loading active shop
        $oShop = $this->_getActShop();

        // shop information
        $oPdf->setFont($oPdfBlock->getFont(), '', 6);
        $oPdf->text(15, 55, $oShop->oxshops__oxname->getRawValue() . ' - ' . $oShop->oxshops__oxstreet->getRawValue() . ' - ' . $oShop->oxshops__oxzip->value . ' - ' . $oShop->oxshops__oxcity->getRawValue());

        // billing address
        $this->_setBillingAddressToPdf($oPdf);

        // delivery address
        if ($this->oxorder__oxdelsal->value) {
            $this->_setDeliveryAddressToPdf($oPdf);
        }

        // loading user
        $oUser = oxNew('oxuser');
        $oUser->load($this->oxorder__oxuserid->value);

        // user info
        $sText = $this->translate('ORDER_OVERVIEW_PDF_FILLONPAYMENT');
        $oPdf->setFont($oPdfBlock->getFont(), '', 5);
        $oPdf->text(195 - $oPdf->getStringWidth($sText), 55, $sText);

        // customer number
        $sCustNr = $this->translate('ORDER_OVERVIEW_PDF_CUSTNR') . ' ' . $oUser->oxuser__oxcustnr->value;
        $oPdf->setFont($oPdfBlock->getFont(), '', 7);
        $oPdf->text(195 - $oPdf->getStringWidth($sCustNr), 59, $sCustNr);

        // setting position if delivery address is used
        if ($this->oxorder__oxdelsal->value) {
            $iTop = 115;
        } else {
            $iTop = 91;
        }

        // shop city
        if ($this->oxorder__oxbilldate->value) {
            $sText = $oShop->oxshops__oxcity->getRawValue() . ', ' . date('d.m.Y', strtotime($this->oxorder__oxbilldate->value));
        } else {
            $sText = $oShop->oxshops__oxcity->getRawValue() . ', ' . date('d.m.Y');
        }
        $oPdf->setFont($oPdfBlock->getFont(), '', 10);
        $oPdf->text(195 - $oPdf->getStringWidth($sText), $iTop + 8, $sText);

        // shop VAT number
        if ($oShop->oxshops__oxvatnumber->value) {
            $sText = $this->translate('ORDER_OVERVIEW_PDF_TAXIDNR') . ' ' . $oShop->oxshops__oxvatnumber->value;
            $oPdf->text(195 - $oPdf->getStringWidth($sText), $iTop + 12, $sText);
            $iTop += 8;
        } else {
            $iTop += 4;
        }

        // invoice number
        $sText = $this->translate('ORDER_OVERVIEW_PDF_COUNTNR') . ' ' . $this->oxorder__oxbillnr->value;
        $oPdf->text(195 - $oPdf->getStringWidth($sText), $iTop + 8, $sText);

        // marking if order is canceled
        if ($this->oxorder__oxstorno->value == 1) {
            $this->oxorder__oxordernr->setValue($this->oxorder__oxordernr->getRawValue() . '   ' . $this->translate('ORDER_OVERVIEW_PDF_STORNO'), oxField::T_RAW);
        }

        // order number
        $oPdf->setFont($oPdfBlock->getFont(), '', 12);
        $oPdf->text(15, $iTop, $this->translate('ORDER_OVERVIEW_PDF_PURCHASENR') . ' ' . $this->oxorder__oxordernr->value);

        // order date
        $oPdf->setFont($oPdfBlock->getFont(), '', 10);
        $aOrderDate = explode(' ', $this->oxorder__oxorderdate->value);
        $sOrderDate = oxRegistry::get("oxUtilsDate")->formatDBDate($aOrderDate[0]);
        $oPdf->text(15, $iTop + 8, $this->translate('ORDER_OVERVIEW_PDF_ORDERSFROM') . $sOrderDate . $this->translate('ORDER_OVERVIEW_PDF_ORDERSAT') . $oShop->oxshops__oxurl->value);
        $iTop += 16;

        // product info header
        $oPdf->setFont($oPdfBlock->getFont(), '', 8);
        $oPdf->text(15, $iTop, $this->translate('ORDER_OVERVIEW_PDF_AMOUNT'));
        $oPdf->text(30, $iTop, $this->translate('ORDER_OVERVIEW_PDF_ARTID'));
        $oPdf->text(45, $iTop, $this->translate('ORDER_OVERVIEW_PDF_DESC'));
        $oPdf->text(135, $iTop, $this->translate('ORDER_OVERVIEW_PDF_VAT'));
        $oPdf->text(148, $iTop, $this->translate('ORDER_OVERVIEW_PDF_UNITPRICE'));
        $sText = $this->translate('ORDER_OVERVIEW_PDF_ALLPRICE');
        $oPdf->text(195 - $oPdf->getStringWidth($sText), $iTop, $sText);

        // separator line
        $iTop += 2;
        $oPdf->line(15, $iTop, 195, $iTop);

        // #345
        $siteH = $iTop;
        $oPdf->setFont($oPdfBlock->getFont(), '', 10);

        // order articles
        $this->_setOrderArticlesToPdf($oPdf, $siteH, true);

        // generating pdf file
        /** @var paypInstallmentsPdfArticleSummary|paypInstallmentsInvoicePdfArticleSummary $oArtSumm */
        $oArtSumm = new $sPdfArticleSummaryClass($this, $oPdf);
        $iHeight = $oArtSumm->generate($siteH);
        if ($siteH + $iHeight > 258) {
            $this->pdfFooter($oPdf);
            $iTop = $this->pdfHeader($oPdf);
            $oArtSumm->ajustHeight($iTop - $siteH);
            $siteH = $iTop;
        }

        $oArtSumm->run($oPdf);
        $siteH += $iHeight + 8;

        $oPdf->text(15, $siteH, $this->translate('ORDER_OVERVIEW_PDF_GREETINGS'));
    }
}
