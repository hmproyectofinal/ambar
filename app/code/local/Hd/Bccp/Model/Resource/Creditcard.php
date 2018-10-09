<?php
class Hd_Bccp_Model_Resource_Creditcard extends Hd_Bccp_Model_Resource_Abstract
{
    protected $_storeTable = 'hd_bccp_creditcard_store';
        
    protected $_uniqueHashes = array();
    
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard", "creditcard_id");
    }
    
    /**
     * Append Map Tables Ids
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        // Bank Ids
        $select->joinLeft(
            array('lpbc' => 'hd_bccp_bank_creditcard')
            ,'hd_bccp_creditcard.creditcard_id = lpbc.creditcard_id'
            , array(
                'bank_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT lpbc.bank_id ORDER BY lpbc.bank_id)')
            )
        );
        // Country Ids
        $select->joinLeft(
            array('lpcc' => 'hd_bccp_creditcard_country')
            ,'hd_bccp_creditcard.creditcard_id = lpcc.creditcard_id'
            , array(
                'country_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT lpcc.country_id ORDER BY lpcc.country_id)')
            )
        );
        // Gateway Code
        if ($methodCode = $object->hasMethodCode()) {
            $select->joinLeft(
                array('lpcm' => 'hd_bccp_creditcard_mapping')
                ,'hd_bccp_creditcard.creditcard_id = lpcm.creditcard_id'
                , array('gateway_code' => 'lpcm.code')
            );
            if ($countryId = $object->hasCountryId()) {
                $select->where('lpcm.country_id = ?',$countryId);
            }
            $select->where('lpcm.method = ?', $methodCode);
        }
        return $select;
    }
    
    /**
     * @param Hd_Bccp_Model_Creditcard $object
     * @return Hd_Bccp_Model_Resource_Creditcard
     */
    protected function _afterLoad(\Mage_Core_Model_Abstract $object)
    {
        // Prepare BankIds
        $bankIds = $object->getData('bank_ids')
            ? explode(',', $object->getData('bank_ids'))
            : array();
        $object->setData('bank_ids', $bankIds);
        
        // Prepare Country Ids
        $countryIds = $object->getData('country_ids')
            ? explode(',', $object->getData('country_ids'))
            : array();
        $object->setData('country_ids', $countryIds);
        
        return parent::_afterLoad($object);
        
    }
    
    /**
     * 
     * @param Hd_Bccp_Model_Creditcard $object
     * @return Hd_Bccp_Model_Resource_Creditcard
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this
            // Update Country Relation
            ->_updateCreditcardCountry($object)
            // Update Method Codes
            ->_updateCreditcardCodes($object)
            // Update Payments
            ->_updateCreditcardPayment($object);
            
        return parent::_afterSave($object);
    }    
    
    
    protected function _getUniqueHash($payment)
    {
        return @md5("{$payment['creditcard_id']} / {$payment['country_id']} / {$payment['payments']}");
    }

    /**
     * Unique Keys Validation
     * 
     * @param array $payment
     * @return boolean
     * @throws Exception
     */
    protected function _validatePayment($payment)
    {
        $helper = Mage::helper('hd_bccp');
        $hash = $this->_getUniqueHash($payment);
        if(in_array($hash, $this->_uniqueHashes)) {
            throw new Exception(Mage::helper('hd_bccp')->__(
                'Duplicated Combination "Credit Card / Country / Payments" for "%s / %s / %s"',
                $helper->getCreditcardName($payment['creditcard_id']),
                (isset($payment['country_id']))
                    ? $helper->getCountryName($payment['country_id']) 
                    : $helper->__('Default'),
                $payment['payments']
            ));
        }
        $this->_uniqueHashes[] = $hash;
        return true;
    }
    
    /**
     * Actualiza los payments Asociados
     * 
     * @param Hd_Bccp_Model_Creditcard $object
     * @return Hd_Bccp_Model_Resource_Creditcard
     */
    protected function _updateCreditcardPayment($object)
    {
        $errors = array();
        $payments = $object->getData('payments');
        if($payments && is_array($payments)) {
            
            // Append CreditcardId
            $this->_preparePayments($payments, $object);
            
            foreach($payments as $payment) {
                try {
                    $model = Mage::getModel('hd_bccp/creditcard_payment');
                    $id = @$payment['payment_id'];
                    if (!empty($id)) {
                        $model->load($id);
                        if(@$payment['delete']) {
                            $model->delete();
                            continue;
                        }
                    }
                    // Validacion de Unique Keys
                    $this->_validatePayment($payment);
                    // Save
                    $model->addData($payment);
                    $model->save();
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        
        // Remove Payments For Other Countries
        $collection = Mage::getResourceModel('hd_bccp/creditcard_payment_collection');
        $collection
            ->addCreditcardFilter($object->getCreditcardId())
            ->addFieldToFilter('country_id', array('notnull' => true))
            ->addFieldToFilter('country_id', array('nin' => $object->getCountryIds()));
        
        if($collection->count()) {
            foreach($collection as $item) {
                $item->delete();
            }
        }
        
        // Errors
        if(count($errors) > 0) {
            throw new Exception(implode(" - ", $errors));
        }
        
        return $this;
    }
    
    /**
     * Actualiza la relacion creditcard / country
     * 
     * @param Hd_Bccp_Model_Creditcard $object
     * @return Hd_Bccp_Model_Resource_Creditcard
     */
    protected function _updateCreditcardCountry($object)
    {
        $id = $object->getCreditcardId();
        // Countries Relation
        if ($countryIds = $object->getCountryIds()) {
            // Prepare Data
            $insertData = array();
            foreach ($countryIds as $countryId) {
                $insertData[] = array(
                    'creditcard_id' => $id,
                    'country_id'    => $countryId,
                );
            }
            // Delete
            $this->_getWriteAdapter()->delete(
                $this->getTable('hd_bccp/creditcard_country'),
                array(
                    'creditcard_id = ?' => $id
                )
            );
            // Insert
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('hd_bccp/creditcard_country')
                ,$insertData
            );
        }
        return $this;
    }
    
    /**
     * Actualiza los Codigos para cada Metodo de Pago
     * 
     * @param Hd_Bccp_Model_Creditcard $object
     * @return Hd_Bccp_Model_Resource_Creditcard
     */
    protected function _updateCreditcardCodes($object)
    {
        $id = $object->getCreditcardId();
        if ($methodCodes = $object->getData('method_codes')) {
            $this->_prepareMethodCodes($methodCodes, $object);
            $insertData = $methodCodes;
            // Delete All
            $this->_getWriteAdapter()->delete(
                $this->getTable('hd_bccp/creditcard_mapping'),
                array(
                    'creditcard_id = ?' => $id
                )
            );
            // Insert
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('hd_bccp/creditcard_mapping')
                ,$insertData
            );
        }
        return $this;
    }
    
    protected function _prepareMethodCodes(&$methodCodes, $object)
    {
        $id = $object->getCreditcardId();
        foreach($methodCodes as $k => $method) {
            if(!isset($method['creditcard_id'])) {
                $methodCodes[$k]['creditcard_id'] = $id;
            }
        }
        return $this;
    }
    
    protected function _preparePayments(&$payments, $object)
    {
        $id = $object->getCreditcardId();
        foreach($payments as $k => $payment) {
            if(!isset($payment['creditcard_id'])) {
                $payments[$k]['creditcard_id'] = $id;
            }
        }
        return $this;
    }
            
}
