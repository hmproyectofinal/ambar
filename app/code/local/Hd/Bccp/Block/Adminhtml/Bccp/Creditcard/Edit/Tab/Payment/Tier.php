<?php
class Hd_Bccp_Block_Adminhtml_Bccp_Creditcard_Edit_Tab_Payment_Tier
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
        $this->setTemplate('hd_bccp/payment/creditcard/edit/tab/payment/tier.phtml');
    }
    
    protected function _prepareLayout()
    {
        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->_helper()->__('Add Payment'),
                'onclick'   => "return tierPaymentsControl{$this->getData('country_id')}.addItem()",
                'class'     => 'add'
            ));        
        $addButton->setName('creditcard_payment_add_tier_item_button');
        $this->setChild('add_button', $addButton);
        return parent::_prepareLayout();
    }
    
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $data = $this->getData(); unset($data['type']);
        $element->addData($data);
        // Add Data
        $this->setElement($element);
        return $this->toHtml();
    }
    
    public function getCreditcardId()
    {
        return $this->getElement()->getCreditcardId();
    }
    
    public function getCountryId()
    {
        return $this->getElement()->getCountryId();
    }
    
    public function getPayments()
    {
        $payments = $this->getElement()->getValue();
        return (is_array($payments)) ? $payments : array();
    }
    
    public function getJsonTierPayments()
    {
        $jsonPayments = array();
        foreach($this->getPayments() as $payment) {
            $jsonPayments[] = Mage::helper('core')->jsonEncode($payment);
        }
        return $jsonPayments;
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