(function( $ ) {
	'use strict';
	
	$(function() {
		$(".inperium-form").validate({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					url: inperium_ajax.ajax_url,
					data: {
						_ajax_nonce: inperium_ajax.nonce,
						action: 'inperium_form_submit'
					},
					type: 'post',
					dataType: 'json',
					beforeSubmit: function(_, form, __) {
						form.children('button[type="submit"]').prop('disabled', true);		         
						form.children('button[type="submit"]').html(inperium_ajax.submitting_text);		         
					},
					success: function(response, _, __, form) {
						const data = response.data;
						form.children('div.error').hide();
						form[0].reset();
						form.children('button[type="submit"]').html(inperium_ajax.submitted_text);
						if (data.redirect_page) window.location.replace(data.redirect_page);
					},
					error: function(response, _, __, form) {
						const data = response.responseJSON.data;
						form.children('div.error').html(`${inperium_ajax.error_text}: ${data.message || data.details || inperium_ajax.unknown_error_text}`);
						form.children('div.error').show();
						form.children('button[type="submit"]').prop('disabled', false);
						form.children('button[type="submit"]').html(inperium_ajax.submit_text);
					}
				});
			}
		});
	});

})( jQuery );