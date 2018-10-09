/*!
 * Theme Girodidactico
 * EcloudSolutions.
 */
jQuery(window).load(function(){
 	CATALOGVIEW.init();
});
jQuery(document).ready(function(){
 	HOME.init();
 	HEADER.init();
 	THEME.init();
 	FOOTER.init();
 	PDP.init();
 	CART.init();
 	CATEGORY.init();
 	CHECKOUT.init();
 	AJAXTHEME.init();
 	TABSHOME.init();
});
var TABSHOME = {
	init: function(){
		if (jQuery('body').hasClass('cms-home')) {
			this.makeCategoryTab();
			this.tabsFunctionality();
			this.tabsCarousel();
		}
	},
	makeCategoryTab: function(){
		var tabs_category_target = jQuery('.hometabs #target #tabs');
		var tabs_content_target  = jQuery('.hometabs #target #content');

		jQuery(".tabs_source li.category_tab").appendTo(tabs_category_target);
		jQuery(".tabs_source .category_content").appendTo(tabs_content_target);

	},
	tabsFunctionality: function(){
		//console.log('Funcionalidad tabs');
		//Funcionalidad de las tabs.
		jQuery('#tabs .category_tab').first().addClass('active');
		jQuery('.tabs-content .category_content').first().addClass('active');
		// Al hacer click en un tab cambian los productos del autor
		jQuery('#tabs li.category_tab').bind('click', function(el) {
			jQuery('#tabs .category_tab').removeClass('active');
			jQuery('.tabs-content .category_content').removeClass('active');

		    var activar  = jQuery(this).attr('target');
		    jQuery(activar).addClass('active');
		    jQuery(this).addClass('active');
		});
	},
	tabsCarousel: function(){
		jQuery('#content ul.category_content').owlCarousel({
			items: 4,
			dots: false,
			nav:true,
			loop:true,
			autoWidth:false,
			responsiveClass:true,
			autoplay:true,
		    autoplayTimeout:4000,
		    autoplayHoverPause:true,
		    responsive:{
		        0:{
		            items:1,
		            slideBy:2
		        },
		        600:{
		            items:2,
		           	slideBy:2
		        },
		        1000:{
		            items: 4
		        }
		    }
		});
	}
}

var HEADER = {
    init:function(){
        this.stickyHeader();
        this.mobile();  
        this.color();   
    },

    stickyHeader: function(){
    	function _setStickyifScrollTop(){
    		jQuery(document).ready(function(){
    			var st = jQuery(window).scrollTop();

    			if (st > 30) {
    				jQuery("#header").removeClass("sticky");
    				jQuery("#header").addClass("sticky");    				
    			}else {
    				jQuery("#header").removeClass("sticky");
    			}
    		});
    	}
    	_setStickyifScrollTop();

    	function _setScrollUpDown(){
    		var lastScrollTop = 0;
    		jQuery(window).scroll(function(event){
    			var st = jQuery(this).scrollTop();
    			if (st >= 180) {
    				jQuery("#header").addClass("sticky");
    				if (st > lastScrollTop){
                          // downscroll code
                          jQuery("body").removeClass("scrollUp");
                      } else {
                      	jQuery("body").addClass("scrollUp");
                      }
                  }else{
                  	jQuery("body").removeClass("scrollUp");
                  	jQuery("#header").removeClass("sticky");
                  }
                  lastScrollTop = st;
              });
    	}
    	_setScrollUpDown();
    },

    mobile: function(){
    	if (jQuery(window).width() < 992){
    		jQuery('.nav-links .top-links').insertAfter('#header-nav #nav');
		    jQuery(window).scroll(function () {
		        if (jQuery(this).scrollTop() > 50) {
		            jQuery(".header-top.container-fluid").addClass('sticky-mobile');
		        } else {
		            jQuery(".header-top.container-fluid").removeClass('sticky-mobile');
		        }
		    });
    	}
    },
    color: function(){
    	var nombre= location.pathname;	
    	var institucional= new String('/institucional/');
    	if(nombre==institucional){
    		jQuery('.second').addClass('activo');
    		jQuery('.first').removeClass('activo');
    	}
    	else{
    		jQuery('.second').removeClass('activo');
    		jQuery('.first').addClass('activo');
    	}
    },
}

var PDP = {
	init:function(){
		if(jQuery('body').hasClass('catalog-product-view')){
			this.onMobile();
			this.sliderPdp();
			this.moveCucardas();
		}
	},

	onMobile:function(){
		if (jQuery(window).width() < 992){
			jQuery('#image-main').remove();
			jQuery('.product-img-box .product-image-gallery').addClass('owl-carousel').owlCarousel({
				items: 1,
				singleItem: true
			});

			jQuery('.product-shop .product-name').insertBefore('.product-essential');
			jQuery('.product-shop .price-info').insertBefore('.product-essential');
		}
	},

	changeQty:function(increase) {
		var qty = parseInt($('qty').value);
		if ( !isNaN(qty) ) {
			qty = increase ? qty+1 : (qty>1 ? qty-1 : 1);
			$('qty').value = qty;
		};
	},

	sliderPdp: function(){
		jQuery('#upsell-product-table').owlCarousel({
			loop:true,
			margin:0,
			responsiveClass:true,
			dots:false,
			navs:true,
			responsive:{
				0:{
					items:1,
					nav:true
				},
				600:{
					items:2,
					nav:true
				},
				1000:{
					items:4,
					nav:true,
				}
			}
		});
		jQuery('#related-product-table').owlCarousel({
			loop:true,
			margin:0,
			responsiveClass:true,
			dots:false,
			navs:true,
			responsive:{
				0:{
					items:1,
					nav:true
				},
				600:{
					items:2,
					nav:true
				},
				1000:{
					items:4,
					nav:true,
				}
			}
		})
	},

	moveCucardas: function(){
		jQuery('.product-img-box .product-label.pl-top-right.sale').insertAfter('.product-image.product-image-zoom .product-image-gallery');
		jQuery('.product-img-box .product-label.pl-top-left.new').insertAfter('.product-image.product-image-zoom .product-image-gallery');
		jQuery('.product-img-box .product-label.pl-bottom-left.featured').insertAfter('.product-image.product-image-zoom .product-image-gallery');
	},
}

var FOOTER = {
	init:function(){
		this.mobile();
	},
	newsLeave: function(sender, defaultValue) {
	    if (jQuery.trim(sender.value) == '') sender.value = defaultValue;
	},
	newsSelect: function(sender, defaultValue) {
	    if (sender.value == defaultValue) sender.value = '';
	    else sender.select();
	},

	mobile: function(){
		if (jQuery(window).width() <= 991){
			jQuery('h2.botoncerrar').on('click',function(){
				jQuery(this).siblings(".contenido").toggle();
				jQuery(this).toggleClass('open');
			});
		}
	}
}

var HOME ={
	init: function(){
		if (jQuery('body').hasClass('cms-home')) {
			this.container();
			this.sliderPrincipal();
			this.sliderPrincipalMobile();
			this.sliderDos();
			this.sliderMarcas();
			this.instafeed();
			this.prodsCarrousel();
		};
		if(jQuery('body').hasClass('cms-institucional')){
			this.sliderMarcas();
			this.sliderPrincipal();
			this.sliderPrincipalMobile();
			this.instafeed();
		}
	},
	container:function(){
    	if (jQuery(window).width() > 1440){
    		if(!jQuery('.contenido,.container-slideshow').hasClass('container')){
    			jQuery('.contenido,.container-slideshow').addClass('container');
    		}else{
    			jQuery('.contenido,.container-slideshow').removeClass('container');
    		}
    	}
    },
	prodsCarrousel: function(){
		if (jQuery('.destacados #homeprods').length > 0){
			jQuery(".destacados #homeprods").owlCarousel({
				autoplay: true, // Boolean: Animate automatically, true or false
				// items: 3,
				singleItem: true,
				loop: true,
				dots:false,
				responsive: {
				  0     : { items: 1 },
				  479   : { items: 1 },
				  768   : { items: 2 },
				  991   : { items: 2 },
				  1024  : { items: 4 },
				  1200  : { items: 4 },
				},
			});
		}
	},
	slider: function(){
		jQuery(window).load(function(){
			if (jQuery('body').hasClass('cms-home')) {
				if (jQuery(window).width() > 991) {
					jQuery(".home-slider-desktop").owlCarousel({
						autoplay: true, // Boolean: Animate automatically, true or false
						items: 1,
						singleItem: true,
						loop: true,
					});
				}else{
					jQuery(".home-slider-mobile").owlCarousel({
						autoplay: true, // Boolean: Animate automatically, true or false
						items: 1,
						singleItem: true,
						loop: true,
					});
				}
			}
		})
	},
	sliderDos: function(){
		if (jQuery('#slider-dos').length > 0){
	    	jQuery('#slider-dos').owlCarousel({
		        loop:true,
		        margin:0,
		        nav:false,
		        responsiveClass:true,
		        responsive:{
		            0:{
		                items:1,
		            },
		            600:{
		                items:1,
		            },
		            1000:{
		                items:1,
		            }
		        }
	    	})
    	}
    },
	sliderMarcas: function(){
		if (jQuery('#marcas-slider').length > 0){
	    	jQuery('#marcas-slider').owlCarousel({
		        loop:true,
		        margin:0,
		        responsiveClass:true,
		        autoplay:true,
		        dots:false,
		        navs:true,
		        autoplayTimeout:5000,
		        autowidth: true,
		        responsive:{
		            0:{
		                items:2,
		                nav:true
		            },
		            600:{
		                items:3,
		                nav:true
		            },
		            1000:{
		                items:6,
		                nav:true,
		            }
		        }
	    	})
    	}	
    },
    sliderPrincipal: function(){
    	if (jQuery('#main-slider li').length > 1){
	    	jQuery('#main-slider').owlCarousel({
		        loop:true,
		        margin:0,
		        nav:false,
		        responsiveClass:true,
		        autoplay:true,
		        autoplayTimeout:4000,
		        responsive:{
		            0:{
		                items:1,
		                
		            },
		            600:{
		                items:1,
		                
		            },
		            1000:{
		                items:1,
		               
		            }
		        }
	    	})
    	}else{
    		jQuery('#main-slider').show();
    	}
    },

    sliderPrincipalMobile: function(){
    	if (jQuery('#main-slider-mobile li').length > 1){
	    	jQuery('#main-slider-mobile').owlCarousel({
		        loop:true,
		        margin:0,
		        nav:false,
		        responsiveClass:true,
		        autoplay:true,
		        autoplayTimeout:7000,
		        responsive:{
		            0:{
		                items:1,
		                
		            },
		            600:{
		                items:1,
		                
		            },
		            1000:{
		                items:1,
		               
		            }
		        }
	    	})
    	}else{
    		jQuery('#main-slider-mobile').show();
    	}
    },

    instafeed:function(){
    	if(jQuery('#instafeed').length > 0){
		    jQuery('#instafeed').html('');
			var userFeed = new Instafeed({
				get: 'user',
			  	clientId: '31bc4cad18be44d9aefa33c2cdbb8d69', //Client Id.
			  	userId: '688249175', //User ID.
			  	accessToken: '688249175.31bc4ca.669fbf7bb0664b8a841037034c3972d4', //ACCESSTOKEN.
			  	template: '<li class="instagram-item col-sm-3 col-md-3 col-xs-6"/><a target="_blank" href="{{link}}"><img src="{{image}}"/></a></li>',
			  	limit:4,
			  	resolution: 'standard_resolution'
			});
			userFeed.run();
		}
    },

}

var CART = {
	init:function(){
		if(jQuery('body').hasClass('checkout-cart-index')){
			this.CartOnMobile();
		 	this.sliderCart();
		}
	},
	CartOnMobile:function(){
		if (jQuery(window).width() < 599) {
			jQuery('.cart-review').insertAfter('#shopping-cart-table #cart-products-listing');
		}
	},
	sliderCart: function(){
		if (jQuery('#crosssell-products-list').length > 0){
	    	jQuery('#crosssell-products-list').owlCarousel({
		        loop:true,
		        margin:0,
		        responsiveClass:true,
		        dots:false,
		        navs:true,
		        responsive:{
		            0:{
		                items:1,
		                nav:true
		            },
		            600:{
		                items:2,
		                nav:true
		            },
		            1000:{
		                items:4,
		                nav:true,
		            }
		        }
	    	})
    	}	
    },
}

var CATEGORY = {
	init:function(){
		if (jQuery("body").hasClass("catalog-category-view") || jQuery("body").hasClass("catalogsearch-result-index") ) {
			this.despliegaFiltro();
			this.moveFilters();
			this.moveCategoryImage();
			this.stickyFilters();
			this.showfilters();
		}
	},

	
	despliegaFiltro: function(){
		// jQuery('.filter-title dt').on('click',function(){
			// console.log('entro');
			// if(!jQuery(this).parent().hasClass('active')){
			// 	jQuery(this).parent().addClass('active');
			// }else{
			// 	jQuery('.filter-title').removeClass('active');
			// }
		// });
		jQuery('#narrow-by-list dt').on('click',function(){

			jQuery(this).next('dd').find('ol').toggle();
			
			if(!jQuery(this).parent().hasClass('active')){
				// jQuery(this).parent().toggleClass('active');
				jQuery(this).parent().addClass('active');
			}else{
				jQuery(this).parent().removeClass('active');
			}

			jQuery('#narrow-by-list td').not(jQuery(this).parent()).removeClass('active');
			jQuery('#narrow-by-list td').not(jQuery(this).parent()).find('dt').removeClass('amshopby-collapsed');
		});
	},


	moveFilters: function(){
		jQuery(".block.block-layered-nav.amshopby-filters-top").insertBefore(".category-view > .category-products");
		jQuery(".toolbar").first().appendTo(".block.block-layered-nav.amshopby-filters-top .filter-buttons");
		if (jQuery('body').hasClass('catalogsearch-result-index')) {
			jQuery(".amshopby-filters-top").insertAfter(".page-title");
		};
	},
	moveCategoryImage: function(){
		jQuery(".category-image").insertBefore(".breadcrumbs");
	},
	stickyFilters: function(){
		if (jQuery(window).width() >= 992){
			jQuery(window).scroll(function () {
			      if (jQuery(this).scrollTop() > 450) {
			        jQuery(".amshopby-filters-top").addClass('sticky');
			    } else {
			        jQuery(".amshopby-filters-top").removeClass('sticky');
			    }
			});
		}
    	if (jQuery(window).width() < 992){
		    jQuery(window).scroll(function () {
		        if (jQuery(this).scrollTop() > 400) {
		            jQuery(".filtermobile").addClass("sticky");
		        } else {
		            jQuery(".filtermobile").removeClass("sticky");
		        }
		    });
    	}
	},
	showfilters: function(){
		if (jQuery(window).width() <= 991){
			jQuery('#narrow-by-list .filtermobile').on('click',function(){
				jQuery(this).parent().addClass('active');
			});
			jQuery('.toolbar').on('click',function(){
				jQuery(this).toggleClass("active");
			});
			jQuery('#narrow-by-list .cerrar').insertBefore('tbody');
			jQuery('.cerrar').click(function(){
				jQuery(this).parent().parent().removeClass('active');
			});
		}
	}

}

var THEME ={
	init:function(){
		this.closeSkipLinksOnDocumentClick();
		//this.newsletterDisclaimerPopups();
		this.fancybox();
	},
	newsletterDisclaimerPopups: function(){
		jQuery(window).load(function(){
			if (jQuery('body').hasClass('cms-home')) {
				if (Mage.Cookies.get('visited') != 'true') {
					var background_src = jQuery('#disclaimer-global .background-image img').attr('src');
					jQuery('.disclaimer-popup').css('background-image','url('+background_src+')');
					
					function _openDisclaimerPopup(){
						
						jQuery.fancybox({
							href:'#disclaimer-global',
							afterClose:function(){
								//console.log('close');
								setTimeout(function(){
									_openNewsletterPopupOnDisclaimerClose();	
								},500)
								
							}
						});

						jQuery('.dis-acc-btn').click(function(){
							jQuery.fancybox.close();
						});
					}
					_openDisclaimerPopup();

					function _openNewsletterPopupOnDisclaimerClose(){
						if (jQuery('#newsletterpopup').length) {
							jQuery.fancybox({
								href:'#newsletterpopup',
								wrapCSS: 'homepopup_suscribe'
							});

							jQuery('.homepopup_suscribe .more-information').click(function(){
								jQuery('.homepopup_suscribe .input-box.other_child_birth').toggle();
							})
						}
					}
					Mage.Cookies.set('visited',true);
				}
			}
		})
	},
	fancybox: function(){
		jQuery('.fancybox').click(function(){
			jQuery.fancybox({
				href:jQuery(this).attr('href'),
				wrapCSS: jQuery(this).attr('href').replace('#','')
			});
		})
	},
	closeSkipLinksOnDocumentClick: function(){
		jQuery('.skip-content').click(function(e){
			e.stopPropagation();
		});
		jQuery('body').click(function(e){
			jQuery('.skip-link.skip-active').removeClass('skip-active');
			jQuery('.skip-content.skip-active').removeClass('skip-active');
		});
	},
}

var AJAXFANCYBOX = {
    fancyAjaxSuccess: function(){
        jQuery.fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true,
            'href' : '#ajaxCartSuccess',
            'closeBtn' : false
        });
    },
    continueShopping: function() {
        jQuery.fancybox.close();
        parent.jQuery.fancybox.close();
    },
    goToCart: function(elem) {
        jQuery.fancybox.close();
        parent.jQuery.fancybox.close();
        window.top.location.href = jQuery(elem).attr('data-url');
    }
}

var AJAXTHEME = {
	init:function(){
		this.onPageLoad();
		this.onAjaxAddtoCart();
	},

	onPageLoad:function(){
		jQuery(window).ready(function(){
			setTimeout(function(){
				jQuery('.ajax_load').css('opacity',0);
				jQuery('.ajax_load').css('visibility','hidden');
			},1500)
		})
	},
	
	onAjaxAddtoCart: function(){
		jQuery(document).on('ajaxCartBegin',function(){
			var button = document.activeElement;
			jQuery(button).addClass('loading');
		})

		jQuery(document).on('ajaxCartFinish',function(){
			jQuery('.button').removeClass('loading');
		    AJAXFANCYBOX.fancyAjaxSuccess();
		})
	},

	showMessage:function(message,status){
		jQuery('.messages').remove();
	    if (status== "ERROR") {
	        var stat = "error";
	    } else {
	        var stat = "success";
	    }
	    var html = '<ul id="message-wrapper" class="messages"><li class="' + stat + '-msg"><ul><li><span>' + message + '</span></li></ul></li></ul>';
	    jQuery(".col-main" ).prepend(html);
	    jQuery('.messages').slideDown('400', function () {
	        setTimeout(function () {
	            jQuery('.messages').slideUp('400', function () {
	                jQuery(this).slideUp(400);
	                jQuery(this).remove();
	            });
	        }, 10000)
	        jQuery('#message-wrapper').fadeIn('slow');
	    });
	    function _gotoMessage(){
	    	wrapper_position = jQuery('#message-wrapper').offset().top;
	    	positiony = jQuery(document).scrollTop();
	    	if (positiony > wrapper_position) {
	    		jQuery('html, body').stop().animate({
	    			scrollTop: jQuery('#message-wrapper').offset().top -  60
	    		},1000);
	    	};
	    }
	    // _gotoMessage();
	}
}

var CHECKOUT = {
	init:function(){
		this.mobile();
	},
	mobile: function(){
		if(jQuery('body').hasClass('customer-account-create')){
			if (jQuery(window).width() <= 767){
				jQuery('.inchoo-socialconnect-register').insertAfter('.buttons-set');
			}
		}
	}
}

var CATALOGVIEW = {
	init: function(){
		if (jQuery('.catalog-category-view').length || jQuery('.catalogsearch-result-index').length) {
			this.inifiniteScrollRendered();
			this.onInfiniteScrollEnabled();
		}
	},

	inifiniteScrollRendered: function(){
		jQuery(document).on('ajaxStop',function(){
			//Integracion con infiniteScroll.
			window.ias.on('rendered', function(items){				
				jQuery(window).lazyLoadXT();
			});
		});
	},

	onInfiniteScrollEnabled: function(){
		jQuery(document).on('infiniteScrollReady', function(){
			jQuery('body').addClass('infinitescrollenabled');
		});
	}
}