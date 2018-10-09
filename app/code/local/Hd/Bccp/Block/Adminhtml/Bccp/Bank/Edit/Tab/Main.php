<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Bank_Edit_Tab_Main 
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
        $fieldset = $form->addFieldset('bank_main_form', array(
            'legend' => $this->__('Bank Information')
        ));
        // Default Data
        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'class' => 'required-entry',
            'required' => true,
        ));
        $fieldset->addField('description', 'text', array(
            'name' => 'description',
            'label' => $this->__('Description'),
            'required' => false,
        ));
        
        // Countries Support
        if ($_helper->isCountrySupportEnable()) {
            $countries = Mage::getSingleton('hd_bccp/system_config_source_country')->toOptionArray();
            $fieldset->addField('country_id', 'select', array(
                'name'      => 'country_id',
                'label'     => $this->__('Countries'),
                'required'  => true,
                'values'    => $countries,
            ));
        } else {
            $fieldset->addField('country_id', 'hidden', array(
                'name' => 'country_id',
            ));
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
        
        // Bank Mapping Codes
        if($this->_helper()->hasPaymentMethods() && $model->getId()) {
             $fieldset = $form->addFieldset("grouped_method_codes_fieldset", array(
                'legend' => $this->__('Bank Codes Mapping')
            ));
            foreach ($this->_helper()->getBccPaymentMethods() as $methodCode => $methodModel) {
                $countryId = null;
                if($_helper->isCountrySupportEnable()) {
                    $countryId = ($model->getCountryId()) 
                        ? $model->getCountryId() : null;
                }
                $options = $_helper->getBankCodeOptions($methodCode, $countryId);
                $fieldset->addField("grouped_method_codes-{$methodCode}", 'select', array(
                    'name'      => "grouped_method_codes[{$methodCode}]",
                    'label'     => $this->__('"%s" Code For %s', $methodModel->getTitle(), $model->getName()),
                    'required'  => true,
                    'disabled'  => (count($options) > 1) ? false : true,
                    'required'  => true,
                    'class'     => 'validate-select',
                    'values'    => $options,
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