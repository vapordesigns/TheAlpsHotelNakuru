<?php
/**
 * Contains all the AJAX calls.
 *
 * @since	1.0
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Register new restaurant.
 *
 * @param	void
 * @return	string
 * @since	1.0
 *
 */
function hostme_register_restaurant() {
	$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		
	if ( ! wp_verify_nonce( $_POST['nonce'], 'hostme-register-restaurant' ) ) {
		echo json_encode( array(
			'status' => 'error',
			'message' => __( 'Wrong nonce!', 'hostmerr' )
		) );
		
		die();
	}

	$form = array();

	//	Get the form fields.
	$form['email'] = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$form['name'] = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$form['address'] = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
	$form['phone'] = isset( $_POST['international_phone'] ) ? sanitize_text_field( $_POST['international_phone'] ) : '';
	$widget_id = isset( $_POST['widget_id'] ) ? sanitize_text_field( $_POST['widget_id'] ) : '';

	//	Since all the fields are required, let's double check if they are not empty.
	if ( ! $form['email'] || ! $form['name'] || ! $form['address'] || ! $form['phone'] || ! $widget_id ) {
		echo json_encode( array(
			'status' => 'error',
			'message' => __( 'All fields are required.', 'hostmerr' )
		) );
		
		die();
	}

	//	All looks good so far so let's make the API call.
	$response = wp_remote_post( HOSTME_API_URL . '/api/core/admin/restaurants', array(
		'method' => 'POST',
		'timeout' => 10,
		'body' => $form,
		'sslverify' => true
	    )
	);
	
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		
		echo json_encode( array(
			'status' => 'error',
			'message' => $error_message
		) );
		
		die();
	} else {
		//	In case API returns nothing.
		if ( empty( $response['body'] ) ) {
			echo json_encode( array(
				'status' => 'error',
				'message' => __( 'API data not found.', 'hostmerr' )
			) );
			
			die();
		}

		$restaurant_info = json_decode( $response['body'] );

	   	echo json_encode( array(
			'status' => 'success',
			'message' => __( 'Restaurant registered.', 'hostmerr' ),
			'widget_id' => $widget_id,
			'data' => array(
				'id' => $restaurant_info->data->id,
				'name' => $restaurant_info->data->name
			)
		) );
		
		die();
	}
}
add_action( 'wp_ajax_hostme_register_restaurant', 'hostme_register_restaurant' );

/**
 * Search for a list of restaurants via the provided email.
 *
 * @param	void
 * @return	string
 * @since	1.0
 *
 */
function hostme_search_restaurant() {
	$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		
	if ( ! wp_verify_nonce( $_POST['nonce'], 'hostme-search-restaurant' ) ) {
		echo json_encode( array(
			'status' => 'error',
			'message' => __( 'Wrong nonce!', 'hostmerr' )
		) );
		
		die();
	}

	//	Get the email field.
	$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

	//	Since all the fields are required, let's double check if they are not empty.
	if ( ! $email ) {
		echo json_encode( array(
			'status' => 'error',
			'message' => __( 'Please enter your email.', 'hostmerr' )
		) );
		
		die();
	}

	//	Let's check again that the email is valid.
	if ( ! is_email( $email ) ) {
		echo json_encode( array(
			'status' => 'error',
			'message' => __( 'Email is not valid.', 'hostmerr' )
		) );
		
		die();
	}

	//	All looks good so far so let's make the API call.
	$response = wp_remote_get( HOSTME_API_URL . '/api/core/admin/restaurants/query?email=' . urlencode( $email ) );

	if ( is_array( $response ) ) {
		//	In case API returns no body.
		if ( empty( $response['body'] ) ) {
			echo json_encode( array(
				'status' => 'error',
				'message' => __( 'No restaurants data found.', 'hostmerr' )
			) );
			
			die();
		}

		$restaurants = json_decode( $response['body'] );

		$restaurants_info = array();

		if ( ! empty( $restaurants ) ) {
			foreach ( $restaurants as $restaurant ) {
				$restaurants_info[] = array(
					'id' => absint( $restaurant->id ),
					'name' => esc_attr( $restaurant->name ),
					'address' => esc_attr( $restaurant->address ),
					'phone' => esc_attr( $restaurant->phone ),
					'nonce' => esc_attr( wp_create_nonce( 'hostme-restaurant-id-' . absint( $restaurant->id ) ) ),
				);
			}
	
			echo json_encode( array(
				'status' => 'success',
				'message' => __( 'Restaurants found.', 'hostmerr' ),
				'data' => $restaurants_info
			) );
			
			die();
		} else {
			echo json_encode( array(
				'status' => 'error_no_restaurants',
				'message' => __( 'No restaurants found.', 'hostmerr' )
			) );
			
			die();
		}
	} else {
		//	In case API returns nothing.
		if ( empty( $response ) ) {
			echo json_encode( array(
				'status' => 'error',
				'message' => __( 'There was an error with the API.', 'hostmerr' )
			) );
			
			die();
		}
	}
}
add_action( 'wp_ajax_hostme_search_restaurant', 'hostme_search_restaurant' );