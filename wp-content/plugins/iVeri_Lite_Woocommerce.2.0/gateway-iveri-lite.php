<?php
/*
	Plugin Name: WooCommerce iVeri Lite Payment Gateway
	Plugin URI: 
	Description: A payment gateway for South African payment system, iVeri.
	Version: 2.0
	Author: Llewellyn Dawson / My IT Manager
	Author URI: http://www.myitmanager.co.za/
	Requires at least: 3.9
	Tested up to: 3.9

	Copyright: 2017 MiM.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

load_plugin_textdomain( 'wc_iveri', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );

add_action( 'plugins_loaded', 'woocommerce_iveri_lite_init', 0 );

function woocommerce_iveri_lite_init () {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    require_once( plugin_basename( 'classes/class-iveri-lite.php' ) );

    load_plugin_textdomain('woocommerce-iveri-lite', false, dirname(plugin_basename(__FILE__)) . '/');
    
    add_filter('woocommerce_payment_gateways', 'add_iveri_lite_gateway');

    function add_iveri_lite_gateway($methods) {
        $methods[] = 'iveri_lite';
        return $methods;
    }
    
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'iveri_lite_action_links');

    function iveri_lite_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'iveri-lite') . '</a>'
        );
        
        return array_merge($plugin_links, $links);
    }
    
    function iveri_styles(){
        wp_register_style( 'iveri-style', plugins_url( '/css/iveri-style.css', __FILE__ ), array(), '20170920', 'all' );
        wp_enqueue_style( 'iveri-style' );
    }
    add_action( 'wp_enqueue_scripts', 'iveri_styles' );
    
}