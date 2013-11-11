<?php

	/*
		RESOURCE: getTags
		API VERSION: 1
		URL: /events/api/1/events/getTags.php
		
		PARAMETERS:
		
			N/A
		
		EXAMPLE RESPONSE:
		
			[
				{ "id": "1", "tag": "Camps & Classes" },
				{ "id": "2", "tag": "The Lab" }
			]
	
	*/

	function getTags($date = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		return $db->get_results($db->prepare("SELECT * FROM `tags` ORDER BY `sort_id` ASC"));
	}
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getTags.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$tags = getTags();
		echo json_encode($tags);
	}

?>