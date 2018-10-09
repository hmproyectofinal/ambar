<?php 

// Colors REF 
// http://stackoverflow.com/questions/3696430/print-colorful-string-out-to-console-with-python?lq=1
    
// Profile
$req     = Mage::app()->getRequest();
$timers  = Varien_Profiler::getTimers();

function formatTime($time)
{
    return str_pad(number_format($time,6), 9, ' ', STR_PAD_LEFT);
}

function formatCounts($counts)
{
    return str_pad($counts, 6, ' ', STR_PAD_LEFT);
}

if(count($timers)) {
    
    $resetColor = "\033[0m";

    $overall = 0;
    if(isset($timers['mage'])) {
        $overall = @$timers['mage'];    
        unset($timers['mage']);
    }
    
    $overallTime = $overall['sum'];
    $overallFTime = formatTime($overallTime);
    
    switch(true) {
        case $overallTime > 2:
            $headerColor = "\033[1;91m"; // RED BOLD
            break;
        case $overallTime > 1:
            $headerColor = "\033[0;91m"; // RED
            break;
        case $overallTime > 0.9:
            $headerColor = "\033[1;33m"; // YELLOW BOLD
            break;
        case $overallTime > 0.5:
            $headerColor = "\033[0;93m"; // YELLOW 
            break;
        default:
            $headerColor = "\033[0;92m"; // GREEN
            break;
    }
    $requestUri     = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : ' -- NO URI -- '; 
    $requestPath    = "{$req->getModuleName()}/{$req->getControllerName()}/{$req->getActionName()}";
    $headerText     = "{$headerColor}\n --- ACTION: '{$requestPath}' - URI: '{$requestUri}'- TIME: {$overallFTime}{$resetColor} ";
    $header         = str_pad($headerText, 130, '-', STR_PAD_RIGHT);
    // START LOG       
    $log = $header;
    
//    $log .= "\nCOOKIE: \n" . print_r($_COOKIE,true);
//    $log .= "\nPARAMS: \n" . print_r($req->getParams(),true);
    
    foreach($timers as $name => $timer) {

        $time  = formatTime($timer['sum']);
        $count = str_pad($timer['count'], 6, ' ', STR_PAD_LEFT);

        switch(true) {
            case $timer['sum'] > 1:
                $color = "\033[1;91m"; // RED BOLD
                break;
            case $timer['sum'] > 0.5:
                $color = "\033[0;91m"; // RED
                break;
            case $timer['sum'] > 0.1:
                $color = "\033[1;33m"; // YELLOW BOLD
                break;
            case $timer['sum'] > 0.01:
                $color = "\033[0;93m"; // YELLOW 
                break;
            case $timer['sum'] > 0.001:
                $color = "\033[0;92m"; // GREEN
                break;
            default:
                $color = "\033[0;94m"; // BLUE
                break;
        }

        $log .= "\n {$color}TIME:{$time} | COUNTS: {$count} | {$name}{$resetColor}";

    }

    // END
    $log .= $header;
    Mage::log($log, 7, 'profiler.log');

}