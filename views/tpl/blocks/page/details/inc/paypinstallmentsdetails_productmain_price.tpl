[{assign var="oPrice" value=$oDetailsProduct->getPrice()}]
[{oxid_include_widget cl='paypInstallmentsPresentment' amount=$oPrice->getBruttoPrice() currency=$currency->name}]

[{$smarty.block.parent}]
