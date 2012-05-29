	
<?php

	$results = $db->get_results($db->prepare("SELECT * FROM `events_regular` WHERE `active` = 1"));				
	foreach($results as $result) {
		echo "<p> <strong>" . $result->name . "</strong><br / > " . $result->description . "</p>";
	}
	
?>