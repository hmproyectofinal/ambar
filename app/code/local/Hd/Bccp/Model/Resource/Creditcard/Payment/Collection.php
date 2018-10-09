<?php
/**
 * @todo Des-Mockear Data Layer
 */
class Hd_Bccp_Model_Resource_Creditcard_Payment_Collection 
    extends Hd_Bccp_Model_Resource_Collection_Abstract
{
    
    protected $_mainTableAlias = 'lpcp';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard_payment");
    }
    
    /**
     * 
     * @param array|int $creditcardIds
     * @return \Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    public function addCreditcardFilter($creditcardIds)
    {
        $creditcardIds = (is_array($creditcardIds)) 
            ? $creditcardIds : array($creditcardIds);
        return $this->addFieldToFilter('lpcp.creditcard_id',array('in' => $creditcardIds));
    }
    
    /**
     * 
     * @param type $countryIds
     * @param bool $addDefaultValues
     * @return \Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    public function addCountryFilter($countryIds, $addDefaultValues = true)
    {
        if(!is_array($countryIds)) {
            $this->setCountryId($countryIds);
        }
        
        $countryIds = (is_array($countryIds))
            ? $countryIds : array($countryIds);
        
        $select = $this->getSelect();
        
        if ($addDefaultValues) {
            $select
                ->where('lpcp.country_id IN(?) OR lpcp.country_id  IS NULL', $countryIds)
                ->group('lpcp.payments');
        } else {
            $select->where('lpcp.country_id IN(?)', $countryIds);
        }
        $select->order('lpcp.payments ASC');
        return $this;
    }
    
    /**
     * AGREGAR ITEMS DEL TIPO Hd_Bccp_Model_Creditcard_Payment
     * CON ID = PROMO-id_promo-payments
     * 
     * @param type $bankId
     * @param type $creditcardId
     * @return \Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    public function addCreditcardBankFilter($creditcardId, $bankId)
    {
        $this->addCreditcardFilter($creditcardId);
        $this->_mergePromoPayments  = true;
        $this->_creditcard_id       = $creditcardId;
        $this->_bankId              = $bankId;
        
        return $this;
    }
    
    protected $_mergePromoPayments;
    
    protected function _afterLoad()
    {
        if($this->_mergePromoPayments) {
            
            $payments = array();
            
            $promoCollection = Mage::getResourceModel('hd_bccp/promo_collection');
            /* @var $promoCollection Hd_Bccp_Model_Resource_Promo_Collection */
            $promoCollection
                ->setMethodCode($this->_methodCode)
                ->addValidityFilter()
                ->addBccFilter($this->_creditcard_id, $this->_bankId)
                ->addMethodCodesToResult()
                ;
            
            if($this->_storeIds) {
                $promoCollection->addStoreFilter($this->_storeIds);
            }
            
            foreach ($promoCollection as $promo) {
                // iteramos para sobresicribir en el caso de dos promos 
                // solapen pagos los cuales Tendrian diferentes IDs
                foreach($promo->getResultPayments() as $key => $payment) {
                    $payments[$key] = $payment;
                }
            }
            
            if(count($payments)) {
                // Clean Standard Payments
                $promoKeys = array_keys($payments);
                foreach($this->_items as $k => $item) {
                    if (in_array($item->getPayments(), $promoKeys)) {
                        $this->removeItemByKey($k);
                    }
                }
                // Add Promo Items
                foreach($payments as $key => $payment) {
                    $this->addItem($payment);
                }
            }
        }
        return parent::_afterLoad();
    }
    
    /**
     * Rewrite to avoid Pproblems
     */
    public function addStoreFilter($storeIds, $appendDefault = true)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }
    
    /**
     * Rewrite to avoid Pproblems
     */
    public function joinStoreTable()
    {
        return $this;
    }
    
}
