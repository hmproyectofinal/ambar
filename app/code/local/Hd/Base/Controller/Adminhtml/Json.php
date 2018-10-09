<?php
class Hd_Base_Controller_Adminhtml_Json extends Mage_Adminhtml_Controller_Action
{
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
     * Response D-Generado
     * 
     * @return array
     */
    protected function _getJsonResponse()
    {
        return $this->_jsonResponse;
    }
    
    /**
     * Set de Response Json
     * 
     * @param type $data
     * @return Lc_Gondola_CatalogController 
     */
    protected function _setJsonResponse($data)
    {
        $this->_jsonResponse = $data;
        return $this;
    }
    
    /**
     * Send del Reponse 
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
     * - Valida que el request sea Ajax
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isAjax() && !Mage::getIsDeveloperMode()) {
            $this->getResponse()->setRedirect(Mage::getUrl());
        }
    }
    
    /**
     * Override - PostDispatch
     * - Setea el Json Como Response
     */
    public function postDispatch()
    {
        parent::postDispatch();
        $this->_sendJsonResponse();
    }
    
    /**
     * Override - Construct
     * Prepara el response (x si las moscas)
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
