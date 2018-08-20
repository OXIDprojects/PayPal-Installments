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
 * Class paypInstallmentsValidator
 *
 * Parent Class of all validators. Extend this class if you  build a new validator.
 *
 * The validation is about the content (not acknowledged requests, logical errors, missing or wrong data) of the data
 * not the form of the data.
 * If the data cannot be parsed correctly the parser will throw its own exceptions.
 *
 * This validator validates the AbstractResponse Type part of the response, the more specific parts should be
 * validated by the specific validators.
 *
 * This validator does not validate any requests. This has to be done by the subclasses
 *
 * @todo implement setter/getter for parser, request, response.
 */
class paypInstallmentsSoapValidator extends paypInstallmentsValidatorBase
{

    /**
     * An instance of the response/request parser
     *
     * @var null|paypInstallmentsSoapParser
     */
    protected $_oParser = null;


    /**
     * An instance of a PayPal request.
     * There is no common parent class for requests, so type hinting makes no sens here.
     *
     * @var null
     */
    protected $_oRequest = null;

    /*
     * An instance of a PayPal response.
     * All responses have a common parent class.
     *
     * @var null|\PayPal\EBLBaseComponents\AbstractResponseType
     */
    protected $_oResponse = null;


    /**
     * Property setter
     *
     * @param paypInstallmentsSoapParser $oParser
     *     */
    public function setParser(paypInstallmentsSoapParser $oParser)
    {
        $this->_oParser = $oParser;
    }

    /**
     * Validates the response by calling all required validation methods.
     *
     * @throws paypinstallmentsnoacksuccessexception
     */
    public function validateResponse()
    {
        $this->getLogger()->info("paypInstallmentsSoapValidator validateResponse", array("response" => $this->_oResponse));
        $this->_validateResponseVersion();
        $this->_validateResponseAck();
    }

    /**
     * As the request do not have a common parent class, there can be no common validation :-(
     */
    public function validateRequest()
    {
        $this->getLogger()->info("paypInstallmentsSoapValidator validateRequest", array("request" => $this->_oRequest));
        /**
         * The request holds no version information.
         * The version information is a private property of the Paypal Service and thus not validatable
         */
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * Validates the Ack property of the response.
     *
     * Validation fails for any value other then 'Success'
     *
     * @throws paypinstallmentsnoacksuccessexception
     */
    protected function _validateResponseAck()
    {
        $this->getLogger()->info("paypInstallmentsSoapValidator _validateResponseAck", array());
        if ($this->_oParser->getAck() != paypInstallmentsConfiguration::getResponseAckSuccess()) {
            $sMessage = '';
            $iFirstErrorCode = 0;
            if (is_array($this->_oResponse->Errors)) {
                $iFirstErrorCode = (int) $this->_oResponse->Errors[0]->ErrorCode;
                $aErrors = array();
                foreach ($this->_oResponse->Errors as $oError) {
                    $aErrors[] = 'Error #' . $oError->ErrorCode . ': ' . $oError->ShortMessage . ': ' .
                                 $oError->LongMessage;
                }
                $sMessage = implode('<br />', $aErrors);
            }
            $this->getLogger()->error(
                "paypInstallmentsSoapValidator _validateResponseAck",
                array("error" => "the request returned some errors", "errors" => $this->_oResponse->Errors)
            );

            $this->_throwNoAckSuccessException($sMessage, $iFirstErrorCode);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validates the version of the response.
     *
     * This is important as a older service versions did not provide
     * installments options and we had to modify the original SDK code to change the version.
     *
     * @throws paypinstallmentsversionmismatchexception
     */
    protected function _validateResponseVersion()
    {
        $this->getLogger()->info("paypInstallmentsSoapValidator _validateResponseVersion", array());
        if ($this->_oParser->getVersion() != paypInstallmentsConfiguration::getServiceVersion()) {
            $sMessage = paypInstallmentsConfiguration::getValidationErrorMessage('WRONG_SERVICE_VERSION');
            $this->getLogger()->error(
                "paypInstallmentsSoapValidator _validateResponseVersion",
                array("error" => "The requested service version is not the necessary one", "request" => $this->_oRequest)
            );
            $this->_throwVersionMismatchException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Throws an exception in case of a service version mismatch
     *
     * @param $sMessage
     *
     * @throws paypinstallmentsversionmismatchexception
     */
    protected function _throwVersionMismatchException($sMessage)
    {
        /** @var paypinstallmentsversionmismatchexception $oEx */
        $oEx = oxNew('paypinstallmentsversionmismatchexception');

        $oEx->setMessage($sMessage);

        throw $oEx;
    }

    /**
     * Throws an exception in case of a not acknowledged request
     *
     * @throws paypinstallmentsnoacksuccessexception
     */
    protected function _throwNoAckSuccessException($sMessage, $sErrorCode)
    {
        /** @var paypinstallmentsnoacksuccessexception $oEx */
        $oEx = oxNew('paypinstallmentsnoacksuccessexception', $sMessage, $sErrorCode);

        throw $oEx;
    }

    /**
     * Throws an exception in case of a malformed response
     *
     * @param $sMessage
     *
     * @throws paypinstallmentsmalformedresponseexception
     */
    protected function _throwMalformedResponseException($sMessage)
    {
        /** @var paypinstallmentsmalformedresponseexception $oEx */
        $oEx = oxNew('paypinstallmentsmalformedresponseexception');

        $oEx->setMessage($sMessage);

        throw $oEx;
    }
}
