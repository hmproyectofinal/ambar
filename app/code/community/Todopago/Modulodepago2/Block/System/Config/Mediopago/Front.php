<?php

class Todopago_Modulodepago2_Block_System_Config_Mediopago_Front extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_values = null;
	
    protected function _construct()
    {
        $this->setTemplate('modulodepago2/system/config/medios.phtml');
        return parent::_construct();
    }
	
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());
        return $this->_toHtml();
    }
	
    public function getValues(){
        $values = array();
        //get the available values (use the source model from your question)
		$data = $this->getMediosPago();

        foreach ($data as $value){
            $values[$value['ID']] = $value['Name'];
        }
        return $values;
    }
	
    public function getIsChecked($name){
        return in_array($name, $this->getCheckedValues());
    }
	
    public function getCheckedValues(){
		$setting = Mage::getStoreConfig('payment/todopago_mediosdepago/mediosdepago_check');
		$values = explode(",",$setting);
		if(empty($setting)) {
			$fvalues = $this->getValues();
			foreach($fvalues as $key => $valor)
				$values[] = $key;
		}
		return $values;
    }

    private function get_http_header(){
        return json_decode(Mage::getStoreConfig('payment/modulodepago2/header_http'), TRUE);
    }

	protected function getMediosPago()
	{
		$todopago_connector = Mage::helper('modulodepago2/connector')->getConnector();
		
		try{
			$pay_methods = $todopago_connector->discoverPaymentMethods();
		}
		catch(Exception $e){
			$exception['Operations']['Exception']=$e;
			return $exception;
		}
		
		return $pay_methods['PaymentMethod'];
	}	
}