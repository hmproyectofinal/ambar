<?php
/**
 * 
 * @method Hd_Nps_Model_Psp getMethod()
 * 
 */
class Hd_Nps_Block_Checkout_Redirect_Form extends Hd_Nps_Block_Checkout_Template
{
    /**
     * @return \Varien_Data_Form
     */
    protected function _getForm()
    {
        $method = $this->getMethod();
        
        if (!$method) {
            Mage::throwException($this->__('No Payment Method Received.'));
        }
        
        $data = $method->getPayOnline3pFormData();
        
        $form = new Varien_Data_Form();
        $form->setAction($data["action"])
            ->setId('nps_redirect_form')
            ->setName('nps_redirect_form')
            ->setMethod('POST')
            ->setUseContainer(true);
        
        // Form Fields
        foreach ($data["fields"] as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        
        // Add Debug Button
        if($this->isDebug()) {
            $form->addField('debug_button', 'submit', array('name' => 'debug-button', 'value' => 'Send to NPS'));
        }
        
        return $form;
    }
    
    protected function _getDebugFormReturn()
    {
        $form = clone $this->_getForm();
        $form->setAction($this->_getActionUrl('return'));
        $form->getElement('debug_button')->setData('value', 'SIMULATE RETURN');
        return $form;
    }
    
    protected function _getDebugFormDecline()
    {
        $form = clone $this->_getForm();
        $form->setAction($this->_getActionUrl('decline'));
        $form->getElement('debug_button')->setData('value', 'SIMULATE DECLINE');
        return $form;
    }
    
    public function isDebug()
    {
        return $this->getMethod()->isDebug();
    }
    
}
