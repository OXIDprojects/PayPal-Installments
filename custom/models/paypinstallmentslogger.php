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

/**
 * Class paypInstallmentsLogger
 *
 * The sole purpose of this class is to remove the context from info level logs,
 * if the log level is info.
 * That leads to the context appearing on the lower "debug" log level, where it is
 * necessary
 */
class paypInstallmentsLogger extends Monolog\Logger
{

    /**
     * since logger has no acces to the loglevel by default, we need to add
     * a property and a setter for that purpose
     *
     * @var string
     */
    protected $_sLogLevel;

    /**
     * Log level setter.
     *
     * @param $sLogLevel
     */
    public function setLogLevel($sLogLevel)
    {
        $this->_sLogLevel = $sLogLevel;
    }

    /**
     * For info we expect very compact record.
     *
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function info($message, array $context = array())
    {
        if ($this->_sLogLevel !== "INFO") {
            return $this->addRecord(static::INFO, $message, $context);
        }

        return $this->addRecord(static::INFO, $message, array());
    }
}
