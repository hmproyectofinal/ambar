<?php
class Hd_Base_Model_Soap_Client extends Mage_Core_Model_Abstract
{
    
    /**
     * Config path node to wsdl url configuration (must be implemented in child class)
     * @var string 
     */
    protected $_xmlConfigPathSoapWsdlUrl;
    
    /**
     * Config path node to wsdl client options (must be implemented in child class)
     * @var string 
     */
    protected $_xmlConfigPathSoapClientOptions;
    
    /**
     * Soap Request log file
     * @var string 
     */
    protected $_requestLogFile = 'soap-request.log';
    
    /**
     * Soap Response log file
     * @var string 
     */
    protected $_responseLogFile = 'soap-response.log';
    
    /**
     * Generl log file
     * @var string 
     */
    protected $_generalLogFile = 'soap-general.log';
    
    protected $_logColors = array(
        'req_header' => "\033[1;92m",
        'req_body' => "\033[0;92m",
        'res_header' => "\033[1;33m",
        'res_body' => "\033[0;93m",
        'reset' => "\033[0m",
    );
    
    /**
     * @var Zend_Soap_Client | Zend_Soap_Client_DotNet | Zend_Soap_Client_Local
     * 
     * @see http://framework.zend.com/manual/1.12/en/zend.soap.client.html
     */
    protected $_soapClient;
    
    /**
     * Soap Client Tipe
     * 
     * Available Types:
     * 'standar':        SoapClient
     * 'standar-wcf':    SoapClient
     * 'zend-standard':  Zend_Soap_Client
     * 'zend-local':     Zend_Soap_Client_Local
     * 'zend-dot-net':   Zend_Soap_Client_DotNet
     *  
     * @var string
     * @see http://framework.zend.com/manual/1.12/en/zend.soap.client.html
     * 
     */
    protected $_soapClientType = 'standard';
    
    public function isStandard()
    {
        return ($this->_getClient() instanceof SoapClient);
    }
    
    /**
     * Returns the xml Request Body & Header as string
     * 
     * @param bool $forHtml
     * @param bool $formated
     * @return string
     */
    public function getRequestString($forHtml = false, $formated = true)
    {
        $headers = ($this->isStandard()) ? $this->_getClient()->__getLastRequestHeaders()
            : $this->_getClient()->getLastRequestHeaders();
        
        $xml = ($this->isStandard()) ? $this->_getClient()->__getLastRequest()
            : $this->_getClient()->getLastRequest();
                
        $result = str_repeat('-', 70);
        $result .= $this->_logColors['req_header'];
        $result .= "\nREQUEST HEADER:\n";
        $result .= (!$formated) ? $headers : $this->xmlFormat($headers);
        $result .= $this->_logColors['req_body'];
        $result .= "\n\nREQUEST BODY:\n";
        $result .= (!$formated) ? $xml : $this->xmlFormat($xml);
        $result .= $this->_logColors['reset'];
        
        if($forHtml) {
            $result = nl2br($result);
        }
        return $result;
    }
    

    /**
     * Returns the xml Response Body & Header as string
     * 
     * @param bool $forHtml
     * @param bool $formated
     * @return string
     */
    public function getResponseString($forHtml = false, $formated = true)
    {
        $headers = ($this->isStandard()) ? $this->_getClient()->__getLastResponseHeaders()
            : $this->_getClient()->getLastResponseHeaders();
        
        $xml = ($this->isStandard()) ? $this->_getClient()->__getLastResponse()
            : $this->_getClient()->getLastResponse();
                
        $result = str_repeat('-', 70);
        $result .= $this->_logColors['res_header'];
        $result .= "\nRESPONSE HEADER:\n";
        $result .= (!$formated) ? $headers : $this->xmlFormat($headers);
        $result .= $this->_logColors['res_body'];
        $result .= "\n\nRESPONSE BODY:\n";
        $result .= (!$formated) ? $xml : $this->xmlFormat($xml);
        $result .= $this->_logColors['reset'];        
        
        if($forHtml) {
            $result = nl2br($result);
        }
        return $result;
    }
    
    /**
     * Retuns a nice Formated XML String
     *  
     * @param string $xmlString
     * @return string
     */
    public function xmlFormat($xmlString)
    {
        $outputString = "";
        $previousBitIsCloseTag = false;
        $indentLevel = 0;
        $bits = explode("<", $xmlString);
        foreach ($bits as $bit) {
            $bit = trim($bit);
            if (!empty($bit)) {
                if ($bit[0] == "/") {
                    $isCloseTag = true;
                } else {
                    $isCloseTag = false;
                }
                if (strstr($bit, "/>")) {
                    $prefix = "\n" . str_repeat("  ", $indentLevel);
                    $previousBitIsSimplifiedTag = true;
                } else {
                    if (!$previousBitIsCloseTag and $isCloseTag) {
                        if ($previousBitIsSimplifiedTag) {
                            $indentLevel--;
                            $prefix = "\n" . str_repeat(" ", $indentLevel);
                        } else {
                            $prefix = "";
                            $indentLevel--;
                        }
                    }
                    if ($previousBitIsCloseTag and !$isCloseTag) {
                        $prefix = "\n" . str_repeat("  ", $indentLevel);
                        $indentLevel++;
                    }
                    if ($previousBitIsCloseTag and $isCloseTag) {
                        $indentLevel--;
                        $prefix = "\n" . str_repeat("  ", $indentLevel);
                    }
                    if (!$previousBitIsCloseTag and !$isCloseTag) { {
                            $prefix = "\n" . str_repeat("  ", $indentLevel);
                            $indentLevel++;
                        }
                    }
                    $previousBitIsSimplifiedTag = false;
                }
                $outputString .= $prefix . "<" . $bit;
                $previousBitIsCloseTag = $isCloseTag;
            }
        }
        return $outputString;
    }
    
    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return Mage::getIsDeveloperMode(); 
    }
    
    /**
     * Performs a Generic Soap Call
     *  
     * @param string $method 
     * @param type $params
     * @return type
     */
    public function call($method,$params)
    {
        $result = null;
        try {
            
            $result = call_user_func(array($this->_getClient(),$method),$params);
            
            // Parser Comun
            $result = $this->_parseSoapResponse($result);
            
            // Log
            $this->_logRequest()
                ->_logResponse();
            
        } catch (SoapFault $e) {
            $this->_logSoapFault($e);
        } catch (Zend_Soap_Client_Exception $e) {
            $this->_logSoapException($e);
        } catch (Exception $e) {
            $this->_logException($e);
        }
        return $result;
    }
    
    /**
     * Common Parser
     * 
     * @param mixed $response
     * @return mixed
     */
    protected function _parseSoapResponse($response)
    {
        return $response;
    }
    
    /**
     * Returns wsdl configured Url
     * @return string
     */
    protected function _getWsdlUrl()
    {
        $url = Mage::getStoreConfig($this->_xmlConfigPathSoapWsdlUrl);
        if (!$url) {
            Mage::throwException($this->_helper()->__('WSDL no declarado.'));
        }
        return $url;
    }
    
    /**
     * Return an array with soap client configured options
     * @return array
     */
    protected function _getSoapClientOptions()
    {
        $options = Mage::getStoreConfig($this->_xmlConfigPathSoapClientOptions);
        return (is_array($options)) ? $options : array();
    }
    
    /**
     * Returns the soap client type
     * @return string
     */
    protected function _getSoapClientType()
    {
        return $this->_soapClientType;
    }
    
    /**
     * Returns the request log file name
     * @return string
     */
    protected function _getRequestLogFile()
    {
        return $this->_requestLogFile;
    }
    
    /**
     * Returns the response log file name
     * @return string
     */
    protected function _getResponseLogFile()
    {
        return $this->_responseLogFile;
    }
    
    /**
     * Returns the general log file name
     * @return string
     */
    protected function _getGeneralLogFile()
    {
        return $this->_generalLogFile;
    }            
    
    /**
     * Instanciate & Returns the SoapClient Object
     * 
     * @return SoapClient | Zend_Soap_Client
     * @throws Exception
     */
    protected function _getClient()
    {
        if (!$this->_soapClient) {
            
            $wsdl   = $this->_getWsdlUrl();
            $config = $this->_getSoapClientOptions();
            $type   = $this->_getSoapClientType();
            
            switch($type) {
                case 'standard':
                    // Standard Client 
                    if (!isset($config['trace'])) {
                        $config['trace'] = true;
                    }
                    $this->_soapClient = new SoapClient($wsdl,$config);
                    break;
                case 'zend':
                    // Basic Client 
                    $this->_soapClient = new Zend_Soap_Client($wsdl,$config);
                    break;
                case 'zend-local':
                    // Local Server Client 
                    $this->_soapClient = new Zend_Soap_Client_Local($wsdl,$config);
                    break;
                case 'zend-dot-net':
                    // ASPX Server Client
                    $this->_soapClient = new Zend_Soap_Client_DotNet($wsdl,$config);
                    break;
                default:
                    throw new Exception($this->_helper()->__('El tipo de cliente soap "%s" no es válido.', $type));
                    break;
            }

            // Debug Options
            if ($this->isDebugMode() && $this->_soapClient instanceof Zend_Soap_Client) {
                $this->_soapClient
                    ->setWsdlCache(null);
            }
        }
        return $this->_soapClient;
    }
    
    /**
     * Log the las SOAP Request
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _logRequest()
    {
        Mage::log($this->getRequestString(),6,$this->_getRequestLogFile());
        return $this;
    }
    
    /**
     * Log the las SOAP Response
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _logResponse()
    {
        Mage::log($this->getResponseString(),6,$this->_getResponseLogFile());
        return $this;
    }
    
    /**
     * Log an Excepcion
     * 
     * @param Exception $ex
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _logException(Exception $ex)
    {
        $this->_errorHandler($ex);
        return $this;
    }
    
    /**
     * Loguea a SoapFault
     * 
     * @param SoapFault $fault
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _logSoapFault(SoapFault $fault)
    {
        $this->_errorHandler($fault);
        return $this;
    }
    
    /**
     * Log a Soap Exception
     * 
     * @param Exception $ex
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _logSoapException(Zend_Soap_Client_Exception $ex)
    {
        $this->_errorHandler($ex);
        return $this;
    }
    
    /**
     * Generic Error Handler Implementation
     * 
     * @param Exception $ex
     * @throws Exception
     * @return \Hd_Base_Model_Soap_Client
     */
    protected function _errorHandler(Exception $ex) 
    {
        if ($this->_soapClient) {
            // Log
            $this->_logRequest()
                ->_logResponse();
        }
        
        if ($this->isDebugMode()) {
//            throw $ex;
        }
        // Standard 
        Mage::logException($ex);
        return $this;
    }
    
    /**
     * @param string $key
     * @return Hd_Base_Helper_Data
     */
    protected function _helper($key = null) 
    {
        return ($key) ? Mage::helper("hd_base/$key") : Mage::helper("hd_base");
    }
}
