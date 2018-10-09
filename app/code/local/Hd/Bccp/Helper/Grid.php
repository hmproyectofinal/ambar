<?php
class Hd_Bccp_Helper_Grid extends Hd_Bccp_Helper_Data
{
    public function addGridStoreFilter($collection, $column)
    {
        $filterValue = $column->getFilter()->getCondition();
        $collection->joinStoreTable()->getSelect()
            ->where('store_table.store_id = ?', $filterValue['eq']);
    }
    
    public function addBankGridCountryFilter($collection, $column)
    {
        $field       = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $filterValue = $column->getFilter()->getCondition();
        if(is_array($filterValue) && $filterValue['eq'] == '*') {
            $collection->addFieldToFilter($field, array('null' => true));
        } else {
            $collection->addFieldToFilter($field, $filterValue);
        }
    }
    
    
}
