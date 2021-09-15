define([
  "jquery",
], 
function($) {
  "use strict";
  jQuery(document).ready(function(){
  	if($('body').hasClass('catalog-category-view') == true|| $('body').hasClass('blog-index-index') == true){
  		var cat_name = $('body .page-title-wrapper>h1>span').text();
  		$(".level-top>span:contains('"+cat_name+"')").filter(function() {
		    if(($(this).text() === cat_name)=== true){$(this).addClass('active-page');}
		});
  	}
  	else if($('body').hasClass('cms-home') == true){
  		$(".level-top>span:contains('Home')").addClass('active-page');
  	}
  	$(document).ready(function() {	
		$("#spinner").fadeOut("slow");
	});
  	/*$( ".account .page-title-wrapper" ).insertBefore( $( ".columns" ) );*/
  	$(".header-center .action.nav-toggle").appendTo(".tm_header.container-width");
  	
  	$(document).ready(function() {accountdash();})
	$(window).resize(function() {accountdash();})
  	function accountdash(){
		if((($(window).width()>767 === true) && (($(window).width()<980) === true))){
		$(".account .block-collapsible-nav .title").click(function(){
			$(".block-collapsible-nav .content").toggle();
		});}
	}

	$('.custommenu .menu-title').click(function() {
		$('.custommenu #mainmenu').slideToggle('slow');
		$('.custommenu .menu-title').toggleClass('active');
	});	

  });
	require(['jquery', 'owlcarousel','fancybox', 'jstree', 'bxslider', 'flexslider',], function($) {
	jQuery(document).ready(function() {

	 	jQuery(".lightbox").fancybox({
			'frameWidth' : 890,
	    	'frameHeight' : 630,
			openEffect  : 'fade',
			closeEffect : 'fade',
			helpers: {
	        	title: null
    		}
		});
		
		$("body").append("<a class='top_button' title='Back To Top' href=''></a>");
		$(window).scroll(function () {
			if ($(this).scrollTop() > 70) {
				$('.top_button').fadeIn();
				$('.top_button_bottom').fadeIn();
			} else {
				$('.top_button').fadeOut();
				$('.top_button_bottom').fadeOut();
			}
		});

		// scroll body to 0px on click
		
		$('.top_button,top_button_bottom').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});	   

		/*$('#blog-carousel').bxSlider({
			minSlides: 2,
			slideMargin: 0,
	    	moveSlides:1,
	    	auto: false,
	    	mode: 'vertical'

		});*/

	    	if ($(window).width() < 980)
			{
				$('#blog-carousel').bxSlider({
					minSlides: 1,
					mode: 'vertical'
				});
			}
			else{

				$('#blog-carousel').bxSlider({
					minSlides: 2,
					slideMargin: 0,
			    	moveSlides:1,
			    	auto: false,
			    	mode: 'vertical'
				});
			}

		/*jQuery("#blog-carousel").owlCarousel({
			nav: true,
			loop: true,
			items: 10,
			vertical: true,
			responsive: {
			0: {
			items: 1
			},
				480: {
			items: 2
			},
				768: {
			items: 3
			},
				1024: {
			items: 5
			}
			},
			navText: [
			  "<i class='icon-chevron-left icon-white'><</i>",
			  "<i class='icon-chevron-right icon-white'>></i>"
			 ]
        });*/

        

		jQuery("#brand-carousel").owlCarousel({
			nav: true,
			loop: true,
			autoplay:false,
			items: 7,
			autoplaySpeed:1000,
			autoplayTimeout:1000,
			responsive: {
			0: {
			items: 1
			},
				480: {
			items: 3
			},
				768: {
			items: 4
			},
				1024: {
			items: 5
			}
			},
			navText: [
			  "<i class='icon-chevron-left icon-white'><</i>",
			  "<i class='icon-chevron-right icon-white'>></i>"
			 ]
		});
		

		function bestDealCarousel(){
			jQuery("#best_deal_carousel .widget-product-carousel").owlCarousel({
				nav: true,
				loop: false,
				items: 3,
				responsive: {
				0: {
				items: 1
				},
					641: {
				items: 2
				},
					768: {
				items: 2
				},
					1024: {
				items: 2
				},
					1201: {
				items: 3
				}
				},
				navText: [
				  "<i class='icon-chevron-left icon-white'><</i>",
				  "<i class='icon-chevron-right icon-white'>></i>"
				 ]
			});
		}
		jQuery(document).ready(function(){bestDealCarousel();});
		jQuery(window).resize(function() {bestDealCarousel();});

		function categoryBlogCarousel(){
			jQuery("#category_blog-carousel.product-carousel").owlCarousel({
				nav: true,
				loop: false,
				items: 7,
				responsive: {
				0: {
				items: 1
				},
					480: {
				items: 3
				},
					768: {
				items: 4
				},
					1024: {
				items: 5
				},
					1201: {
				items: 6
				},
					1251: {
				items: 7
				}
				},
				navText: [
				  "<i class='icon-chevron-left icon-white'><</i>",
				  "<i class='icon-chevron-right icon-white'>></i>"
				 ]
			});
		}
		jQuery(document).ready(function(){categoryBlogCarousel();});
		jQuery(window).resize(function() {categoryBlogCarousel();});

		function productCarouselAutoSet(){
	        jQuery('.products-carousel .owl-carousel').owlCarousel({
				items: 5,
	        	nav: true,
				responsive: {
					0: {
					    items: 2
					},
							480: {
					    items: 2
					},
							640: {
					    items: 3
					},
						767: {
					    items: 4
					},
							980: {
					    items: 4
					},
							1024: {
					    items: 5
					}
				    },
				navText: [
				  "<i class='icon-chevron-left icon-white'><</i>",
				  "<i class='icon-chevron-right icon-white'>></i>"
				 ]
	    	});
	    	checkClasses();
    		jQuery('.products-carousel .owl-carousel').on('translated.owl.carousel', function(event) {
        		checkClasses();
    		});
		    function checkClasses(){
		    	//console.log($(".product_tabs .tab_product"));
		    	$(".column.main .block").each(function(){
		    	 var total = $(this).find('.owl-stage .owl-item.active').length;
		        	//console.log(this);
		        	$(this).find('.owl-stage .owl-item').removeClass('firstActiveItem lastActiveItem');
		        
		        $(this).find('.owl-stage .owl-item.active').each(function(index){
		            if (index === 0) {
		                $(this).addClass('firstActiveItem');
		            }
		            if (index === total - 1 && total>1) {
		                $(this).addClass('lastActiveItem');
		            }
		        });
    		}); 
        }}
		jQuery(document).ready(function(){productCarouselAutoSet();});
		jQuery(window).resize(function() {productCarouselAutoSet();});

        jQuery('#right-banner-inner').flexslider({
		    animation: "fade",
		    controlNav: true,
		    pauseOnHover:true,
		    slideshowSpeed:2000
		});
		jQuery('.flexslider').flexslider({
		    animation: "slide",
		    controlNav: false,
		    pauseOnHover:true
		});
		jQuery("#category-treeview").treeview({
			animated: "slow",
			collapsed: true,
			unique: true
		});

		function productListAutoSet(){
			jQuery('.widget-product-carousel').owlCarousel({
				items: 5,
				nav: true,		
				responsive: {
				0: {
				    items: 2
				},
						480: {
				    items: 2
				},
						641: {
				    items: 3
				},
						768: {
				    items: 3
				},
						1024: {
				    items: 4
				},
					1201: {
				    items: 5
				}
				},
				navText: [
				"<i class='icon-chevron-left icon-white'><</i>",
				"<i class='icon-chevron-right icon-white'>></i>"
				]
			});
			checkClasses();
    jQuery('.widget-product-carousel').on('translated.owl.carousel', function(event) {
        checkClasses();
    });
    function checkClasses(){
    	//console.log($(".product_tabs .tab_product"));
    	$(".product_tabs .tab_product").each(function(){
    	 var total = $(this).find('.owl-stage .owl-item.active').length;
        	//console.log(this);
        	$(this).find('.owl-stage .owl-item').removeClass('firstActiveItem lastActiveItem');
        
        $(this).find('.owl-stage .owl-item.active').each(function(index){
            if (index === 0) {
                $(this).addClass('firstActiveItem');
            }
            if (index === total - 1 && total>1) {
                $(this).addClass('lastActiveItem');
            }
        });
    	}); 
    }
		}
		jQuery(document).ready(function(){productListAutoSet();});
		jQuery(window).resize(function() {productListAutoSet();});

	
		jQuery(".tab_product:not(:first)").hide();
		jQuery(".tab_product:first").show();
		 
		//when we click one of the tabs
		jQuery(".tabbernav_product  li  a").click(function(){
	
			//get the ID of the element we need to show
			var stringref = jQuery(this).attr("href").split('#')[1];
			//hide the tabs that doesn't match the ID
			jQuery('.tab_product:not(#'+stringref+')').hide();
			 //fix
			if (jQuery.browser.msie && jQuery.browser.version.substr(0,3) == "6.0") {
			 	jQuery('.tab_product#' + stringref).show();
			}
			else{
				//display our tab fading it in
				jQuery('.tab_product#' + stringref).fadeIn();
			}
			jQuery(".tabbernav_product a").removeClass("selected");
			jQuery(this).addClass("selected");
			
			var $owl = jQuery('#'+stringref+' .widget-product-carousel');
			$owl.trigger('destroy.owl.carousel');
			$owl.html($owl.find('.owl-stage-outer').html()).removeClass('owl-loaded');	
			productListAutoSet();
			return false;
		});

    }); // Require Ends here


	function mobileHeaderLink(){
		if (jQuery(window).width() < 768)
		{  	 
			jQuery(".tm_headerlinkmenu" ).addClass('toggle');	
			jQuery(".tm_headerlinkmenu .headertoggle_img").click(function(){
				jQuery(".tm_headerlinks_inner").parent().toggleClass('active').parent().find('.tm_headerlinks').slideToggle(0);
				jQuery(".header_customlink").parent().find('ul').removeAttr('style');
				jQuery(".navigation.custommenu").parent().find('#mainmenu').removeAttr('style');
			});


		 }
		 else
		 {
		 	jQuery('.tm_headerlinkmenu').mouseover(function() {
		    	 jQuery(".tm_headerlinks").parent().addClass("active").find(".tm_headerlinks").css("display","block");
		  	});	
		  	jQuery('.tm_headerlinkmenu').mouseout(function() {
		    	 jQuery(".tm_headerlinks").parent().removeClass("active").find(".tm_headerlinks").css("display","none");
		  	}); 
			
			jQuery('.minicart-wrapper').mouseover(function() {
				 jQuery(".action.showcart").addClass("active")
		    	 jQuery(".minicart-wrapper").addClass("active").find(".block-minicart").css("display","block");
		    	 jQuery(".minicart-wrapper > div").css("display","block");
		  	});
		  	jQuery('.minicart-wrapper').mouseout(function() {
		  		 jQuery(".action.showcart").removeClass("active")
		    	 jQuery(".minicart-wrapper").removeClass("active").find(".block-minicart").css("display","none");
		    	 jQuery(".minicart-wrapper > div").css("display","none");
		  	});
		 }
	}
	jQuery(document).ready(function(){mobileHeaderLink();});
	

	function footerToggleMenu(){
		if (jQuery(window).width() < 980)
		{
			jQuery(".page-footer .footer-area .mobile_togglemenu").remove();
			jQuery(".page-footer .footer-area h6").append( "<a class='mobile_togglemenu'>&nbsp;</a>" );
			jQuery(".page-footer .footer-area h6").addClass('toggle');
			jQuery(".page-footer .footer-area .mobile_togglemenu").click(function(){
				jQuery(this).parent().toggleClass('active').parent().find('ul').toggle('slow');
				jQuery(this).parent().parent().find('.payment_block').toggle('slow');
			});
		}else{
			jQuery(".page-footer .footer-area h6").parent().find('ul').removeAttr('style');
			jQuery(".page-footer .footer-area  h6").removeClass('active');
			jQuery(".page-footer .footer-area  h6").removeClass('toggle');
			jQuery(".page-footer .mobile_togglemenu").remove();
		}	
	}
	jQuery(document).ready(function(){footerToggleMenu();});
	jQuery(window).resize(function(){footerToggleMenu();}); 

	function sidebarToggle(){
		if (jQuery(window).width() < 980){
			jQuery(".sidebar .block .mobile_togglemenu").remove();
			jQuery(".sidebar .block .block-title").append( "<a class='mobile_togglemenu'>&nbsp;</a>" );
			jQuery(".sidebar .block .block-title").addClass('toggle');
			jQuery(".sidebar .block .mobile_togglemenu").click(function(){
				jQuery(this).parent().toggleClass('active').parent().find('.block-content').slideToggle('slow');
			});
		}else{
			jQuery(".sidebar .block .block-title").parent().find('.block-content').removeAttr('style');
			jQuery(".sidebar .block .block-title").removeClass('active');
			jQuery(".sidebar .block .block-title").removeClass('toggle');
			jQuery(".sidebar .block .mobile_togglemenu").remove();
		}	
	}
	jQuery(window).resize(function(){sidebarToggle();});
	jQuery(document).ready(function(){sidebarToggle();});
	jQuery(document).load(function(){sidebarToggle();});
	 


	/*jQuery(function($){
		var max_elem = 3 ;
		var items = $('.navigation.custommenu .mainmenu > ul > li');
		var surplus = items.slice(max_elem, (items.length-2));
		surplus.wrapAll('<li class="menu-dropdown-icon level0 hiden_menu"><ul class="dropdown-inner-list">');
		$('.hiden_menu').prepend('<a class="level-top">More</a>');
		});
*/
	

	function top_banner(){
	 	if(jQuery('body').hasClass('cms-home')){
			jQuery('.header-top-banner').show();
		}
		jQuery(".close-btn").on("click", function() {
			jQuery(this).fadeOut(100);
			jQuery('.header-top-banner').slideUp(1000);
		});
	}
	jQuery(document).ready(function(){top_banner();});


	$(window).scroll(function(){
	    var scroll = $(window).scrollTop();
	    //console.log(scroll);
		if (jQuery(window).width() > 979){
		    if(scroll>=200){
		    	$(".page-header").addClass("fixed");
		    	$(".header.content").addClass("fixed-header-style");
		    }
		    else{
		    	$(".page-header").removeClass("fixed");
		    	$(".header.content").removeClass("fixed-header-style");
		    }
		    //console.log(scroll);
		    // Do something
		}
		else{
			jQuery(".page-header").removeClass('fixed');
			jQuery(".header.content").removeClass("fixed-header-style");
		}
	});

});
  return; //return is optional I kept it to prevent unnecessery error occurance in future.
});//Define Ends here and So does Custom.js.