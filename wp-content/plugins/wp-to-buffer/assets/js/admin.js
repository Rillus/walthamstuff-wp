jQuery( document ).ready( function( $ ) {

	/**
	* Character Count
	*/
	var wp_to_buffer_pro_character_counting = false;
	var wp_to_buffer_pro_character_count = function() {

		// If we're currently running an AJAX request, don't run another one
		if ( wp_to_buffer_pro_character_counting ) {
			return;
		}

        // Find the displayed panel
		$( 'div.sub-panel' ).each( function() {
			if ( $( this ).css( 'display' ) == 'block' ) {
				var active_panel = this,
					statuses = [];

				// Iterate through all textareas within the active panel, getting the status text for each
				$( 'div.status textarea', $( active_panel ) ).each( function() {
					statuses.push( $( this ).val() );
				} );

				// Set a flag so we know we're performing an AJAX request
				wp_to_buffer_pro_character_counting = true;

				// Send an AJAX request to fetch the parsed statuses and character counts for each status
				$.post( 
					wp_to_buffer_pro.ajax, 
					{
						'action': 						'wp_to_buffer_pro_character_count',
						'post_id': 						wp_to_buffer_pro.post_id,
						'statuses': 					statuses,
						'nonce': 						wp_to_buffer_pro.character_count_nonce
					},
					function( response ) {

						// Iterate through the textareas again
						$( 'div.status textarea', $( active_panel ) ).each( function( i ) {
							// Update the character count for this textarea
							$( 'span.character-count', $( this ).parent() ).text( response.data.parsed_statuses[ i ].length );	
						} );

						// Reset the flag
						wp_to_buffer_pro_character_counting = false;
						
		            }
		        );
			}
		} );
	}

	/**
	 * Character Count Events
	 */
	$( '#wp-to-buffer-pro-override .nav-tab-wrapper a' ).on( 'click', function( e ) {
		wp_to_buffer_pro_character_count();
	} );
	$( '#wp-to-buffer-pro-override input[type="checkbox"]' ).on( 'change', function( e ) {
		wp_to_buffer_pro_character_count();
	} );
	$( '#wp-to-buffer-pro-override div.status textarea' ).on( 'change', function( e ) {
		wp_to_buffer_pro_character_count();
	} );
	$( '#wp-to-buffer-pro-override a.button.add-status' ).on( 'change', function( e ) {
		wp_to_buffer_pro_character_count();
	} );

	/**
	* Clear Log
	*/
	$( '#wp-to-buffer-pro-log a.clear-log' ).on( 'click', function(e) {
		e.preventDefault();

		// Confirm clear
		var result = confirm( wp_to_buffer_pro.clear_log_message );
		if ( ! result ) {
			return;
		}

		$.post( 
			wp_to_buffer_pro.ajax, 
			{
				'action': 						'wp_to_buffer_pro_clear_log',
				'post': 						$( 'input[name=post_ID]' ).val(),
				'wp-to-buffer-pro-clear-log': 	1,
				'nonce': 						wp_to_buffer_pro.clear_log_nonce
			},
			function(response) {
				if ( response == '1' ) {
					// Clear log from UI
					$( '#wp-to-buffer-pro-log table.widefat tbody' ).html( '<tr><td colspan="3">' + wp_to_buffer_pro.clear_log_completed + '</td></tr>' );
				}
            }
        );
	} );

	/**
	* Tags
	*/
	var reinit_tags = function() {
		$( 'select.tags' ).each( function() {
			$( this ).unbind( 'change.wp-to-buffer-pro' ).on( 'change.wp-to-buffer-pro', function( e ) {
				// Insert tag into required textarea
				var tag 	= $( this ).val(),
					status 	= $( this ).closest( 'div.status' ),
					sel 	= $( 'textarea', $( status ) ),
					val 	= $( sel ).val();

				$( sel ).val( val += ' ' + tag ).trigger( 'change' );
			});
		});
	}
	reinit_tags();
	
	/**
	* Add Status Update
	*/
	$( 'a.button.add-status' ).on( 'click', function( e ) {
		e.preventDefault();

		// Setup vars
		var button 				= $( this ),
			button_container 	= $( button ).parent(),
			statuses_container 	= $( button ).closest( 'div.statuses' ),
			status 				= $( button_container ).prev().html();

		// Clone status
		$( button_container ).before( '<div class="option sortable">' + status + '</div>' );

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

		// Reload sortable
		$( 'div.statuses' ).sortable( 'refresh' );

		// Reload conditionals
		$( 'input,select' ).conditional();

		// Reload tag selector
		reinit_tags();

	});

	/**
	* Reorder Status Updates
	*/
	if ( typeof sortable !== 'undefined' ) {
		$( 'div.statuses' ).sortable( {
			containment: 'parent',
			items: '.sortable',
			stop: function( e, ui ) {
				// Get status and container
				var status 				= $( ui.item ),
					statuses_container 	= $( status ).closest( 'div.statuses' );

				// Reindex statuses
				reindex_statuses( $( statuses_container ) );
			}
		} );
	}

	/**
	* Force focus on inputs, so they can be accessed on mobile.
	* For some reason using jQuery UI sortable prevents us accessing textareas on mobile
	* See http://bugs.jqueryui.com/ticket/4429
	*/
	$( 'div.statuses' ).bind( 'click.sortable mousedown.sortable', function( e ) {
		e.target.focus();
	} );

	/**
	* Delete Status Update
	*/
	$( 'div.sub-panel' ).on( 'click', 'a.delete', function( e ) {
		e.preventDefault();

		// Confirm deletion
		var result = confirm( wp_to_buffer_pro.delete_status_message );
		if ( ! result ) {
			return;
		}

		// Get status and container
		var status 				= $( this ).closest( 'div.option' ),
			statuses_container 	= $( status ).closest( 'div.statuses' );

		// Delete status
		$( status ).remove();

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

	});

	/**
	* Changes the displayed index on each status within the given container
	*
	* @since 3.0
	*
	* @param obj status_container  		Status Container
	*/
	var reindex_statuses = function( statuses_container ) {

		// Find all sortable options in the status container (these are individual statuses)
		// and reindex them from 1
		$( 'div.option.sortable', $( statuses_container ) ).each(function( i ) {
			$( 'div.number a.count ', $( this ) ).html( '#' + ( i + 1 ) );

			// Set 'first' class
			if ( i == 0 ) {
				$( this ).addClass( 'first' );
			} else {
				$( this ).removeClass( 'first' );
			}
		});

	}

	/**
	* Datepicker
	*/
	if ( typeof datepicker !== 'undefined' ) {
		$( 'input.datepicker' ).datepicker({
			dateFormat: 'yy-mm-dd',
			maxDate: 0,
		});
	}

	/**
	* Select All
	*/
	$( 'body.wp-to-buffer-pro_page_wp-to-buffer-pro-bulk-publish' ).on( 'change', 'input[name=toggle]', function( e ) {
		// Change
		if ( $( this ).is( ':checked' ) ) {
			$( 'ul.categorychecklist input[type=checkbox]' ).prop( 'checked', true );
		} else {
			$( 'ul.categorychecklist input[type=checkbox]' ).prop( 'checked', false );
		}
	} );

});