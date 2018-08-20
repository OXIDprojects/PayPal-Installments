[{$smarty.block.parent}]
[{assign var=basketGrandTotal value=$oxcmp_basket->getPrice()}]
[{oxid_include_widget cl='paypInstallmentsPresentment' amount=$basketGrandTotal->getPrice() currency=$currency->name}]