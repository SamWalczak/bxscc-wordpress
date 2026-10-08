jQuery(document).ready(function(){
	jQuery('#top-nav li:has(> ul)').addClass('haschild');
	jQuery("#top-nav > div > ul  li,#top-nav > div > div > ul  li").hover(function(){
	  if(matchMedia('only screen and (max-width : 767px)').matches || matchMedia('only screen and (max-width : 1024px)').matches){return false;}
		jQuery(this).parent("ul").find("ul").slideUp(5);
		jQuery(this).parent("ul").children().removeClass("active");
		jQuery(this).addClass("active");
	  if(jQuery(this).find("ul").length){jQuery(this).children("ul").stop().slideDown("fast").addClass("open");}
	},function(){
    if(matchMedia('only screen and (max-width : 767px)').matches || matchMedia('only screen and (max-width : 1024px)').matches){return false;}
		jQuery(this).parent("ul").children().removeClass("active");
		jQuery(this).find(">ul").slideUp(100);
		jQuery(".open").removeClass("open");
	});
		
	jQuery("#top-nav > div > ul  li.haschild > a,#top-nav > div > div > ul  li.haschild > a").click(function(){
		if(matchMedia('only screen and (max-width : 767px)').matches || matchMedia('only screen and (min-width: 768px) and (max-width: 1024px)').matches){
			if(jQuery(this).parent().hasClass("open")){
				jQuery(this).parent().parent().find(".haschild ul").slideUp(100);
				jQuery(this).parent().removeClass("open");
				return false;
			}
			jQuery(this).parent().parent().find(".haschild ul").slideUp(100);
			jQuery(this).parent().parent().find(".haschild").removeClass("open");
			jQuery(this).next("ul").slideDown("fast");
			jQuery(this).parent().addClass("open");
			return false;
		}
	});
	
	jQuery("header").on("click","#menu-button-block", function(){
		if(jQuery("#top-nav").hasClass("open")){
			jQuery("header #top-nav").slideUp("fast");
			jQuery("#top-nav").removeClass("open");
		}
		else{
			jQuery("header #top-nav").slideDown("slow");
			jQuery("#top-nav").addClass("open");
		}
	});
	
	var visible=jQuery("#wd-categories-vertical-tabs ul.tabs li").length;
	var data_count=jQuery("#wd-categories-vertical-tabs ul.tabs").attr("data-count");
	////jQuery("#wd-categories-vertical-tabs .arrows-block").height(visible*76+"px");
	////jQuery("#wd-categories-vertical-tabs .content-block").css('min-height', visible*76+10);
	/////jQuery("#wd-categories-vertical-tabs .content-block .tab-content").height(visible*76+"px");

	
	jQuery("#wd-categories-vertical-tabs .content-block > ul > li").height(jQuery("#wd-categories-vertical-tabs ul.tabs").height()-50);
	
	for(var i=1; i<=data_count; i++){
		if(jQuery(".content-block #categories-vertical-tabs-content-"+i+"").find(".thumbnail-block").length){
		  ///////jQuery(".content-block #categories-vertical-tabs-content-"+i+" .text").height((visible*76-210)+"px");
		  /////jQuery(".phone .content-block #categories-vertical-tabs-content-"+i+"").css("height","360px");
		  ////jQuery(".phone .content-block #categories-vertical-tabs-content-"+i+" .text").css("height","172px");
		} else{
		  ////jQuery(".phone .content-block #categories-vertical-tabs-content-"+i+"").css("height","180px");
		} 
	}
	
	
	/////jQuery("#wd-categories-vertical-tabs ul.content li:first-child").css({"display":"block"}).addClass("active");
	jQuery("#wd-categories-vertical-tabs ul.tabs li a").click(function(){
		if(jQuery(this).hasClass("changing")){return false;}
		jQuery(this).addClass("changing");
		
		tabClick(jQuery(this),"constant");
		return false;	
	});
	
	
	
	jQuery("#wd-categories-vertical-tabs .arrow-up").click(function(){
		jQuery(this).addClass("changing");
		count=(jQuery("#wd-categories-vertical-tabs ul.tabs li").length-1); 
		if(matchMedia('only screen and (max-width : 767px)').matches){var isactive=jQuery("#wd-categories-vertical-tabs ul.tabs li.active").prev().index();
		if(isactive==-1){isactive=count;}
			jQuery("#wd-categories-vertical-tabs ul.tabs li").removeClass("active");
			jQuery("#wd-categories-vertical-tabs ul.tabs li").eq(isactive).addClass("active");
			
			jQuery("#wd-categories-vertical-tabs ul.content > li").removeClass("active").css({display:"none"});
			jQuery("#wd-categories-vertical-tabs ul.content > li").eq(isactive).addClass("active").css({display:"block"});}
		else{
			var gotop=true;
			if(jQuery("#wd-categories-vertical-tabs ul.tabs li.active").index()=="0"){return false;}
			else{ tabClick(jQuery("#wd-categories-vertical-tabs ul.tabs li.active").prev().find("a"),gotop);return false;}
		}
	});
	
	
	jQuery("#wd-categories-vertical-tabs .arrow-down").click(function(){
		jQuery(this).addClass("changing");
		count=(jQuery("#wd-categories-vertical-tabs ul.tabs li").length-1);
		if(matchMedia('only screen and (max-width : 767px)').matches){var isactive=jQuery("#wd-categories-vertical-tabs ul.tabs li.active").next().index();
		if(isactive==-1){isactive=0;}
			jQuery("#wd-categories-vertical-tabs ul.tabs li").removeClass("active");
			jQuery("#wd-categories-vertical-tabs ul.tabs li").eq(isactive).addClass("active");
			jQuery("#wd-categories-vertical-tabs ul.content > li").removeClass("active").css({display:"none"});
			jQuery("#wd-categories-vertical-tabs ul.content > li").eq(isactive).addClass("active").css({display:"block"});}
		else{
			if(jQuery("#wd-categories-vertical-tabs ul.tabs li.active").index()==count){tabClick(jQuery("#wd-categories-vertical-tabs ul.tabs li").eq(0).find("a"),false);return false;}
			else{tabClick(jQuery("#wd-categories-vertical-tabs ul.tabs li.active").next().find("a"),false);return false;}
		}
	});
	

	function tabClick(thisElem,gotop){
		
		if(jQuery("#wd-categories-vertical-tabs .tabs-scroll-block").css("top")=='auto')
			jQuery("#wd-categories-vertical-tabs .tabs-scroll-block").css("top",0);
		jQuery("#wd-categories-vertical-tabs ul.tabs li").removeClass("active");
		var id=thisElem.attr("href").replace("#","");
		thisElem.parent().addClass("active");
		jQuery("#wd-categories-vertical-tabs ul.content li.active").css({display:"none"});
		jQuery("#wd-categories-vertical-tabs ul.content li").removeClass("active");
		jQuery("#categories-vertical-tabs-content-"+id).fadeIn(800).addClass("active");
		
		var visible=jQuery("#wd-categories-vertical-tabs .tabs").attr("data-visible");
		var active=(jQuery("#wd-categories-vertical-tabs ul.content li.active").index()+1);
		
		if(active=="1" || (gotop=="constant" && active<=visible)){
			jQuery("#wd-categories-vertical-tabs .tabs-scroll-block").css({"top":"0"});
		}
		else if(active>visible && !gotop){
			jQuery("#wd-categories-vertical-tabs .tabs-scroll-block").css({"top":"-=76"});
		}
		else if(gotop=='constant' && active>=visible){
			return false;
		}
		else if(gotop && active>=visible){
			jQuery("#wd-categories-vertical-tabs .tabs-scroll-block").css({"top":"+=76"});
		}
		jQuery("#wd-categories-vertical-tabs a").removeClass("changing");
		return false;
	}
	
});

function wdwt_front_ajax_pagination(page, action, container, args){
  var data_send = {};

  if(typeof args != 'undefined'){
    for (var prop in args) {
      data_send[prop] = args[prop];
    }
  }

  data_send.action = 'wdwt_front_'+action;
  data_send.paged = page;
  
  jQuery.post(news_magazine_admin_ajax, data_send, function(data) {  
      jQuery(container).html(data);
    }).success(function(jqXHR, textStatus, errorThrown) {
      jQuery(container).addClass("ajaxed");
    });
}