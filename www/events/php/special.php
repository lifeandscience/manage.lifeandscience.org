<h2>Special Events</h2>

<a href="/events/special/add">Create Special Event</a>

<div class="day_selector">
	<ul class="days">
		<li><a class="filterLink" href="#thisweek">This Week</a></li>
		<li><a class="filterLink" href="#next30">Next 30 Days</a></li>
		<li><a class="filterLink" href="#showall">Show All</a></li>
		<li><a class="filterLink" href="#past">Past Events</a></li>
	</ul>
</div>
<div id="list"></div>


<script type="text/javascript">

	var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]; //bummer
	var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ]; //bummer

	$("a.filterLink").click(function(e) {
		showEvents(e.target.hash);
	});
	
	//Check the hash value to see if we should pre-select a day.
	if(window.location.hash) {
		showEvents(window.location.hash);
	}
	
	function showEvents(hash) {
		var args = { "detail_level" : "summary" };
		switch(hash) {
			case "#thisweek":
				args.end_date = <?= date("Ymd", strtotime("+1 week")) ?>;
				break;
			case "#next30":
				args.end_date = <?= date("Ymd", strtotime("+30 day")) ?>;
				break;
			case "#past":
				args.start_date = <?= date("Ymd", strtotime("-1 year")) ?>;
				args.end_date = <?= date("Ymd"); ?>;		
		}
		$.getJSON("/events/api/1/events/getEvents.php", args,
			function(data){
				$("#list").empty();
				if(data && data.length > 0) {
					$.each(data, function(index, event) {	
						var displayTime;
						var html = "<div class=\"specialEvent\">";
						
						if(event.all_day === "1") {
							displayTime = "All Day";	
						} else {
							displayTime = event.start_time;
							if(event.end_time) {
								displayTime += " - " + event.end_time;
							}
						}
						
						//format the date like "August 25"
						var parts = event.date.match(/(\d+)/g);
						var displayDate = new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
						displayDate = dayNames[displayDate.getDay()] + ", " + monthNames[displayDate.getMonth()] + " " + displayDate.getDate();

						html += "<span class=\"eventDate\">" + displayDate + "</span>";
						html += "<span class=\"eventTime\">" + displayTime + "</span>";
						html += "<span class=\"eventName\"><a href=\"/events/special/edit/" + event.id + "\" title=\"Click to Edit\">" + event.name + "</a></span>";						
						$("#list").append(html);
						
					});
				} else {
					//show no events message
					$("#list").append("<div>No events scheduled</div>");
				}
			}
		);
	}
	

</script>


