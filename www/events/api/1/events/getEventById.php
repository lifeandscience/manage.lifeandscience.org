<?php

	/*
		RESOURCE: getEventsById
		API VERSION: 1
		URL: /events/api/1/events/getEventById.php?id=28
		
		PARAMETERS:
		
			id	(required)		ex. 10
		
		EXAMPLE RESPONSE:
		
			{
				"id":"28",
				"name":"A feb event",
				"date":"2012-02-16",
				"display_date":"16th of February in the afternoon.",		
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"sun_start_time":"13:00:00",		
				"sun_end_time":"16:00:00",		
				"all_day":"0",		
				"image":"event28.png",
				"fb_link":"http://facebook.com/blah",
				"tweet":"Check out this event happening at @lifeandscience",
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
				"registration_url": "http://ticketmaster.com/blah/3",
				"attachments": [
					{"id":"43","filename":"attachment895_1.pdf","event_id":"28","added":"2013-12-30 23:11:24"},
					{"id":"44","filename":"attachment54_2.doc","event_id":"28","added":"2013-12-30 23:11:25"}
				]
			}
		
		NOTE: This API is for special events occurring on a fixed date, not daily reocurring events.
	
	*/
	
	
	function getEventById($id = null) {
	
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		//ERROR: No Event ID specified
		if($id == null) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "No event id specified.";
			$error->details = "You must include the `id` parameter with an integer event id.";
			return $error;
		}
		
		 $event = $db->get_row($db->prepare("SELECT * FROM `events_special` WHERE `id` = %d", $id));
		
		//Get attachments
		$attachments = $db->get_results($db->prepare("SELECT * FROM `attachments` WHERE `event_id` = %d", $event->group_id ? $event->group_id : $id));
		if($attachments) {
			$event->attachments = $attachments;
		}
		
		if(!$event) {
			$event = array(); //return an empty array instead of null if no event is found (TODO: does this make sense?)
		}
		
		return $event;
	}
	
	$id = isset($_GET["id"]) ? $_GET["id"] : null;
	
	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/1/events/getEventById.php") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$event = getEventById($id);
		echo json_encode($event);
	}

?>