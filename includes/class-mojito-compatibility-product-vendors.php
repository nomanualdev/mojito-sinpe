<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://marodok.com
 * @since      1.0.0
 *
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/includes
 */

namespace Mojito_Sinpe;

/**
 * Compatibility class for Product_Vendors plugin.
 *
 * Based on: https://gist.github.com/SiR-DanieL/4d2f204b87d097eb99d2572b8d8bcc44#file-functions-php
 *
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/includes
 * @author     Manfred Rodriguez <marodok@gmail.com>
 */
class Mojito_Sinpe_Compatibility_Product_Vendors_Support {

	/**
	 * Contructor
	 */
	public function __construct() {
		if ( ! $this->installed() ) {
			return;
		}
	}


	/**
	 * Check if WooCommerce Product Vendors is installed
	 */
	public function installed() {

		/**
		 * Check if WooCommerce Product Vendors is installed
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce-product-vendors' ) ) {
				return true;
			}
		} else {
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Add filters
	 */
	public function run() {

		if ( ! $this->installed() ) {
			return;
		}

		/**
		 * Register term fields.
		 */
		add_action(
			'init',
			function () {
				add_action( WC_PRODUCT_VENDORS_TAXONOMY . '_add_form_fields', array( $this, 'add_vendor_custom_fields' ) );
				add_action( WC_PRODUCT_VENDORS_TAXONOMY . '_edit_form_fields', array( $this, 'edit_vendor_custom_fields' ), 10 );
				add_action( 'edited_' . WC_PRODUCT_VENDORS_TAXONOMY, array( $this, 'save_vendor_custom_fields' ) );
				add_action( 'created_' . WC_PRODUCT_VENDORS_TAXONOMY, array( $this, 'save_vendor_custom_fields' ) );
				add_action( 'wcpv_registration_form', array( $this, 'vendors_reg_custom_fields' ) );
				add_action( 'wcpv_shortcode_registration_form_process', array( $this, 'vendors_reg_custom_fields_save' ), 10, 2 );
			}
		);
	}


	/**
	 * Add term fields form
	 */
	public function add_vendor_custom_fields() {
		wp_nonce_field( basename( __FILE__ ), 'vendor_custom_sinpe_nonce' );
		?>
		<div class="form-field">
			<label for="sinpe-number"><?php esc_html_e( 'Sinpe Number', 'mojito-sinpe' ); ?></label>
			<input type="text" name="sinpe-number" id="sinpe-number" value="" />
		</div>
		<?php
	}


	/**
	 * Edit term fields form.
	 *
	 * @param object $term Term.
	 */
	public function edit_vendor_custom_fields( $term ) {
		wp_nonce_field( basename( __FILE__ ), 'vendor_custom_sinpe_nonce' );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="sinpe-number"><?php _e( 'Sinpe Number', 'mojito-sinpe' ); ?></label></th>
			<td>
				<input type="text" name="sinpe-number" id="sinpe-number" value="<?php echo sanitize_text_field( get_term_meta( $term->term_id, 'sinpe-number', true ) ); ?>" />
			</td>
		</tr>
		<?php
	}


	/**
	 * Save term fields.
	 *
	 * @param string $term_id Term.
	 */
	public function save_vendor_custom_fields( $term_id ) {

		if ( ! wp_verify_nonce( $_POST['vendor_custom_sinpe_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		$old_sinpe = get_term_meta( $term_id, 'sinpe-number', true );
		$new_sinpe = sanitize_text_field( $_POST['sinpe-number'] );

		if ( ! empty( $old_sinpe ) && '' === $new_sinpe ) {
			delete_term_meta( $term_id, 'sinpe-number' );
		} elseif ( $old_sinpe !== $new_sinpe ) {
			update_term_meta( $term_id, 'sinpe-number', $new_sinpe, $old_sinpe );
		}
	}


	/**
	 * Custom fields form.
	 *
	 * @return void
	 */
	public function vendors_reg_custom_fields() {
		?>
		<p class="form-row form-row-first">
			<label for="wcpv-sinpe-number"><?php esc_html_e( 'Sinpe Number', 'mojito-sinpe'); ?></label>
			<input type="text" class="input-text" name="sinpe-number" id="wcpv-sinpe-number" value="
			<?php 
			if ( ! empty( $_POST['sinpe-number'] ) ) {
				echo esc_attr( trim( sanitize_text_field( $_POST['sinpe-number'] ) ) );
			} 
			?>
			" />
		</p>
		<?php
	}


	/**
	 * Save custom fields
	 *
	 * @param array $args Args.
	 * @param array $items Items.
	 * @return void
	 */
	public function vendors_reg_custom_fields_save( $args, $items ) {

		$term = get_term_by( 'name', $items['vendor_name'], WC_PRODUCT_VENDORS_TAXONOMY );

		if ( isset( $items['sinpe-number'] ) && ! empty( $items['sinpe-number'] ) ) {
			$sinpe_number = sanitize_text_field( $items['sinpe-number'] );
			update_term_meta( $term->term_id, 'sinpe-number', $sinpe_number );
		}
	}
}
