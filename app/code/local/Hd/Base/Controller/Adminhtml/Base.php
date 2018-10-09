<?php
class Hd_Base_Controller_Adminhtml_Base extends Mage_Adminhtml_Controller_Action
{
    
    /**************************************************************************/
    /**************************************************** MODULE PROPERTIES  **/
    /**************************************************************************/
    
    /**
     * Module Namespace
     * @var string
     */
    protected $_moduleNamespace;
    
    /**
     * Module Name'
     * @var string
     */
    protected $_moduleName;
    
    /**
     * Selected Menu Key
     * @var string
     */
    protected $_menuKey;
    
    /**************************************************************************/
    /****************************************************  MODEL PROPERTIES  **/
    /**************************************************************************/
    
    /**
     * Class (path) to use in Mage::getModel({modelClass})
     * @var string 
     */
    protected $_modelClass;
    
    /**
     * Session Namespace to store model data in case of errors
     * @var string
     */
    protected $_modelNamespace;
    
    /**
     * Entity "Name" to use in messages. Eg 'Car'
     * @var string 
     */
    protected $_entityName;
    
    /**************************************************************************/
    /**************************************************************  BLOCKS  **/
    /**************************************************************************/
    
    /**
     * Grid (Container) Block Path
     * @var string 
     */
    protected $_gridBlock;
    
    /**
     * Edit Content Block Path
     * @var string 
     */
    protected $_editContentBlock;
    
    /**
     * Edit Left (Tabs) Block Path
     * @var string
     */
    protected $_editLeftBlock;
    
    /**************************************************************************/
    /*******************************************************  ACTION METHODS **/
    /**************************************************************************/
    
    public function indexAction()
    {
        $this->_forward('grid');
    }

    public function gridAction()
    {
        $this->_initView();
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
        $this->_initView();
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

        if (!isset($params['id'])) {
            $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current'=>true));
        } elseif($back = $this->getRequest()->getParam('back')) {
            $this->_redirect("*/*/{$back}", array('id' => $model->getId(), 'active_tab' => $this->getRequest()->getParam('active_tab'),  '_current'=>true));
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
    
    /**************************************************************************/
    /*****************************************************  INTERNAL METHODS **/
    /**************************************************************************/
    
    /**
     * Initialize Layout, Menu & Title
     * @return \Hd_Base_Controller_Adminhtml_Base
     */
    protected function _initView()
    {
        // Layout
        $this->loadLayout();
        // Title
        $this->_title($this->_helper()->__($this->_moduleName));
        // Selected Menu
        $this->_setActiveMenu($this->_menuKey);
        // Return
        return $this;
    }
    
    /**
     * Initialization, Registration & Restore of the Entity Model and Data
     * @return Mage_Core_Model_Abstract
     */
    protected function _initModel()
    {
        $namespace = $this->_getModelNamespace();
        if(!$model = Mage::registry($namespace)) {
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
        }
        return $model;
    }
    
    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel()
    {
        return Mage::getModel($this->_modelClass);
    }
    
    /**
     * Save received RAW Params in session
     * @return \Hd_Base_Controller_Adminhtml_Base
     */
    protected function _backupParams()
    {
        $params = $this->getRequest()->getParams();
        $this->_getSession()->setData($this->_getModelNamespace(), $params);
        return $this;
    }
    
    /**
     * Clear Saved Params
     * @return \Hd_Base_Controller_Adminhtml_Base
     */
    protected function _clearParams()
    {
        $this->_getSession()->unsetData($this->_getModelNamespace());
        return $this;
    }
    
    /**
     * Overwrite this Method to manipulate params
     * 
     * @param array $params
     * @return array
     */
    protected function _prepareParams($params)
    {
        return $params;
    }
    
    /**
     * Tranforms selected fields ($fileds) in $params from array to string "," separated
     * 
     * @param array $params
     * @param array $fields
     * @return array
     */
    protected function _filterArray($params, array $fields) 
    {
        foreach ($fields as $k) {
            if(isset($params[$k])) {
                $params[$k] = (is_array($params[$k])) ? implode(',',$params[$k]) : $params[$k];
            }
        }
        return $params;
    }
    
    /**
     * Returns Entity Model Namespace
     * @return string
     */
    protected function _getModelNamespace()
    {
        return $this->_modelNamespace;
    }
    
     /**
     * Extended Helper Factory
     * 
     * @param string $key
     * @return Mage_Core_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("{$this->_moduleNamespace}/$key")
            : Mage::helper($this->_moduleNamespace);
    }
    
}

