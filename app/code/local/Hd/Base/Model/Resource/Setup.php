<?php
class Hd_Base_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    
    const MAGE_ENVIRONMENT_DEVELOPMENT = 'dev';
    
    const MAGE_ENVIRONMENT_STAGING     = 'stage';
    
    const MAGE_ENVIRONMENT_PRODUCTION  = 'production';
    
    const MAKE_DB_BACKUP_FLAG  = 'MAGE_MAKE_DB_BACKUP';
    
    protected $_currentStoreId  = null;
    
    protected $_defaultStoreId  = null;
    
    protected $_isSetAsAdmin    = null;
   
    protected $_defaultWebsiteData = array(
        'sort_order' => 0,
        'is_active'  => 0,
    );
    
    protected $_defaultGroupData = array(
        'sort_order' => 0,        
        'is_active'  => 0,
    );
    
    protected $_defaultStoreData = array(
        'sort_order' => 0,        
        'is_active'  => 0,
    );
            
    protected $_defaultRootCategoryData = array(
        'level'         => '1',
        'position'      => '1',
        'parent_id'     => '1',
        'is_active'     => '1',
        'is_anchor'     => '1',
        'store_id'      => Mage_Catalog_Model_Category::DEFAULT_STORE_ID,
        'display_mode'  => Mage_Catalog_Model_Category::DM_PRODUCT,
        'include_in_menu' => '1',
    );
    
    /**
     * @var Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection 
     */
    protected $_productAttributeSets;
    
    /**
     * @var int 
     */
    protected $_productEntityTypeId;
   
/******************************************************************************/    
/******************************************************************* CATALOG  */    
/******************************************************************************/
    
    /**
     * @return \Mage_Catalog_Model_Resource_Setup 
     */
    public function getCatalogSetupModel()
    {
        return new Mage_Catalog_Model_Resource_Setup('catalog_setup');
    }
    
    /**************************************************************************/    
    /****************************************************** CATALOG / PRODUCT */    
    /**************************************************************************/
        
    /**
     * @return int
     */
    protected function _getProductEntityTypeId()
    {
        if(!$this->_productEntityTypeId) {
            $this->_productEntityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }
        return $this->_productEntityTypeId;
    }    
    
    /**************************************************************************/    
    /***************************************** CATALOG / PRODUCT / ATTRIBUTES */    
    /**************************************************************************/    
    
    /**
     * @param string $code
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addProductAttribute($code, $data)
    {
        $installer = $this->getCatalogSetupModel();
        $installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, $code);
        $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $code, $data);
        return $this;
    }
    
    
    /**
     * @param array $attributesData
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addProductAttributes(array $attributesData)
    {
        foreach($attributesData as $code => $data) {
            $this->addProductAttribute($code, $data);
        }
        return $this;
    }
    
    /**
     * @param string $code
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function updateProductAttribute($code, $data) 
    {
        $installer = $this->getCatalogSetupModel();
        foreach($data as $k => $v) {
            $installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, $code, $k, $v);
        }
        return $this;
    }
    
    /**
     * @param string $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeProductAttribute($code)
    {
        $installer = $this->getCatalogSetupModel();
        $installer
            ->removeAttribute(Mage_Catalog_Model_Product::ENTITY, $code);
        return $this;
    }
    
    /**
     * @param array $codes
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeProductAttributes(array $codes)
    {
        foreach($codes as $code) {
            $this->removeProductAttribute($code);
        }
        return $this;
    }
    
    /**************************************************************************/    
    /************************************* CATALOG / PRODUCT / ATTRIBUTE SETS */    
    /**************************************************************************/    
    
    /**
     * @param int $setId
     * @return \Mage_Eav_Model_Entity_Attribute_Set | null
     */
    public function getProductAttributeSet($setId)
    {
        foreach($this->getProductAttributeSets() as $attributeSet) {
            if($attributeSet->getAttributeSetId() == $setId) {
                return $attributeSet;
            }
        }
        return null;
    }
    
    /**
     * @param string $setName
     * @return \Mage_Eav_Model_Entity_Attribute_Set | null
     */
    public function getProductAttributeSetByName($setName)
    {
        foreach($this->getProductAttributeSets() as $attributeSet) {
            if($attributeSet->getAttributeSetName() == $setName) {
                return $attributeSet;
            }
        }
        return null;
    }
    
    /**
     * @return \Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
     */
    public function getProductAttributeSets()
    {
        if(!$this->_productAttributeSets) {
            $collection = Mage::getResourceModel('eav/entity_attribute_set_collection');
            $collection
                ->setEntityTypeFilter($this->_getProductEntityTypeId())
                ->load();
            $this->_productAttributeSets = $collection;
        }
        return $this->_productAttributeSets;
    }
    
    /**************************************************************************/
    /*********************************** CATALOG / PRODUCT / ATTRIBUTE GROUPS */
    /**************************************************************************/
        
    /**
     * @param type $groupName
     * @param type $setId
     * @param type $resetLoadedGroups
     * 
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addProductAttributeSetGroup($groupName, $setId, $resetLoadedGroups = true)
    {
        // Validate
        if($group = $this->getProductAttributeSetGroupByName($groupName, $setId)) {
            return $this;
        }
        // Create
        $this->getCatalogSetupModel()
            ->addAttributeGroup($this->_getProductEntityTypeId(), $setId, $groupName);
        // Reset
        if($resetLoadedGroups) {
            unset($this->_productAttributeSetGroups[$setId]);
        }
        return $this;
    }

    
    /**
     * @param int $groupId
     * @param int $setId
     * @return \Mage_Eav_Model_Entity_Attribute_Group | null
     */
    public function getProductAttributeSetGroup($groupId, $setId)
    {
        foreach($this->getProductAttributeSetGroups($setId) as $group) {
            if($group->getAttributeGroupId() == $groupId) {
                return $group;
            }
        }
        return null;
    }
    
    /**
     * @param string $groupName
     * @param int $setId
     * @return \Mage_Eav_Model_Entity_Attribute_Group | null
     */
    public function getProductAttributeSetGroupByName($groupName, $setId)
    {
        foreach($this->getProductAttributeSetGroups($setId) as $group) {
            if($group->getAttributeGroupName() == $groupName) {
                return $group;
            }
        }
        return null;
    }
    
    /**
     * @var Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection 
     */
    protected $_productAttributeSetGroups = array();
    
    /**
     * @param int $setId
     * @return \Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection
     */
    public function getProductAttributeSetGroups($setId)
    {
        if(!$attributeSet = $this->getProductAttributeSet($setId)) {
            Mage::throwException(Mage::helper('hd_base')->__('Atribute set (ID: %s) does not exists.', $setId));
        }
        if(!isset($this->_productAttributeSetGroups[$setId])) {
            $collection = Mage::getResourceModel('eav/entity_attribute_group_collection');
            $collection
                ->setAttributeSetFilter($setId)
                ->load();
            $this->_productAttributeSetGroups[$setId] = $collection;
        }
        return $this->_productAttributeSetGroups[$setId];
    }

    
    /**************************************************************************/
    /***************************************************** CATALOG / CATEGORY */
    /**************************************************************************/
    
    /**************************************************************************/
    /**************************************** CATALOG / CATEGORY / ATTRIBUTES */
    /**************************************************************************/
    
    /**
     * @param string $code
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCategoryAttribute($code,$data)
    {
        $installer = $this->getCatalogSetupModel();
        $installer->removeAttribute(Mage_Catalog_Model_Category::ENTITY, $code);
        $installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, $code, $data);
        return $this;
    }
    
    /**
     * @param array $attributesData
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCategoryAttributes(array $attributesData)
    {
        foreach($attributesData as $code => $data) {
            $this->addCategoryAttribute($code, $data);
        }
        return $this;
    }    
    
    /**
     * @param string $code
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function updateCategoryAttribute($code, $data) 
    {
        $installer = $this->getCatalogSetupModel();
        $installer->updateAttribute(Mage_Catalog_Model_Category::ENTITY, $code,$data);
        return $this;
    }
    
    
    /**************************************************************************/
    /****************************************************************** SALES */
    /**************************************************************************/
    
    /**
     * @return \Hd_Base_Model_Resource_Sales_Setup
     */
    public function getSalesSetupModel()
    {
        return new Hd_Base_Model_Resource_Sales_Setup('sales_setup');
    }
    
    /**************************************************************************/
    /********************************************************** ORDER / QUOTE */
    /**************************************************************************/
        
    /**
     * @param array $statuses
     * $satatuses = array(
     *    [0] => array(
     *        'code' => 'status_code',  
     *        'label' => 'status_label',  
     *        'state' => 'status_state',  
     *    ),
     *    ...
     *    ...
     *    [n]
     * )
     * 
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addOrderStatuses(array $statuses)
    {
        foreach($statuses as $status) {
            $this->addOrderStatus($status);
        }
        return $this;
    }

    /**
     * @param array $data
     * 
     * $data = array(
     *     'code' => 'status_code',  
     *     'label' => 'status_label',  
     *     'state' => 'status_state',  
     * )
     * 
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addOrderStatus(array $data)
    {
        // Validacion
        if (!isset($data['code'])) {
            Mage::throwException(__METHOD__ . ' - "code" key must be defined.');
        }
        if (!isset($data['label'])) {
            Mage::throwException(__METHOD__ . ' - "label" key must be defined.');
        }
        if (!isset($data['state'])) {
            Mage::throwException(__METHOD__ . ' - "state" key must be defined.');
        }
        
        $status = Mage::getModel('sales/order_status');
        $status
            ->setStatus($data['code'])
            ->setLabel($data['label'])
            ->assignState($data['state'])
            ->save();
        return $this;
    }
    
    /**
     * @param mixed $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeOrderStatus($code)
    {
        $status = Mage::getModel('sales/order_status');
        /* @var $status Mage_Sales_Model_Order_Status */
        $status->load($code,'status');
        if (!$status->getId()) {
            Mage::logException(new Exception('Invalid Order Status Code: "'. $code . '"'));
            return $this;
        }
        
        $status
            ->unassignState(Mage_Sales_Model_Order::STATE_PROCESSING)
            ->save()
            ->delete();
        
        return $this;
    }
    
    /**
     * 
     * @param type $data
     * $data = array(
     *     'code'        => 'status_code',
     *     'label'       => 'status_label',
     *     'remove_state'=> 'old_status_state',
     *     'state'       => 'status_state',
     * )
     * 
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function updateOrderStatus($data) 
    {
        $status = Mage::getModel('sales/order_status');
        /* @var $status Mage_Sales_Model_Order_Status */
        
        if (!isset($data['code'])) {
            Mage::throwException(__METHOD__ . ' - "code" Key Can Not Be Empty');
        }
        $status->load($data['code'],'status');
        if (!$status->getId()) {
            Mage::logException(new Exception('Invalid Order Status Code: "'. $data['code'] . '"'));
            return $this;
        }
        
        if (isset($data['label'])) {
            $status->setLabel($data['label']);
        }
        if (isset($data['remove_state'])) {
            $status->unassignState($data['remove_state']);
        }
        if (isset($data['state'])) {
            $status->assignState($data['state']);
        }
        
        // Save Save Saaaaaaaaaaave me
        if ($status->hasDataChanges()) {
            $status->save();
        }
        
        return $this;
    }
    
    
    
    
    /**************************************************************************/    
    /************************************************************** CUSTOMER  */    
    /**************************************************************************/
    
    /**
     * @param string $code
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCustomerAttribute($code, $data)
    {
        return $this->_addCustomerAttributeType('customer', $code, $data);
    }
    
    /**
     * @param array $attributesData
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCustomerAttributes(array $attributesData)
    {
        foreach ($attributesData as $code => $data) {
            $this->addCustomerAttribute($code, $data);
        }
        return $this;
    }
    
    /**
     * @param string $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeCustomerAttribute($code)
    {
        return $this->_removeCustomerAttributeType('customer', $code);
    }
    
    
    /**************************************************************************/    
    /**************************************************** CUSTOMER / ADDRESS  */    
    /**************************************************************************/
    
    /**
     * @param string $code 
     * @param array $data | Parametros del atributos
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCustomerAddressAttribute($code, $data)
    {
        return $this->_addCustomerAttributeType('customer_address', $code, $data);
    }
    
    /**
     * @param array $attributesData
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function addCustomerAddressAttributes(array $attributesData)
    {
        foreach ($attributesData as $code => $data) {
            $this->addCustomerAddressAttribute($code, $data);
        }
        return $this;
    }
    
    /**
     * @param string $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeCustomerAddressAttribute($code)
    {
        return $this->_removeCustomerAttributeType('customer_address', $code);
    }
    
    /**
     * @param string $type | 'customer'/'customer_address'
     * @param string $code
     * @param array $data
     * 
     * Available Forms by Type
     * 
     *  $type = "customer" 
     *      'adminhtml_checkout', 
     *      'adminhtml_customer', 
     *      'checkout_register', 
     *      'customer_account_create', 
     *      'customer_account_edit',
     * 
     *  $type = "customer_address" 
     *      'adminhtml_customer_address',
     *      'customer_address_edit',
     *      'customer_register_address'
     * 
     * @return \Hd_Base_Model_Resource_Setup
     */
    protected function _addCustomerAttributeType($type, $code, $data)
    {
        // Eav Installer
        $installer = $this->getCustomerSetupModel();
        $installer->addAttribute($type, $code, $data);
        
        $attribute = Mage::getSingleton('eav/config')->getAttribute($type, $code);
        
        // Form Tables
        if ($forms = @$data['forms']) {
            // Forms
            $attribute->setData('used_in_forms', $forms)
                ->save();
        }
        
        // Enterprise Tables
        if (Mage::helper('hd_base')->isEnterprise()) {
            $this->_getEnterpriseCustomerQuoteModel($type)
                ->saveNewAttribute($attribute);
            $this->_getEnterpriseCustomerOrderModel($type)
                ->saveNewAttribute($attribute);
        } else {
            // @todo For non EE instalations creates a 
            // "customer_{attribute_code}" in order table
            
        }
        
        return $this;
    }
    
    /**
     * Elimina Un Atributo de Customer o Customer Address
     * 
     * @param string $type | 'customer'/'customer_address'
     * @param string $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    protected function _removeCustomerAttributeType($type, $code)
    {
        // Eav Attribute
        $attribute = Mage::getSingleton('eav/config')->getAttribute($type, $code);
        
        if (!$attribute->getId()) {
            Mage::log(
                Mage::helper('hd_base')->__('Invalid "%s" attribute with code "%s"',$type,$code),
                1
            );
            // Clear Singleton
            Mage::getSingleton('eav/config')->clear();
            return $this;
        }
        
        // Enterprise Tables
        if (Mage::helper('hd_base')->isEnterprise()) {
            if (!$attribute->isObjectNew()) {
                $this->_getEnterpriseCustomerQuoteModel($type)
                    ->deleteAttribute($attribute);
                $this->_getEnterpriseCustomerOrderModel($type)
                    ->deleteAttribute($attribute);
            }
        } else {
            
            // Removes
            // @todo For non EE instalations creates a 
            // "customer_{attribute_code}" in order table
            
        }
        
        // Eav Installer
        $installer = $this->getCustomerSetupModel();
        $installer->removeAttribute($type, $code);
        
        // Clear Singleton
        Mage::getSingleton('eav/config')->clear();
        
        return $this;
        
    }
    
    /**
     * @param type $type
     * @return type
     */
    protected function _getEnterpriseCustomerQuoteModel($type)
    {
        return ($type == 'customer')
            ? Mage::getModel("enterprise_customer/sales_quote")
            : Mage::getModel("enterprise_customer/sales_quote_address");
    }
    
    /**
     * @param type $type
     * @return type
     */
    protected function _getEnterpriseCustomerOrderModel($type)
    {
        return ($type == 'customer')
            ? Mage::getModel("enterprise_customer/sales_order")
            : Mage::getModel("enterprise_customer/sales_order_address");
    }   
    
    /**
     * @return \Mage_Customer_Model_Resource_Setup
     */
    public function getCustomerSetupModel()
    {
        return new Mage_Customer_Model_Resource_Setup('customer_setup');
    }
    
    
    
    
    /**************************************************************************/
    /******************************************************************* CORE */
    /**************************************************************************/
    
    /**************************************************************************/
    /****************************************** WEBSITE / STORE GROUP / STORE */
    /**************************************************************************/

    /**
     * Create/Update Website Schema (store/group/website)
     * 
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup 
     */
    public function addWebsiteSchema($data)
    {
        $this->_validateWebsiteSchema($data);
        $website = $this->_addWebsite($data['website']);
        $group   = $this->_addGroup($data['group'], $website);
        $store   = $this->_addStore($data['store'], $website, $group);
        // Root Category
        $this->setUpRootCategory($data, $store);

        return $this;
    }
    
    /**
     * Updates the entire Website/Group/Store/ schema
     * 
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup 
     * @example:
     * 
     * Basic Structure:
     * 
     * ['store']
     *     ['code']
     *     ['name']
     * ['group']
     *     ['name']
     *     ['root_category']
     *         ['name']
     * ['website']
     *     ['code']
     *     ['name']
     * 
     * Only the first level keys (store,group,website) will be validated,
     * all nested ones are optional.
     * 
     */
    public function updateDefaultWebsiteSchema($data) 
    {
       $this->_validateWebsiteSchema($data);
        
        // Default Store/Group/Website Update
        // Store Data (StoreView)
        $store = Mage::app()->getDefaultStoreView();
        $storeData = array_merge($store->getData(),$data['store']);
        $store
            ->setData($storeData)
            ->save();
        // Group Data (Store)
        $group = $store->getGroup();
        $groupData = array_merge($group->getData(),$data['group']);
        $group
            ->setData($groupData)
            ->save();
        // Group - Root Category 
        if (isset($data['group']['root_category'])) {
            $rootCategory = Mage::getModel('catalog/category')->load($group->getRootCategoryId());
            $rootCategoryData = array_merge($rootCategory->getData(), $data['group']['root_category']);
            $rootCategory
                ->setData($rootCategoryData)
                ->setStoreId(0)
                ->save();
        }
        // Website Data
        $website     = $store->getWebsite();
        $websiteData = array_merge($website->getData(), $data['website']);
        $website
            ->setData($websiteData)
            ->save();
        return $this;
    }
    
    /**
     * @param array $data
     * @return \Hd_Base_Model_Resource_Setup
     */
    protected function _validateWebsiteSchema($data)
    {
        if (!isset($data['store'])) {
            Mage::throwException(__METHOD__ . ' -"store" key must be defined.');
        }
        if (!isset($data['group'])) {
            Mage::throwException(__METHOD__ . ' - "group" key must be defined.');
        }
        if (!isset($data['website'])) {
            Mage::throwException(__METHOD__ . ' - "website" key must be defined.');
        }
        return $this;
    }

    
    /**
     * Creates/Updates Website / Finds by 'code'
     * 
     * @param array $data
     * @return Mage_Core_Model_Website
     */
    protected function _addWebsite($data)
    {
        // Website
        $website = $this->loadWebsite($data['code']);
        $websiteData = (!$website->getId()) 
            ? array_merge($this->_defaultWebsiteData, $data) 
            : array_merge($website->getData(), $data);
        $website
            ->setData($websiteData)
            ->save()
            ;
        return $website;
    }
    
    /**
     * Removes Website / Finds by 'code'
     *  
     * @param string $code
     * @return \Hd_Base_Model_Resource_Setup
     */        
    public function removeWebsite($code)
    {
        $website = $this->loadWebsite($code);
        if ($website->getId()) {
            // Delete Stores
            foreach ($website->getStores() as $store) {
                /* @var $store Mage_Core_Model_Store */
                $store->delete();
            }
            // Delete StoreGroups
            foreach ($website->getGroups() as $group) {
                /* @var $group Mage_Core_Model_Store_Group */
                $group->delete();
            }
            // Finaly the Website
            $website->delete();
        }
        Mage::app()->reinitStores();
        return $this;
    }

    /**
     * @param string $value
     * @param string $field
     * @return Mage_Core_Model_Website
     */
    public function loadWebsite($value = null, $field = 'code')
    {
        return Mage::getModel('core/website')->load($value,$field);
    }
    
    
    /**
     * Creates/Updates StoreGroup / Finds by 'name'
     * 
     * @param array $data
     * @param Mage_Core_Model_Website $website
     * @return Mage_Core_Model_Store_Group 
     */
    protected function _addGroup($data,Mage_Core_Model_Website $website)
    {
        // Store Group
        $group = $this->loadGroup($data['name']);
        $groupData = (!$group->getId())
            ? array_merge($this->_defaultGroupData, $data)
            : array_merge($group->getData(), $data);        
        $group
            ->setData($groupData)
            ->setWebsiteId($website->getId())
            ->save();        
        return $group;
    }
    
    /**
     * Removes StoreGroup / Finds by 'name'
     * 
     * @param type $name
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeGroup($name)
    {
        /* @var $group Mage_Core_Model_Store_Group */
        $group = $this->loadGroup($name);
        if ($group->getId()) {
            // Delete Stores
            foreach ($group->getStores() as $store) {
                /* @var $store Mage_Core_Model_Store */
                $store->delete();
            }
            // Delete StoreGroups
            $group->delete();
        }
        
        // Por si las moscas
        Mage::app()->reinitStores();
        
        return $this;
    }
    
    /**
     * @param string $value
     * @param string $field
     * @return Mage_Core_Model_Store_Group 
     */
    public function loadGroup($value, $field = 'name')
    {
        return Mage::getModel('core/store_group')->load($value,$field);
    }
    
    
    /**
     * Creates/Updates / Finds by 'name'
     * 
     * @param array $data
     * @param Mage_Core_Model_Website $website
     * @param Mage_Core_Model_Store_Group $group
     * @return \Mage_Core_Model_Store 
     */
    protected function _addStore($data,Mage_Core_Model_Website $website, Mage_Core_Model_Store_Group $group)
    {
        // Store Group
        $store = $this->loadStore($data['code']);
        $storeData = ($store->getId())
            ? array_merge($store->getData(), $data)
            : array_merge($this->_defaultStoreData, $data);
        $store 
            ->setData($storeData)
            ->setWebsiteId($website->getId())
            ->setGroupId($group->getId())
            ->save()
            ;
        return $store;
    }
    
    /**
     * Removes Store / Finds by 'code'
     * 
     * @param type $code
     * @return \Hd_Base_Model_Resource_Setup
     */
    public function removeStore($code)
    {
        /* @var $store Mage_Core_Model_Store */
        $store = $this->loadStore($code);
        $store->delete();
        
        // Por si las moscas
        Mage::app()->reinitStores();
        
        return $this;
    }
    
    /**
     *
     * @param string $value
     * @param string $field
     * @return Mage_Core_Model_Store 
     */
    public function loadStore($value, $field = 'code')
    {
        return Mage::getModel('core/store')->load($value,$field);
    }
    
    
    
    /**
     * @param string $value 
     * @param string $field
     * @return Mage_Catalog_Model_Category
     */
    public function loadCategory($value = null, $field = 'name')
    {
        $model = Mage::getModel('catalog/category');
        if ($value) {
            if ($field == 'id' || $field == 'entity_id') {
                $model->load($value);
            } else {
                $collection = $model->getCollection();
                /*@var $collection Mage_Catalog_Model_Resource_Category_Collection */
                $collection
                    ->setStoreId($this->_getStoreId())
                    ->addAttributeToFilter($field,array('eq' => $value))
                    ;
                $model->load($collection->getFirstItem()->getEntityId());
            }
        }
        return $model;
    }
    
    /**
     * Creates/Updates RootCategory
     * 
     * @param array $data
     * @param Mage_Core_Model_Store $store
     * @return \Hd_Base_Model_Resource_Setup 
     */
    public function setUpRootCategory($data,  Mage_Core_Model_Store $store)
    {
        $this->_currentStoreId = $store->getId();
        $group = $store->getGroup();
        if (!isset($data['group']['root_category']) && !$group->getRootCategoryId()) {
            Mage::throwException('A Root Category must be defined.');
        }
        if (isset($data['group']['root_category'])) {
            $categoryData = array_merge(
                $data['group']['root_category'],
                array('store_id' => $store->getId())
            );
            $rootCategory = (isset($data['root_category']['update']) && $data['root_category']['update'])
                ? $this->_addRootCategory($categoryData, $group->getRootCategoryId())
                : $this->_addRootCategory($categoryData);
            // Category -> StoreGroup
            $group
                ->setRootCategoryId($rootCategory->getId())
                ->save();
        }
        return $this;
    }
    
    /**
     * Creates/Updates root category, 
     * if $id param is present will be loaded in standard way
     * 
     * @param array $data
     * @param int $id 
     * @return Mage_Catalog_Model_Category
     */
    protected function _addRootCategory($data, $id = null)
    {
        if (!isset($data['name'])) {
            Mage::throwException('Category Name must be defined.');
        }
        $rootCategory = ($id) 
            ? $this->loadCategory($id, 'id')
            : $this->loadCategory($data['name']);
        
        if (!$rootCategory->getId()) {
            $rootCategory
                ->addData(array_merge($this->_defaultRootCategoryData, $data))
                ->setAttributeSetId($rootCategory->getDefaultAttributeSetId())
                ->setPath($this->_getMainRootCategory()->getPath())
                ;
            if (!isset($data['store_id'])) {
                $rootCategory
                    ->setStoreId($this->_getStoreId());
            }
        } else {
            $rootCategory
                ->addData($data);
        }
        
        $rootCategory
            ->save();

        return $rootCategory;
    }
    
    protected function _getMainRootCategory() 
    {
        return Mage::getModel('catalog/category')->load(Mage_Catalog_Model_Category::TREE_ROOT_ID);
    }
    
    
/******************************************************************************/
/************************************************************************ EAV */
/******************************************************************************/
    
    /**
     * @return \Mage_Eav_Model_Entity_Setup
     */
    public function getEavSetupModel()
    {
        return new Mage_Eav_Model_Entity_Setup('eav_setup');
    }

/******************************************************************************/
/********************************************************************** SETUP */
/******************************************************************************/
    
    /**
     * @return type
     */
    protected function _getStoreId() 
    {
        return ($this->_currentStoreId === null) 
            ? $this->_defaultStoreId : $this->_currentStoreId;
    }
    
    /**
     * @param array $config
     * @return \Hd_Base_Model_Resource_Setup
     * @example: Format
     * 
     * ['default']
     *     ['path'] => ['value']
     * ['stores']
     *     ['{store_code}']
     *         ['path'] => ['value']
     * ['websites']
     *     ['{website_code}']
     *         ['path'] => ['value']
     * 
     */
    public function setConfig($config)
    {
        // Default Config
        if (isset($config['default'])) {
            foreach ($config['default'] as $path => $value) {
                $this->setConfigData($path, $value);
            }
        }
        // Website Config
        if (isset($config['websites'])) {
            foreach ($config['websites'] as $websiteCode => $websiteData) {
                $website = $this->loadWebsite($websiteCode);
                if (!$websiteId = $website->getId()) {
                    Mage::throwException('No existe un website con codigo: "'. $websiteCode .'"');
                }
                foreach ($websiteData as $path => $value) {
                    $this->setConfigData($path, $value, 'websites', $websiteId); 
                }                
            }
        }
        // Store Config
        if (isset($config['stores'])) {
            foreach ($config['stores'] as $storeCode => $storeData) {
                $store = $this->loadStore($storeCode);
                if (!$storeId = $store->getId()) {
                    Mage::throwException('No existe un store con codigo: "'. $storeCode .'"');
                }
                foreach ($storeData as $path => $value) {
                    $this->setConfigData($path, $value, 'stores', $storeCode); 
                }
            }
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    public function setEnvironmentConfig()
    {
        switch ($this->_getEnviromentFlag()) {
            case self::MAGE_ENVIRONMENT_DEVELOPMENT:
                $this->setConfigData('admin/security/session_cookie_lifetime',14400);
                break;
            case self::MAGE_ENVIRONMENT_STAGING:
                break;
            case self::MAGE_ENVIRONMENT_PRODUCTION:
                break;
            default:
                break;
                ;
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    protected function _setAsAdmin()
    {
        // ON
        $this->turnOnMaintenanceMode();
        // Force the store to be admin
        if (!Mage::registry('isSecureArea')) {
            Mage::register('isSecureArea', 1);            
        }
        Mage::app()->setUpdateMode(false);
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->_isSetAsAdmin = true;
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    protected function _unsetAsAdmin()
    {
        // Force the store to not be admin
        if (Mage::registry('isSecureArea')) {
            Mage::unregister('isSecureArea');
        }
        // Store
        Mage::app()->setUpdateMode(true);        
        Mage::app()->setCurrentStore(Mage::app()->getDefaultStoreView()->getId());
        // Reinit Fucking Stores
        $this->_reinitStores();
        // asAdmin
        $this->_isSetAsAdmin = false;
        // OFF
        $this->turnOffMaintenanceMode();
    }
    
    /**
     * - Creates DbBackup
     * - Init Environment
     * 
     * @param bool $asAdmin
     * @param bool $makeDbBackup
     * @return \Hd_Base_Model_Resource_Setup
     */
    protected function _initSetup($asAdmin = false, $makeDbBackup = false)
    {
        $this->startSetup();
        if ($asAdmin) {
            $this->_setAsAdmin();
        }
        
        if ($makeDbBackup || $this->_getMakeDbBackupsFlag()) {
            $this->_backupDb();
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    protected function _endSetup($reindex = false)
    {        
                
        if ($this->_isSetAsAdmin) {
            $this->_unsetAsAdmin();
        }
        
        // Seteos de Configuracion segun Entorno
        $this->setEnvironmentConfig();
        
        if ($reindex) {
            // Reindex por si las moscas
            $this->_reindex();            
        }
        
        // Cache
        switch ($this->_getEnviromentFlag()) {
            case self::MAGE_ENVIRONMENT_DEVELOPMENT:
                $this->_disableCache();
                break;
            case self::MAGE_ENVIRONMENT_STAGING:
            case self::MAGE_ENVIRONMENT_PRODUCTION:
            default:
                $this->_refreshCache();
                break;
                ;
        }
        
        // Parent - End Setup
        $this->endSetup();
        
        return $this;
    }    
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */ 
    protected function _reindex()
    {
        $indexer = Mage::getSingleton('index/indexer');
        foreach ($indexer->getProcessesCollection() as $process) {
            $process->load($process->getId());
            if ($process->getStatus() == Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX) {
                Mage::log('Reindexando: ' . $process->getIndexerCode());
                $process->reindexEverything();
            }
        }
        return $this;
    }
    
   
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */ 
    protected function _refreshCache()
    {
        $types    = $this->_getAllCacheTypes();
        $allTypes = Mage::app()->useCache();
        foreach($types as $code => $type) {
            if (!empty($allTypes[$code])) {
                // Clean
                Mage::app()->getCacheInstance()->cleanType($code);
            }
        }
        return $this;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    protected function _disableCache()
    {
        $types    = $this->_getAllCacheTypes();
        $allTypes = Mage::app()->useCache();        
        foreach($types as $code => $type) {
            if (isset($allTypes[$code])) {
                // Disable
                $allTypes[$code] = 0;
                // Clean
                Mage::app()->getCacheInstance()->cleanType($code);
            }
        }
        Mage::app()->saveUseCache($allTypes);
        return $this;
    }
    
    /**
     * @return array
     */
    protected function _getAllCacheTypes()
    {
        return Mage::app()->getCacheInstance()->getTypes();
    }

    /**
     * Redirect
     */
    protected function _redirect($path = null)
    {
        if ($path) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl($path));
        }
        Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl());
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup 
     */
    protected function _reinitStores()
    {
        Mage::app()->reinitStores();
        Mage::app()->getStore()->resetConfig();
        return $this;
    }
    
    /**
     * Returns the environment flag
     * @return string|null
     */
    protected function _getEnviromentFlag()
    {
        return (isset($_SERVER['MAGE_ENV_CONFIG_TYPE'])) ? $_SERVER['MAGE_ENV_CONFIG_TYPE'] : null;
    }
    
    /**
     * @return string|null
     */
    protected function _getMakeDbBackupsFlag()
    {
        return (isset($_SERVER[self::MAKE_DB_BACKUP_FLAG]));
    }
    
    /**
     * @return string
     */
    protected function _getInstallInfo()
    {
        foreach (debug_backtrace(2) as $trace) {
            if (isset($trace['file']) && (strpos($trace['file'], 'install-') > -1
                || strpos($trace['file'], 'upgrade-') > -1)) {
                $filePath = explode('/', $trace['file']);
                $fileName = trim(end($filePath),'.php');
            }
        }
        if (!isset($fileName)) {
            return $this->_resourceName . '-'. (string)$this->_moduleConfig->version;
        }
        $info = $this->_resourceConfig->setup->module . '-'. $fileName;
        $info = $this->_resourceName . '-'. $fileName;
        $info = uc_words(str_replace(array('_','-'), ' ',$info));
        return $info;
    }
    
    /**
     * @return \Hd_Base_Model_Resource_Setup
     */
    protected function _backupDb($force = false)
    {
        /**
         * @var Mage_Backup_Helper_Data $helper
         */
        $helper = Mage::helper('backup');
        
        // Backup Name:
        $name = 'Before' . $this->_getInstallInfo();
        
        try {
            
            $type = Mage_Backup_Helper_Data::TYPE_DB;
            $backupManager = Mage_Backup::getBackupInstance($type)
                ->setBackupExtension($helper->getExtensionByType($type))
                ->setTime(time())
                ->setBackupsDir($helper->getBackupsDir());
            $backupManager->setName($name);
            if (!$force) {
                $collection = Mage::getSingleton('backup/fs_collection');
                foreach ($collection as $backup) {
                    if ($backup->getName() == $backupManager->getName()) {
                        Mage::log('Skiping Backup: ' . $backup->getName());
                        return $this;
                    }
                }
            }
            $backupManager->create();
            
        } catch (Mage_Backup_Exception_NotEnoughFreeSpace $e) {
            $errorMessage = Mage::helper('backup')->__('Not enough free space to create backup.');
        } catch (Mage_Backup_Exception_NotEnoughPermissions $e) {
            Mage::log($e->getMessage());
            $errorMessage = Mage::helper('backup')->__('Not enough permissions to create backup.');
        } catch (Exception  $e) {
            Mage::log($e->getMessage());
            $errorMessage = Mage::helper('backup')->__('An error occurred while creating the backup.');
        }
        
        if (!empty($errorMessage)) {
            Mage::logException(new Exception(__METHOD__ . ' - DETAIL: ' . $errorMessage));
        }
        return $this;
    }
    
    /**
     * Put store into maintenance mode
     *
     * @return bool
     */
    public function turnOnMaintenanceMode()
    {
        $maintenanceFlagFile = $this->getMaintenanceFlagFilePath();
        $result = @file_put_contents($maintenanceFlagFile, '');
        return $this;
    }

    /**
     * Turn off store maintenance mode
     */
    public function turnOffMaintenanceMode()
    {
        $maintenanceFlagFile = $this->getMaintenanceFlagFilePath();
        @unlink($maintenanceFlagFile);
        return $this;
    }
    
    /**
     * Get path to maintenance flag file
     * @return string
     */
    protected function getMaintenanceFlagFilePath()
    {
        return Mage::getBaseDir() . DS . 'maintenance.flag';
    }
    
    
}