<?php

	/*
		RESOURCE: getEvents
		API VERSION: 1
		URL: /events/api/1/events/getEvents.php?detail_level=summary&count=20&page=1
		
		PARAMETERS:
		
			count		(optional)		ex. 20				Max number events to return (per page, if applicable). By default, this will return ALL events.
			page		(optional)		ex. 3				Pass the page number if you need result paging for big result sets. The count param is required when paging.
			start_date	(optional)		ex. 20120701		Will only return events starting from this date until end_date (if specified). 
															If start_date is not specified, the default value is the current date. (i.e. no past events shown)
			end_date	(optional)		ex. 20120801		Will only return events from start_date (or current date if no start is specified) to end_date.
			detail_level (required)		ex. summary			Specifies the level of detail included in the response. Accepted values are 'summary' or 'full'.
		
		EXAMPLE RESPONSES:
		
			Detail Level: full
			
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
				"special_note":"This goes under the image"
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
			
			Detail Level: summary
			
			[{
				"id":"28",
				"name":"A feb event",
				"date":"2013-02-16",
				"start_time":"11:00:00",
				"end_time":"15:00:00",		
				"all_day":"0",
				"image":"event28.png",
				"description":"Two dates in Feb 2012",
				"active":"1"
			},
			{
				...
				...
			}]		
	
	*/
	
	function getEvents($count = null, $page = null, $start_date = null, $end_date = null, $detail_level = null) {

		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
		//ERROR: No Detail Level specified
		if(!$detail_level) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "You must specify the level of detail for the response.";
			$error->details = "You must include the `detail_level` parameter with either summary or full.";
			return $error;
		}
		
		$summary_fields = "`id`,`name`,`date`,`start_time`,`end_time`,`all_day`,`image`,`description`,`active`";
		$select_params = (strtolower($detail_level) === "summary") ? $summary_fields : "*";

		if(!$start_date) $start_date = date("Ymd"); //Default value = current date
		
		$offset = ($page && $count) ? $page * $count : 0;
		
		if($end_date) {
			
			
			if($count) {
			
				$events = $db->get_results($db->prepare("SELECT " . $select_params . " FROM `events_special` WHERE `active` = 1 AND `date` BETWEEN %d AND %d ORDER BY `date` ASC LIMIT %d, %d", $start_date, $end_date, $offset, $count));
				
			} else {
				
				$events = $db->get_results($db->prepare("SELECT " . $select_params . " FROM `events_special` WHERE `active` = 1 AND `date` BETWEEN %d AND %d ORDER BY `date` ASC", $start_date, $end_date));
				
			}
			
		} else {

			if($count) {
			
				$events = $db->get_results($db->prepare("SELECT " . $select_params . " FROM `events_special` WHERE `active` = 1 AND `date` >= %d ORDER BY `date` ASC LIMIT %d, %d", $start_date, $offset, $count));
				
			} else {
				
				$events = $db->get_results($db->prepare("SELECT " . $select_params . " FROM `events_special` WHERE `active` = 1 AND `date` >= %d ORDER BY `date` ASC", $start_date));
				
			}

		}
		
		return $events;
	}
	
	$count = isset($_GET["count"]) ? $_GET["count"] : null;
	$page = isset($_GET["page"]) ? $_GET["page"] : null;
	$start_date = isset($_GET["start_date"]) ? $_GET["start_date"] : null;
	$end_date = isset($_GET["end_date"]) ? $_GET["end_date"] : null;
	$detail_level = isset($_GET["detail_level"]) ? $_GET["detail_level"] : null;

	//Check to see if this is a direct GET request, or a PHP include from another page.
	if(stripos($_SERVER["SCRIPT_FILENAME"], "api/") !== FALSE) {
		//this script was called directly, likely as a GET request from some javascript
		$events = getEvents($count, $page, $start_date, $end_date, $detail_level);
		echo json_encode($events);
	}

?>