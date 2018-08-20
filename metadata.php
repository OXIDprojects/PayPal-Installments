<?php

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'paypinstallments',
    'title'       => 'Ratenzahlung Powered by PayPal',
    'description' => 'PayPal Installments',
    'thumbnail'   => 'out/pictures/PP_Ratenzahlung_144x33.png',
    'version'     => '2.0.1',
    'author'      => 'info@paypal.com',
    'url'         => 'http://www.paypal.com',
    'email'       => 'info@paypal.com',
    'extend'      => array(
        'basket'           => 'payp/installments/controllers/paypinstallmentsbasket',
        'order'            => 'payp/installments/controllers/paypinstallmentsorder',
        'payment'          => 'payp/installments/controllers/paypinstallmentspayment',
        'thankyou'         => 'payp/installments/controllers/paypinstallmentsthankyou',
        'oxbasket'         => 'payp/installments/models/paypinstallmentsoxbasket',
        'oxorder'          => 'payp/installments/models/paypinstallmentsoxorder',
        'oxpayment'        => 'payp/installments/models/paypinstallmentsoxpayment',
        'oxpaymentgateway' => 'payp/installments/models/paypinstallmentsoxpaymentgateway',
        'oxsession'        => 'payp/installments/core/paypinstallmentsoxsession',
        'oxviewconfig'     => 'payp/installments/core/paypinstallmentsoxviewconfig',
        'start'            => 'payp/installments/controllers/paypinstallmentsstart',
        'oxemail'          => 'payp/installments/core/paypinstallmentsoxemail',
    ),
    'files'       => array(
        //controllers
        'admin_paypinstallments_ordertab'                              => 'payp/installments/controllers/admin/admin_paypinstallments_ordertab.php',
        'paypinstallmentsformatter'                                    => 'payp/installments/controllers/paypinstallmentsformatter.php',

        //widgets
        'paypinstallmentspresentment'                                  => 'payp/installments/components/widgets/paypinstallmentspresentment.php',

        // core
        'paypinstallmentsevents'                                       => 'payp/installments/core/paypinstallmentsevents.php',
        'paypinstallmentsinvoicepdfarticlesummary'                     => 'payp/installments/core/paypinstallmentsinvoicepdfarticlesummary.php',
        'paypinstallmentspdfarticlesummary'                            => 'payp/installments/core/paypinstallmentspdfarticlesummary.php',

        //exceptions
        'paypinstallmentsexception'                                    => 'payp/installments/custom/exceptions/paypinstallmentsexception.php',
        'paypinstallmentsinvalidbillingcountryexception'               => 'payp/installments/custom/exceptions/paypinstallmentsinvalidbillingcountryexception.php',
        'paypinstallmentsinvalidshippingcountryexception'              => 'payp/installments/custom/exceptions/paypinstallmentsinvalidshippingcountryexception.php',
        'paypinstallmentsmalformedresponseexception'                   => 'payp/installments/custom/exceptions/paypinstallmentsmalformedresponseexception.php',
        'paypinstallmentsmalformedrequestexception'                    => 'payp/installments/custom/exceptions/paypinstallmentsmalformedrequestexception.php',
        'paypinstallmentsnoacksuccessexception'                        => 'payp/installments/custom/exceptions/paypinstallmentsnoacksuccessexception.php',
        'paypinstallmentssetexpresscheckoutexception'                  => 'payp/installments/custom/exceptions/paypinstallmentssetexpresscheckoutexception.php',
        'paypinstallmentssetexpresscheckoutrequestvalidationexception' =>
            'payp/installments/custom/exceptions/paypinstallmentssetexpresscheckoutrequestvalidationexception.php',
        'paypinstallmentsversionmismatchexception'                     => 'payp/installments/custom/exceptions/paypinstallmentsversionmismatchexception.php',
        'paypinstallmentsrequirementsvalidatorexception'               => 'payp/installments/custom/exceptions/paypinstallmentsrequirementsvalidatorexception.php',
        'paypinstallmentsgetexpresscheckoutvalidationexception'        => 'payp/installments/custom/exceptions/paypinstallmentsgetexpresscheckoutvalidationexception.php',
        'paypinstallmentsgetexpresscheckoutdetailsvalidationexception' => 'payp/installments/custom/exceptions/paypinstallmentsgetexpresscheckoutdetailsvalidationexception.php',
        'paypinstallmentsgetexpresscheckoutdetailsparseexception'      => 'payp/installments/custom/exceptions/paypinstallmentsgetexpresscheckoutdetailsparseexception.php',
        'paypinstallmentsdoexpresscheckoutparseexception'              => 'payp/installments/custom/exceptions/paypinstallmentsdoexpresscheckoutparseexception.php',
        'paypinstallmentsbasketintegritylostexception'                 => 'payp/installments/custom/exceptions/paypinstallmentsbasketintegritylostexception.php',
        'paypinstallmentsdoexpresscheckoutvalidationexception'         => 'payp/installments/custom/exceptions/paypinstallmentsdoexpresscheckoutvalidationexception.php',
        'paypinstallmentsfinancingoptionsexception'                    => 'payp/installments/custom/exceptions/paypinstallmentsfinancingoptionsexception.php',
        'paypinstallmentsrefundtransactionexception'                   => 'payp/installments/custom/exceptions/paypinstallmentsrefundtransactionexception.php',
        'paypinstallmentsrefundrequestparametervalidationexception'    => 'payp/installments/custom/exceptions/paypinstallmentsrefundrequestparametervalidationexception.php',
        'paypinstallmentsrefundresponsevalidationexception'            => 'payp/installments/custom/exceptions/paypinstallmentsrefundresponsevalidationexception.php',
        'paypinstallmentscorruptsessionexception'                      => 'payp/installments/custom/exceptions/paypinstallmentscorruptsessionexception.php',
        'paypinstallmentspersistpaymentdataexception'                  => 'payp/installments/custom/exceptions/paypinstallmentspersistpaymentdataexception.php',
        'paypinstallmentsdoexpresscheckoutexception'                   => 'payp/installments/custom/exceptions/paypinstallmentsdoexpresscheckoutexception.php',
        'paypinstallmentspresentmentvalidationexception'               =>
            'payp/installments/custom/exceptions/paypinstallmentspresentmentvalidationexception.php',

        //handler
        'paypinstallmentshandlerinterface'                             => 'payp/installments/custom/handlers/paypinstallmentshandlerinterface.php',
        'paypinstallmentshandlerbase'                                  => 'payp/installments/custom/handlers/paypinstallmentshandlerbase.php',
        'paypinstallmentsdoexpresscheckoutpaymenthandler'              => 'payp/installments/custom/handlers/paypinstallmentsdoexpresscheckoutpaymenthandler.php',
        'paypinstallmentsgetexpresscheckoutdetailshandler'             => 'payp/installments/custom/handlers/paypinstallmentsgetexpresscheckoutdetailshandler.php',
        'paypinstallmentsgetfinancingoptionshandler'                   => 'payp/installments/custom/handlers/paypinstallmentsgetfinancingoptionshandler.php',
        'paypinstallmentsrefundhandler'                                => 'payp/installments/custom/handlers/paypinstallmentsrefundhandler.php',
        'paypinstallmentssetexpresscheckouthandler'                    => 'payp/installments/custom/handlers/paypinstallmentssetexpresscheckouthandler.php',

        //models
        'paypinstallmentscheckoutdataprovider'                         => 'payp/installments/custom/models/paypinstallmentscheckoutdataprovider.php',
        'paypinstallmentsconfiguration'                                => 'payp/installments/custom/models/paypinstallmentsconfiguration.php',
        'paypinstallmentsfinancingoption'                              => 'payp/installments/custom/models/paypinstallmentsfinancingoption.php',
        'paypinstallmentspaymentdata'                                  => 'payp/installments/custom/models/paypinstallmentspaymentdata.php',
        'paypinstallmentsrefund'                                       => 'payp/installments/custom/models/paypinstallmentsrefund.php',
        'paypinstallmentsrefundlist'                                   => 'payp/installments/custom/models/paypinstallmentsrefundlist.php',
        'paypinstallmentssdkobjectgenerator'                           => 'payp/installments/custom/models/paypinstallmentssdkobjectgenerator.php',
        'paypinstallmentsfinancingdetails'                             => 'payp/installments/custom/models/paypinstallmentsfinancingdetails.php',
        'paypinstallmentsloggermanager'                                => 'payp/installments/custom/models/paypinstallmentsloggermanager.php',
        'paypinstallmentslogger'                                       => 'payp/installments/custom/models/paypinstallmentslogger.php',

        //parser
        'paypinstallmentsparserinterface'                              => 'payp/installments/custom/parser/paypinstallmentsparserinterface.php',
        'paypinstallmentsparserbase'                                   => 'payp/installments/custom/parser/paypinstallmentsparserbase.php',
        'paypinstallmentssoapparser'                                   => 'payp/installments/custom/parser/paypinstallmentssoapparser.php',
        'paypinstallmentsdoexpresscheckoutpaymentparser'               => 'payp/installments/custom/parser/paypinstallmentsdoexpresscheckoutpaymentparser.php',
        'paypinstallmentsgetexpresscheckoutdetailsparser'              => 'payp/installments/custom/parser/paypinstallmentsgetexpresscheckoutdetailsparser.php',
        'paypinstallmentsrefundparser'                                 => 'payp/installments/custom/parser/paypinstallmentsrefundparser.php',
        'paypinstallmentssetexpresscheckoutparser'                     => 'payp/installments/custom/parser/paypinstallmentssetexpresscheckoutparser.php',
        'paypinstallmentsgetfinancingoptionsparser'                    => 'payp/installments/custom/parser/paypinstallmentsgetfinancingoptionsparser.php',

        //validator
        'paypinstallmentsvalidatorbase'                                => 'payp/installments/custom/validators/paypinstallmentsvalidatorbase.php',
        'paypinstallmentssoapvalidator'                                => 'payp/installments/custom/validators/paypinstallmentssoapvalidator.php',
        'paypinstallmentsdoexpresscheckoutpaymentvalidator'            => 'payp/installments/custom/validators/paypinstallmentsdoexpresscheckoutpaymentvalidator.php',
        'paypinstallmentsgetexpresscheckoutdetailsvalidator'           => 'payp/installments/custom/validators/paypinstallmentsgetexpresscheckoutdetailsvalidator.php',
        'paypinstallmentsgetfinancingoptionsvalidator'                 => 'payp/installments/custom/validators/paypinstallmentsgetfinancingoptionsvalidator.php',
        'paypinstallmentsrefundvalidator'                              => 'payp/installments/custom/validators/paypinstallmentsrefundvalidator.php',
        'paypinstallmentsrequirementsvalidator'                        => 'payp/installments/custom/validators/paypinstallmentsrequirementsvalidator.php',
        'paypinstallmentssetexpresscheckoutvalidator'                  => 'payp/installments/custom/validators/paypinstallmentssetexpresscheckoutvalidator.php',
        'paypinstallmentspresentmentvalidator'                         => 'payp/installments/custom/validators/paypinstallmentspresentmentvalidator.php',

    ),
    'templates'   => array(
        'paypinstallmentsorder.tpl'                                         => 'payp/installments/views/admin/tpl/paypinstallmentsorder.tpl',
        'widget/presentment/paypinstallmentspresentment.tpl'                =>
            'payp/installments/views/widget/presentment/paypinstallmentspresentment.tpl',
        'widget/presentment/options/paypinstallmentsqualifiedoptions.tpl'   =>
            'payp/installments/views/widget/presentment/options/paypinstallmentsqualifiedoptions.tpl',
        'widget/presentment/options/paypinstallmentsqualifiedoptionssimple.tpl'   =>
            'payp/installments/views/widget/presentment/options/paypinstallmentsqualifiedoptionssimple.tpl',
        'widget/presentment/options/paypinstallmentsmultiplequalifiedoptions.tpl'   =>
            'payp/installments/views/widget/presentment/options/paypinstallmentsmultiplequalifiedoptions.tpl',
        'widget/presentment/options/paypinstallmentsunqualifiedoptions.tpl' =>
            'payp/installments/views/widget/presentment/options/paypinstallmentsunqualifiedoptions.tpl',
        'widget/presentment/paypinstallmentserror.tpl'                      =>
            'payp/installments/views/widget/presentment/paypinstallmentserror.tpl',
        'blocks/email/html/inc/paypinstallmentsfinancingdetails.tpl'        =>
            'payp/installments/views/tpl/blocks/email/html/inc/paypinstallmentsfinancingdetails.tpl',
        'blocks/email/plain/inc/paypinstallmentsfinancingdetails.tpl'       =>
            'payp/installments/views/tpl/blocks/email/plain/inc/paypinstallmentsfinancingdetails.tpl',
    ),
    'blocks'      => array(
        array(
            'template' => 'page/checkout/order.tpl',
            'block'    => 'checkout_order_btn_confirm_bottom',
            'file'     => 'views/tpl/blocks/page/checkout/paypinstallmentsorder_shippingandpayment.tpl',
        ),
        array(
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'checkout_payment_errors',
            'file'     => 'views/tpl/blocks/page/checkout/paypinstallmentspayment_checkout_payment_errors.tpl',
        ),
        array(
            'template' => 'page/details/inc/productmain.tpl',
            'block'    => 'details_productmain_price',
            'file'     => 'views/tpl/blocks/page/details/inc/paypinstallmentsdetails_productmain_price.tpl',
        ),
        array(
            'template' => 'page/checkout/basket.tpl',
            'block'    => 'checkout_basket_next_step_top',
            'file'     => 'views/tpl/blocks/page/checkout/paypinstallmentscheckout_basket_next_step_top.tpl',
        ),
        array(
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'checkout_payment_main',
            'file'     => 'views/tpl/blocks/page/checkout/paypinstallmentscheckout_payment_main.tpl',
        ),
        array(
            'template' => 'layout/sidebar.tpl',
            'block'    => 'sidebar',
            'file'     => 'views/tpl/blocks/layout/paypinstallmentslayout_sidebar_sidebar.tpl',
        ),
        array(
            'template' => 'layout/page.tpl',
            'block'    => 'content_main',
            'file'     => 'views/tpl/blocks/layout/paypinstallmentslayout_page_content_main.tpl',
        ),
        array(
            'template' => 'email/html/order_cust.tpl',
            'block'    => 'email_html_order_cust_paymentinfo_top',
            'file'     => 'views/tpl/blocks/email/html/email_html_order_cust_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/plain/order_cust.tpl',
            'block'    => 'email_plain_order_cust_paymentinfo',
            'file'     => 'views/tpl/blocks/email/plain/email_plain_order_cust_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_paymentinfo',
            'file'     => 'views/tpl/blocks/email/html/email_html_order_owner_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_ownerpaymentinfo',
            'file'     => 'views/tpl/blocks/email/plain/email_plain_order_ownerpaymentinfo.tpl',
        ),
        array(
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_form',
            'file'     => 'views/admin/tpl/blocks/admin_payment_main_form.tpl',
        ),
    ),
    'settings'    => array(

        array(
            'group' => 'paypInstallmentsGeneral',
            'name'  => 'paypInstallmentsActive',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'paypInstallmentsGeneral',
            'name'  => 'paypInstallmentsGenAdvertHome',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'paypInstallmentsGeneral',
            'name'  => 'paypInstallmentsGenAdvertCat',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'paypInstallmentsGeneral',
            'name'  => 'paypInstallmentsGenAdvertDetail',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'paypInstallmentsGeneral',
            'name'  => 'paypInstallmentsWithCalcValue',
            'type'  => 'bool',
            'value' => false,
        ),

        //Production mode settings
        //SOAP API credentials - API Username
        array(
            'group' => 'paypInstallmentsApi',
            'name'  => 'paypInstallmentsSoapUsername',
            'type'  => 'str',
            'value' => '',
        ),
        //SOAP API credentials - API Password
        array(
            'group' => 'paypInstallmentsApi',
            'name'  => 'paypInstallmentsSoapPassword',
            'type'  => 'str',
            'value' => '',
        ),
        //SOAP API credentials - Signature
        array(
            'group' => 'paypInstallmentsApi',
            'name'  => 'paypInstallmentsSoapSignature',
            'type'  => 'str',
            'value' => '',
        ),

        //REST API credentials - Client ID
        array(
            'group' => 'paypInstallmentsApi',
            'name'  => 'paypInstallmentsRestClientId',
            'type'  => 'str',
            'value' => '',
        ),
        //REST API credentials - Secret
        array(
            'group' => 'paypInstallmentsApi',
            'name'  => 'paypInstallmentsRestSecret',
            'type'  => 'str',
            'value' => '',
        ),

        //Sandbox mode settings
        //activate sandbox mode
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSandboxApi',
            'type'  => 'bool',
            'value' => false,
        ),
        //Sandbox SOAP API credentials - API Username
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSBSoapUsername',
            'type'  => 'str',
            'value' => '',
        ),
        //Sandbox SOAP API credentials - API Password
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSBSoapPassword',
            'type'  => 'str',
            'value' => '',
        ),
        //Sandbox SOAP API credentials - Signature
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSBSoapSignature',
            'type'  => 'str',
            'value' => '',
        ),

        //REST API credentials - Client ID
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSBRestClientId',
            'type'  => 'str',
            'value' => '',
        ),
        //REST API credentials - Secret
        array(
            'group' => 'paypInstallmentsSandboxApi',
            'name'  => 'paypInstallmentsSBRestSecret',
            'type'  => 'str',
            'value' => '',
        ),

        //Logging
        //activate logging?
        array(
            'group' => 'paypInstallmentsLogging',
            'name'  => 'paypInstallmentsLogging',
            'type'  => 'bool',
            'value' => false,
        ),
        // General logging
        // path to log file
        array(
            'group' => 'paypInstallmentsLogging',
            'name'  => 'paypInstallmentsLoggingFile',
            'type'  => 'str',
            'value' => 'paypinstallments.log',
        ),
        //define log level for general logging
        array(
            'group'      => 'paypInstallmentsLogging',
            'name'       => 'paypInstallmentsLoggingLevel',
            'type'       => 'select',
            'constrains' => 'DEBUG|INFO|ERROR',
            'value'      => 'INFO',
        ),
        // Logging of SOAP Calls
        //path to log file for SOAP calls
        array(
            'group' => 'paypInstallmentsLogging',
            'name'  => 'paypInstallmentsLoggingFileSoap',
            'type'  => 'str',
            'value' => 'paypinstallments_soap.log',
        ),
        //define log level for SOAP calls
        array(
            'group'      => 'paypInstallmentsLogging',
            'name'       => 'paypInstallmentsLoggingLevelSoap',
            'type'       => 'select',
            'constrains' => 'FINE|INFO|WARN|ERROR',
            'value'      => 'INFO',
        ),
    ),
    'events'      => array(
        'onActivate'   => 'paypInstallmentsEvents::onActivate',
        'onDeactivate' => 'paypInstallmentsEvents::onDeactivate',
    ),
);
