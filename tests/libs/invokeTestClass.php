<?php
/**
 * This file is part of OXID eSales PayPal Plus module.
 *
 * OXID eSales PayPal Plus module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal Plus module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal Plus module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

/**
 * Class invokeTestClass.
 * A dummy class for testing on objects
 */
class invokeTestClass
{

    public $sProperty = 'STR';

    public $iProperty = 0;

    public $aProperty = array('x' => 'y');


    public function getArray()
    {
        return array(0 => 'VAL', 1 => (object) array('key' => 'value'), 2 => array('field' => 1));
    }

    public function getSelf()
    {
        return $this;
    }

    public function getString($mArgument = null)
    {
        if (is_null($mArgument)) {
            return 'x';
        }

        return 'ARGUMENT';
    }

    public function getNull()
    {
        return null;
    }

    public function getByArgument($mArgument)
    {
        if ($mArgument === 'self') {
            return $this->getSelf();
        }

        return 'just value';
    }
}
