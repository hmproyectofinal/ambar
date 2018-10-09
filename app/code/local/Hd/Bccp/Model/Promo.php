<?php
class Hd_Bccp_Model_Promo extends Hd_Bccp_Model_Abstract
{
    protected $_eventPrefix = 'hd_bccp_promo';
    
    protected $_eventObject = 'promo';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/promo", "promo_id");
    }
    
    public function getMethodCodes()
    {
        if(!is_array($this->getData('method_codes'))) {
            $conn = $this->getResource()->getReadConnection();
            $select = $conn->select()
                ->from($this->getResource()->getTable('hd_bccp/promo_mapping'))
                ->where('promo_id = ?', $this->getPromoId());
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
                $groupedMethodCodes[$code['method']] = $code;
            }
            $this->setData('grouped_method_codes', $groupedMethodCodes);
        }
        // Flat Data
        foreach($this->getData('grouped_method_codes') as $method => $codes) {
            foreach($codes as $key => $value) {
                $this->setData("grouped_method_codes-{$method}-{$key}", $value);
            }
        }
        
        return $this;
    }
    
    public function preparePayments()
    {
        if(!$this->getData('payments')) {
            $payments = array();
            $pArray = explode(',',$this->getData('payments_pattern'));
            foreach($pArray as $value) {
                if(strpos($value, '-') > -1) {
                    $slots = explode('-', $value);
                    $i  = $slots[0];
                    $end = $slots[1];
                    while ($i <= $end) {
                        $payments[] = (int)$i;
                        $i++;
                    }
                } else {
                    $payments[] = (int)$value;
                }
            }
            sort($payments);
            $this->setData('payments', $payments);
        }
        return $this;
    }
    
    public function prepareTimeSlot()
    {
        if(!$this->getData('time_slot')) {
            $start  = substr(str_replace(',', ':', $this->getData('active_from_time')),0,5);
            $end    = substr(str_replace(',', ':', $this->getData('active_to_time')),0,5);
            $timeslot = $this->_helper()->__("%s to %s", $start, $end);
            $this->setData('time_slot', $timeslot);
        }
        return $this;
    }

    public function getTimeslot()
    {
        if(!is_array($this->getData('time_slot'))) {
            $this->prepareTimeSlot();
        }
        return $this->getData('time_slot');
    }
    
    // 
    public function getPayments()
    {
        if(!is_array($this->getData('payments'))) {
            $this->preparePayments();
        }
        return $this->getData('payments');
    }
    
    public function getResultPayments()
    {
        if(!$this->getData('result_payments')) {
            $result = array();
            $commonData = array(
                'bank_id'               => $this->getData('bank_id'),
                'creditcard_id'         => $this->getData('creditcard_id'),
                'country_id'            => $this->getData('country_id'),
                'promo_code'            => $this->getData('promo_code'),
                'merchant_code'         => $this->getData('merchant_code'),
                'coefficient'           => $this->getData('coefficient'),
                'promo_data' => array(
                    'name'               => $this->getData('name'),
                    'description'        => $this->getData('description'),
                    'bank_discount'      => $this->getData('bank_discount'),
                    'bank_discount_info' => $this->getData('bank_discount_info'),
                )
            );
            
            foreach($this->getPayments() as $payment)  {
                $id =  "PROMO-{$this->getPromoId()}-$payment";
                $data = array_merge(
                    $commonData,
                    array(
                        'id'          => $id,
                        'payment_id'  => $id,
                        'payments'    => $payment,
                    )
                );
                $model = Mage::getModel('hd_bccp/creditcard_payment');
                $result[$payment] = $model->setData($data);
            }            
            $this->setData('result_payments', $result);            
        }
        
        return $this->getData('result_payments');
        
    }
    
}