(function ( $ ) {
	"use strict";

	$(function () {

		$( document ).ready(function() {	
			var contheight = jQuery( ".popup-content" ).outerHeight()+2;      	
	      	jQuery( ".sticky-popup" ).css( "bottom", "-"+contheight+"px" );

	      	jQuery( ".sticky-popup" ).css( "visibility", "visible" );

	      	jQuery('.sticky-popup').addClass("open_sticky_popup");
	      	jQuery('.sticky-popup').addClass("popup-content-bounce-in-up");
	      	
	        jQuery( ".popup-header" ).click(function() {
	        	if(jQuery('.sticky-popup').hasClass("open"))
	        	{
	        		jQuery('.sticky-popup').removeClass("open");
	        		jQuery( ".sticky-popup" ).css( "bottom", "-"+contheight+"px" );
	        	}
	        	else
	        	{
	        		jQuery('.sticky-popup').addClass("open");
	          		jQuery( ".sticky-popup" ).css( "bottom", 0 );		
	        	}
	          
	        });		    
		});
	});

}(jQuery));