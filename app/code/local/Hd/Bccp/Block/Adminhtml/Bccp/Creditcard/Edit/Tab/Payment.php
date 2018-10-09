<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Creditcard_Edit_Tab_Payment 
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('edit_form');
    }
    
    protected $_tierBlockClass = 'hd_bccp/adminhtml_bccp_creditcard_edit_tab_payment_tier';

    protected function _prepareForm()
    {
        $model = $this->getModel();
        /* @var $model Hd_Bccp_Model_Creditcard */
        
        $_helper = $this->_helper();
        $countrySupport = $_helper->isCountrySupportEnable();
        
        $form = new Varien_Data_Form();

        // Defualt Payments - Fieldset
        $fieldset = $form->addFieldset('grouped_payments_fieldset-default', array(
            'legend' => ($countrySupport) ? $this->__('Credit Card Payments Configuration - Default Values')
                : $this->__('Credit Card Payments Configuration')
        ));
        $fieldset->addField('grouped_payments-default', 'text', array(
            'name' => 'grouped_payments[default]',
            'label' => $this->__('Payments'),
            'class' => 'required-entry',
        ));
        // Tier Block
        $form->getElement('grouped_payments-default')->setRenderer(
            $this->getLayout()->createBlock($this->_tierBlockClass, 'payments_tier_block_default',array(
//                'payments'      => $model->getPaymentsByCountry(),
                'creditcard_id' => $model->getCreditcardId(),
                'country_id'    => null,
                'country_name'  => 'Default',
            ))
        );
        
        if ($countrySupport) {
            
            $countries = Mage::getSingleton('hd_bccp/system_config_source_country')->toOptionHash(false);
            $countries = array_intersect_key($countries, array_flip($model->getCountryIds()));
            
            foreach ($countries as $countryId => $countryName) {
                // Country Specific Payments - Fieldset
                $fieldset = $form->addFieldset("grouped_payments_fieldset-{$countryId}", array(
                    'legend' => $this->__('Credit Card Payments Configuration For %s', $countryName)
                ));
                $fieldset->addField("grouped_payments-{$countryId}", 'text', array(
                    'name' => "grouped_payments[{$countryId}]",
                    'label' => $this->__('Available Payments For %s', $countryName),
                    'class' => 'required-entry',
                ));
                // Tier Block Data
                $tierBlock = $this->getLayout()->createBlock($this->_tierBlockClass, "payments_tier_block_{$countryId}",array(
                    'creditcard_id' => $model->getCreditcardId(),
                    'country_id'    => $countryId,
                    'country_name'  => $countryName,
                ));
                $form->getElement("grouped_payments-{$countryId}")->setRenderer($tierBlock);
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