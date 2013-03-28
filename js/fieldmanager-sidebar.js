jQuery( document ).ready( function( $ ) {
	$('.fm-sidebar-widget').css('display', 'none');
	$('.fm-sidebar-select').each( function() {
  		$(this).nextAll("."+  $(this).val() + '-wrap').first().css('display', 'block');
  		var $dataobj = jQuery.parseJSON($(this).attr('data-value'));
  		for(var datakey in $dataobj) {
  			if ( datakey != 'widget_id' ){

  				if ( $(this).nextAll("."+  $(this).val() + '-wrap').first().find('.widget-inside :input[name$="\['+datakey+'\]"]').is(':checkbox') && $dataobj[datakey] ) {
  					$(this).nextAll("."+  $(this).val() + '-wrap').first().find('.widget-inside :input[name$="\['+datakey+'\]"]').attr("checked","checked");
  				} else {
  					$(this).nextAll("."+  $(this).val() + '-wrap').first().find('.widget-inside :input[name$="\['+datakey+'\]"]').val($dataobj[datakey]);
  				}
  			}

  		}

	});
	$('.fm-sidebar-widget').each( function() {
		if( $(this).css('display') == 'none' ){
			$(this).find('.widget-inside :input').attr("disabled", "disabled");
		} else {
			$(this).find('.widget-inside :input').removeAttr("disabled");
		}
		if( !($(this).find('.widget-inside :input').hasClass("fm-element")) ) {
			$(this).find('.widget-inside :input').addClass("fm-element");
		}
	});

	$( '.fm-sidebar-select' ).live('change', function() {
		$(this).nextAll('.fm-sidebar-widget').css('display', 'none');
		$(this).nextAll("."+  $(this).val() + '-wrap').first().css('display', 'block');
		$('.fm-sidebar-widget').each( function() {
			if( $(this).css('display') == 'none' ){
				$(this).find('.widget-inside :input').attr("disabled", "disabled");
			} else {
				$(this).find('.widget-inside :input').removeAttr("disabled");
			}
			if( !($(this).find('.widget-inside :input').hasClass("fm-element")) ) {
				$(this).find('.widget-inside :input').addClass("fm-element");
			}
		});
	} );
} );

