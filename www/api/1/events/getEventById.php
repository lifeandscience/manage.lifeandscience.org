<?php

	/*
		RESOURCE: getEventsById
		API VERSION: 1
		URL: /api/1/events/getEventById.php?id=10
		
		PARAMETERS:
		
			id	(required)		ex. 10
		
		EXAMPLE RESPONSE:
		
			{
				"id": "10",
				"name": "Event 3",
				"day_of_week": "Wednesday",
				"time": "11:00:00",
				"description": "Blah blah blah",
				"icon": "",							//TODO: Determine the icon data structure
				"active": "1",
				"added": "2012-05-29 01:10:07"
			}
	
	*/


	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/api.php");

	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	$event_id = $_GET["id"];
	
	//ERROR: No Event ID specified
	if(!$event_id) {
		$error = new ErrorObject();
		$error->code = "ERROR";
		$error->message = "No event id specified.";
		$error->details = "You must include the `id` property with an integer event id.";
		echo json_encode($error);
		return;
	}
	
	$event = $db->get_row($db->prepare("SELECT * FROM `events_weekly` WHERE `id` = %d", $event_id));
	
	if(!$event) {
		$event = array(); //return an empty array instead of null if no event is found (TODO: does this make sense?)
	}
	
	echo json_encode($event);
	
?>