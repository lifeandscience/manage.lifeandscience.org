<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	function formatDate($event) {
			if(!$event) return "";
			$displayString = "";
			
			//If a custom display date string exists, just use that.
			if($event->display_date) {
				return $event->display_date;
			}
			if($event->date) {
				$_date = strtotime($event->date);
				$displayString .= date('F j', $_date);
			}
			if($event->end_date) {
				$_endDate = strtotime($event->end_date);
				//If the end date is in the same month, omit the month name.
				if(date('F', $_date) == date('F', $_endDate)) {
					$displayString .= "–" . date('j', $_endDate);	
				} else {
					$displayString .= " – " . date('F j', $_endDate);	
				}
			}
			return $displayString;
		}
?>

<h2 class="sectionTitle">Archived Monthly Events</h2>

<?php
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 0 ORDER BY `date` DESC"));
		foreach($events as $event) {
			echo "
				<div class=\"event\">
					<span class=\"eventDate\">" . formatDate($event) . "</span>
					<span class=\"eventName\"><a title=\"Click to Edit\" href=\"/events/special/edit/" . $event->id . "\">" . $event->name . "</a></span>
				</div>
			";		
		}
		
		//There are no archived events
		if(!$events) {
			echo "<p>There are no archived monthly events.</p>";
		}
?>

<h2 class="sectionTitle" style="margin-top: 40px;">Archived Daily Events</h2>

<?php
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_weekly` WHERE `active` = 0 ORDER BY `added` DESC"));
		foreach($events as $event) {
			
			if($event->all_day === "1") {				
				$displayTime = "All Day";
			} else {
				$displayTime = date("g:i A", strtotime($event->start_time));
				if($event->end_time) {
					$displayTime .= " - " . date("g:i A", strtotime($event->end_time));
				}
			}
			echo "
				<div class=\"event\">
					<span class=\"eventName\"><a title=\"Click to Edit\" href=\"/events/weekly/edit/" . $event->id . "\">" . $event->name . "</a></span>
					<span class=\"eventTime\">" . $displayTime . "</span>
				</div>
			";		
		}
		
		//There are no archived events
		if(!$events) {
			echo "<p>There are no archived daily events.</p>";
		}
?>

<h2 class="sectionTitle" style="margin-top: 40px;">Archived Lab Programs</h2>

<?php
		
		$events = $db->get_results($db->prepare("SELECT * FROM `events_special` WHERE `active` = 0 AND `isLab` = 1 ORDER BY `date` DESC"));
		foreach($events as $event) {
			echo "
				<div class=\"event\">
					<span class=\"eventDate\">" . formatDate($event) . "</span>
					<span class=\"eventName\"><a title=\"Click to Edit\" href=\"/events/special/edit/" . $event->id . "\">" . $event->name . "</a></span>
				</div>
			";		
		}
		
		//There are no archived events
		if(!$events) {
			echo "<p>There are no archived lab programs.</p>";
		}
?>


