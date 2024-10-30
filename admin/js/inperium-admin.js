(function( $ ) {
	'use strict';

	$(function() {
		$( "#inperium-properties-sortable" ).sortable({
      placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			delay: 150,
			opacity: 0.8,
			revert: 150,
			update: function () {
				$(this).find('.inperium-property-order').each(function(){
					$(this).val($(this).parents('li.inperium-fields-row').index());
				});
			}
    });
    $( "#inperium-properties-sortable" ).disableSelection();
	});

})( jQuery );