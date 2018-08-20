[{if $paypInstallmentsIsAjax }]
    [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_ERROR_MESSAGE'}]
[{else}]
    [{oxstyle include=$oViewConf->getModuleUrl('paypinstallments', 'out/src/css/paypalinstallments.css')|cat:'?1.0.0'}]
    <div class="pa-paypal-installment-presentment">[{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_ERROR_MESSAGE'}]</div>
[{/if}]
