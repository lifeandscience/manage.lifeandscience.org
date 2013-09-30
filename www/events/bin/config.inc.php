<?php
if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/../config.inc.php')){
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../config.inc.php');
} else {
	//database settings
	define('SITE_DB_NAME', 'ncmls');
	define('SITE_DB_USER', 'root');
	define('SITE_DB_PASSWORD', 'root');
	define('SITE_DB_HOST', 'localhost');
	define('DB_CHARSET', 'utf8');
	define('DB_COLLATE', '');
	
	define('NCMLS_DAILY_EVENTS_ENDPOINT', 'http://lifeandscience.org/node/1630');
	define('NCMLS_MONTHLY_EVENTS_ENDPOINT', 'http://lifeandscience.org/node/1629');
	define('NCMLS_AFTER_HOURS_EVENTS_ENDPOINT', 'http://lifeandscience.org/node/1634');
	
}
?>