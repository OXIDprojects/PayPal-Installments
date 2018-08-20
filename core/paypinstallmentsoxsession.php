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
 * Class paypInstallmentsOxSession
 *
 * This is a simple implementation of a registry stored inside the session of the users.
 * This registry holds the central parameters of the PayPal checkout process.
 * Store values using paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(<one or the class constants>, $value) and get them
 * using paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(<one or the class constants>).
 * These functions will throw an exception, if you don't
 */
class paypInstallmentsOxSession extends paypInstallmentsOxSession_parent
{

    /**
     * Name of the registry key
     */
    const aPayPalInstallmentsRegistryKey = 'paypinstallments';
    /**
     * Name of the token key
     */
    const sPayPalTokenKey = 'Token';
    /**
     * Name of the basket fingerprint key
     */
    const sBasketFingerprintKey = 'BasketFingerprint';
    /**
     * Name of the basket key
     */
    const sBasketKey = 'Basket';
    /**
     * Name of the billing country key
     */
    const sBillingCountryKey = 'BillingCountry';
    /**
     * Name of the shipping country key
     */
    const sShippingCountryKey = 'ShippingCountry';

    /**
     * Name of FinancingDetails key
     */
    const sPayPalFinancingDetailsKey = 'FinancingDetails';

    /**
     * Name of PayerId key
     */
    const sPayPalPayerIdKey = 'PayerId';

    /**
     * Name of the OrderNr key
     */
    const sPayPalOrderNrKey = 'OrderNr';

    /**
     * Array for storing all class constants.
     * This is used for validation.
     *
     * @var null
     */
    protected $constCacheArray = null;

    /**
     * Set a registry value.
     *
     * @param string $sKey Use one ot the class constants
     * @param mixed  $mValue
     *
     * @throws InvalidArgumentException if the key is not one of the class constants
     */
    public function paypInstallmentsSetPayPalInstallmentsRegistryValueByKey($sKey, $mValue)
    {
        if (!$this->_paypInstallmentsIsValidKeyName($sKey)) {
            $this->throwInvalidArgumentException('PAYP_ERR_INVALID_KEY');
        }
        $aRegistry = $this->_paypInstallmentsGetPayPalInstallmentsRegistry(true);
        $aRegistry[$sKey] = $mValue;
        $this->_paypInstallmentsSetPayPalInstallmentsRegistry($aRegistry);
    }

    /**
     * Get a registry value.
     *
     * @param string $sKey Use one ot the class constants
     *
     * @throws InvalidArgumentException if the key is not one of the class constants
     *
     * @return null|mixed Value of the registry key
     */
    public function paypInstallmentsGetPayPalInstallmentsRegistryValueByKey($sKey)
    {
        if (!$this->_paypInstallmentsIsValidKeyName($sKey)) {
            $this->throwInvalidArgumentException('PAYP_ERR_INVALID_KEY');
        }
        $aRegistry = $this->_paypInstallmentsGetPayPalInstallmentsRegistry();
        $mValue = isset ($aRegistry[$sKey]) ?
            $aRegistry[$sKey] : null;

        return $mValue;
    }

    /**
     * Delete a registry key.
     *
     * @param string $sKey Use one ot the class constants
     *
     * @throws InvalidArgumentException if the key is not one of the class constants
     *
     */
    public function paypInstallmentsDeletePayPalInstallmentsRegistryKey($sKey)
    {
        if (!$this->_paypInstallmentsIsValidKeyName($sKey)) {
            $this->throwInvalidArgumentException('PAYP_ERR_INVALID_KEY');
        }
        $aRegistry = $this->_paypInstallmentsGetPayPalInstallmentsRegistry();
        if (!is_null($aRegistry) && isset($aRegistry[$sKey])) {
            unset($aRegistry[$sKey]);
            $this->_paypInstallmentsSetPayPalInstallmentsRegistry($aRegistry);
        }
    }

    /**
     * Delete the whole registry
     *
     * @throws InvalidArgumentException if the key is not one of the class constants
     *
     */
    public function paypInstallmentsDeletePayPalInstallmentsRegistry()
    {
        $this->deleteVariable(self::aPayPalInstallmentsRegistryKey);
    }

    /**
     * Get the OrderNr from the session.
     * If there is not yet an OrderNr create one and stored it in the session.
     *
     * @return string|null
     */
    public function paypInstallmentsGetOrderNr()
    {
        $sOrderNr = $this->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(self::sPayPalOrderNrKey);
        if (empty($sOrderNr)) {
            $oOrder = oxNew('oxOrder');
            $sOrderNr = $oOrder->paypInstallments_getOrderNr();
            $this->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(self::sPayPalOrderNrKey, $sOrderNr);
        }

        return $this->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(self::sPayPalOrderNrKey);
    }

    /**
     * Initialize it registry by storing an empty array in the session.
     *
     */
    protected function _paypInstallmentsInitializePayPalInstallmentsRegistry()
    {
        $aPayPalInstallmentsRegistry = array();
        $this->setVariable(self::aPayPalInstallmentsRegistryKey, $aPayPalInstallmentsRegistry);
    }

    /**
     * Stores the registry in the session after having modified the registry.
     *
     * @param $aPayPalInstallmentsRegistry
     */
    protected function _paypInstallmentsSetPayPalInstallmentsRegistry($aPayPalInstallmentsRegistry)
    {
        $this->setVariable(self::aPayPalInstallmentsRegistryKey, $aPayPalInstallmentsRegistry);
    }

    /**
     * Retrieve the registry from the session.
     *
     * @return mixed
     */
    protected function _paypInstallmentsGetPayPalInstallmentsRegistry($blInititalize = false)
    {
        $aPayPalInstallmentsRegistry = $this->getVariable(self::aPayPalInstallmentsRegistryKey);
        if ($blInititalize && is_null($aPayPalInstallmentsRegistry)) {
            $this->_paypInstallmentsInitializePayPalInstallmentsRegistry();
        }

        return $aPayPalInstallmentsRegistry;
    }

    /**
     * Get the class constants in order to be able validate the keys passed to one of the class methods.
     *
     * @return mixed
     */
    protected function _paypInstallmentsGetConstants()
    {
        is_null($this->constCacheArray) and $this->constCacheArray = array();

        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, $this->constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            $this->constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return $this->constCacheArray[$calledClass];
    }

    /**
     * Validate a key  passed to one of the class methods.
     *
     * @param $sKey
     *
     * @return bool
     */
    protected function _paypInstallmentsIsValidKeyName($sKey)
    {
        $aConstants = $this->_paypInstallmentsGetConstants();

        $aConstantValues = array_values($aConstants);

        return in_array($sKey, $aConstantValues);
    }

    /**
     * Throw an InvalidArgumentException.
     *
     * @param $sMessage
     *
     * @throws InvalidArgumentException
     */
    protected function throwInvalidArgumentException($sMessage)
    {
        throw new InvalidArgumentException($sMessage);
    }
}
