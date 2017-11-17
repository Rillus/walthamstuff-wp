		//new sendit js
		jQuery('#senditform').submit(function(e){
			e.preventDefault();
			jQuery('#sendit_subscribe_button').prop('disabled', true);
			jQuery('#sendit_response').fadeOut();
			jQuery('#sendit-wait').show();
			jQuery('#sendit-wait').spin('start');

			subscriber = jQuery( "#email_add" ).val();
			options = jQuery("#senditform").serialize();
			lista = jQuery('#lista').val();
				jQuery.post(sendit_ajaxurl, {
					action: 'sendit_subscription',
					options: options, email_add: jQuery( "#email_add" ).val(), lista: lista
				}, function(output) {
				jQuery('#sendit_subscribe_button').prop('disabled', false);
				jQuery('#sendit-wait').fadeOut();
				jQuery('#sendit_response').fadeIn();
				jQuery('#sendit_response').html(output);
				//alert(output);
			});
		});
