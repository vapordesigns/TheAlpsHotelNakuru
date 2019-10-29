/*
 * Check that the email is in valid format.
 *
 * @param	string	email
 * @return	boolean
 * @since	1.0
 *
 */
function hostme_is_email( email ) {
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	
	return regex.test( email );
}

jQuery( document ).ready( function ( $ ) {
	"use strict";

	var current_widget_id = 'host-me-id';

	var ajax_executed = false;

	/**
	 * Get current widget ID.
	 *
	 */
	var hostme_get_current_widget_id = function ( element ) {
		current_widget_id = element.parents( '.host-me-id' ).attr( 'id' );
	}

	$( '.host-me-id' ).on( 'focus', 'input', function () {
		hostme_get_current_widget_id( $( this ) );
	} );

	var hostme_email_field_set = false;
	var hostme_name_field_set = false;
	var hostme_address_field_set = false;
	var hostme_phone_field_set = false;

	/**
	 * Check that all fields are filled out and in correct format where required and then enable the register button.
	 *
	 */
	var hostme_validate_fields = function() {
		if ( hostme_is_email( $( '#' + current_widget_id + ' .hostme-register-email' ).val() ) ) {
			hostme_email_field_set = true;
		} else {
			hostme_email_field_set = false;
		}

		if ( $( '#' + current_widget_id + ' .hostme-register-name' ).val() ) {
			hostme_name_field_set = true;
		} else {
			hostme_name_field_set = false;
		}

		if ( $( '#' + current_widget_id + ' .hostme-register-address' ).val() ) {
			hostme_address_field_set = true;
		} else {
			hostme_address_field_set = false;
		}

		if ( $( '#' + current_widget_id + ' .hostme-register-phone' ).intlTelInput( 'isValidNumber' ) ) {
			hostme_phone_field_set = true;
			
			$( '#' + current_widget_id + ' .hostme-register-international-phone' ).val( $( '#' + current_widget_id + ' .hostme-register-phone' ).intlTelInput( 'getNumber' ) );
		} else {
			hostme_phone_field_set = false;
		}

		if ( hostme_email_field_set && hostme_name_field_set && hostme_address_field_set && hostme_phone_field_set ) {
			$( '#' + current_widget_id + ' #hostme-register-restaurant' ).prop( 'disabled', false );
		} else {
			$( '#' + current_widget_id + ' #hostme-register-restaurant' ).prop( 'disabled', true );
		}
	}

	/**
	 * Register new restaurant. Makes a call to Hostme API, registers the new restaurant and retrives restaurant data.
	 *
	 */
	var hostme_register_restaurant = function ( widget ) {
		/*if ( ajax_executed ) {
			return false;
		}*/
		
		if ( hostme_email_field_set && hostme_name_field_set && hostme_address_field_set && hostme_phone_field_set ) {
			var data = {
				'action': 'hostme_register_restaurant', 
				'nonce': widget.find( '#hostme-register-restaurant' ).data( 'nonce' ),
				'email': widget.find( '.hostme-register-email' ).val(),
				'name': widget.find( '.hostme-register-name' ).val(),
				'address': widget.find( '.hostme-register-address' ).val(),
				'phone': widget.find( '.hostme-register-phone' ).val(),
				'international_phone': widget.find( '.hostme-register-international-phone' ).val(),
				'widget_id': current_widget_id
			};

			$.ajax( {
	        	type: 'post',
	        	url: ajaxurl,
	        	data: data,
	        	dataType: 'json',
	        	beforeSend: function () {
	        		ajax_executed = true; 
	        	},
	        	success: function ( response ) {
	        		if ( typeof response.status !== "undefined" && response.status == 'success' ) {
		        		widget.find( '#hostme-register-restaurant span' ).css( 'display', 'none' );
						widget.find( '#hostme-register-restaurant' ).prop( 'disabled', false );
						
						//	Hide the connect-register panel and show the current-restaurant panel.
						widget.find( '.hostme-panel-connect-register' ).hide();
						
						var current_restaurant = widget.find( '.hostme-panel-current-restaurant' );
						
						current_restaurant.find( '.hostme-current-restaurant-text' ).html( response.data.name );
						current_restaurant.find( '#hostme_restaurant_name' ).val( response.data.name );
						current_restaurant.find( '#hostme_restaurant_id' ).val( response.data.id );

						current_restaurant.show();

						//	Clear fields.
						widget.find( '.hostme-register-email' ).val( '' );						
						widget.find( '.hostme-register-name' ).val( '' );						
						widget.find( '.hostme-register-address' ).val( '' );						
						widget.find( '.hostme-register-phone' ).val( '' );						
						widget.find( '.hostme-register-international-phone' ).val( '' );						
	        		}
	        		
	        		ajax_executed = false;
	        	}
	        } );			
		}
	}

	/**
	 * Validate register fields.
	 *
	 */
	$( '.host-me-id' ).on( 'keyup change', '.hostme-panel-connect-register input', function () {
		hostme_validate_fields();
	} );

	/**
	 * Change the phone field to international format.
	 *
	 */
	if ( typeof $.fn.intlTelInput !== 'undefined' ) {
		$( '.hostme-register-phone' ).intlTelInput( {
			utilsScript: hostme.plugin_url + '/js/utils.js'
		} );
	}

	/**
	 * Retrieves the list of restaurants connected to the email.
	 *
	 */
	var hostme_select_restaurant = function ( select_restaurant_btn ) {
		var widget = select_restaurant_btn.parents( '.host-me-id' );

		widget.find( '.hostme-panel-connect-register' ).hide();

		var select_restaurant = widget.find( '.hostme-panel-select-restaurant' );

		select_restaurant.find( '.hostme-search-email' ).val( '' );
		select_restaurant.children( '.hostme-restaurant-list-container' ).html( '' );

		select_restaurant.show();
	}

	/**
	 * On manage restaurant button, switch to panel with email field.
	 *
	 */
	$( '.host-me-id' ).on( 'click', '#hostme-connect-restaurant', function () { 
		hostme_select_restaurant( $( this ) );
	} );

	/**
	 * Register restaurant.
	 *
	 */
	$( '.host-me-id' ).on( 'click', '#hostme-register-restaurant', function ( e ) {
		e.preventDefault();

		hostme_get_current_widget_id( $( this ) );

		hostme_validate_fields();

		$( 'span', this ).css( 'display', 'inline-block' );
		$( this ).prop( 'disabled', true );

		hostme_register_restaurant( $( this ).parents( '.host-me-id' ) );
	} );

	/**
	 * Function that switches panels to the first one.
	 *
	 */
	var hostme_connect_register_panel = function ( element ) {
		hostme_get_current_widget_id( element );

		$( '#' + current_widget_id ).find( '.hostme-panel-current-restaurant' ).hide();
		$( '#' + current_widget_id ).find( '.hostme-panel-select-restaurant' ).hide();
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).show();

		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-email' ).val( '' );
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-name' ).val( '' );
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-address' ).val( '' );
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-phone' ).val( '' );
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-international-phone' ).val( '' );		
		$( '#' + current_widget_id ).find( '.hostme-panel-connect-register' ).find( '.hostme-register-phone' ).intlTelInput( 'setCountry', 'us' );
	}

	/**
	 * Go back to first panel.
	 *
	 */
	$( '.host-me-id' ).on( 'click', '#hostme-change-restaurant, .hostme-go-back', function () {
		hostme_connect_register_panel( $( this ) );
	} );

	/**
	 * Search for restaurants by email.
	 *
	 */
	var hostme_search_restaurant = function ( email, nonce ) {
		/*if ( ajax_executed ) {
			return false;
		}*/
		
		var data = {
			'action': 'hostme_search_restaurant', 
			'nonce': nonce,
			'email': email
		};

		$.ajax( {
        	type: 'post',
        	url: ajaxurl,
        	data: data,
        	dataType: 'json',
        	beforeSend: function () {
        		ajax_executed = true;

				$( '#' + current_widget_id ).find( '.hostme-search-restaurant-field .spinner' ).css( 'visibility', 'visible' );
        	},
        	success: function ( response ) {
        		if ( typeof response.status !== "undefined" && response.status == 'success' ) {
					//	Build a list of restaurants.
	        		var restaurant_list = $( '#' + current_widget_id ).find( '.hostme-restaurant-list-container' );

					$( '#' + current_widget_id ).find( '.hostme-no-restaurant-found' ).hide();
	
					restaurant_list.html( '' ).show();

					var restaurant_list_html = '<ul>';

					$.each( response.data, function( i, e ) {
						restaurant_list_html += '<li data-id="' + e.id + '" data-name="' + e.name + '" data-phone="' + e.phone + '" data-address="' + e.address + '" data-nonce="' + e.nonce + '">' + e.name + '</li>';							
					} );

					restaurant_list_html += '</ul>';

					restaurant_list.html( restaurant_list_html );
        		} else if ( typeof response.status !== "undefined" && response.status == 'error_no_restaurants' ) {
					//	Show a note that no restaurants were found.
					var restaurant_list = $( '#' + current_widget_id ).find( '.hostme-restaurant-list-container' );

					restaurant_list.html( '' ).hide();

					$( '#' + current_widget_id ).find( '.hostme-no-restaurant-found' ).show();
				}
        		
        		ajax_executed = false;

				$( '#' + current_widget_id ).find( '.hostme-search-restaurant-field .spinner' ).css( 'visibility', 'hidden' );
        	}
        } );			
	}

	/**
	 * Check the search for restaurant email field while typing.
	 * Once we have a valid email, sent it to the API.
	 *
	 */
	$( '.host-me-id' ).on( 'keyup', '.hostme-search-email', function () {
		if ( hostme_is_email( $( this ).val() ) ) {
			hostme_search_restaurant( $( this ).val(), $( this ).data( 'nonce' ) );
		} else {
			$( this ).parents( '.hostme-panel-select-restaurant' ).children( '.hostme-restaurant-list-container' ).html( '' );
		}
	} );

	/**
	 * Search for restaurants by email.
	 *
	 */
	var hostme_set_active_restaurant = function ( widget, data ) {
		//	Hide the select panel and show the current-restaurant panel.
		widget.find( '.hostme-panel-select-restaurant' ).hide();
		
		var current_restaurant = widget.find( '.hostme-panel-current-restaurant' );
		
		current_restaurant.find( '.hostme-current-restaurant-text' ).html( data.name );
		current_restaurant.find( '#hostme_restaurant_name' ).val( data.name );
		current_restaurant.find( '#hostme_restaurant_id' ).val( data.id );

		current_restaurant.show();
	}

	/**
	 * If a restaurant in the list is clicked, set it as current one.
	 *
	 */
	$( '.host-me-id' ).on( 'click', '.hostme-restaurant-list-container li', function () {
		var data = {
			'id': $( this ).data( 'id' ),
			'name': $( this ).data( 'name' ),
			'nonce': $( this ).data( 'nonce' )
		};

		hostme_set_active_restaurant( $( this ).parents( '.host-me-id' ), data );
	} );

	/**
	 * Hook into added/updated widget code.
	 *
	 */
	$( document ).on( 'widget-added widget-updated', function ( e, widget ) {
		/**
		 * Get current widget ID.
		 *
		 */
		$( '.host-me-id', widget ).on( 'focus', 'input', function () {
			hostme_get_current_widget_id( $( this ) );
		} );
	
		/**
		 * Change the phone field to international format.
		 *
		 */
		if ( typeof $.fn.intlTelInput !== 'undefined' ) {
			$( '.hostme-register-phone', widget ).intlTelInput( {
				utilsScript: hostme.plugin_url + '/js/utils.js',
				autoPlaceholder: "aggressive"
			} );
		}

		/**
		 * Validate register fields.
		 *
		 */
		$( '.host-me-id', widget ).on( 'keyup change', '.hostme-panel-connect-register input', function () {
			hostme_validate_fields();
		} );

		/**
		 * On manage restaurant button, switch to panel with email field.
		 *
		 */
		$( widget ).on( 'click', '#hostme-connect-restaurant', function () { 
			hostme_select_restaurant( $( this ) );
		} );

		/**
		 * Register restaurant.
		 *
		 */
		$( widget ).on( 'click', '#hostme-register-restaurant', function ( e ) {
			e.preventDefault();

			hostme_get_current_widget_id( $( this ) );

			hostme_validate_fields();

			$( 'span', this ).css( 'display', 'inline-block' );
			$( this ).prop( 'disabled', true );

			hostme_register_restaurant( widget );
		} );

		/**
		 * Check the search for restaurant email field while typing.
		 * Once we have a valid email, sent it to the API.
		 *
		 */
		$( widget ).on( 'keyup', '.hostme-search-email', function () {
			if ( hostme_is_email( $( this ).val() ) ) {
				hostme_search_restaurant( $( this ).val(), $( this ).data( 'nonce' ) );
			} else {
				$( this ).parents( '.hostme-panel-select-restaurant' ).children( '.hostme-restaurant-list-container' ).html( '' );
			}
		} );

		/**
		 * Go back to first panel.
		 *
		 */
		$( widget ).on( 'click', '#hostme-change-restaurant, .hostme-go-back', function () {
			hostme_connect_register_panel( $( this ) );
		} );

		/**
		 * If a restaurant in the list is clicked, set it as current one.
		 *
		 */
		$( widget ).on( 'click', '.hostme-restaurant-list-container li', function () {
			var data = {
				'id': $( this ).data( 'id' ),
				'name': $( this ).data( 'name' ),
				'nonce': $( this ).data( 'nonce' )
			};
	
			hostme_set_active_restaurant( $( this ).parents( '.host-me-id' ), data );
		} );
	} );
} );