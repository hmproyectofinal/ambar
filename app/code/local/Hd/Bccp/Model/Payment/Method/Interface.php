<?php
interface Hd_Bccp_Model_Payment_Method_Interface 
{
    public function getPaymentSource();
    
    public function getPaymentSourceType();
    
    public function getMerchantCode();
    
    public function getBankCodes($countryId = null);
    
    public function getCreditcardCodes($countryId = null);
    
}
