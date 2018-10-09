<?php
class Hd_Base_Helper_Date extends Hd_Base_Helper_Data
{
    const ZEND_DATE_FORMAT_DB_DATE       = 'y-MM-dd';
    const ZEND_DATE_FORMAT_DB_DATETIME   = 'y-MM-dd HH:mm:ss';
    const ZEND_DATE_FORMAT_DB_TIME       = 'HH:mm:ss';
    const ZEND_DATE_FORMAT_XSD_DATETIME  = 'y-MM-ddTHH:mm:ss';
    
    // @todo Create Some Nice Datetime formtas
    const ZEND_DATE_FORMAT_TEXT_FULL  = 'y-MM-ddTHH:mm:ss';
    
    /**
     * Creates & Returns a Zend_Date object from a YYYY-MM-DD String
     * 
     * @param string $date
     * @param Zend_Locale $locale
     * @return Zend_Date
     */
    public function getDate($date = null, $locale = null)
    {
        $locale = (is_null($locale))
            ? Mage::app()->getLocale()->getLocale()
            : $locale;
        return new Zend_Date($date, self::ZEND_DATE_FORMAT_DB_DATE, $locale);
    }
    
    /**
     * Creates & Returns a Zend_Date object from a YYYY-MM-DD HH:MM:SS String
     * 
     * @param string $date
     * @param Zend_Locale $locale
     * @return Zend_Date
     */
    public function getDatetime($date = null, $locale = null)
    {
        $locale = (is_null($locale))
            ? Mage::app()->getLocale()->getLocale()
            : $locale;
        return new Zend_Date($date, self::ZEND_DATE_FORMAT_DB_DATETIME, $locale);
    }
        
    /**
     * Creates & Returns a Zend_Date object based on $store NOW Date 
     * If $includeTime is TRUE sets the actual time too.
     * 
     * @param int $store
     * @param bool $includeTime
     * @return Zend_Date
     */
    public function getNowStore($store = null, $includeTime = false)
    {
        $store = (is_null($store)) ? $this->getCurrentStoreId() : $store;
        return Mage::app()->getLocale()->storeDate($store, null, $includeTime);
    }
    
    /**
     * Returns a String with the NOW Date of the selected Store 
     * in YYYY-MM-DD format
     * 
     * @param int $store
     * @return string
     */
    public function getNowStoreDate($store = null)
    {
        return $this->formatDate($this->getNowStore($store));
    }
    
    /**
     * Returns a String with the NOW Datetime of the selected Store 
     * in YYYY-MM-DD HH:MM:SS format
     * 
     * @param int $store
     * @return string
     */
    public function getNowStoreDatetime($store = null)
    {
        return $this->formatDatetime($this->getNowStore($store, true));
    }
    
    /**
     * Returns a String with the NOW Time of the selected Store 
     * in HH:MM:SS format
     * 
     * @param int $store
     * @return string
     */
    public function getNowStoreTime($store = null)
    {
        return $this->formatTime($this->getNowStore($store, true));
    }
    
    /**
     * Creates & Returns a Zend_Date object based on UTC NOW Date 
     * If $includeTime is TRUE sets the actual time too.
     * 
     * @param int $store
     * @param bool $includeTime
     * @return Zend_Date
     */
    public function getNowUtc($store = null, $includeTime = false)
    {
        $store = (is_null($store)) ? $this->getCurrentStoreId() : $store;
        $date = $this->getNowStoreDatetime($store);
        return Mage::app()->getLocale()->utcDate($store, $date, $includeTime);
    }
    
    /**
     * Returns a String with the NOW (UTC) Date
     * in YYYY-MM-DD format
     * 
     * @param int $store
     * @return string
     */
    public function getNowUtcDate($store = null)
    {
        return $this->formatDate($this->getNowUtc($store));
    }
    
    /**
     * Returns a String with the NOW (UTC) Datetime
     * in YYYY-MM-DD HH:MM:SS format
     * 
     * @param int $store
     * @return string
     */
    public function getNowUtcDatetime($store = null)
    {
        return $this->formatDatetime($this->getNowUtc($store, true));
    }
    
    /**
     * Returns a String with the NOW (UTC) Time
     * in HH:MM:SS format
     * 
     * @param int $store
     * @return string
     */
    public function getNowUtcTime($store = null)
    {
        return $this->formatTime($this->getNowUtc($store, true));
    }
    
    /**
     * Returns a Zend_Date with the UTC equivalent date 
     * for the passed $date in $store
     * 
     * @param string $date
     * @param int $store
     * @param bool $includeTime
     * @return Zend_Date
     */
    public function getUtc($date, $store = null, $includeTime = false)
    {
        $store = (is_null($store)) ? $this->getCurrentStoreId() : $store;
        $date = ($includeTime) 
            ? $this->formatDatetime($this->getDatetime($date))
            : $this->formatDate($this->getDate($date));
        return Mage::app()->getLocale()->utcDate($store, $date, $includeTime);
    }
    
    /**
     * Returns a String with the UTC equivalent date for the passed $date in $store
     * in YYYY-MM-DD Format
     * 
     * @param string $date | 
     * @param type $store
     * @return type
     */
    public function getUtcDate($date, $store = null)
    {
        return $this->formatDate($this->getUtc($date,$store));
    }
    
    /**
     * Returns a String with the UTC equivalent datetime for the passed $date in $store
     * in YYYY-MM-DD HH:MM:SS Format
     * 
     * @param type $date
     * @param type $store
     * @return type
     */
    public function getUtcDatetime($date ,$store = null)
    {
        return $this->formatDatetime($this->getUtc($date,$store,true));
    }
    
    /**
     * @return Mage_Core_Model_Date
     */
    public function getDateModel()
    {
        return Mage::getModel('core/date');
    }
    
    /**
     * Returns Current Store Id
     * 
     * @return int
     */
    public function getCurrentStoreId()
    {
        return Mage::app()->getStore()->getId();
    }
    
    /**
     * Returns a String with YYYY-MM-DD format of the given Zend_Date
     * 
     * @param Zend_Date $date
     * @return string
     */
    public function formatDate(Zend_Date $date)
    {
        return $date->toString(self::ZEND_DATE_FORMAT_DB_DATE);
    }
    
    /**
     * Returns a String with YYYY-MM-DD HH:MM:SS format of the given Zend_Date
     * 
     * @param Zend_Date $date
     * @return string
     */
    public function formatDatetime(Zend_Date $date)
    {
        return $date->toString(self::ZEND_DATE_FORMAT_DB_DATETIME);
    }
    
    /**
     * Returns a String with YYYY-MM-DDTHH:MM:SS format of the given Zend_Date
     * 
     * @param Zend_Date $date
     * @return string
     */
    public function formatXsdDatetime(Zend_Date $date)
    {
        return $date->toString(self::ZEND_DATE_FORMAT_XSD_DATETIME);
    }
    
    /**
     * Returns a String with HH:MM:SS format of the given Zend_Date
     * 
     * @param Zend_Date $date
     * @return string
     */
    public function formatTime(Zend_Date $date)
    {
        return $date->toString(self::ZEND_DATE_FORMAT_DB_TIME);
    }
    
    /**
     * Perfoms a basic fromt/to dates Validation
     * 
     * @param type $from
     * @param type $to
     * @param type $store
     * @param type $useUtc
     * @return boolean
     */
    public function validateFromTo($from, $to, $store = null, $useUtc = false)
    {
        if(is_null($to)) {
            return false;
        }
        $now = ($useUtc) ? $this->getNowUtc($store) : $this->getNowStore($store);
        $to = ($useUtc) ? $this->getUtc($to) : $this->getDate($to);        
        if ($from) {
            $from = ($useUtc) ? $this->getUtc($from) : $this->getDate($from);
        }        
        if ($now->isLater($to)) {
            return false;
        }        
        if ($from) {
            if ($now->isEarlier($from)) {
                return false;
            }
        }        
        return true;
    }
    
}