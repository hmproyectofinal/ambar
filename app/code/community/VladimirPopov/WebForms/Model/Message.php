<?php

class VladimirPopov_WebForms_Model_Message
    extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/message');
    }

    public function sendEmail()
    {
        $result = Mage::getModel('webforms/results')
            ->load($this->getResultId());

        $email = $result->getCustomerEmail();

        if (!$email) return false;

        $name = $result->getCustomerName();

        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($result->getStoreId())
            ->load($result->getWebformId());

        $sender = Array(
            'name' => Mage::app()->getStore($result->getStoreId())->getFrontendName(),
            'email' => $result->getReplyTo('customer'),
        );

        if (Mage::getStoreConfig('webforms/email/email_from')) {
            $sender['email'] = Mage::getStoreConfig('webforms/email/email_from');
        }

        $subject = $result->getEmailSubject();

        $vars = $this->getTemplateVars();

        $storeId = $result->getStoreId();

        $templateId = 'webforms_reply';

        if ($webform->getEmailReplyTemplateId()) {
            $templateId = $webform->getEmailReplyTemplateId();
        }

        $mail = Mage::getModel('core/email_template')
            ->setTemplateSubject($subject)
            ->setReplyTo($result->getReplyTo('customer'))
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);

        return $mail->getSentSuccess();
    }

    public function getTemplateVars()
    {
        $result = Mage::getModel('webforms/results')
            ->load($this->getResultId());
        $name = $result->getCustomerName();

        $varCustomer = new Varien_Object(array(
            'name' => $name
        ));

        $varResult = $result->getTemplateResultVar();

        $varResult->addData(array(
            'id' => $result->getId(),
            'subject' => $result->getEmailSubject(),
            'date' => Mage::helper('core')->formatDate($result->getCreatedTime()),
            'html' => $result->toHtml('customer'),
        ));

        $varReply = new Varien_Object(array(
            'date' => Mage::helper('core')->formatDate($this->getCreatedTime()),
            'message' => $this->getMessage(),
            'author' => $this->getAuthor()
        ));

        $vars = Array(
            'customer' => $varCustomer,
            'result' => $varResult,
            'reply' => $varReply,
            'webform' => $result->getWebform()
        );
        $customer = $this->getCustomer();

        if ($customer) {
            $customerObject = new Varien_Object();
            $customerObject->setData($customer->getData());
            $vars['customer'] = $customerObject;
        }

        return $vars;
    }
}