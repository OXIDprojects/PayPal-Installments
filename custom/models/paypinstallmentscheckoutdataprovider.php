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
 * Class paypInstallmentsCheckoutDataProvider
 * Class to get all needed data from oxid basket object
 */
class paypInstallmentsCheckoutDataProvider extends oxSuperCfg implements \Psr\Log\LoggerAwareInterface
{


    /**
     * variable that holds the oxBasket object
     *
     * @var oxBasket
     */
    protected $_oBasket = null;

    /**
     * This will be set to the oxorder__ordernr
     *
     * @var string $_sInvoiceId
     */
    protected $_sInvoiceId;

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
     * sets basket object
     *
     * @param oxBasket $oBasket
     */
    public function setBasket(oxBasket $oBasket)
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * returns oxBasket object
     *
     * @return oxBasket
     */
    public function getBasket()
    {
        return $this->_oBasket;
    }

    /**
     * returns list of basket item data
     *
     * @return array
     */
    public function getBasketItemDataList()
    {
        $aBasketItemDataList = array();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        if ($oBasket) {
            foreach ($oBasket->getContents() as $oBasketItem) {
                //go through basket items and extract needed data
                $aBasketItemDataList[] = $this->_extractBasketItemInformation($oBasketItem);
            }
        }

        return $aBasketItemDataList;
    }

    /**
     * returns simple object of basket item that holds the following data:
     * - Name -> $oBasketItemData->sName
     * - Price -> $oBasketItemData->fPrice
     * - Currency -> $oBasketItemData->sCurrency
     * - Quantity -> $oBasketItemData->iQuantity
     * - ItemCategory -> $oBasketItemData->sItemCategory (Digital or Physical)
     *
     * @param oxBasketItem $oBasketItem
     *
     * @return StdClass $oBasketItemData
     */
    protected function _extractBasketItemInformation(oxBasketItem $oBasketItem)
    {
        //setting item name
        $sName = $oBasketItem->getTitle();

        //setting total brutto price. This is the total price of all items
        $oItemPrice = $oBasketItem->getPrice();
        $fItemPrice = $this->_getPrice($oItemPrice);

        // setting the item brutto price
        $oUnitPrice = $oBasketItem->getUnitPrice();
        $fUnitPrice = $this->_getPrice($oUnitPrice);

        //setting currency e.g. EUR
        $sCurrency = $this->getCurrency();

        //setting item quantity
        $iQuantity = $oBasketItem->getAmount();

        //setting item category
        $sItemCategory = ($this->_isPhysicalArticle($oBasketItem)) ? 'Physical' : 'Digital';

        $oBasketItemData = $this->getBasketItemData($sName, $fItemPrice, $fUnitPrice, $sCurrency, $iQuantity, $sItemCategory);

        return $oBasketItemData;
    }

    public function getBasketItemData($sName, $fItemPrice, $fUnitPrice, $sCurrency, $iQuantity, $sItemCategory = 'Physical')
    {
        $oLogger = $this->getLogger();

        $oBasketItemData = new StdClass();

        //setting item name
        $oBasketItemData->sName = $sName;

        //setting total brutto price. This is the total price of all items
        $oBasketItemData->fPrice = $fItemPrice;

        // setting the item brutto price
        $oBasketItemData->fUnitPrice = $fUnitPrice;
        //setting currency e.g. EUR
        $oBasketItemData->sCurrency = $sCurrency;

        //setting item quantity
        $oBasketItemData->iQuantity = $iQuantity;

        //sets item category
        $oBasketItemData->sItemCategory = $sItemCategory;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('oBasketItemData' => $oBasketItemData));

        return $oBasketItemData;
    }


    /**
     * returns simple object of shipping address that holds the following data:
     * - first name
     * - last name
     * - street
     * - additional info
     * - city
     * - state (if there is one)
     * - zip
     * - country
     *
     * @return StdClass
     */
    public function getShippingAddressData()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        //getting user
        /** @var oxUser $oUser */
        $oUser = $oBasket->getBasketUser();

        $oShippingAddress = $this->_getUserShippingAddress($oUser);

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('oShippingAddress' => $oShippingAddress));

        return $oShippingAddress;
    }

    /**
     * extracts needed data from oxUser object whether we need shipping or billing address
     *
     * @param oxUser $oUser
     *
     * @return StdClass
     */
    protected function _getUserShippingAddress(oxUser $oUser)
    {
        $oLogger = $this->getLogger();

        $oShippingAddress = new StdClass();
        $oAddressObject = null;
        $sAddressPrefix = '';
        $sStateIsoAlpha2 = null;

        $sAddressId = oxRegistry::getSession()->getVariable('deladrid');

        if ($sAddressId) {
            //there is a shipping address set
            $oAddress = oxNew('oxAddress');
            if ($oAddress->load($sAddressId)) {
                $oAddressObject = $oAddress;
                $sAddressPrefix = 'oxaddress';
            }
        } else {
            //billing address = shipping address
            $oAddressObject = $oUser;
            $sAddressPrefix = 'oxuser';
        }

        $oShippingAddress->sFirstname = $oAddressObject->{$sAddressPrefix . '__oxfname'}->value;
        $oShippingAddress->sLastname = $oAddressObject->{$sAddressPrefix . '__oxlname'}->value;
        $oShippingAddress->sStreet = $oAddressObject->{$sAddressPrefix . '__oxstreet'}->value . ' ' . $oAddressObject->{$sAddressPrefix . '__oxstreetnr'}->value;
        $oShippingAddress->sAddinfo = $oAddressObject->{$sAddressPrefix . '__oxaddinfo'}->value;
        $oShippingAddress->sCompany = $oAddressObject->{$sAddressPrefix . '__oxcompany'}->value;
        $oShippingAddress->sCity = $oAddressObject->{$sAddressPrefix . '__oxcity'}->value;

        if ($oAddressObject->{$sAddressPrefix . '__oxstateid'}->value) {
            //if there is some state id set, try to load isoalpha2 code
            $oState = oxNew('oxState');
            if ($oState->load($oAddressObject->{$sAddressPrefix . '__oxstateid'}->value)) {
                $sStateIsoAlpha2 = $oState->getFieldData('oxisoalpha2');
            }
        }
        $oShippingAddress->sState = $sStateIsoAlpha2;

        $oShippingAddress->sZip = $oAddressObject->{$sAddressPrefix . '__oxzip'}->value;

        //load country and return isoalpha2 code
        $oCountry = oxNew('oxcountry');
        if ($oCountry->load($oAddressObject->{$sAddressPrefix . '__oxcountryid'}->value)) {
            $oShippingAddress->sCountry = $oCountry->getFieldData('oxisoalpha2');
        }

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('oShippingAddress' => $oShippingAddress));

        return $oShippingAddress;
    }

    /**
     * returns trusted shops protection costs for basket
     *
     * @return float
     */
    public function getTSProtectionCosts()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        /** @var oxPrice $oPrice */
        $oPrice = $oBasket->getCosts('oxtsprotection');

        $fPrice = $oPrice instanceof oxPrice ? $this->_getPrice($oPrice) : 0;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * returns payment costs for basket
     *
     * @return float
     */
    public function getPaymentCosts()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        /** @var oxPrice $oPrice */
        $oPrice = $oBasket->getCosts('oxpayment');

        $fPrice = $oPrice instanceof oxPrice ? $this->_getPrice($oPrice) : 0;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * returns wrapping costs for basket
     *
     * @return float
     */
    public function getWrappingCosts()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        /** @var oxPrice $oPrice */
        $oPrice = $oBasket->getCosts('oxwrapping');

        $fPrice = $oPrice instanceof oxPrice ? $this->_getPrice($oPrice) : 0;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * returns giftcard costs for basket
     *
     * @return float
     */
    public function getGiftcardCosts()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        /** @var oxPrice $oPrice */
        $oPrice = $oBasket->getCosts('oxgiftcard');

        $fPrice = $oPrice instanceof oxPrice ? $this->_getPrice($oPrice) : 0;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * returns shipping costs for basket
     *
     * @return float
     */
    public function getShippingCosts()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        /** @var oxPrice $oPrice */
        $oPrice = $oBasket->getCosts('oxdelivery');

        $fPrice = $oPrice instanceof oxPrice ? $this->_getPrice($oPrice) : 0;

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * get currency (e.g. EUR) for all prices from shop
     *
     * @return mixed
     */
    public function getCurrency()
    {
        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        $oActCur = $oBasket->getBasketCurrency();

        return $oActCur->name;
    }

    /**
     * returns discount sum
     *
     * @return float|int
     */
    public function getTotalDiscountSum()
    {
        $oLogger = $this->getLogger();

        //getting current basket
        /** @var oxBasket $oBasket */
        $oBasket = $this->getBasket();

        $fDiscountSum = $oBasket->getTotalDiscountSum();

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fDiscountSum' => $fDiscountSum));

        return $fDiscountSum;
    }

    /**
     * returns total amount of all products in basket
     *
     * @return float
     */
    public function getItemTotal()
    {
        $oLogger = $this->getLogger();

        if ($this->_isPriceViewModeNetto()) {
            $fPrice = $this->getBasket()->getNettoSum();
        } else {
            $fPrice = $this->getBasket()->getBruttoSum();
        }

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * returns order total
     *
     * @return float|int
     */
    public function getOrderTotal()
    {
        $oLogger = $this->getLogger();

        $fPrice = $this->getBasket()->getPrice()->getPrice();

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fPrice' => $fPrice));

        return $fPrice;
    }

    /**
     * checks if product is physical or digital
     *
     * @param oxBasketItem $oBasketItem
     *
     * @return bool
     * @throws oxArticleException
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     */
    protected function _isPhysicalArticle(oxBasketItem $oBasketItem)
    {
        $oProduct = $oBasketItem->getArticle();

        $blPhysical = true;

        if ($oProduct->getFieldData('oxisdownloadable') || $oProduct->getFieldData('oxnonmaterial')) {
            $blPhysical = false;
        }

        return $blPhysical;
    }

    /**
     * calculate and return the handling total for our order
     *
     * @return float
     */
    public function getHandlingTotal()
    {
        $oLogger = $this->getLogger();

        $fHandlingTotal = $this->getPaymentCosts() +
                          $this->getGiftcardCosts() +
                          $this->getWrappingCosts();

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fHandlingTotal' => $fHandlingTotal));

        return $fHandlingTotal;
    }


    public function getBasketTotalVat()
    {
        $oLogger = $this->getLogger();

        $fBasketTotalVat = 0.0;
        if ($this->_isPriceViewModeNetto()) {
            $fBasketTotalVat = $this->getProductVatTotal() + $this->getCostsVatTotal();
        }

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fBasketTotalVat' => $fBasketTotalVat));

        return $fBasketTotalVat;
    }

    public function getProductVatTotal()
    {
        $oLogger = $this->getLogger();

        $fProductVatTotal = 0.0;
        $aProductVats = $this->getBasket()->getProductVats();
        foreach ($aProductVats as $sVat) {
            $fProductVatTotal += (float) str_replace(',', '.', $sVat);
        }

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fProductVatTotal' => $fProductVatTotal));

        return $fProductVatTotal;
    }

    public function getCostsVatTotal()
    {
        $oLogger = $this->getLogger();

        $fCostsVatTotal = 0.0;
        $aCosts = $this->getBasket()->getCosts();
        foreach ($aCosts as $oPrice) {
            if ($oPrice instanceof oxPrice) {
                $fCostsVatTotal += (float) $oPrice->getVatValue();
            }
        }

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fCostsVatTotal' => $fCostsVatTotal));

        return $fCostsVatTotal;
    }

    /**
     * calculate the shipping discount
     *
     * @return float
     */
    public function getShippingDiscount()
    {
        $oLogger = $this->getLogger();

        $fShippingDiscount = -1.0 * $this->getBasket()->getTotalDiscountSum();

        $oLogger->debug(__CLASS__ . ' ' . __FUNCTION__, array('fShippingDiscount' => $fShippingDiscount));

        return $fShippingDiscount;
    }


    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getInvoiceId()
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
     * All price objects in the basket should have the same price mode (brutto or netto) set and a call to getPrice
     * should return the correct (brutto or netto) price for all objects.
     * But there may be exceptions, if some 3rd party modules inject prices into the basket.
     * So be better handle this on or own here.
     *
     * @param oxPrice $oPrice
     *
     * @return float
     */
    protected function _getPrice(oxPrice $oPrice)
    {
        if ($this->_isPriceViewModeNetto()) {
            $fPrice = $oPrice->getNettoPrice();
        } else {
            $fPrice = $oPrice->getBruttoPrice();
        }

        return (float) $fPrice;
    }

    /**
     * Checks and return true if price view mode is netto
     *
     * @return bool
     */
    protected function _isPriceViewModeNetto()
    {
        $blResult = (bool) $this->getConfig()->getConfigParam('blShowNetPrice');
        $oUser = $this->getBasket()->getBasketUser();
        if ($oUser) {
            $blResult = $oUser->isPriceViewModeNetto();
        }

        return $blResult;
    }
}