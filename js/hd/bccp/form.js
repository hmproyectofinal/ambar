Hd = (typeof(Hd) === "object") ? Hd : {
    namespace: function (ns_string) {
        var parts = ns_string.split('.'),
            parent = Hd,
            i;
        if (parts[0] === "Hd") {
            parts = parts.slice(1);
        }
        for (i = 0; i < parts.length; i += 1) {
            if (typeof parent[parts[i]] === "undefined") {
                parent[parts[i]] = {};
            }
            parent = parent[parts[i]];
        }
        return parent;
    }
};

Hd.namespace('Bccp');

(function($){
    
    Hd.Bccp.Form = function(type, options) {
        
        var _defaults = {
            auto_select_on_single_option: true,
            auto_init: true,
            is_backend_checkout: false,
            current_method: false,
            elementIds: {
                cc: '_cc_id',
                bank: '_bank_id',
                payment: '_payments',
            },
            
            //@todo Add Support For Any Creditcard/Bank Order Selection 
            bcc: {
                order: 'ccb',
            },
            changeCcCallback: null,
            changeBankCallback: null,
            changePaymentCallback: null,
        };
        
        // Merge Config
        var _config = $.extend(true, _defaults, options);
        
        var _this = this;
        
        var _type = type;
        
        var _code = _config.code;
        
        var _data = null;
        
        var _paymentsData = {};
        
        var _getCcSelect = function()
        {
            return $('#' + _code + _config.elementIds.cc);
        };
        
        var _getBankSelect = function()
        {
            return $('#' + _code + _config.elementIds.bank);
        };
        
        var _getPaymentSelect = function()
        {
            return $('#' + _code + _config.elementIds.payment);
        };
        
        /**********************************************************************/
        /**************************************************** EVENT METHODS ***/
        /**********************************************************************/

        var _ccSelectChange = function(obj, event) 
        {
            var _val = obj.val();
            // Case - Empty Selection
            if(_val === '') {
                // Reset
                _resetSelect(_getPaymentSelect());
                if(_type === 'bcc' && _config.bcc.order === 'ccb') {
                    _resetSelect(_getBankSelect());
                }
                return;
            }
            // Case - Valid Selection
            if(_type === 'bcc' && _config.bcc.order === 'ccb') {
                _buildBankSelect(_val);
            } else {
                _loadPayments(_val,_getBankSelect().val());
            }
            // Callback
            if (typeof _config.changeCcCallback === 'function') {
                _config.changeCcCallback(_getCallbackParams());
            }
            return;
        };
        
        
        var _bankSelectChange = function(obj, event) 
        {
            var _val = obj.val();
            // Case - Empty Selection
            if(_val === '') {
                // Reset
                _resetSelect(_getPaymentSelect());
                if(_config.bcc.order === 'bcc') {
                    _resetSelect(_getCcSelect());
                }
                return;
            }
            // Case - Valid Selection
            if(_config.bcc.order === 'ccb') {
                // Selects Build
                _loadPayments(_getCcSelect().val(), _val);
            } else {
                _buildCcSelect(_val);
                _resetSelect(_getPaymentSelect());
            }                
            // Callback
            if (typeof _config.changeBankCallback === 'function') {
                _config.changeBankCallback(_getCallbackParams());
            }
            return;
        };
        
        var _paymentSelectChange = function(obj, event) 
        {
            var _val = obj.val();
            if(_val === '') {
                return;
            }
            var _pData = _paymentsData[_val];
            if(_pData === undefined) {
                return;
            }
            $('#' + _code + '_cc_payment_id').val(_pData.payment_id);
            $('#' + _code + '_gateway_cc_code').val(_pData.gateway_cc_code);
            
            // Common Data
            if(_type === 'bcc') {
                $('#' + _code + '_gateway_bank_code').val(_pData.gateway_bank_code);
                $('#' + _code + '_gateway_merchant_code').val(_pData.gateway_merchant_code);
                $('#' + _code + '_gateway_promo_code').val(_pData.gateway_promo_code);
            }
            
            // Admin CallBack
            if(_config.is_backend_checkout) {
                setTimeout(function() {
                    var _savePaymentParams = window.order.prepareParams();
                    window.order.loadArea(['totals'], true, _savePaymentParams);
                },100);
            }
            
            // Callback
            if (typeof _config.changePaymentCallback === 'function') {
                _config.changePaymentCallback(_getCallbackParams());
            }
            return;
        };
        
        var _getCallbackParams = function()
        {
            var params = {
                controls: {
                    cc: _getCcSelect(),
                    bank: _getBankSelect(),
                    payment: _getPaymentSelect(),
                    form: _this,
                },
                config: _config,
                payments_data: _paymentsData,
            };
            return params;
        };
        
        /**********************************************************************/
        /************************************************ AJAX LOAD METHODS ***/
        /**********************************************************************/
        
        /**
         * Common Ajax Json Loader
         * 
         * @param {String} url
         * @param {Object} params
         * @param {Funtion} sCallback | Success Callback Function
         * @param {Funtion} eCallback | Error Callback Function
         * 
         */
        var _loadJson = function(url, params, sCallback, eCallback)
        {
            $.ajax({
                url: url,
                dataType: 'json',
                type: 'POST',
                data: params,
                success: function(response) {
                    if (sCallback === 'undefined') {
                        // OK..
                    } else {
                        sCallback(response);
                    }
                    return false;
                },
                error: function(a,b,c,d) {
                    if (eCallback === 'undefined') {
                        _defaultErrorHandler(a,b,c,d)
                    } else {
                        eCallback(a,b,c,d);
                    }
                    return false;
                }
            });
        };
        
        /**
         * Default Json Ajax Error Handler
         * @param {type} error
         */
        var _defaultErrorHandler = function(a,b,c,d)
        {
            if(console !== undefined) {
                console.log(a);
                console.log(b);
                console.log(c);
                console.log(d);
            }
        };
                
        var _loadPayments = function(ccId, bankId)
        {
            // Set Loading
            _resetSelect(_getPaymentSelect(), _translate('Loading Payments ...'));
            // Disable
            _getPaymentSelect().prop('disabled','disabled');
            // Prepare Params
            var _params = {cc_id: ccId,method: _code};
            
            if(_type === 'bcc') {
                _params.bank_id = bankId;
            }
            if(_config.product_id) {
                _params.product_id = _config.product_id;
            }
            if(_config.is_backend_checkout) {
                _params.form_key = FORM_KEY;
            }
            // Load Data
            _loadJson(
                _config.payments_url,
                _params,
                function(response) {
                    _loadPaymentsHandler(response, _params)
                }
            );
            return;
        };
        
        var _loadPaymentsHandler = function(response, params) 
        {
            if (response.status !== undefined && response.status === "ok") {
                // Save Data
                _paymentsData = response.result.data;
                // Build Select 
                _buildPaymentSelect(_paymentsData.options);
            } else if (response.status === "error") {
                _defaultErrorHandler(response.error.message);
            }
            // Enable
            _getPaymentSelect().prop('disabled',false);
            return;
        };
        
        /**********************************************************************/
        /**************************************************** BUILD METHODS ***/
        /**********************************************************************/
        
        var _buildCcSelect = function(bankId)
        {
            // Reset
            _resetSelect(_getPaymentSelect());
            if(_type === 'bcc' && _config.bcc.order === 'ccb') {
                _resetSelect(_getBankSelect());
            }
            
            // Options By Case
            var _ccOptions = (_type === 'bcc' && _config.bcc.order === 'bcc') 
                ? _config.banks[bankId].creditcards.options
                : _config.creditcards.options;
            
            // Build
            var html =_buildSelectOptions(_ccOptions, _translate('Select Credit Card'));
            // Append
            _getCcSelect().empty().append(html);
            // Auto Select First Option
            _autoSelect(_getCcSelect());
            
            // load from Data
            if(_data !== null) {
                _getCcSelect().val(_data.hd_bccp_cc_id);
                _getCcSelect().trigger('change');
            }
            
            return _this;
        };
        
        var _buildBankSelect = function(ccId)
        {
            // Reset
            _resetSelect(_getPaymentSelect());
            if(_type === 'bcc' && _config.bcc.order === 'bcc') {
                _resetSelect(_getCcSelect());
            }
            
            // Options By Case
            var _bankOptions = (_type === 'bcc' && _config.bcc.order === 'bcc') 
                ? _config.banks.options
                : _config.creditcards[ccId].banks.options;            
            
            // Build
            var html =_buildSelectOptions(_bankOptions, _translate('Select Bank'));
            // Append
            _getBankSelect().empty().append(html);
            // Auto Select First Option
            _autoSelect(_getBankSelect());
            
            // load from Data
            if(_data !== null) {
                _getBankSelect().val(_data.hd_bccp_bank_id);
                _getBankSelect().trigger('change');
            }
            return _this;
        };
        
        var _buildPaymentSelect = function(options)
        {
            // Build
            var html =_buildSelectOptions(options, _translate('Select Payment Plan'));
            // Append
            _getPaymentSelect().empty().append(html);
            // Auto Select First Option
            _autoSelect(_getPaymentSelect());
            // load from Data
            if(_data !== null) {
                _getPaymentSelect().val(_data.hd_bccp_payments);
                _getPaymentSelect().trigger('change');
                _data = null;
            }
            return _this;
        };
        
        /**********************************************************************/
        /************************************************* INTERNAL METHODS ***/
        /**********************************************************************/
        
        /**
         * Translate a String
         * 
         * @param {String} String
         * @returns {String} Translated String 
         */
        var _translate = function(string)
        {
            return (_config.translate[string] !== undefined) 
                ? _config.translate[string]
                : string;
        };
        
        /**
         * Eval if select have just ONE option an select it
         * 
         * @param {jQuery Object} selectObj
         */
        var _autoSelect = function(selectObj)
        {
            if(!_config.auto_select_on_single_option) {
                return _this;
            }
            if (selectObj.children('option').size() < 2) {
                selectObj.trigger("change");
            }
        };
        
        /**
         * Reset HTML Select Options
         * 
         * @param {jQueryObject} obj
         * @returns {Hd.Bccp}
         */
        var _resetSelect = function(obj, label)
        {
            obj.empty().append(
                _buildOption({value:'', label: (label!== undefined) ? label : ''})
            );
        };
        
        /**
         * Create HTML Select Options
         * 
         * @param {obj} options
         * @param {String} firstOptionText
         * @returns {String}
         */
        var _buildSelectOptions = function(options, firstOptionText)
        {
            var html = '';
            var _count = 0;
            $.each(options, function(k,v) {
                html += _buildOption(v);
                _count++;
            });
            if (_count > 1 ) {
                html = _buildOption({value:'', label:firstOptionText}, true) + html;
            }
            return html;
        };
        
        /**
         * Create HTML Select Option
         * 
         * @param {Object} obj
         * @param {Boolean} selected Flag
         * @returns {String}
         */
        var _buildOption = function(obj, selected)
        {
            var _selected = "";
            if(selected === true) {
                _selected = ' selected="selected"';
            }
            return '<option value="' + obj.value + '"'+ _selected + '>' + obj.label + '</option>\n'
        };
        
       
        _this.init = function()
        {
//            console.log(_config);
            
            // Bind Events
            _getCcSelect().change(function(event){
                _ccSelectChange($(this),event);
            });
            
            _getPaymentSelect().change(function(event){
                _paymentSelectChange($(this),event);
            });
            
            if(_type === 'bcc') {
                _getBankSelect().change(function(event){
                    _bankSelectChange($(this),event);
                });
            }
            
            // Restore Selection
            if(typeof _config.current_method == 'object') {
                if(_config.current_method.method == _code) {
                    _data = _config.current_method;
                }
            }
            
            // Init By CC
            if(_type === 'bcc' && _config.bcc.order === 'bcc') {
                _buildBankSelect();
            } else {
                _buildCcSelect();
            }
            
        };
        
        if(_config.auto_init) {
            _this.init();
        }
        
        return _this;
        
    };
    
    Hd.Bccp.FormCc = function(options) {
        return new Hd.Bccp.Form('cc', options);
    };
    
    Hd.Bccp.FormBcc = function(options) {
        return new Hd.Bccp.Form('bcc', options);
    };
    
    // jQuery Extend
    $.fn.serializeObject = function() {
        var arrayData, objectData;
        arrayData = this.serializeArray();
        objectData = {};

        $.each(arrayData, function() {
            var value;

            if (this.value != null) {
                value = this.value;
            } else {
                value = '';
            }

            if (objectData[this.name] != null) {
                if (!objectData[this.name].push) {
                    objectData[this.name] = [objectData[this.name]];
                }

                objectData[this.name].push(value);
            } else {
                objectData[this.name] = value;
            }
        });        
        return objectData;
    };
    
    
}(jQuery));  



