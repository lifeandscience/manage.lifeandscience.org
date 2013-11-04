<h2 class="sectionTitle">Monthly Events <a class="createLink" href="/events/special/add">Add New</a> </h2>



<div class="day_selector">
	<ul class="days">
		<li><a class="filterLink thisweek" href="#thisweek">This Week</a></li>
		<li><a class="filterLink next30" href="#next30">Next 30 Days</a></li>
		<li><a class="filterLink showall" href="#showall">Show All</a></li>
		<li><a class="filterLink past" href="#past">Past Events</a></li>
	</ul>
</div>
<div id="list"></div>


<script type="text/javascript">

	$("a.filterLink").click(function(e) {
		showEvents(e.target.hash);
	});
	
	//Check the hash value to see if we should pre-select a day.
	if(window.location.hash) {
		showEvents(window.location.hash);
	} else {
		//Show the events for the current week on first load
		showEvents();
	}
	
	function showEvents(hash) {
		var args = { "detail_level" : "summary" };
		switch(hash) {
			case "#next30":
				args.end_date = <?= date("Ymd", strtotime("+30 day")) ?>;
				break;
			case "#past":
				args.start_date = <?= date("Ymd", strtotime("-1 year")) ?>;
				args.end_date = <?= date("Ymd"); ?>;
				break;
			case "#showall":
				args.start_date = null;
				args.end_date = null;
				break;
			default:
				hash = "#thisweek";
				args.end_date = <?= date("Ymd", strtotime("+1 week")) ?>;
		}
		
		//Show the selected state
		$("a.filterLink").removeClass("selected");
		$("a." + hash.substring(1)).addClass("selected");
				
		$.getJSON("/events/api/1/events/getEvents.php", args,
			function(data){
				var datesByGroupID = {};
				var uniqueGroupIds = [];
				$("#list").empty();
				if(data && data.length > 0) {
					
					//Collect all of the dates for each groupId
					$.each(data, function(index, event) {
						if(event && event.group_id && event.group_id !== "0") {
							if(datesByGroupID[event.group_id] != null) {
								datesByGroupID[event.group_id].push(event.date);
							} else {
								datesByGroupID[event.group_id] = [ ]; //intentionally skip adding the 1st occurence since we are showing it as the primary date for the event.
							}
						}
					});
					 
					$.each(data, function(index, event) {
						if(event && event.group_id && event.group_id != "0") {
							if($.inArray(event.group_id, uniqueGroupIds) != -1) {
								return; //continue
							} else {
								uniqueGroupIds.push(event.group_id);	
							}
							var moreDates = getDateStringFromArray(datesByGroupID[event.group_id]);
						}
						var html = "<div class=\"specialEvent\">";
						html += "<span class=\"eventDate\">" + getDisplayDate(event) + "</span>";
						html += "<span class=\"eventTime\">" + getDisplayTime(event) + "</span>";
						html += "<span class=\"eventName\"><a href=\"/events/special/edit/" + event.id + "\" title=\"Click to Edit\">" + event.name + "</a></span>";
						if(moreDates) html += "<div class=\"additionalDates\">Repeats: " + moreDates + "</div>";
						html += "</div>";
						$("#list").append(html);
					});
				} else {
					//show no events message
					$("#list").append("<div class=\"noEvents\">No events scheduled</div>");
				}
			}
		);
	}
	
	function getDateStringFromArray(datesArray) {
		if(datesArray) {
			datesArray.sort();
			var displayString = "";
			$.each(datesArray, function(index, date) {
				displayString += getShortDate(date) + ", ";
			});
			if(displayString) {
				displayString = displayString.substring(0, displayString.length - 2); //remove trailing comma
			}
			return displayString;
		}
	}
	

</script>


