<?php
class Ecloud_Shipmentfilter_Model_Attributeset
{
	public function toOptionArray(){

        $attributeModel  = Mage::getModel("eav/entity_attribute_set")->getCollection();

        foreach ($attributeModel as $atrrset) {
            if (isset($atrrset['attribute_set_id'])){
                if ( (!isset($attrsetname)) || $atrrset['attribute_set_name'] != $attrsetname){
                    $attrsetname      = $atrrset['attribute_set_name'];
                    // $attrsetid        = $atrrset['attribute_set_id'];
                    $_attributesets[] = array('value' => $attrsetname, 'label' => $attrsetname);
                }
            }
        }
        return $_attributesets;
    }
}
		