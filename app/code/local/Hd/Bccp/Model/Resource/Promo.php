<?php
class Hd_Bccp_Model_Resource_Promo extends Hd_Bccp_Model_Resource_Abstract
{
    protected $_storeTable = 'hd_bccp_promo_store';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/promo", "promo_id");
    }
    
    /**
     * Append Map Tables Ids
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        // Gateway Codes
        if ($methodCode = $object->hasMethodCode()) {
            $select->joinLeft(
                array('lppm' => 'hd_bccp_promo_mapping')
                ,'hd_bccp_promo.promo_id = lppm.promo_id'
                , array(
                    'merchant_code' => 'lppm.merchant_code',
                    'promo_code'    => 'lppm.promo_code',
                )
            );
            $select->where('lppm.method = ?', $methodCode);
        }
        return $select;
    }
    
    protected function _afterSave(\Mage_Core_Model_Abstract $object)
    {
        $this
            // Update Method Codes
            ->_updateMethodCodes($object);
        
        return parent::_afterSave($object);
    }
    
    protected function _updateMethodCodes($object)
    {
        if ($methodCodes = $object->getMethodCodes()) {
            // Prepare Data
            $insertData = $methodCodes;
            // Delete All
            $this->_getWriteAdapter()->delete(
                $this->getTable('hd_bccp/promo_mapping'),
                array(
                    'promo_id = ?' =>  $object->getPromoId()
                )
            );
            // Insert
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('hd_bccp/promo_mapping')
                ,$insertData
            );
        }
        return $this;
    }
    
}
