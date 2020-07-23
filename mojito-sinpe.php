<?php

/**
 * Mojito Sinpe
 *
 * @package           Mojito_Sinpe
 * @author            Marodok
 * @link              https://marodok.com
 *
 * @wordpress-plugin
 * Plugin Name: Mojito Sinpe
 * Plugin URI: https://mojitowp.com/
 * Description: Sinpe Móvil as Woocommerce gateway
 * Version: 0.0.2
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * Author: Manfred Rodríguez
 * Author URI: https://marodok.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mojito-sinpe
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 4.3
 */

namespace Mojito_Sinpe;

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Version.
 */
define( 'MOJITO_SINPE_VERSION', '0.0.1' );

/**
 * Define plugin constants.
 */
define( 'MOJITO_SINPE_DIR', plugin_dir_path( __FILE__ ) );


/**
 * Plugin activation
 */

register_activation_hook(
	__FILE__,
	function () {
		require_once MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe-activator.php';
		Mojito_Sinpe_Activator::activate();
	}
);

/**
 * Plugin deactivation.
 */
register_deactivation_hook(
	__FILE__,
	function () {
		require_once MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe-deactivator.php';
		Mojito_Sinpe_Deactivator::deactivate();
	}
);

/**
 * The core plugin class that is used to define internationalization and public-facing site hooks.
 */
require MOJITO_SINPE_DIR . 'includes/class-mojito-sinpe.php';

/**
 * Begins execution.
 *
 * @since    1.0.0
 */
function mojito_sinpe_run() {
	global $mojito_sinpe;
	if ( ! isset( $mojito_sinpe ) ) {
		$mojito_sinpe = new Mojito_Sinpe();
		$mojito_sinpe->run();
	}
}
mojito_sinpe_run();

