<?php

function clean_log_tables() {
    global $db;
    $tables = array(
	'aw_core_logger',
	'lengow_log',
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
	'index_event',
	'report_event',
	'report_viewed_product_index',
	'report_compared_product_index',
	'report_event',
	'report_compared_product_index',
	'catalog_compare_item',
	'catalogindex_aggregation',
	'catalogindex_aggregation_tag',
	'catalogindex_aggregation_to_tag'
	);
	mysql_connect($db['host'], $db['user'], $db['pass']) or die(mysql_error());
	mysql_select_db($db['name']) or die(mysql_error());
	foreach($tables as $v => $k) {
	    echo 'Query for dbname'.$db['name'].' : TRUNCATE `'.$db['pref'].$k.'`'."\n";
	    $result = mysql_query('TRUNCATE `'.$db['pref'].$k.'`') or print(mysql_error());
	    echo $result."\n";
	}
}

function clean_var_directory($magento_dir) {
	if(empty($magento_dir)){echo 'empty magento_dir!'; exit();}
	$dirs = array(
	$magento_dir.'/var/log/',
	$magento_dir.'/var/report/',
//      $magento_dir.'/var/tmp/',
	);

	foreach($dirs as $v => $k) {
	    if(empty($k)){
		echo 'empty $k'; exit();
	    }
	    else{
		echo'deleted'.$k."\n";
		exec('rm -rf '.$k.'/*');
	    }
	}
}




$lines = shell_exec('find /home/ -maxdepth 6 -path \'*/app/etc/*\' -name \'local.xml\'  | xargs grep -l "Magento" > /tmp/listmagento.tmp');
$lines = file('/tmp/listmagento.tmp', FILE_IGNORE_NEW_LINES);
print($lines);
foreach ($lines as $value) {
//echo $value;


if(file_exists($value)) {

    
    // /home/ftp/cust_a89/dev/app/etc/local.xml
	
$magento_dir = explode("app/etc/local.xml", $value);
//echo $magento_dir[0];
echo '---'.$magento_dir[0]."\n";
    
    // Load in the local.xml and retrieve the database settings
    $xml = simplexml_load_file($value);
    if(isset($xml->global->resources->default_setup->connection)) {
	$connection = $xml->global->resources->default_setup->connection;
	echo 'Host : '.$connection->host[0]."\n";
	echo 'Dbname : '.$connection->dbname[0]."\n";
	echo 'Username : '.$connection->username[0]."\n";
	echo 'Pwd : '.$connection->password[0]."\n";
	echo 'Prefix : '.$connection->table_prefix[0]."\n";
	
	//(Pour la fonction clean database)
	$db['host'] = $connection->host[0];
	$db['name'] = $connection->dbname[0];
	$db['user'] = $connection->username[0];
	$db['pass'] = $connection->password[0];
	$db['pref'] = $connection->table_prefix[0];
	
	// verify
	if(is_dir($magento_dir[0].'/var/session')){
	echo exec("find ".$magento_dir[0]."/var/session -type f -mmin +600 -delete");
	echo " clean logsql \n";
	clean_log_tables();
	echo " cleanvar \n";
	clean_var_directory($magento_dir[0]);
	
	if(is_file($magento_dir[0].'/shell/log.php')){
	    echo " log.php exists \n";
	    echo exec("php -q ".$magento_dir[0]."/shell/log.php clean status");
	    echo exec("php -q ".$magento_dir[0]."/shell/log.php clean --days 1");
	    echo exec("php -q ".$magento_dir[0]."/shell/log.php clean status");
	}

	
	
	}
	else {
	    echo "\n ! ".$magento_dir[0].'/var/session doesnt exists'."\n";
	}
    }
    
} else {
    die('Unable to load Magento local xml File');
}
}
?>