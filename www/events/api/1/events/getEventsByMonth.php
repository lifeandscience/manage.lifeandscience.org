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
				"display_date":"16th of February in the afternoon.",		
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"sun_start_time":"13:00:00",		
				"sun_end_time":"16:00:00",		
				"all_day":"0",
				"image":"event28.png",
				"fb_link":"http://facebook.com/blah",
				"description":"Two dates in Feb 2012",
				"special_note":"This goes under the image",
				"url":"http://lifeandscience.org/event/123",
				"custom_1":"Cost $30, Ages 12-18",
				"active":"1",
				"added":"2012-06-24 23:46:04",
				"group_id":"28",
				"tags":"1,3",
				"big_image": "event28_lrg.png",
				"col1": "This is a big description for the left column.",
				"col2": "This is a big description for the right column.",
				"registration_code": "2",
				"registration_url": "http://ticketmaster.com/blah/3"
			},
			{
				...
				...
			}]
	
	*/
	

	function getEventsByMonth($month = null, $year = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEvents.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No Month specified
		if($month == null) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No month specified.";
			$error->details = "You must include the `month` parameter using the integer representation.";
			return $error;
		}
		
		//Use the getEvents API with start/end dates corresponding to the first and last day of the requested month.
		$monthName = date("F", mktime(0, 0, 0, $month, 10)) . " ";
		$year = $year ? $year : date("Y");
		
		$start_date = $monthName . "1, " . $year;
		$end_date = $monthName . date('t', strtotime($start_date)) . ", " . $year;
		
		//Format start/end dates for the getEvents API
		$start_date = date("Ymd", strtotime($start_date));
		$end_date = date("Ymd", strtotime($end_date));
		
		return getEvents(false, false, $start_date, $end_date, "full");
	}
	
	$month = isset($_GET["month"]) ? $_GET["month"] : null;
	$year = isset($_GET["year"]) ? $_GET["year"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getEventsByMonth.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByMonth($month, $year);
		echo json_encode($events);
	}

?>