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
 * Class paypInstallmentsPresentment
 *
 * @desc Widget shows info about PayPalInstallments. Mandatory parameters: amount, currency, country.
 */
class paypInstallmentsPresentment extends oxWidget implements \Psr\Log\LoggerAwareInterface
{

    /** Time to live of the cached presentment entries */
    const TTL = 86400; // 24 * 60 * 60;
    /** Parameter used for both widget and option render method */
    const PARAM_AMOUNT = 'amount';
    /** Parameter used for both widget and option render method */
    const PARAM_CURRENCY = 'currency';
    /** Parameter used for both widget and option render method */
    const PARAM_AMOUNTMAX = 'amountmax';
    /** Parameter used for both widget and option render method */
    const PARAM_CURRENCYMAX = 'currencymax';
    /** Parameter used for both widget and option render method */
    const PARAM_COUNTRY = 'country';
    /** Parameters used to store financing length in month */
    const PARAM_MONTHS = 'months';
    /** Parameter to store shop root url */
    const PARAM_ROOT_URL = 'root_url';


    const TEMPLATE_WIDGET =
        'widget/presentment/paypinstallmentspresentment.tpl';
    const TEMPLATE_QUALIFIED_OPTIONS =
        'widget/presentment/options/paypinstallmentsqualifiedoptions.tpl';
    const TEMPLATE_QUALIFIED_OPTIONS_SIMPLE =
        'widget/presentment/options/paypinstallmentsqualifiedoptionssimple.tpl';
    const TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS =
        'widget/presentment/options/paypinstallmentsmultiplequalifiedoptions.tpl';
    const TEMPLATE_UNQUALIFIED_OPTIONS =
        'widget/presentment/options/paypinstallmentsunqualifiedoptions.tpl';
    const TEMPLATE_ERROR =
        'widget/presentment/paypinstallmentserror.tpl'; //Any errors is hidden

    /** @var float Initial instalment amount. E.g. price of article, order cost... */
    protected $_dAmount;
    /** @var string Currency symbol. E.g.: EUR. */
    protected $_sCurrency;
    /** @var string Country code. E.g.: DE. */
    protected $_sCountryCode;
    /** @var paypInstallmentsGetFinancingOptionsHandler  Handler fetches options from PayPal. */
    protected $_oFinancingOptionsHandler;
    /** @var array data ready for templates */
    protected $_aRenderData = array();
    /** @var  paypInstallmentsPresentmentValidator */
    protected $_oValidator;
    /** @var null @var array of financing plans */
    protected $_aQualifiedOptions = null;
    /** @var array of bools which options are representative */
    protected $_aRepresentativeOptions = array();
    /** @var bool */
    protected $_isError = false;
    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $_oLogger;

    /**
     * Setter for logger. Method chain supported.
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @codeCodeCoverageIgnore
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
     * Render the template for the presentments.
     * Depending on the presence of the presentment in the cache the template get rendered in a different way.
     * If the presentment is present in the cache, the function getCachedPresentmentHtml() will be called in the template.
     *
     * If the presentment is NOT present in the cache, the template will get rendered to include a JavaScript widget,
     * which will call getCachedPresentmentHtml() asynchronously.
     *
     * In each case the same templates are used.
     *
     * @See module/payp/installments/views/widget/presentment/paypinstallmentspresentment.tpl
     */
    public function render()
    {
        $this->_sThisTemplate = static::TEMPLATE_WIDGET;
        $this->_aViewData['paypInstallmentsIsAjax'] = false;
        $this->_paypInstallmentPresentment_setIsCached(false);

        try {
            $this->setAmount($this->getViewParameter(static::PARAM_AMOUNT));
            $this->setCurrency($this->getViewParameter(static::PARAM_CURRENCY));
            $this->setCountryCode($this->getViewParameter(static::PARAM_COUNTRY));

            $sHash = md5($this->getAmount() . $this->getCurrency() . $this->getCountryCode());
            $aQualifiedOptions = $this->_getCache($sHash);
            if (!is_null($aQualifiedOptions)) {
                $this->_paypInstallmentPresentment_setIsCached(true);
            } else {
                $this->getValidator()->validate();

                $this->setRenderData(
                    array(
                        static::PARAM_AMOUNT   => $this->getAmount(),
                        static::PARAM_CURRENCY => $this->getCurrency(),
                        static::PARAM_COUNTRY  => $this->getCountryCode(),
                        static::PARAM_ROOT_URL => $this->getRootUrl()
                    )
                );
            }
        } catch (Exception $oEx) {
            $oLogger = $this->getLogger();
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' An exception was caught. See EXCEPTION_LOG.txt for details';
            $oLogger->error($sMessage, array('exception' => $oEx));
            if ($oEx instanceof oxException) {
                $oEx->debugOut();
            }

            $this->_sThisTemplate = static::TEMPLATE_ERROR;
        }

        return $this->paypInstallmentPresentment_render_parent();
    }

    /**
     * Get presentments from cache and return rendered template as a string
     * This method is called from the template rendered by render()
     * in case the presentment is found in the cache.
     *
     * in case the presentment is not in the cache.
     *
     * @return string
     */
    public function getCachedPresentmentHtml()
    {
        $sHash = md5($this->getAmount() . $this->getCurrency() . $this->getCountryCode());
        $oFinancingOption = $this->_getCache($sHash);
        $this->_fixObject($oFinancingOption);

        /** Each of the following function calls sets $this->_sThisTemplate  */
        if ($oFinancingOption instanceof paypInstallmentsFinancingOption) {
            $this->_prepareRenderDataOfQualifiedOptions($oFinancingOption);
        } else {
            $this->_prepareRenderDataOfUnQualifiedOptions();
        }

        $sCachedPresentmentHtml = $this->_paypInstallmentPresentment_getTemplateOutput($this->_sThisTemplate);

        return $sCachedPresentmentHtml;
    }

    /**
     * Get presentments from PayPal and return rendered template as a string.
     * This method is called form the templates AJAX from the JavaScript code rendered by render()
     * in case the presentment is not in the cache.
     *
     * Parameters `amount`, `currency`, `country` should be passed as request parameters.
     * Any errors is hidden
     *
     * @return null
     */
    public function getPresentmentHtml()
    {
        $this->_sThisTemplate = static::TEMPLATE_ERROR;
        $this->_aViewData['paypInstallmentsIsAjax'] = true;
        $oFinancingOptions = null;

        try {
            $this->setAmount($this->getConfig()->getRequestParameter(static::PARAM_AMOUNT))
                ->setCurrency($this->getConfig()->getRequestParameter(static::PARAM_CURRENCY))
                ->setCountryCode($this->getConfig()->getRequestParameter(static::PARAM_COUNTRY));

            $this->getValidator()->validate();

            $aQualifiedOptions = $this->getFinancingOptionsHandler()->doRequest();

            $iMinRate = 0;
            if (is_array($aQualifiedOptions) && !empty($aQualifiedOptions)) {
                for ($i = 0; $i < count($aQualifiedOptions); $i++) {
                    if ($aQualifiedOptions[$i]->getMonthlyPayment() < $aQualifiedOptions[$iMinRate]->getMonthlyPayment()) {
                        $iMinRate = $i;
                    }
                }
                $oFinancingOptions = $aQualifiedOptions[$iMinRate];
            }

            if ($oFinancingOptions instanceof paypInstallmentsFinancingOption ) {
                $sHash = md5($this->getAmount() . $this->getCurrency() . $this->getCountryCode());
                $this->_setCache($sHash, $aQualifiedOptions[$iMinRate]);

                $this->_prepareRenderDataOfQualifiedOptions($oFinancingOptions);
            } else {
                $this->_prepareRenderDataOfUnQualifiedOptions();
            }
        } catch (Exception $oEx) {
            $oLogger = $this->getLogger();
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' An exception was caught. See EXCEPTION_LOG.txt for details';
            $oLogger->error($sMessage, array('exception' => $oEx));
            if ($oEx instanceof oxException) {
                $oEx->debugOut();
            }
        }

        $this->_renderAndDisplay($this->_sThisTemplate);
    }

    /**
     * Get presentments from PayPal and return rendered template as a string.
     * This method is called form the templates AJAX from the JavaScript code rendered by render()
     * in case the presentment is not in the cache.
     *
     * Parameters `amount`, `currency`, `country` should be passed as request parameters.
     * Any errors is hidden
     *
     * @return null
     */
    public function getPresentmentInfoHtml()
    {
        $this->_isError = false;
        $this->_sThisTemplate = static::TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS;
        $this->_aViewData['paypInstallmentsIsAjax'] = true;
        $oFinancingOptions = null;

        try {
            $this->setAmount($this->getConfig()->getRequestParameter(static::PARAM_AMOUNT))
                ->setCurrency($this->getConfig()->getRequestParameter(static::PARAM_CURRENCY))
                ->setCountryCode($this->getConfig()->getRequestParameter(static::PARAM_COUNTRY));

            $this->getValidator()->validate();

            $aQualifiedOptions = $this->getFinancingOptionsHandler()->doRequest();
            $iMinRate = 0;

            if (is_array($aQualifiedOptions) && !empty($aQualifiedOptions)) {
                for ($i = 0; $i < count($aQualifiedOptions); $i++) {
                    if ($aQualifiedOptions[$i]->getMonthlyPayment() < $aQualifiedOptions[$iMinRate]->getMonthlyPayment()) {
                        $iMinRate = $i;
                    }
                }
                $oFinancingOptions = $aQualifiedOptions[$iMinRate];
            }

            if ($oFinancingOptions instanceof paypInstallmentsFinancingOption ) {
                $sHash = md5($this->getAmount() . $this->getCurrency() . $this->getCountryCode());
                $this->_setCache($sHash, $aQualifiedOptions[$iMinRate]);

                $this->_prepareRenderDataOfMultipleQualifiedOptions($aQualifiedOptions);
            } else {
                $this->_isError = true;
            }
        } catch (Exception $oEx) {
            $this->_isError = true;
            $oLogger = $this->getLogger();
            $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' An exception was caught. See EXCEPTION_LOG.txt for details';
            $oLogger->error($sMessage, array('exception' => $oEx));
            if ($oEx instanceof oxException) {
                $oEx->debugOut();
            }
        }

        $this->_renderAndDisplay($this->_sThisTemplate);
    }

    public function isError()
    {
        return $this->_isError;
    }

    /**
     * get financing plans
     *
     * @return array
     */
    public function getFinancingOptions()
    {
        if ($this->_aQualifiedOptions == null) {
            $this->setAmount($this->getConfig()->getRequestParameter(static::PARAM_AMOUNT))
                ->setCurrency($this->getConfig()->getRequestParameter(static::PARAM_CURRENCY))
                ->setCountryCode($this->getConfig()->getRequestParameter(static::PARAM_COUNTRY));

            $this->getValidator()->validate();

            $this->_aQualifiedOptions = $this->getFinancingOptionsHandler()->doRequest();
            $this->_markRepresentativeOptions();
        }
        return $this->_aQualifiedOptions;
    }

    protected function _markRepresentativeOptions()
    {
        $iMin = 0;
        $iMaxApr = 0;
        $blDifferingApr = false;
        foreach ($this->_aQualifiedOptions as $iOption => $oOption) {
            if ($oOption->getMonthlyPayment() < $this->_aQualifiedOptions[$iMin]->getMonthlyPayment()) {
                $iMin = $iOption;
            }
            if ($oOption->getAnnualPercentageRate() > $this->_aQualifiedOptions[$iMaxApr]->getAnnualPercentageRate()) {
                $iMaxApr = $iOption;
            }
            if ($iMaxApr > 0 || ($oOption->getAnnualPercentageRate() !== $this->_aQualifiedOptions[$iMaxApr]->getAnnualPercentageRate())) {
                $blDifferingApr = true;
            }
        }
        $this->_aRepresentativeOptions[$iMin] = true;
        if ($blDifferingApr) {
            $this->_aRepresentativeOptions[$iMaxApr] = true;
        }
    }

    /**
     * is plan iOption representative?
     *
     * @return bool
     */
    public function isOptionRepresentative($iOption)
    {
        return $this->_aRepresentativeOptions[$iOption - 1] == true; // iOption counts starting from 1
    }

    /**
     * RenderData getter.
     *
     * @return array
     */
    public function getRenderData()
    {
        return $this->_aRenderData;
    }

    /**
     * RenderData setter. Default value unSets attribute. Method chain supported.
     *
     * @param mixed $renderData
     *
     * @return $this
     */
    public function setRenderData(array $renderData = array())
    {
        $this->_aRenderData = array();
        foreach ($renderData as $key => $value) {
            $this->_pushRenderData($key, $value);
        }

        return $this;
    }

    /**
     * Are we in basket context?
     */
    public function isBasketController()
    {
        $sClass = oxRegistry::getConfig()->getRequestParameter('cl');
        return $sClass == 'basket';
    }

    /**
     * Amount getter.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_dAmount;
    }

    /**
     * Formated Amount getter.
     *
     * @return float
     */
    public function getFAmount()
    {
        return oxRegistry::getUtils()->fRound($this->_dAmount, $this->getConfig()->getActShopCurrencyObject());
    }

    /**
     * Amount setter. Default value unSets attribute. Method chain supported.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount = null)
    {
        $this->_dAmount = $amount;

        return $this;
    }

    /**
     * CountryCode getter.
     *
     * @return mixed
     */
    public function getCountryCode()
    {
        if (is_null($this->_sCountryCode)) {
            $this->setCountryCode($this->_fetchCountryCode());
        }

        return $this->_sCountryCode;
    }

    /**
     * CountryCode setter. Default value unSets attribute. Method chain supported.
     *
     * @param mixed $countryCode
     *
     * @return $this
     */
    public function setCountryCode($countryCode = null)
    {
        $this->_sCountryCode = $countryCode;

        return $this;
    }

    /**
     * Currency getter.
     *
     * @return string
     */
    public function getCurrency()
    {
        if (is_null($this->_sCurrency)) {
            $this->setCurrency($this->_fetchCurrency());
        }

        return $this->_sCurrency;
    }

    /**
     *  Returns active shop currency object.
     *
     * @return object
     */
    public function getCurrencyObject()
    {
        return $this->getConfig()->getActShopCurrencyObject();
    }


    /**
     * Currency setter. Default value unSets attribute. Method chain supported.
     *
     * @param mixed $currency
     *
     * @return $this
     */
    public function setCurrency($currency = null)
    {
        $this->_sCurrency = $currency;

        return $this;
    }

    /**
     * FinancingOptionsHandler getter.
     *
     * @return paypInstallmentsGetFinancingOptionsHandler
     */
    public function getFinancingOptionsHandler()
    {
        if (is_null($this->_oFinancingOptionsHandler)) {
            $this->setFinancingOptionsHandler($this->_buildFinancingOptionsHandler());
        }

        return $this->_oFinancingOptionsHandler;
    }

    /**
     * FinancingOptionsHandler setter. Default value unSets attribute. Method chain supported.
     *
     * @param paypInstallmentsGetFinancingOptionsHandler $financingOptionsHandler
     *
     * @return $this
     */
    public function setFinancingOptionsHandler(paypInstallmentsGetFinancingOptionsHandler $financingOptionsHandler = null)
    {
        $this->_oFinancingOptionsHandler = $financingOptionsHandler;

        return $this;
    }

    /**
     * Validator getter.
     *
     * @return paypInstallmentsPresentmentValidator
     */
    public function getValidator()
    {
        if (is_null($this->_oValidator)) {
            $this->setValidator($this->fetchValidator());
        }

        return $this->_oValidator;
    }

    /**
     * Validator setter. Default value unSets attribute. Method chain supported.
     *
     * @param paypInstallmentsPresentmentValidator $validator
     *
     * @return $this
     */
    public function setValidator(paypInstallmentsPresentmentValidator $validator = null)
    {
        $this->_oValidator = $validator;

        return $this;
    }

    /**
     * Get shop root url with slash at the end.
     *
     * @return string
     */
    public function getRootUrl()
    {
        return rtrim($this->getConfig()->getCurrentShopUrl(false), '/') . '/';
    }

    /**
     * Get FinancingOptionsHandler for the first time.
     *
     * @return paypInstallmentsGetFinancingOptionsHandler
     */
    protected function _buildFinancingOptionsHandler()
    {
        return oxNew(
            'paypInstallmentsGetFinancingOptionsHandler',
            $this->getAmount(),
            $this->getCurrency(),
            $this->getCountryCode()
        );
    }

    /**
     * Call parent render.
     *
     * @return string
     *
     * @codeCodeCoverageIgnore call parent render
     */
    protected function paypInstallmentPresentment_render_parent()
    {
        return parent::render();
    }

    /**
     * Get shop currency.
     *
     * @return string
     */
    protected function _fetchCurrency()
    {
        return $this->getConfig()->getActShopCurrencyObject()->name;
    }

    /**
     * Get shop country code. Returns upper cased string.
     *
     * @return string
     */
    protected function _fetchCountryCode()
    {
        return strtoupper($this->getConfig()->getShopConfVar('sShoppingCountry'));
    }

    /**
     * Collects Data required to display qualified options.
     *
     * @param paypInstallmentsFinancingOption $oFinancingOption
     *
     * @return $this
     */
    protected function _prepareRenderDataOfQualifiedOptions(paypInstallmentsFinancingOption $oFinancingOption)
    {
        $oLang = oxRegistry::getLang();

        if ($oFinancingOption->getAnnualPercentageRate() == 0) {
            $this->setRenderData(
            // monthlyPayment currency
                array(
                    $oLang->formatCurrency($oFinancingOption->getMonthlyPayment()),
                    '&euro;',
                )
            );

            $this->_sThisTemplate = static::TEMPLATE_QUALIFIED_OPTIONS_SIMPLE;

        } else {
            $this->setRenderData(
            // monthlyPayment currency term nominalRate AnnualPercentageRate Total
                array(
                    $oLang->formatCurrency($oFinancingOption->getMonthlyPayment()),
                    '&euro;',
                    $oFinancingOption->getNumMonthlyPayments(),
                    $oLang->formatCurrency($oFinancingOption->getAmount()),
                    '&euro;',
                    $oFinancingOption->getNominalRate(),
                    $oFinancingOption->getAnnualPercentageRate(),
                    $oLang->formatCurrency($oFinancingOption->getTotalPayment()),
                    '&euro;',
                    $oFinancingOption->getNumMonthlyPayments(),
                    $oLang->formatCurrency($oFinancingOption->getMonthlyPayment()),
                    '&euro;',
                )
            );

            $this->_sThisTemplate = static::TEMPLATE_QUALIFIED_OPTIONS;
        }

        return $this;
    }

    /**
     * Collects Data required to display multiple qualified options.
     *
     * @param array $aFinancingOptions paypInstallmentsFinancingOption $oFinancingOption
     *
     * @return $this
     */
    protected function _prepareRenderDataOfMultipleQualifiedOptions($aFinancingOptions )
    {
        $oFinancingOption = $aFinancingOptions[0];
        $oLang = oxRegistry::getLang();

        $this->setRenderData(
        // monthlyPayment currency term nominalRate AnnualPercentageRate Total
            array(
                $oLang->formatCurrency($oFinancingOption->getMonthlyPayment()),
                '&euro;',
                $oFinancingOption->getNumMonthlyPayments(),
                $oLang->formatCurrency($oFinancingOption->getAmount()),
                '&euro;',
                $oFinancingOption->getNominalRate(),
                $oFinancingOption->getAnnualPercentageRate(),
                $oLang->formatCurrency($oFinancingOption->getTotalPayment()),
                '&euro;',
                $oFinancingOption->getNumMonthlyPayments(),
                $oLang->formatCurrency($oFinancingOption->getMonthlyPayment()),
                '&euro;',
            )
        );

        $this->_sThisTemplate = static::TEMPLATE_MULTIPLE_QUALIFIED_OPTIONS;

        return $this;
    }

    /**
     * Collects Data required to display UnQualified options.
     */
    protected function _prepareRenderDataOfUnQualifiedOptions()
    {
        $oLang = oxRegistry::getLang();
        $fMinAmount = paypInstallmentsConfiguration::getPaymentMethodMinAmount();
        $fMaxAmount = paypInstallmentsConfiguration::getPaymentMethodMaxAmount();

        $this->setRenderData(
            array(
                static::PARAM_AMOUNT   => $oLang->formatCurrency($fMinAmount),
                static::PARAM_CURRENCY => '&euro;',
                static::PARAM_AMOUNTMAX   => $oLang->formatCurrency($fMaxAmount),
                static::PARAM_CURRENCYMAX => '&euro;',
            )
        );

        $this->_sThisTemplate = static::TEMPLATE_UNQUALIFIED_OPTIONS;

        return $this;
    }

    /**
     * Render and output financing options.
     *
     * @param $sThisTemplate
     *
     * @return null
     */
    protected function _renderAndDisplay($sThisTemplate)
    {
        return oxRegistry::getUtils()->showMessageAndExit(
            oxRegistry::get('oxUtilsView')->getTemplateOutput(
                $sThisTemplate,
                $this
            )
        );
    }

    /**
     * Get Validator for the first time.
     *
     * @return paypInstallmentsPresentmentValidator
     */
    protected function fetchValidator()
    {
        return oxNew('paypInstallmentsPresentmentValidator', $this);
    }


    /**
     * Push render data one by one. Method chain supported.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function _pushRenderData($key, $value)
    {
        $this->_aRenderData[$key] = $value;

        return $this;
    }

    /**
     * Get a value by hash from the cache
     *
     * @param $sHash
     *
     * @return null
     */
    protected function _getCache($sHash)
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $aCache = $oSession->getVariable('paypinstallments_presentments_cache') ? $oSession->getVariable('paypinstallments_presentments_cache') : array();
        $timestamp = $aCache[$sHash][0];
        $aQualifiedOptions = $aCache[$sHash][1];

        /** Invalidate the cached entry, if it is older than the TTL */
        if ((int) $timestamp < (time() - static::TTL)) {
            $aQualifiedOptions = null;
            unset($aCache[$sHash]);
            $oSession->setVariable('paypinstallments_presentments_cache', $aCache);
        }

        return $aQualifiedOptions;
    }

    /**
     * Store value in the cache
     *
     * @param $sHash
     * @param $aQualifiedOptions
     */
    protected function _setCache($sHash, $aQualifiedOptions)
    {
        /** @var paypInstallmentsOxSession|oxSession $oSession */
        $oSession = $this->getSession();
        $aCache = $oSession->getVariable('paypinstallments_presentments_cache') ? $oSession->getVariable('paypinstallments_presentments_cache') : array();
        $aCache[$sHash] = array(
            time(), $aQualifiedOptions
        );

        $oSession->setVariable('paypinstallments_presentments_cache', $aCache);
    }

    /**
     * Fix objects, which are not de-serialized right.
     *
     * @param $object
     *
     * @return mixed
     */
    protected function _fixObject(&$object)
    {
        if (!is_object($object) && gettype($object) == 'object')
            return ($object = unserialize(serialize($object)));

        return $object;
    }

    protected function _paypInstallmentPresentment_setIsCached($blIsCached)
    {
        $this->_aViewData['paypInstallmentsIsCached'] = $blIsCached;
    }

    /**
     * @return string
     */
    protected function _paypInstallmentPresentment_getTemplateOutput($sTemplate)
    {
        $sTemplateOutput = oxRegistry::get('oxUtilsView')->getTemplateOutput(
            $sTemplate,
            $this
        );

        return $sTemplateOutput;
    }
}
