<?php
$xml = simplexml_load_file('./../app/etc/local.xml', NULL, LIBXML_NOCDATA);

$db['host'] = $xml->global->resources->default_setup->connection->host;
$db['name'] = $xml->global->resources->default_setup->connection->dbname;
$db['user'] = $xml->global->resources->default_setup->connection->username;
$db['pass'] = $xml->global->resources->default_setup->connection->password;
$db['pref'] = $xml->global->resources->db->table_prefix;

if (!isset($argv[1]) || !stristr($argv[1], 'clean=')) {
    echo 'Please use one of the commands below:' . PHP_EOL;
    echo 'php maintenance.php clean=log' . PHP_EOL;
    echo 'php maintenance.php clean=var' . PHP_EOL;
    die;
}

$method = str_replace('clean=', '', $argv[1]);

switch ($method) {
case 'log':
    clean_log_tables();
    break;
case 'var':
    clean_var_directory();
    break;
default:
    echo 'Please use one of the commands below:' . PHP_EOL;
    echo 'php maintenance.php clean=log' . PHP_EOL;
    echo 'php maintenance.php clean=var' . PHP_EOL;
    break;
}

function clean_log_tables() {
    global $db;

    $tables = array(
        'catalogindex_aggregation',
        'catalogindex_aggregation_tag',
        'catalogindex_aggregation_to_tag',
        'catalog_compare_item',
        'dataflow_batch_export',
        'dataflow_batch_import',
        'log_customer',
        'log_quote',
        'log_summary',
        'log_summary_type',
        'log_url',
        'log_url_info',
        'log_visitor',
        'log_visitor_info',
        'log_visitor_online',
        'report_compared_product_index',
        'report_event',
        'report_viewed_product_index'
    );

    mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name']) or die(mysqli_error());

    foreach($tables as $v => $k) {
        @mysqli_query('TRUNCATE `'.$db['pref'].$k.'`');
        echo $db['pref'] . $k . ' has been truncated' . PHP_EOL;
    }
}

function clean_var_directory() {
    $dirs = array(
        '../downloader/.cache/*',
        '../downloader/pearlib/cache/*',
        '../downloader/pearlib/download/*',
        '../var/cache/',
        '../var/locks/',
        '../var/log/',
        '../var/report/',
        '../var/session/',
        '../var/tmp/'
    );

    foreach($dirs as $v => $k) {
        exec('rm -rf '.$k);
        echo $k . ' has been emptied' . PHP_EOL;
    }
}
