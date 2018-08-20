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
$sLangName = 'Deutsch';

$aLang = array(
    'charset'                                                => 'UTF-8',

    'PAYP_INSTALLMENTS_MODULE_DESC'                          => 'Ratenzahlung Powered by PayPal',
    'PAYP_INSTALLMENTS_MODULE_LONGDESC'                      => '',

    'SHOP_MODULE_GROUP_paypInstallmentsGeneral'              => 'Allgemeine Einstellungen',
    'SHOP_MODULE_paypInstallmentsActive'                     => 'Zahlungsart aktiv?',
    'SHOP_MODULE_paypInstallmentsGenAdvertHome'              => 'Generische Bewerbung Homepage',
    'SHOP_MODULE_paypInstallmentsGenAdvertCat'               => 'Generische Bewerbung Kategorieseiten',
    'SHOP_MODULE_paypInstallmentsGenAdvertDetail'            => 'Generische Bewerbung Detailseiten',
    'SHOP_MODULE_paypInstallmentsWithCalcValue'              => 'Mit berechnetem Wert',

    'SHOP_MODULE_GROUP_paypInstallmentsApi'                  => 'API Zugangsdaten f&uuml;r den Produktiv Modus',
    'SHOP_MODULE_paypInstallmentsSoapUsername'               => 'SOAP Username',
    'SHOP_MODULE_paypInstallmentsSoapPassword'               => 'SOAP Passwort',
    'SHOP_MODULE_paypInstallmentsSoapSignature'              => 'SOAP Signature',
    'SHOP_MODULE_paypInstallmentsRestClientId'               => 'REST Client ID',
    'SHOP_MODULE_paypInstallmentsRestSecret'                 => 'REST Secret',

    'SHOP_MODULE_GROUP_paypInstallmentsSandboxApi'           => 'API Zugangsdaten f&uuml;r den Sandbox Modus',
    'SHOP_MODULE_paypInstallmentsSandboxApi'                 => 'Sandbox Modus aktivieren',
    'SHOP_MODULE_paypInstallmentsSBSoapUsername'             => 'SOAP Username',
    'SHOP_MODULE_paypInstallmentsSBSoapPassword'             => 'SOAP Passwort',
    'SHOP_MODULE_paypInstallmentsSBSoapSignature'            => 'SOAP Signature',
    'SHOP_MODULE_paypInstallmentsSBRestClientId'             => 'REST Client ID',
    'SHOP_MODULE_paypInstallmentsSBRestSecret'               => 'REST Secret',

    'SHOP_MODULE_GROUP_paypInstallmentsLogging'              => 'Logging Einstellungen',
    'SHOP_MODULE_paypInstallmentsLogging'                    => 'Logging aktivieren',
    'SHOP_MODULE_paypInstallmentsLoggingFile'                => 'Dateiname der allgemeinen Log Datei',
    'SHOP_MODULE_paypInstallmentsLoggingLevel'               => 'Allgemeines Logging Level',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_DEBUG'         => 'DEBUG',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_INFO'          => 'INFO',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_WARN'          => 'WARN',
    'SHOP_MODULE_paypInstallmentsLoggingLevel_ERROR'         => 'ERROR',
    'SHOP_MODULE_paypInstallmentsLoggingFileSoap'            => 'Dateiname f&uuml;r das Logging der SOAP Requests an die PayPal API',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap'           => 'Logging Level der SOAP Requests an die PayPal API',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_FINE'      => 'FINE',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_INFO'      => 'INFO',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_WARN'      => 'WARN',
    'SHOP_MODULE_paypInstallmentsLoggingLevelSoap_ERROR'     => 'ERROR',

    'tbclorder_paypinstallments'                             => 'Ratenzahlung by PayPal',

    // Order tab "PayPal Installments" translations
    'PAYP_INSTALLMENTS_ONLY_FOR_PAYPAL_INSTALLMENTS_PAYMENT' => 'Diese Registerkarte ist nur f&uuml;r Bestellungen mit der Zahlungsart PayPal Installments.',

    'PAYP_INSTALLMENTS_PAYMENT_OVERVIEW'                     => 'Zahlungs&uuml;bersicht',
    'PAYP_INSTALLMENTS_PAYMENT_STATUS'                       => 'Zahlungsstatus:',
    'PAYP_INSTALLMENTS_ORDER_AMOUNT'                         => 'Bestellpreis gesamt:',
    'PAYP_INSTALLMENTS_REFUNDED_AMOUNT'                      => 'Erstatteter Betrag:',
    'PAYP_INSTALLMENTS_TRANSACTION_ID'                       => 'Transaction ID:',

    'PAYP_INSTALLMENTS_PAYMENT_REFUNDING'                    => 'R&uuml;ckerstattungen',
    'PAYP_INSTALLMENTS_AVAILABLE_REFUNDS'                    => 'Anzahl verf&uuml;gbarer R&uuml;ckerstattungen:',
    'PAYP_INSTALLMENTS_AVAILABLE_REFUND_AMOUNT'              => 'Verf&uuml;gbarer R&uuml;ckerstattungsbetrag:',
    'PAYP_INSTALLMENTS_DATE'                                 => 'Datum',
    'PAYP_INSTALLMENTS_AMOUNT'                               => 'Betrag',
    'PAYP_INSTALLMENTS_CURRENCY'                             => 'Währung',
    'PAYP_INSTALLMENTS_STATUS'                               => 'Status',
    'PAYP_INSTALLMENTS_NEW_REFUND'                           => 'R&uuml;ckerstattung veranlassen',
    'PAYP_INSTALLMENTS_REFUND'                               => 'Erstatten',

    'PAYP_INSTALLMENTS_REFUND_ERR_10001'                     => 'Fehler: Diese Transaktion ist PayPal (noch) nicht bekannt, bitte probieren Sie es in ein paar Minuten noch einmal.',
    'PAYP_INSTALLMENTS_REFUND_ERR_NOT_REFUNDABLE'            => 'Fehler: Bei dieser Zahlung k&ouml;nnen keine R&uuml;ckerstattungen mehr gemacht werden.',
    'PAYP_ERR_VALIDATION_NEGATIVE_REFUND_AMOUNT'             => 'Fehler: Sie k&ouml;nnen keinen negativen Betrag r&uuml;ckerstatten',
    'PAYP_ERR_VALIDATION_REFUND_AMOUNT_GT_REFUNDABLE'        => 'Fehler: Sie k&ouml;nnen keinen Betrag r&uuml;ckerstatten, der gr&ouml;sser ist als der verf&uuml;gbare R&uuml;ckerstattungsbetrag',

    'PAYP_INSTALLMENTS_INVOICE_FINANCING_FEE_AMOUNT'         => 'Finanzierungskosten (0% MwSt.)',
    'PAYP_INSTALLMENTS_INVOICE_FINANCING_TOTAL'              => 'Gesamtsumme (inkl. Finanzierungskosten)',
);
// @codeCoverageIgnoreEnd
