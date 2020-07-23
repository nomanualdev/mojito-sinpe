<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://marodok.com
 * @since      1.0.0
 *
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/includes
 * @author     Manfred Rodriguez <marodok@gmail.com>
 */

namespace Mojito_Sinpe;

class Mojito_Sinpe
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Mojito_Sinpe_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'MOJITO_SINPE_VERSION' ) ) {
			$this->version = MOJITO_SINPE_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'mojito-sinpe';

		/**
		 * Define plugin name as constant.
		 */
		define( 'MOJITO_SINPE_SLUG', $this->plugin_name );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_filter(
			'woocommerce_payment_gateways',
			function ($methods) {
				$methods[] = 'Mojito_Sinpe\Mojito_Sinpe_Gateway';
				return $methods;
			}
		);

		/**
		 * Load gateway
		 */
		add_action(
			'plugins_loaded',
			function () {
				/**
				 * The class responsible for defining all actions that occur in the public-facing
				 * side of the site.
				 */
				require_once MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe-gateway.php';
			}
		);

		/**
		 * Save client bank selection as meta to use it later in the order email
		 */
		add_action(
			'woocommerce_checkout_update_order_meta',
			function ($order_id) {
				if (!empty($_POST['mojito_sinpe_bank'])) {
					update_post_meta($order_id, 'mojito_sinpe_bank', sanitize_text_field($_POST['mojito_sinpe_bank']));
				}
			}
		);

		/**
		 * Add SINPE link to order email
		 */
		add_action(
			'woocommerce_email_before_order_table',
			function ( $order, $sent_to_admin, $plain_text, $email ) {
				
				/**
				 * Check if is the correct email
				 */
				if ( 'customer_on_hold_order' !== $email->id ) {
					return;
				}

				/**
				 * Check if is sent to admin
				 */
				if ( $sent_to_admin ) {
					return;
				}

				/**
				 * Check if is the correct payment method
				 */
				if ( 'mojito-sinpe' !== $order->get_payment_method() ) {
					return;
				}

				/**
				 * Check if order is pais
				 */
				if ( $order->is_paid() ) {
					return;
				}

				/**
				 * Get Bank selected by client
				 */
				$bank = get_post_meta( $order->get_id(), 'mojito_sinpe_bank', true );

				/**
				 * Set the bank number
				 */
				$bank_number = '';

				switch ( $bank ) {

					case 'bn':
						$bank_number = '2627';
						break;

					case 'bcr':
						$bank_number = '2276';
						break;

					case 'bac':
						$bank_number = '1222';
						break;

					case 'lafise':
						$bank_number = '9091';
						break;

					case 'davivienda':
						$bank_number = '70707474';
						break;

					case 'mutual-alajuela':
						$bank_number = '70707079';
						break;

					case 'promerica':
						$bank_number = '62232450';
						break;

					case 'coopealianza':
						$bank_number = '62229523';
						break;

					case 'caja-de-ande':
						$bank_number = '62229524';
						break;

					case 'mucap':
						$bank_number = '62229525';
						break;
				}

				/**
				 * Check if there is bank number
				 */
				if ( empty( $bank_number ) ){
					return;
				}

				/**
				 * Build SMS link
				 */				
				$wc_gateways = new \WC_Payment_Gateways();
				$payment_gateways = $wc_gateways->get_available_payment_gateways();
				$mojito_sinpe_settings = $payment_gateways['mojito-sinpe'];
				$number = $mojito_sinpe_settings->settings['number'];
				

				$message = 'Pase ' . $order->get_total() . ' ' . $number;

				$link = '<a href="';
				$link .= 'sms:+' . $bank_number . '?body=' . $message;
				$link .= '">';
				$link .= apply_filters( 'mojito_sinpe_email_label', __('Pague aquí SINPE Móvil', 'mojito-sinpe' ) );
				$link .= '</a>';
				$link .= '<br><br>';

				echo $link;

			},
			10,
			4
		);
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Mojito_Sinpe_Loader. Orchestrates the hooks of the plugin.
	 * - Mojito_Sinpe_i18n. Defines internationalization functionality.
	 * - Mojito_Sinpe_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		if( !class_exists( 'Mojito_Sinpe_Loader' ) ){
			require_once MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe-loader.php';
		}		

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		if ( !class_exists( 'Mojito_Sinpe_i18n' ) ) {
			require_once MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe-i18n.php';
		}
		
		/**
		 * The class responsible for mobile detection
		 */
		if ( !class_exists( 'Mobile_Detect' ) ) {
			require_once MOJITO_SINPE_DIR . 'includes/class-mobile-detect.php';
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once MOJITO_SINPE_DIR . 'public/class-mojito-sinpe-public.php';


		$this->loader = new Mojito_Sinpe_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Mojito_Sinpe_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Mojito_Sinpe_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Mojito_Sinpe_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{

		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Mojito_Sinpe_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

}
