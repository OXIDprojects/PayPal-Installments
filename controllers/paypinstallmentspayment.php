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
 * Class paypInstallmentsPayment
 *
 * @see payment
 */
class paypInstallmentsPayment extends paypInstallmentsPayment_parent implements Psr\Log\LoggerAwareInterface
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

    protected $_oPaypInstallmentsPaymentList;

    /**
     * @inheritdoc
     *
     * Whenever we enter the payment page the PayPal specific session registry will be deleted.
     * There is no way to recycle an earlier Express Checkout, we will always start a new one.
     *
     */
    public function init()
    {

        $this->_paypInstallments_CallInitParent();

        /** @var paypInstallmentsOxSession $oSession */
        $oSession = $this->_paypInstallments_GetSession();
        /** Delete registry here */
        $oSession->paypInstallmentsDeletePayPalInstallmentsRegistry();
    }

    /**
     * @inheritdoc
     *
     * Additional PayPal Installments requirements are validated.
     * Payment method "PayPal Installments" is removed from _oPaymentList, if the requirements are not met.
     *
     * TODO This function is called several times and the code always gets executed. Try to cache or use different approach.
     *
     * @return object
     */
    public function getPaymentList()
    {
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        //load payment list by parent function
        $this->_paypInstallments_CallGetPaymentListParent();

        $this->_oPaypInstallmentsPaymentList = $this->_paypInstallments_GetPaymentList();

        /**
         * Unset the PayPal Installments from payment list, if the module is not activated in the module settings
         */
        $blIsModuleActive = oxRegistry::getConfig()->getConfigParam('paypInstallmentsActive');
        if (! $blIsModuleActive ) {
                unset($this->_oPaypInstallmentsPaymentList[$sPayPalInstallmentsPaymentId]);
        }

        /**
         * Validate the requirements in case PayPal Installments is within the list of available requirements.
         * If validation fails, PayPal Installments will be removed from the list.
         */
        if (isset($this->_oPaypInstallmentsPaymentList[$sPayPalInstallmentsPaymentId])) {
            /** @var oxBasket $oBasket Get the current basket */
            $oBasket = $this->_paypInstallments_GetBasketFromSession();

            /** @var oxUser $oUser Get the current user */
            $oUser = $oBasket->getBasketUser();

            try {
                $oPayPalInstallmentsRequirementsValidator = $this->_paypInstallments_GetRequirementsValidator();
                $oPayPalInstallmentsRequirementsValidator->setBasket($oBasket);
                $oPayPalInstallmentsRequirementsValidator->setUser($oUser);
                $oPayPalInstallmentsRequirementsValidator->validateRequirements();
            } catch (Exception $oEx) {
                $oLogger = $this->getLogger();
                $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' PayPal Installments Requirements are not met';
                $oLogger->info($sMessage, array('exception' => $oEx));
                $this->_paypInstallments_HandlePayPalInstallmentsRequirementsException($oEx);
            }
        }

        $this->_oPaymentList = $this->_oPaypInstallmentsPaymentList;

        return $this->_oPaymentList;
    }

    /**
     * @inheritdoc
     *
     * Call setExpressCheckout if selected payment method is PayPal Installments.
     *
     * @return mixed|null
     */
    public function validatePayment()
    {
        /** $mReturnValue is null, if parent validation did not pass, paymentid is set to session in parent::validatePayment */
        $mReturnValue = $this->_paypInstallments_CallValidatePaymentParent();

        /** Get the ID of the payment the user has selected */
        $sSelectedPaymentId = $this->_paypInstallments_getPaymentIdFromSession();

        /** Get ID of PayPal Installments payment method */
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        /**
         * If parent validation passed and the user selected PayPal installments as payment method,
         * a call to PayPal API method SetExpressCheckout will be made.
         */
        if (!is_null($mReturnValue) && $sSelectedPaymentId == $sPayPalInstallmentsPaymentId) {
            /**
             * Do the SetExpressCheckout API call and return the token.
             * If the call fails we will return.
             */
            $oBasket = $this->_paypInstallments_GetBasketFromSession();
            /**
             * Recalculate basket to update costs (e.g. Trusted Shops "tsprotection" )
             */
            $oBasket->calculateBasket(true);

            /**
             * NOTE:
             * A new order number is created on every \paypInstallmentsPayment::validatePayment().
             * if the current order is not finished, the orderNr is orphaned.
             */
            /** @var paypInstallmentsOxSession|oxSession $oSession */
            $oSession = $this->getSession();
            $sOrderNr = $oSession->paypInstallmentsGetOrderNr();
            try {
                $oPayPalInstallmentsSetExpressCheckoutHandler = $this->_paypInstallments_GetSetExpressCheckoutHandler();
                $oPayPalInstallmentsSetExpressCheckoutHandler->setBasket($oBasket);
                $oPayPalInstallmentsSetExpressCheckoutHandler->setInvoiceId($sOrderNr);
                $sToken = $oPayPalInstallmentsSetExpressCheckoutHandler->doRequest();

                /**
                 * Certain parameters of the checkout must not be changed from this point on.
                 * Store the important checkout related data in a session registry to be able to compare them
                 * afterwards.
                 */
                $this->_paypInstallments_StorePayPalInstallmentsDataInRegistry($sToken);
                /** Redirect to the PayPal installments application page */
                $this->_paypInstallments_RedirectToPayPal($sToken);
            } catch (Exception $oEx) {
                /**
                 * Handle all possible exceptions and set return value to null.
                 * This code is tested by testValidatePayment_callsPaHandlePayPalInstallmentsDoRequestException_onError,
                 * but coverage is not recognized by some reason.
                 */
                $oLogger = $this->getLogger();
                $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' An exception was caught. See EXCEPTION_LOG.txt for details';
                $oLogger->error($sMessage, array('exception' => $oEx));
                $this->_paypInstallments_HandlePayPalInstallmentsDoRequestException($oEx);
                $mReturnValue = null;
            }
        }

        return $mReturnValue;
    }

    /**
     * @inheritdoc
     *
     * Fallback to SESSION, if tsprotection cannot retrieved from REQUEST
     *
     * @return integer
     */
    public function getCheckedTsProductId()
    {
        /**
         * Call to parent::getCheckedTsProductId sets _sCheckedProductId from null to false,
         * if value for 'stsprotection' is not set in REQUEST
         */
        parent::getCheckedTsProductId();

        /** Fallback to SESSION, if variable is still not set */
        if ($this->_sCheckedProductId === false) {
            if ($sId = $this->getSession()->getVariable('stsprotection')) {
                $this->_sCheckedProductId = $sId;
            }
        }

        return $this->_sCheckedProductId;
    }

    /**
     * Helper method. supplies `country` to widget `paypInstallmentsPresentment`.
     * See `paypinstallments_change_payment.tpl`.
     *
     * @return string
     */
    public function getBillingCountryCode()
    {
        $sCountryId = $this->_paypInstallments_GetBasketFromSession()
            ->getBasketUser()
            ->getFieldData('oxcountryid');
        /** @var oxCountry $oCountry */
        $oCountry = $this->getCountryService();
        $oCountry->load($sCountryId);

        return (string) $oCountry->getFieldData('OXISOALPHA2');
    }

    /**
     * @return oxCountry
     */
    public function getCountryService()
    {
        return oxNew('oxCountry');
    }

    /**
     * Store important checkout related data in a session registry.
     *
     * @param $sToken
     */
    protected function _paypInstallments_StorePayPalInstallmentsDataInRegistry($sToken)
    {
        /**
         * Store the PayPal token in the session.
         *
         * @var paypInstallmentsOxSession $oSession
         */
        $oSession = $this->_paypInstallments_GetSession();
        $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey
        (
            paypInstallmentsOxSession::sPayPalTokenKey, $sToken
        );

        /**
         * Take a fingerprint of the basket and store it.
         * No further changes must be made to the cart items from now on.
         */
        /** @var paypInstallmentsOxBasket $oBasket */
        $oBasket = $this->_paypInstallments_GetBasketFromSession();
        $sBasketItemsFingerprint = $oBasket->paypInstallments_GetBasketItemsFingerprint();
        $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey
        (
            paypInstallmentsOxSession::sBasketFingerprintKey, $sBasketItemsFingerprint
        );

        /**
         * Store the basket as we need to access to its values on the thank you page
         */
        $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey
        (
            paypInstallmentsOxSession::sBasketKey, $oBasket
        );

        /**
         * Store the shipping country. It must not be changed
         */
        $oDataProvider = $this->_paypInstallments_GetCheckoutDataProvider();
        $oDataProvider->setBasket($oBasket);
        $oShippingAddress = $oDataProvider->getShippingAddressData();
        $sShippingCountry = $oShippingAddress->sCountry;
        $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey
        (
            paypInstallmentsOxSession::sShippingCountryKey, $sShippingCountry
        );
    }

    /**
     * Central function to handle the exceptions that may be thrown during requirement validation.
     *
     * @param $oEx
     */
    protected function _paypInstallments_HandlePayPalInstallmentsRequirementsException($oEx)
    {
        switch (true) {
            case $oEx instanceof paypInstallmentsInvalidBillingCountryException:
            case $oEx instanceof paypInstallmentsInvalidShippingCountryException:
            default:
                /**
                 * At the moment the default action is taken for all exceptions.
                 * The payment method will be removed from the payment list
                 */
                $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();
                unset($this->_oPaypInstallmentsPaymentList[$sPayPalInstallmentsPaymentId]);
        }
    }

    /**
     * Central function to handle the exceptions that may be thrown during request or response validation.
     *
     * @param $oEx
     */
    protected function _paypInstallments_HandlePayPalInstallmentsDoRequestException($oEx)
    {
        $sMessage = 'PAYP_INSTALLMENTS_GENERIC_EXCEPTION_MESSAGE';
        if ($oEx instanceof oxException) {
            $oEx->debugOut();
        }
        switch (true) {
            /** Parse Errors */
            case $oEx instanceof paypInstallmentsMalformedRequestException:
            case $oEx instanceof paypInstallmentsMalformedResponseException:

                /** Validation Errors. Request or Response are not valid  */
            case $oEx instanceof paypInstallmentsSetExpressCheckoutRequestValidationException:

                /** Wrong SDK Version. Someone messed arround with the SDK */
            case $oEx instanceof paypInstallmentsVersionMismatchException:

                /** PayPal did not accept the request, see the error messages from PayPal for reason */
            case $oEx instanceof paypInstallmentsNoAckSuccessException:

                /**
                 * At the moment the default action is taken for all exceptions:
                 * An error message is displayed on the payment page
                 */
            default:
                $oEx->setMessage($sMessage);
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
        }
    }

    /**
     * Return the classes _oPaymentList property.
     * Needed for mocking.
     *
     * @codeCoverageIgnore
     *
     * @return object
     */
    protected function _paypInstallments_GetPaymentList()
    {
        return $this->_oPaymentList;
    }

    /**
     * Call parent::init.
     * Needed for testing.
     *
     * @codeCoverageIgnore
     *
     * @return null|string
     */
    protected function _paypInstallments_CallInitParent()
    {
        parent::init();
    }

    /**
     * Call parent::validatePayment.
     * Needed for testing.
     *
     * @codeCoverageIgnore
     *
     * @return null|string
     */
    protected function _paypInstallments_CallValidatePaymentParent()
    {
        $mParentReturn = parent::validatePayment();

        return $mParentReturn;
    }

    /**
     * Call parent::getPaymentList
     * Needed for testing.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    protected function _paypInstallments_CallGetPaymentListParent()
    {
        parent::getPaymentList();
    }

    /**
     * Return an instance of oxSession.
     * Needed for mocking.
     *
     * @return oxSession|paypInstallmentsOxSession
     */
    protected function _paypInstallments_GetSession()
    {
        return $this->getSession();
    }

    /**
     * Return an instance of paypInstallmentsSetExpressCheckoutHandler.
     * Needed for mocking.
     *
     * @return paypInstallmentsSetExpressCheckoutHandler
     */
    protected function _paypInstallments_GetSetExpressCheckoutHandler()
    {
        return oxNew('paypInstallmentsSetExpressCheckoutHandler');
    }

    /**
     * Retrieve the basket from the session and return an instance of oxbasket.
     * Needed for mocking.
     *
     * @return oxbasket
     */
    protected function _paypInstallments_GetBasketFromSession()
    {
        return $this->_paypInstallments_GetSession()->getBasket();
    }

    /**
     * Return an instance of paypInstallmentsRequirementsValidator.
     * Needed for mocking.
     *
     * @return paypInstallmentsRequirementsValidator
     */
    protected function _paypInstallments_GetRequirementsValidator()
    {
        $oValidator = oxNew('paypInstallmentsRequirementsValidator');

        return $oValidator;
    }

    /**
     * Redirect to PayPal.
     * The redirection cannot be covered by unit tests, so we exclude this method from code coverage report.
     *
     * @codeCoverageIgnore
     *
     * @param $sToken
     */
    protected function _paypInstallments_RedirectToPayPal($sToken)
    {
        $oModuleConfig = oxNew('paypInstallmentsConfiguration');
        $sRedirectUrl = $oModuleConfig->getPayPalInstallmentsRedirectUrl($sToken);
        oxRegistry::getUtils()->redirect($sRedirectUrl, false);
    }

    /**
     * Return an instance of paypInstallmentsCheckoutDataProvider.
     * Needed for mocking.
     *
     * @return paypInstallmentsCheckoutDataProvider
     */
    protected function _paypInstallments_GetCheckoutDataProvider()
    {
        $oDataProvider = oxNew('paypInstallmentsCheckoutDataProvider');

        return $oDataProvider;
    }

    /**
     * Return the selected payment ID from the session.
     *
     * @return mixed
     */
    protected function _paypInstallments_getPaymentIdFromSession()
    {
        $oSession = $this->getSession();
        $sSelectedPaymentId = $oSession->getVariable('paymentid');

        return $sSelectedPaymentId;
    }
}
