[{$smarty.block.parent}]
[{if $oFinancingDetails }]
    [{include file="blocks/email/plain/inc/paypinstallmentsfinancingdetails.tpl"
    basket=$basket
    oFinancingDetails=$oFinancingDetails}]
[{/if}]
