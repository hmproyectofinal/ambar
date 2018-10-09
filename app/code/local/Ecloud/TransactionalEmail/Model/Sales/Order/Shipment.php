<?php
class Ecloud_TransactionalEmail_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment {
    public function sendEmail($notifyCustomer = true, $comment = ''){

        $order = $this->getOrder();
        $storeId = $order->getStore()->getId();

        if(!Mage::getStoreConfig('transactionalemail/global/activado', $order->getStore())){
            return parent::sendEmail($notifyCustomer, $comment);    
        }
        
        //Mage::log("Custom Shipment Rewrite");

        $completeOrder = Mage::getModel('sales/order')->load($order->getId());
        $shippingMethod = $completeOrder->getShippingMethod();
        //Mage::log("Shipping method: ".$shippingMethod);

        $carrierCode = explode('_', $shippingMethod, 2);
        //Mage::log("Shipping method: ".$carrierCode[0]);


        $customEmailTemplate = Mage::getModel("transactionalemail/carriertemplate")->loadByCarrier($carrierCode[0], $storeId);
        //Mage::log("Custom template id: ".$customEmailTemplate->getEmailTemplate());

        if (!Mage::helper('sales')->canSendNewShipmentEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        //$mailer->setTemplateId($templateId);

        //Mage::log("Default Template ID: ".$templateId);

        if($customEmailTemplate->getEmailTemplate()!="") {
            $mailer->setTemplateId($customEmailTemplate->getEmailTemplate());
        } else {
            $mailer->setTemplateId($templateId);
        }

        $mailer->setTemplateParams(array(
                'order'        => $order,
                'shipment'     => $this,
                'comment'      => $comment,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        return $this;
    }
}