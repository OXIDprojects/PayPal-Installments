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
 * Class paypInstallmentsThankyou
 *
 * @inheritdoc
 *
 * @desc extends the OXID thankyou page controller to be able to display PayPal Installments specific information and do some logging
 */
class paypInstallmentsThankyou extends paypInstallmentsThankyou_parent implements \Psr\Log\LoggerAwareInterface
{

    /**
     * @var \Psr\Log\LoggerInterface | null
     */
    protected $_oLogger = null;

    /**
     * getter for logger
     *
     * @return \Psr\Log\LoggerInterface | Psr\Log\NullLogger
     */
    public function getLogger()
    {
        if ($this->_oLogger === null) {
            $oConfig = oxNew('paypInstallmentsConfiguration');
            $oLoggerManager = oxNew('paypInstallmentsLoggerManager', $oConfig);
            $this->setLogger($oLoggerManager->getLogger());
        }

        return $this->_oLogger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $oLogger
     *
     * @return void
     */
    public function setLogger(\Psr\Log\LoggerInterface $oLogger)
    {
        $this->_oLogger = $oLogger;
    }

    /**
     * @var $_paypInstallmentsFinancingDetails paypInstallmentsFinancingDetails
     */
    protected $_paypInstallmentsFinancingDetails;

    /**
     * @var $_paypInstallmentsBasket oxBasket
     */
    protected $_paypInstallmentsBasket;

    /**
     * @inheritdoc
     *
     * Stores some values from the PayPal Installments Registry for later use in template
     * and deletes PayPal Installments Registry afterwards.
     *
     * @return string
     * @throws oxException
     */
    public function render()
    {
        $oLogger = $this->getLogger();

        $blIsPayPalInstallmentaPayment = $this->_paypInstallments_isPayPalInstallmentsPayment();

        if ($blIsPayPalInstallmentaPayment) {
            $this->_paypInstallments_setPropertiesAndCleanup($oLogger);
        }

        $sTemplate = $this->_paypInstallments_callRenderParent();

        return $sTemplate;
    }

    /**
     * Property getter used in template
     *
     * @return paypInstallmentsFinancingDetails
     */
    public function paypInstallments_getFinancingDetails()
    {
        return $this->_paypInstallmentsFinancingDetails;
    }

    /**
     * Property setter
     *
     * @param paypInstallmentsFinancingDetails $oFinancingDetails
     */
    protected function _paypInstallments_setFinancingDetails(paypInstallmentsFinancingDetails $oFinancingDetails)
    {
        $this->_paypInstallmentsFinancingDetails = $oFinancingDetails;
    }

    /**
     * Property setter
     *
     * @param oxBasket $oBasket
     */
    public function paypInstallments_setBasket(oxBasket $oBasket)
    {
        $this->_paypInstallmentsBasket = $oBasket;
    }

    /**
     * Property getter
     *
     * @return oxBasket
     */
    public function paypInstallments_getBasket()
    {
        return $this->_paypInstallmentsBasket;
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
        $oFinancingOptions = $this->paypInstallments_getFinancingDetails();
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
     * Return true if the selected payment is PayPal Installments.
     *
     * @return bool
     */
    protected function _paypInstallments_isPayPalInstallmentsPayment()
    {
        $blIsPayPalInstallmentsPayment = false;

        /** Get the ID of the payment the user has selected */
        $sSelectedPaymentId = $this->_paypInstallments_getPaymentIdFromSession();

        /** Get ID of PayPal Installments payment method */
        $sPayPalInstallmentsPaymentId = paypInstallmentsConfiguration::getPaymentId();

        /**
         * If the user selected PayPal installments as payment method,
         * a call to PayPal API method DoExpressCheckout will be made.
         */
        if ($sSelectedPaymentId == $sPayPalInstallmentsPaymentId) {
            $blIsPayPalInstallmentsPayment = true;
        }

        return $blIsPayPalInstallmentsPayment;
    }

    /**
     * Set class properties and delete PayPal Registry from session
     *
     * @param \Psr\Log\LoggerInterface $oLogger
     */
    protected function _paypInstallments_setPropertiesAndCleanup(\Psr\Log\LoggerInterface $oLogger)
    {
        $oBasket = $this->_paypInstallments_getBasketFromSession();
        if ($oBasket instanceof oxBasket) {
            $this->paypInstallments_setBasket($oBasket);
        } else {
            $sMessage = 'PAYP_INSTALLMENTS_ERROR_SESSION_LOST_BASKET';
            oxRegistry::get("oxUtilsView")->addErrorToDisplay($sMessage);
            $oLogger->error($sMessage, array('session' => $this->getSession()));
        }

        $oFinancingDetails = $this->_paypInstallments_getFinancingDetailsFromSession();
        if ($oFinancingDetails instanceof paypInstallmentsFinancingDetails) {
            $this->_paypInstallments_setFinancingDetails($oFinancingDetails);
        } else {
            $sMessage = 'PAYP_INSTALLMENTS_ERROR_SESSION_LOST_FINACING_DETAILS';
            oxRegistry::get("oxUtilsView")->addErrorToDisplay($sMessage);
            $oLogger->error($sMessage, array('session' => $this->getSession()));
        }

        /**
         * Delete PayPal Registry from session here and reset the PayPal Installments application process
         */
        $this->_paypInstallments_deletePayPalRegistryFromSession();
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
        $sTemplate = parent::render();

        return $sTemplate;
    }

    /**
     * @return array
     */
    protected function _paypInstallments_getFinancingDetailsFromSession()
    {
        $oSession = $this->getSession();
        $oFinancingDetails = $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sPayPalFinancingDetailsKey);

        return $oFinancingDetails;
    }

    /**
     *
     */
    protected function _paypInstallments_deletePayPalRegistryFromSession()
    {
        $oSession = $this->getSession();
        $oSession->paypInstallmentsDeletePayPalInstallmentsRegistry();
    }

    /**
     * @return mixed|null
     */
    protected function _paypInstallments_getBasketFromSession()
    {
        /** @var paypInstallmentsOxSession $oSession */
        $oSession = $this->getSession();
        $oBasket = $oSession->paypInstallmentsGetPayPalInstallmentsRegistryValueByKey(paypInstallmentsOxSession::sBasketKey);

        return $oBasket;
    }
}