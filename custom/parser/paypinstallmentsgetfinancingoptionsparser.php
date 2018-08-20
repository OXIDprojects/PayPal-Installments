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
 * Class paypInstallmentsGetFinancingOptionsParser
 *
 * @desc Financing option parser
 */
class paypInstallmentsGetFinancingOptionsParser extends paypInstallmentsParserBase
{

    const RESPONSE_TYPE = 'stdClass';


    /**
     * @var array
     */
    protected $_aRequestInformation;

    /**
     * Amount to be financed
     *
     * @var float
     */
    protected $_fAmount;

    /**
     * getter for json response
     *
     * @return string
     */
    public function getResponse()
    {
        return json_encode($this->_oResponse);
    }

    /**
     * setter for requestInformation
     *
     * @param array $aRequestInformation
     */
    public function setRequestInformation($aRequestInformation)
    {
        $this->_aRequestInformation = $aRequestInformation;
    }

    /**
     * Original requested amount.
     *
     * @param $fAmount
     */
    public function setAmount($fAmount)
    {
        $this->_fAmount = $fAmount;
    }

    /**
     * Original requested amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_fAmount;
    }

    /**
     * return the htpp response code for our curl request
     *
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->_aRequestInformation["http_code"];
    }

    /**
     * Return the error_description returned by PayPal
     *
     * @return mixed
     */
    public function getErrorDescription()
    {
        return $this->_oResponse->error_description;
    }

    /**
     * Return the access_token returned by PayPal
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->_oResponse->access_token;
    }

    /**
     * Return the token_type returned by PayPal
     *
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->_oResponse->token_type;
    }

    /**
     * Return the name value returned by PayPal
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_oResponse->name;
    }

    /**
     * Return the Message value returned by PayPal
     *
     * @return mixed
     */
    public function getMessage()
    {
        return $this->_oResponse->message;
    }

    /**
     * Extract all qualifying financing options returned by PayPal
     *
     * @return paypInstallmentsFinancingOption[]
     */
    public function extractFinancingOptions()
    {
        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsParser extractFinancingOptions",
            array("response" => $this->_oResponse)
        );

        return $this->_extractFinancingOptionsBase(
            $this->_oResponse->financing_options[0]->qualifying_financing_options ?: array()
        );
    }


    /**
     * Extract all NOT qualifying financing options returned by PayPal
     *
     * @return paypInstallmentsFinancingOption[]
     */
    public function extractFinancingOptionsNotQualified()
    {
        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsParser extractFinancingOptionsNotQualified",
            array("response" => $this->_oResponse)
        );

        return $this->_extractFinancingOptionsBase(
            $this->_oResponse->financing_options[0]->non_qualifying_financing_options ?: array()
        );
    }

    /**
     * Extract financing options returned by PayPal
     *
     * @param array $aRawFinancingOptions
     *
     * @return array that holds paypInstallmentsFinancingOption objects
     */
    protected function _extractFinancingOptionsBase(array $aRawFinancingOptions)
    {
        $aFinancingOptions = array();

        if (!$aRawFinancingOptions) {
            return $aFinancingOptions;
        }

        foreach ($aRawFinancingOptions as $oRawFinancingOption) {
            $aFinancingOptions[] = $this->_extractFinancingOptionForm($oRawFinancingOption);
        }

        // sort financing options by financing term ascending
        usort($aFinancingOptions, array($this, '_sortOptions'));

        return $aFinancingOptions;
    }

    /**
     * Sort options.
     *
     * @param paypInstallmentsFinancingOption $a
     * @param paypInstallmentsFinancingOption $b
     *
     * @return int
     */
    protected function _sortOptions(paypInstallmentsFinancingOption $a, paypInstallmentsFinancingOption $b)
    {
        $iARates = $a->getNumMonthlyPayments();
        $iBRates = $b->getNumMonthlyPayments();
        if ($iARates == $iBRates) {
            return 0;
        } elseif ($iARates > $iBRates) {
            return 1;
        }

        return -1;
    }

    /**
     * Create a Financing Option Object From PayPals Response Data
     *
     * @param $oRawFinancingOption
     *
     * @return paypInstallmentsFinancingOption
     */
    protected function _extractFinancingOptionForm($oRawFinancingOption)
    {
        $this->getLogger()->info(
            "paypInstallmentsGetFinancingOptionsParser _extractFinancingOptionForm",
            array("rawFinancingOption" => $oRawFinancingOption)
        );

        $fAmount = $this->getAmount();
        # the duration of the contract
        $iTerm = $oRawFinancingOption->credit_financing->term;
        # the nominal rate
        $fNominalRate = $oRawFinancingOption->credit_financing->nominal_rate;
        # the annual percentage rate
        $fAnnualPercentageRate = $oRawFinancingOption->credit_financing->apr;
        # the instalment amount
        $fMonthlyPayment = $oRawFinancingOption->monthly_payment->value;
        # the total amount
        $fTotalCost = $oRawFinancingOption->total_cost->value;

        $oFinancingOption = new paypInstallmentsFinancingOption();
        $oFinancingOption->setAmount($fAmount);
        $oFinancingOption->setNumMonthlyPayments($iTerm);
        $oFinancingOption->setNominalRate($fNominalRate);
        $oFinancingOption->setAnnualPercentageRate($fAnnualPercentageRate);
        $oFinancingOption->setMonthlyPayment($fMonthlyPayment);
        $oFinancingOption->setTotalPayment($fTotalCost);
        $oFinancingOption->setFinancingFee($oRawFinancingOption->total_interest->value);
        $oFinancingOption->setMinAmount($oRawFinancingOption->min_amount->value);
        $oFinancingOption->setCurrency($oRawFinancingOption->min_amount->currency_code);

        return $oFinancingOption;
    }
}
