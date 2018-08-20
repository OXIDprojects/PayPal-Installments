[{*
Required params
oxBasket $basket
paypInstallmentsFinancingDetails $oFinancingDetails
*}]
[{oxmultilang ident="PAYP_INSTALLMENTS_BRUTTO_SUM"}] [{oxprice price=$basket->paypInstallments_GetBasketGrandTotal() currency=$currency}]
[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_TERM"}][{$oFinancingDetails->getFinancingTerm()}]
[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_MONTHLY_PAYMENT"}] [{oxprice price=$oFinancingDetails->getFinancingMonthlyPayment() currency=$currency}]
[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_FEE_AMOUNT"}] [{oxprice price=$oFinancingDetails->getFinancingFeeAmount() currency=$currency}][{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_TOTAL"}] [{oxprice price=$oFinancingDetails->getFinancingTotalCost() currency=$currency}]
