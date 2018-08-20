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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class paypInstallmentsLoggerManager extends oxSuperCfg
{

    /**
     * @var Monolog\Logger
     */
    protected $_oLogger;

    /**
     * @var paypInstallmentsConfiguration
     */
    protected $_oPayPalConfig;

    /**
     * prepare a logger according to the configuration in the module backend
     *
     * @param $oConfig paypInstallmentsConfiguration
     */
    public function __construct($oConfig)
    {
        $this->_oPayPalConfig = $oConfig;
    }

    /**
     * return the configured logger
     *
     * @return Monolog\Logger
     */
    public function getLogger()
    {
        if (is_null($this->_oLogger)) {
            $this->setLogger($this->_fetchLogger());
        }

        return $this->_oLogger;
    }

    /**
     * Logger setter. Method chain supported.
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return $this
     */
    public function setLogger(Psr\Log\LoggerInterface $oLogger)
    {
        $this->_oLogger = $oLogger;

        return $this;
    }

    /**
     * Get logger for the first time.
     *
     * @return paypInstallmentsLogger|\Psr\Log\NullLogger
     */
    protected function _fetchLogger()
    {
        if ($this->_oPayPalConfig->isLoggingEnabled()) {
            $oLogger = new paypInstallmentsLogger("paypInstallmentsLogger");

            $oLogLevel = $this->_getLogLevel($this->_oPayPalConfig);
            $oLogger->setLogLevel($this->_oPayPalConfig->getLogLevel());

            $sLogDir = oxRegistry::getConfig()->getLogsDir();
            $sLogFile = $sLogDir . $this->_oPayPalConfig->getLogFilePath();
            if (defined('OXID_PHP_UNIT')) {
                $sLogFile = '/dev/null';
            }

            $oLogger->pushHandler(new StreamHandler($sLogFile, $oLogLevel));
        } else {
            $oLogger = new Psr\Log\NullLogger();
        }

        return $oLogger;
    }

    /**
     * get the monolog log level that is equivalent to the selected option
     *
     * @param paypInstallmentsConfiguration $oConfig
     *
     * @return int
     */
    protected function _getLogLevel(paypInstallmentsConfiguration $oConfig)
    {
        $sLogLevel = $oConfig->getLogLevel();
        $oLogLevel = Logger::DEBUG;
        switch ($sLogLevel) {
            case "DEBUG":
                $oLogLevel = Logger::DEBUG;
                break;
            case "INFO":
                $oLogLevel = Logger::INFO;
                break;
            case "WARN":
                $oLogLevel = Logger::WARNING;
                break;
            case "ERROR":
                $oLogLevel = Logger::ERROR;
                break;
        }

        return $oLogLevel;
    }
}
