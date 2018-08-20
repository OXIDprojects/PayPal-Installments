[{if ($oxid == 'paypinstallments') }]
    <tr>
        <td class="edittext" width="70">
            [{ oxmultilang ident="GENERAL_ACTIVE" }]
        </td>
        <td class="edittext">
            <input class="edittext" type="checkbox" name="editval[oxpayments__oxactive]" value="1" [{if $edit->oxpayments__oxactive->value == 1}]checked[{/if}] />
            [{ oxinputhelp ident="HELP_GENERAL_ACTIVE" }]
        </td>
    </tr>
    <tr>
        <td class="edittext" width="100">
            [{ oxmultilang ident="PAYMENT_MAIN_NAME" }]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="25" maxlength="[{$edit->oxpayments__oxdesc->fldmax_length}]" name="editval[oxpayments__oxdesc]" value="[{$edit->oxpayments__oxdesc->value}]" [{$readonly}] />
            [{ oxinputhelp ident="HELP_PAYMENT_MAIN_NAME" }]
        </td>
    </tr>
<tr>
    <td class="edittext">
        [{ oxmultilang ident="PAYMENT_MAIN_ADDPRICE" }] ([{ $oActCur->sign }])
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="15" maxlength="[{$edit->oxpayments__oxaddsum->fldmax_length}]" name="editval[oxpayments__oxaddsum]" value="[{$edit->oxpayments__oxaddsum->value }]" readonly="readonly" >
        <select name="editval[oxpayments__oxaddsumtype]" class="editinput" [{include file="help.tpl" helpid=addsumtype}] [{ $readonly }] >
        [{foreach from=$sumtype item=sum}]
        <option value="[{ $sum }]" [{ if $sum == $edit->oxpayments__oxaddsumtype->value}]SELECTED[{/if}]>[{ $sum }]</option>
        [{/foreach}]
        </select>
        [{ oxinputhelp ident="HELP_PAYMENT_MAIN_ADDPRICE" }]
    </td>
</tr>
[{if $noticeoxaddsumrules eq 1}]
    <tr>
        <td colspan="2">
            <div class="errorbox">[{ oxmultilang ident="PAYMENT_MAIN_NOTICEDEFAULTVALUESSELECTED" }]</div>
        </td>
    </tr>
    [{ /if}]
    <tr>
        <td class="edittext" valign="top">
            [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES"}]
        </td>
        <td class="edittext">
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td><input type="checkbox" name="oxpayments__oxaddsumrules[]" value="1" [{if !$edit->oxpayments__oxaddsumrules->value || $edit->oxpayments__oxaddsumrules->value & 1}]checked[{/if}] readonly="readonly" /> [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES_ALLGOODS"}]</td>
                    <td rowspan="5" valign="top">[{oxinputhelp ident="HELP_PAYMENT_MAIN_ADDSUMRULES"}]</td>
                </tr>
                <tr><td><input type="checkbox" name="oxpayments__oxaddsumrules[]" value="2" [{if !$edit->oxpayments__oxaddsumrules->value || $edit->oxpayments__oxaddsumrules->value & 2}]checked[{/if}] readonly="readonly" /> [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES_DISCOUNTS"}]</td></tr>
                <tr><td><input type="checkbox" name="oxpayments__oxaddsumrules[]" value="4" [{if !$edit->oxpayments__oxaddsumrules->value || $edit->oxpayments__oxaddsumrules->value & 4}]checked[{/if}] readonly="readonly" /> [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES_VOUCHERS"}]</td></tr>
                <tr><td><input type="checkbox" name="oxpayments__oxaddsumrules[]" value="8" [{if !$edit->oxpayments__oxaddsumrules->value || $edit->oxpayments__oxaddsumrules->value & 8}]checked[{/if}] readonly="readonly" /> [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES_SHIPCOSTS"}]</td></tr>
                <tr><td><input type="checkbox" name="oxpayments__oxaddsumrules[]" value="16" [{if $edit->oxpayments__oxaddsumrules->value & 16}]checked[{/if}] readonly="readonly" disabled="disabled" > [{oxmultilang ident="PAYMENT_MAIN_ADDSUMRULES_GIFTS"}]</td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{ oxmultilang ident="PAYMENT_MAIN_FROMBONI" }]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="25" maxlength="[{$edit->oxpayments__oxfromboni->fldmax_length}]" name="editval[oxpayments__oxfromboni]" value="[{$edit->oxpayments__oxfromboni->value}]" [{ $readonly }]>
            [{ oxinputhelp ident="HELP_PAYMENT_MAIN_FROMBONI" }]
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{ oxmultilang ident="PAYMENT_MAIN_AMOUNT" }] ([{ $oActCur->sign }])
        </td>
        <td class="edittext">
            [{ oxmultilang ident="PAYMENT_MAIN_FROM" }] <input type="text" class="editinput" size="5" maxlength="[{$edit->oxpayments__oxfromamount->fldmax_length}]" name="editval[oxpayments__oxfromamount]" value="[{$edit->oxpayments__oxfromamount->value}]" readonly="readonly" />  [{ oxmultilang ident="PAYMENT_MAIN_TILL" }] <input type="text" class="editinput" size="5" maxlength="[{$edit->oxpayments__oxtoamount->fldmax_length}]" name="editval[oxpayments__oxtoamount]" value="[{$edit->oxpayments__oxtoamount->value}]" readonly="readonly" >
            [{ oxinputhelp ident="HELP_PAYMENT_MAIN_AMOUNT" }]
        </td>
    </tr>

    <tr>
        <td class="edittext">
            [{ oxmultilang ident="PAYMENT_MAIN_SELECTED" }]
        </td>
        <td class="edittext">
            <input type="checkbox" name="editval[oxpayments__oxchecked]" value="1" [{if $edit->oxpayments__oxchecked->value}]checked[{/if}] [{$readonly}] >
            [{ oxinputhelp ident="HELP_PAYMENT_MAIN_SELECTED" }]
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{ oxmultilang ident="GENERAL_SORT" }]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="25" maxlength="[{$edit->oxpayments__oxsort->fldmax_length}]" name="editval[oxpayments__oxsort]" value="[{$edit->oxpayments__oxsort->value}]" [{$readonly}] >
            [{ oxinputhelp ident="HELP_PAYMENT_MAIN_SORT" }]
        </td>
    </tr>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
