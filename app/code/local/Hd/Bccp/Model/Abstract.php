<?php
abstract class Hd_Bccp_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Id del Pais
     * @var string 
     */
    protected $_countryId;
    
    /**
     * Codigo del Metodo de Pagos o "Gateway"
     * @var string 
     */
    protected $_methodCode;
    
    /**
     * Setea el ID del Pais Actual
     * @return string
     */
    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;
        return $this;
    }
    
    /**
     * Setea el codigo del Metodo de Pagos o "Gateway"
     * @return strig
     */
    public function setMethodCode($method)
    {
        $this->_methodCode = $method;
        return $this;
    }
    
    public function hasCountryId()
    {
        return ($this->_countryId) ? $this->_countryId : false; 
    }
    
    public function hasMethodCode()
    {
        return ($this->_methodCode) ? $this->_methodCode : false;
    }
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("hd_bccp/$key")
            : Mage::helper("hd_bccp");
    }
}