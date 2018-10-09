<?php
class Hd_Nps_Model_Resource_Setup extends Hd_Bccp_Model_Resource_Setup
{
    public function setStatuses()
    {
        // Statuses
        $statuses = array(
            array(
                'label' => 'NPS Pending Payment',
                'code'  => Hd_Nps_Model_Psp::ORDER_STATUS_PENDING,
                'state' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            ),
            array(
                'label' => 'NPS Payment Canceled',
                'code'  => Hd_Nps_Model_Psp::ORDER_STATUS_CANCELED,
                'state' => Mage_Sales_Model_Order::STATE_CANCELED,
            ),
            array(
                'label' => 'NPS Payment Processed',
                'code'  => Hd_Nps_Model_Psp::ORDER_STATUS_PROCESSED,
                'state' => Mage_Sales_Model_Order::STATE_PROCESSING,
            ),
            array(
                'label' => 'NPS Holded - AVS Error',
                'code'  => Hd_Nps_Model_Psp::ORDER_STATUS_HOLDED_AVS,
                'state' => Mage_Sales_Model_Order::STATE_HOLDED,
            ),
            array(
                'label' => 'NPS Holded - Fraud Error',
                'code'  => Hd_Nps_Model_Psp::ORDER_STATUS_HOLDED_FRAUD,
                'state' => Mage_Sales_Model_Order::STATE_HOLDED,
            ),
        );
        
        return $this->addOrderStatuses($statuses);
        
    }
    
    public function setPaymentAttributes()
    {
        $salesInstaller = $this->getSalesSetupModel();
        
        // Quote Payment
//        $salesInstaller->addAttribute("quote_payment", "nps_payonline_form_data",           array("type"=>"text", "grid"=>"false",));
//        $salesInstaller->addAttribute("quote_payment", "nps_payonline_transaction_data",    array("type"=>"text", "grid"=>"false",));
        
        // Order Payment
        $salesInstaller->addAttribute("order_payment", "nps_payonline_form_data",           array("type"=>"text", "grid"=>"false",));
        $salesInstaller->addAttribute("order_payment", "nps_payonline_transaction_data",    array("type"=>"text", "grid"=>"false",));
        $salesInstaller->addAttribute("order_payment", "nps_payonline_avs_data",            array("type"=>"text", "grid"=>"false",));
        $salesInstaller->addAttribute("order_payment", "nps_payonline_fraud_data",          array("type"=>"text", "grid"=>"false",));
        
        return $this;
        
    }

    public function removeMockData()
    {
        return $this->resetAll();
    }
    
    public function createMockData()
    {
        // Reset Bccp
        $this->resetAll();
        
        // Prepare Data
        $storeIds = $this->_getStoreIds();
        $storeData = array();
        $countryIds = array();
        $currencyCodes = array();
        
        foreach($storeIds as $storeId) {
            
            // Load Configuration
            $countryId      = Mage::getStoreConfig('general/store/information/merchant/country', $storeId);
            $currencyCode   = Mage::getStoreConfig('currency/options/base', $storeId);
            
            // Validation
            if(!$products = $this->_getPsp()->getCountryProduct($countryId, 'cc')) {
                continue;
            }
            
            // Store Data
            $storeData[$storeId] = array(
                'country_id' => $countryId,
                'currency_code' => $currencyCode,
            );
            
            // Add For Bccp Config
            $countryIds[] = $countryId;
            $currencyCodes[] = $currencyCode;
         
            // Add Creditcards
            $this->_addMockCreditcards($storeId, $countryId, $products);
            
        }        
        
        // Save Creditcards
        $this->_saveMockCreditcards();
        
        
        // Iteramos de VUelta los Stores por los Bancos
        foreach ($storeData as $storeId => $data) {
             $this->_addMockBanks($storeId, $data['country_id']);
        }
        
        $this->_saveMockBanks();
        
        // Set Bccp Config Data
        $this->_mockBccpConfig['hd_bccp/country_support/specificcountry'] = 
            implode(',', $countryIds);
        
        $this->setConfig($this->_mockBccpConfig);
        
        // @todo Implement Mock Promos
        
        return $this;
        
    }
    
    protected function _addMockCreditcards($storeId, $countryId, $products)
    {
        // Itarate Product
        foreach($products as $code => $name) {
            // Load CC
            if(!$cc = $this->_getMockCreditcard($code)) {
                $cc = $this->_mockCcSkeleton;
                $cc['name'] = $name;
                $cc['description'] = $name . ' - MOCK CREDITCARD';
            }
            
            // Append Method Codes
            // CC
            $cc['method_codes'][] = array(
                'country_id' => $countryId,
                'code' => $code,
                'method' => 'nps_cc',
            );
            // BCC
            $cc['method_codes'][] = array(
                'country_id' => $countryId,
                'code' => $code,
                'method' => 'nps_bcc',
            );
            $cc['store_ids'][] = $storeId;
            $cc['country_ids'][] = $countryId;  
            
            // Save
            $this->_mockCcs[$code] = $cc;
            
        }
    }
    
    protected function _saveMockCreditcards()
    {
        foreach ($this->_mockCcs as $k => $cc) {
            
            $model = Mage::getModel('hd_bccp/creditcard');
            $model->addData($cc)
                ->save();
            // Set Id
            $this->_mockCcs[$k]['id'] = $model->getId();
        }
        return $this;
    }
    
    protected function _getMockCreditcard($key)
    {
        return (isset($this->_mockCcs[$key]))
            ? $this->_mockCcs[$key] : null;
    }
    
    protected function _getMockBankCreditcardIds($products)
    {
        $ids = array();
        foreach ($products as $key) {
            if($id = @$this->_mockCcs[$key]['id']) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
    
    protected function _addMockBanks($storeId, $countryId)
    {
        $banks = $this->_mockBanksData[$countryId];
        foreach($banks as $bank) {
            $bank['creditcard_ids'] = $this->_getMockBankCreditcardIds($bank['psp_products']);
            $bank['country_id'] = $countryId;
            $bank['store_ids'][] = $storeId;
            $this->_mockBanks[] = $bank;
        }
        return $this;
    }
    
    protected function _saveMockBanks()
    {
        foreach ($this->_mockBanks as $k => $bank) {
            
            $model = Mage::getModel('hd_bccp/bank');
            $model->addData($bank)
                ->save();
            // Set Id
            $this->_mockBanks[$k]['id'] = $model->getId();
        }
        
        return $this;
    }
    
    /**
     * @return Hd_Nps_Model_Psp
     */
    protected function _getPsp()
    {
        return Mage::getSingleton('hd_nps/psp');
    }
    
    protected function _getStoreIds()
    {
        $ids = array();
        $stores = Mage::app()->getStores();
        foreach($stores as $store) {
            $ids[] = $store->getId();
        }
        return $ids;
    }
    
    protected $_mockCcs = array();
    
    protected $_mockBanks = array();
    
    protected $_mockPromos = array();
    
    protected $_mockBccpConfig = array(
        'hd_bccp/country_support/enable' => 1,
        'hd_bccp/country_support/allowspecific' => 1,
        'hd_bccp/country_support/specificcountry' => '',
        'hd_bccp/store_support/enable' => 1,
    );
    
    protected $_mockCcSkeleton = array(
        'id' => 666,
        'description' => 'Mock Creditcard',
        'store_ids' => array(),
        'method_codes' => array(),
        'payments' => array(
            array(
                'payments' => 1,
                'coefficient' => 1,
                'note' => 'Mock Payment NOTE',
            ),
            array(
                'payments' => 3,
                'coefficient' => 1.1,
                'note' => 'Mock Payment NOTE',
            ),
            array(
                'payments' => 6,
                'coefficient' => 1.2,
                'note' => 'Mock Payment NOTE',
            ),
            array(
                'payments' => 9,
                'coefficient' => 1.3,
                'note' => 'Mock Payment NOTE',
            ),
            array(
                'payments' => 12,
                'coefficient' => 1.5,
                'note' => 'Mock Payment NOTE',
            ),
        ),
    );
    
    
    protected $_mockBanksData = array(
        'AR' => array(
            array(
                'name' => 'Santander Rio',
                'description' => 'Santander Rio - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    1,14
                ),
            ),
            array(
                'name' => 'Banco Francés',
                'description' => 'Banco Francés - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    14,5
                ),
            ),
            array(
                'name' => 'Otros Bancos',
                'description' => 'Otros Bancos - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    1,2,5,8,9,10,14,17,20,21,42,43,48,49,50,61,63,65,72,95,110,
                ),
            ),
        ),
        'BR' => array(
            array(
                'name' => 'Banco do Rio',
                'description' => 'Banco do Rio - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    1,2,4,5,14,
                ),
            ),
            array(
                'name' => 'Banco do Mar',
                'description' => 'Banco do Mar - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    101,102,104,105
                ),
            ),
        ),
        'CO' => array(
            array(
                'name' => 'Banco de Bogotá',
                'description' => 'Banco de Bogotá - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    1,2,5
                ),
            ),
            array(
                'name' => 'Banco de Cali',
                'description' => 'Banco de Cali - MOCK BANK',
                'method_codes' => array(
                    array(
                        'code' => '00',
                        'method' => 'nps_bcc',
                    )
                ),
                'psp_products' => array(
                    5,14,106
                ),
            ),
        ),
    );
    
    
}
