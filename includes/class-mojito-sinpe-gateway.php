<?php
/**
 * WooCommerce compatibility of the plugin.
 *
 * @link       https://marodok.com
 * @since      1.0.0
 * WooCommerce compatibility of the plugin.
 *
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/public
 * @author     Manfred Rodriguez <marodok@gmail.com>
 */

namespace Mojito_Sinpe;
use WC_Payment_Gateway;

class Mojito_Sinpe_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for gateway class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->id                 = 'mojito-sinpe';
		$this->icon               = plugin_dir_url( __DIR__ ) . 'public/img/sinpe-movil.png';
		$this->has_fields         = true;
		$this->method_title       = __( 'SINPE Móvil', 'mojito-sinpe' );
		$this->method_description = __( 'Payment using SINPE Móvil', 'mojito-sinpe' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option('title');

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'mojito-sinpe' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable SINPE Payment', 'mojito-sinpe' ),
				'default' => 'yes',
			),
			'title'   => array(
				'title'       => __( 'Title', 'mojito-sinpe' ),
				'type'        => 'text',
				'description' => __( 'Pay usin SINPE Móvil', 'woocommerce' ),
				'default'     => __( 'SINPE Móvil Payment', 'mojito-sinpe' ),
				'desc_tip'    => true,
			),
			'bank'   => array(
				'title'       => __('Bank', 'mojito-sinpe'),
				'type'        => 'select',
				'description' => __('Select your bank', 'woocommerce'),
				'options' => array(
					'bcr' => 'Banco de Costa Rica',
					'bn'  => 'Banco Nacional de Costa Rica',
					'bac'  => 'BAC Credomatic',
				),
			),
			'number'  => array(
				'title'   => __( 'Phone number', 'mojito-sinpe' ),
				'type'    => 'text',
				'default' => '',
			),
		);
	}

	public function payment_fields() {


		$number = $this->settings['number'];
		$bank   = $this->settings['bank'];

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
		}

		if ( empty( $number ) ){
			return;
		}

		if ( is_checkout() ) {

			global $woocommerce;

			$amount = $woocommerce->cart->total;
			$message = 'Pase ' . $amount . ' ' . $number;

			echo '<a href="sms:+' . $bank_number . '?body=' . $message . '">' . sprintf( __( 'Pay now: %s', 'mojito-sinpe' ), $amount ) . '</a>';
		}
	}


	public function process_payment( $order_id ) {

		global $woocommerce;
		$order = new \WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the SINPE).
		$order->update_status( 'on-hold', __( 'Awaiting SINPE payment', 'mojito-sinpe' ) );

		// Remove cart.
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

}
