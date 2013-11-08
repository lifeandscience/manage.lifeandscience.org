<h2 class="sectionTitle">Daily Events <a class="createLink" href="/events/weekly/add">Add New</a> </h2>

<div class="day_selector">
	<ul class="days">
		<?php
			//Generate list of days
			$timestamp = strtotime('next Monday');
			for ($x = 0; $x < 7; $x++) {
				$dayName = strftime('%A', $timestamp);
				echo "<li><a class=\"filterLink " . $dayName ."\" href=\"#" . $dayName . "\">" . $dayName . "</a></li>";
				$timestamp = strtotime('+1 day', $timestamp);
			}
		?>
	</ul>
</div>
<div id="list"></div>


<script type="text/javascript">

	$("a.filterLink").click(function(e) {
		if(e.target) {
			var day = e.target.innerHTML;
			showEvents(day);
		}
	});
	
	function showEvents(day) {
		if(!day){
			var d = new Date();
			day = dayNames[d.getDay()];
		}		
		$.getJSON("/events/api/1/events/getEventsByDay.php", { "day": day },
			function(data){
				$("#list").empty();
				
				//Show the selected state
				$("a.filterLink").removeClass("selected");
				$("a." + day).addClass("selected");

				if(data && data.length > 0) {					
					$.each(data, function(index, event) {	
						var html = "<div class=\"weeklyEvent\">";
						html += "<span class=\"eventTime\">" + getDisplayTime(event) + "</span>";
						html += "<span class=\"eventName\"><a href=\"/events/weekly/edit/" + event.id + "\" title=\"Click to Edit\">" + event.name + "</a></span>";						
						$("#list").append(html);
						
					});
				} else {
					//show no events message
					$("#list").append("<div class=\"noEvents\">No events scheduled</div>");
				}
			}
		);
	}
	
	//Check the hash value to see if we should pre-select a day.
	if(window.location.hash) {
		var hash = window.location.hash;
		showEvents(hash.substring(1));
	} else {
		//Show the events for the current day on the first load
		showEvents();
	}

</script>


