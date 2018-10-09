<?php

class Hd_Bccp_Adminhtml_Rule_Widget_ChooserController
    extends Mage_Adminhtml_Controller_Action
{
    public function loadAction()
    {
        $entity = $this->getRequest()->getParam('entity');
        if($entity) {
            $block = $this->getLayout()->createBlock(
                "hd_bccp/adminhtml_rule_widget_chooser_{$entity}",
                "adminhtml_rule_widget_chooser_{$entity}",
                array('js_form_object' => $this->getRequest()->getParam('form'),
            ));
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/hd_bccp/promo');
    }

}
?>
