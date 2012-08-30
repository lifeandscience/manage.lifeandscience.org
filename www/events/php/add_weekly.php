<?php

	//Get the time settings from the database
	$settings = $db->get_results($db->prepare("SELECT * FROM `settings`"), "OBJECT_K");
	$START_TIME = $settings["start_time"]->value;
	$END_TIME = $settings["end_time"]->value;
	$INTERVAL = $settings["time_interval"]->value;
	
	//Check to see if we are editing an existing event
	$event = null;
	$event_id = $_GET["event_id"];
	if($event_id) {
		//Get the event (No API exists to do this, so we must query)
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);		
		$event = $db->get_row($db->prepare("SELECT * FROM `events_weekly` WHERE `id` = %d", $event_id));
		if(!$event) {
			echo "An error occurred trying to fetch this event. Check the error log.";
		}
	}

?>

<div id="errordiv" style="margin-top:8px;" class="noDisplay error">An error occurred.</div>
<div id="validationdiv" style="margin-top:8px;" class="noDisplay error">Please correct the errors below and try again.</div>


<?php
	//Need to show unique labels if we are in Edit vs. Create mode
	if($event) {
		echo "<div id=\"successdiv\" style=\"margin-top:8px;\" class=\"noDisplay success\">Event changes have been saved.</div>";
		echo "<h2 class=\"eventTitle\"><span class=\"editEventName\">" . $event->name . "</span>";
		echo "<a class=\"backLink\" href=\"/events/weekly#" . $event->day_of_week  . "\" title=\"Back to events\">Back to event list</a></h2>";
	} else {
		echo "<div id=\"successdiv\" style=\"margin-top:8px;\" class=\"noDisplay success\">Event created successfully.</div>";
		echo "<h2 class=\"eventTitle\">Create Weekly Event</h2>";
		echo "<p class=\"description\">These events occur every week on a specific day and time. (ex: Monday at 3pm, Friday at 11am)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/php/post_weekly.php" method="post">
	<?php
		//We should send the event_id so the posting script knows to do an update instead of an insert
		if($event_id) {
			echo "<input id='event_id' name='event_id' type='hidden' value='" . $event_id ."' />";
		}
	?>
    <table>
		<thead>
			<th style="width:130px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: </td>
	            <td><input type="text" name="name" id="name" class="inputfield" value="<?= ($event) ? $event->name : "" ?>" /><span class="required">*</span>
	            	<span class="inlineError" id="nameError">Enter an event name</span></td>
	        </tr>
			<tr>
	            <td>Day: </td>
	            <td><select name="day_of_week" id="day_of_week" style="width: 120px">
	            	<option></option>
					<?php
						$timestamp = strtotime('next Monday');
						for ($x = 0; $x < 7; $x++) {
							$name = strftime('%A', $timestamp);
							$sel = ($event && $event->day_of_week === $name) ? "selected='selected'" : "";
							echo "<option name='day_{$x}' value='{$name}' {$sel} />{$name}</option>";
							$timestamp = strtotime('+1 day', $timestamp);
						}
					?>
	             </select><span class="required">*</span><span class="inlineError" id="dayError">Select a day</span></td>
	        </tr>
			<tr>
	            <td>Start time: </td>
	            <td><select name="start_time" style="width: 120px" id="start_time">
	            	<option></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							$sel = ($event && $event->start_time === date('H:i', $i)) ? "selected='selected'" : "";
							echo "<option value='". date('H:i', $i) . "' {$sel} >" . date('g:i A', $i) . "</option>";
						}
		            ?>
	        </select><span class="required">*</span>
	        <input type="checkbox" name="all_day" id="all_day" style="vertical-align:middle;" <?= ($event && $event->all_day === "1") ? "checked=checked" : "" ?> />
	        <label for="all_day">All-day event</label><span class="inlineError" id="startError">Select a start time or all-day event.</span></td>
	        </tr>
	        <tr>
	            <td>End time: </td>
	            <td><select name="end_time" style="width: 120px" id="end_time">
	            	<option></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							$sel = ($event && $event->end_time === date('H:i', $i)) ? "selected='selected'" : "";
							echo "<option value='". date('H:i', $i) . "' {$sel} >" . date('g:i A', $i) . "</option>";
						}
		            ?>
	        </select></td>
	        </tr>
	        <tr>
	            <td>Description: </td>
	            <td><textarea name="description" id="description"><?= ($event) ? $event->description : "" ?></textarea></td>
	        </tr>
	        <tr>
	            <td>Icon: </td>
	            <td><input type="file" name="thumbnail" />
		            <?php
		            	if($event && $event->icon) {
		            		$path = "/events/uploads/" . $event->icon;
		            		echo "<img src=\"" . $path . "\" height='50' width='50' />";
		            	}
		            ?>
	            </td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center">
		            <?php
					
						//Show a delete link if we are in edit mode
						if($event) {
							echo "<a href=\"#\" id=\"deleteLink\">Delete event</a>";
						}	
						
					?>
		            <input type="button" onclick="validate()" class="button" value="<?= ($event) ? "Edit" : "Create" ?> Event" />
					<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a>
				</td>
			</tr> 
		</tbody>
    </table>
</form>



<script type="text/javascript">

	function validate() {		
		var name = $('#name').val();
		var day = $('#day_of_week').val();
		var start_time = $('#start_time').val();
		var all_day = $('#all_day').is(':checked');
		
		if(!start_time && !all_day) {
			$('#startError').show();
		} else {
			$('#startError').hide();
		}
		
		if(!day) {
			$('#dayError').show();
		} else {
			$('#dayError').hide();
		}
		
		$('#nameError').toggle(name == "");
		
		if(!day || !name || (!start_time && !all_day) ) {
			$("#validationdiv").show();
		}
		else {
			$("#validationdiv").hide();
			$('#addEvent').submit();
		}
	}

	function cancel() {
		history.back();
	}
	
	<?php if($event) { ?>
		function deleteEvent(e) {
			e.preventDefault();
			var event_id = <?= $event->id ?>;
			var yesDelete = confirm("Are you sure you want to delete \"<?= $event->name ?>\"?");
			if(yesDelete) {
				$.ajax({
					type: "POST",
					url: "/events/php/delete.php",
					data: { event_type : "weekly", event_id : event_id },
					success: function(response){
				  		if(response.trim() === "OK") {
					  		window.location.href = "/events/weekly/#<?= $event->day_of_week ?>";					  		
				  		} else {
					  		$("#errordiv").show();
				  		}
				  }
				});
			}
		}
		
		$("#deleteLink").click(function(e) {
			deleteEvent(e);		
		});
	
	<?php } ?>
	
	
	//Disable start/end times for all-day events
	$("#all_day").change(function() {
		$("#end_time").prop("disabled", this.checked);
		$("#start_time").prop("disabled", this.checked);
	});

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>
