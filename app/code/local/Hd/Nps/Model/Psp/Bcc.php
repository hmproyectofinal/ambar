<?php
class Hd_Nps_Model_Psp_Bcc extends Hd_Nps_Model_Psp_Cc
    implements Hd_Bccp_Model_Payment_Method_Interface
{
    
    /**************************************************************************/
    /************************************ Mage_Payment_Model_Method_Abstract **/
    /**************************************************************************/    
    
    protected $_code = 'nps_bcc';
    
    /**************************************************************************/
    /******************************** Hd_Bccp_Model_Payment_Method_Interface **/
    /**************************************************************************/
    
    protected $_formBlockType = 'hd_nps/payment_form_bcc';
    
    protected $_infoBlockType = 'hd_nps/payment_info_bcc';
    
    protected $_paymentSource = 'hd_nps/payment_source_bcc';
    
    protected $_paymentSourceType = 'bcc';
    
    protected $_xmlConfigPathSoapWsdlUrl = 'payment/nps_bcc/ws_wsdl_url';
    
    public function getBankCodes($countryId = null)
    {
        return array(
            '00' => 'No Utiliza Código',
        );
    }
    
}