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
 * Class paypInstallmentsParser
 *
 * Parent Class of all parsers. Extend this class if you  build a new parser.
 *
 * This parser parses the AbstractResponse Type part of the response, the more specific parts should be
 * parsed by the specific parsers.
 */
abstract class paypInstallmentsSoapParser extends paypInstallmentsParserBase
{

    const RESPONSE_TYPE = null;

    /**
     * @var null|\PayPal\PayPalAPI\SetExpressCheckoutReq
     */
    protected $_oRequest = null;

    /**
     * @return \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType
     */
    public function getResponse()
    {
        return parent::getResponse();
    }

    /**
     * Return the value of the Ack property in the PayPal response
     *
     * @return string
     */
    public function getAck()
    {
        return $this->_getValueFromResponse('Ack');
    }

    /**
     * Return the value of the Build property in the PayPal response
     *
     * @return string
     */
    public function getBuild()
    {
        return $this->_getValueFromResponse('Build');
    }

    /**
     * Return the value of the CorrelationId property in the PayPal response
     *
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->_getValueFromResponse('CorrelationId');
    }

    /**
     * Return the value of the Errors property in the PayPal response
     *
     * @return string
     */
    public function getErrors()
    {
        return $this->_getValueFromResponse('Errors');
    }

    /**
     * Return the value of the Timestamp property in the PayPal response
     *
     * @return string
     */
    public function getTimestamp()
    {
        // Format is 2015-12-04T09:37:30Z
        return $this->_getValueFromResponse('Timestamp');
    }

    /**
     * Return the value of the Version property in the PayPal response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_getValueFromResponse('Version');
    }

    /**
     * Generic function to return a given Property from the first level of the response
     *
     * @param $sProperty
     *
     * @return null
     * @throws paypinstallmentsmalformedresponseexception
     */
    protected function _getValueFromResponse($sProperty)
    {
        $this->getLogger()->info("paypInstallmentsSoapParser _getValueFromResponse",
            array("property" => $sProperty, "response" => $this->getResponse()));
        $mValue = null;

        $sMessage = paypInstallmentsConfiguration::getParseErrorMessage('MISSING_' . strtoupper($sProperty));
        if (!is_object($this->getResponse())) {
            $this->getLogger()->error("paypInstallmentsSoapParser _getValueFromResponse",
                array("error" => "no response is set", "response" => $this->getResponse()));

            $this->_throwMalformedResponseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!property_exists($this->getResponse(), $sProperty) || !isset($this->getResponse()->{$sProperty})) {
            $this->getLogger()->error("paypInstallmentsSoapParser _getValueFromResponse",
                array("error" => "the property does not have the passed property", "response" => $this->getResponse(), "property" => $sProperty));

            $this->_throwMalformedResponseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        $mValue = $this->getResponse()->$sProperty;

        return $mValue;
    }

    /**
     * Helper function to return the value of a class property.
     * Throws an exception if the class is not a class or the property does not exist.
     *
     * @param object $oClass    Instance of a class to get the property value from
     * @param string $sProperty Property name
     * @param string $sMessage  Exception Error message
     *
     * @return mixed
     * @throws paypinstallmentsmalformedrequestexception
     */
    protected function _getValueByClassAndProperty($oClass, $sProperty, $sMessage)
    {
        $this->getLogger()->info("paypInstallmentsSoapParser _getValueByClassAndProperty",
            array("property" => $sProperty, "class" => $oClass, "message" => $sMessage, "response" => $this->getResponse()));
        if (!is_object($oClass)) {
            $this->getLogger()->error("paypInstallmentsSoapParser _getValueByClassAndProperty",
                array("error" => "The passed 'object' is not an object", "object" => $oClass));
            $this->_throwParseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!property_exists($oClass, $sProperty)) {
            $this->getLogger()->error("paypInstallmentsSoapParser _getValueByClassAndProperty",
                array("error" => "The passed object does not have the passed property", "object" => $oClass, "property" => $sProperty));
            $this->_throwParseException($sMessage);
            //  There is a strange behaviour in php code coverage, which reports the parenthesis as uncovered
            // @codeCoverageIgnoreStart
        }

        // @codeCoverageIgnoreEnd


        return $oClass->$sProperty;
    }

    /**
     * throw a validation exception using the given message
     *
     * @param $sMessage
     *
     * @throws paypinstallmentsmalformedrequestexception
     */
    protected function _throwParseException($sMessage)
    {
        /** @var paypinstallmentsmalformedrequestexception $oEx */
        $oEx = oxNew('paypinstallmentsmalformedrequestexception');

        $oEx->setMessage($sMessage);

        throw $oEx;
    }

    /**
     * Exception to be thrown in case of a malformed response
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
