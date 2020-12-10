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
			bank_number = '7070-1212';

		} else if ( bank === 'lafise' ) {
			bank_number = '9091';

		} else if ( bank === 'davivienda' ) {
			bank_number = '70707474';

		} else if ( bank === 'mutual-alajuela' ) {
			bank_number = '70707079';

		} else if ( bank === 'promerica' ) {
			bank_number = '62232450';

		} else if ( bank === 'coopealianza' ) {
			bank_number = '62229523';

		} else if ( bank === 'caja-de-ande' ) {
			bank_number = '62229524';
		
		} else if ( bank === 'credecoop' ) {
			bank_number = '71984256';

		} else if ( bank === 'mucap' ) {
			bank_number = '62229525';
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
