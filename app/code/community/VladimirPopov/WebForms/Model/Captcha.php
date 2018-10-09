<?php

class VladimirPopov_WebForms_Model_Captcha
    extends Mage_Core_Model_Abstract
{

    protected $_publicKey;
    protected $_privateKey;
    protected $_theme = 'standard';

    public function setPublicKey($value)
    {
        $this->_publicKey = $value;
    }

    public function setPrivateKey($value)
    {
        $this->_privateKey = $value;
    }

    public function setTheme($value)
    {
        $this->_theme = $value;
    }

    public function verify($response)
    {

        //Get user ip
        $ip = $_SERVER['REMOTE_ADDR'];

        //Build up the url
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $full_url = $url . '?secret=' . $this->_privateKey . '&response=' . $response . '&remoteip=' . $ip;

        //Get the response back decode the json
        $data = json_decode(file_get_contents($full_url));

        //Return true or false, based on users input
        if (isset($data->success) && $data->success == true) {
            return true;
        }

        return false;
    }

    public function getHtml()
    {
        $output = '';
        $rand = Mage::helper('webforms')->randomAlphaNum();
        if (!Mage::registry('webforms_recaptcha_gethtml')) {
            $output .= '<script>var reWidgets =[];</script>';
        }

        $output .= <<<HTML
<script>
    function recaptchaCallback{$rand}(response){
        $('re{$rand}').value = response;
        Validation.validate($('re{$rand}'));
        for(var i=0; i<reWidgets.length;i++){
            if(reWidgets[i].id != '{$rand}')
                grecaptcha.reset(reWidgets[i].inst);
        }
    }
    reWidgets.push({id:'{$rand}',inst : '',callback: recaptchaCallback{$rand}});

</script>
<div id="g-recaptcha{$rand}"></div>
<input type="hidden" id="re{$rand}" name="recapcha{$rand}" class="required-entry"/>
HTML;

        if (!Mage::registry('webforms_recaptcha_gethtml')) {
            $output .= <<<HTML
<script>
    function recaptchaOnload(){
        for(var i=0; i<reWidgets.length;i++){
            reWidgets[i].inst = grecaptcha.render('g-recaptcha'+reWidgets[i].id,{
                'sitekey' : '{$this->_publicKey}',
                'theme' : '{$this->_theme}',
                'callback': reWidgets[i].callback
            });
        }
    }
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit" async defer></script>
HTML;
        }
        if (!Mage::registry('webforms_recaptcha_gethtml')) Mage::register('webforms_recaptcha_gethtml', true);

        return $output;
    }
}
