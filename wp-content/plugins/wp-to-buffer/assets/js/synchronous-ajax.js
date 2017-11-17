/**
* Synchronous AJAX Requests
*
* This jQuery plugin allows feature rich web applications to send a specified number of requests, sequentially and synchronously,
* to a given endpoint, binding the progress to a jQuery UI Progressbar.
*
* This plugin acts as a wrapper for $.post, with some extra callback functions when each request succeeds or fails, plus a final 
* callback function when the entire routine completes.
*
* The advantage of this approach is that the UI is not locked - so updates can be posted to the web page - and the server isn't flodded
* with 100 requests at once.  Each request must complete before the next one can run.
*
* Your server-side script will be sent all data as a POST array, including POST['current_index'], telling your script what number fhits
* request is.
*/
(function($){
	/**
	* Init Synchronous Request
	*
	* @param object options Override Default Settings
	*/
	$.fn.synchronous_request = function( options ) {
		// Default Settings
		var settings = $.extend({
			// Required
			url: 			'',
			number_requests:0,
			data: 			{ },

			// Optional
			progress_bar: 	'.progress-bar',
			type: 			'post',
			cache: 			false,
			dataType: 		'json',
			onRequestSuccess:function(response) {
			},
			onRequestError: function(xhr, textStatus, e) {
			},
			onFinished: function() {
			}
		}, options);

		// Init ProgressBar
		progressbar = $( this ).progressbar({
			value: 0
		});

		// Init first request
		synchronous_ajax_request( settings, -1, progressbar );
   	}; 

   	/**
   	* Do Synchronous Request
   	*/
   	function synchronous_ajax_request( settings, currentIndex, progressbar ) {
		// Increment 
   		currentIndex++;

   		// If currentIndex exceeds or equals settings.number_requests, we have finished
   		if ( currentIndex >= settings.number_requests ) {
   			// Call completion closure
   			settings.onFinished();
   			return true;
   		}

   		// Include currentIndex in settings.data
   		var data = $.extend({
   			current_index: currentIndex
   		}, settings.data);

   		// Do request
		$.ajax({
		    url:      	settings.url,
		    type:     	settings.type,
		    async:    	true,
		    cache:    	settings.cache,
		    dataType: 	settings.dataType,
		    data: 		data,
		    success: function( response ) {
		    	// Update Progress Bar
		    	progressbar.progressbar( 'value', Number(((currentIndex+1) / settings.number_requests) * 100) );

		    	// Call closure
		    	settings.onRequestSuccess(response);

		    	// Start next request
		    	synchronous_ajax_request( settings, currentIndex, progressbar );
		    	return;
		    },
		    error: function(xhr, textStatus, e) {
		    	console.log(xhr);
		    	console.log(textStatus);
		    	console.log(e);
		    	
		    	// Update Progress Bar
		    	progressbar.progressbar( 'value', Number(((currentIndex+1) / settings.number_requests) * 100) );

		    	// Call closure
		    	settings.onRequestError(xhr, textStatus, e);

		    	// Start next request
		    	synchronous_ajax_request( settings, currentIndex, progressbar );
		    	return;
		    }
		});
   	}
})(jQuery);