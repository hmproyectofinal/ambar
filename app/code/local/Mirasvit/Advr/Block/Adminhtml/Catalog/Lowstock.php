<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Reports
 * @version   1.0.3
 * @build     571
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Advr_Block_Adminhtml_Catalog_Lowstock extends Mirasvit_Advr_Block_Adminhtml_Catalog_Abstract
{
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setHeaderText(Mage::helper('advr')->__('Low stock'));

        return $this;
    }

    protected function prepareChart()
    {
        $this->setChartType('column');

        $this->initChart()
            ->setXAxisType('category')
            ->setXAxisField('product_name');

        return $this;
    }

    protected function prepareGrid()
    {
        $this->initGrid()
            ->setDefaultSort('product_stock_qty')
            ->setDefaultDir('asc');

        return $this;
    }

    protected function prepareToolbar()
    {
        $this->initToolbar();

        $this->getToolbar()->getForm()->addField('include_child', 'checkbox', array(
            'name' => 'include_child',
            'label' => Mage::helper('advr')->__('Include child products'),
            'value' => 1,
            'checked' => $this->getIncludeChild(),
        ));

        return $this;
    }

    protected function _prepareCollection()
    {
        Mage::register('ignore_tz', true);
        $collection = Mage::getModel('advr/report_sales')
            ->setBaseTable('catalog/product')
            ->setFilterData($this->getFilterData())
            ->selectColumns('product_id')
            ->selectColumns($this->getVisibleColumns())
            ->groupByColumn('product_id');

        return $collection;
    }

    public function getColumns()
    {
        $columns = array(
            'product_sku' => array(
                'header' => 'SKU',
                'type' => 'text',
                'totals_label' => 'Total',
                'filter_totals_label' => 'Subtotal',
                'link_callback' => array($this, 'rowUrlCallback'),
            ),

            'product_name' => array(
                'header' => 'Product',
            ),

            'product_stock_qty' => array(
                'header' => 'Stock Quantity',
                'type' => 'number',
                'chart' => true,
            ),

            'product_is_in_stock' => array(
                'header' => 'Stock Availability',
                'type' => 'options',
                'options' => Mage::getSingleton('advr/system_config_source_stock')->toOptionHash(),
            ),
        );

        $columns += $this->getBaseProductColumns(true);
        if (isset($columns['sum_item_row_total'])) {
            $columns['sum_item_row_total']['chart'] = false;
        }

        $columns['actions'] = array(
            'header' => 'Actions',
            'actions' => array(
                array(
                    'caption' => Mage::helper('advr')->__('View Product'),
                    'callback' => array($this, 'rowUrlCallback'),
                ),
            ),
        );

        return $columns;
    }

    public function rowUrlCallback($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getData('product_id')));
    }

    public function getIncludeChild()
    {
        if (!$this->getFilterData()->getIncludeChild()) {
            return 0;
        }

        return $this->getFilterData()->getIncludeChild();
    }
}
