<?php
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventsByDate.php");
	function buildEventlist($date) {
		
		$html = "";
		$events = getEventsByDate($date);
		foreach($events as $event) {
		
			$isWeekly = isset($event->day_of_week);
			
			if($event->all_day === "1") {				
				$displayTime = "All Day";
			} else {
				$displayTime = date("g:i A", strtotime($event->start_time));
				if($event->end_time) {
					$displayTime .= " - " . date("g:i A", strtotime($event->end_time));
				}
			}	
	
			$edit_link = $isWeekly ? "weekly" : "special";
			
			$html .= "
			
				<div class=\"event\">
					<span class=\"eventTime\">" . $displayTime . "</span>
					<span class=\"eventName\"><a title=\"Click to Edit\" href=\"/events/" . $edit_link  . "/edit/" . $event->id . "\">" . $event->name . "</a></span>
				</div>
			
			";
			
		}
		if(!$html) {
			$html = "<div class=\"noEvents\">No scheduled events.</div>";
		}
		return $html;
	}

?>

<div class="eventActions" style="float:right">
	<h3>Create Events</h3>
	<div><a href="/events/weekly/add">Create Weekly Event</a></div>
	<div><a href="/events/special/add">Create Special Event</a></div>
</div>



<h3>Today's Events</h3>
<div id="todays" class="eventList">
	<?= buildEventlist(date('Ymd')); ?>
</div>

<h3>Tomorrow's Events</h3>
<div id="todays" class="eventList">
	<?= buildEventlist(date('Ymd', strtotime("+1 day"))); ?>
</div>

