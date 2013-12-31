<?php

	/*
		RESOURCE: getNote
		API VERSION: 1
		URL: /events/api/1/events/getNote.php?date=20130216
		
		PARAMETERS:
		
			date (integer)	(required)		ex. 20130216
		
		EXAMPLE RESPONSE:
		
			{
				"date": "2013-12-04",
				"notes": "This is a note.",
				"dailyEventsDisabled": "0"
			}
	
	*/

	function getNote($date = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No date specified
		if($date == null) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No date specified.";
			$error->details = "You must include the `date` parameter. Ex. 20130216";
			return $error;
		}
		$note = $db->get_row($db->prepare("SELECT * FROM `special_notes` WHERE `date` = %s", $date));
		return $note;
	}
	
	$date = isset($_GET["date"]) ? $_GET["date"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getNote.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$note = getNote($date);
		echo json_encode($note);
	}

?>