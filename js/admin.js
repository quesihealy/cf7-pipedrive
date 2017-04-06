(function ( $ ) {
	"use strict";

	$(function () {

		$( document ).ready(function() {	
			$('#popup_header_color').wpColorPicker();
			$('#popup_header_border_color').wpColorPicker();
			$('#popup_title_color').wpColorPicker();			
			var title_icon_uploader; 
		    $('#popup_title_image_button').click(function(e) {
		 
		        e.preventDefault();
		 
		        //If the uploader object has already been created, reopen the dialog
		        if (title_icon_uploader) {
		            title_icon_uploader.open();
		            return;
		        }
		 
		        //Extend the wp.media object
		        title_icon_uploader = wp.media.frames.file_frame = wp.media({
		            title: 'Choose Image',
		            button: {
		                text: 'Choose Image'
		            },
		            multiple: false
		        });
		 
		        //When a file is selected, grab the URL and set it as the text field's value
		        title_icon_uploader.on('select', function() {		        	        	
		            var attachment = title_icon_uploader.state().get('selection').first().toJSON();		            
		            $('#popup_title_image').val(attachment.url);
		        });
		 
		        //Open the uploader dialog
		        title_icon_uploader.open();
		 
		    });
		});
	});

}(jQuery));