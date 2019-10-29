<?php

/**
 * Clase principal
 * copyright Enrique J. Ros - enrique@enriquejros.com
 *
 * @author 			Enrique J. Ros (enrique@enriquejros.com)
 * @link 			https://www.enriquejros.com
 * @since 			2.1.0
 * @package 		AddToCart
 *
 */

defined ('ABSPATH') or exit;

if (!class_exists ('EJR_Add_To_Cart')) :

	Class EJR_Add_To_Cart {

		public function __construct () {

			add_filter ('add_to_cart_text', 'cambia_texto_boton'); //WooCommerce <2.1
			add_filter ('woocommerce_product_add_to_cart_text', array($this, 'cambia_texto_boton'));
			add_filter ('woocommerce_product_single_add_to_cart_text', array($this, 'cambia_texto_boton'));
			add_filter ('woocommerce_booking_single_add_to_cart_text', array($this, 'cambia_texto_boton')); //WC Bookings
			}

		public function cambia_texto_boton () {

			global $product;
			$product_type = $product->get_type();

			//Cogemos el valor de la variable de la bd y si no por defecto
			$texto_single = __('Add to cart', 'woocommerce');
			$texto_externo = esc_attr (get_option ('add_to_cart_external', __('Buy product', 'woocommerce')));
			$texto_agrupado = esc_attr (get_option ('add_to_cart_grouped', __('View products', 'woocommerce')));
			$texto_simple = esc_attr (get_option ('add_to_cart_simple', __('Add to cart', 'woocommerce')));
			$texto_variable = esc_attr (get_option ('add_to_cart_variable', __('Select options', 'woocommerce')));
			$texto_bookable = esc_attr (get_option ('add_to_cart_bookable', __('Book now', 'woocommerce')));
			$texto_externo_single = esc_attr (get_option ('add_to_cart_external_single', $texto_externo));
			$texto_agrupado_single = esc_attr (get_option ('add_to_cart_grouped_single', $texto_single));
			$texto_simple_single = esc_attr (get_option ('add_to_cart_simple_single', $texto_single));
			$texto_variable_single = esc_attr (get_option ('add_to_cart_variable_single', $texto_single));
			$texto_bookable_single = esc_attr (get_option ('add_to_cart_bookable_single', $texto_bookable));

			if (is_product()) { //Para la página de producto

				switch ($product_type) {

					case 'external':
						return $texto_externo_single;
						break;

					case 'grouped':
						return $texto_agrupado_single;
						break;

					case 'simple':
						return $texto_simple_single;
						break;

					case 'variable':
						return $texto_variable_single;
						break;

					case 'booking':
						return $texto_bookable_single;
						break;

					default:
						return $texto_single;

					}
				}

			else { //Para las páginas de archivo

				switch ($product_type) {

					case 'external':
						return $texto_externo;
						break;

					case 'grouped':
						return $texto_agrupado;
						break;

					case 'simple':
						return $texto_simple;
						break;

					case 'variable':
						return $texto_variable;
						break;

					case 'booking':
						return $texto_bookable;
						break;

					default:
						return $texto_single;

					}
				}

			}

		}

endif;