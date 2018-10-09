<?php
class Hd_Base_Helper_Debug extends Mage_Core_Helper_Abstract
{
    
    public function dumpClassMethods($obj, $includeVarien = false, $dType = 'zDump')
    {
        $cm = $this->getClassMethods($obj, $includeVarien);
        switch ($dType) {
            case 'zDump':
                $this->zDump($cm);
                return $this;
            case 'sDump':
                $this->sDump($cm);
                return $this;
            case 'pDump':
                $this->pDump($cm);
                return $this;
            default:
                echo $this->__('Unknown Debug Type "%s"',$dType);
        }
        return $this;
    }
    
    public function getClassMethods($obj, $includeVarien = false)
    {
        $cm = get_class_methods($obj);
        sort($cm);
        if (!$includeVarien) {
            $vm = get_class_methods(new Varien_Object);
            foreach ($cm as $k => $v) {
                if (in_array($v, $vm)) {
                    unset($cm[$k]);
                }
            }
        }
        return $cm;
    }
    
    public function sDump($obj, $format = true)
    {
        if ($format) {
            echo "<pre>";
            var_dump($obj);
            echo "</pre>";
        }
    }
    
    public function zDump($obj)
    {
        Zend_Debug::dump($obj);
    }
    
    public function pDump($obj, $return = false, $format = true)
    {
        $dump = print_r($obj,true);
        
        if ($return) {
            return $dump;
        }
        
        if ($format) {
            echo "<pre>$dump</pre>";
            return $this;
        }
        
        print_r($obj); 
        return $this;
    }
    
    public function getBacktraceString($from = 1, $showParams = false)
    {
        $backtrace  = array_reverse(debug_backtrace());
        array_pop($backtrace);
        $from       = ($from < 1) ? 1 : $from;
        $result     = "BACKTRACE:\n\n";
        $pos        = 0;
        foreach ($backtrace as $trace) {
            if($pos > $from) {
                $result .= "\t{$this->getTraceString($trace, $showParams)}\n";
            }
            $pos++;
        }
        return $result;
    }
    
    public function getTraceString($trace, $showParams = false)
    {
        return ($showParams) 
            ? @"{$trace['class']}::{$trace['function']} @ {$trace['line']}"
            : @"{$trace['class']}::{$trace['function']} @ {$trace['line']}";
    }
    
    public function pSql($str)
    {
        $in = array(
            "FROM",
            "\n JOIN ",
            "\n LEFT JOIN",
            "\n RIGHT JOIN",
            "\n INNER JOIN",
            "ON",
            "AND",
            "WHERE",
            "GROUP",
            "HAVING",
            "ORDER",
            "LIMIT",
            "`, `",
        );
        $out = array(
            "\nFROM",
            "\nJOIN `",
            "\nLEFT JOIN",
            "\nRIGHT JOIN",
            "\nINNER JOIN",
            "\n    ON",
            "\n    AND",
            "\nWHERE",
            "\nGROUP",
            "\nHAVING",
            "\nORDER",
            "\nLIMIT",
            "`,\n    `",
        );
        $str = str_replace($in, $out , (string)$str);
        return "\n --- Pretty Sql DUMP ---\n\n{$str}\n\n --- Pretty Sql DUMP ---\n";
    }
    
    
}
	 