<?php
class Hd_Base_Controller_Json extends Mage_Core_Controller_Front_Action
{
    
    const XML_CONFIG_PATH_ALLOWED_AGENTS = 'hd_base/json/allowed_agents';
    
    /**
     * @var Array
     */
    protected $_jsonResponse;
    
    /**
     * Error Handler Standard
     *  
     * @param type $message
     * @param type $type
     * @return type
     */
    protected function _errorHandler($message, $type = 'general')
    {
        $response = $this->_getJsonResponse();
        $response['status']  = 'error';
        $response['error'] = array(
            'type'    => $type,
            'message' => $message,
        );
        $this->_setJsonResponse($response);
        return;
    }
    
    /**
     * Genarated Response
     * 
     * @return array
     */
    protected function _getJsonResponse()
    {
        if($this->_jsonResponse === null) {
            $this->_jsonResponse = array();
        }
        return $this->_jsonResponse;
    }
    
    /**
     * Response Json Setter
     * 
     * @param Array
     * @return Hd_Base_Controller_Json
     */
    protected function _setJsonResponse($data)
    {
        $this->_jsonResponse = $data;
        return $this;
    }
    
    /**
     * Response Sender 
     */
    protected function _sendJsonResponse()
    {
        if ($this->_jsonResponse['status'] != 'error') {
            unset($this->_jsonResponse['error']);
        }
        if (!isset($this->_jsonResponse['action'])) {
            $this->_jsonResponse['action'] = $this->getRequest()->getActionName();
        }
        $body = Mage::helper('core')->jsonEncode($this->_jsonResponse);
        $this->getResponse()
            ->setHeader('Content-type', 'text-javascript')
            ->setBody($body)
            ;
    }
        
    protected function _getParam($param, $notPost = false)
    {
        return (Mage::getIsDeveloperMode() || $notPost) 
            ? $this->getRequest()->getParam($param)
            : $this->_getPost($param);
    }
    
    protected function _getPost($param = null)
    {
        return $this->getRequest()->getPost($param);
    }
    
    /**
     * Override - Pre Dispatch
     * - Ajax Call Validation
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isAjax() && !$this->_validateAgentsBypass()) {
            $this->setFlag(
                $this->getRequest()->getActionName(),
                Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH,
                true
            );
            $this->_noAjaxHandler();
        }
        return;
    }
    
    /**
     * Default No Ajax Request Handler
     */
    protected function _noAjaxHandler()
    {
        $this->norouteAction();
        return;
    }
    
    /**
     * Validate if we are using a configured agent to allow json response 
     * in "non-ajax" Request
     * 
     * @return boolean
     */
    protected function _validateAgentsBypass()
    {
        $allowedAgents = explode('||', Mage::getStoreConfig(self::XML_CONFIG_PATH_ALLOWED_AGENTS));
        $agent = $this->getRequest()->getHeader('User-Agent');
        if(in_array($agent, $allowedAgents)) {
            return true;
        }
        return false;
    }
    
    /**
     * Override - PostDispatch
     * 
     * - Sets JsonResponse
     */
    public function postDispatch()
    {
        parent::postDispatch();
        $this->_sendJsonResponse();
    }
    
    /**
     * Override - Construct
     * 
     * - Prepare a Basic Empty Response
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_setJsonResponse(array(
            'type'   => 'json',
            'status' => 'error',
            'error'  => array(
                Mage::helper('hd_base')->__('Unknown Error.')
            )
        ));
    }
    
}
