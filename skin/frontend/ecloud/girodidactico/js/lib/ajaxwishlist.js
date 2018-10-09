function ajaxCompare(url,id){
	url = url.replace("catalog/product_compare/add","hellothemessettings/ajax/compare");
	url += 'isAjax/1/';
	jQuery.ajax( {
		url : url,
		dataType : 'json',
		success : function(data) {
			jQuery('#ajax_loading'+id).hide();
			if(data.status == 'ERROR'){
				showMessage(data.message,status); // Hellothemes improved
				gotoMessage();
			}else{
				updateCompareHeader(data.count);
				showMessage(data.message,status); // Hellothemes improved
				gotoMessage();
				if(jQuery('.block-compare').length){
                    jQuery('.block-compare').replaceWith(data.sidebar);
                }else{
                    if(jQuery('.col-right').length){
                    	jQuery('.col-right').prepend(data.sidebar);
                    }
                }
			}
		}
	});
}

function ajaxWishlist(url,id){
	url = url.replace("wishlist/index/add","hellothemessettings/ajax/wishlist");
	url += 'isAjax/1/';
	jQuery.ajax( {
		url : url,
		dataType : 'json',
		success : function(data) {
			jQuery('#ajax_loading'+id).hide();
			if(data.status == 'ERROR'){
				showMessage(data.message,status); // Hellothemes improved
				gotoMessage();
			}else{
				showMessage(data.message,status); // Hellothemes improved
				gotoMessage();
				if(jQuery('.block-wishlist').length){
                    jQuery('.block-wishlist').replaceWith(data.sidebar);
                }else{
                    if(jQuery('.col-right').length){
                    	jQuery('.col-right').prepend(data.sidebar);
                    }
                }
			}
		}
	});
}

function gotoMessage(){
	jQuery('html, body').stop().animate({scrollTop: jQuery('.messages').offset().top - 
				60},1000); 
}

function updateCompareHeader(count){
	jQuery(".header-compare span.count").text(count);
	if(count > 0){
		jQuery(".header-compare").show();
	}else{
		jQuery(".header-compare").hide();
	}
}
