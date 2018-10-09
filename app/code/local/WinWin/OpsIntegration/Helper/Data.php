<?php

class WinWin_OpsIntegration_Helper_Data extends Mage_Core_Helper_Abstract
{

	public $_winwinUserIs;

	public function checkDate($date)
	{
		//YYYY/MM/DD
		$date = explode('/', $date);
		if (!(is_array($date) && count($date)===3)) {
			return false;
		}
		if (!( strlen($date[0]) === 4 && strlen($date[1]) === 2 && strlen($date[2]) === 2 )) {
			return false;
		}
		$date[1] = ltrim($date[1], '0');
		$date[2] = ltrim($date[2], '0');
		return checkdate($date[1], $date[2], $date[0]);
	}
	
	public function checkSkip($string)
	{
		if ($string === '' || $string === '0') {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function setUserIsCron($cron)
	{
		if ($cron) {
			$this->_winwinUserIs = 'automatic';
		}
		else {
			$this->_winwinUserIs = 'manual';
		}
	}
	
	public function getUser()
	{
		if ($this->_winwinUserIs === 'automatic') {
			return '';
		}
		else {
			$user='';
			if (Mage::getDesign()->getArea() === 'adminhtml') {
				$user = Mage::getSingleton('admin/session')->getUser()->getUsername();
			}
			return $user;
		}
	}
	
	public function getAdminUserName()
	{
		return Mage::getSingleton('admin/session')->getUser()->getUsername();
	}
	
	public function getGeneralContactEmail()
    {
        
    	if (!$generalContactEmail = Mage::getSingleton('core/config_data')->getCollection()->getItemByColumnValue('path', 'trans_email/ident_general/email')) {
            $conf = Mage::getSingleton('core/config')->init()->getXpath('/config/default/trans_email/ident_general/email');
            $generalContactEmail = array_shift($conf);
        } else {
            $generalContactEmail = $generalContactEmail->getValue();
        }

        return (string)$generalContactEmail;
    }    
    
    public function getGeneralContactName()
    {
        if (!$generalContactName = Mage::getSingleton('core/config_data')->getCollection()->getItemByColumnValue('path', 'trans_email/ident_general/name')) {
            $conf = Mage::getSingleton('core/config')->init()->getXpath('/config/default/trans_email/ident_general/name');
            $generalContactName = array_shift($conf);
        } else {
            $generalContactName = $generalContactName->getValue();
        }

        return (string)$generalContactName;
    }


    public function getStockAndPrice($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/file_stock_price');
    	
    }
    

	public function getDebugMode($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/debug_mode');
    }

    //public function getEmailNotificationsToEmails($store_id)
    public function getEmailNotificationsToEmails($website_code)
    {
    	$emails = Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/email_notifications');
    	//$emails = Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/email_notifications', $store_id);
    	$emails = str_replace(' ', '', $emails);
		$emails = explode(',', $emails);
    	return $emails;
    	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    	$emails = array();
        $conf = Mage::getStoreConfig('winwin_ecommerce_opsintegration');
        $conf = $conf['winwin_opsintegration_general_settings'];
        if (isset ($conf['email_notifications']) && !empty ($conf['email_notifications'])) {
            $emails = $conf['email_notifications'];
            $emails = str_replace(' ', '', $emails);
            $emails = explode(',', $emails);            
        }
        
        return $emails;
    }


    public function getCheckFTP($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/check_ftp');
    	
    }

    public function getFTPHost($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_host');
    	
    }

    public function getFTPUser($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_user');
    	
    }

    public function getFTPPassword($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_password');
    	
    }
    
	//public function getStatusUpdate($store_id)
	public function getStatusUpdate($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/status_update');
    	//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/status_update', $store_id);
    }


	public function getIgnoreSpecialPrice($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/ignore_special_price');
    	
    }
    
    //public function getOutOfStock($store_id)
    public function getUpdateStockAvailability($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/update_stock_availability');
    	//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/out_of_stock', $store_id);
    }

    public function getUpdateStockAvailabilityBO($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/update_stock_availability_backorders');
    	//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/out_of_stock', $store_id);
    }
    
	//public function getProjectName($store_id)
	public function getProjectName($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/project_name');
		//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/project_name', $store_id);
	}
    
    //public function getDelimiter($store_id)
    public function getDelimiter($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/field_delimiter');
		//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/field_delimiter', $store_id);
	}
	
	//public function getEnclosure($store_id)
	public function getEnclosure($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/field_enclosure');
		//return Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/field_enclosure', $store_id);
	}
	
	public function getDirectoryLocationWebsiteCode($website_code)
	{
		$defaultFolder = implode(DS, explode('/', Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/directory_location')));
		$defaultFolder = rtrim($defaultFolder, DS);
		return $defaultFolder;
	}
	
	//public function getDirectoryLocation($store_id)
	public function getDirectoryLocation($website_code)
	{
		$defaultFolder = implode(DS, explode('/', Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/directory_location')));
		//$defaultFolder = implode(DS, explode('/', Mage::getStoreConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/directory_location', $store_id)));
		$defaultFolder = rtrim($defaultFolder, DS);
		return $defaultFolder;
	}
	
	//public function getOrderToErp($store_id)
	public function getOrderToErp($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/status_order2erp');
		
	}

	public function getChangeOrderToErpAfter($website_code) {
        
        return (bool)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/status_change_after_integration');
        
    }

    public function getOrderToErpAfter($website_code) {
        
        return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/status_order_after_integration');
        
    }

    public function getExportOrderToFTP($website_code) {
        
        return (bool)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/export_to_ftp');
        
    }

    public function getDniField($website_code) {
        
        return (bool)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/dni_field');
        
    }

    
	public function getOrderExportCollectionLimit($website_code)
	{
		$cl = (int)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/collection_limit');
		return $cl;
		//return ($cl < 100) ? 100 : $cl;		
	}
	
	
	public function getOrderExportPageLimit($website_code)
	{
		
		$pl = (int)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/per_collection');
		return $pl;
		//return ($pl < 100) ? 100 : $pl;		
	}

	public function getOrderExtraData($website_code)
	{
		$extra = Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/extra_data');
		if ( ! $extra || $extra == "" ) return array();

		return explode("|", $extra);		
	}

	public function getOrderFileNameCode($website_code)
	{
		return  Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_orders_integration_settings/file_name_code');
		
	}
	
	public function checkValidOrderState($website_code,$state) {
		$fromConfigStates = Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_states_integration_configuration/status_order2import');
		$valid_states = explode(',', $fromConfigStates);
		return in_array($state, $valid_states);
	}

	public function getFTPConnectioString($ftp_host, $ftp_user, $ftp_pass){
		
    	return sprintf('ftp://%1$s:%2$s@%3$s',$ftp_user,$ftp_pass,$ftp_host);
	}

	public function getFTPDeleteFile($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_delete_file');
    	
    }

    public function getFTPInboundDirectory($website_code)
	{
		$defaultFolder = implode(DS, explode('/', Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_inbound_directory')));
		$defaultFolder = rtrim($defaultFolder, DS);
		return $defaultFolder;
	}

	public function getFTPOutgoingDirectory($website_code)
	{
		$defaultFolder = implode(DS, explode('/', Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_ftp_settings/ftp_outgoing_directory')));
		$defaultFolder = rtrim($defaultFolder, DS);
		return $defaultFolder;
	}

    	//public function getOrderToErp($store_id)
	public function getStatesReserved($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/status_reserved');
	}

	public function getUpdateConfigPrice($website_code)
	{
		return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/update_config_price');		
	}


	public function getStatusesAllowed($website_code)
	{

		return explode(",", Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_shipments_integration_configuration/status_allowed'));
	}


	public function getStatusAfterImport($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_shipments_integration_configuration/status_after_import');
	}

	public function getSendNotification($website_code)
	{
		return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_shipments_integration_configuration/send_notification');		
	}

	public function getGenerateInvoice($website_code)
	{
		return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_shipments_integration_configuration/generate_invoice');		
	}

	public function getMessageTemplate($website_code)
	{
		return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_shipments_integration_configuration/message_template');
	}

	public function getFlushCache($website_code)
    {
    	return (boolean)Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_general_settings/flush_cache');
    	
    }

    public function getStockFilesName($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/stock_files');
    	
    }

    public function getPriceFilesName($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/price_files');
    	
    }
    

    public function getStockDisableCron($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_stock_integration_configuration/stock_stop_automatic_integration');
    	
    }

    public function getPriceDisableCron($website_code)
    {
    	return Mage::app()->getWebsite($website_code)->getConfig('winwin_ecommerce_opsintegration/winwin_opsintegration_prices_integration_configuration/price_stop_automatic_integration');
    	
    }

}
