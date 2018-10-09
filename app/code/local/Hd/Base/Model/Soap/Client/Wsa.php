<?php

//class Hd_Base_Model_Soap_Client_Wsa extends Zend_Soap_Client
class Hd_Base_Model_Soap_Client_Wsa extends SoapClient
{

    protected $_wsaObject;
    
    protected function _getWsa($params)
    {
        if (!$this->_wsaObject) {
            $this->_wsaObject = Mage::getModel('hd_base/soap_client_wsa_object',$params);
        }
        return $this->_wsaObject;
    }
    
    /**
     * @param type $request
     * @return \DOMDocument
     */
    protected function _getDom($request)
    {
        $dom = new DOMDocument();
        $dom->loadXML($request);
        return $dom;
    }

    function __doRequest($request, $location, $saction, $version)
    {
        
//Mage::log(func_get_args());

        $wsa = $this->_getWsa(array($dom));
        $wsa->addAction($saction)
            ->addTo($location)
            ->addMessageID()
            ->addReplyTo();

//        $dom = $wsa->getDoc();
//
//        $objWSSE = new WSSESoap($dom);
//        /* Sign all headers to include signing the WS-Addressing headers */
//        $objWSSE->signAllHeaders = TRUE;
//
//        $objWSSE->addTimestamp();
//
//        /* create new XMLSec Key using RSA SHA-1 and type is private key */
//        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
//
//        /* load the private key from file - last arg is bool if key in file (TRUE) or is string (FALSE) */
//        $objKey->loadKey(PRIVATE_KEY, TRUE);
//
//        /* Sign the message - also signs appropraite WS-Security items */
//        $objWSSE->signSoapDoc($objKey);
//
//        /* Add certificate (BinarySecurityToken) to the message and attach pointer to Signature */
//        $token = $objWSSE->addBinaryToken(file_get_contents(CERT_FILE));
//        $objWSSE->attachTokentoSig($token);
//
//        $request = $objWSSE->saveXML();
        
        return parent::__doRequest($request, $location, $saction, $version);
    }

}