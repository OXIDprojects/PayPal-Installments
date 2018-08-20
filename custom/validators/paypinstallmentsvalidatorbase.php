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
 * Class paypInstallmentsValidatorBase
 *
 * @desc attributes (logger, configuration) common for all validators of module.
 *
 * @todo introduce setter/getter of parser interface.
 */
abstract class paypInstallmentsValidatorBase implements \Psr\Log\LoggerAwareInterface
{

    /** @var Psr\Log\LoggerInterface */
    protected $_oLogger;

    /** @var  paypInstallmentsConfiguration */
    protected $_oConfiguration;


    /**
     * Configuration getter. If one is not set - fetch it.
     *
     * @return paypInstallmentsConfiguration
     */
    public function getConfiguration()
    {
        if (is_null($this->_oConfiguration)) {
            $this->setConfiguration($this->_fetchConfiguration());
        }

        return $this->_oConfiguration;
    }

    /**
     * Configuration setter. Default value unSetts attribute. Method chain supported.
     *
     * @param paypInstallmentsConfiguration $oConfiguration
     *
     * @return $this
     */
    public function setConfiguration(paypInstallmentsConfiguration $oConfiguration = null)
    {
        $this->_oConfiguration = $oConfiguration;

        return $this;
    }


    /**
     * Setter for logger. Default value unSets logger. Method chain supported.
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $oLogger = null)
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
            return new Psr\Log\NullLogger();
        }

        return $this->_oLogger;
    }

    /**
     * Get configuration for the first time.
     *
     * @return paypInstallmentsConfiguration
     */
    protected function _fetchConfiguration()
    {
        return oxRegistry::get('paypInstallmentsConfiguration');
    }
}
