<?php
class Treggo_Customshippingmethod_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() 
    {
    }

    public function newShippingAction() 
    {
        $id = $this->getRequest()->getPost('id');
        $order = Mage::getModel('sales/order')->load($id);

        $shippingAddress = $order->getShippingAddress();

        if(isset($shippingAddress)) {
            $fullName = $shippingAddress->getFirstname(). ' ' . $shippingAddress->getLastname();

            $customerId = $order->getCustomerId();
            $customerData = Mage::getModel('customer/customer')->load($customerId);

            $model = Mage::getModel('treggo_customshippingmethod/Shipping');

            $contact = $model->getConfigData('orderprefix'). ' ' .$id;
            $warehouse = $model->getConfigData('warehouse');
            $email = isset($customerData['email']) ? $customerData['email'] : '';
            $phone = $shippingAddress->getTelephone();

            $data = array(
              "destinos" => array (
                array (
                  "direccion" => $warehouse,
                  "contacto" => $contact,
                  "email" => $email,
                  "telefono" => $phone
                ),
                array (
                  "direccion" => $shippingAddress->getStreet1().", ".$shippingAddress->getCity(),
                  "contacto" => $fullName,
                  "puerta" => $shippingAddress->getStreet2()
                )
              )
            );

            $shippingObj = array();
            $shippingObj['address'] = $shippingAddress->getStreet1(); 
            $shippingObj['region'] = $shippingAddress->getCity(); 
            $api = $model->postToTreggo('/api/1/alta', $data, $shippingObj);
        } else {
            $api = array();
            $api['correcto'] = false;
        }

        if($api->correcto) {
            $order->setTreggoOrderStatus('true');
            $order->save();
        }

        Mage::app()->getResponse()->setBody(json_encode($api));
    }
}