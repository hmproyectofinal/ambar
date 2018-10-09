<?php

class Hd_Bccp_Model_Resource_Promo_Collection 
    extends Hd_Bccp_Model_Resource_Collection_Abstract
{
    protected $_mainTableAlias = 'lpp';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/promo");
    }
    
    public function addGroupBy()
    {
        $this->getSelect()->group('lpp.promo_id');
        return $this;
    }
    
    public function addActiveFilter()
    {
        $this->addFieldToFilter('is_active', array('eq' => '1'));
        return $this;
    }

    public function addCountryFilter($countryId)
    {
        return $this;
//        $this->addFieldToFilter('country_id', array('eq' => $countryId));
//        return $this;
    }
    
    public function addValidityFilter(Zend_Date $now = null, $store = null)
    {
        
        $dhelper = Mage::helper('hd_base/date');
        /* @var $dhelper Hd_Base_Helper_Date */
        
        $store  = ($store) ? $store : Mage::app()->getStore()->getId();
        $now    = ($now) ? $now : $dhelper->getNowStore($store,true);
        
        $weekDay = $now->get(Zend_Date::WEEKDAY_DIGIT);
        $dbHour  = $now->toString('HH,mm,ss');
        
        $tbl = $this->_mainTableAlias;
        $this->getSelect()
            ->where("{$tbl}.active_from_date <= ?", $dhelper->formatDate($now))
            ->where("{$tbl}.active_to_date >= ?", $dhelper->formatDate($now))
            ->where("{$tbl}.active_from_time <= ?", $dbHour)
            ->where("{$tbl}.active_to_time = '00,00,00' OR {$tbl}.active_to_time >= ?", $dbHour)
            ->where("{$tbl}.active_week_days IS NULL OR {$tbl}.active_week_days LIKE '%{$weekDay}%'")
            ;
        // Active 
        return $this->addActiveFilter();
    }
    
    public function addBankFilter($bankId)
    {
        $this->addFieldToFilter("{$this->_mainTableAlias}.bank_id", array('eq' => $bankId));
        return $this;
    }
    
    public function addCreditcardFilter($ccId)
    {
        $this->addFieldToFilter("{$this->_mainTableAlias}.creditcard_id", array('eq' => $ccId));
        return $this;
    }
    
    public function addBccFilter($ccId, $bankId)
    {
        $this->addCreditcardFilter($ccId)
            ->addBankFilter($bankId);
        return $this;
    }
    
    protected $_loadMethodCodes;
    
    public function addMethodCodesToResult()
    {
        $this->_loadMethodCodes = true;
        return $this;
    }
    
    protected $_prepareData;
    
    public function addPreparedData()
    {
        $this->_prepareData = true;
        return $this;
    }
    
    protected function _beforeLoad()
    {
        if($this->_loadMethodCodes) {
            if ($method = $this->_methodCode) {
                // Cargamos los codigos del metodo actual
                $select = $this->getSelect();
                $select->joinLeft(
                    array('lppm' => 'hd_bccp_promo_mapping')
                    ,"{$this->_mainTableAlias}.promo_id = lppm.promo_id"
                    // Fields
                    ,array(
                        'merchant_code' => 'lppm.merchant_code',
                        'promo_code'    => 'lppm.promo_code',
                    )
                );
                // Method Code
                $select->where('lppm.method = ?', $method);
            }
        }
        
//        $log = Mage::helper('hd_base/debug')->pSql($this->getSelectSql(true));
//        Mage::log($log);
        
        return parent::_beforeLoad();
    }
    
    protected function _afterLoad()
    {
        // Adaptamos algunos campos para tener vaores mas amigables
        if($this->_prepareData) {
            foreach($this->_items as $item) {
                $item->preparePayments()
                    ->prepareTimeSlot();
            }
        }
        
        // Load Method Codes (Si hace falta)
        if($this->_loadMethodCodes) {
            
        }
        
        return parent::_afterLoad();
    }
    
}
