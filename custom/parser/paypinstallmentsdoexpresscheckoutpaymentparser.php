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
class paypInstallmentsDoExpressCheckoutPaymentParser extends paypInstallmentsSoapParser
{

    const RESPONSE_TYPE = '\PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType';

    /**
     * @param string $sFormat Defaults to convert the date into MySql DateTime Format in UTC
     *
     * @return string Formatted Date
     */
    public function getFormattedTimestamp($sFormat = 'Y-m-d H:i:sP')
    {
        $sDateString = $this->getTimestamp();
        $oDate = new DateTime($sDateString);

        return $oDate->format($sFormat);
    }

    /**
     * Extract the Transaction ID from the PayPal Response
     *
     * @return mixed
     */
    public function getTransactionId()
    {
        $this->getLogger()->info("DoExpressCheckoutDetailsParser getTransactionId", array());
        if ($this->getResponse()->DoExpressCheckoutPaymentResponseDetails === null) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_RESPONSE_DETAILS');
            $this->getLogger()->error(
                "Missing Response Details object",
                array("response" => $this->getResponse())
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0] === null) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO');
            $this->getLogger()->error(
                "Missing Payment Info object",
                array("response" => $this->getResponse())
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID === null) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_TRANSACTION_ID');
            $this->getLogger()->error(
                "Missing Transaction Id",
                array("response" => $this->getResponse())
            );
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }

        // @codeCoverageIgnoreEnd

        return $this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
    }

    public function getPaymentStatus()
    {
        if (is_null($this->getResponse()->DoExpressCheckoutPaymentResponseDetails)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_RESPONSE_DETAILS');
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (is_null($this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0])) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_INFO');
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (is_null($this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->PaymentStatus)) {
            $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_PAYMENT_STATUS');
            $this->_throwParseException($sMessage);
            // @codeCoverageIgnoreStart
        }

        // @codeCoverageIgnoreEnd

        return $this->getResponse()->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->PaymentStatus;
    }

    protected function _throwParseException($sMessage)
    {
        $ex = new paypInstallmentsDoExpressCheckoutParseException();
        $ex->setMessage($sMessage);
        throw $ex;
    }
}
