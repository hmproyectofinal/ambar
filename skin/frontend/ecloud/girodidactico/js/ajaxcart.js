/*!
 * Theme Girodidactico
 * EcloudSolutions.
 */
var oldSetLocation = setLocation;
var setLocation = (function() {
    return function(url){
        if( url.search('checkout/cart/add') != -1 ) {
            //its simple/group/downloadable product
			if( url.search('has_qty') != -1){
				var $_btn = jQuery(document.activeElement),
				$_pr = $_btn.parents('.addtocart-wrapper').first();
				qty = jQuery('[name="qty"]',$_pr).first().val();
				if(qty != ''){
                    url += ('qty/' + qty+'/');
				}
			}
            AJAXCART.ajaxCartSubmit(url);
        } else if( url.search('checkout/cart/delete') != -1 ) {
            AJAXCART.ajaxCartSubmit(url);
        } else if( url.search('options=cart') != -1 ) {
            //its configurable/bundle product
            url += '&ajax=true';
            // AJAXCART.getConfigurableOptions(url);
        } else {
            oldSetLocation(url);
        }
    };
})();

var AJAXCART = {
    ajaxCartSubmit: function (obj) {
        var _this = this;
        try {
            if(typeof obj == 'string') {
                var url = obj;
                url = url.replace("checkout/cart", "ecloudtheme/cart"); // New Code
                url = url + 'isAjax/1';
                new Ajax.Request(url, {
                	method: 'POST',
                    onCreate: function() {
                        jQuery(document).trigger('ajaxCartBegin');
                    },
                    onSuccess	: function(response) {
                    	var data =  JSON.parse(response.responseText);
                    	_this.updateBlocks(data);
                		AJAXTHEME.showMessage(data.message, data.status);
                    },
                    onComplete: function(){
                        jQuery(document).trigger('ajaxCartFinish');
                    }
                });
            }
        } catch(e) {
            console.log(e);
            if(typeof obj == 'string') {
                window.location.href = obj;
            } else {
                document.location.reload(true);
            }
        }
    },

    reAssignLinksClick: function(){
        var skipContents = jQuery('#header-cart');
        var skipLinks    = jQuery('.skip-cart');
        skipLinks.on('click', function (e) {
            e.stopPropagation(); //Fix for closeSkipLinksOnDocumentClick();
            e.preventDefault();
            var self = jQuery(this);
            // Use the data-target-element attribute, if it exists. Fall back to href.
            var target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href');
            // Get target element
            var elem = jQuery(target);
            // Check if stub is open
            var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;
            // Hide all stubs
            skipLinks.removeClass('skip-active');
            skipContents.removeClass('skip-active');
            // Toggle stubs
            if (isSkipContentOpen) {
                self.removeClass('skip-active');
            } else {
                self.addClass('skip-active');
                elem.addClass('skip-active');
            }
        });
        jQuery('#header-cart').on('click', '.skip-link-close', function(e) {
            var parent  = jQuery(this).parents('.skip-content');
            var link 	= parent.siblings('.skip-link');
            parent.removeClass('skip-active');
            link.removeClass('skip-active');
            e.preventDefault();
        });
    },

    updateBlocks: function(data){
    	var _this = this
        if(data.status == 'ERROR'){
            alert(data.message);
        }else{
            if(jQuery('.block-cart')){
                jQuery('.header-top .header-minicart').html(data.sidebar);
            }
            if(jQuery('.header .links')){
                jQuery('.header .links').replaceWith(data.toplink);
            }
        }
        _this.reAssignLinksClick();
    },

    addSubmitEvent: function () {
		var _this = this;
        if(typeof productAddToCartForm != 'undefined') {
            productAddToCartForm.submit = function(url){
                if(this.validator && this.validator.validate()){
                    _this.ajaxCartSubmit(this);
                }
                return false;
            }

            productAddToCartForm.form.onsubmit = function() {
                productAddToCartForm.submit();
                return false;
            };
        }
		jQuery('.ajax-update-cart').each(function(){
			var $updateForm = jQuery(this);
			var formId = $updateForm.attr('id');
			var updateForm = new VarienForm(formId);
			updateForm.submit = function(url){
				 if(this.validator && this.validator.validate()){
					_this.ajaxCartSubmit(this);
				 }
                return false;
			}
			updateForm.form.onsubmit = function() {
                updateForm.submit();
                return false;
            };
		});
    }
};
