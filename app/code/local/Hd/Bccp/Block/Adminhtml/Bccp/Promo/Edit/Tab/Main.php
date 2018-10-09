<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Promo_Edit_Tab_Main 
    extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('edit_form');
    }

    protected function _prepareForm()
    {
        $_helper = $this->_helper();
        
        $model = $this->getModel();
        // Append Method Codes
        $model->loadGroupedMethodCodes();
        
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('promo_main_fieldset', array(
            'legend' => $this->__('Promo Information')
        ));
        
        // Default Data
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $this->__('Name'),
            'title'     => $this->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ));
        
        $fieldset->addField('is_active', 'select', array(
            'name'      => 'is_active',
            'label'     => $this->__('Status'),
            'title'     => $this->__('Status'),
            'required' => true,
            'options'    => array(
                '1' => $this->__('Active'),
                '0' => $this->__('Inactive'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        
        // Stores Support
        if($this->_helper()->isStoreSupportEnable()) {
            
            $stores = Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray();
            $fieldset->addField('store_ids_flag', 'select', array(
                'name'      => 'store_ids_flag',
                'label'     => $this->__('Stores'),
                'options'   => array(
                        '0' => $this->__('All Stores'),
                        '1' => $this->__('Specified'),
                    ),
            ));
            $fieldset->addField('store_ids', 'multiselect', array(
                'name'      => 'store_ids',
                'values'    => $stores,
                'display'   => 'none',
                'required'  => true,
            ));
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap("{$this->_htmlIdPrefix}store_ids_flag", 'store_ids_flag')
                ->addFieldMap("{$this->_htmlIdPrefix}store_ids", 'store_ids')
                ->addFieldDependence('store_ids', 'store_ids_flag', '1')
            );
        }
        
        // Only in Edit Mode
        if ($model->getId()) {
            
            $storeIds = ($_helper->isStoreSupportEnable()) 
                ? $model->getStoreIds() : null;
            
            $fieldset->addField('description', 'textarea', array(
                'name'      => 'description',
                'label'     => $this->__('Description'),
                'required'  => false,
                'note'      => $this->__('Extended information about promo.'),
            ));
            
            // Creditcard Fieldset
            $fieldset = $form->addFieldset('promo_bank_creditcard_fieldset', array(
                'legend' => $this->__('Bank And Creditcard Options')
            ));
            
            $ccOptions = Mage::getSingleton('hd_bccp/system_config_source_creditcard')
                ->toOptionArray(true, null, $storeIds);

            $fieldset->addField('creditcard_id', 'select', array(
                'name'      => 'creditcard_id',
                'label'     => $this->__('Credit Card'),
                'required'  => true,
                'values'    => $ccOptions,
            ));
            $fieldset->addField('payments_pattern', 'text', array(
                'name'      => 'payments_pattern',
                'label'     => $this->__('Payments'),
                'note'      => $this->__('You can use both: %s or %s or %s', '1,2,3,4,5,6', '1-6', '1,2,3-6'),
                'required'  => true,
            ));
            $fieldset->addField('coefficient', 'text', array(
                'name'      => 'coefficient',
                'label'     => $this->__('Coefficient'),
                'note'      => $this->__('Use 1 for flat payments, 1.15 for a 15% surcharge.'),
                'class'     => 'validate-greater-than-zero',
                'required'  => true,
            ));
//            $bankOptions = Mage::getSingleton('hd_bccp/system_config_source_bank')->toOptionArray(true);
            $bankOptions = Mage::getSingleton('hd_bccp/system_config_source_bank')
                ->toOptionArray(true, null, $storeIds);
            $fieldset->addField('bank_id', 'select', array(
                'name'      => 'bank_id',
                'label'     => $this->__('Bank'),
                'required'  => false,
                'values'    => $bankOptions,
            ));
            
            $fieldset->addField('bank_discount', 'text', array(
                'name'      => 'bank_discount',
                'label'     => $this->__('Bank Discount Percent'),
                'note'      => $this->__('This value is used for communication purposes only.'),
                'class'     => 'validate-number',
            ));
            $fieldset->addField('bank_discount_info', 'textarea', array(
                'name'      => 'bank_discount_info',
                'label'     => $this->__('Bank Discount Information'),
                'note'      => $this->__('Extended information about Bank Discount'),
            ));
        }
        
        // Payment Method Codes
        if($this->_helper()->hasPaymentMethods() && $model->getId()) {
            foreach ($this->_helper()->getBccPaymentMethods() as $methodCode => $methodModel) {
                
                $fieldset = $form->addFieldset("grouped_method_codes_fieldset_{$methodCode}", array(
                    'legend' => $this->__('%s - Payment Method - Promo Codes', $methodModel->getTitle())
                ));
                $fieldset->addField("grouped_method_codes-{$methodCode}-merchant_code", 'text', array(
                    'name'      => "grouped_method_codes[{$methodCode}][merchant_code]",
                    'label'     => $this->__('%s - Merchant Code', $methodModel->getTitle()),
                    'required'  => false,
                ));
                $fieldset->addField("grouped_method_codes-{$methodCode}-promo_code", 'text', array(
                    'name'      => "grouped_method_codes[{$methodCode}][promo_code]",
                    'label'     => $this->__('%s - Promotion Code', $methodModel->getTitle()),
                    'required'  => false,
                ));
            }
        }
        
        $this->setForm(
            $form->setValues($model)
        );
        
        return parent::_prepareForm();
    }
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("hd_bccp/$key")
            : Mage::helper("hd_bccp");
    }

}