<?php
abstract class Hd_Bccp_Model_Rule_Condition_Abstract
    extends Mage_Rule_Model_Condition_Abstract
{
    
    protected $_entityKey;
    
    public function getInputType()
    {
        return 'multiselect';
    }
    
    public function getValueAfterElementHtml()
    {
        $html = '';
        $image = Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif');
        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger" title="' . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
        }
        return $html;
    }
    
    public function getValueElementType()
    {
        return 'text';
    }
    
    public function getExplicitApply()
    {
        return true;
    }
    
    public function getValueElementChooserUrl()
    {
        $url = "adminhtml/rule_widget_chooser/load";
        $params = array(
            'entity' => $this->getAttribute(),
        );
        if ($jsForm = $this->getJsFormObject()) {
            $params['form'] = $jsForm;
        }
        return Mage::helper('adminhtml')->getUrl($url, $params);
    }
    
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'multiselect' => array('==', '!=', '()', '!()'),
            );
        }
        return $this->_defaultOperatorInputByType;
    }
    
    public function validate(\Varien_Object $object)
    {
        if(!$this->_canValidate($object)) {
            return false;
        }
        
        $result         = false;
        $ruleValue      = $this->getValueParsed();
        $currentValue   = $this->getCurrentValue($object);
        
        switch ($this->getOperator()) {
            case "==":
                $result = ($ruleValue == $currentValue);
                break;
            case "!=":
                $result = ($ruleValue != $currentValue);
                break;
            case "()":
                $result = in_array($currentValue, $ruleValue);
                break;
            case "!()":
                $result = !in_array($currentValue, $ruleValue);
                break;
        }
        
//        if(Mage::getIsDeveloperMode()) {
//            $debug = array(
//                '_entityKey'    => $this->_entityKey,
//                'attribute'     => $this->getAttribute(),
//                'operator'      => $this->getOperator(),
//                'ruleValue'     => $ruleValue,
//                'currentValue'  => $currentValue,
//                'result'        => ($result) ? 'TRUE' : 'FALSE',
//            );
//            Mage::log($debug);
//        }
        
        return $result;
        
    }
    
    public function getCurrentValue(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getQuote()->getPayment()->getData($this->_entityKey);
    }
    
    protected function _canValidate($object)
    {
        if(!$object instanceof Mage_Sales_Model_Quote_Address) {
            return false;
        }
        if (($object->getAddressType() == 'billing')) {
            return false;
        }        
        if (!count($this->_getAddressItems($object))) {
            return false;
        }        
        if(!$object->getQuote()->getPayment()->hasMethodInstance()) {
            return false;
        }
        $payment = $object->getQuote()->getPayment()->getMethodInstance();
        if (!$payment instanceof Hd_Bccp_Model_Payment_Method_Interface) {
            return false;
        }
        return true;
    }
    
    
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getAllNonNominalItems();
    }
    
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("hd_bccp/$key")
            : Mage::helper("hd_bccp");
    }
}
