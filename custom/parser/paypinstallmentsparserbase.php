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
 * Class paypInstallmentsParserBase
 *
 * @desc
 */
abstract class paypInstallmentsParserBase extends oxSuperCfg implements \Psr\Log\LoggerAwareInterface, paypInstallmentsParserInterface
{

    const RESPONSE_TYPE = null;//each parser can handle specific response

    /**
     * @var null|stdClass
     */
    protected $_oResponse = null;


    /**
     * Response setter
     *
     * @param mixed $oResponse
     *
     * @throws paypInstallmentsRefundRequestParameterValidationException
     * @return $this
     */
    public function setResponse($oResponse)
    {
        $this->validateResponseType($oResponse);
        $this->_oResponse = $oResponse;

        return $this;
    }

    /**
     * Response getter.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_oResponse;
    }


    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $_oLogger;


    /**
     * Setter for logger. Method chain supported.
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $oLogger)
    {
        $this->_oLogger = $oLogger;

        return $this;
    }

    /**
     * Getter for logger. If one is not set - return NullLogger.
     *
     * @return \Psr\Log\LoggerInterface | Psr\Log\NullLogger
     */
    public function getLogger()
    {
        if (is_null($this->_oLogger)) {
            return new Psr\Log\NullLogger();
        }

        return $this->_oLogger;
    }


    /**
     * Test response type is compatible to parser.
     *
     * @param mixed $oResponse
     *
     * @return bool
     */
    protected function isResponseTypeValid($oResponse)
    {
        return $this->getValidResponseType() && is_a($oResponse, $this->getValidResponseType());
    }

    /**
     * Validate response type. Unfortunately there is no common response interface so have to do validation on method.
     *
     * @param mixed $oResponse
     *
     * @return $this
     * @throws paypInstallmentsRefundRequestParameterValidationException
     */
    protected function validateResponseType($oResponse)
    {
        if ($this->isResponseTypeValid($oResponse)) {
            return $this;
        }
        throw new paypInstallmentsRefundRequestParameterValidationException('INVALID_RESPONSE_TYPE');
    }

    /**
     * Expected response type getter.
     *
     * @return null|string
     */
    protected function getValidResponseType()
    {
        return static::RESPONSE_TYPE;
    }
}
