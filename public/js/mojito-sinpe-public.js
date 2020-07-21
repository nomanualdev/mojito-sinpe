(function( $ ) {
	'use strict';

	 $(document).on('click', '.mojito-sinpe-toggle-tracking', function(event) {
		 event.preventDefault();
		 $('.tracking-details').toggleClass('open');
	 })

})( jQuery );
