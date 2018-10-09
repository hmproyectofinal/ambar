<?php
class Hd_Bccp_Model_Resource_Bank extends Hd_Bccp_Model_Resource_Abstract
{
    protected $_storeTable = 'hd_bccp_bank_store';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/bank", "bank_id");
    }
    
    /**
     * Append Map Tables Ids
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        // Creditcard Ids
        $select->joinRight(
            array('pbc' => 'hd_bccp_bank_creditcard')
            ,'hd_bccp_bank.bank_id = pbc.bank_id'
            , array(
                'creditcard_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pbc.creditcard_id ORDER BY pbc.creditcard_id)')
            )
        );
        // Gateway Code
        if ($methodCode = $object->hasMethodCode()) {
            $select->joinLeft(
                array('lpbm' => 'hd_bccp_bank_mapping')
                ,'hd_bccp_bank.bank_id = lpbm.bank_id'
                , array('gateway_code' => 'lpbm.code')
            );
            $select->where('lpbm.method = ?', $methodCode);
        }
        return $select;
    }
    
    protected function _afterSave(\Mage_Core_Model_Abstract $object)
    {
        $this
            // Update Related Creditcards
            ->_updateCreditcards($object)
            // Update Method Codes
            ->_updateBankCodes($object);
        
        return parent::_afterSave($object);
    }
    
    /**
     * @param \Mage_Core_Model_Abstract $object
     * @return \Hd_Bccp_Model_Resource_Bank
     */
    protected function _afterLoad(\Mage_Core_Model_Abstract $object)
    {
        // CreditcardIds
        $creditcardIds = $object->getData('creditcard_ids')
            ? explode(',', $object->getData('creditcard_ids'))
            : array();
        $object->setData('creditcard_ids', $creditcardIds);
        
        return parent::_afterLoad($object);
    }
    
    protected function _updateCreditcards($object)
    {
        $id = $object->getBankId();
        if ($creditcardIds = $object->getCreditcardIds()) {            
            // Prepare Data
            $insertData = array();
            foreach ($creditcardIds as $creditcardId) {
                $insertData[] = array(
                    'bank_id' => $id,
                    'creditcard_id' => $creditcardId,
                );
            }
            // Delete All
            $this->_getWriteAdapter()->delete(
                $this->getTable('hd_bccp/bank_creditcard'),
                array(
                    'bank_id = ?' => $id
                )
            );
            // Insert
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('hd_bccp/bank_creditcard')
                ,$insertData
            );
        }
        return $this;
    }
    
    protected function _updateBankCodes($object)
    {
        $id = $object->getBankId();
        if ($methodCodes = $object->getMethodCodes()) {
            // Prepare Data
            $this->_prepareMethodCodes($methodCodes, $object);
            $insertData = $methodCodes;
            // Delete All
            $this->_getWriteAdapter()->delete(
                $this->getTable('hd_bccp/bank_mapping'),
                array(
                    'bank_id = ?' => $id
                )
            );
            // Insert
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('hd_bccp/bank_mapping')
                ,$insertData
            );
        }
        return $this;
    }
    
    protected function _prepareMethodCodes(&$methodCodes, $object)
    {
        $id = $object->getBankId();
        foreach($methodCodes as $k => $method) {
            if(!isset($method['bank_id'])) {
                $methodCodes[$k]['bank_id'] = $id;
            }
        }
        return $this;
    }
    

    
}
