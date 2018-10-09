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



/**
 * Class Mirasvit_Advr_Block_Adminhtml_Order_Plain.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Mirasvit_Advr_Block_Adminhtml_Order_Plain extends Mirasvit_Advr_Block_Adminhtml_Block_Container
{
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setHeaderText(Mage::helper('advr')->__('Orders'));

        return $this;
    }

    protected function prepareChart()
    {
        $this->setChartType('column');

        $this->initChart()
            ->setXAxisType('order')
            ->setXAxisField('increment_id');

        return $this;
    }

    protected function prepareGrid()
    {
        $this->initGrid()
            ->setDefaultSort('base_grand_total')
            ->setDefaultDir('desc')
            ->setPagerVisibility(true)
            ->setRowUrlCallback(array($this, 'rowUrlCallback'));

        return $this;
    }

    public function _prepareCollection()
    {
        $collection = Mage::getModel('sales/order')->getCollection();

        $this->addColumns($collection);
        $this->applyFilter($collection);

        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function getColumns()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash();

        $paymentMethods = Mage::getSingleton('payment/config')->getActiveMethods();
        $paymentMethodOptions = array();

        foreach (array_keys($paymentMethods) as $paymentCode) {
            $paymentMethodOptions[$paymentCode] = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
        }

        $columns = array(
            'increment_id' => array(
                'header' => Mage::helper('advr')->__('Order #'),
                'totals_label' => Mage::helper('advr')->__('Totals'),
            ),

            'invoice_id' => array(
                'header' => Mage::helper('advr')->__('Invoice #'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => array($this, 'invoice'),
                'export_callback' => array($this, 'invoice'),
                'hidden' => true,
            ),

            'customer_firstname' => array(
                'header' => Mage::helper('advr')->__('Firstname'),
                'column_css_class' => 'nobr',
            ),

            'customer_lastname' => array(
                'header' => Mage::helper('advr')->__('Lastname'),
                'column_css_class' => 'nobr',
            ),

            'customer_email' => array(
                'header' => Mage::helper('advr')->__('Email'),
                'column_css_class' => 'nobr',
            ),

            'customer_group_id' => array(
                'header' => Mage::helper('advr')->__('Customer Group'),
                'type' => 'options',
                'options' => $groups,
                'column_css_class' => 'nobr',
            ),

            'customer_taxvat' => array(
                'header' => Mage::helper('advr')->__('Tax/VAT number'),
                'hidden' => true,
            ),

            'created_at' => array(
                'header' => Mage::helper('advr')->__('Purchased On'),
                'type' => 'datetime',
                'column_css_class' => 'nobr',
            ),

            'state' => array(
                'header' => Mage::helper('advr')->__('State'),
                'type' => 'options',
                'options' => Mage::getSingleton('sales/order_config')->getStates(),
                'hidden' => true,
            ),

            'status' => array(
                'header' => Mage::helper('advr')->__('Status'),
                'type' => 'options',
                'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            ),

            'products' => array(
                'header' => Mage::helper('advr')->__('Item(s)'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => array($this, 'products'),
                'export_callback' => array($this, 'products'),
                'hidden' => true,
            ),

            'tracking_number' => array(
                'header' => Mage::helper('advr')->__('Tracking Number'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => array($this, 'trackingNumber'),
                'export_callback' => array($this, 'trackingNumber'),
                'hidden' => true,
            ),

            'method' => array(
                'type' => 'options',
                'header' => Mage::helper('advr')->__('Payment Type'),
                'hidden' => true,
                'options' => $paymentMethodOptions,
                'index' => 'method',
                'filter_index' => 'payment_table.method',
            ),

            'total_qty_ordered' => array(
                'header' => Mage::helper('advr')->__('Quantity Ordered'),
                'type' => 'number',
            ),

            'base_tax_amount' => array(
                'header' => Mage::helper('advr')->__('Tax'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_shipping_amount' => array(
                'header' => Mage::helper('advr')->__('Shipping'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_discount_amount' => array(
                'header' => Mage::helper('advr')->__('Discount'),
                'type' => 'currency',
            ),

            'base_total_refunded' => array(
                'header' => Mage::helper('advr')->__('Refunded'),
                'type' => 'currency',
            ),

            'base_total_paid' => array(
                'header' => Mage::helper('advr')->__('Paid'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_total_invoiced' => array(
                'header' => Mage::helper('advr')->__('Total Invoiced'),
                'type' => 'currency',
                'hidden' => true,
            ),

            'base_grand_total' => array(
                'header' => Mage::helper('advr')->__('Grand Total'),
                'type' => 'currency',
                'chart' => true,
            ),

            'gross_profit' => array(
                'header' => Mage::helper('advr')->__('Gross Profit'),
                'type' => 'currency',
                'chart' => false,
            ),
        );

        $columns['actions'] = array(
            'header' => 'Actions',
            'hidden' => true,
            'actions' => array(
                array(
                    'caption' => Mage::helper('advr')->__('View'),
                    'callback' => array($this, 'rowUrlCallback'),
                ),
            ),
        );

        return $columns;
    }

    /**
     * Filter collection.
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    private function applyFilter($collection)
    {
        $filterData = $this->getFilterData();
        $collection->addFieldToFilter('created_at', array('gteq' => $filterData->getFromLocal()))
            ->addFieldToFilter('created_at', array('lteq' => $filterData->getToLocal()));

        if (count($filterData->getStoreIds())) {
            $collection->getSelect()
                ->where('main_table.store_id IN('.implode(',', $filterData->getStoreIds()).')');
        }
    }

    /**
     * Join additional tables to collection to add new columns.
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    private function addColumns($collection)
    {
        $collection->getSelect()->columns(
            array('gross_profit' => new Zend_Db_Expr(
                'IF(
                    main_table.base_total_invoiced_cost = 0 OR main_table.base_total_invoiced_cost IS NULL,
                    0,
                    main_table.base_subtotal_invoiced - main_table.base_total_invoiced_cost
                )'
            ))
        );

        $collection->getSelect()->joinLeft(
            array('payment_table' => $collection->getResource()->getTable('sales/order_payment')),
            'payment_table.parent_id = main_table.entity_id',
            array('method')
        );
    }

    public function rowUrlCallback($row)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
    }

    public function invoice($value, $row, $column)
    {
        $adapter = Mage::getSingleton('core/resource');
        $read = $adapter->getConnection('core_read');
        $select = 'SELECT GROUP_CONCAT(increment_id) as increment_id FROM '.$adapter->getTableName('sales/invoice').' WHERE order_id = '.$row->getId();

        return $read->fetchOne($select);
    }

    public function products($value, $row, $column)
    {
        $data = array();
        $collection = $row->getAllVisibleItems();
        foreach ($collection as $item) {
            $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $item->getProductId()));
            $data[] = '<a class="nobr" href="'.$url.'">'
                .$item->getSku()
                .' / '
                .Mage::helper('core/string')->truncate($item->getName(), 50)
                .' / '.intval($item->getQtyOrdered())
                .' × '.Mage::helper('core')->currency($item->getBasePrice())
                .'</a>';
        }

        return implode('<br>', $data);
    }

    public function trackingNumber($value, $row, $column)
    {
        $trackNumbers = array();

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($row);

        foreach ($shipmentCollection as $shipment) {
            foreach ($shipment->getAllTracks() as $trackNumber) {
                $trackNumbers[] = $trackNumber->getNumber();
            }
        }

        return implode('<br>', $trackNumbers);
    }
}
