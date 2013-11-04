<?php
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventsByDate.php");
	function buildEventlist($date) {
		
		$html = "";
		$events = getEventsByDate($date);
		foreach($events as $event) {
		
			$isWeekly = isset($event->mon);
			
			if($event->all_day === "1") {				
				$displayTime = "All Day";
			} else {
			
				$start_time = $event->start_time;
				$end_time = $event->end_time;
				if(date("D",strtotime($date)) == "Sun") {
					if(!empty($event->sun_start_time)) {
						$start_time = $event->sun_start_time;		
					}
					if(!empty($event->sun_end_time)) {
						$end_time = $event->sun_end_time;		
					}
				}
				$displayTime = date("g:i A", strtotime($start_time));
				if($end_time) {
					$displayTime .= " - " . date("g:i A", strtotime($end_time));
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
	<div><a href="/events/special/add">Create Monthly Event</a></div>
</div>



<h3>Today's Events</h3>
<div id="todays" class="eventList">
	<?= buildEventlist(date('Ymd')); ?>
</div>

<h3>Tomorrow's Events</h3>
<div id="todays" class="eventList">
	<?= buildEventlist(date('Ymd', strtotime("+1 day"))); ?>
</div>

