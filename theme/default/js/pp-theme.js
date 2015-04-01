

jQuery(document).ready(function(){

	jQuery('.pp-dynamic-avatar').initial({fontSize:15, fontWeight:600}); 
	
	jQuery( document ).ajaxComplete(function( event, data, settings ) {
		jQuery('.pp-dynamic-avatar').initial({fontSize:15, fontWeight:600});
	});
	
});
