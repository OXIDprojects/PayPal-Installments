[{assign var='amount' value=$oView->getAmount()}]
[{assign var='oConf' value=$oViewConf->getConfig()}]
[{assign var='currencycode' value=$oView->getCurrency()}]
[{assign var='countrycode' value=$oView->getCountryCode()}]
[{if $oViewConf->isWithCalculatedValue() }]
    [{if $paypInstallmentsIsCached }]
        [{capture assign='presentmentHtml'}]
            [{$oView->getCachedPresentmentHtml()}]
        [{/capture}]
    [{else}]
        [{oxscript include=$oViewConf->getPayPalInstallmentsUrl('js/paypalinstallmentspresentments.js')}]
        [{assign var='scope' value=$oView->getRenderData()}]
        [{capture assign='presentmentHtml'}]
            <div class="paypal-installment-loader" style="width: 100%; text-align: center;">[{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_LOADING_MESSAGE"}]</div>
        [{/capture}]
        [{capture assign='installmentScript'}]
            jQuery(document).ready(function () {
                jQuery('.pa-paypal-installment-presentment').paPayPalInstallmentPresentment(
                        {
                            amount:[{$scope.amount}],
                            currency:'[{$scope.currency}]',
                            country:'[{$scope.country}]',
                            rootUrl :'[{$scope.root_url}]'
                        }
                );
            });
        [{/capture}]
        [{oxscript add=$installmentScript}]
        [{oxstyle include=$oViewConf->getModuleUrl('paypinstallments', 'out/src/css/paypalinstallments.css')|cat:'?1.0.0'}]
    [{/if}]
[{else}]
    [{capture assign='presentmentHtml'}]
        [{if $amount >= 99 && $amount <= 5000}]
            [{if $oView->isBasketController() }]
                [{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_NO_CALC_VALUE_BSKT"}]
            [{else}]
                [{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_NO_CALC_VALUE"}]
            [{/if}]
        [{else}]
            [{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_NO_CALC_VALUE_LOW"}]
        [{/if}]
    [{/capture}]
[{/if}]

[{oxstyle include=$oViewConf->getModuleUrl('paypinstallments', 'out/src/css/paypalinstallments.css')|cat:'?1.0.0'}]
<div class="pa-paypal-installment-presentment-cont">
    <div class="pa-paypal-installment-presentment">[{$presentmentHtml}]</div>

    [{if $amount >= 99 && $amount <= 5000}]
        <a id="installmentsInfoTrigger"  href="#installmentsModal" data-toggle="modal">[{oxmultilang ident="PAYP_INSTALLMENTS_PRESENTMENT_INFO"}]</a>
        [{assign var='shopURL' value=$oConf->getCurrentShopUrl()}]
        [{ if $oViewConf->getActiveTheme() eq 'azure' }]
            [{assign var='shopURL' value=$oConf->getCurrentShopUrl()}]
            [{oxscript include="js/widgets/oxmodalpopup.js" priority=10 }]
            [{oxscript add="$('#installmentsInfoTrigger').oxModalPopup({target:'#installmentsModal', width: 930, loadUrl: '`$shopURL`/widget.php?disabled=false&cl=paypInstallmentsPresentment&fnc=getPresentmentInfoHtml&amount=`$amount`&currency=`$currencycode`&country=`$countrycode`'});"}]
        [{else}]
            [{oxscript add="$('#installmentsInfoTrigger').on(
            'click',
             function() {el = $('#installmentsModal'); el.load('`$shopURL`/widget.php?disabled=false&cl=paypInstallmentsPresentment&fnc=getPresentmentInfoHtml&amount=`$amount`&currency=`$currencycode`&country=`$countrycode`')}
             )"}]
        [{/if}]
    [{/if}]

    <div id="installmentsModal" class="popupBox corners FXgradGreyLight glowShadow modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    [{ if $oViewConf->getActiveTheme() eq 'azure' }]
                        <img src="[{$oViewConf->getImageUrl('x.png')}]" alt="" class="closePop">
                    [{else}]
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    [{/if}]
                    <img src="[{$oViewConf->getModuleUrl('paypinstallments')}]/out/pictures/PP_Ratenzahlung_144x33.png" alt="Ratenzahlung Powered by PayPal" /><br />
                </div>
                <div class="pa-paypal-installments-body">
                    [{oxmultilang ident='PAYP_INSTALLMENTS_PRESENTMENT_PLAN_LOADING'}]
                </div>
                <div class="modal-footer">
                    [{*<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>*}]
                </div>
            </div>
        </div>
    </div>

</div>


