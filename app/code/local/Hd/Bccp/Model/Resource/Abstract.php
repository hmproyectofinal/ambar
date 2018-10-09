<?php
abstract class Hd_Bccp_Model_Resource_Abstract 
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    protected $_storeTable;
    
    public function getStoreTable()
    {
        return $this->_storeTable;
    }
    
    protected function _getLoadSelect($field, $value, $object)
    {
        $storeTable     = $this->getStoreTable();
        $entityTable    = $this->getMainTable();
        $entityIdField  = $this->getIdFieldName();
        
        $select = parent::_getLoadSelect($field, $value, $object);
        // Store Ids
        $select->joinLeft(
            array('store_table' => $storeTable)
            ,"{$entityTable}.{$entityIdField} = store_table.{$entityIdField}"
            , array(
                'store_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT store_table.store_id ORDER BY store_table.store_id)')
            )
        );
        return $select;
    
    }
    
    protected function _afterLoad(\Mage_Core_Model_Abstract $object)
    {
        // Prepare Store Ids
        $storeIds = $object->getData('store_ids')
            ? explode(',', $object->getData('store_ids'))
            : array();
        $object->setData('store_ids', $storeIds);
        
        // Store Ids Flag
        if (count($storeIds) > 1 || (count($storeIds) && $storeIds[0] != '0')) {
            $object->setData('store_ids_flag', 1);
        }
        
        parent::_afterLoad($object);
    }
    
    protected function _afterSave(\Mage_Core_Model_Abstract $object)
    {
        $storeTable     = $this->getStoreTable();
        $entityIdField  = $this->getIdFieldName();
        $id             = $object->getData($entityIdField);
        
        // Countries Relation
        if ($storeIds = $object->getStoreIds()) {
            // Prepare Data
            $insertData = array();
            foreach ($storeIds as $storeId) {
                $insertData[] = array(
                    $entityIdField  => $id,
                    'store_id'      => $storeId,
                );
            }
            // Delete
            $this->_getWriteAdapter()->delete(
                $storeTable,
                array(
                    "{$entityIdField} = ?" => $id
                )
            );
            // Insert
            $this->_getWriteAdapter()
                ->insertMultiple($storeTable, $insertData);
        }
        
        return parent::_afterSave($object);
    }
    
}

