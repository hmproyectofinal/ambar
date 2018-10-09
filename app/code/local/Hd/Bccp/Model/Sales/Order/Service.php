<?php
class Hd_Bccp_Model_Sales_Order_Service extends Mage_Core_Model_Abstract
{

    public function orderInvoce(Mage_Sales_Model_Order $order, $status = null, $comment = null)
    {
        if (!$order->canInvoice()) {
            throw new Exception(Mage::helper('hd_bccp')->__('Order Can not be Invoiced'));
        }

        // Invoice Create
        $invoice = Mage::getModel('sales/service_order', $order)
            ->prepareInvoice();
        $invoice->setRequestedCaptureCase(
            Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE
        );
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        
        $transaction = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
        
        // Optional Custom Status
        if($status) {
            // Status
            $order->setStatus($status);
            if($comment) {
                $order->addStatusHistoryComment($comment, false);
            }
            $order->save();
        }

        // Email
        if (!$invoice->getData('email_sent')) {
            $invoice->sendEmail(true, '')->setEmailSent(true);
        }
        
        return $this;
    }
    
    
    public function orderHold(Mage_Sales_Model_Order $order, $status = null, $comment = null, $notifiedCustomer = false)
    {
        $comment = ($comment) ?: 'Order Holded';
        
        // Validations
        if(!$order->canHold()) {
            return $this;
        }
        if($order->getStatus() == $status) {
            return $this;
        }
        
        // Custom Status Implementation
        if($status) {
            
            $order->addStatusHistoryComment($comment, $status)
                ->save();
            
            $order->hold()
                ->setStatus($status)->save();
            
        // Default Status Implementation
        } else {
            $order->hold()->save();
        }
        
        // Implement Email
        
            
    }
    
    public function orderCancel(Mage_Sales_Model_Order $order, $status = null, $comment = null, $sendEmail = false)
    {
        
    }
    
}
