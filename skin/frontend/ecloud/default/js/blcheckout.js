/*!
 * Theme Girodidactico
 * EcloudSolutions.
 */
jQuery(document).ready(function(){
	BLCHECKOUT.init();
});

var BLCHECKOUT = {
	init:function(){
		this.onLogin();
		this.transformSelectOnSquares();
		this.seDefaultOptions();
	},
	setRegisterOnLoad: function(){
		current_url = window.location.href;
		if(! current_url.includes('register')){
			current_url += '?register';
			window.location = current_url;
		}
		
	},
	onLogin: function(){
		jQuery('.login').click(function(){
			function _activateLogin(){
				jQuery('#opc-login .step-title').show();
				jQuery('#opc-login #checkout-step-login').show();
				jQuery('#opc-login').addClass('active');
			}
			_activateLogin();
			jQuery('#opc-billing').hide();
			
		})
	},
	transformSelectOnSquares: function(){
		function _hideSelect(){
			jQuery('#checkout-step-billing #billing-address-select').hide();
		}
		_hideSelect();
		function _makeSquareOption(){
			var option_square = '<div class="addres_square_options"></div>';
			jQuery(option_square).insertBefore('#billing-address-select');

			jQuery('#checkout-step-billing #billing-address-select option').each(function(){
				if (jQuery(this).val() != '') {
					var value = jQuery(this).val();
					var option_text 	= jQuery(this).text().split(",");
					var option    = '<div class="address-option"><input class="squareradio" id="#addressoption_'+value+'" type="radio" data-value="'+value+'"/><label for="#addressoption_'+value+'">';
					jQuery.each(option_text,function(i){
					   option += '<span>'+option_text[i]+'</span></br>';
					});
					option += '</label></div>';
				}
				jQuery(option).appendTo('.addres_square_options');

			})
			var add_direction = '<div class="address-option add-address"><input type="radio" class="hidden"/><label class="squareradio add-direction" data-value=""><span>Agregar otra dirección</span></label></div></div>';
			jQuery(add_direction).appendTo('.addres_square_options');
		}
		_makeSquareOption();

		function _onRadioClick(){
			jQuery('.squareradio').click(function(){
				jQuery('.squareradio').not(jQuery(this)).prop('checked',false);
				var selected_option = jQuery(this).attr('data-value');
				jQuery('#checkout-step-billing #billing-address-select option[value="'+selected_option+'"]').trigger('click');
				jQuery('#checkout-step-billing #billing-address-select').val(selected_option);
				if (selected_option == '') {
					billing.newAddress(1);
				}else{
					billing.newAddress(0);
				}
			})
		}
		_onRadioClick();
	},
	seDefaultOptions: function(){
		jQuery(window).load(function(){
			jQuery('.address-option').not('.add-address').first().find('label').trigger('click');
			jQuery("label[for='billing:use_for_shipping_yes']").trigger('click');	
			jQuery("label[for='billing:save_in_address_book']").trigger('click');	
			jQuery("label[for='shipping:same_as_billing']").trigger('click');	
			shipping.setSameAsBilling(true);
		});
	}
}
