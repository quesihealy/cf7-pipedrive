(function ( $ ) {
	"use strict";
	$(function () {
		$( document ).ready(function() {	


			var checkbox_values = $('input[name="cf7_pipedrive_forms[]"]:checked').map(function() {
			    return this.value;
			}).get();

			$.each( checkbox_values, function( key, value ) {
				$('.field_value_'+value).show();
			});

			$('input[name="cf7_pipedrive_forms[]"]').change(function() {
				var checkbox_values = $('input[name="cf7_pipedrive_forms[]"]:checked').map(function() {
				    return this.value;
				}).get();
				$('.cf7_pipedrive_field_value').hide();
				$.each( checkbox_values, function( key, value ) {
					$('.field_value_'+value).show();
				});
			});


		});
	});

}(jQuery));