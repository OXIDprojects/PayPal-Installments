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
abstract class paypInstallmentsHandlerBase extends oxSuperCfg implements \Psr\Log\LoggerAwareInterface, paypInstallmentsHandlerInterface
{

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
     * getter for logger
     *
     * @return \Psr\Log\LoggerInterface | Psr\Log\NullLogger
     */
    public function getLogger()
    {
        if ($this->_oLogger === null) {
            $oManager = new paypInstallmentsLoggerManager(oxNew("paypInstallmentsConfiguration"));
            $this->setLogger($oManager->getLogger());
        }

        return $this->_oLogger;
    }

    /**
     * Returns an instance of the requested Validator
     *
     * @param $sValidator
     *
     * @return paypInstallmentsSoapValidator subclass of paypInstallmentsValidator
     */
    protected function _getValidator($sValidator)
    {
        return oxNew($sValidator);
    }

    /**
     * Returns an instance of the requested parser
     *
     * @param $sParser
     *
     * @return paypInstallmentsSoapParser subclass of paypInstallmentsParser
     */
    protected function _getParser($sParser)
    {
        return oxNew($sParser);
    }
}
