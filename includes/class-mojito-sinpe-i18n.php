<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mojito_Sinpe
 * @subpackage Mojito_Sinpe/includes
 * @author     Manfred Rodriguez <marodok@gmail.com>
 */

namespace Mojito_Sinpe;

class Mojito_Sinpe_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'mojito-sinpe',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
