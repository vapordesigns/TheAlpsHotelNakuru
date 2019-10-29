<?php

/**
 *
 * Plugin Name: 			Add to Cart Button Custom Text
 * Description: 			Allows to customize the "Add to Cart" button text in WooCommerce by product type in both archive and product pages
 * Plugin URI: 				https://www.enriquejros.com/plugins/
 * Author: 					Enrique J. Ros
 * Author URI: 				https://www.enriquejros.com/
 * Version: 				2.2.0
 * License: 				GNU General Public License v2 or later
 * License URI: 			http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 			add-to-cart-custom-text
 * Domain Path: 			/lang/
 * Requires at least:		4.7
 * Tested up to:			5.2
 * WC requires at least:	3.0
 * WC tested up to: 		3.6
 *
 * @author 					Enrique J. Ros
 * @link              		https://www.enriquejros.com
 * @since             		1.0.0
 * @package           		AddToCart
 *
 */

/*
    Copyright 2016 - 2019 Enrique J. Ros (email: enrique@enriquejros.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined ('ABSPATH') or exit;

if (!class_exists ('Plugin_EJR_Add_To_Cart')) :

	Class Plugin_EJR_Add_To_Cart {

		private static $instancia;

		private function __construct () {

			$this->nombre   = __('Add to Cart Button Custom Text', 'add-to-cart-custom-text');
			$this->domain   = 'add-to-cart-custom-text';
			$this->gestor   = 'options-general.php?page=add-to-cart';
			$this->campos   = false;
			$this->archivos = array('class', 'options');
			$this->clases   = array('EJR_Add_To_Cart', 'Opciones_EJR_Add_To_Cart');
			$this->dirname  = dirname (__FILE__);

			$this->carga_archivos($this->archivos, $this->campos);
			$this->carga_traducciones($this->domain);

			register_activation_hook (__FILE__, function () {
				set_transient ($this->domain . '-activado', true, 5);
				}, 10 );

			add_action ('init', array($this, 'arranca_plugin'), 10);
			add_action ('admin_notices' , array( $this, 'aviso_ayuda'), 10);
			add_filter ('plugin_action_links', array($this, 'enlaces_accion'), 10, 2);
			}

		public function __clone () {

			_doing_it_wrong (__FUNCTION__, sprintf (__('You cannot clone instances of %s.', 'add-to-cart-custom-text'), get_class ($this)), '2.1.2');
			}

		public function carga_archivos ($archivos, $campos) {

			foreach ($archivos as $archivo)
				require ($this->dirname . '/' . $archivo . '.php');
			}

		public function arranca_plugin () {

			if ($this->woocommerce_activo())
				foreach ($this->clases as $clase)
					new $clase;
			}

		private function woocommerce_activo () {

			if (!class_exists ('WooCommerce')) {

				add_action ('admin_notices', function () {
					?>
						<div class="notice notice-error is-dismissible">
							<p><?php printf (__('The plugin %s needs WooCommerce to be active in order to work. Please, activate WooCommerce first.', 'add-to-cart-custom-text'), '<i>' . __($this->nombre, 'add-to-cart-custom-text') . '</i>'); ?></p>
						</div>
					<?php
					}, 10);

				return false;
				}

			return true;
			}

		public function aviso_ayuda () {

			if (get_transient ($this->domain . '-activado')) {

				?>
					<div class="updated notice is-dismissible woocommerce-message">
						<p><?php printf ( __('Thanks for using %s. Go to the settings page to configure the plugin.', 'add-to-cart-custom-text'), '<i>' . $this->nombre . '</i>' ); ?></p>
						<p><?php printf ('<a href="%s" class="button button-primary">%s</a>', $this->gestor, __('Settings')); ?></p>
					</div>
				<?php
				}
			}

		public function carga_traducciones ($domain) {

			$locale = is_admin() && function_exists ('get_user_locale') ? get_user_locale() : get_locale();
			$locale = apply_filters ('plugin_locale', $locale, $domain);
			load_textdomain ($domain, $this->dirname . '/lang/' . $domain . '-' . $locale . '.mo');
			load_plugin_textdomain ($domain, false, $this->dirname . '/lang');
			}

		public function enlaces_accion ($damelinks, $plugin) {

			static $addtocart;
			isset ($addtocart) or $addtocart = plugin_basename (__FILE__);

			if ($addtocart == $plugin) {

				$enlaces['settings'] = '<a href="' . $this->gestor . '">' . __('Settings') . '</a>';
				$damelinks = array_merge ($enlaces, $damelinks);
				}
			
			return $damelinks;
			}

		public static function instancia () {

			if (null === self::$instancia)
				self::$instancia = new self();

			return self::$instancia;
			}

		}

endif;

Plugin_EJR_Add_To_Cart::instancia();