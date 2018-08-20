[{$smarty.block.parent}]
[{if $iPayError == 1100}]
    <div class="status error">[{oxmultilang ident=PAYP_INSTALLMENTS_GENERIC_EXCEPTION_MESSAGE }]</div>
[{elseif $iPayError == 1101}]
    <div class="status error">[{oxmultilang ident=PAYP_INSTALLMENTS_LOST_SESSION_DATA_EXCEPTION_MESSAGE }]</div>
[{/if}]