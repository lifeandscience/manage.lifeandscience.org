<?php

	/*
		RESOURCE: getEventsByDay
		API VERSION: 1
		URL: /api/1/events/getEventsByDay.php?day=Monday
		
		PARAMETERS:
		
			day (string)	(required)		ex. Monday
					
		EXAMPLE RESPONSE:
		
			[{
				"id":"3",
				"name":"Monkey Monday",
				"day_of_week": "Monday",
				"time":"10:40:00",
				"description":"Lots of monkeys all over the place.",
				"icon":"mm3.png",
				"active":"1",
				"added":"2012-06-24 23:46:04"
			},
			{
				...
				...
			}]
	
	*/

	function getEventsByDay($day = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No Day specified
		if(!$day) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No day specified.";
			$error->details = "You must include the `day` parameter. (example value: 'Monday')";
			return $error;
		}
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_weekly` WHERE `active` = 1 AND `day_of_week` = %s", $day));			

		if(!$events) {
			$events = array(); //return an empty array instead of null if no events are found matching the specified month
		}
		
		return $events;
	}
	
	$day = isset($_GET["day"]) ? $_GET["day"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByDay($day);
		echo json_encode($events);
	}

?>