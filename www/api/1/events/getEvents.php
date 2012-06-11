<?php

	/*
		RESOURCE: getEvents
		API VERSION: 1
		URL: /api/1/events/getEvents.php?detail_level=Summary
		
		PARAMETERS:
		
			count		(optional)		ex. 20				Max number events to return (per page, if applicable). By default, this will return ALL events.
			page		(optional)		ex. 3				Pass the page number if you need result paging for big result sets
			start_date	(optional)		ex. 2012-05-29		Will only return events starting from this date until end_date (if specified). 
															If not specified, the default value is the current date.
			end_date	(optional)		ex. 2012-06-29		Will only return events from start_date (or current date if no start is specified) to end_date.
			detail_level (required)		ex. Summary			Specifies the level of detail included in the response. Values are Summary or Full. (See below for example)		
		
		EXAMPLE RESPONSE:
		
			//TODO
	
	*/

	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/api.php");

	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	$count = isset($_GET["count"]) ? $_GET["count"] : null;
	$page = isset($_GET["page"]) ? $_GET["page"] : null;
	$start_date = isset($_GET["start_date"]) ? $_GET["start_date"] : null;
	$end_date = isset($_GET["end_date"]) ? $_GET["end_date"] : null;
	$detail_level = isset($_GET["detail_level"]) ? $_GET["detail_level"] : null;
	
	//ERROR: No Detail Level specified
	if(!$detail_level) {
		$error = new ErrorObject();
		$error->code = "ERROR";
		$error->message = "You must specify the level of detail for the response.";
		$error->details = "You must include the `detail_level` property with either Summary or Full.";
		echo json_encode($error);
		return;
	}
	
	/* TODO:
		
		1) implement count/page paging
		2) implment ranges start/end dates
		3) determine the different detail levels. document & implement
		4) figure out if I need to join the 2 event tables for this API
	
	*/
	
	$events = $db->get_results($db->prepare("SELECT * FROM `events_weekly`"));
	
	echo json_encode($events);
	
?>