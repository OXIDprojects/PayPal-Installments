[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]
<link rel="stylesheet" href="../modules/pa/paypalinstallments/out/src/css/paypinstallmentsbackend.css"/>
<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="oxidCopy" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="Admin_paypInstallments_orderTab"/>
    <input type="hidden" name="language" value="[{$actlang}]"/>
</form>
[{if $oView->isPayPalInstallmentOrder()}]
    [{assign var="scope" value=$oView->getRenderData()}]
    <table width="98%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
        <tr>
            <td class="edittext" valign="top" width="50%">
                <b>[{oxmultilang ident="PAYP_INSTALLMENTS_PAYMENT_OVERVIEW"}]</b>
                <table class="paypInstallmentsOverviewTable">[{*todo css *}]
                    <tbody>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="PAYP_INSTALLMENTS_PAYMENT_STATUS"}]</td>
                        <td class="edittext"><b>[{$scope.payment.status}]</b></td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="PAYP_INSTALLMENTS_ORDER_AMOUNT"}]</td>
                        <td class="edittext"><b>[{$scope.order.total}] [{$scope.order.currency}]</b></td>
                    </tr>
                    [{if $dRefundedAmount}]
                        <tr>
                            <td class="edittext">[{oxmultilang ident="PAYP_INSTALLMENTS_REFUNDED_AMOUNT"}]</td>
                            <td class="edittext"><b>[{$scope.refundList.total}] [{$scope.order.currency}]</b></td>
                        </tr>
                    [{/if}]
                    <tr>
                        <td class="edittext">[{oxmultilang ident="PAYP_INSTALLMENTS_TRANSACTION_ID"}]</td>
                        <td class="edittext"><b>[{$scope.payment.transactionId}]</b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td class="edittext" valign="top" align="left" width="50%">
                [{if $scope.payment.refundable}]
                    <b>[{oxmultilang ident="PAYP_INSTALLMENTS_PAYMENT_REFUNDING"}]</b>
                    <table class="paypInstallmentsOverviewTable" cellpadding="0" border="0">
                        <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td class="edittext">[{oxmultilang ident="PAYP_INSTALLMENTS_AVAILABLE_REFUND_AMOUNT"}]</td>
                            <td class="edittext"><b>[{$scope.remainingRefund}] [{$scope.order.currency}]</b></td>
                            <td>&nbsp;</td>
                        </tr>
                        [{if $scope.refund.list }]
                            <tr><td colspan="3">&nbsp;</td></tr>
                            <tr>
                                <th class="listheader first">&nbsp;</th>
                                <th class="listheader">[{oxmultilang ident="PAYP_INSTALLMENTS_DATE"}]</th>
                                <th class="listheader" height="15">[{oxmultilang ident="PAYP_INSTALLMENTS_AMOUNT"}]</th>
                                <th class="listheader" height="15">[{oxmultilang ident="PAYP_INSTALLMENTS_CURRENCY"}]</th>
                                <th class="listheader">[{oxmultilang ident="PAYP_INSTALLMENTS_STATUS"}]</th>
                            </tr>
                            [{foreach name='refunds_list' from=$scope.refund.list item="refund"}]
                                <tr>
                                    <td valign="top" class="listitem edittext">#[{$smarty.foreach.refunds_list.iteration}]</td>
                                    <td valign="top" class="listitem edittext">[{$refund.date}]</td>
                                    <td valign="top" class="listitem edittext" height="15">[{$refund.total}]</td>
                                    <td valign="top" class="listitem edittext" height="15">[{$refund.currency}]</td>
                                    <td valign="top" class="listitem edittext">[{$refund.status}]</td>
                                </tr>
                            [{/foreach}]
                        [{/if}]
                        [{if $scope.payment.refundable}]
                            <tr>
                                <td colspan="4">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    [{if $scope.error}]
                                        <div class="errorbox">[{$scope.error}]</div>
                                    [{/if}]
                                </td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td class="edittext"><b>[{oxmultilang ident="PAYP_INSTALLMENTS_NEW_REFUND"}]</b></td>
                                <td class="edittext" colspan="2">
                                    <form id="refund" name="myedit" action="[{$oViewConf->getSelfLink()}]"
                                          method="post">
                                        [{$oViewConf->getHiddenSid()}]
                                        <input type="hidden" name="cl" value="Admin_paypInstallments_orderTab"/>
                                        <input type="hidden" name="fnc" value="refund"/>
                                        <input type="hidden" name="oxid" value="[{$oxid}]"/>
                                        <input type="hidden" name="orderId" value="[{$oxid}]"/>
                                        <input type="text" class="editinput" size="7" maxlength="8"
                                               name="refundAmount" value=""/>&nbsp;[{$scope.order.currency}]
                                        <input type="submit" class="edittext paypInstallmentsRefundButton" name="refund"
                                               value="[{oxmultilang ident="PAYP_INSTALLMENTS_REFUND"}]"/>
                                    </form>
                                </td>
                            </tr>
                        [{/if}]
                        </tbody>
                    </table>
                [{/if}]
            </td>
        </tr>
        </tbody>
    </table>
[{else}]
    <div class="messagebox">[{oxmultilang ident="PAYP_INSTALLMENTS_ONLY_FOR_PAYPAL_INSTALLMENTS_PAYMENT"}]</div>
[{/if}]
[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]
