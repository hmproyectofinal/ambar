<?php
class Treggo_Customshippingmethod_Model_Observer
{
    public function adminhtmlWidgetContainerHtmlBefore($event) 
    {
        $block = $event->getBlock();
        $order = Mage::registry('current_order');
        
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            if(isset($order)) {
                //$order->setTreggoOrderStatus(null);
                //$order->save();
                $orderStatus = $order->getTreggoOrderStatus();
                if(!$orderStatus) {
                    $id = $order->getId();
                    $block->addButton(
                        'treggo_button', array(
                        'label'     => Mage::helper('treggo_customshippingmethod')->__('Solicitar TREGGO'),
                        'onclick'   =>  "treggoSignupOrder($id)",
                        'class'     => 'go',
                        'id'        => 'treggoButton'
                        )
                    );   
                } else {
                    $block->addButton(
                        'treggo_button', array(
                        'label'     => Mage::helper('treggo_customshippingmethod')->__('TREGGO ya solicitado'),
                        'class'     => 'success',
                        'id'        => 'treggoButton'
                        )
                    ); 
                }
            }
        }
    }
}