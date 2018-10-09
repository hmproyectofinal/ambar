<?php
class Hd_Nps_Adminhtml_Bccp_MockController extends Mage_Adminhtml_Controller_Action
{
    public function createAction()
    {
        try {
            $this->_getSetupModel()->createMockData();
            $this->_getSession()->addSuccess('Mock Data was Created successfully.');
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        
        $this->_redirectUrl($this->getRequest()->getServer('HTTP_REFERER'));
        
    }
    
    public function resetAction()
    {
        try {
            $this->_getSetupModel()->removeMockData();
            $this->_getSession()->addSuccess('Mock Data was Removed successfully.');
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        
        $this->_redirectUrl($this->getRequest()->getServer('HTTP_REFERER'));
        
    }
    
    /**
     * @return \Hd_Nps_Model_Resource_Setup
     */
    protected function _getSetupModel()
    {
        return new Hd_Nps_Model_Resource_Setup('hd_nps_setup');
    }
            
}

