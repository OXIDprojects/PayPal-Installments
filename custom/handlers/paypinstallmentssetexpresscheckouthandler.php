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
 * Class paypInstallmentsSetExpressCheckoutHandler
 *
 * Handles the SetExpressCheckout Call to PalPal
 */
class paypInstallmentsSetExpressCheckoutHandler extends paypInstallmentsHandlerBase
{

    /**
     * An instance of oxBasket at the moment of checkout step 3
     *
     * @var oxBasket $_oBasket
     */
    protected $_oBasket ;

    /**
     * This will be set to the oxorder__ordernr
     *
     * @var string $_sInvoiceId
     */
    protected $_sInvoiceId ;

    /**
     * Calls SetExpressCheckout on the PayPalService
     *
     * Throws exceptions if:
     * - the call fails
     * - request is not valid or cannot be parsed
     * - the response is not valid or cannot be parsed
     *
     * @throws Exception
     *
     * @return string The PayPal Token to be used in GetExpressCheckout, DoExpressCheckout, Refund, etc ..
     */
    public function doRequest()
    {
        /** @var paypInstallmentsSdkObjectGenerator $oObjectGenerator */
        $oObjectGenerator = $this->_getObjectGenerator();

        $oModuleConfig = oxNew('paypInstallmentsConfiguration');
        $oObjectGenerator->setConfiguration($oModuleConfig);

        $oDataProvider = oxNew('paypInstallmentsCheckoutDataProvider');
        $oBasket = $this->_getBasket();
        $oDataProvider->setBasket($oBasket);
        $oObjectGenerator->setDataProvider($oDataProvider);

        /**
         * Instance of the parser. Will throw exceptions if request or response cannot be parsed
         */
        $oParser = $this->_getParser('paypInstallmentsSetExpressCheckoutParser');

        /**
         * Instance of the validator
         */
        $oValidator = $this->_getValidator('paypInstallmentsSetExpressCheckoutValidator');
        $oValidator->setParser($oParser);

        /**
         * set up lögging
         */

        $oLogger = $this->getLogger();
        $oValidator->setLogger($oLogger);
        $oParser->setLogger($oLogger);

        /**
         * Get the request object
         */
        $oRequest = $oObjectGenerator->getSetExpressCheckoutReqObject();

        /**
         * Validate the request. If validations (or parsing) fails an
         * paypInstallmentsSetExpressCheckoutRequestValidationException
         * exception is thrown.
         * This is the last resource. The calling controller should already validate some basic values.
         *
         * @see paypInstallmentsSetExpressCheckoutValidator for validation conditions
         */
        $oValidator->setRequest($oRequest);
        $oValidator->validateRequest();

        $oLogger->info("SetExpressCheckoutDetails doRequest", array("request" => $oRequest));

        /**
         * Call SetExpressCheckout.
         * Rethrow Exception, so we can handle it in our own way.
         */
        /** Get instance of the PayPal Service */
        $oPayPalService = $oObjectGenerator->getPayPalServiceObject();
        try {
            /**
             * Get the response from PayPal
             */
            $oResponse = $oPayPalService->SetExpressCheckout($oRequest);
        } catch (Exception $oPayPalException) {
            $sMessage = $oPayPalException->getMessage();
            $oLogger->error("SetExpressCheckoutDetails doRequest", array("request" => $oRequest, "error" => $sMessage));
            $this->_throwSetExpressCheckoutException($sMessage);
        //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        /**
         * And validate the response. If the validation (or parsing) fails an exception is thrown here
         */
        $oValidator->setResponse($oResponse);
        $oValidator->validateResponse();

        /**
         * Return the PayPal Token for further processing if everything went fine :-)
         */
        return (string) $oParser->getToken();
    }

    /**
     * Property setter
     *
     * @codeCoverageIgnore
     *
     * @param $oBasket
     */
    public function setBasket($oBasket) {
        $this->_oBasket = $oBasket;
    }

    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return oxBasket
     */
    protected function _getBasket() {
        return $this->_oBasket;
    }

    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function _getInvoiceId()
    {
        return $this->_sInvoiceId;
    }

    /**
     * Property setter
     *
     * @codeCoverageIgnore
     *
     * @param string $sInvoiceId
     */
    public function setInvoiceId($sInvoiceId)
    {
        $this->_sInvoiceId = (string) $sInvoiceId;
    }

    /**
     * Returns an instance paypInstallmentsSdkObjectGenerator.
     * Needed for mocking.
     *
     * @codeCoverageIgnore
     *
     * @return paypInstallmentsSdkObjectGenerator
     */
    protected function _getObjectGenerator()
    {
        $oObjectGenerator = oxNew('paypInstallmentsSdkObjectGenerator');

        return $oObjectGenerator;
    }

    /**
     * Re-Throws an exception in case the API call to SetExpressCheckout throws an exception
     *
     * @param $sMessage
     *
     * @throws paypinstallmentssetexpresscheckoutexception
     */
    protected function _throwSetExpressCheckoutException($sMessage)
    {
        /** @var paypinstallmentssetexpresscheckoutexception $oEx */
        $oEx = oxNew('paypinstallmentssetexpresscheckoutexception');
        $oEx->setMessage($sMessage);
        throw $oEx;
    }
}
