<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Estadotemplate_Renderer_Template extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		
		$template = Mage::getModel('core/email_template')->load($value);
		Mage::log($template->getData('template_code'));
		return $template->getData('template_code');
	}
}