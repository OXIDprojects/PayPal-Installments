[{assign var=basketGrandTotal value=$oxcmp_basket->paypInstallments_GetBasketGrandTotal() }]
[{if $sPaymentID == "paypinstallments" }]
    [{oxid_include_widget cl='paypInstallmentsPresentment' amount=$basketGrandTotal->getPrice() currency=$currency->name country=$oView->getBillingCountryCode()}]
[{elseif $sPaymentID != "paypinstallments"}]
    [{$smarty.block.parent}]
[{/if}]