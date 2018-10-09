<?php
class Hd_Nps_Model_Psp_Fraud_Processor extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    
    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = false;
    
    /**
     * @var Hd_Nps_Model_Psp
     */
    protected $_psp;
    
    /**
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return $this->_order;
    }
    
    /**
     * @return Hd_Nps_Model_Psp
     */
    protected function _getPsp()
    {
        return $this->_psp;
    }
    
    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        if($this->_customer === false) {
            
            if($this->_getOrder()->getCustomerIsGuest()) {
                $this->_customer = null;
            } else {
                $id = $this->_getOrder()->getCustomerId();
                $this->_customer = Mage::getModel('customer/customer')->load($id);
            }
        }
        return $this->_customer;
    }
    
    protected function _getPspConfig($key = null)
    {
        return $this->_getPsp()->getConfigData($key);
    }
    
    public function __construct()
    {
        $params = func_get_arg(0);
        
        if(!isset($params['order']) || !($params['order'] instanceof Mage_Sales_Model_Order)) {
            Mage::throwException('Implementation Error: you must pass a valid Order object as argument');
        }
        if(!isset($params['psp']) || !($params['psp'] instanceof Hd_Nps_Model_Psp)) {
            Mage::throwException('Implementation Error: you must pass a valid Psp object as argument');
        }
        
        parent::__construct();
        
        $this->_order = $params['order'];
        $this->_psp = $params['psp'];
        
    }
    
    protected $_fraudConfig;

    public function getFraudConfig($key = null)
    {
        if(!$this->_fraudConfig) {
            $this->_fraudConfig = Mage::getStoreConfig('hd_nps_fraud');
        }
        return ($key) 
            ? (isset($this->_fraudConfig[$key])) ? $this->_fraudConfig[$key] : null
            : $this->_fraudConfig;
    }
    
    public function getFraudPspData()
    {
        $data = array(
            'psp_CustomerAdditionalDetails' => $this->getCustomerAdditionalDetails(),
            'psp_BillingDetails' => $this->getBillingDetails(),
            'psp_ShippingDetails' => $this->getShippingDetails(),
            'psp_OrderDetails' => $this->getOrderDetails(),
        );
        return $data;
    }
    
    public function getCustomerAdditionalDetails()
    {
        $o = $this->_getOrder();
        $data = array(
            'EmailAddress'              => $o->getCustomerEmail(),
            'IPAddress'                 => ($o->getXForwardedFor()) ?: $o->getRemoteIp(),
            'DeviceType'                => $this->_getDeviceType(),
            'BrowserLanguage'           => 'NULL',
            'HttpUserAgent'             => $this->_getUserAgent(),
            'AccountPreviousActivity'   => '1',
            'AccountHasCredentials'     => '1',
            'AlternativeEmailAdress'    => '',
            'AccountID'                 => '',
            'AccountCreatedAt'          => '',
        );
        
        // Only if is Customer
        if($customer = $this->_getCustomer()) {            
            $data['EmailAddress']               = $customer->getEmail();
            $data['AccountID']                  = $customer->getEntityId();
            $data['AccountCreatedAt']           = substr($customer->getCreatedAt(),0,10);
            $data['AccountPreviousActivity']    = ($this->_customerHasOrders($customer, $o->getStoreId())) ? '0' : '1';
            $data['AccountHasCredentials']      = '0';
        }
        
        return $this->_mapResult($data, 'customer', array('order' => $o));
    }
    
    public function getBillingDetails()
    {
        $address = $this->_getOrder()->getBillingAddress();
        $data = array(
            'Person' => $this->getAddressPersonData($address),
            'Address' => $this->getAddressData($address),
        );
        return $data;
    }
    
    public function getShippingDetails()
    {
        $o = $this->_getOrder();
        $address = $this->_getOrder()->getShippingAddress();
        $data = array(
            "TrackingNumber"        => '',
            "Method"                => $this->_getPspShippingMethod($address),  // MAP !IMPORTANT
            "Carrier"               => $this->_getPspShippingCarrier($address),  // MAP !IMPORTANT
            "DeliveryDate"          => '',
            "FreightAmount"         => $this->_formatPrice($o->getShippingAmount()),
            "GiftMessage"           => '',
            "GiftWrapping"          => '',
            "PrimaryRecipient"      => $this->getAddressPersonData($address),
            "SecondaryRecipient"    => '',
            "Address"               => $this->getAddressData($address),
        );
        return $this->_mapResult($data, 'shipping', array('address' => $address));
    }
    
    public function getOrderDetails()
    {
        $data = array(
            'OrderItems' => array(),
        );        
        $items = $this->_getOrder()->getItemsCollection();
        foreach ($items as $item) {
            /* @var $item Mage_Sales_Model_Order_Item */
            $_item = array(
                "Quantity"      => (int)$item->getQtyOrdered(),
                "UnitPrice"     => $this->_formatPrice($item->getPrice()),
                "Description"   => substr($item->getName(), 0, 127),
                "SkuCode"       => $item->getSku(),
                "Type"          => '',          // MAP
                "Risk"          => '',          // MAP
                "ManufacturerPartNumber" => '', // MAP
            );
            $data['OrderItems'][] = $this->_mapResult($_item, 'order_item', array('item' => $item));
        }
        return $data;
    }
    
    public function getAddressData(Mage_Sales_Model_Order_Address $address)
    {
        $data = array(
            "Street"            => $address->getStreet1(),
            "HouseNumber"       => ((int)$address->getStreet2()) ?: '000', // MAP
            "AdditionalInfo"    => mb_substr("Dir: " . str_replace(array("\n","\r"),' ',$address->getStreetFull()),0,50),
            "City"              => $address->getCity(),
            "StateProvince"     => $address->getRegion(),
            "Country"           => $this->_getPspCountry($address),
            "ZipCode"           => $address->getPostcode(),
        );
        return $this->_mapResult($data, 'address', array('address' => $address));
    }
    
    public function getAddressPersonData(Mage_Sales_Model_Order_Address $address)
    {
        $data = array(
            "FirstName"     => $address->getFirstname(),
            "LastName"      => $address->getLastname(),
            "MiddleName"    => $address->getMiddlename(),
            "PhoneNumber1"  => $address->getTelephone(),
            "PhoneNumber2"  => $address->getFax(),
            "Gender"        => $this->_getPspGender($address),
            "DateOfBirth"   => $this->_getPspDob($address),
            "Nationality"   => $this->_getPspCountry($address),
            "IDNumber"      => $this->_getPspIdNumber($address),
            "IDType"        => $this->_getPspIdType($address),      // MAP
        );
        return $this->_mapResult($data, 'address_person', array('address' => $address));
    }
    
    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @return string
     */
    protected function _getPspShippingMethod(Mage_Sales_Model_Order_Address $address)
    {
        $info = mb_strtolower($address->getOrder()->getShippingDescription());
        switch(true) {
            case $address->getOrder()->getIsVirtual():
                $code = '20';
                break;
            case strpos($info, 'express') > -1:
                $code = '42';
                break;
            case strpos($info, 'store pickup') > -1:
            case strpos($info, 'store_pickup') > -1:
            case strpos($info, 'store-pickup') > -1:
                $code = '50';
                break;
            default:
                $code = '99';
                break;
        }
        return $code;
    }
    
    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @return string
     */
    protected function _getPspShippingCarrier(Mage_Sales_Model_Order_Address $address)
    {
        $info = mb_strtolower($address->getOrder()->getShippingDescription());
        switch(true) {
            case strpos($info, 'ups') > -1:
                $code = '100';
                break;
            case strpos($info, 'usps') > -1:
                $code = '101';
                break;
            case strpos($info, 'fedex') > -1:
                $code = '102';
                break;
            case strpos($info, 'dhl') > -1:
                $code = '103';
                break;
            case strpos($info, 'purolator') > -1:
                $code = '104';
                break;
            case strpos($info, 'greyhound') > -1:
                $code = '105';
                break;
            case strpos($info, 'correo argentino') > -1:
            case strpos($info, 'correo_argentino') > -1:
            case strpos($info, 'correo-argentino') > -1:
                $code = '200';
                break;
            case strpos($info, 'oca') > -1:
                $code = '201';
                break;
            default:
                $code = '999';
                break;
        }
        return $code;
    }
    
    protected function _getPspIdNumber(Mage_Sales_Model_Order_Address $address) 
    {
        // Since Address does not allocate taxvat itself if shipping person
        // is different we must skip this taxvat value
        if($address->getAddressType() == 'shipping' 
            && $this->_addressesAreDifferent($address->getOrder())) {
            return '';
        }
        // Try To load from Order
        if($tv = $address->getOrder()->getCustomerTaxvat()) {
            return $tv;
        }
        // Try To load from Customer
        if($customer = $this->_getCustomer()) {
            return ($customer->getTaxvat()) ?: '';
        }
    }
    
    protected function _getPspIdType(Mage_Sales_Model_Order_Address $address) 
    {
        return '';
    }
    
    protected function _getPspDob(Mage_Sales_Model_Order_Address $address) 
    {
        if($customer = $this->_getCustomer()) {
            $dob = (string)$customer->getDob();
        } else {
            $dob = (string)$address->getOrder()->getCustomerDob();
        }
        return substr($dob, 0, 10);
    }
    
    protected function _getPspCountry(Mage_Sales_Model_Order_Address $address) 
    {
        return $this->_getPsp()->getPspCountry($address->getCountryId());
    }
    
    protected function _getPspGender(Mage_Sales_Model_Order_Address $address) 
    {
        if($gender = $address->getOrder()->getCustomerGender()) {
            return ($gender = '123') ? 'M' : 'F';
        }
        return '';
    }

    protected function _formatPrice($price)
    {
        return (int)round($price,2) * 100;
    }
    
    /**
     * 
     * @todo Implement Mapping
     * 
     * @param type $data
     * @param type $configKey
     * @param type $address
     */
    protected function _mapResult($data, $configKey, $params = array())
    {
        foreach($this->getFraudConfig($configKey) as $k => $v) {
            
            // Remove Disable Fields
            if($v == '' || is_null($v)) {
                unset($data[$k]);
                continue;
            }
            
            // Eval Fields with Config Different of 'A'
            if($v != 'A') {
                $data[$k] = $this->_evalFieldMapping($k, $data[$k], $v, $params);                
            }
            
            // Remove Empty Fields
            if($data[$k] == '' || $data === null || $data === false) {
                unset($data[$k]);
            }
            
        }
        return $data;
    }
    
    /**
     * @todo
     * 
     * @param type $field
     * @param type $configValue
     * @param array $params
     */
    protected function _evalFieldMapping($field, $value, $configValue, $params)
    {
        return $field;
    }
    
    /**
     * 
     * 0 => 'Desktop'
     * 1 => 'Mobile'
     * 2 => 'Tablet'
     * 3 => 'Tv'
     * 
     * @return int
     */
    protected function _getDeviceType()
    {
        // Only in active sessions
        $session = Mage::getSingleton('checkout/session');
        if(!$session->getNpsQuoteId()) {
            return null;
        }
        $detector = new Mobile_Detect();
        switch(true) {
            case $detector->isTablet():
                $code = '2';
                break;
            case $detector->isMobile():
                $code = '1';
                break;
            default:
                $code = '0';
                break;
        }
        return $code;
    }
    
    protected function _getUserAgent()
    {
        // Only in active sessions
        $session = Mage::getSingleton('checkout/session');
        if(!$session->getNpsQuoteId()) {
            return null;
        }
        return Mage::app()->getRequest()->getServer('HTTP_USER_AGENT');
    }
    
    protected function _customerHasOrders(Mage_Customer_Model_Customer $customer, $storeId)
    {
        $col = $this->_getOrderCollection()
            ->addFieldToFilter('customer_id', array('eq' => $customer->getEntityId()))
            ->addFieldToFilter('store_id', array('eq' => $storeId))
            ;
        return ($col->count() > 1);
    }
    
    protected function _addressesAreDifferent(Mage_Sales_Model_Order $order)
    {
        $ba = $order->getBillingAddress();
        $sa = $order->getBillingAddress();
        return ($ba->getName() != $sa->getName()) || ($ba->getStreetFull() != $sa->getStreetFull());
    }
    
    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getOrderCollection()
    {
        return Mage::getResourceModel('sales/order_collection');
    }

    /**
     * @return Hd_Base_Helper_Date
     */
    protected function _dHelper()
    {
        return Mage::helper('hd_base/date');
    }
    
    
    
}
