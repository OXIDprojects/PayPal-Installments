[{$smarty.block.parent}]
[{block name="paypinstallments_presentment_sidebar"}]
        [{oxifcontent ident="paypinstallmentssidebar" object="oContent"}]
            [{if $oViewConf->isShowGenericAdvert() }]
            <div class="box">
                <h3 id="payp-installments-presentment-start-title">[{$oContent->oxcontents__oxtitle->value}]</h3>
                <div class="content" id="payp-installments-presentment-start-content">
                    [{$oContent->oxcontents__oxcontent->value}]
                    <img src="[{$oViewConf->getModuleUrl('paypinstallments')}]/out/pictures/PP_Ratenzahlung_144x33.png" alt="Ratenzahlung Powered by PayPal" />
                </div>
            </div>
            [{/if}]
        [{/oxifcontent}]
[{/block}]