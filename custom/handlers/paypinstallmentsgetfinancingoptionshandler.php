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
class paypInstallmentsGetFinancingOptionsHandler extends paypInstallmentsHandlerBase
{

    /** @var  float */
    protected $_fAmount;
    /** @var  string */
    protected $_sCurrency;
    /** @var  string */
    protected $_sCountryCode;
    /** @var  paypInstallmentsFinancingOption[] */
    protected $qualifiedOptions;
    /** @var  paypInstallmentsFinancingOption */
    protected $unQualifiedOptions;

    /**
     * Constructor accepts mandatory fields.
     *
     * @param $fAmount
     * @param $sCurrency
     * @param $sCountryCode
     */
    public function __construct($fAmount, $sCurrency, $sCountryCode)
    {
        $this->setAmount($fAmount)
            ->setCurrency($sCurrency)
            ->setCountryCode($sCountryCode);
    }

    /**
     * autheticate yourself, then ask paypal for potential financing options,
     * then extract these into an array and return it
     *
     * @return array
     */
    public function doRequest()
    {
        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsHandler doRequest"
        );
        /** @var paypInstallmentsGetFinancingOptionsParser $oParser */
        $oParser = oxNew('paypInstallmentsGetFinancingOptionsParser');
        /** @var paypInstallmentsGetFinancingOptionsValidator $oValidator */
        $oValidator = oxNew('paypInstallmentsGetFinancingOptionsValidator');
        $oValidator->setParser($oParser);

        $oLogger = $this->getLogger();
        $oValidator->setLogger($oLogger);

        $oValidator->validateFinancingOptionsArguments($this->getAmount(), $this->getCurrency(), $this->getCountryCode());

        $oConfig = $this->_getConfig();
        $sAuthToken = $this->authenticate($oConfig);
        $sUrl = $oConfig->getRestFinancingOptionsRequestUrl();
        $aHttpHeader =
            array(
                'Authorization: Bearer ' . $sAuthToken,
                'Accept: application/json',
                'Content-Type: application/json'
            );
        $aRequestPayload = $this->_prepareFinancingOptionsRequestPayload(
            $this->getAmount(),
            $this->getCurrency(),
            $this->getCountryCode()
        );

        $curl = curl_init($sUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $aHttpHeader);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $aRequestPayload);

        $oLogger->info(
            "paypInstallmentsGetFinancingOptionsHandler doRequest request",
            array(
                'EndpointUrl' => $sUrl,
                'HttpHeader'  => $aHttpHeader,
                'Payload'     => $aRequestPayload
            )
        );
        $oResponse = $this->_performCurlRequest($curl);
        $aRequestInformation = $this->_getCurlRequestInformation($curl);
        $oLogger->info(
            "paypInstallmentsGetFinancingOptionsHandler doRequest response",
            array("response" => $oResponse)
        );

        $oParser->setRequestInformation($aRequestInformation);
        $oValidator->validateFinancingOptionsResponse();

        $oParser->setAmount($this->getAmount());
        $oParser->setResponse($oResponse);


        $this->setQualifiedOptions($oParser->extractFinancingOptions());
        $this->setUnQualifiedOptions($oParser->extractFinancingOptionsNotQualified());

        return $this->getQualifiedOptions();
    }

    /**
     * use the data from the passed Configuration to authenticate with
     * PayPal and return the authentication token in case of success
     *
     * @param paypInstallmentsConfiguration $oConfig
     *
     * @return string
     */
    public function authenticate($oConfig)
    {
        $this->getLogger()->info("paypInstallmentsGetFinancingOptionsHandler authenticate", array());

        $sUrl = $oConfig->getRestAuthenticationUrl();
        $sAuthenticationToken = $oConfig->getPayPalRestAuthenticationToken();
        $sRequestPayload = 'grant_type=client_credentials';

        $curl = curl_init($sUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept-Language: en_US'));
        curl_setopt($curl, CURLOPT_USERPWD, $sAuthenticationToken);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sRequestPayload);

        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsHandler authenticate request",
            array(
                'EndpointUrl'         => $sUrl,
                'AuthenticationToken' => $sAuthenticationToken,
                'Payload'             => $sRequestPayload
            )
        );

        $oResponse = $this->_performCurlRequest($curl);
        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsHandler authenticate response",
            array("response" => $oResponse)
        );
        $aRequestInformation = $this->_getCurlRequestInformation($curl);

        $oParser = new paypInstallmentsGetFinancingOptionsParser();
        $oParser->setResponse($oResponse);
        $oParser->setRequestInformation($aRequestInformation);

        $oValidator = new paypInstallmentsGetFinancingOptionsValidator();
        $oValidator->setParser($oParser);
        $oValidator->setLogger($this->getLogger());

        $oValidator->validateAuthenticationResponse();

        return $oParser->getAccessToken();
    }

    /**
     * BlIsAdmin getter.
     *
     * @return boolean
     */
    public static function isBlIsAdmin()
    {
        return self::$_blIsAdmin;
    }

    /**
     * BlIsAdmin setter. Default value unSets attribute.
     *
     * @param boolean $blIsAdmin
     */
    public static function setBlIsAdmin($blIsAdmin = null)
    {
        self::$_blIsAdmin = $blIsAdmin;
    }

    /**
     * Amount getter.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_fAmount;
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
        $this->_fAmount = $amount;

        return $this;
    }

    /**
     * CountryCode getter.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->_sCountryCode;
    }

    /**
     * CountryCode setter. Default value unSets attribute. Method chain supported.
     *
     * @param string $countryCode
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
        return $this->_sCurrency;
    }


    /**
     * Currency setter. Default value unSets attribute. Method chain supported.
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency = null)
    {
        $this->_sCurrency = $currency;

        return $this;
    }


    /**
     * QualifiedOptions getter.
     *
     * @return paypInstallmentsFinancingOption[]
     */
    public function getQualifiedOptions()
    {
        return $this->qualifiedOptions;
    }

    /**
     * QualifiedOptions setter. Default value unSets attribute. Method chain supported.
     *
     * @param paypInstallmentsFinancingOption[] $qualifiedOptions
     *
     * @return $this
     */
    public function setQualifiedOptions(array $qualifiedOptions = null)
    {
        $this->qualifiedOptions = $qualifiedOptions;

        return $this;
    }


    /**
     * UnQualifiedOptions getter.
     *
     * @return paypInstallmentsFinancingOption[]
     */
    public function getUnQualifiedOptions()
    {
        return $this->unQualifiedOptions;
    }

    /**
     * UnQualifiedOptions setter. Default value unSets attribute. Method chain supported.
     *
     * @param paypInstallmentsFinancingOption[] $unQualifiedOptions
     *
     * @return $this
     */
    public function setUnQualifiedOptions(array $unQualifiedOptions = null)
    {
        $this->unQualifiedOptions = $unQualifiedOptions;

        return $this;
    }


    /**
     * create a new Config object and return it
     *
     * @return paypInstallmentsConfiguration
     */
    protected function _getConfig()
    {
        $oConfig = new paypInstallmentsConfiguration();

        return $oConfig;
    }

    /**
     * Create the json request data, for which we want to be offered financing options
     *
     * @param $fAmount
     * @param $sCurrency
     * @param $sCountryCode
     *
     * @return string
     */
    protected function _prepareFinancingOptionsRequestPayload($fAmount, $sCurrency, $sCountryCode)
    {
        $sFormattedAmount = number_format($fAmount, 2, '.', '');
        $oRequestPayload = array(
            "transaction_amount"     => array(
                "value"         => $sFormattedAmount,
                "currency_code" => $sCurrency
            ),
            "financing_country_code" => $sCountryCode
        );
        $sRequestPayload = json_encode($oRequestPayload);

        return $sRequestPayload;
    }

    /**
     * Perform the prepared curl request and return the response
     *
     * @param $curl
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function _performCurlRequest($curl)
    {
        $sResponse = curl_exec($curl);
        $oResponse = json_decode($sResponse);

        return $oResponse;
    }

    /**
     * Return the RequestInformation for the passed curl request
     *
     * @param $curl
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function _getCurlRequestInformation($curl)
    {
        $aRequestInformation = curl_getinfo($curl);

        return $aRequestInformation;
    }
}
