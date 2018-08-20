[{oxstyle include=$oViewConf->getModuleUrl('paypinstallments', 'out/src/css/paypalinstallments.css')|cat:'?1.0.0'}]
[{$smarty.block.parent}]
[{ if $oViewConf->getActiveTheme() ne 'azure' }]
[{block name="paypinstallments_presentment_content_main"}]
        [{oxifcontent ident="paypinstallmentssidebar" object="oContent"}]
            [{if $oViewConf->isShowGenericAdvert() }]
            <div class="box">
                <div class="content" id="payp-installments-presentment-advert-content">
                    [{$oContent->oxcontents__oxcontent->value}]
                </div>
                <div class="content" id="payp-installments-presentment-advert-image">
                    <img src="[{$oViewConf->getModuleUrl('paypinstallments')}]/out/pictures/PP_Ratenzahlung_144x33.png" alt="Ratenzahlung Powered by PayPal" />
                </div>
            </div>
            [{/if}]
        [{/oxifcontent}]
[{/block}]
[{/if}]