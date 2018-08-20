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

// @codeCoverageIgnoreStart
$sLangName = 'English';

$aLang = array(
    'charset'                                                => 'UTF-8',

    'PAYP_INSTALLMENTS_MODULE_DESC'                          => 'Installments Powered by PayPal',
    'PAYP_INSTALLMENTS_MODULE_LONGDESC'                      => '',

    'SHOP_MODULE_GROUP_paypInstallmentsGeneral'              => 'General settings',
    'SHOP_MODULE_paypInstallmentsActive'                     => 'Payment method active?',
    'SHOP_MODULE_paypInstallmentsGenAdvertHome'              => 'Generic Advertising Homepage',
    'SHOP_MODULE_paypInstallmentsGenAdvertCat'               => 'Generic Advertising Category Pages',
    'SHOP_MODULE_paypInstallmentsGenAdvertDetail'            => 'Generic Advertising Detail Pages',
    'SHOP_MODULE_paypInstallmentsWithCalcValue'              => 'With calculated value',

    'SHOP_MODULE_GROUP_paypInstallmentsApi'                  => 'API Credentials for Productive Mode',
    'SHOP_MODULE_paypInstallmentsSoapUsername'               => 'SOAP Username',
    'SHOP_MODULE_paypInstallmentsSoapPassword'               => 'SOAP Password',
    'SHOP_MODULE_paypInstallmentsSoapSignature'              => 'SOAP Signature',
    'SHOP_MODULE_paypInstallmentsRestClientId'               => 'REST Client ID',
    'SHOP_MODULE_paypInstallmentsRestSecret'                 => 'REST Secret',

    'SHOP_MODULE_GROUP_paypInstallmentsSandboxApi'           => 'API Credentials for Sandbox Mode',
    'SHOP_MODULE_paypInstallmentsSandboxApi'                 => 'Activate Sandbox mode',
    'SHOP_MODULE_paypInstallmentsSBSoapUsername'             => 'Sandbox SOAP Username',
    'SHOP_MODULE_paypInstallmentsSBSoapPassword'             => 'Sandbox SOAP Password',
    'SHOP_MODULE_paypInstallmentsSBSoapSignature'            => 'Sandbox SOAP Signature',
    'SHOP_MODULE_paypInstallmentsSBRestClientId'             => 'Sandbox REST Client ID',
    'SHOP_MODULE_paypInstallmentsSBRestSecret'               => 'Sandbox REST Secret',

    'SHOP_MODULE_GROUP_paypInstallmentsLogging'              => 'Logging settings',
    'SHOP_MODULE_paypInstallmentsLogging'                    => 'Activate logging',
    'SHOP_MODULE_paypInstallmentsLoggingFile'                => 'Filename for general module logging',
    'SHOP_MODULE_paypInstallmentsLoggingLevel'               => 'Logging level of general module logging',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_DEBUG'         => 'DEBUG',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_INFO'          => 'INFO',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_WARN'          => 'WARN',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_ERROR'         => 'ERROR',
    'SHOP_MODULE_paypInstallmentsLoggingFileSoap'            => 'Filename of logging of SOAP Requests to PayPal API',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap'           => 'Logging Level of SOAP Requests to PayPal API',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_FINE'      => 'FINE',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_INFO'      => 'INFO',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_WARN'      => 'WARN',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_ERROR'     => 'ERROR',

    'tbclorder_paypinstallments'                             => 'Installments by PayPal',

    // Order tab "PayPal Plus" translations
    'PAYP_INSTALLMENTS_ONLY_FOR_PAYPAL_INSTALLMENTS_PAYMENT' => 'This tab is valid only for orders payed using PayPal Installments payment method.',

    'PAYP_INSTALLMENTS_PAYMENT_OVERVIEW'                     => 'Payment overview',
    'PAYP_INSTALLMENTS_PAYMENT_STATUS'                       => 'Payment status:',
    'PAYP_INSTALLMENTS_ORDER_AMOUNT'                         => 'Payment total:',
    'PAYP_INSTALLMENTS_REFUNDED_AMOUNT'                      => 'Refunded amount:',
    'PAYP_INSTALLMENTS_TRANSACTION_ID'                       => 'Transaction ID:',

    'PAYP_INSTALLMENTS_PAYMENT_REFUNDING'                    => 'Refunds',
    'PAYP_INSTALLMENTS_AVAILABLE_REFUNDS'                    => 'Remaining number of refund operation:',
    'PAYP_INSTALLMENTS_AVAILABLE_REFUND_AMOUNT'              => 'Remaining payment amount to refund:',
    'PAYP_INSTALLMENTS_DATE'                                 => 'Date',
    'PAYP_INSTALLMENTS_AMOUNT'                               => 'Amount',
    'PAYP_INSTALLMENTS_CURRENCY'                             => 'Currency',
    'PAYP_INSTALLMENTS_STATUS'                               => 'Status',
    'PAYP_INSTALLMENTS_NEW_REFUND'                           => 'Submit a refund',
    'PAYP_INSTALLMENTS_REFUND'                               => 'Refund',
    'PAYP_INSTALLMENTS_REFUND_ERR_10001'                     => 'This transaction is not (yet) known to PayPal. Try again in a few miniutes.',
    'PAYP_INSTALLMENTS_REFUND_ERR_NOT_REFUNDABLE'            => 'Error: This payment cannot be refunded.',
    'PAYP_ERR_VALIDATION_NEGATIVE_REFUND_AMOUNT'             => 'Error: You cannot refund a negative amount',
    'PAYP_ERR_VALIDATION_REFUND_AMOUNT_GT_REFUNDABLE'        => 'Error: You cannot refund an amount, which is greater than the refundable amount',

    'PAYP_INSTALLMENTS_INVOICE_FINANCING_FEE_AMOUNT'         => 'Financing Fee (0% VAT.)',
    'PAYP_INSTALLMENTS_INVOICE_FINANCING_TOTAL'              => 'Grand Total including Financing Fee',
);
// @codeCoverageIgnoreEnd
