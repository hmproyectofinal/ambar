<?php
class Hd_Nps_Block_Payment_Info_Bcc extends Hd_Bccp_Block_Info_Bcc
{
    /**
     * Payment Method Model (class path)
     */
    protected $_methodModel = 'hd_nps/psp_bcc';
    
    protected $_customTemplate = 'hd_nps/info/bcc.phtml';
    
}
