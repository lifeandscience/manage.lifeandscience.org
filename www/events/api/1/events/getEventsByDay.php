<?php

	/*
		RESOURCE: getEventsByDay
		API VERSION: 1
		URL: /events/api/1/events/getEventsByDay.php?day=Monday
		
		PARAMETERS:
		
			day (string)			(required)		ex. Monday (or Mon)
					
		EXAMPLE RESPONSE:
		
			[{
				"id":"3",
				"name":"Monkey Monday",
				"mon": "1",
				"tue": "0",
				"wed": "0",
				"thu": "1",
				"fri": "0",
				"sat": "0",
				"sun": "1",
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"all_day":"0",
				"description":"Lots of monkeys all over the place.",
				"icon":"mm3.png",
				"active":"1",
				"added":"2012-06-24 23:46:04",
				"registration_code": "2",
				"registration_url": "http://ticketmaster.com/blah/3"
			},
			{
				...
				...
			}]
	
	*/

	function getEventsByDay($day = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No Day specified
		if($day == null) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No day specified.";
			$error->details = "You must include the `day` parameter. (example value: 'Monday')";
			return $error;
		}
		
		$p_day = strtolower(substr($day, 0, 3));
		
		//Since we can't use prepare and %s for column names, ensure that the value exists in the whitelist for security.
		$whitelisted_col_names = array("mon","tue","wed","thu","fri","sat","sun");
		if(in_array($p_day, $whitelisted_col_names)) {
			$events = $db->get_results("SELECT * FROM `events_weekly` WHERE `active` = 1 AND `{$p_day}` = 1 ORDER BY `start_time` ASC");
		}

		if(!$events) {
			$events = array(); //return an empty array instead of null if no events are found matching the specified month
		}
		
		return $events;
	}
	
	$day = isset($_GET["day"]) ? $_GET["day"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getEventsByDay.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByDay($day);
		echo json_encode($events);
	}

?>