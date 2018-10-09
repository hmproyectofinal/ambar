<?php
class Hd_Bccp_Block_Sales_Order_Total extends Mage_Sales_Block_Order_Totals
{
    /**
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
    
    public function getAddress()
    {
        return $this->getSource()->getShippingAddress();
    }

    /**
     * @return Hd_Bccp_Block_Sales_Order_Total
     */
    public function initTotals()
    {
        $amount     = $this->getAddress()->getData('hd_bccp_surcharge');
        $baseAmount = $this->getAddress()->getData('hd_bccp_base_surcharge');
        
        if ((float) $amount) {
            
            $isCreditmemeo = ($this->getSource() instanceof Mage_Sales_Model_Order_Creditmemo);

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'hd_bccp_surcharge',
                'strong' => false,
                'value'  =>  $isCreditmemeo ? - $amount : $amount,
                'base_value' => $isCreditmemeo ? - $baseAmount : $baseAmount,
                'label'  => $this->helper('hd_bccp')->__('Payment Surcharge'),
            )));
        }
        return $this;
    }
}