/*!
 * Theme Girodidactico
 * EcloudSolutions.
 */
jQuery(document).ready(function(){
 	HOME.init();
 	HEADER.init();
 	THEME.init();
 	FOOTER.init();
 	// PDP.init();
 	CART.init();
 	AJAXTHEME.init();
 	CATALOGVIEW.init();
});

var HEADER = {
	init:function(){
		this.mobile();
	},
	mobile: function(){
		if (jQuery(window).width() < 992){
			jQuery('.nav-links .top-links').insertAfter('#header-nav #nav'); 
		}
	}
}

var PDP = {
	init:function(){
		if(jQuery('body').hasClass('catalog-product-view')){
			this.onMobile();
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
	}
}

var FOOTER = {
	init:function(){
		this.mobile();
	},
	mobile: function(){
		if (jQuery(window).width() <= 991){
			jQuery('.footer-widget').on('click',function(){
				jQuery(this).toggleClass('active');
			});
		}
	}
}

var HOME ={
	init: function(){
		this.slider();
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
	}
}

var CART = {
	init:function(){
		if(jQuery('body').hasClass('checkout-cart-index')){
			this.CartOnMobile();
		}
	},
	CartOnMobile:function(){
		if (jQuery(window).width() < 599) {
			jQuery('.cart-review').insertAfter('#shopping-cart-table #cart-products-listing');
		}
	}
}

var THEME ={
	init:function(){
		this.closeSkipLinksOnDocumentClick();
		this.newsletterDisclaimerPopups();
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
	}
}

var AJAXTHEME = {
	init:function(){
		this.onPageLoad();
		this.onAjaxAddtoCart();
	},

	onPageLoad:function(){
		jQuery(window).load(function(){
			setTimeout(function(){
				jQuery('.ajax_load').css('opacity',0);
				jQuery('.ajax_load').css('visibility','hidden');
			},200)
		})
	},
	
	onAjaxAddtoCart: function(){
		jQuery(document).on('ajaxCartBegin',function(){
			var button = document.activeElement;
			jQuery(button).addClass('loading');
		})

		jQuery(document).on('ajaxCartFinish',function(){
			jQuery('.button').removeClass('loading');
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
	    _gotoMessage();
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
