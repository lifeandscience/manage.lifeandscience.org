<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	foreach($_POST as $property=>$value) {
		$db->update('settings', array("value" => $value), array('property' => $property ));
	}
	echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/settings?success';</script>";

?>