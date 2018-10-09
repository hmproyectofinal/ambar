<?php

/**
 * @todo FIX UPDATE
 */
class Hd_Base_Model_Resource_Sales_Setup extends Mage_Sales_Model_Resource_Setup
{
    /**
     * Remove entity attribute. Overwritten for flat entities support
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Sales_Model_Resource_Setup
     */
    public function removeAttribute($entityTypeId, $code)
    {
        if (isset($this->_flatEntityTables[$entityTypeId]) &&
            $this->_flatTableExist($this->_flatEntityTables[$entityTypeId]))
        {
            $this->_removeFlatAttribute($this->_flatEntityTables[$entityTypeId], $code);
            $this->_removeGridAttribute($this->_flatEntityTables[$entityTypeId], $code, $entityTypeId);
        } else {
            parent::removeAttribute($entityTypeId, $code, $attr);
        }
        return $this;
    }

    /**
     * Remove an attribute as separate column in the table
     * The sales setup class doesn't support it by default
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @return Mage_Sales_Model_Mysql4_Setup
     */
    protected function _removeFlatAttribute($table, $attribute)
    {
        $this->getConnection()->dropColumn($this->getTable($table), $attribute);
        return $this;
    }

    /**
     * Remove attribute from grid
     *
     * @param string $table
     * @param string $attribute
     * @param array $attr
     * @param string $entityTypeId
     * @return Mage_Sales_Model_Mysql4_Setup
     */
    protected function _removeGridAttribute($table, $attribute, $entityTypeId)
    {
        if (in_array($entityTypeId, $this->_flatEntitiesGrid)) {
            $this->getConnection()->dropColumn($this->getTable($table . '_grid'), $attribute);
        }
        return $this;
    }
}