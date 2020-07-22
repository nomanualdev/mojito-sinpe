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
			'number'  => array(
				'title'   => __( 'Phone number', 'mojito-sinpe' ),
				'type'    => 'text',
				'default' => '',
			),
		);
	}

	public function payment_fields() {

		if ( ! is_checkout() ) {
			return;
		}
	
		$number = $this->settings['number'];		

		if ( empty( $number ) ){
			return;
		}

		$sinpe_banks = array(
			'none'            => __( 'Select your bank', 'mojito-sinpe' ),
			'bn'              => 'Banco Nacional de Costa Rica', // 2627
			'bcr'             => 'Banco de Costa Rica', // 2276
			'bac'             => 'Banco BAC San José', // 1222
			'lafise'          => 'Banco Lafise', // 9091
			'davivienda'      => 'Banco Davivienda', // 7070-7474
			'mutual-alajuela' => 'Grupo Mutual Alajuela - La Vivienda', // 7070-7079
			'promerica'       => 'Banco Promerica', // 6223-2450
			'coopealianza'    => 'Coopealianza', // 6222 9523
			'caja-de-ande'    => 'Caja de Ande', // 6222 -9524
			'mucap'           => 'MUCAP', // 8858-4646 ó 8861-5353
		);

		?>
		<p>
			<label for="mojito_sinpe_bank"><?php echo __( 'Select your bank', 'mojito-sinpe' ); ?></label>
			<select class="mojito_sinpe_bank_selector" id="mojito_sinpe_bank" name="mojito_sinpe_bank">
				<?php foreach ( $sinpe_banks as $option_key => $option_value ) : ?>
					<option value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_value ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php

		global $woocommerce;

		$amount  = $woocommerce->cart->total;
		$message = 'Pase ' . $amount . ' ' . $number;

		if ( $this->is_mobile() ) {
			echo '<a data-type="mobile" data-msj="' . $message . '" data-amount="' . $amount . '" data-number="' . $number . '" class="mojito-sinpe-link">' . sprintf( __( 'Pay now: %s', 'mojito-sinpe' ), $amount ) . '</a>';

		} else {
			echo '<a data-type="desktop" data-msj="' . $message . '" data-amount="' . $amount . '" data-number="' . $number . '" class="mojito-sinpe-link">' . sprintf( __('Pay now: %s', 'mojito-sinpe'), $amount ) . '</a>';
			echo '<p class="mojito-sinpe-payment-container"></p>';			
		}

	}

	/**
	 * Detect mobile client
	 *
	 * @return boolean
	 */
	public function is_mobile() {

		if ( ! class_exists( 'Mobile_Detect' ) ) {
			return;
		}

		$detect = new \Mobile_Detect();

		$is_mobile = false;

		if ( method_exists( 'Mobile_Detect', 'isMobile' ) ) {
			$is_mobile = $detect->isMobile();
		}

		if ( false === $is_mobile && method_exists( 'Mobile_Detect', 'isTablet' ) ) {
			$is_mobile = $detect->isTablet();
		}

		return $is_mobile;
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
