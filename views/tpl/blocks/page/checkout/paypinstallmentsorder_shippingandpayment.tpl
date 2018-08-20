[{oxstyle include=$oViewConf->getModuleUrl('paypinstallments', 'out/src/css/paypalinstallments.css')}]
[{assign var="oFinancingDetails" value=$oView->paypInstallments_getFinancingDetailsFromSession()}]
[{if $oFinancingDetails}]
    [{assign var="oFinancingDetailsRenderData" value=$oView->paypInstallments_getFinancingOptionsRenderData()}]
    <div id="pa-paypalinstallments-financingdetails" class="clear">
        <hr>
        <div>
            <div class="heading">
                [{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_DETAILS_HEADER_ORDER"}]
            </div>
            <ul>
                <li id="pa-paypalinstallments-term">
                    [{"PAYP_INSTALLMENTS_FINANCING_DETAIL_PAYMENT"|oxmultilangassign:$oFinancingDetailsRenderData}]
                </li>
                <li id="pa-paypalinstallments-brutto-sum">
                    <span class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_BRUTTO_SUM"}]</span>
                    <span class="value">[{oxprice price=$oxcmp_basket->paypInstallments_GetBasketGrandTotal() currency=$currency}]</span>
                </li>
                <li id="pa-paypalinstallments-financing-fee">
                    <span class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_FEE_AMOUNT"}]</span>
                    <span class="value">[{oxprice price=$oFinancingDetails->getFinancingFeeAmount() currency=$currency}]</span>
                </li>
                <li id="pa-paypalinstallments-total-costs">
                    <span class="label">[{oxmultilang ident="PAYP_INSTALLMENTS_FINANCING_TOTAL"}]</span>
                    <span class="value">[{oxprice price=$oFinancingDetails->getFinancingTotalCost() currency=$currency}]</span>
                </li>
            </ul>
        </div>
    </div>
[{/if}]
<div class="clearfix"></div>

[{$smarty.block.parent}]