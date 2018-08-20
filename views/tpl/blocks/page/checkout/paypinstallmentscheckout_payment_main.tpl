[{assign var=basketGrandTotal value=$oxcmp_basket->paypInstallments_GetBasketGrandTotal() }]
[{oxid_include_widget cl='paypInstallmentsPresentment' amount=$basketGrandTotal->getPrice() currency=$currency->name country=$oView->getBillingCountryCode()}]
[{$smarty.block.parent}]
