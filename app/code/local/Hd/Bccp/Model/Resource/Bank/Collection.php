<?php

class Hd_Bccp_Model_Resource_Bank_Collection 
    extends Hd_Bccp_Model_Resource_Collection_Abstract
{
    protected $_mainTableAlias = 'lpb';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/bank");
    }
    
    public function addGroupBy()
    {
        $this->getSelect()->group('lpb.bank_id');
        return $this;
    }
    
    public function addCreditcardFilter($creditcardIds)
    {
        $creditcardIds = (!is_array($creditcardIds))
            ? array($creditcardIds) : $creditcardIds;
        
        $select = $this->getSelect();
        $select->joinLeft(
            array('lpbc' => 'hd_bccp_bank_creditcard')
            ,'lpb.bank_id = lpbc.bank_id'
            ,array(
                'creditcard_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT lpbc.creditcard_id ORDER BY lpbc.creditcard_id)')
            )
        );        
        // Creditcards
        $select
            ->where('lpbc.creditcard_id IN(?)', $creditcardIds)
            ->group('lpbc.bank_id');
        
        return $this;
    }
    
    public function addCountryFilter($countryId)
    {
        $this->addFieldToFilter('country_id',array(
            array('eq'  => $countryId), 
            array('null'=> true) // All Countries
        ));
        return $this;
    }
    
    public function addMethodFilter($method)
    {
        $this->setMethodCode($method);
        
        $select = $this->getSelect();
        $select->joinLeft(
            array('lpbm' => 'hd_bccp_bank_mapping')
            ,'lpb.bank_id = lpbm.bank_id'
            ,array('gateway_code'=> 'lpbm.code')
        );
        // Method Code
        $select->where('lpbm.method = ?', $method)
            ->where('lpbm.code IS NOT NULL');
        
        return $this;
    }
    
}
