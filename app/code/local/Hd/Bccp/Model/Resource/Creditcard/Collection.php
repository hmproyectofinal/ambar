<?php

class Hd_Bccp_Model_Resource_Creditcard_Collection 
    extends Hd_Bccp_Model_Resource_Collection_Abstract
{
    protected $_mainTableAlias = 'lpc';
    
    protected $_loadPayments;
    
    protected $_loadBanks;
    
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard");
    }
    
    /**
     * Setea el Flag para que despues de cargar la collection 
     * le agregue los pagos relacionados
     * 
     * @return \Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addPaymentsToResult()
    {
        $this->_loadPayments = true;
        return $this;
    }
    
    /**
     * Setea el Flag para que despues de cargar la collection 
     * le agregue los bancos relacionados
     * 
     * @return \Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addBanksToResult()
    {
        $this->_loadBanks = true;
        return $this;
    }
    
    /**
     * Agrega como filtro:
     * 
     * - Tarjetas Asignadas al Pais: $country
     * 
     * @param type $countryId
     * @return \Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addCountryFilter($countryId = null)
    {
        $countryId = ($countryId) ? $countryId : $this->_countryId;
        $select = $this->getSelect();
        $select->joinLeft(
            array('lpcc' => 'hd_bccp_creditcard_country')
            ,'lpc.creditcard_id = lpcc.creditcard_id'
            ,array()
        );
        if($countryId) {
            $select->where('lpcc.country_id = ?', $countryId);
        }
        return $this;
    }
    
    /**
     * Agrega como filtro:
     * 
     * - Tarjetas Mapeadas a $method para $country
     * 
     * @param type $method
     * @param type $countryId
     * @return \Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addMethodFilter($method, $countryId = null)
    {
        $this->setMethodCode($method);
        
        $countryId = ($countryId) ? $countryId : $this->_countryId;
        
        $joinCondition  = "lpc.creditcard_id = lpcm.creditcard_id";
        $joinCondition .= " AND lpcm.method = '{$method}'";
        if($countryId) {
            $joinCondition .= " AND lpcm.country_id = '{$countryId}'";
        }
        
        $select = $this->getSelect();
        $select->joinLeft(
            array('lpcm' => 'hd_bccp_creditcard_mapping')
            ,$joinCondition
            ,array('gateway_code' => 'lpcm.code')
        );
        
        // MAPPED FILTER
        $select->where('lpcm.code IS NOT NULL');
        
        return $this;
    }
    
    /**
     * Agrega como filtro los bancos:
     * 
     * - Relacionados a la Tarjeta 
     * - Mapeados al Metodo "$method"
     * - Disponibles para el pais $country
     * 
     * @param type $method
     * @param type $countryId
     * @return \Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addBankRelationFilter($method) 
    { 
        $countryId = $this->_countryId;
        $storeIds  = $this->_storeIds;
        
        $select = $this->getSelect();
        $select->joinLeft(
            array('lpbc' => 'hd_bccp_bank_creditcard')
            ,'lpc.creditcard_id = lpbc.creditcard_id'
            ,array()
        );
        
        $select->joinLeft(
            array('lpb' => 'hd_bccp_bank')
            ,'lpbc.bank_id = lpbc.bank_id'
            ,array()
        );
        
        // Bank - Method Code Mapping
        $select->joinLeft(
            array('lpbm' => 'hd_bccp_bank_mapping')
            ,"lpbc.bank_id = lpbm.bank_id AND lpbm.method = '{$method}' AND lpbm.code IS NOT NULL"
            ,array()
        );
            
        // Bank Relation Where
        if($this->_isCountrySupportEnable() && $countryId) {
            $select->where("lpb.country_id = '{$countryId}' OR lpb.country_id IS NULL");
        }
        
        // Bank Store Relation Filter
        if($this->_isStoreSupportEnable() && $storeIds) {
            $select->joinLeft(
                array('lpbs' => 'hd_bccp_bank_store')
                ,"lpbc.bank_id = lpbs.bank_id"
                ,array()
            );
            $select->where("lpbs.store_id IN(?)", $storeIds);
        }
        
        return $this;
    }
    
    /**
     * @todo  IMPORTANTE!!!
     * 
     * Implementar Caso de Promociones
     * 
     * - Quitar Join y Agregar Un "creditcard_id IN X"
     * - donde X es un MERGE entre los "creditard_id" de "hd_bccp_creditcard_payment"
     * y los que se encuentren en las promociones
     * 
     * En otras Palabras: si hay una promocion para una tarjeta pero no tiene configuracion de cuotas no se va a ver
     * hay que levantar de la tabla de promociones los ids de tarjetas con promociones activas.
     * 
     * @param type $countryId
     */
    public function addPaymentRelationFilter($countryId = null, $allowDefaults = true)
    {
        $countryId = ($countryId) ? $countryId : $this->_countryId;
        
        $conn = $this->getConnection();
        $stm = $conn->select()
            ->from('hd_bccp_creditcard_payment', array('creditcard_id'))
            ->group('creditcard_id')
            ;
        if($countryId) {
            $stm->where('country_id = ?',$countryId);
        }
        if($allowDefaults) {
            $stm->orWhere('country_id IS NULL');
        }
        
        $creditcardIds = $conn->fetchCol($stm);
        
        // @TODO Agregar Ids de tarjetas en promociones a $creditcardIds
        
        $this->addFieldToFilter('lpc.creditcard_id', array('in' => $creditcardIds));
        
        return $this;
        
    }
    
    public function addGroupBy()
    {
        $this->getSelect()->group('lpc.creditcard_id');
        return $this;
    }
    
    /**
     * Devuelve la Coleccion de Tarjetas:
     * - Habilitadas (Mapeados al methodo)
     * - Asociadas a Bancos Habilitados (Mapeados al methodo)
     * - Con Planes de Cuotas
     * 
     * @return Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function addBccAvailabilityFilter($method, $countryId = null, $storeId = null)
    {
        $this->setCountryId($countryId);
        
        if($storeId) {
            $this->addStoreFilter($storeId);
        }
        
        $this
            // CountryFilter
            ->addCountryFilter()
            // Method Code Filter
            ->addMethodFilter($method)
            // Bank Filter
            ->addBankRelationFilter($method)
            // Payment Filter
            ->addPaymentRelationFilter()
            // Que lindo Grupete
            ->addGroupBy();
        
//        Zend_Debug::dump($this->getSelectSql(true));
        
        return $this;
    }
    
    public function addCcAvailabilityFilter($method, $countryId = null, $storeId = null)
    {
        $this->setCountryId($countryId);
        
        if($storeId) {
            $this->addStoreFilter($storeId);
        }
        
        $this
            // CountryFilter
            ->addCountryFilter()
            // Method Code Filter
            ->addMethodFilter($method)
            // Payment Filter
            ->addPaymentRelationFilter()
            // Que lindo Grupete
            ->addGroupBy();
        
        return $this;
    }
    
    /**
     * @return Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    protected function _afterLoad()
    {
        // Append Payments
        if($this->_loadPayments === true) {
            $this->_loadPayments();
        }
        // Append Banks
        if($this->_loadBanks === true) {
            $this->_loadBanks();
        }
        return parent::_afterLoad();
    }
    
    
    protected function _loadPayments()
    {
        $paymentCollection = $this->_getPaymentResourceCollection();
        $paymentCollection
            ->addCreditcardFilter($this->getAllIds());

        if($this->_countryId) {
            $paymentCollection
                ->addCountryFilter($this->_countryId);
        }
        
        if ($paymentCollection->count()) {
            // Payments By 
            $groupedPayments = array();
            foreach($paymentCollection as $payment) {
                $groupedPayments[$payment->getCreditcardId()][] = $payment;
            }
            foreach ($groupedPayments as $creditcardId => $creditcardPayments) {
                $this->getItemById($creditcardId)
                    ->setData('payments', $creditcardPayments);
            }
        }
        return $this;
    }
    
    protected function _loadBanks()
    {
        $bankCollection = $this->_getBankResourceCollection();
        // Add Country Filter
        if ($this->_countryId) {
            $bankCollection->addCountryFilter($this->_countryId);
        }
        // Add Country Filter
        if ($this->_storeIds) {
            $bankCollection->addStoreFilter($this->_storeIds);
        }
        // Add Method Filter
        if ($this->_methodCode) {
            $bankCollection->addMethodFilter($this->_methodCode);
        }
        
        // Add Creditcard Filter
        $bankCollection->addCreditcardFilter($this->getAllIds());
        
        foreach ($bankCollection as $bank) {
            
            $creditcardIds = explode(',',$bank->getData('creditcard_ids'));
            $bank->setData('creditcard_ids', $creditcardIds);
            
            foreach($creditcardIds as $creditcardId) {
                $this->getItemById($creditcardId)->addBank($bank);
            }
        }
        
        return $this;
        
    }
    
    public function getAllIds()
    {
        if (!$this->isLoaded()) {
            $idsSelect = clone $this->getSelect();
            $idsSelect->reset(Zend_Db_Select::ORDER);
            $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $idsSelect->reset(Zend_Db_Select::COLUMNS);
            $idsSelect->columns($this->getResource()->getIdFieldName(), $this->_mainTableAlias);
            return $this->getConnection()->fetchCol($idsSelect);
        }
        // Pa'no volver a pegarle a la db
        return array_keys($this->_items);
    }
    
    /**
     * @return Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    protected function _getPaymentResourceCollection()
    {
        return Mage::getResourceModel('hd_bccp/creditcard_payment_collection');
    }
    
    /**
     * @return Hd_Bccp_Model_Resource_Bank_Collection
     */
    protected function _getBankResourceCollection()
    {
        return Mage::getResourceModel('hd_bccp/bank_collection');
    }
    
}
