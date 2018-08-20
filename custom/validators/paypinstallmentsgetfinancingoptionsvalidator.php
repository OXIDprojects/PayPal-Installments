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
 * Class paypInstallmentsGetFinancingOptionsValidator
 *
 * @desc Financing option validator
 */
class paypInstallmentsGetFinancingOptionsValidator extends paypInstallmentsValidatorBase
{

    /** @var paypInstallmentsGetFinancingOptionsParser */
    protected $_oParser;

    /**
     * @param paypInstallmentsGetFinancingOptionsParser $oParser
     */
    public function setParser(paypInstallmentsGetFinancingOptionsParser $oParser)
    {
        $this->_oParser = $oParser;
    }

    /**
     * Parser getter.
     *
     * @return paypInstallmentsGetFinancingOptionsParser
     */
    public function getParser()
    {
        return $this->_oParser;
    }

    /**
     * Throw the correct exceptions in case something went wrong with the request
     *
     * @throws paypInstallmentsFinancingOptionsException
     */
    public function validateAuthenticationResponse()
    {
        $this->getLogger()->info("paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse", array());
        if ($this->getParser()->getHttpCode() === 401) {
            if ($this->getParser()->getErrorDescription() === "The client credentials are invalid") {
                $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INCORRECT_CLIENT_ID');
                $this->getLogger()->error(
                    "paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse",
                    array("error" => "incorrect paypal client id entered")
                );
                $this->_throwException($sMessage);
                // @codeCoverageIgnoreStart
            } // @codeCoverageIgnoreEnd
            else if ($this->getParser()->getErrorDescription() === "Client secret does not match for this client") {
                $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('INCORRECT_CLIENT_SECRET');
                $this->getLogger()->error(
                    "paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse",
                    array("error" => "the entered secret does not match the clientid")
                );
                $this->_throwException($sMessage);
                // @codeCoverageIgnoreStart
            }
        }
        // @codeCoverageIgnoreEnd

        if (!$this->getParser()->getAccessToken()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_ACCESS_TOKEN');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse",
                array("error" => "No Access Token was returned by paypal")
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!$this->getParser()->getTokenType()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('MISSING_TOKEN_TYPE');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse",
                array("error" => "No Access Token Type was returned by paypal")
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if ($this->getParser()->getTokenType() !== "Bearer") {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('NON_BEARER_TOKEN');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateAuthenticationResponse",
                array("error" => "The Access Token returned by paypal is not a bearer token")
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Throw exceptions if someone tries to pass invalid data to paypal
     *
     * @param $fAmount
     * @param $sCurrency
     * @param $sCountryCode
     *
     * @throws paypInstallmentsFinancingOptionsException
     */
    public function validateFinancingOptionsArguments($fAmount, $sCurrency, $sCountryCode)
    {
        $this->getLogger()->info("paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsArguments", array());
        if ($fAmount <= 0.0) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('NEGATIVE_AMOUNT');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsArguments",
                array("error" => "negative amount passed", "amount" => $fAmount)
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($sCurrency !== $this->getConfiguration()->getRequiredOrderTotalCurrency()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_CURRENCY');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsArguments",
                array("error" => "non euro currency passed", "currency" => $sCurrency)
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if ($sCountryCode !== $this->getConfiguration()->getRequiredShippingCountry()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('ORDER_NOT_FROM_GERMANY');
            $this->getLogger()->error(
                "paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsArguments",
                array(
                    "error"       => "the passed country code is not " . $this->getConfiguration()->getRequiredShippingCountry(),
                    "countryCode" => $sCountryCode
                )
            );
            $this->_throwException($sMessage);
            // @codeCoverageIgnoreStart
        }
    }
    // @codeCoverageIgnoreEnd

    /**
     * Throw exceptions if an error occured with our paypal request
     *
     * @throws paypInstallmentsFinancingOptionsException
     */
    public function validateFinancingOptionsResponse()
    {
        $this->getLogger()->info("paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsResponse", array());
        if ($this->getParser()->getHttpCode() != "200") {
            if ($this->getParser()->getName() && $this->getParser()->getName() == "inputValidationError") {
                $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYPAL_VALIDATION_ERROR');
                $sMessage .= " " . $this->getParser()->getMessage();
                $this->getLogger()->error(
                    "paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsResponse",
                    array("error" => "PayPal Validation Error", "message" => $this->getParser()->getMessage())
                );
                $this->_throwException($sMessage);
                // @codeCoverageIgnoreStart
            } // @codeCoverageIgnoreEnd
            else {
                $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('PAYPAL_REQUEST_ERROR');
                $sMessage .= " " . $this->getParser()->getResponse();
                $this->getLogger()->error(
                    "paypInstallmentsGetFinancingOptionsValidator validateFinancingOptionsResponse",
                    array("error" => "PayPal Request Error", "response" => $this->getParser()->getResponse())
                );
                $this->_throwException($sMessage);
                // @codeCoverageIgnoreStart
            }
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Throw an exception with the given Message
     *
     * @param $sMessage string
     *
     * @throws paypInstallmentsFinancingOptionsException
     */
    protected function _throwException($sMessage)
    {
        $ex = new paypInstallmentsFinancingOptionsException();
        $ex->setMessage($sMessage);
        throw $ex;
    }
}
