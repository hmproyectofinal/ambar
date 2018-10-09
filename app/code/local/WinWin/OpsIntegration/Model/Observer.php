<?php

//echo 'test-observer'; exit;
class WinWin_OpsIntegration_Model_Observer
{
    public function executionHistoryLogger($observer)
    {
       
    	$log = $observer->getData();
    	
    	$rf04 = $log['rf04'];
    	$_fileMoved = $log['file_moved'];
    	$rf03 = $log['rf03'];
    	//$store_id = $log['store_id'];
    	$website_code = $log['website_code'];
    	$file = $log['file'];
    	$_fileName = $log['file_name'];
    	$_executedTimestamp = $log['executed_timestamp'];
    	$_costTime = $log['cost_time'];
    	$_logFilename = $log['log_filename'];
    	$_errorLogFilename = $log['log_error'];
       
        $info = Mage::getModel('winwin_opsintegration/execution_history_info');
        $info->setData($rf04);
  
        try {
            $info->save();
        } catch (Exception $e) {
            Mage::log('Failed to log execution history. Exception message: '.$e->getMessage().'.', null, 'executionHistoryLogger.error.log', true);
            $rf03[] = 'Failed to log execution history. Exception message: '.$e->getMessage().'. Check: var/log/executionHistoryLogger.error.log file';
        }
      
        $_defaultFolder = Mage::helper('winwin_opsintegration/data')->getDirectoryLocation($website_code);
        
        /**
         * **********************  LOG  **********************
         * Code for generating log file
         * 
         */
        $_io = new Varien_Io_File();
		
        $_path = Mage::getBaseDir('base') . DS . $_defaultFolder . DS . 'Logs' . DS . 'Executions' . DS;
		$_name = $_logFilename;
		$_file = $_path . $_name;
		$_io->setAllowCreateFolders(true);
		$_io->open(array('path' => $_path));
		$_io->streamOpen($_file, 'a+');
		$_io->streamLock(true);
		$_logFilenameStr = '';
		$_logFilenameStr .= "\n".'Home Integration: '.$_executedTimestamp."\n\n";
		$_logFilenameStr .= 'File: '.$_fileName."\n"; /* .csv file */
		$_logFilenameStr .= ($rf04['integration_name'] === 'Ordenes_Export')?'Total Orders Exported: ' . $rf04['total_records'] . "\n":'Total Records: ' . $rf04['total_records'] ."\n";
		$_logFilenameStr .= ($rf04['integration_name'] === 'Ordenes_Export')?'Order Quantity Exported: ' . $rf04['records_processed_correctly'] . "\n":'Record Processed OK: ' . $rf04['records_processed_correctly'] . "\n";
		$_logFilenameStr .= ($rf04['integration_name'] === 'Ordenes_Export')?'Number Of Orders Not Exported: ' . ($rf04['total_records'] - $rf04['records_processed_correctly']) .":\n":'Record Processed ERROR: ' . count($rf03) . "\n";
		$_logFilenameStr .= ($rf04['integration_name'] !== 'Ordenes_Export')?'File Moved ' . $_fileMoved . "\n":'';
		$_logFilenameStr .= 'Foreclosure Type: ' . ucfirst(strtolower($rf04['execution_type'])) . "\n";
		$_logFilenameStr .= 'Length: ' . $_costTime."\n\n";
		$_logFilenameStr .= '___________________________________________________________________'."\n";
		$_io->streamWrite($_logFilenameStr);
		$_io->streamUnlock();
		$_io->streamClose();
        
		
		/**
         * **********************  ERROR LOG  **********************
         * Code for generating Error log file
         * 
         */
		if (is_array($rf03) && count($rf03)) {
			$_io = new Varien_Io_File();
	        $_path = Mage::getBaseDir('base') . DS . $_defaultFolder . DS . 'Logs' . DS . 'Errors' . DS;
			$_name = $_errorLogFilename;
			$_file = $_path . $_name;
			$_io->setAllowCreateFolders(true);
			$_io->open(array('path' => $_path));
			$_io->streamOpen($_file, 'a+');
			$_io->streamLock(true);
			$_logFilenameStr = '';
			$_logFilenameStr .= "\n".'Integration: '.$_executedTimestamp."\n\n";
			$_logFilenameStr .= 'File: '.$_fileName."\n"; /* .csv file */
			
			foreach ($rf03 as $rowString) {
				$_logFilenameStr .= $rowString."\n";
			}	
			$_logFilenameStr .= "\n";
			$_logFilenameStr .= '___________________________________________________________________'."\n";
			$_io->streamWrite($_logFilenameStr);
			$_io->streamUnlock();
			$_io->streamClose();
		}
		
	    $emailTemplate  = Mage::getModel('core/email_template')
							->loadDefault('winwin_opsintegration_execution_notification');                                 
	
		$projectName = Mage::helper('winwin_opsintegration')->getProjectName($website_code);
		$subject = 'Integration '.$info->getIntegrationName().' - '.$projectName;
		
		$emailTemplateVariables = array();
		
		$emailTemplateVariables['subject'] = $subject;
		
		$emailTemplateVariables['integration_name'] = $rf04['integration_name']; /* read from execution history table */
		$emailTemplateVariables['executed_at'] = $_executedTimestamp; /* read from execution history table */
		$emailTemplateVariables['processed_file_name'] = $rf04['processed_file_name']; /* read from execution history table */
		$emailTemplateVariables['execution_status'] = $info->getExecutionStatus(); /* read from execution history table */
		$emailTemplateVariables['total_records'] = $rf04['total_records']; /* read from execution history table */
		$emailTemplateVariables['records_processed_correctly'] = $rf04['records_processed_correctly']; /* read from execution history table */
		$emailTemplateVariables['records_processed_incorrectly'] = count($rf03);
		$emailTemplateVariables['file_moved'] = $_fileMoved;
		$emailTemplateVariables['cost_time'] = $_costTime;
		
		
		
		$emailTemplate->setSenderName(Mage::helper('winwin_opsintegration')->getGeneralContactName());
		$emailTemplate->setSenderEmail(Mage::helper('winwin_opsintegration')->getGeneralContactEmail());
		$emailTemplate->setTemplateSubject($subject);        
		
		$toEmails = Mage::helper('winwin_opsintegration')->getEmailNotificationsToEmails($website_code);
		if (!empty ($toEmails)) {
			foreach ($toEmails as $toEmail) {
				try {
					$emailTemplate->send($toEmail, null, $emailTemplateVariables);
				}
				catch (Exception $e) {
					//catch an unsuccessful email
				}        
			}
		}
    }
}
