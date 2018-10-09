<?php
class Hd_Bccp_Controller_Adminhtml_Bccp extends Mage_Adminhtml_Controller_Action
{
    
    protected $_gridBlock;
    
    protected $_editContentBlock;
    
    protected $_editLeftBlock;
    
    protected $_modelClass;
    
    protected $_entityName;
    
    protected $_modelNamespace;
    
    public function indexAction()
    {
        $this->_forward('grid');
    }

    public function gridAction()
    {
        $this->_initAction();
        // Grid
        if ($gridBlock = $this->_gridBlock) {
            $this->_addContent($this->getLayout()->createBlock($gridBlock));
        }
        $this->renderLayout();
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    {
        $model = $this->_initModel();
        $this->_initAction();
        // Content
        if ($content = $this->_editContentBlock) {
            $this->_addContent($this->getLayout()->createBlock($content));
        }
        // Left
        if ($left = $this->_editLeftBlock) {
            $this->_addLeft($this->getLayout()->createBlock($left));
        }
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        try {
            // Load - Init
            $model  = $this->_initModel();
            // Filter Params
            $params = $this->_prepareParams($this->getRequest()->getParams());
            // Append Data
            $model->addData($params)
                ->save();
            
            $entity = $this->_helper()->__($this->_entityName);
            $this->_getSession()
                ->addSuccess($this->_helper()->__('The %s "%s" was successfully saved.', $entity, $model->getName()));
            
        } catch (Exception $e) {
            // Backup Session Data
            $this->_backupParams();
            
            // Error
            $this->_getSession()->addError($e->getMessage());
            
            if($id = $this->getRequest()->getParam('id')) {
                $this->_redirect('*/*/edit', array('id'=> $id));
                return;
            }
            
            $this->_redirect('*/*/index');
            return;
        }
        
        // Clean Session Data
        $this->_clearParams();

        // Caso New - Vuelve al Edit
        if (!isset($params['id'])) {
            $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current'=>true));
        // Caso Save & Continue Edit
        } elseif($back = $this->getRequest()->getParam('back')) {
            $this->_redirect("*/*/{$back}", array('id' => $model->getId(), 'active_tab' => $this->getRequest()->getParam('active_tab'),  '_current'=>true));
        // Caso Nada... Vuelve al index    
        } else {
            $this->_redirect('*/*/index');
        }
        return;
    }
    
    public function deleteAction()
    {
        try {
            // Load - Init
            $model = $this->_initModel();
            $name = $model->getName();
            // Voleta
            $model->delete();            
            $this->_getSession()
                ->addSuccess($this->_helper()->__('The %s "%s" was successfully removed.', $this->_helper()->__($this->_entityName), $name));
            
        } catch (Exception $e) {
            // Error
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
        return;
    }
    
    protected function _initAction()
    {
        // Title
        $this->_title($this->_helper()->__('Payments Management'));
        // Layout
        $this->loadLayout();
        // Return
        return $this;
    }
    
    protected function _getModel()
    {
        return Mage::getModel($this->_modelClass);
    }
    
    protected function _backupParams()
    {
        $params = $this->getRequest()->getParams();
        $this->_getSession()->setData($this->_getModelNamespace(), $params);
        return $this;
    }
    
    protected function _filterArray($params, array $fields) 
    {
        foreach ($fields as $k) {
            if(isset($params[$k])) {
                $params[$k] = (is_array($params[$k])) ? implode(',',$params[$k]) : $params[$k];
            }
        }
        return $params;
    }
    
    protected function _clearParams()
    {
        $this->_getSession()->unsetData($this->_getModelNamespace());
        return $this;
    }
    
    protected function _initModel()
    {
        $model = $this->_getModel();        
        if ($id = $this->getRequest()->getParam('id')) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()
                    ->addError($this->_helper()->__('Requested %s no longer exists.', $this->_helper()->__($this->_entityName)));
                $this->_redirect('*/*/index');
            }
        }
        
        // Restore Data From Session
        if ($data = $this->_getSession()->getData($this->_getModelNamespace())) {
            $model->addData($data);
            $this->_getSession()->unsetData($this->_getModelNamespace());
        }
        
        // Registry
        Mage::register($this->_getModelNamespace(), $model);
        
        return $model;
    }
    
    protected function _prepareParams($params)
    {
        // Prepare Stores
        if ($this->_helper()->isStoreSupportEnable()) {
            $params['store_ids'] = ((bool)$params['store_ids_flag'])
                    ? $params['store_ids'] : array(0);
        }
        return $params;
    }
    
    protected function _getModelNamespace()
    {
        return $this->_modelNamespace;
    }
    
     /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = 'data')
    {
        return Mage::helper("hd_bccp/$key");
    }
    
}
