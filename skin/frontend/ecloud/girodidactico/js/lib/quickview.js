$j(document).ready(function(){
    Quickview.fancyboxQuickview();
})

var Quickview ={
    fancyboxQuickview:function(){
      $j('.fancyqv').fancybox(
          {
             hideOnContentClick : true,
             width: 900,
             arrows: false,
             autoSize: false,
             type : 'iframe',
             showTitle: false,
             scrolling: 'no',
             fixed: false,
             wrapCSS: 'iframeWrap', //for simple selection pourposes
             onUpdate: function() {
               var offset = 25;
               var productHeight = $j(".iframeWrap iframe").contents().find(".product-view").outerHeight() + offset;
               var boxInner = $j(".iframeWrap .fancybox-inner");
               if (boxInner.height != productHeight) {
                 // Set the Height
                 boxInner.height(productHeight);
               }
             }
          }
      );
    },
    setAjaxData: function(data,iframe){
        if(data.status == 'ERROR'){
            alert(data.message);
        }else{
            if($j('.block-cart')){
                $j('.header-top .header-minicart').html(data.sidebar);
            }
            if($j('.header .links')){
                $j('.header .links').replaceWith(data.toplink);
            }
        }
    },
    reassignLinksClick: function(){
        var skipContents = jQuery('#header-cart');
        var skipLinks    = jQuery('.skip-cart');
        skipLinks.on('click', function (e) {
            e.stopPropagation(); //Fix for closeSkipLinksOnDocumentClick();
            e.preventDefault();
            var self = $j(this);
            // Use the data-target-element attribute, if it exists. Fall back to href.
            var target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href');
            // Get target element
            var elem = $j(target);
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
        $j('#header-cart').on('click', '.skip-link-close', function(e) {
            var parent = $j(this).parents('.skip-content');
            var link = parent.siblings('.skip-link');
            parent.removeClass('skip-active');
            link.removeClass('skip-active');
            e.preventDefault();
        });
    },
    setLocationAjax:function(url,id){
        url += 'isAjax/1';
        url = url.replace("checkout/cart","ajax/index");
        try {
            $j.ajax( {
                url : url,
                dataType : 'json',
                success : function(data) {
                    $j('#ajax_loader'+id).hide();
                    setAjaxData(data,false);
                }
            });
        } catch (e) {
        }
    }
}
