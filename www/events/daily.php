<?php

	$_date = "20130719";
	
	// LOCAL
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventsByDate.php");
	$events = getEventsByDate($_date);
	
	// OR REMOTE
	
//	$url = "http://ncmls/events/api/1/events/getEventsByDate.php?date=" . $_date;
//	$json = file_get_contents($url);
//	$events = json_decode($json);

	
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>Single Day Events</title>
	<meta charset="utf-8" /> 
	
	<style>
	
		.event {
			clear: both;
			border-bottom: 1px #ededed solid;
			margin: 10px;
			padding: 10px;
		}
		.left, 
		.right {
			display: inline-block;
			vertical-align: top;	
		}
		.left {
			width: 15%;
			min-width: 120px;
		}
		.left > img {
			width: 100px;
			height: auto;
		}
		.right {
			width: 80%;
		}
		.right > h3 {
			margin: 0 0 5px 0;
		}
		.right > .description {
			color: #777;
		}
	
	</style>
</head>

<body>

	<div class="wrapper">

		<h1>Friday, July 19, 2013</h1>
	
		<?php
		
			foreach($events as $event) {
			
				if($event->all_day === "1") {				
					$displayTime = "All Day";
				} else {
					$displayTime = date("g:i A", strtotime($event->start_time));
					if($event->end_time) {
						$displayTime .= " - " . date("g:i A", strtotime($event->end_time));
					}
					//Remove :00 from times for a cleaner display
					$displayTime = str_replace(":00", "", $displayTime);
				}
				
				$iconSrc = $event->icon ? "/events/uploads/" . $event->icon : ""; //TODO: Set a default icon
				
				echo "
				
					<div class=\"event\">
						<div class=\"left\">
							<img src=\"" . $iconSrc . "\" />
							<div>" . $displayTime . "</div>
						</div>
						<div class=\"right\">
							<h3>" . $event->name . "</h3>
							<div class=\"description\">" . $event->description . "</div>
						</div>
					</div>
				
				";
				
			}
			
		?>
				
	</div>
 	
</body>
</html>



