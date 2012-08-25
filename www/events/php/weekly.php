<h2>Weekly Events</h2>


<a href="/events/weekly/add">Create Weekly Event</a>


<div class="day_selector">
	<ul class="days">
		<?php
			//Generate list of days
			$timestamp = strtotime('next Monday');
			for ($x = 0; $x < 7; $x++) {
				$dispTime = strftime('%A', $timestamp);
				echo "<li><a class=\"daylink\" href=\"#" . $dispTime . "\">" . $dispTime . "</a></li>";
				$timestamp = strtotime('+1 day', $timestamp);
			}
		?>
	</ul>
</div>
<div id="list"></div>


<script type="text/javascript">


	$("a.daylink").click(function(e) {
		if(e.target) {
			var day = e.target.innerHTML;
			showEvents(day);
		}
	});
	
	function showEvents(day) {
		$.getJSON("/events/api/1/events/getEventsByDay.php", { "day": day },
			function(data){
				$("#list").empty();
				if(data && data.length > 0) {
					$.each(data, function(index, event) {	

						var displayTime;
						var html = "<div class=\"weeklyEvent\">";
						
						if(event.all_day === "1") {
							displayTime = "All Day";	
						} else {
							displayTime = event.start_time;
							if(event.end_time) {
								displayTime += " - " + event.end_time;
							}
						}
						html += "<span class=\"eventTime\">" + displayTime + "</span>";
						html += "<span class=\"eventName\"><a href=\"/events/weekly/edit/" + event.id + "\" title=\"Click to Edit\">" + event.name + "</a></span>";
						console.debug(event);
						
						$("#list").append(html);
						
					});
				} else {
					//show no events message
					$("#list").append("<div>No events scheduled</div>");
				}
			}
		);
	}
	
	//Check the hash value to see if we should pre-select a day.
	if(window.location.hash) {
		var hash = window.location.hash;
		showEvents(hash.substring(1));
	}

</script>


