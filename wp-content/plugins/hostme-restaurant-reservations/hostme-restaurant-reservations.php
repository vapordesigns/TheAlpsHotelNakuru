<?php
/*
Plugin Name: Hostme Restaurant Reservations
Plugin URI: https://wordpress.org/plugins/hostme-restaurant-reservations
Description: Instant online reservations. 50 reservations/month for free, no extra fees. Set up in 1 minute & manage on tablets, smartphones or desktop.
Version: 1.2
Author: Hostme
Author URI: https://www.hostmeapp.com/
License: GPLv2 or later
License URL: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: hostmerr
Domain Path: /languages
*/

//	Set plugin path and URL
define( 'HOSTME_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HOSTME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//	API base URL.
define( 'HOSTME_API_URL', 'https://service.hostmeapp.com' );
//	JS URL
define( 'HOSTME_WIDGET_JS_URL', 'https://tables.hostmeapp.com/widget.js' );
//	Link restaurant base URL
define( 'HOSTME_RESTAURANT_URL', 'https://tables.hostmeapp.com/#/restaurants/' );

/**
 * Include AJAX functions file.
 *
 * @since	1.0
 *
 */
require_once( HOSTME_PLUGIN_DIR . 'include/ajax.php' );

class Hostme_Widget extends WP_Widget {
	const HOSTME_VERSION = '1.2';

	/**
	 * Widget constructor.
	 *
	 * @param	void
	 * @return	void
	 * @since	1.0
	 *
	 */
	public function __construct() {
		parent::__construct(
			'hostme_widget',
			esc_html__( 'Hostme', 'hostmerr' ),
			array( 
				'classname' => 'hostme_widget',
				'description' => esc_html__( 'Hostme description', 'hostmerr' ),
				//'customize_selective_refresh' => true,
			)
		);

		//	Output JS and CSS that we need for the widget.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		add_action( 'wp_head', array( $this, 'hide_widget_edit_icon' ) );
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	$args	Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param	array	$instance	The settings for the particular instance of the widget.
	 * @return	string
	 * @since	1.0
	 *
	 */
	public function widget( $args, $instance ) {
		$restaurant_name = isset( $instance['hostme_restaurant_name'] ) ? $instance['hostme_restaurant_name'] : '';
		$restaurant_id = isset( $instance['hostme_restaurant_id'] ) ? $instance['hostme_restaurant_id'] : '';
		$widget_type = isset( $instance['hostme_widget_type'] ) ? $instance['hostme_widget_type'] : '';

		echo $args['before_widget'];

		if ( ! empty( $instance['hostme_restaurant_name'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_hostme_title', $restaurant_name ) . $args['after_title'];
		}

		if ( empty( $restaurant_id ) ) {
			_e( 'Restaurant ID is missing', 'hostmerr' );
		} else {
			echo "<div class='j-hostme-widget' data-hostme-id='" . absint( $restaurant_id ) . "' data-widget-type='" . esc_attr( $widget_type ) . "'></div> <script src='" . HOSTME_WIDGET_JS_URL . "' async></script>";
		}

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options HTML in the admin area.
	 *
	 * @param	array	$instance	Existing or non-existing widget options.
	 * @return	string
	 * @since	1.0
	 *
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( ( array ) $instance, array( 
			'hostme_restaurant_id' => '',
			'hostme_restaurant_name' => '',
			'hostme_widget_type' => '',
			'hostme_search_email' => '',
			'hostme_email' => '',
			'hostme_name' => '',
			'hostme_address' => '',
			'hostme_phone' => '',
		) );

		$show_connect_register_panel = true;
		$show_select_restaurant_panel = false;
		$show_current_restaurant_panel = false;

		if ( ! empty( $instance['hostme_restaurant_id'] ) && ! empty( $instance['hostme_restaurant_name'] ) ) {
			$show_connect_register_panel = false;
			$show_select_restaurant_panel = false;
			$show_current_restaurant_panel = true;
		}
		?>
		<div class="host-me-id" id="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>">
			<div class="hostme-panel-connect-register" <?php echo ! $show_connect_register_panel ? 'style="display: none;"' : ''; ?>>
				<p><input type="button" name="hostme_connect_restaurant" id="hostme-connect-restaurant" class="hostme-btn button widefat" value="<?php _e( "I'm registered on Hostme", 'hostmerr' ); ?>"/></p>
				<hr/>
				<p><?php printf( __( 'If you are not registered on <a target="_blank" href="%s">Hostme</a> please enter your restaurant details:', 'hostmerr' ), 'https://www.hostmeapp.com/' ); ?></p>
				<p>
					<input type="email" name="<?php echo $this->get_field_name( 'hostme_email' ); ?>" id="<?php echo $this->get_field_id( 'hostme_email' ); ?>" class="widefat hostme-register-email" value="<?php echo esc_attr( $instance['hostme_email'] ); ?>" placeholder="<?php _e( 'your@email.com', 'hostmerr' ); ?>" />
				</p>
				<p>
					<input type="text" name="<?php echo $this->get_field_name( 'hostme_name' ); ?>" id="<?php echo $this->get_field_id( 'hostme_name' ); ?>" class="widefat hostme-register-name" value="<?php echo esc_attr( $instance['hostme_name'] ); ?>" placeholder="<?php _e( 'Restaurant Name', 'hostmerr' ); ?>" />
				</p>
				<p>
					<input type="text" name="<?php echo $this->get_field_name( 'hostme_address' ); ?>" id="<?php echo $this->get_field_id( 'hostme_address' ); ?>" class="widefat hostme-register-address" value="<?php echo esc_attr( $instance['hostme_address'] ); ?>" placeholder="<?php _e( 'Restaurant Address', 'hostmerr' ); ?>" />
				</p>
				<p>
					<input type="tel" name="<?php echo $this->get_field_name( 'hostme_phone' ); ?>" id="<?php echo $this->get_field_id( 'hostme_phone' ); ?>" class="widefat hostme-register-phone" value="<?php echo esc_attr( $instance['hostme_phone'] ); ?>" placeholder="" />
					<input type="hidden" name="hostme_international_phone" class="widefat hostme-register-international-phone" value="<?php echo esc_attr( $instance['hostme_phone'] ); ?>" />
				</p>
				<p class="hostme-submit-btn"><button name="hostme_register_restaurant" id="hostme-register-restaurant" class="hostme-btn button widefat" data-nonce="<?php echo esc_attr( wp_create_nonce( 'hostme-register-restaurant' ) ); ?>" disabled><?php _e( 'Continue', 'hostmerr' ); ?><span class="hostme-spinner"></span></button>
				<p class="text-center">
					<a href="http://www.hostmeapp.com/terms/"><?php _e( 'Terms of Use', 'hostmerr' ); ?></a> | <a href="http://www.hostmeapp.com/privacy/"><?php _e( 'Privacy Policy', 'hostmerr' ); ?></a>
				</p>
			</div>
			<div class="hostme-panel-select-restaurant" <?php echo ! $show_select_restaurant_panel ? 'style="display: none;"' : 'style="display: block;"'; ?>>
				<p><?php _e( 'Enter your email below and pick a restaurant from the list.', 'hostmerr' ); ?></p>
				<p class="hostme-search-restaurant-field">
					<input type="email" name="<?php echo $this->get_field_name( 'hostme_search_email' ); ?>" id="<?php echo $this->get_field_id( 'hostme_search_email' ); ?>" class="widefat hostme-search-email" value="<?php echo esc_attr( $instance['hostme_search_email'] ); ?>" placeholder="<?php _e( 'your@email.com', 'hostmerr' ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'hostme-search-restaurant' ) ); ?>"/>
					<span class="spinner"></span>
				</p>
				<div class="hostme-restaurant-list-container">

				</div>				
				<p class="hostme-no-restaurant-found"><?php _e( 'No restaurants found.', 'hostmerr' ); ?></p>
				<p class="hostme-go-back-container"><a class="hostme-go-back"><?php _e( 'Go back', 'hostmerr' ); ?></a></p>
			</div>
			<div class="hostme-panel-current-restaurant" <?php echo ! $show_current_restaurant_panel ? 'style="display: none;"' : 'style="display: block;"'; ?>>
				<input type="hidden" name="<?php echo $this->get_field_name( 'hostme_restaurant_id' ); ?>" id="hostme_restaurant_id" value="<?php echo esc_attr( $instance['hostme_restaurant_id'] ); ?>"/>
				<input type="hidden" name="<?php echo $this->get_field_name( 'hostme_restaurant_name' ); ?>" id="hostme_restaurant_name" value="<?php echo esc_attr( $instance['hostme_restaurant_name'] ); ?>"/>
				<p>
					<label for="<?php echo $this->get_field_id( 'hostme_restaurant_name' ); ?>"><?php _e( 'Current Restaurant:', 'hostmerr' ); ?></label>
					<br/>
					<span class="hostme-current-restaurant-text"><?php echo esc_html( $instance['hostme_restaurant_name'] ); ?></span>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'hostme_widget_type' ); ?>"><?php _e( 'Widget layout:', 'hostmerr' ); ?></label>
					<select name="<?php echo $this->get_field_name( 'hostme_widget_type' ); ?>" id="<?php echo $this->get_field_id( 'hostme_widget_type' ); ?>" class="widefat">
						<option value="vertical" <?php selected( 'vertical', $instance['hostme_widget_type'] ); ?>><?php _e( 'Vertical', 'hostmerr' ); ?></option>
						<option value="horizontal" <?php selected( 'horizontal', $instance['hostme_widget_type'] ); ?>><?php _e( 'Horizontal', 'hostmerr' ); ?></option>
					</select>
				</p>
				<p>
					<?php _e( 'Link to restaurant reservation page:', 'hostmerr' ); ?>
					<br/>
					<a href="<?php echo esc_url( HOSTME_RESTAURANT_URL . $instance['hostme_restaurant_id'] ); ?>"><?php echo esc_html( HOSTME_RESTAURANT_URL . $instance['hostme_restaurant_id'] ); ?></a>
				</p>
				<p><input type="button" name="hostme_change_restaurant" id="hostme-change-restaurant" class="hostme-btn button" value="<?php _e( 'Change Restaurant', 'hostmerr' ); ?>"/></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['hostme_restaurant_id'] = ! empty( $new_instance['hostme_restaurant_id'] ) ? absint( $new_instance['hostme_restaurant_id'] ) : '';
		$instance['hostme_restaurant_name'] = ! empty( $new_instance['hostme_restaurant_name'] ) ? sanitize_text_field( $new_instance['hostme_restaurant_name'] ) : '';
		$instance['hostme_widget_type'] = ! empty( $new_instance['hostme_widget_type'] ) ? sanitize_text_field( $new_instance['hostme_widget_type'] ) : '';
		$instance['hostme_search_email'] = ! empty( $new_instance['hostme_search_email'] ) ? sanitize_email( $new_instance['hostme_search_email'] ) : '';
		$instance['hostme_email'] = ! empty( $new_instance['hostme_email'] ) ? sanitize_email( $new_instance['hostme_email'] ) : '';
		$instance['hostme_name'] = ! empty( $new_instance['hostme_name'] ) ? sanitize_text_field( $new_instance['hostme_name'] ) : '';
		$instance['hostme_address'] = ! empty( $new_instance['hostme_address'] ) ? sanitize_text_field( $new_instance['hostme_address'] ) : '';
		$instance['hostme_phone'] = ! empty( $new_instance['hostme_phone'] ) ? sanitize_text_field( $new_instance['hostme_phone'] ) : '';

		return $instance;
	}

	/**
	 * Load all the required JS/CSS files that we need for the widget.
	 *
	 * @param	string	$hook
	 * @return	void
	 * @since	1.0
	 *
	 */
	public function enqueue_scripts( $hook ) {
		//	Load custom JS/CSS only on widgets page.
		if ( $hook == 'widgets.php' || is_customize_preview() ) {
	   		wp_enqueue_script( 'hostme-widget', HOSTME_PLUGIN_URL . 'js/functions.js', array( 'jquery' ), self::HOSTME_VERSION, true );
	
			//	Send some strings to functions.js so that we can use them. :)	
	   		wp_localize_script( 'hostme-widget', 'hostme', array(
				'plugin_url' => HOSTME_PLUGIN_URL,
				'admin_url' => admin_url(),
				'current_restaurant_text' => __( 'Restaurant', 'hostmerr' )
	   		) );

	   		wp_enqueue_script( 'hostme-widget-phoneval', HOSTME_PLUGIN_URL . 'js/intlTelInput.min.js', array( 'jquery' ), self::HOSTME_VERSION, true );
	   		
	   		wp_enqueue_style( 'hostme-widget-phoneval', HOSTME_PLUGIN_URL . 'css/intlTelInput.css' );
	   		wp_enqueue_style( 'hostme-widget', HOSTME_PLUGIN_URL . 'css/style.css' );
	   	}
	}
	
	public function hide_widget_edit_icon() {
		if ( is_customize_preview() ) {
		?>
		<style>
		.widget.hostme_widget span[class^='widget-hostme_widget-'], .widget.hostme_widget span[class*='widget-hostme_widget-'] {
			display: none;
		}
		</style>
		<?php 
		}
	}
}

/**
 * Register widget function.
 *
 * @param	void
 * @return	void
 * @since	1.1
 *
 */
function hostme_register_widget() {
	register_widget( "Hostme_Widget" );
}
add_action( 'widgets_init', 'hostme_register_widget' );

/**
 * Translation hook.
 *
 * @param	void
 * @return	void
 * @since	1.0
 *
 */
function hostme_load_plugin_textdomain() {
	load_plugin_textdomain( 'hostmerr', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'hostme_load_plugin_textdomain' );