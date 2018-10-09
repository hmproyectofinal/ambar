<?php
class Hd_Base_Helper_Product extends Hd_Base_Helper_Data
{
    /**
     * @var array
     */
    protected $_attributeOptions = array();
    
    /**
     * Return Product attribute value
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $attribute | Codigo del Atributo 
     * @return string
     */
    public function getAttributeValue(Mage_Catalog_Model_Product $product, $attribute)
    {
        $value = $product->getData($attribute);
        foreach ($this->getAttributeOptions($attribute) as $k => $v) {
            if ($value == $k) {
                return $v;
            }
        }
        return $value;
    }

    /**
     * 
     * @param string $attributeName
     * @param bool $includeEmpty
     * @return array 
     */
    public function getAttributeOptions($attributeName, $includeEmpty = false)
    {
        $options = array();
        foreach ($this->getAttributeAllOptions($attributeName) as $option) {
            if(!isset($option['value']) || !isset($option['label'])) {
                continue;
            }
            if ($option['value'] != '' || $includeEmpty) {
                $options[$option['value']] = $option['label'];
            }
        }
        return $options;
    }
    
    /**
     * 
     * @param string $attributeName
     * @return array
     */
    public function getAttributeAllOptions($attributeName) 
    {
        if (!isset($this->_attributeOptions[$attributeName])) {
            $attribute = Mage::getModel('eav/config')
                ->getAttribute('catalog_product', $attributeName);
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $this->_attributeOptions[$attributeName] = $attribute->getSource()->getAllOptions();
        }
        return $this->_attributeOptions[$attributeName];
    }
    
    /**
     * Get Attributes Set By Name
     * 
     * @param string $name
     * @return Mage_Eav_Model_Resource_Entity_Attribute | null
     */
    public function getAttributeSetByName($name)
    {
        foreach($this->getAttributeSets() as $attributeSet) {
            if ($attributeSet->getAttributeSetName() == $name) {
                return $attributeSet;
            }
        }
        return null;
    }
    
    /**
     * @var Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
     */
    protected $_attributeSets;
    
    /**
     * Returns the Product Attribute Set Collection 
     * 
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
     */
    public function getAttributeSets()
    {
        if(!$this->_attributeSets) {
            $this->_attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load();
        }
        return $this->_attributeSets;
    }
    
    /**
     * Returns all product data grouped by attribute group
     * 
     * Format:
     * ['group_name'] => array
     *     ['attribute_code'] => array
     *         ['label'] => 'Attribute Optios Label'
     *         ['value'] => 'Attribute Option Value'
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array|null $groups | Array to filter groups
     * @param array|null $groupsMap | Array to map Groups Names
     * @param array|null $attributes | Array to filter attributes
     * 
     * @return array
     */
    public function getProductDataByGroups(
        Mage_Catalog_Model_Product $product, 
        array $groups = null,
        array $groupsMap = null, 
        array $attributes = null 
        ) {
        
        $groups     = (!is_null($groups)) ? $groups : array();
        $groupsMap  = (!is_null($groupsMap)) ? $groupsMap : array();
        $attributes = (!is_null($attributes)) ? $attributes : array();
        
        $attributeGroups = $this->getGroupedAttributes($product);
        $result = array();
        foreach($attributeGroups as $group) {
            
            if(count($groups) && !in_array($group['attribute_group_name'], $groups)) {
                continue;
            }
            
            $groupName = (in_array($group['attribute_group_name'], array_keys($groupsMap)))
                    ? $groupsMap[$group['attribute_group_name']]
                    : $group['attribute_group_name'];
            
            foreach ($group['attributes'] as $code => $attribute) {
                
                if(count($attributes) && !in_array($code, $attributes) || !$attribute->getIsVisibleOnFront()) {
                    continue;
                }
                
                switch ($attribute->getFrontendInput()) {
                    case 'select':
                    case 'multiselect':
                        $value = $product->getAttributeText($code);
                        break;
                    case 'text':
                    default:
                        $value = $product->getData($code);
                        break;
                }
                
                $result[$groupName][$code] = array(
                    'label' => $attribute->getStoreLabel(),
                    'value' => $value,
                );
                
            }
        }
        return $result;
    }    
    
    /**
     * @var array
     */
    protected $_groupedAttributeSets = array();
    
    /**
     * Returns a grouped set of attributes (by Set and Group)
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return type
     */
    public function getGroupedAttributes(Mage_Catalog_Model_Product $product)
    {
        $setId = $product->getAttributeSetId();
        
        if(!isset($this->_groupedAttributeSets[$setId])) {
            $groupedAttributes = array();
            foreach($product->getAttributes() as $attribute) {
                $attributeCode  = $attribute->getAttributeCode();
                $groupId        = $attribute->getAttributeGroupId();
                if(!$groupId) {
                    continue;
                }
                // Set Group
                if(!isset($groupedAttributes[$groupId])) {
                    $group = $this->getAttributeGroup($groupId);
                    $groupedAttributes[$groupId] = $group->getData();
                }
                // Add Attribute
                $groupedAttributes[$groupId]['attributes'][$attributeCode] = $attribute;
            }
            $this->_groupedAttributeSets[$setId] = $groupedAttributes;
        }
        return $this->_groupedAttributeSets[$setId];
    }
    
    /**
     * @var array
     */
    protected $_attributeGroups = array();
    
    /**
     * Load and returns an Attribute Group
     * 
     * @param int $groupId
     * @return Mage_Eav_Model_Entity_Attribute_Group
     */
    public function getAttributeGroup($groupId)
    {
        if(!isset($this->_attributeGroups[$groupId])) {
            $this->_attributeGroups[$groupId] = Mage::getModel('eav/entity_attribute_group')
                ->load($groupId);
        }
        return $this->_attributeGroups[$groupId];
    }
    
}
	 