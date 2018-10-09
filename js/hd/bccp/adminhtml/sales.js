// Fake Parent
AdminOrder.addMethods({

    // Keep Reference to original Method
    _parent_setPaymentMethod: AdminOrder.prototype.setPaymentMethod,
            
    hdBccpMethods: {},
    
    addHdBccpMethod: function(method)
    {
        this.hdBccpMethods[method] = true;
        return this;
    },
    
    isHdBccpMethod: function(method) 
    {
        return (this.hdBccpMethods[method] === true);
    },
    
    /**
     * @param {type} method
     * @returns {undefined}
     */
    setPaymentMethod : function(method) {
        if(!this.isHdBccpMethod(method)) {
            this._parent_setPaymentMethod(method);
            return;
        }
        // Custom Implementation
        if (this.paymentMethod && $('payment_form_'+this.paymentMethod)) {
            var form = 'payment_form_'+this.paymentMethod;
            [form + '_before', form, form + '_after'].each(function(el) {
                var block = $(el);
                if (block) {
                    block.hide();
                    block.select('input', 'select', 'textarea').each(function(field) {
                        field.disabled = true;
                    });
                }
            });
        }
        
        if(!this.paymentMethod || method){
            $('order-billing_method_form').select('input', 'select', 'textarea').each(function(elem){
                if(elem.type != 'radio') elem.disabled = true;
            })
        }

        if ($('payment_form_'+method)){
            this.paymentMethod = method;
            var form = 'payment_form_'+method;
            [form + '_before', form, form + '_after'].each(function(el) {
                var block = $(el);
                if (block) {
                   block.show();
                   block.select('input', 'select', 'textarea').each(function(field) {
                       field.disabled = false;
                       if (!el.include('_before') && !el.include('_after') && !field.bindChange) {
                           field.bindChange = true;
                           field.paymentContainer = form; /** @deprecated after 1.4.0.0-rc1 */
                           field.method = method;
                           // REMOVE onChange Observer
                           // field.observe('change', this.changePaymentData.bind(this));
                        }
                    },this);
                }
            },this);
        }
    },
    
    
});