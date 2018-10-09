<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_TransactionalEmail_Model_Resource_Estadotemplate extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
     {
         $this->_init('transactionalemail/estadotemplate', 'id');
     }
}