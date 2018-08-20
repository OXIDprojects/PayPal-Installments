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
 * Class paypInstallmentsConfiguration
 *
 * Provides getters for central configuration parameters of the module.
 *
 * Add the documentation to the property and not to the getter method.
 */
class paypInstallmentsConfiguration extends oxSuperCfg
{

    /**
     * @var string OXID of the PayPal installments method
     */
    protected static $sPaymentId = 'paypinstallments';

    /**
     * @var float Minimum amount for an installments order total
     */
    protected static $fPaymentMethodMinAmount = 99.0;

    /**
     * @var float Maximum amount for an installments order total
     */
    protected static $fPaymentMethodMaxAmount = 5000.0;

    /**
     * @var string Value that identifies a successful response to the PayPal API
     */
    protected static $sResponseAckSuccess = 'Success';

    /**
     * @var string Required PayPal API version. This value will be transmitted in each request and has to be validated
         * Oxid-Module v1.0.1 = 124.0  v2.0.0 = 204.0
     */
    protected static $sServiceVersion = '204.0';

    /**
     * @var string Error string for wrong service version
     */
    protected static $sErrorWrongServiceVersion = 'PAYP_ERR_VALIDATION_WRONG_SERVICE_VERSION';

    /**
     * @var string Prefix for all parse errors
     */
    protected static $sParseErrorMessagePrefix = 'PAYP_ERR_PARSE_';

    /**
     * @var string Prefix for all validation errors
     */
    protected static $sValidationErrorMessagePrefix = 'PAYP_ERR_VALIDATION_';

    /**
     * @var string Shipping countries, which qualify for installments
     */
    protected static $sRequiredShippingCountry = 'DE';

    /**
     * @var string Landing page, which qualify for installments
     */
    protected $sRequiredLandingPage = 'Billing';

    /**
     * @var string Funding sources, which qualify for installments
     */
    protected $sRequiredFundingSource = 'Finance';

    /**
     * @var array Order currencies, which qualify for installments
     */
    protected $sRequiredOrderTotalCurrency = 'EUR';

    /**
     * @var string URL to redirect after Checkout step 3, if PayPal Installments is selected in sandbox mode
     */
    protected $sPayPalInstallmentsSandboxRedirectBaseUrl = 'https://www.sandbox.paypal.com/checkoutnow/2?token=';

    /**
     * @var string REST API Endpoint for sandbox mode
     */
    protected $sPayPalInstallmentsSandboxRestEndpointUrl = 'https://api.sandbox.paypal.com/';

    /**
     * @var string SOAP API Endpoint for sandbox mode
     */
    protected $sPayPalInstallmentsSandboxSoapApiEndpoint = 'https://api-3t.sandbox.paypal.com/2.0/';

    /**
     * @var string URL to redirect after Checkout step 3, if PayPal Installments is selected
     */
    protected $sPayPalInstallmentsRedirectBaseUrl = 'https://www.paypal.com/checkoutnow/2?token=';

    /**
     * @var string REST API Endpoint
     */
    protected $sPayPalInstallmentsRestEndpointUrl = 'https://api.paypal.com/';

    /**
     * @var string SOAP API Endpoint
     */
    protected $sPayPalInstallmentsSoapApiEndpoint = 'https://api-3t.paypal.com/2.0/';

    /**
     * @var int Error to be displayed on the payment.tpl page everything, that is bigger than 6 results in
     * MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT , if not overwritten in special block.
     */
    protected $iPayPalInstallmentsPaymentError = 99;


    protected $sRefundablePaymentStatus = 'Completed';

    /**
     * The string, which indicates that a payment has been completed
     *
     * @var string
     */
    protected $sPayPalInstallmentsPaymentCompletedStatus = 'Completed';

    /**
     * Term for refund type 'Full'
     */
    const sRefundTypeFull = 'Full';
    /**
     * Term for refund type 'Partial'
     */
    const sRefundTypePartial = 'Partial';
    /**
     * Term for refund type 'Other'
     */
    const sRefundTypeOther = 'Other';

    const sRefundAllowedStatus = 'Instant';
    /**
     * @var array Array of allowed refund types
     */
    protected $aAllowedRefundTypes = array(self::sRefundTypeFull, self::sRefundTypePartial);

    /** Code For PayPal Partner Program  */
    const sButtonSource = 'Oxid_Cart_Inst';

    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function getAllowedRefundTypes()
    {
        return $this->aAllowedRefundTypes;
    }

    /**
     * Parameter to be appended to the PayPal Installments ReturnURL
     *
     * @var string
     */
    protected $sPayPalInstallmentsSuccessParameter = 'paypinstallmentssuccess';

    /**
     * @return array
     */
    public function getRequiredOrderTotalCurrency()
    {
        return $this->sRequiredOrderTotalCurrency;
    }

    /**
     * @return string
     */
    public static function getRequiredShippingCountry()
    {
        return static::$sRequiredShippingCountry;
    }

    /**
     * @return string
     */
    public function getRequiredLandingPage()
    {
        return $this->sRequiredLandingPage;
    }

    /**
     * @return string
     */
    public function getRequiredFundingSource()
    {
        return $this->sRequiredFundingSource;
    }

    /**
     * Property getter
     *
     * @return string
     */
    public static function getPaymentId()
    {
        return self::$sPaymentId;
    }

    /**
     * Property getter
     *
     * @return float
     */
    public static function getPaymentMethodMinAmount()
    {
        return self::$fPaymentMethodMinAmount;
    }

    /**
     * Property getter
     *
     * @return float
     */
    public static function getPaymentMethodMaxAmount()
    {
        return self::$fPaymentMethodMaxAmount;
    }

    /**
     * Property getter
     *
     * @return string
     */
    public static function getResponseAckSuccess()
    {
        return self::$sResponseAckSuccess;
    }

    /**
     * Returns input string with a common prefix for parse error messages.
     *
     * @param string $sMessage
     *
     * @return string
     */
    public static function getParseErrorMessage($sMessage)
    {
        return self::$sParseErrorMessagePrefix . $sMessage;
    }

    /**
     * Returns input string with a common prefix for validation error messages.
     *
     * @param string $sMessage
     *
     * @return string
     */
    public static function getValidationErrorMessage($sMessage)
    {
        return self::$sValidationErrorMessagePrefix . $sMessage;
    }

    /**
     * Property getter
     *
     * @return string
     */
    public static function getServiceVersion()
    {
        return self::$sServiceVersion;
    }

    /**
     * Property getter
     *
     * @return string
     */
    public function getPayPalInstallmentsRedirectBaseUrl()
    {
        if ($this->isSandboxMode()) {
            $sRedirectBaseUrl = $this->sPayPalInstallmentsSandboxRedirectBaseUrl;
        } else {
            $sRedirectBaseUrl = $this->sPayPalInstallmentsRedirectBaseUrl;
        }

        return $sRedirectBaseUrl;
    }

    /**
     * Property getter
     *
     * @return string
     */
    public function getPayPalInstallmentsSoapApiEndpoint()
    {
        if ($this->isSandboxMode()) {
            $sSoapApiEndpoint = $this->sPayPalInstallmentsSandboxSoapApiEndpoint;
        } else {
            $sSoapApiEndpoint = $this->sPayPalInstallmentsSoapApiEndpoint;
        }

        return $sSoapApiEndpoint;
    }

    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return int
     */
    public function getPayPalInstallmentsPaymentError()
    {
        return $this->iPayPalInstallmentsPaymentError;
    }

    /**
     * Property getter
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getPayPalInstallmentsPaymentCompletedStatus()
    {
        return $this->sPayPalInstallmentsPaymentCompletedStatus;
    }


    /**
     * Returns an array whith the SOAP API Configuration to be used as parameter in PayPalAPIInterfaceServiceService.
     *
     * @return array
     */
    public function getSoapApiConfiguration()
    {
        $sLogFileDirectory = oxRegistry::getConfig()->getLogsDir();
        $sEnvironment = '';

        if ($this->isSandboxMode()) {
            $sEnvironment = 'SB';
        }
        $sApiUsername = oxRegistry::getConfig()->getConfigParam('paypInstallments' . $sEnvironment . 'SoapUsername');
        $sApiPassword = oxRegistry::getConfig()->getConfigParam('paypInstallments' . $sEnvironment . 'SoapPassword');
        $sApiSignature = oxRegistry::getConfig()->getConfigParam('paypInstallments' . $sEnvironment . 'SoapSignature');
        $sLogEnabled = oxRegistry::getConfig()->getConfigParam('paypInstallmentsLogging');
        $sLogFile = $sLogFileDirectory . oxRegistry::getConfig()->getConfigParam('paypInstallmentsLoggingFileSoap');
        $sLogLevel = oxRegistry::getConfig()->getConfigParam('paypInstallmentsLoggingLevelSoap');
        $sPayPalInstallmentsSoapApiEndpoint = $this->getPayPalInstallmentsSoapApiEndpoint();

        $aConfig = array(
            'mode'                         => strtolower($sEnvironment),
            'log.LogEnabled'               => $sLogEnabled,
            'log.FileName'                 => $sLogFile,
            'log.LogLevel'                 => $sLogLevel,
            'acct1.UserName'               => $sApiUsername,
            'acct1.Password'               => $sApiPassword,
            'acct1.Signature'              => $sApiSignature,
            'service.EndPoint.PayPalAPI' => $sPayPalInstallmentsSoapApiEndpoint,
            'service.EndPoint.PayPalAPIAA' => $sPayPalInstallmentsSoapApiEndpoint,
        );

        return $aConfig;
    }

    public function getCancelUrl()
    {
        $sBaseUrl = $this->getBaseUrl();
        $sCancelUrl = sprintf(
            '%scl=%s&%s=1',
            $sBaseUrl, 'payment', 'paypinstallmentscanceled'
        );

        return $sCancelUrl;
    }

    public function getReturnUrl()
    {
        $sBaseUrl = $this->getBaseUrl();
        $sReturnUrl = sprintf(
            '%scl=%s&%s=1&%s=%s',
            $sBaseUrl, 'order', $this->sPayPalInstallmentsSuccessParameter,
            'force_paymentid', self::$sPaymentId
        );

        return $sReturnUrl;
    }

    public function getPayPalInstallmentsRedirectUrl($sToken)
    {
        return $this->getPayPalInstallmentsRedirectBaseUrl() . $sToken;
    }

    /**
     * Returns true if sandbox mode is enabled else returns false.
     *
     * @return bool
     */
    public function isSandboxMode()
    {
        return (bool) oxRegistry::getConfig()->getConfigParam('paypInstallmentsSandboxApi');
    }

    /**
     * Property getter
     *
     * @return string
     */
    public function getPayPalInstallmentsSuccessParameter()
    {
        return $this->sPayPalInstallmentsSuccessParameter;
    }

    /**
     * Returns true if logging was turned on by the user
     *
     * @return boolean
     */
    public function isLoggingEnabled()
    {
        return $this->getConfig()->getConfigParam('paypInstallmentsLogging');
    }

    /**
     * Returns the path to the general log file
     *
     * @return string
     */
    public function getLogFilePath()
    {
        return $this->getConfig()->getConfigParam('paypInstallmentsLoggingFile');
    }

    /**
     * return the minimum log level for the general logger
     *
     * @return string
     */
    public function getLogLevel()
    {
        return $this->getConfig()->getConfigParam('paypInstallmentsLoggingLevel');
    }

    /**
     * return the endpoint url based on whether we use sandbox mode or not
     *
     * @return string
     */
    public function getRestEndpointUrl()
    {
        if ($this->isSandboxMode()) {
            $sRestEndpointUrl = $this->sPayPalInstallmentsSandboxRestEndpointUrl;
        } else {
            $sRestEndpointUrl = $this->sPayPalInstallmentsRestEndpointUrl;
        }

        return $sRestEndpointUrl;
    }

    /**
     * get the url for authentication with paypal via REST
     *
     * @return string
     */
    public function getRestAuthenticationUrl()
    {
        return $this->getRestEndpointUrl() . 'v1/oauth2/token';
    }

    /**
     * get the url, we need to request the financing options from
     *
     * @return string
     */
    public function getRestFinancingOptionsRequestUrl()
    {
        return $this->getRestEndpointUrl() . 'v1/credit/calculated-financing-options';
    }

    /**
     * get the rest client id, dependant on whether we are in sandbox mode
     *
     * @return string
     */
    public function getPayPalRestClientId()
    {
        if ($this->isSandboxMode()) {
            return $this->getConfig()->getConfigParam('paypInstallmentsSBRestClientId');
        } else {
            return $this->getConfig()->getConfigParam('paypInstallmentsRestClientId');
        }
    }

    /**
     * get the rest client secret
     *
     * @return string
     */
    public function getPayPalRestSecret()
    {
        if ($this->isSandboxMode()) {
            return $this->getConfig()->getConfigParam('paypInstallmentsSBRestSecret');
        } else {
            return $this->getConfig()->getConfigParam('paypInstallmentsRestSecret');
        }
    }

    /**
     * prepare an authentication token for use with pay pals rest api
     *
     * @return string
     */
    public function getPayPalRestAuthenticationToken()
    {
        return $this->getPayPalRestClientId() . ':' . $this->getPayPalRestSecret();
    }

    /**
     * Get refundable payment status.
     *
     * @return string
     */
    public function getRefundablePaymentStatus()
    {
        return $this->sRefundablePaymentStatus;
    }

    protected function getBaseUrl()
    {
        $sBaseUrl = oxRegistry::getConfig()->getShopHomeURL();

        return $sBaseUrl;
    }
}
