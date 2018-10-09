<?php
//class Hd_Bccp_Model_Bank extends Mage_Core_Model_Abstract
class Hd_Bccp_Model_Bank extends Hd_Bccp_Model_Abstract
{
    protected $_eventPrefix = 'hd_bccp_bank';
    
    protected $_eventObject = 'bank';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/bank", "bank_id");
    }
    
    public function getMethodCodes()
    {
        if(!is_array($this->getData('method_codes'))) {
            $conn = $this->getResource()->getReadConnection();
            $select = $conn->select()
                ->from('hd_bccp_bank_mapping')
                ->where('bank_id = ?', $this->getBankId());
                ;
            $methodCodes = $conn->fetchAll($select);
            $this->setData('method_codes', $methodCodes);
        }
        return $this->getData('method_codes');
    }
    
    public function loadGroupedMethodCodes()
    {
        if(!is_array($this->getData('grouped_method_codes'))) {
            $groupedMethodCodes = array();
            foreach($this->getMethodCodes() as $code) {
                $bankCode = (is_null($code['code'])) ? '-' : $code['code'];
                $groupedMethodCodes[$code['method']][] = $bankCode;
            }
            $this->setData('grouped_method_codes', $groupedMethodCodes);
        }
        // Flat Data
        foreach($this->getData('grouped_method_codes') as $method => $code) {
            $this->setData("grouped_method_codes-{$method}", $code);
        }
        return $this;
    }
    
}