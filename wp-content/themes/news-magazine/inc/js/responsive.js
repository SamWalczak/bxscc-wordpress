var wdwt_window_cur_size = 'screen';

jQuery('document').ready(function(){
	var sliderHeight = parseInt(jQuery("#slider-wrapper").height());
	var sliderWidth = parseInt(jQuery("#slider-wrapper").width());
	var sliderIndex = sliderHeight/sliderWidth;
	if(matchMedia('only screen and (max-width : 767px)').matches){
		phone();		
	} else if (matchMedia('only screen and (min-width: 768px) and (max-width: 1024px)').matches){
		tablet();
	} else{
		checkMedia();
	}
	jQuery(window).resize(function() {
		checkMedia();
	});
	
	function checkMedia(){
		//################SCREEN
		if(matchMedia('only screen and (min-width: 1025px)').matches){ screen(); }
		//################TABLET
		if (matchMedia('only screen and (min-width: 768px) and (max-width: 1024px)').matches){ tablet(); }
		//################PHONE
		if(matchMedia('only screen and (max-width : 767px)').matches){ phone(false); }
		if(typeof(wdwt_slider_resize)=='function'){
			wdwt_slider_resize();
		}
	}

	function screen(){
		sHeight = sliderIndex*parseInt(jQuery("#slider-wrapper").width());
		wdwt_sliderSize(sHeight);	
		if(wdwt_window_cur_size == 'phone' || wdwt_window_cur_size == 'tablet'){
			jQuery('#content').before(jQuery('#sidebar1'));
			jQuery("#top-nav > div > ul  li.addedli,#top-nav > div > div > ul  li.addedli").remove();
		}
		if(wdwt_window_cur_size == 'phone'){
			jQuery("#top-nav").show();	
		}
		if(wdwt_window_cur_size == 'phone' || wdwt_window_cur_size == 'tablet'){
			jQuery("#top-nav .sub-menu").css("display","");	
		}
		wdwt_inserting_div_float_problem(jQuery('#sidebar-footer'));
	
		wdwt_window_cur_size	= 'screen';
	}
	
	function tablet(){
		if(wdwt_window_cur_size == 'screen'){
			jQuery('#content').after(jQuery('#sidebar1'));
			jQuery("#top-nav > div > ul  li:has(> ul),#top-nav > div > div > ul  li:has(> ul)").each(function(){
				var strtext=jQuery(this).children("a").html();
				var strhref=jQuery(this).children("a").attr("href");
				var strlink='<a href="'+strhref+'">'+strtext+'</a>';
				jQuery(this).children("ul").prepend('<li class="addedli">'+strlink+'</li>');
			});
		}
		if(wdwt_window_cur_size == 'phone'){
			jQuery("#top-nav").show();	
		}
		sHeight = sliderIndex*parseInt(jQuery("#slider-wrapper").width());
		wdwt_sliderSize(sHeight);

		wdwt_window_cur_size	= 'tablet';
	}
	
	function phone(full){
		sHeight = sliderIndex*parseInt(jQuery("#slider-wrapper").width());
		wdwt_sliderSize(sHeight);
				
		if(wdwt_window_cur_size == 'screen'){
			jQuery('#content').after(jQuery('#sidebar1'));
			jQuery("#top-nav > div > ul  li:has(> ul),#top-nav > div > div > ul  li:has(> ul)").each(function(){
				var strtext=jQuery(this).children("a").html();
				var strhref=jQuery(this).children("a").attr("href");
				var strlink='<a href="'+strhref+'">'+strtext+'</a>';
				jQuery(this).children("ul").prepend('<li class="addedli">'+strlink+'</li>');
			});
		}
		
		wdwt_window_cur_size	= 'phone';
	}
	
	function wdwt_sliderSize(sHeight) {
		jQuery("#slider-wrapper").css('height',sHeight);
	}	
	
	function wdwt_inserting_div_float_problem(main_div){
		jQuery(main_div).children('.clear:not(:last-child)').remove();
		var iner_elements=jQuery(main_div).children();
		var main_width=jQuery(main_div).width();
		var summary_width=0;
		for(i=0;i<iner_elements.length;i++){
			summary_width=summary_width+jQuery(iner_elements[i]).outerWidth();
			if(summary_width >= main_width){
				jQuery(iner_elements[i]).before('<div class="clear"></div>')
				summary_width=jQuery(iner_elements[i]).outerWidth();
			}
		}
	}
	
});

