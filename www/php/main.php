	
<?php

/*

	echo "<h1>Weekly Events</h1>";
	
	$results = $db->get_results($db->prepare("SELECT * FROM `events_weekly` WHERE `active` = 1"));				
	foreach($results as $result) {
		echo "<p> <strong>" . $result->name . "</strong><br / > " . $result->description . "</p>";
	}
	
	echo "<h1>Special Events</h1>";
	$results = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 1 ORDER BY `date` ASC"));				
	foreach($results as $result) {
		echo "<p> <strong>" . $result->name . "</strong><br / > " . $result->date . " at " . $result->time . "</p>";
	}

*/

	require_once($_SERVER['DOCUMENT_ROOT'] . "/api/1/events/getEventsByGroupId.php");
	
	print_r( getEventsByGroupId(25) , false);
	
	


	
?>