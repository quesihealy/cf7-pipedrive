(function ( $ ) {
	"use strict";
	$(function () {
		$( document ).ready(function() {

      $('.btn').click(function (event) {
       var value =  $('#add_new').val();
       $('#'+value).show();
      });

      $('.fields_forms_pipedrive').each(function () {
        var selectField = $(this).find('select').val();
        if (selectField) {
          $(this).show();
        }
      });

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
