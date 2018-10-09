<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2014 Vladimir Popov
 */
class VladimirPopov_WebForms_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    const DKEY = 'WF1DM';
    const SKEY = 'WFSRV';

    public function getRealIp()
    {
        $ip = false;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);

            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }

            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
                    if (version_compare(phpversion(), "5.0.0", ">=")) {
                        if (ip2long($ips[$i]) != false) {
                            $ip = $ips[$i];
                            break;
                        }
                    } else {
                        if (ip2long($ips[$i]) != -1) {
                            $ip = $ips[$i];
                            break;
                        }
                    }
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    public function captchaAvailable()
    {
        if (Mage::getStoreConfig('webforms/captcha/public_key') && Mage::getStoreConfig('webforms/captcha/private_key'))
            return true;
        return false;
    }

    public function getCaptcha()
    {
        $pubKey = Mage::getStoreConfig('webforms/captcha/public_key');
        $privKey = Mage::getStoreConfig('webforms/captcha/private_key');

        $recaptcha = false;

        if ($pubKey && $privKey) {
            $recaptcha = Mage::getModel('webforms/captcha');
            $recaptcha->setPublicKey($pubKey);
            $recaptcha->setPrivateKey($privKey);
            $recaptcha->setTheme(Mage::getStoreConfig('webforms/captcha/theme'));
        }
        return $recaptcha;
    }

    public function getMageEdition()
    {
        if (method_exists('Mage', 'getEdition')) {
            switch (Mage::getEdition()) {
                case Mage::EDITION_COMMUNITY:
                    return 'CE';
                case Mage::EDITION_ENTERPRISE:
                    return 'EE';
                case Mage::EDITION_GO:
                    return 'GO';
                case Mage::EDITION_PROFESSIONAL:
                    return 'PRO';
            }
        }

        $version = explode('.', Mage::getVersion());

        if ($version[1] >= 9)
            return 'EE';

        return 'CE';
    }

    public function getMageSubversion()
    {
        $version = explode('.', Mage::getVersion());
        if (!empty($version[1])) return $version[1];
        return false;
    }

    public function htmlCut($text, $max_length)
    {
        $tags = array();
        $result = "";

        $is_open = false;
        $grab_open = false;
        $is_close = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";

        $i = 0;
        $stripped = 0;

        $stripped_text = strip_tags($text);

        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length) {
            $symbol = $text{$i};
            $result .= $symbol;

            switch ($symbol) {
                case '<':
                    $is_open = true;
                    $grab_open = true;
                    break;

                case '"':
                    if ($in_double_quotes)
                        $in_double_quotes = false;
                    else
                        $in_double_quotes = true;

                    break;

                case "'":
                    if ($in_single_quotes)
                        $in_single_quotes = false;
                    else
                        $in_single_quotes = true;

                    break;

                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes) {
                        $is_close = true;
                        $is_open = false;
                        $grab_open = false;
                    }

                    break;

                case ' ':
                    if ($is_open)
                        $grab_open = false;
                    else
                        $stripped++;

                    break;

                case '>':
                    if ($is_open) {
                        $is_open = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    } else if ($is_close) {
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }

                    break;

                default:
                    if ($grab_open || $is_close)
                        $tag .= $symbol;

                    if (!$is_open && !$is_close)
                        $stripped++;
            }

            $i++;
        }

        while ($tags)
            $result .= "</" . array_pop($tags) . ">";

        return $result;
    }

    public function addAssets(Mage_Core_Model_Layout $layout)
    {
        $head = $layout->getBlock('head');
        $content = $layout->getBlock('content');

        if ($head && $content) {

            $head->addCss('webforms/form.css', 'webforms');
            $head->addJs('prototype/window.js', 'webforms');
            $head->addItem('js_css', 'prototype/windows/themes/default.css', 'webforms');
            $head->addItem('js_css', 'prototype/windows/themes/alphacube.css', 'webforms');

            // logic
            $head->addJs('webforms/logic.js', 'webforms');

            // multistep
            $head->addJs('webforms/multistep.js', 'webforms');

            // stars
            $head->addJs('webforms/stars.js', 'webforms');
            $head->addCss('webforms/stars.css', 'webforms');

            // tooltips
            $head->addJs('webforms/opentip/opentip-native-excanvas.min.js', 'webforms');
            $head->addItem('js_css', 'webforms/opentip/opentip.css', 'webforms');

            // wysiwyg
            $head->addJs('tiny_mce/tiny_mce.js', 'tiny-mce');

            // calendar
            $head->addJs('calendar/calendar.js', 'webforms');
            $head->addJs('calendar/calendar-setup.js', 'webforms');
            $head->addItem('js_css', 'calendar/calendar-blue.css', 'webforms');

            // ajax file uploader
            if (Mage::getStoreConfig('webforms/files/ajax')) {
                if (Mage::getStoreConfig('webforms/files/load_jquery')) {
                    $head->addJs('webforms/jQuery/jquery-1.11.1.min.js', 'webforms');
                    $head->addJs('webforms/jQuery/no-conflict.js', 'webforms');
                }
                $head->addJs('webforms/blueimp/js/vendor/jquery.ui.widget.js', 'webforms');
                $head->addJs('webforms/blueimp/js/jquery.iframe-transport.js', 'webforms');
                $head->addJs('webforms/blueimp/js/jquery.fileupload.js', 'webforms');
                $head->addCss('webforms/file-upload.css', 'webforms');
            }
        }

        if (in_array('cms_page', $layout->getUpdate()->getHandles()) || in_array('webforms_index_index', $layout->getUpdate()->getHandles())) {
            if (!$this->isProduction()) {
                Mage::getSingleton('core/session')->addError($this->getNote());
            }
        }

        // add custom assets
        Mage::dispatchEvent('webforms_add_assets', array('layout' => $layout));

        return $this;
    }

    protected function getDomain($url)
    {
        $url = str_replace(array('http://', 'https://', '/'), '', $url);
        $tmp = explode('.', $url);
        $cnt = count($tmp);

        if(empty($tmp[$cnt - 2]) || empty($tmp[$cnt - 1])) return $url;
        
        $suffix = $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];

        $exceptions = array(
            'com.au', 'com.br', 'com.bz', 'com.ve', 'com.gp',
            'com.ge', 'com.eg', 'com.es', 'com.ye', 'com.kz',
            'com.cm', 'net.cm', 'com.cy', 'com.co', 'com.km',
            'com.lv', 'com.my', 'com.mt', 'com.pl', 'com.ro',
            'com.sa', 'com.sg', 'com.tr', 'com.ua', 'com.hr',
            'com.ee', 'ltd.uk', 'me.uk', 'net.uk', 'org.uk',
            'plc.uk', 'co.uk', 'co.nz', 'co.za', 'co.il',
            'co.jp', 'ne.jp', 'net.au', 'com.ar'
        );

        if (in_array($suffix, $exceptions))
            return $tmp[$cnt - 3] . '.' . $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];

        return $suffix;
    }

    public function verify($domain, $checkstr)
    {

        if ("wf" . substr(sha1(self::DKEY . $domain), 0, 18) == substr($checkstr, 0, 20)) {
            return true;
        }

        if ("wf" . substr(sha1(self::SKEY . $_SERVER['SERVER_ADDR']), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        if ("wf" . substr(sha1(self::SKEY . gethostbyname($_SERVER['SERVER_NAME'])), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        if ("wf" . substr(sha1(self::SKEY . gethostbyname($domain)), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        $base = $this->getDomain(parse_url(Mage::app()->getStore(0)->getConfig('web/unsecure/base_url'), PHP_URL_HOST));
        if ("wf" . substr(sha1(self::SKEY . gethostbyname($base)), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        if (substr(sha1(self::SKEY . $base), 0, 8) == substr($checkstr, 12, 8))
            return true;

        if ($this->verifyIpMask(array($_SERVER['SERVER_ADDR'], $_SERVER['SERVER_NAME'], $domain, $base), $checkstr)) {
            return true;
        }
        return false;
    }

    private function verifyIpMask($data, $checkstr)
    {
        if (!is_array($data)) {
            $data = array($data);
        }
        foreach ($data as $name) {
            $ipdata = explode('.', gethostbyname($name));
            if (isset($ipdata[3])) $ipdata[3] = '*';
            $mask = implode('.', $ipdata);
            if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
            if (isset($ipdata[2])) $ipdata[2] = '*';
            $mask = implode('.', $ipdata);
            if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }
        return false;
    }

    public function isProduction(){
        $domain = $this->getDomain($_SERVER['SERVER_NAME']);

        return substr($domain,-6) == '.com' || substr($domain,-4) == '.com' || $_SERVER['SERVER_NAME'] == 'localhost';
    }

    public function isLocal(){
       $domain = $this->getDomain($_SERVER['SERVER_NAME']);

       return substr($domain,-6) == '.com' || substr($domain,-4) == '.com' || $_SERVER['SERVER_NAME'] == 'localhost';
    }

    public function getNote()
    {
        if (Mage::getStoreConfig('webforms/license/serial')) {
            return $this->__('WebForms Professional Edition license number is not valid for store domain.');
        }
        return $this->__('License serial number for WebForms Professional Edition is missing.');
    }

    public function randomAlphaNum($length = 6)
    {
        $rangeMin = pow(36, $length - 1); //smallest number to give length digits in base 36
        $rangeMax = pow(36, $length) - 1; //largest number to give length digits in base 36

        $base10Rand = @mt_rand($rangeMin, $rangeMax); //get the random number
        if (!$base10Rand)
            $base10Rand = @mt_rand($rangeMax, $rangeMin);
        $newRand = base_convert($base10Rand, 10, 36); //convert it

        return $newRand; //spit it out
    }

    public function getVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->VladimirPopov_WebForms->version;
    }
}