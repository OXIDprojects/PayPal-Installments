[{ if $oViewConf->getActiveTheme() ne 'azure' }]
<div class="modal-dialog">
[{/if}]
    <div class="modal-content">
        <div class="installmentsHead modal-header">
            [{ if $oViewConf->getActiveTheme() eq 'azure' }]
                <img src="[{$oViewConf->getImageUrl('x.png')}]" alt="" class="closePop" onClick="$('#installmentsModal').dialog('close')">
            [{else}]
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            [{/if}]
            <img src="[{$oViewConf->getModuleUrl('paypinstallments')}]/out/pictures/PP_Ratenzahlung_144x33.png" alt="Ratenzahlung Powered by PayPal" /><br />

        </div>
        <div class="pa-paypal-installments-body modal-body"  style="margin: 0 auto">
            [{if $oView->isError() }]
               [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_ERROR_MESSAGE'}]
            [{else}]
                [{assign var='aOptions' value=$oView->getFinancingOptions()}]
                [{assign var="amount" value=$oView->getFAmount()}]
                [{assign var="currency" value=$oView->getCurrencyObject()}]
                <div class="pa-paypal-installments-body-intro">
                    [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_INTRO'}] <strong>[{oxprice price=$amount currency=$currency}]</strong>
                </div>
                <div class="pa-paypal-installments-body-block">
                    <div class="pa-paypal-installments-body-center">
                        [{assign var="iOption" value=0}]
                        [{foreach from=$aOptions item=oOption}]
                                <div class="pa-paypal-installments-body-plan">
                                    [{assign var="iOption" value=$iOption+1}]
                                <strong>[{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_PLAN_PLAN"}] [{$iOption}]</strong> [{if $oView->isOptionRepresentative($iOption)}]*[{/if}]<br />
                                [{$oOption->getNumMonthlyPayments()}] [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_RATES'}]<span class="percent"><strong>[{oxprice price=$oOption->getMonthlyPayment() currency=$currency}]</strong></span><br />
                                [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_FIXED_INTEREST'}]: <span class="percent">[{$oOption->getNominalRate()}] %</span><br />
                                [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_EFFECTIVE_INTEREST'}]: <span class="percent">[{$oOption->getAnnualPercentageRate()}] %</span><br />
                                [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_TOTAL_INTEREST'}]: <span class="percent">[{oxprice price=$oOption->getFinancingFee() currency=$currency}]</span><br />
                                <strong>[{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_TOTAL_AMOUNT'}]: <span class="percent">[{oxprice price=$oOption->getTotalPayment() currency=$currency}]</strong></span><br />
                                </div>
                        [{/foreach}]
                    </div>
                </div>
                <div class="pa-paypal-installments-body-extro">
                    <p>* [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_REPR_EXAMPLE'}]</p>
                    <p>[{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_CREDITOR'}]: [{$oViewConf->getInstallmentsCreditor()}]</p>
                </div>
            [{/if}]
        </div>
    </div>
[{ if $oViewConf->getActiveTheme() ne 'azure' }]
</div>
[{/if}]