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


	define('NCMLS_DAILY_EVENTS_ENDPOINT', 'http://lifeandscience.org/visit/calendar');
	define('NCMLS_MONTHLY_EVENTS_ENDPOINT', 'http://lifeandscience.org/visit/calendar/all');
	
	define('NCMLS_DAILY_EVENTS_ENDPOINT_MOBILE', 'http://m.lifeandscience.org/daily-events');
	define('NCMLS_MONTHLY_EVENTS_ENDPOINT_MOBILE', 'http://m.lifeandscience.org/monthly-events');
	
}
?>