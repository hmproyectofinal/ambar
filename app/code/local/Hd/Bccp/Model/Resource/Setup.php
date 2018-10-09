<?php
class Hd_Bccp_Model_Resource_Setup extends Hd_Base_Model_Resource_Setup
{
    public function resetAll()
    {
        $this->startSetup();
        
        $this->_resetConfig()
            ->_createModuleTables();
        
        $this->endSetup();
        
        return $this;
    }
    
    protected function _resetConfig()
    {
        $this->getConnection()->delete(
            'core_config_data', 
            "path LIKE 'hd_bccp/%'"
        );
        return $this;
    }
    
    protected function _createModuleTables()
    {
        return $this->_createBankTables()
            ->_createCreditcardTables()
            ->_createPromoTables();
    }
    
    /**
     * @return \Hd_Bccp_Model_Resource_Setup
     */
    protected function _createBankTables()
    {
        /**********************************************************************/
        /*********************************  Drop/Create table 'hd_bccp/bank'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/bank');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('bank_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'Bank ID')
            ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 2, array(
                'nullable'  => true,
            ), 'country_id')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Credit Card Name')
            ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => true,
            ), 'Description')
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank', 'country_id', 'directory/country', 'country_id')
                , 'country_id'
                , $this->getTable('directory/country'), 'country_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Bank Table');
        $this->getConnection()->createTable($table);


        /**********************************************************************/
        /***************************  Drop/Create table 'hd_bccp/bank_store'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/bank_store');
        if ($this->getConnection()->isTableExists($tableName)) {
                $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('bank_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Bank ID')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable'  => false,
                'unsigned'  => true,
            ), 'Store ID')
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank_store', 'bank_id', 'hd_bccp/bank', 'bank_id')
                ,'bank_id'
                ,$this->getTable('hd_bccp/bank'), 'bank_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank_store', 'store_id', 'core/store', 'store_id')
                ,'store_id'
                ,$this->getTable('core/store'), 'store_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Bank - Store Relation Table');
        $this->getConnection()->createTable($table);        

        /**********************************************************************/
        /*************************  Drop/Create table 'hd_bccp/bank_mapping'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/bank_mapping');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('bank_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Bank ID')
            ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'unsigned'  => true,
                'nullable'  => true,
            ), 'Method Bank ID')
            ->addColumn('method', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Method Code')
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank_mapping', 'bank_id', 'hd_bccp/bank', 'bank_id')
                , 'bank_id'
                , $this->getTable('hd_bccp/bank'), 'bank_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Bank Mapping Table');

        $this->getConnection()->createTable($table);
        
        return $this;
        
    }
    
    /**
     * @return \Hd_Bccp_Model_Resource_Setup
     */
    protected function _createCreditcardTables()
    {
        
        /**********************************************************************/
        /***************************  Drop/Create table 'hd_bccp/creditcard'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/creditcard');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'identity'  => true,
                'primary'   => true,
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Creditcard ID')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Credit Card Name')
            ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => true,
            ), 'Description')
            ->setComment('CreditCard Table');
        $this->getConnection()->createTable($table);
        

        /**********************************************************************/
        /*********************  Drop/Create table 'hd_bccp/creditcard_store'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/creditcard_store');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Promo ID')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable'  => false,
                'unsigned'  => true,
            ), 'Store ID')
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_store', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                ,'creditcard_id'
                ,$this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_store', 'store_id', 'core/store', 'store_id')
                ,'store_id'
                ,$this->getTable('core/store'), 'store_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Creditacard - Store Relation Table');
        $this->getConnection()->createTable($table);


        /**********************************************************************/
        /*******************  Drop/Create table 'hd_bccp/creditcard_country'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/creditcard_country');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Creditcard ID')
            ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 2, array(
                'nullable'  => true,
            ), 'Country ID')
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_country', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                , 'creditcard_id'
                , $this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_country', 'country_id', 'directory/country', 'country_id')
                , 'country_id'
                , $this->getTable('directory/country'), 'country_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('CreditCard - Country Table');    
        $this->getConnection()->createTable($table);


        /**********************************************************************/
        /*******************  Drop/Create table 'hd_bccp/creditcard_mapping'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/creditcard_mapping');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable'  => false,
                'unsigned'  => true,
            ), 'Creditcard ID')
            ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 2, array(
                'nullable'  => true,
            ), 'country_id')
            ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => true,
            ), 'Method Creditcard Code')
            ->addColumn('method', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Method Code')
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_mapping', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                , 'creditcard_id'
                , $this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_mapping', 'country_id', 'directory/country', 'country_id')
                , 'country_id'
                , $this->getTable('directory/country'), 'country_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Creditcard - Mapping Table');
        $this->getConnection()->createTable($table);


        /**********************************************************************/
        /*******************  Drop/Create table 'hd_bccp/creditcard_payment'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/creditcard_payment');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'identity'  => true,
                'primary'   => true,
                'primary' => true,
                'nullable' => false,
            ), 'Payment ID')
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable' => false,
                'unsigned' => true,
            ), 'Credit Card ID')
            ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
                'nullable' => true,
            ), 'country_id')
            ->addColumn('payments', Varien_Db_Ddl_Table::TYPE_INTEGER, 2, array(
                'nullable' => false,
            ), 'Payments')
            ->addColumn('coefficient', Varien_Db_Ddl_Table::TYPE_FLOAT, '1,4', array(
                'nullable' => false,
            ), 'Coefficient')
            ->addColumn('note', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable' => true,
            ), 'Note')
            ->addIndex(
                $this->getIdxName('hd_bccp/creditcard_payment', array('creditcard_id', 'country_id', 'payments'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
                , array('creditcard_id', 'country_id', 'payments'),
                array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_payment', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                , 'creditcard_id'
                , $this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/creditcard_payment', 'country_id', 'directory/country', 'country_id')
                , 'country_id'
                , $this->getTable('directory/country'), 'country_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Creditcard - Payments Table');
        $this->getConnection()->createTable($table);

        /**********************************************************************/
        /**********************  Drop/Create table 'hd_bccp/bank_creditcard'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/bank_creditcard');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('bank_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Bank ID')
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable'  => false,
                'unsigned'  => true,
            ), 'Creditcard ID')
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank_creditcard', 'bank_id', 'hd_bccp/bank', 'bank_id')
                , 'bank_id'
                , $this->getTable('hd_bccp/bank'), 'bank_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/bank_creditcard', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                , 'creditcard_id'
                , $this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Bank - Credicard Relation Table');
        $this->getConnection()->createTable($table);
        
        return $this;
        
    }
    
    /**
     * @return \Hd_Bccp_Model_Resource_Setup
     */
    protected function _createPromoTables()
    {
        
        /**********************************************************************/
        /********************************  Drop/Create table 'hd_bccp/promo'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/promo');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('promo_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'Promo ID')
            ->addColumn('creditcard_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable' => true,
                'unsigned' => true,
            ), 'Credit Card ID')            
            ->addColumn('bank_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => true,
            ), 'Bank ID')            
            ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => false,
                'default'   => '0',
            ), 'Is Active')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Credit Card Name')
            ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => true,
            ), 'Description')
            ->addColumn('payments_pattern', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable' => true,
            ), 'Payments')
            ->addColumn('coefficient', Varien_Db_Ddl_Table::TYPE_FLOAT, '1,4', array(
                'nullable' => true,
                'default' => 1,
            ), 'Coefficient')
            ->addColumn('bank_discount', Varien_Db_Ddl_Table::TYPE_FLOAT, '1,4', array(
                'nullable' => true,
            ), 'Bank Discount')
            ->addColumn('bank_discount_info', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable' => true,
            ), 'Bank Discount Info')
            ->addColumn('active_from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
                'nullable' => true,
            ), 'Active From Date')
            ->addColumn('active_to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
                'nullable' => true,
            ), 'Active To Date')
            ->addColumn('active_from_time', Varien_Db_Ddl_Table::TYPE_VARCHAR, '8', array(
                'nullable' => true,
            ), 'Active From Time')            
            ->addColumn('active_to_time', Varien_Db_Ddl_Table::TYPE_VARCHAR, '8', array(
                'nullable' => true,
            ), 'Active To Time')
            ->addColumn('active_week_days', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable' => true,
            ), 'Active Week Days')
            ->addForeignKey(
                $this->getFkName('hd_bccp/promo', 'bank_id', 'hd_bccp/bank', 'bank_id')
                , 'bank_id'
                , $this->getTable('hd_bccp/bank'), 'bank_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/promo', 'creditcard_id', 'hd_bccp/creditcard', 'creditcard_id')
                , 'creditcard_id'
                , $this->getTable('hd_bccp/creditcard'), 'creditcard_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Promotions Table');
        $this->getConnection()->createTable($table);


        /**********************************************************************/
        /************************  Drop/Create table 'hd_bccp/promo_mapping'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/promo_mapping');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('promo_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Bank ID')
            ->addColumn('merchant_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'unsigned'  => true,
                'nullable'  => true,
            ), 'Method Merchant Code')
            ->addColumn('promo_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'unsigned'  => true,
                'nullable'  => true,
            ), 'Method Merchant Code')
            ->addColumn('method', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                'nullable'  => false,
            ), 'Payment Method Code')
            ->addForeignKey(
                $this->getFkName('hd_bccp/promo_mapping', 'promo_id', 'hd_bccp/promo', 'promo_id')
                , 'promo_id'
                , $this->getTable('hd_bccp/promo'), 'promo_id'
                , Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Promo Mapping Table');
        $this->getConnection()->createTable($table);

        /**********************************************************************/
        /**************************  Drop/Create table 'hd_bccp/promo_store'  */
        /**********************************************************************/

        $tableName = $this->getTable('hd_bccp/promo_store');
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('promo_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'unsigned'  => true,
                'nullable'  => false,
            ), 'Promo ID')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
                'nullable'  => false,
                'unsigned'  => true,
            ), 'Store ID')
            ->addForeignKey(
                $this->getFkName('hd_bccp/promo_store', 'promo_id', 'hd_bccp/promo', 'promo_id')
                ,'promo_id'
                ,$this->getTable('hd_bccp/promo'), 'promo_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->getFkName('hd_bccp/promo_store', 'store_id', 'core/store', 'store_id')
                ,'store_id'
                ,$this->getTable('core/store'), 'store_id'
                ,Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
            )
            ->setComment('Promo - Store Relation Table');
        $this->getConnection()->createTable($table);
        
        return $this;
        
    }
    
}