<?php

/**
 * Pantalla de ajustes
 * copyright Enrique J. Ros - enrique@enriquejros.com
 *
 * @author 			Enrique J. Ros (enrique@enriquejros.com)
 * @link 			https://www.enriquejros.com
 * @since 			1.0.0
 * @package 		AddToCart
 *
 */

defined ('ABSPATH') or exit;

if (!class_exists ('Opciones_EJR_Add_To_Cart')) :

	Class Opciones_EJR_Add_To_Cart {

		public function __construct () {

			if (!is_admin())
				return;

			add_action ('admin_menu', array($this, 'crea_menu_opciones'));
			add_action ('admin_init', array($this, 'registra_opciones'));
			}

		public function crea_menu_opciones () {

			add_options_page (__('Add to Cart Custom Text', 'add-to-cart-custom-text'), __('Add to Cart Button', 'add-to-cart-custom-text'), 'manage_options', 'add-to-cart', array($this, 'add_to_cart_opciones'));
			}

		public function registra_opciones () {

			$opciones = array('add_to_cart_external', 'add_to_cart_grouped', 'add_to_cart_simple', 'add_to_cart_variable', 'add_to_cart_external_single', 'add_to_cart_grouped_single', 'add_to_cart_simple_single', 'add_to_cart_variable_single');

			if (post_type_exists ('bookable_resource'))
				array_push ($opciones, 'add_to_cart_bookable', 'add_to_cart_bookable_single');

			foreach ($opciones as $opcion)
				register_setting ('addtocart-opciones', $opcion);
			}

		public function add_to_cart_opciones () {

			current_user_can ('manage_options') or wp_die (__('Sorry, you are not allowed to access this page.'));

			?>

			<div class="wrap">

				<h2><?php _e('Add to Cart Custom Text options', 'add-to-cart-custom-text'); ?></h2>

				<form method="post" action="options.php">

					<?php

						settings_fields ('addtocart-opciones');
						do_settings_sections ('addtocart-opciones');

						$addtocart     = __('Add to cart', 'woocommerce');
						$buyproduct    = _x('Buy product', 'placeholder', 'woocommerce');
						$selectoptions = __('Select options', 'woocommerce');
						$viewproducts  = __('View products', 'woocommerce');
						$booknow       = __('Book now', 'woocommerce-bookings');

						$idioma  = get_locale();
						$general = substr ($idioma, 0, 2);

					?>

					<h3><?php _e('Button text in single product pages', 'add-to-cart-custom-text'); ?></h3>

					<p><?php printf(__('Custom text for the %s button in single product pages by product type', 'add-to-cart-custom-text'), '<em>'.$addtocart.'</em>'); ?></p>

					<table class="form-table">
		        
						<tr valign="top">

							<th scope="row"><?php _e('Simple product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_simple_single" value="<?php _e(esc_attr (get_option ('add_to_cart_simple_single', $addtocart))); ?>" /></td>

						</tr>

						<tr valign="top">

							<th scope="row"><?php _e('External/Affiliate product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_external_single" value="<?php _e(esc_attr (get_option ('add_to_cart_external_single', $buyproduct))); ?>" /></td>

						</tr>
		        
						<tr valign="top">

							<th scope="row"><?php _e('Variable product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_variable_single" value="<?php _e(esc_attr (get_option ('add_to_cart_variable_single', $addtocart))); ?>" /></td>

						</tr>
		        
						<tr valign="top">

							<th scope="row"><?php _e('Grouped product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_grouped_single" value="<?php _e(esc_attr (get_option ('add_to_cart_grouped_single', $addtocart))); ?>" /></td>

						</tr>

						<?php if (post_type_exists ('bookable_resource')) : ?>
		        
							<tr valign="top">

								<th scope="row"><?php _e('Bookable product', 'woocommerce-bookings'); ?></th>
								<td><input type="text" name="add_to_cart_bookable_single" value="<?php _e(esc_attr (get_option ('add_to_cart_bookable_single', $booknow))); ?>" /></td>

							</tr>

						<?php endif; ?>

					</table>

					<hr />

					<h3><?php _e('Button text in archive pages', 'add-to-cart-custom-text'); ?></h3>

					<p><?php printf(__('Custom text for the %s button in archive pages (shop, category, tags...) by product type', 'add-to-cart-custom-text'), '<em>' . $addtocart . '</em>'); ?></p>

					<table class="form-table">

						<tr valign="top">

							<th scope="row"><?php _e('Simple product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_simple" value="<?php _e(esc_attr (get_option ('add_to_cart_simple', $addtocart))); ?>" /></td>

						</tr>

						<tr valign="top">

							<th scope="row"><?php _e('External/Affiliate product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_external" value="<?php _e(esc_attr (get_option ('add_to_cart_external', $buyproduct))); ?>" /></td>

						</tr>
		        

						<tr valign="top">

							<th scope="row"><?php _e('Variable product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_variable" value="<?php _e(esc_attr (get_option ('add_to_cart_variable', $selectoptions))); ?>" /></td>

						</tr>
		        
						<tr valign="top">

							<th scope="row"><?php _e('Grouped product', 'woocommerce'); ?></th>
							<td><input type="text" name="add_to_cart_grouped" value="<?php _e(esc_attr (get_option ('add_to_cart_grouped', $viewproducts))); ?>" /></td>

						</tr>

						<?php if (post_type_exists ('bookable_resource')): ?>
		        
							<tr valign="top">

								<th scope="row"><?php _e('Bookable product', 'woocommerce-bookings'); ?></th>
								<td><input type="text" name="add_to_cart_bookable" value="<?php _e(esc_attr (get_option ('add_to_cart_bookable', 'Book now')), 'woocommerce-bookings'); ?>" /></td>

							</tr>

						<?php endif; ?>

					</table>
		    
					<?php submit_button(); ?>

				</form>

			<?php if ('es' == $general) : ?>

				<div style="padding:10px 25px;border:2px solid #ec731e;border-radius:4px;">

					<h3>Otros plugins para WooCommerce que te pueden interesar</h3>

					<ul>

						<?php if ('es_ES' == $idioma || 'ca_ES' == $idioma) { ?>

							<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-provincias-envios-woocommerce/">Plugin para seleccionar a qué provincias se realizan envíos (España)</a></li>
							<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-pedir-nif-pedidos-woocommerce/">Plugin para pedir el NIF/CIF en los pedidos</a> <?php if (class_exists ('WPO_WCPDF')) echo '(compatible con WooCommerce PDF Invoices & Packing Slips, permite insertar el NIF en la factura)'; ?></li>

						<?php } elseif ('es_AR' == $idioma) { ?>

							<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-envio-provincias-argentina-woocommerce/">Plugin para seleccionar a qué provincias se realizan envíos (Argentina)</a></li>

						<?php } elseif ('es_MX' == $idioma) { ?>

							<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-envio-provincias-mexico-woocommerce/">Plugin para seleccionar a qué provincias se realizan envíos (México)</a></li>

						<?php } elseif ('es_CO' == $idioma) { ?>

							<li><a target="_blank" href="https://www.enriquejros.com/plugins/anadir-departamentos-colombia-woocommerce/">Añadir departamentos de Colombia a WooCommerce</a></li>

						<?php } elseif ('es_UY' == $idioma) { ?>

							<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-anadir-los-departamentos-uruguay-woocommerce/">Añadir departamentos de Uruguay a WooCommerce</a></li>

						<?php } ?>

						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-contenido-email-pedido-woocommerce/">Plugin para añadir contenido personalizado en el email del pedido</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-personalizar-checkout-woocommerce/">Plugin para personalizar campos del checkout</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-zonas-envio-personalizadas-woocommerce/">Plugin para personalizar los estados/provincias/departamentos</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-insertar-pixel-facebook-woocommerce/">Plugin para insertar el píxel de Facebook en WooCommerce</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-pestanas-mi-cuenta-woocommerce/">Plugin para añadir pestañas personalizadas en la página <i>Mi cuenta</i></a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-directo-checkout-sin-carrito/">Plugin para ir directo al checkout sin pasar por el carrito</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/quitar-intervalo-precios-productos-variables/">Plugin para quitar el intervalo de precios en productos variables</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-envio-gratuito-woocommerce/">Plugin para ocultar las formas de envío si está disponible el envío gratuito</a></li>
						<li><a target="_blank" href="https://www.enriquejros.com/plugins/plugin-pedido-minimo-woocommerce/">Plugin para establecer un pedido mínimo</a></li>
						
					</ul>

				</div>

				<?php endif; ?>

			</div>

			<?php
			}

		}

endif;