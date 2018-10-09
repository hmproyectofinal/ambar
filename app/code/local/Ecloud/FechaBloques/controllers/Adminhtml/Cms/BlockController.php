<?php 

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Cms'.DS.'BlockController.php');
 
class Ecloud_FechaBloques_Adminhtml_Cms_BlockController extends Mage_Adminhtml_Cms_BlockController
{
	/**
     * Save action
     */
    public function saveAction()
    {
    	//die("entra a controller");
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            //agregamos filtro de fechas para que se guarde bien en la base de datos
            $data = $this->_filterDateTime($data, array('fecha_desde', 'fecha_hasta'));
            
            $id = $this->getRequest()->getParam('block_id');
            $model = Mage::getModel('cms/block')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('This block no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            // init model and set data

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('The block has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('block_id' => $model->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('block_id' => $this->getRequest()->getParam('block_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
}