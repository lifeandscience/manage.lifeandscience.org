<?php

	/*
		RESOURCE: getEventsByTag
		API VERSION: 1
		URL: /events/api/1/events/getEventsByTag.php?id=4
		
		PARAMETERS:
		
			id (integer)	(required)		ex. 4
		
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
				"col2": "This is a big description for the right column."
			},
			{
				...
				...
			}]
	
	*/

	function getEventsByTag($tagId = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No tag id specified
		if($tagId == null) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No tag id specified.";
			$error->details = "You must include the `id` parameter.";
			return $error;
		}
		$now = date("Ymd");
		$all_events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 AND (`date` >= %d OR `end_date` >= %d) ORDER BY `date` ASC", $now, $now));
		$matching_events = array();
		foreach($all_events as $event) {
			$tags = explode(",", $event->tags);
			if($tags && in_array($tagId, $tags)) {
				array_push($matching_events,$event);
			}
		}
		return $matching_events;
	}
	
	$tagId = isset($_GET["id"]) ? $_GET["id"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getEventsByTag.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEventsByTag($tagId);
		echo json_encode($events);
	}

?>