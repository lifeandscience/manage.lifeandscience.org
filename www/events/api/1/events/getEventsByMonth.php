<?php

	/*
		RESOURCE: getEventsByMonth
		API VERSION: 1
		URL: /events/api/1/events/getEventsByMonth.php?month=2&year=2013
		
		PARAMETERS:
		
			month (integer)	(required)		ex. 2 (Feb)
			year (integer) (optional, default = current year) ex. 2013
		
		EXAMPLE RESPONSE:
		
			[{
				"id":"28",
				"name":"A feb event",
				"date":"2013-02-16",
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"all_day":"0",
				"image":"event28.png",
				"fb_link":"http://facebook.com/blah",
				"description":"Two dates in Feb 2012",
				"special_note":"This goes under the image",
				"members_only":"0",
				"cost_members":"$24",
				"cost_public":"$30",
				"custom_1":"Ages 12-18",
				"active":"1",
				"added":"2012-06-24 23:46:04",
				"group_id":"28"
			},
			{
				...
				...
			}]
	
	*/

	function getEventsByMonth($month = null, $year = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No Month specified
		if(!$month) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No month specified.";
			$error->details = "You must include the `month` parameter using the integer representation.";
			return $error;
		}
		
		if($year) {
			$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 AND MONTH(date) = %d AND YEAR(date) = %d", $month, $year));		
		} else {
			$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 AND MONTH(date) = %d AND YEAR(date) = YEAR(CURDATE())", $month));			
		}

		if(!$events) {
			$events = array(); //return an empty array instead of null if no events are found matching the specified month
		}
		
		return $events;
	}
	
	$month = isset($_GET["month"]) ? $_GET["month"] : null;
	$year = isset($_GET["year"]) ? $_GET["year"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByMonth($month, $year);
		echo json_encode($events);
	}

?>