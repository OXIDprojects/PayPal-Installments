# PayPal-Installments
## Requirements

    * OXID eShop 6.0.x

## Installation

Install via composer 

`composer require oxid-paypal/paypalinstallments`

## Credits

    * Author: ps@oxid-esales.com
    * URL: http://www.paypal.com
    * Mail: ps@oxid-esales.com
    
## For developers
    * File `docs/uninstall.sql` contains SQL to completely clean module. It is recommended to use after trying and uninstalling module.
    * Widget paypInstallmentsPresentment shows info about PayPalInstallments. 
        Mandatory parameter: amount. Optional parameters: currency, country. Default values for optional parameters are extracted from active shop. 
        Code example `[{oxid_include_widget cl="paypInstallmentsPresentment" amount=5, currency='EUR' country='DE'}]`
    * Override template of installments widget - create 'widget/presentment/paypinstallmentspresentment.tpl' inside `[theme]/tpl` folder.
    * Append payment list with "paPayPal_installments" if used together with PayPal-Plus. See settings of module PayPal-Plus.
    * Presentment on the start page extends whole template because there is not a single block on start.tpl. There is dedicated div for start page presentment script. See `paypinstallments_page_content_main.tpl`.
    * `pa/paypalinstallments/application/views/admin/tpl/blocks/admin_payment_main_form.tpl` makes fields except `editval[oxpayments__oxfromboni]` readonly/disabled. Unfortunately JS solution did not worked at the time.
    * User/owner can receive customised email depending on shop theme. Create templates `blocks/email/inc/paypinstallmentsfinancingdetails.tpl` and `blocks/email/plain/inc/paypinstallmentsfinancingdetails.tpl` on theme directory.
    
