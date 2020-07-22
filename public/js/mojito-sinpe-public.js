(function( $ ) {
	'use strict';

	 $(document).on('change', '.mojito_sinpe_bank_selector', function(event) {
		 
		event.preventDefault();

		var link = $('.mojito-sinpe-link');
		var type = link.data('type');
		var bank = $(this).val();
		var text_container = $('.mojito-sinpe-payment-container');

		if ( bank === 'none' ){
			link.hide();
			text_container.hide();
			return;
		}

		var bank_number = '';
		if ( bank === 'bn' ) {
			bank_number = '2627';

		} else if ( bank === 'bcr' ) {
			bank_number = '2276';

		} else if ( bank === 'bac' ) {
			bank_number = '1222';

		} else if ( bank === 'lafise' ) {
			bank_number = '9091';

		} else if ( bank === 'davivienda' ) {
			bank_number = '7070-7474';

		} else if ( bank === 'mutual-alajuela' ) {
			bank_number = '7070-7079';

		} else if ( bank === 'promerica' ) {
			bank_number = '6223-2450';

		} else if ( bank === 'coopealianza' ) {
			bank_number = '6222-9523';

		} else if ( bank === 'caja-de-ande' ) {
			bank_number = '6222-9524';

		} else if ( bank === 'mucap' ) {
			bank_number = '8858-4646';
		}
		
		if ( type === 'mobile' ){
			var href = 'sms:+' + bank_number + '?body=' + link.data('msj');
			link.attr('href', href);
			link.show();
		}else{			
			text_container.text('Envie un SMS al +' + bank_number + ' con el texto: ' + link.data('msj') );
			text_container.show();
		}
		

	 })

})( jQuery );
