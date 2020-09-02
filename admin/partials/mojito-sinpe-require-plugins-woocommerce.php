<?php

namespace Mojito_Sinpe;

if ( ! class_exists( 'WooCommerce' ) ) {
	?>
	<div id="message" class="error">
		<p>
			<?php echo __( 'Mojito Sinpe Plugin requires WooCommerce to be active.', 'mojito-sinpe' ); ?>
			<a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce</strong></a>
		</p>
	</div>
	<?php
}
