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
 * The sole purpose of this class is to make a GetExpressCheckoutDetailsHandler Request to Paypal
 *
 * Class paypInstallmentsGetExpressCheckoutDetailsHandler
 */
class paypInstallmentsGetExpressCheckoutDetailsHandler extends paypInstallmentsHandlerBase
{

    /** @var  string */
    protected $_sAuthToken;

    /**
     * @var oxBasket
     */
    protected $oBasket;

    /**
     * @var paypInstallmentsCheckoutDataProvider
     */
    protected $_oDataProvider;


    /**
     * Pass mandatory parameter authToken during creation.
     *
     * @param string $sAuthToken
     */
    public function __construct($sAuthToken)
    {
        $this->setAuthToken($sAuthToken);
    }

    /**
     * setter for basket
     *
     * @codeCoverageIgnore
     *
     * @param $oBasket oxBasket
     */
    public function setBasket($oBasket)
    {
        $this->oBasket = $oBasket;
    }

    /**
     * getter for DataProvider
     *
     * @codeCoverageIgnore
     *
     * @return paypInstallmentsCheckoutDataProvider
     */
    public function getDataProvider()
    {
        return $this->_oDataProvider;
    }

    /**
     * Build up all the PayPal API Objects, perform the actual request, Then validate it and return the
     * parsed result.
     *
     * @return array
     *
     * @throws paypInstallmentsException
     */
    public function doRequest()
    {
        $oObjectGenerator = $this->prepareObjectGenerator();
        $oRequest = $oObjectGenerator->getGetExpressCheckoutReqObject($this->getAuthToken());
        $oService = $oObjectGenerator->getPayPalServiceObject();

        $oLogger = $this->getLogger();

        $oLogger->info("GetExpressCheckoutDetails  doRequest", array("request" => $oRequest));
        try {
            $oResponse = $oService->GetExpressCheckoutDetails($oRequest);
        } catch (Exception $oPayPalException) {
            $sMessage = $oPayPalException->getMessage();
            $oLogger->error("GetExpressCheckoutDetails doRequest", array("request" => $oRequest, "error" => $sMessage));
            /** @var paypInstallmentsException $oException */
            $oException = oxNew("paypInstallmentsException");
            $oException->setMessage($sMessage);
            throw $oException;
        }

        $oLogger->info("GetExpressCheckoutDetails doRequest", array("response" => $oResponse));

        $oValidator = oxNew("paypInstallmentsGetExpressCheckoutDetailsValidator");
        $oParser = oxNew("paypInstallmentsGetExpressCheckoutDetailsParser");
        $oParser->setLogger($oLogger);

        $oValidator->setLogger($oLogger);
        $oValidator->setParser($oParser);
        $oValidator->setDataProvider($this->getDataProvider());
        $oValidator->setRequest($oRequest);
        $oValidator->setResponse($oResponse);
        $oValidator->validateResponse();

        return array(
            'PayerId'                         => $oParser->getPayerId(),
            'FinancingFeeAmountValue'         => $oParser->getFinancingFeeAmountValue(),
            'FinancingFeeAmountCurrency'      => $oParser->getFinancingFeeAmountCurrency(),
            'FinancingMonthlyPaymentValue'    => $oParser->getFinancingMonthlyPaymentValue(),
            'FinancingMonthlyPaymentCurrency' => $oParser->getFinancingMonthlyPaymentCurrency(),
            'FinancingTotalCostValue'         => $oParser->getFinancingTotalCostValue(),
            'FinancingTotalCostCurrency'      => $oParser->getFinancingTotalCostCurrency(),
            'FinancingTerm'                   => $oParser->getFinancingTerm(),
        );
    }

    /**
     * AuthToken getter.
     *
     * @return string
     */
    public function getAuthToken()
    {
        return $this->_sAuthToken;
    }


    /**
     * AuthToken setter. Default value unSets attribute. Method chain supported.
     *
     * @param string $authToken
     *
     * @return $this
     */
    public function setAuthToken($authToken = null)
    {
        $this->_sAuthToken = $authToken;

        return $this;
    }

    /**
     * create an objectGenerator and configure it correctly
     *
     * @return paypInstallmentsSdkObjectGenerator
     */
    protected function prepareObjectGenerator()
    {
        $oObjectGenerator = oxNew("paypInstallmentsSdkObjectGenerator");
        $oConfig = oxNew("paypInstallmentsConfiguration");
        $oObjectGenerator->setConfiguration($oConfig);
        $this->_oDataProvider = oxNew("paypInstallmentsCheckoutDataProvider");
        $this->_oDataProvider->setBasket($this->oBasket);
        $oObjectGenerator->setDataProvider($this->_oDataProvider);

        return $oObjectGenerator;
    }
}
