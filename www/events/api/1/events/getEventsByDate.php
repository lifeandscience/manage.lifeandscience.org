<?php

	/*
		RESOURCE: getEventsByDate
		API VERSION: 1
		URL: /events/api/1/events/getEventsByDate.php?date=20130216
		
		PARAMETERS:
		
			date (integer)	(required)		ex. 20130216
		
		EXAMPLE RESPONSE:
		
			[{
				"id":"28",
				"name":"A feb event",		
				"date":"2013-02-16",
				"display_date":"16th of February in the afternoon.",		
				"start_time":"11:00:00",
				"end_time":"15:00:00",	
				"sun_start_time":"13:00:00",		
				"sun_end_time":"16:00:00",			
				"all_day":"0",
				"image":"event28.png",
				"icon":"icon27.png",
				"fb_link":"http://facebook.com/blah",
				"description":"Two dates in Feb 2012",
				"special_note":"This goes under the image",
				"custom_1":"Cost $30, Ages 12-18",
				"active":"1",
				"added":"2012-06-24 23:46:04",
				"mon": "1",
				"tue": "0",
				"wed": "0",
				"thu": "1",
				"fri": "0",
				"sat": "1",
				"sun": "0",
				"group_id":"28",
				"tags":"1,3",
				"big_image": "event28_lrg.png",
				"col1": "This is a big description for the left column.",
				"col2": "This is a big description for the right column."
			},
			{
				...
				...
			}]
			
		NOTES:
		
			This API returns two different types of events: weekly reoccurring, and special events. These two event types
			have different data models, so not all of the properties will appear in every result object. You should check
			the property to make sure it's set before attempting to use it. The 'mon' (short for Monday) property can be used to
			determine the event type for a given result object.
	
	*/

	function getEventsByDate($date = null) {
	
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
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 AND (`date` = %d OR (`date` <= %d AND `end_date` >= %d)) ORDER BY `start_time` ASC", $date, $date, $date));
		
		$p_day = strtolower(date('D', strtotime($date)));
		//Since we can't use prepare and %s for column names, ensure that the value exists in the whitelist for security.
		$whitelisted_col_names = array("mon","tue","wed","thu","fri","sat","sun");
		$events2 = array();
		if(in_array($p_day, $whitelisted_col_names)) {
			$events2 = $db->get_results("SELECT * FROM `events_weekly` WHERE `active` = 1 AND `{$p_day}` = 1 ORDER BY `start_time` ASC");
		}
		
		$events_combined = array_merge($events, $events2);
	
		if(!$events_combined) {
			$events_combined = array(); //return an empty array instead of null if no events are found matching the specified month
		}
		
		//Sort the array by start_time (sunday gets a custom sort due to potential sun_start_time)
		if(date('D',strtotime($date)) === "Sun") {
			usort($events_combined, "sorter");
		} else {
			$func = create_function('$a,$b', 'return strcmp($a->start_time, $b->start_time);');
			usort($events_combined, $func);	
		}
		return $events_combined;
	}
	
	//Only called on Sundays
	function sorter($a,$b) {
		$timeA = $a->start_time;
		if(!empty($a->sun_start_time)) {
			$timeA = $a->sun_start_time;	
		}
		$timeB = $b->start_time;
		if(!empty($b->sun_start_time)) {
			$timeB = $b->sun_start_time;	
		}
		return strcmp($timeA, $timeB);
	}
	
	$date = isset($_GET["date"]) ? $_GET["date"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getEventsByDate.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByDate($date);
		echo json_encode($events);
	}

?>
