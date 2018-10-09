<?php
class Hd_Nps_Block_Payment_Info_Cc extends Hd_Bccp_Block_Info_Cc
{
    /**
     * Payment Method Model (class path)
     */
    protected $_methodModel = 'hd_nps/psp_cc';
    
    protected $_customTemplate = 'hd_nps/info/cc.phtml';
    
}
