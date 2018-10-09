<?php
class Hd_Base_Model_Rewrite_Core_Translate
    extends Mage_Core_Model_Translate
{
    protected function _addData($data, $scope, $forceReload=false)
    {
        if (!Mage::helper('hd_base')->isTranslateRewriteActive()) {
            return parent::_addData($data, $scope, $forceReload);
        }
        foreach ($data as $key => $value) {
            if ($key === $value) {
                continue;
            }
            $key    = $this->_prepareDataString($key);
            $value  = $this->_prepareDataString($value);
            if ($scope && isset($this->_dataScope[$key]) && !$forceReload ) {
                /**
                 * Checking previous values
                 */
                $scopeKey = $this->_dataScope[$key] . self::SCOPE_SEPARATOR . $key;
                if (!isset($this->_data[$scopeKey])) {
                    if (isset($this->_data[$key])) {
                        $this->_data[$scopeKey] = $this->_data[$key];
                        /**
                         * Translation conflict. Log? Fix? Tell Magento?
                         */
                        // if (Mage::getIsDeveloperMode()) {
                        //     unset($this->_data[$key]); # NO!!!!
                        // }
                    }
                }
                $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;
                $this->_data[$scopeKey] = $value;
            } else {
                $this->_data[$key]      = $value;
                $this->_dataScope[$key] = $scope;
            }
        }
        return $this;
    }
}

