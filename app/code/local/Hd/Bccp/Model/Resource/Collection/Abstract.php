<?php
class Hd_Bccp_Model_Resource_Collection_Abstract 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    /**
     * Country Id 
     * @var string 
     */
    protected $_countryId;
    
    /**
     * Store Id
     * @var string 
     */
    protected $_storeIds;
    
    /**
     * Codigo del Metodo de Pagos o "Gateway"
     * @var string 
     */
    protected $_methodCode;
    
    /**
     * Main Table Alias
     * @var string 
     */
    protected $_mainTableAlias = '';
    
    /**
     * Store Table Joined Flag
     * @var bool 
     */
    protected $_isStoreTableJoined = false;
    
    /**
     * Store Ids Prepare Flag
     * @var type 
     */
    protected $_addStoreIdsToResult = false;
    
    /**
     * Setea el ID del Pais Actual
     * @return string
     */
    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;
        return $this;
    }
    
    /**
     * Setea el codigo del Metodo de Pagos o "Gateway"
     * @return strig
     */
    public function setMethodCode($method)
    {
        $this->_methodCode = $method;
        return $this;
    }
    
    public function getStoreTable()
    {
        return $this->getResource()->getStoreTable();
    }
    
    public function joinStoreTable()
    {
        if(!$this->_isStoreTableJoined) {
            $entityIdField = $this->getResource()->getIdFieldName();
            $this->getSelect()
                ->joinLeft(
                    array('store_table' => $this->getResource()->getStoreTable())
                    ,"{$this->_mainTableAlias}.{$entityIdField} = store_table.{$entityIdField}"
                    , array()
                )
                ->group("{$this->_mainTableAlias}.{$entityIdField}");                    
            // Set Flag
            $this->_isStoreTableJoined = true;
        }
        return $this;
    }

    public function addStoreFilter($storeIds, $appendDefault = true)
    {
        // Validate Empty
        if (is_null($storeIds) || empty($storeIds)) {
            return $this;
        }
        
        // to Array
        $storeIds = (is_array($storeIds)) ? $storeIds : array($storeIds);
        // Case Only Default Value is present
        if(count($storeIds) == 1 && $storeIds[0] == '0') {
            return $this;
        }
        // Append Default Store
        if ($appendDefault) {
            if(!in_array('0', $storeIds)) {
                $storeIds[] = '0';
            }
        }
        
        // Set Store Ids
        $this->_storeIds = $storeIds;
         
        // Join Store Table
        $this->joinStoreTable()
            ->getSelect()
            ->where('store_table.store_id IN(?)', $storeIds);
            
//        Mage::log(__METHOD__);
//        Mage::log('Store Ids:');
//        Mage::log($storeIds);
//        Mage::log('Store Table:   ' . $this->getResource()->getStoreTable());
//        Mage::log('Id Field Name: ' . $entityIdField);
//        Mage::log('Main Table:    ' . $this->getMainTable());
//        Mage::log(Mage::helper('hd_base/debug')->pSql($this->getSelect()));
//        Mage::log("-------------------------------------------\n");
        
        return $this;
    }
    
    public function addStoreIdsToResult()
    {
        $this->joinStoreTable()
            ->addFieldToSelect( 
                new Zend_Db_Expr('GROUP_CONCAT(DISTINCT store_id ORDER BY store_id)'),
                'store_ids'
            );
        $this->_addStoreIdsToResult = true;
        return $this;
    }
    
//    public function count()
//    {
//        if($this->isLoaded()) {
//            return parent::count();
//        }
//        $query = $this->getSelectCountSql()->reset(Zend_Db_Select::GROUP);
//        $count = (int) $this->getConnection()->fetchOne($query);
//        
//Mage::log(__METHOD__ . " COUNT: {$count}");
//Mage::log(Mage::helper('hd_base/debug')->pSql($this->getSelectCountSql()));
//        
//        return $count;
//    }
    
    protected function _afterLoad()
    {
        if($this->_addStoreIdsToResult) {
            foreach($this as $item) {
                $item->setData('store_ids', explode(',' ,$item->getData('store_ids')));
            }
        }
        return parent::_afterLoad();
    }

    protected function _initSelect()
    {
        $this->getSelect()->from(array($this->_mainTableAlias => $this->getMainTable()));
        return $this;
    }
    
    protected function _isCountrySupportEnable()
    {
        return $this->_helper()->isCountrySupportEnable();
    }
    
    protected function _isStoreSupportEnable()
    {
        return $this->_helper()->isStoreSupportEnable();
    }
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = 'data')
    {
        return Mage::helper("hd_bccp/$key");
    }
    
}
