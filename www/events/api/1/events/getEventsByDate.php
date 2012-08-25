<?php

	/*
		RESOURCE: getEventsByDate
		API VERSION: 1
		URL: /api/1/events/getEventsByDate.php?date=20130216
		
		PARAMETERS:
		
			date (integer)	(required)		ex. 20130216
		
		EXAMPLE RESPONSE:
		
			[{
				"id":"28",
				"name":"A feb event",
				"day_of_week":"Saturday",
				"date":"2013-02-16",
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"all_day":"0",
				"image":"event28.png",
				"icon":"icon27.png",
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
			
		NOTES:
		
			This API returns two different types of events: weekly reoccurring, and special events. These two event types
			have different data models, so not all of the properties will appear in every result object. You should check
			the property to make sure it's set before attempting to use it. The 'day_of_week' property can be used to
			determine the event type for a given result object.
	
	*/

	function getEventsByDate($date = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No date specified
		if(!$date) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No date specified.";
			$error->details = "You must include the `date` parameter. Ex. 20130216";
			return $error;
		}
		
		$day_of_week = date('l', strtotime($date));
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 AND `date` = %d", $date));
		$events2 = $db->get_results($db->prepare("SELECT * FROM `events_weekly` WHERE `active` = 1 AND `day_of_week` = %s", $day_of_week));
		
		$events_combined = array_merge($events, $events2);

		if(!$events_combined) {
			$events_combined = array(); //return an empty array instead of null if no events are found matching the specified month
		}
		
		return $events_combined;
	}
	
	$date = isset($_GET["date"]) ? $_GET["date"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByDate($date);
		echo json_encode($events);
	}

?>