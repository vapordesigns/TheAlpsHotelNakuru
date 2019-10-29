<?php
/**
 * Plugin Name: WP Booking System
 * Plugin URI:  http://www.wpbookingsystem.com
 * Description: WP Booking System.
 * Version:     4.3.0
 * Author:      WP Booking System
 * Author URI:  http://www.wpbookingsystem.com
 *
 * Copyright (c) 2017 WP Booking System
 */

include 'include/createTables.php';
register_activation_hook( __FILE__, 'wpbs_install' );

define("WPBS_VERSION", "4.3.0");
define("WPBS_PATH",plugins_url('',__FILE__));
define("WPBS_DIR_PATH",dirname(__FILE__));
define("WPBS_NAME", "WP Booking System");
define("WPBS_SLUG", "wp-booking-system");


add_action( 'plugins_loaded', 'wpbs_load_textdomain' );
function wpbs_load_textdomain()
{
    load_plugin_textdomain( 'wpbs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}



include 'include/calendarLanguages.php';
include 'include/calendarFunctions.php';
include 'include/calendarAdmin.php';
include 'include/calendarCore.php';
include 'include/calendarAjax.php';

include 'include/formCore.php';
include 'include/formAjax.php';

include 'include/bookingCore.php';
include 'include/bookingAjax.php';

include 'include/pluginStructure.php';
include 'include/pluginShortcodeButton.php';
include 'include/pluginShortcode.php';
include 'include/pluginWidget.php';


// if(get_option('timezone_string'))
// {
//     @date_default_timezone_set(get_option('timezone_string'));
// } 
// elseif(get_option('gmt_offset'))
// {
//     @date_default_timezone_set(wpbs_tz_offset_to_name(get_option('gmt_offset')));
// }


if (is_admin()) 
{
	add_action('admin_menu', 'wpbs_menu');   
    function wpbs_admin_enqueue_files()
    {
        wp_enqueue_style( 'wpbs-calendar',          WPBS_PATH . '/css/wpbs-calendar.css', array(), WPBS_VERSION );
        wp_enqueue_style( 'wpbs-admin',             WPBS_PATH . '/css/wpbs-admin.css', array(), WPBS_VERSION );
        wp_enqueue_style( 'colorpicker',            WPBS_PATH . '/css/colorpicker.min.css', array(), WPBS_VERSION );
        wp_enqueue_style( 'chosen',                 WPBS_PATH . '/css/chosen.min.css', array(), WPBS_VERSION );

        wp_enqueue_script('postbox');        
		wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-sortable');
        // wp_enqueue_script('wpbs-admin',             WPBS_PATH . '/js/wpbs-admin.js', array('jquery'));

        wp_register_script( 'wpbs-admin',           WPBS_PATH . '/js/wpbs-admin.js', array('jquery'), WPBS_VERSION );
        wp_localize_script( 'wpbs-admin',           'wpbs_ajaxurl', admin_url( 'admin.php?page=wp-booking-system&do=ajax-call&noheader=true' ) );

        wp_enqueue_script( 'wpbs-admin' );

        wp_enqueue_script('wpbs-admin-forms',       WPBS_PATH . '/js/wpbs-forms.js', array('jquery','jquery-ui-sortable'), WPBS_VERSION );
        wp_enqueue_script('wpbs-admin-bookings',    WPBS_PATH . '/js/wpbs-bookings.js', array('jquery'), WPBS_VERSION );
        wp_enqueue_script('wpbs-colorpicker',       WPBS_PATH . '/js/colorpicker.min.js', array('jquery'), WPBS_VERSION );
        // wp_enqueue_script('custom-select',          WPBS_PATH . '/js/custom-select.js', array('jquery'));
        wp_enqueue_script('chosen',                 WPBS_PATH . '/js/chosen.jquery.min.js', array('jquery'), WPBS_VERSION );
        wp_enqueue_script('data-tables',            WPBS_PATH . '/js/jquery.dataTables.min.js', array('jquery'), WPBS_VERSION );
    }
    add_action( 'admin_head','wpbs_js_translations');
    add_action( 'admin_init', 'wpbs_admin_enqueue_files' );       
} 
else 
{
    add_action('wp_head','wpbs_ajaxurl');
}

//Admin Menu
function wpbs_menu()
{
    if ( !current_user_can('manage_options') )
    {
        if ( wpbs_user_has_calendars( get_current_user_id( ) ) )
        {
            add_menu_page(
                'WP Booking System', 
                'WP Booking System', 
                'read', 
                'wp-booking-system', 
                'wpbs_calendars', 
                WPBS_PATH . '/images/date-button.gif' , 
                '375.457' 
            );
            add_submenu_page( 
                'wp-booking-system', 
                __('Calendars','wpbs'), 
                __('Calendars','wpbs'), 
                'read', 
                'wp-booking-system', 
                'wpbs_calendars' 
            );
            add_submenu_page( 
                'wp-booking-system', 
                __('Sync', 'wpbs'), 
                __('Sync', 'wpbs'), 
                'read', 
                'wp-booking-system-ical', 
                'wpbs_ical' 
            );
        }
    }
    else
    {
        add_menu_page( 
            'WP Booking System', 
            'WP Booking System', 
            'read', 
            'wp-booking-system', 
            'wpbs_calendars', 
            WPBS_PATH . '/images/date-button.gif' , 
            '375.457' 
        );
        add_submenu_page( 
            'wp-booking-system', 
            __('Calendars','wpbs'), 
            __('Calendars','wpbs'), 
            'manage_options', 
            'wp-booking-system', 
            'wpbs_calendars' 
        );
        add_submenu_page( 
            'wp-booking-system', 
            __('Default Legend','wpbs'),
            __('Default Legend','wpbs'), 
            'manage_options', 
            'wp-booking-system-default-legend', 
            'wpbs_default_legend' 
        );
        add_submenu_page( 
            'wp-booking-system', 
            __('Forms','wpbs'), 
            __('Forms','wpbs'), 
            'manage_options', 
            'wp-booking-system-forms', 
            'wpbs_forms' 
        );
        add_submenu_page( 
            'wp-booking-system', 
            __('Sync', 'wpbs'), 
            __('Sync', 'wpbs'), 
            'manage_options', 
            'wp-booking-system-ical', 
            'wpbs_ical' 
        );
        add_submenu_page( 
            'wp-booking-system', 
            __('Settings','wpbs'),
            __('Settings','wpbs'), 
            'manage_options', 
            'wp-booking-system-settings', 
            'wpbs_settings' 
        );  
        add_submenu_page( 
            'wp-booking-system',
            __('Backup/Restore','wpbs'), 
            __('Backup/Restore','wpbs'), 
            'manage_options', 
            'wp-booking-system-export', 
            'wpbs_export' 
        );        
    }



    add_action('admin_print_scripts-toplevel_page_wp-booking-system',                           'wpbs_dashboard_toggle');
    add_action('admin_print_scripts-wp-booking-system_page_wp-booking-system-default-legend',   'wpbs_dashboard_toggle');
    add_action('admin_print_scripts-wp-booking-system_page_wp-booking-system-forms',            'wpbs_dashboard_toggle');
    add_action('admin_print_scripts-wp-booking-system_page_wp-booking-system-settings',         'wpbs_dashboard_toggle');
    
}


// Change the Admin Footer when on plugin pages
add_action( 'admin_footer_text', 'wpbs_footer_ad' );
function wpbs_footer_ad($text)
{

    $screen = get_current_screen();
    if ( !empty( $screen->id ) && strpos( $screen->id, 'wp-booking-system' ) !== false ) 
    {
        echo __('<strong>WP Booking System</strong> - If you like our plugin please help us spread the word by rating <a href="https://wordpress.org/support/view/plugin-reviews/wp-booking-system?filter=5" title="Please help us spread the word" target="_blank" rel="noopener noreferrer">★★★★★</a> on <a href="https://wordpress.org/support/view/plugin-reviews/wp-booking-system?filter=5" title="Please help us spread the word" target="_blank" rel="noopener noreferrer">WordPress.org</a>! It helps us a lot!', 'wpbs');
        return;
    }

    return $text;
}

function wpbs_dashboard_toggle()
{
    wp_enqueue_script('dashboard');
}

// Ajax Hooks
add_action('wp_ajax_wpbs_changeDayAdmin', 'wpbs_changeDayAdmin_callback');
add_action('wp_ajax_wpbs_changeDay', 'wpbs_changeDay_callback');
add_action('wp_ajax_nopriv_wpbs_changeDay', 'wpbs_changeDay_callback');

add_action('wp_ajax_wpbs_submitForm' , 'wpbs_submitForm_callback');
add_action('wp_ajax_nopriv_wpbs_submitForm' , 'wpbs_submitForm_callback');

add_action('wp_ajax_wpbs_bookingModalData' , 'wpbs_bookingModalData_callback');
add_action('wp_ajax_wpbs_bookingMarkAsRead' , 'wpbs_bookingMarkAsRead_callback');


function wpbs_ajaxurl() 
{
    $wpbsOptions = json_decode(get_option('wpbs-options'),true);
    ?>
    <script type="text/javascript">var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';<?php if(!empty($wpbsOptions['enableReCaptcha']) && $wpbsOptions['enableReCaptcha'] == 'yes'):?> var recaptcha_public = '<?php echo $wpbsOptions['recaptcha_public'];?>';<?php endif;?></script>
    <?php
}

function wpbs_js_translations() 
{
    ?>
    <script type="text/javascript">
        var wpbs_edit_availatility = '<?php echo __('Edit Availability','wpbs'); ?>';
        var wpbs_accept_booking = '<?php echo __('Accept Booking','wpbs'); ?>';
        var wpbs_edit_booking = '<?php echo __('Edit Booking','wpbs'); ?>';
        var wpbs_delete_booking = '<?php echo __('Delete Booking','wpbs'); ?>';
    </script>
    <?php
}



add_filter( 'admin_menu', 'wpbs_add_submenu_count');

function wpbs_add_submenu_count()
{
    global $wpdb, $menu;
    
    if ( current_user_can('manage_options') )
        $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0';
    else
    {
        $userCalendars = wpbs_get_user_calendars( get_current_user_id() );

        if ( $userCalendars && count($userCalendars) > 0)
        {
            $calendarIds    = implode(",", $userCalendars);

            $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0 AND calendarID IN (' . $calendarIds . ')';
        }
        else
            $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0';
    }

    $rows = $wpdb->get_results( $sql, ARRAY_A );
    $count = $wpdb->num_rows;
    $menu['375.457'][0] .= " <span class='update-plugins count-$count'><span class='plugin-count'>" . number_format_i18n($count) . '</span></span>';
}

add_action('wp_before_admin_bar_render', 'wpbs_admin_bar_notifications',1);

function wpbs_admin_bar_notifications($wp_admin_bar)
{
    global $wp_admin_bar, $wpdb;

    if ( current_user_can('manage_options') )
        $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0';
    else
    {
        $userCalendars = wpbs_get_user_calendars( get_current_user_id() );
        if ( $userCalendars && count($userCalendars) > 0)
        {
            $calendarIds    = implode(",", $userCalendars);

            $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0 AND calendarID IN (' . $calendarIds . ')';
        }
        else
        {
            return false;
            // $sql = 'SELECT bookingRead FROM ' . $wpdb->prefix . 'bs_bookings WHERE bookingRead = 0';
        }
    }
    
    $rows = $wpdb->get_results( $sql, ARRAY_A );
    $count = $wpdb->num_rows;

    $args = array(
        'id' => 'wp-bookig-system-admin',
        'href' => admin_url('admin.php?page=wp-booking-system'),
        'parent' => 'root-default',
    );

    if($count == 1)
    {
        $title = ' ' . __('New Booking','wpbs');
    } 
    else
    {
        $title = ' ' . __('New Bookings','wpbs');
    }
    $args['meta']['title'] = $title;

    if($count == 0)
    {
        $display = '<span class="wpbs-ab-text">'.$count.' '.$title.'</span>';
    } 
    else
    {
        $display = '<span class="wpbs-update-bubble">'.$count.'</span><span class="wpbs-ab-text-active">'.$title.'</span>';
    }
    $args['title'] = $display;
    $wp_admin_bar->add_node($args);
}
$wpbsOptions = json_decode(get_option('wpbs-options'),true);
if( @$wpbsOptions['enableiCal'] == 'yes' && isset($_GET['wp-booking-system-ical']) && !empty($_GET['wp-booking-system-ical']))
{
    include 'include/pluginiCal.php';
    die();
}

/**
 *
 * Returns all the calendars the user is assegned to or FALSE
 *
 * @param   $userid     The current users ID
 *
 * @return  List of calendarIDs on success or FALSE on failure
 */

function wpbs_get_user_calendars( $userid )
{
    if ( wpbs_user_has_calendars( $userid ) )
    {
        global $wpdb;

        $sql        = 'SELECT calendarUsers, calendarID FROM ' . $wpdb->prefix . 'bs_calendars;';
        $rows       = $wpdb->get_results( $sql, ARRAY_A );

        
        $calendars  = array();
        foreach ($rows as $calendar )
        {
            $users  = json_decode( $calendar['calendarUsers'] );
            if ( in_array( $userid, $users ) )
            {
                $calendars[] = $calendar['calendarID'];
            }
        }

        return $calendars;
    }
    else
        return false;
}

/**
 *
 * Checks if the current user has calendars assigned
 *
 * @param   $userid     The current users ID
 *
 * @return  TRUE on success or FALSE on failure
 */
function wpbs_user_has_calendars( $userid )
{
    global $wpdb;

    $sql        = 'SELECT calendarUsers FROM ' . $wpdb->prefix . 'bs_calendars;';
    $rows       = $wpdb->get_results( $sql, ARRAY_A );

    foreach ($rows as $calendar )
    {
        $users  = json_decode( $calendar['calendarUsers'] );
        if ( in_array( $userid, $users ) )
        {
            return true;
        }
    }

    return false;
}
