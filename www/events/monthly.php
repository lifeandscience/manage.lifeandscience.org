<?php

	$_month = 7;
	
	// LOCAL
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventsByMonth.php");
	$events = getEventsByMonth($_month);
	
	// OR REMOTE
	
//	$url = "http://ncmls/events/api/1/events/getEventsByMonth.php?month=" . $_month . "&year=" . $_year;
//	$json = file_get_contents($url);
//	$events = json_decode($json);

	
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>Events by Month</title>
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
			width: 35%;
			min-width: 320px;
		}
		.left > img {
			width: 300px;
			height: auto;
		}
		.right {
			width: 60%;
		}
		.right > h3 {
			margin: 0 0 5px 0;
		}
		
		.right > .datetime {
			border-bottom: 1px #999 solid;
			margin-bottom: 6px;
		}
		
		.right > .costs {
			margin: 0 0 20px;
			font-size: 0.9em;
		}
		.right > .costs div {
			width: 25%;
			padding: 0 5px;
			display: inline-block;
			color: #777;
			text-align: center;
		}
		.separator {
			display: inline-block;
			margin: 0 5px;
			width: 1px;
			height: 20px;
			background: #000;
		}
		.right > .description {
			color: #777;
		}
	
	</style>
</head>

<body>

	<div class="wrapper">

		<h1>Events by Month</h1>
	
		<?php
		
			function formatDate($event) {
				if(!$event) return "";
				$displayString = "";
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
			
			function formatTime($event) {
				if($event->all_day === "1") {				
					$displayTime = "";
				} else {
					$displayTime = "at " . date("g:i A", strtotime($event->start_time));
					if($event->end_time) {
						$displayTime .= " - " . date("g:i A", strtotime($event->end_time));
						$displayTime = str_replace("at ", "from ", $displayTime);
					}
					//Remove :00 from times for a cleaner display
					$displayTime = str_replace(":00", "", $displayTime);
				}
				return $displayTime;
			}
		
			foreach($events as $event) {
			
				$iconSrc = $event->image ? "/events/uploads/" . $event->image : ""; //TODO: Set a default image?
				
				echo "
				
					<div class=\"event\">
						<div class=\"left\">
							<img src=\"" . $iconSrc . "\" />
							<div class=\"specialnote\">" . $event->special_note . "</div>
						</div>
						<div class=\"right\">
							<h3>" . $event->name . "</h3>
							<div class=\"datetime\">" . formatDate($event) . " " . formatTime($event) . "</div>
							<div class=\"costs\">";
							
							if($event->custom_1) echo "<div>" . $event->custom_1 . "</div><span class=\"separator\"></span>";
							if($event->cost_members) echo "<div>" . $event->cost_members . " Museum Members</div><span class=\"separator\"></span>";
							if($event->cost_public) echo "<div>" . $event->cost_public . " General Public</div><span class=\"separator\"></span>";
							
							echo "							
								<div style='width:75px;'><a href=\"". $moreLink . "\">MORE</a></div>
							</div>
							<div class=\"description\">" . $event->description . "</div>
						</div>
					</div>
				
				";
				
			}
			
		?>
				
	</div>
 	
</body>
</html>



