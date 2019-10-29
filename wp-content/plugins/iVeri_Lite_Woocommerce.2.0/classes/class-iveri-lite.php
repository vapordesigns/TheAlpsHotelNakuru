<?php
/**
 * iVeri Payment Gateway
 *
 * Provides a iVeri Payment Gateway.
 *
 * @class 	WC_Gateway_IVeri_Lite
 * @package	WooCommerce
 * @category	Payment Gateways
 * @author	My IT Manager
 */
class iveri_lite extends WC_Payment_Gateway {
    /**
     * Constructor
     */
    public function __construct() {
        $this->id                   = "iveri_lite";
        $this->method_title         = __("Iveri Lite", 'iveri-lite');
        $this->method_description   = __("Iveri Lite Payment Gateway Plug-in for WooCommerce", 'iveri-lite');
        $this->title                = __("Iveri Lite", 'iveri-lite');
        $this->icon                 = WP_PLUGIN_URL . "/" . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/logo.png';
        $this->response_url         = add_query_arg( 'wc-api', 'iveri_lite', home_url( '/' ) );
        
        // Load form fields
        $this->init_form_fields();
        // Load the settings and turn the settings into variables
        $this->init_settings();
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }
        
        // add a hook to launch our payment gateway page
        
        add_action('woocommerce_api_iveri_lite', array($this,'check_payment_respone'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
        add_action('woocommerce_receipt_iveri_lite', array( $this, 'receipt_page' ));
        // hook to update the order
        //add_action('woocommerce_thankyou', array($this,'thank_you_page'));
        //add_action('woocommerce_thankyou_iveri_lite', array($this,'check_payment_respone'));
        
    }
    // End __construct()
    
    // Init admin form fields
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable / Disable', 'iveri-lite'),
                'label' => __('Enable This Payment Gateway', 'iveri-lite'),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'iveri-lite'),
                'type' => 'text',
                'desc_tip' => __('Payment Title of Checkout Process.', 'iveri-lite'),
                'default' => __('Credit cards,etc', 'iveri-lite')
            ),
            'description' => array(
                'title' => __('Description', 'iveri-lite'),
                'type' => 'textarea',
                'desc_tip' => __('Payment Title of Checkout Process.', 'iveri-lite'),
                'default' => __('Successfully payment through credit card.', 'iveri-lite'),
                'css' => 'max-width:450px;'
            ),
            'live_url' => array(
                'title' => __('Payment Gateway Live URL', 'iveri-lite'),
                'type' => 'text',
                'desc_tip' => __('This is the url that will be used for live transactions', 'iveri-lite')
            ),
            'test_url' => array(
                'title' => __('Payment Gateway TEST URL', 'iveri-lite'),
                'type' => 'text',
                'desc_tip' => __('This is the url that will be used for TEST transactions', 'iveri-lite')
            ),
            'live_application_id' => array(
                'title' => __('Iveri Lite Live Application ID', 'iveri-lite'),
                'type' => 'text',
                'desc_tip' => __('This the application ID assigned to this merchant for Live transactions', 'iveri-lite')
            ),
            'test_application_id' => array(
                'title' => __('Iveri Lite Test Application ID', 'iveri-lite'),
                'type' => 'text',
                'desc_tip' => __('This the application ID assigned to this merchant for Testing transactions', 'iveri-lite')
            ),
            'test_mode' => array(
                'title' => __('Iveri Lite Test Mode', 'iveri-lite'),
                'label' => __('Enable Test Mode', 'iveri-lite'),
                'type' => 'checkbox',
                'description' => __('This is the test mode of gateway.', 'iveri-lite'),
                'default' => 'no'
            )
        );
    }

    // Response handled for payment gateway
    public function process_payment($order_id) {
        global $woocommerce;
        
        $order = wc_get_order($order_id);
        
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    // Validate fields
    public function validate_fields() {
        return true;
    }

    public function generate_iveri_lite_payment_form($order_id) {
        global $woocommerce;
        
        $order = wc_get_order($order_id);
        // checking for transiction
        $environment = ($this->test_mode == "yes") ? 'TRUE' : 'FALSE';
        $applicationid = ("TRUE" == $environment) ? $this->test_application_id : $this->live_application_id;
        
        // Decide which URL to post to
        $environment_url = ("TRUE" == $environment) ? $this->test_url : $this->live_url;
        
        $paytotal = $order->get_total() * 100;
        // echo $paytotal;
        
        $payload = array(
            // Iveri Lite Credentials
            "Lite_Merchant_ApplicationID"      => $applicationid,
            // "Lite_Authorization"            => "FALSE"
            "LITE_CONSUMERORDERID_PREFIX"      => 'AUTOGENERATE',
            "Ecom_ConsumerOrderID"             => $order->get_order_number(),
            "Ecom_TransactionComplete"         => "FALSE",
            // Order total
            "Lite_Order_Amount"                => $paytotal,
            
            // return url information
            "Lite_Website_Successful_Url"      => $this->response_url,
            "Lite_Website_Fail_Url"            => $this->response_url,
            "Lite_Website_TryLater_Url"        => $this->response_url,
            "Lite_Website_Error_Url"           => $this->response_url,
            
            // Shipping details.
            'Ecom_ShipTo_Postal_Name_First'    => $order->shipping_first_name,
            'Ecom_ShipTo_Postal_Name_Last'     => $order->shipping_last_name,
            'Ecom_ShipTo_Postal_Street_Line1'  => $order->shipping_address_1,
            'Ecom_ShipTo_Postal_Street_Line2'  => $order->shipping_address_2,
            'Ecom_ShipTo_Postal_City'          => $order->shipping_city,
            'Ecom_ShipTo_Postal_StateProv'     => $order->shipping_state,
            'Ecom_ShipTo_Postal_PostalCode'    => $order->shipping_postcode,
            'Ecom_ShipTo_Postal_CountryCode'   => $order->shipping_country,
            'Ecom_ShipTo_Telecom_Phone_Number' => $order->billing_phone,
            'Ecom_ShipTo_Online_Email'         => $order->billing_email,

            // Billing details.
            'Ecom_BillTo_Postal_Name_First'    => $order->billing_first_name,
            'Ecom_BillTo_Postal_Name_Last'     => $order->billing_last_name,
            'Ecom_BillTo_Postal_Street_Line1'  => $order->billing_address_1,
            'Ecom_BillTo_Postal_Street_Line2'  => $order->billing_address_2,
            'Ecom_BillTo_Postal_City'          => $order->billing_city,
            'Ecom_BillTo_Postal_StateProv'     => $order->billing_state,
            'Ecom_BillTo_Postal_PostalCode'    => $order->billing_postcode,
            'Ecom_BillTo_Postal_CountryCode'   => $order->billing_country,
            'Ecom_BillTo_Telecom_Phone_Number' => $order->billing_phone,
            'Ecom_BillTo_Online_Email'         => $order->billing_email,
            
            // other fields required by Iveri Lite
            "Ecom_Payment_Card_Protocols"      => "iVeri",
            "Lite_Version"                     => "woocommerce-gateway-iveri_lite_2.0",
        
        );
        
        // var_dump($payload);
        // add single line item with the order total
        $all_items = array();
        $linecount = 0;
        
        //foreach ($order->get_items() as $orderitem) {
        //    $linecount = $linecount + 1;
        //    $all_items["Lite_Order_LineItems_Product_" . $linecount] = $orderitem['name'];
        //    $all_items["Lite_Order_LineItems_Quantity_" . $linecount] = $orderitem['quantity'];
        //    $all_items["Lite_Order_LineItems_Amount_" . $linecount] = (($orderitem['subtotal'] * 100) + ($orderitem['subtotal_tax'] * 100))/$orderitem['quantity'];
        //}
        
        $linecount++;
        $all_items['Lite_Order_LineItems_Product_'.$linecount]  = $order->get_order_number();
        $all_items['Lite_Order_LineItems_Quantity_'.$linecount] = '1';
        $all_items['Lite_Order_LineItems_Amount_'.$linecount] = $paytotal;

        // Check if discount apply
        //if ( $order->get_total_discount() > 0 ) {
        //    $payload['Lite_Order_DiscountAmount'] = $order->get_total_discount() * 100;
        //}
        
        $data = array_merge($payload, $all_items);
        
        $formbdy = '<form name="redirectpost" id="redirectpost" method="post" action="' . $environment_url . '" target="myIframe">';
        if (! is_null($data)) {
            foreach ($data as $k => $v) {
                $formbdy .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
            }
        }
        $formbdy .= ' 
            <input type="submit" class="button alt" id="proPayment" value="' . __( 'Pay With Credit/Debit Card', 'iveri-lite' ) . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __( 'Cancel order &amp; restore cart', 'iveri-lite' ) . '</a>
            </form>
            <div id="iframecontainer">
                <iframe src="'.$environment_url.'" name="myIframe" id="myIframe" frameborder="0" scrolling="yes" width="100%" height="600">
                    <p>iframes are not supported by your browser.</p>
                </iframe>
            </div>
            
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $("#iframecontainer").hide();
                    $("#myIframe").hide();
                    $("#redirectpost").submit(function(e) { 
                        setTimeout(function(){ 
                            $("#iframecontainer").show();
                            jQuery("#myIframe").show();  
                        }, 1000);
                    });
                });
             </script>
        ';
        
        return $formbdy;
    }

    public function receipt_page($order) {
        echo '<p>' . __('Thank you for your order, please click the button below to pay with Iveri Lite.', 'woocommerce-gateway-iveri_lite') . '</p>';
        echo $this->generate_iveri_lite_payment_form($order);
    }

    public function check_payment_respone() {
        global $woocommerce;
        
        if (isset($_POST['LITE_TRANSACTIONINDEX'])) {
        
            $order_id = $_POST['ECOM_CONSUMERORDERID'];
            
            $order = new WC_Order($order_id);
            //echo "order - ".$order;
            $this->log( 'iVeri Data: '. print_r( $_POST, true ) );
            $this->log( 'Order Data: '. print_r( $order, true ) );
            $this->log( 'Return URL: '. $this->get_return_url($order) );

            if (isset($_POST["ECOM_PAYMENT_CARD_PROTOCOLS"])) {

                $iVeri = $_POST["ECOM_PAYMENT_CARD_PROTOCOLS"];

                if ($iVeri == 'iVeri') {
                    $iVeriResult = $_POST["LITE_PAYMENT_CARD_STATUS"];

                    $pay_result_desc = $_POST["LITE_RESULT_DESCRIPTION"];

                    switch ($iVeriResult) {
                        case 0:
                            $order->add_order_note(__('Iveri Lite complete payment.', 'iveri-lite'));
                            $order->payment_complete();
                            $woocommerce->cart->empty_cart();
                            //wp_redirect(get_site_url().'/checkout/order-received/');
                            echo "<script type='text/javascript'>window.top.location = '" . $this->get_return_url($order) . "'</script>";
                            exit;
                            break;
                        case 1:
                        case 2:
                        case 5:
                        case 9:
                            wc_add_notice(__('Payment error: Please try again later ', 'woothemes') . $pay_result_desc, 'error');
                            $order->add_order_note(__('Payment error: Please try again later', 'woothemes') . $pay_result_desc);
                            //wp_redirect(get_site_url().'/checkout');
                            echo "<script type='text/javascript'>window.top.location = '" . get_site_url().'/checkout/' . "'</script>";
                            exit;
                            break;
                        case 255:
                            wc_add_notice(__('Payment error: An Error Occurred ', 'woothemes') . $pay_result_desc, 'error');
                            $order->add_order_note(__('Payment error: An Error Occurred', 'woothemes') . $pay_result_desc);
                            //wp_redirect(get_site_url().'/checkout');
                            echo "<script type='text/javascript'>window.top.location = '" . get_site_url().'/checkout/' . "'</script>";
                            exit;
                            break;
                        default:
                            wc_add_notice(__('Payment failed: ', 'woothemes') . $pay_result_desc, 'error');
                            $order->add_order_note(__('Payment failed', 'woothemes') . $pay_result_desc);
                            $order->update_status('failed');
                            //wp_redirect(get_site_url().'/checkout');
                            echo "<script type='text/javascript'>window.top.location = '" . get_site_url().'/checkout/' . "'</script>";
                            exit;
                            break;
                    }
                }
            }
        } else {
             echo "<script type='text/javascript'>window.top.location.reload();</script>";
        }
        die();
    }
    
    /**
    * Log system processes.
    */
    public function log( $message ) {
        if ( $this->test_mode == "yes" ) {
            if ( ! $this->logger ) {
                $this->logger = new WC_Logger();
            }
            $this->logger->add( 'iveri', $message );
        }
    }
}