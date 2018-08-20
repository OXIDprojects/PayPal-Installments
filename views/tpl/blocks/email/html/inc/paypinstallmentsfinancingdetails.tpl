[{*
Required params
oxBasket $basket
paypInstallmentsFinancingDetails $oFinancingDetails
*}]
    <li id="pa-paypalinstallments-brutto-sum"><span
                class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_BRUTTO_SUM"}]</span> [{oxprice price=$basket->paypInstallments_GetBasketGrandTotal() currency=$currency}]
    </li>
    <li id="pa-paypalinstallments-term"><span
                class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_TERM"}]</span> [{$oFinancingDetails->getFinancingTerm()}]
    </li>
    <li id="pa-paypalinstallments-monthly-payment"><span
                class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_MONTHLY_PAYMENT"}]</span> [{oxprice price=$oFinancingDetails->getFinancingMonthlyPayment() currency=$currency}]
    </li>
    <li id="pa-paypalinstallments-fee-amount"><span
                class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_FEE_AMOUNT"}]</span> [{oxprice price=$oFinancingDetails->getFinancingFeeAmount() currency=$currency}]
    </li>
    <li id="pa-paypalinstallments-total-costs"><span
                class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_TOTAL"}]</span> [{oxprice price=$oFinancingDetails->getFinancingTotalCost() currency=$currency}]
    </li>
