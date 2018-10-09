<?php

class Ecloud_TransactionalEmail_Block_Adminhtml_Estadotemplate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() {
		parent::__construct();
		$this->setId("estadotemplateGrid");
		$this->setDefaultSort("id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection() {
		$collection = Mage::getModel("transactionalemail/estadotemplate")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$this->addColumn("id", array(
			"header" => Mage::helper("transactionalemail")->__("ID"),
			"align" =>"right",
			"width" => "50px",
			"type" => "number",
			"index" => "id",
			));

		$this->addColumn("estado_code", array(
			"header" => Mage::helper("transactionalemail")->__("Codigo de Estado"),
			"index" => "estado_code",
			));
		$this->addColumn("email_template", array(
			"header" => Mage::helper("transactionalemail")->__("Template de Email"),
			"index" => "email_template",
			"type"	=> "text",
			"renderer" => "transactionalemail/adminhtml_estadotemplate_renderer_template",
			));

		if (!Mage::app()->isSingleStoreMode()) {
		    $this->addColumn('store_id', array(
		        'header'        => Mage::helper('transactionalemail')->__('Store View'),
		        'index'         => 'store_id',
		        'type'          => 'store',
		        'store_all'     => true,
		        'store_view'    => true,
		        'sortable'      => true,
		        'filter_condition_callback' => array($this,
		            '_filterStoreCondition'),
		    ));
		}

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return $this->getUrl("*/*/edit", array("id" => $row->getId()));
	}

	protected function _filterStoreCondition($collection, $column){
	    if (!$value = $column->getFilter()->getValue()) {
	        return;
	    }
	    $this->getCollection()->addStoreFilter($value);
	}
}