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
class paypInstallmentsDoExpressCheckoutPaymentHandler extends paypInstallmentsHandlerBase
{

    /** @var  string */
    protected $_sAuthToken;
    /** @var  string */
    protected $_sPayerId;

    /**
     * @var oxBasket
     */
    protected $_oBasket;

    /**
     * Pass mandatory parameters authToken and payerId during creation
     *
     * @param string $sAuthToken
     * @param string $sPayerId
     */
    public function __construct($sAuthToken, $sPayerId)
    {
        $this->setAuthToken($sAuthToken)
            ->setPayerId($sPayerId);
    }

    /**
     * setter for basket
     *
     * @param $oBasket oxBasket
     */
    public function setBasket($oBasket)
    {
        $this->_oBasket = $oBasket;
    }

    /**
     * create an objectGenerator and configure it correctly
     *
     * @return paypInstallmentsSdkObjectGenerator
     */
    public function prepareObjectGenerator()
    {
        $oObjectGenerator = oxNew('paypInstallmentsSdkObjectGenerator');
        $oConfig = oxNew("paypInstallmentsConfiguration");
        $oObjectGenerator->setConfiguration($oConfig);
        $oDataProvider = oxNew("paypInstallmentsCheckoutDataProvider");
        $oDataProvider->setBasket($this->_oBasket);
        $oObjectGenerator->setDataProvider($oDataProvider);

        return $oObjectGenerator;
    }

    /**
     * Perform the Do Express Checkout Request
     *
     * @return mixed - the transaction ID returned by paypal
     * @throws paypInstallmentsBasketIntegrityLostException
     * @throws paypInstallmentsException
     */
    public function doRequest()
    {
        $oObjectGenerator = $this->prepareObjectGenerator();
        $oRequest = $oObjectGenerator->getDoExpressCheckoutReqObject($this->getPayerId(), $this->getAuthToken());
        $oService = $oObjectGenerator->getPayPalServiceObject();

        $oLogger = $this->getLogger();
        $oLogger->info("DoExpressCheckoutPayment doRequest", array("request" => $oRequest));

        $oValidator = $this->_getValidator("paypInstallmentsDoExpressCheckoutPaymentValidator");
        $oValidator->setLogger($oLogger);
        $oValidator->setRequest($oRequest);
        $oValidator->setBasket($this->_oBasket);
        $oValidator->validateRequest();

        try {
            $oResponse = $oService->DoExpressCheckoutPayment($oRequest);
        } catch (Exception $oPayPalException) {
            $sMessage = $oPayPalException->getMessage();
            $oLogger->error("DoExpressCheckoutPayment doRequest", array("request" => $oRequest, "error" => $sMessage));
            /** @var paypInstallmentsException $oException */
            $oException = oxNew("paypInstallmentsException");
            $oException->setMessage($sMessage);
            throw $oException;
        }

        $oLogger->info("DoExpressCheckoutPayment doRequest", array("response" => $oResponse));

        $oParser = oxNew("paypInstallmentsDoExpressCheckoutPaymentParser");
        $oParser->setLogger($oLogger);

        $oValidator->setParser($oParser);
        $oValidator->setRequest($oRequest);
        $oValidator->setResponse($oResponse);
        $oValidator->validateResponse();


        return array(
            'Timestamp' =>     $oParser->getFormattedTimestamp(),
            'TransactionId' => $oParser->getTransactionId(), // TransactionID must be persisted in the DB as it is needed for refunding
            'PaymentStatus' => $oParser->getPaymentStatus(), // PaymentStatus will be refunded in DB
            'Response'      => $oResponse // Response will be refunded in DB
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
     * PayerId getter.
     *
     * @return string
     */
    public function getPayerId()
    {
        return $this->_sPayerId;
    }

    /**
     * PayerId setter. Default value unSets attribute. Method chain supported.
     *
     * @param string $payerId
     *
     * @return $this
     */
    public function setPayerId($payerId = null)
    {
        $this->_sPayerId = $payerId;

        return $this;
    }
}