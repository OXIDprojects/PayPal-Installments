[{$smarty.block.parent}]
[{if $oFinancingDetails }]
    <ul>
        [{include file="blocks/email/html/inc/paypinstallmentsfinancingdetails.tpl"
        basket=$basket
        oFinancingDetails=$oFinancingDetails}]
    </ul>
[{/if}]
