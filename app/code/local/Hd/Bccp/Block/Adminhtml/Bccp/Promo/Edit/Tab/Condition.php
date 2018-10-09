<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Promo_Edit_Tab_Condition
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('edit_form');
    }

    protected function _prepareForm()
    {
        $_helper = Mage::helper('hd_bccp');
        
        $model = $this->getModel();
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('promo_condition_fieldset', array(
            'legend' => $this->__('Promo Conditions')
        ));

//        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
//        $fieldset->addField('active_from_date', 'date', array(
//            'name'      => 'active_from_date',
//            'label'     => $this->__('Active From'),
//            'title'     => $this->__('Active From'),
//            'format'    => $dateFormatIso,
//            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
//            'class'     => 'required-entry',
//            'required'  => true
//        ));
//        $fieldset->addField('active_to_date', 'date', array(
//            'name'      => 'active_to_date',
//            'label'     => $this->__('Active To'),
//            'title'     => $this->__('Active To'),
//            'format'    => $dateFormatIso,
//            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
//            'class'     => 'required-entry',
//            'required'  => true
//        ));
//        $fieldset->addField('active_from_time', 'time', array(
//            'name'      => 'active_from_time',
//            'label'     => $this->__('Start Hour'),
//            'title'     => $this->__('Start Hour'),
//        ));
//        $fieldset->addField('active_to_time', 'time', array(
//            'name'      => 'active_to_time',
//            'label'     => $this->__('End Hour'),
//            'title'     => $this->__('End Hour'),
//        ));
//
//        $days = Mage::getSingleton('adminhtml/system_config_source_locale_weekdays')->toOptionArray();
//        $fieldset->addField('active_week_days', 'multiselect', array(
//            'name'      => 'active_week_days',
//            'label'     => $this->__('Specific Days'),
//            'values'   => $days,
//        ));
        
        $this->setForm(
            $form->setValues($model)
        );
        return parent::_prepareForm();
    }

}