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
 * Class paypInstallmentsOrder
 *
 * @see order
 *
 * Add some PayPal Installments specific business logic to order::render.
 * A PayPal SOAP API call is initiated in order::render, part of the return (financing details) is converted into
 * an object and this object is store in the session.
 *
 * Provides a getter for the financing details for the basket and the template.
 *
 */
class paypInstallmentsOrder extends paypInstallmentsOrder_parent implements Psr\Log\LoggerAwareInterface
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

    /**
     * @inheritdoc
     *
     *
     * @return mixed
     *
     * @throws paypInstallmentsException
     */
    public function render()
    {
        /**
         * If the REQUEST is a return from a PayPal Installments application, apply our business logic.
         * We will take the token received in the REQUEST and do a PayPal API GetExpressCheckout call.
         * The values received in the response of the call will be processed by the basket and the will also be displayed.
         */
        /** @var paypInstallmentsConfiguration $oModuleConfig */
        $oModuleConfig = oxNew('paypInstallmentsConfiguration');
        $blReturnedFromPayPal = (bool) oxRegistry::getConfig()->getRequestParameter($oModuleConfig->getPayPalInstallmentsSuccessParameter());
        if ($blReturnedFromPayPal) {
            try {
                /** @var paypInstallmentsOxSession $oSession */
                $oSession = $this->getSession();
                $oBasket = $oSession->getBasket();

                /** Validate the token. This will thrown an exception, if token is not valid */
                $sReceivedToken = $this->getConfig()->getRequestParameter('token');
                $this->_paypInstallments_validateToken($sReceivedToken);

                /** Do the request. This may throw exceptions */
                $oHandler = $this->_paypInstallments_getGetExpressCheckoutDetailsHandler($sReceivedToken);
                $oHandler->setBasket($oBasket);
                $aParsedResponseData = $oHandler->doRequest();
                if (!is_array($aParsedResponseData) || empty ($aParsedResponseData)) {
                    $this->_paypInstallments_throwInvalidResponseDataException();
                    // @codeCoverageIgnoreStart
                }
                // @codeCoverageIgnoreEnd

                /** Store the PayerId received in the response as it is needed in DoExpressCheckout*/
                $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalPayerIdKey, $aParsedResponseData['PayerId']);

                /** Convert return or the doRequest call into an object FinancingDetails  */
                $oFinancingDetails = $this->_paypInstallments_getFinancingDetailsFromResponseData($aParsedResponseData);
                /** And store it in the session so it can be retrieved later by the basket and the template getter */
                $oSession->paypInstallmentsSetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey, $oFinancingDetails);

                /**
                 * Calculate the basket, so that the financing fee gets displayed and
                 * included in the Total Products Gross on checkout step 4.
                 *
                 * @see \paypinstallmentsoxbasket::_calcTotalPrice
                 */
                $oBasket->calculateBasket(true);
                $oSession->setBasket($oBasket);
            } catch (Exception $oEx) {
                $oLogger = $this->getLogger();
                $sMessage = __CLASS__ . '::' . __FUNCTION__ . ' An exception was caught. See EXCEPTION_LOG.txt for details';
                $oLogger->error($sMessage, array('exception' => $oEx));
                $this->_paypInstallments_handlePayPalInstallmentsDoRequestException($oEx);
            }
        }
        $mResult = $this->_paypInstallments_callRenderParent();

        return $mResult;
    }

    /**
     * Template getter.
     * Returns the details of the financing agreement.
     *
     * @return null|paypInstallmentsFinancingDetails
     */
    public function paypInstallments_getFinancingDetailsFromSession()
    {
        /** @var paypInstallmentsOxSession $oSession */
        $oSession = $this->getSession();

        return $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey);
    }

    /**
     * Template getter.
     * Returns the details of the financing agreement.
     *
     * @return array
     */
    public function paypInstallments_getFinancingOptionsRenderData()
    {
        $aRenderData = array();

        $oLang = oxRegistry::getLang();
        /** @var paypInstallmentsFinancingDetails $oFinancingOptions */
        $oFinancingOptions = $this->paypInstallments_getFinancingDetailsFromSession();
        if ($oFinancingOptions) {
            $aRenderData = array(
                $oFinancingOptions->getFinancingTerm(),
                $oLang->formatCurrency($oFinancingOptions->getFinancingMonthlyPayment()->getPrice()),
                $oFinancingOptions->getFinancingCurrency(),
            );
        }

        return $aRenderData;
    }

    /**
     * Get a filled financing details object.
     * This translates the API call response data in OXID template compliant objects
     *
     * @param $aResponseData
     *
     * @return paypInstallmentsFinancingDetails
     */
    protected function _paypInstallments_getFinancingDetailsFromResponseData($aResponseData)
    {
        $oFinancingDetails = oxNew('paypInstallmentsFinancingDetails');
        /** We assume here that all the amounts returned in the response will be in the same currency */
        $oFinancingDetails->setFinancingCurrency($aResponseData['FinancingFeeAmountCurrency']);
        $oFinancingDetails->setFinancingFeeAmount($aResponseData['FinancingFeeAmountValue']);
        $oFinancingDetails->setFinancingMonthlyPayment($aResponseData['FinancingMonthlyPaymentValue']);
        $oFinancingDetails->setFinancingTerm($aResponseData['FinancingTerm']);
        $oFinancingDetails->setFinancingTotalCost($aResponseData['FinancingTotalCostValue']);

        return $oFinancingDetails;
    }

    /**
     * Validate the token received in the REQUEST.
     * It must not be empty and the same that we stored in a former request.
     *
     * @param $sActualToken
     *
     * @throws paypInstallmentsGetExpressCheckoutValidationException
     */
    protected function _paypInstallments_validateToken($sActualToken)
    {
        $blTokenIsInvalid = false;

        /**
         * The token is invalid, if it is empty
         */
        if (empty($sActualToken)) {
            $blTokenIsInvalid = true;
        }

        /**
         * The token is invalid, if it is not the same as the token stored in the session.
         */
        $sExpectedToken = $this->_paypInstallments_getPayPalTokenFromSession();

        if ($sExpectedToken != $sActualToken) {
            $blTokenIsInvalid = true;
        }

        if ($blTokenIsInvalid) {
            $this->_paypInstallments_throwInvalidTokenException();
        }
    }

    /**
     * Central handler for all exception caught in this class.
     *
     * @param $oEx
     */
    protected function _paypInstallments_handlePayPalInstallmentsDoRequestException($oEx)
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
            case $oEx instanceof paypInstallmentsGetExpressCheckoutDetailsValidationException:

                /** Wrong SDK Version. Someone messed arround with the SDK */
            case $oEx instanceof paypInstallmentsVersionMismatchException:

                /** PayPal did not accept the request, see the error messages from PayPal for reason */
            case $oEx instanceof paypInstallmentsNoAckSuccessException:

                /**
                 * At the moment the default action is taken for all exceptions:
                 * An error message is displayed on the payment page
                 */
            default:
                if ($oEx instanceof oxException) {
                    $oEx->setMessage($sMessage);
                }
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
        }
    }

    // @codeCoverageIgnoreStart

    /**
     * This section holds the function which are excluded from code coverage as they are untestable or just
     * simple helper function to make code testing possible at all.
     */

    /**
     * @inheritdoc
     *
     * Calls the parent render method.
     * Needed for testing.
     *
     * @return mixed
     */
    protected function _paypInstallments_callRenderParent()
    {
        return parent::render();
    }

    /**
     * Return PayPal Token stored in session.
     *
     * @return mixed|null
     */
    protected function _paypInstallments_getPayPalTokenFromSession()
    {
        /** @var paypInstallmentsOxSession $oSession */
        $sExpectedToken = $this->getSession()->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalTokenKey);

        return $sExpectedToken;
    }

    /**
     * Get an instance of paypInstallmentsGetExpressCheckoutDetailsHandler.
     * Needed for testing.
     *
     * @return paypInstallmentsGetExpressCheckoutDetailsHandler
     */
    protected function _paypInstallments_getGetExpressCheckoutDetailsHandler($sReceivedToken)
    {
        $oHandler = oxNew('paypInstallmentsGetExpressCheckoutDetailsHandler', array($sReceivedToken));

        return $oHandler;
    }

    /**
     * Throw an exception.
     * Needed for testing.
     *
     * @throws paypInstallmentsGetExpressCheckoutValidationException
     */
    protected function _paypInstallments_throwInvalidTokenException()
    {
        /** @var paypinstallmentsgetexpresscheckoutvalidationexception $oEx */
        $oEx = oxNew('paypinstallmentsgetexpresscheckoutvalidationexception');
        $oEx->setMessage(paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_TOKEN'));

        throw $oEx;
    }

    /**
     * Throw an exception.
     * Needed for testing.
     *
     * @throws paypInstallmentsGetExpressCheckoutValidationException
     */
    protected function _paypInstallments_throwInvalidResponseDataException()
    {
        /** @var paypInstallmentsMalformedResponseException $oEx */
        $oEx = oxNew('paypInstallmentsMalformedResponseException');
        $oEx->setMessage(paypInstallmentsConfiguration::getValidationErrorMessage('INVALID_RESPONSE_DATA'));

        throw $oEx;
    }
}
