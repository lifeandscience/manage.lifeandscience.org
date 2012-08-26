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
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventById.php");
		$event = getEventById($event_id);
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
		echo "<h2>Edit " . $event->name . "</h2>";
		echo "<a href=\"javascript:cancel();\" title=\"Back to events\">Back to event list</a>";
	} else {
		echo "<div id=\"successdiv\" style=\"margin-top:8px;\" class=\"noDisplay success\">Event created successfully.</div>";
		echo "<h2>Create Special Event</h2>";
		echo "<p>These events occur on a specific date(s). (ex. Dec 25, 2012 at 7 AM, Sundays in April)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/php/post_special.php" method="post">
	<?php
		//We should send the event_id so the posting script knows to do an update instead of an insert
		if($event_id) {
			echo "<input id='event_id' name='event_id' type='hidden' value='" . $event_id ."' />";
			echo "<input id='group_id' name='group_id' type='hidden' value='" . $event->group_id ."' />";
		}
	?>
    <table>
		<thead>
			<th style="width:150px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: </td>
	            <td><input type="text" name="name" id="name" class="inputfield" value="<?= ($event) ? $event->name : "" ?>" /><span class="required">*</span>
		            <span class="inlineError" id="nameError">Enter an event name</span></td>
	        </tr>
			<tr>
	            <td>Date(s): </td>
	            <td><div id="ui-datepicker-div"></div>
	            <input id="date" name="date" type="text" class="inputfield" placeholder="Select a date for this event">
	            <span class="required">*</span>
	            <?php if(!$event) echo "<span class=\"tiny\">You can select multiple dates.</span>"; ?><span class="inlineError" id="dateError">Pick a date</span></td>
	        </tr>
			<tr>
	            <td>Start time: </td>
	            <td><select name="start_time" style="width: 120px" id="start_time" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?>>
	            	<option selected="selected"></option>
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
	            <td><select name="end_time" style="width: 120px" id="end_time" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?> >
	            	<option selected="selected"></option>
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
	            <td>Custom Field: </td>
	            <td><input type="text" name="custom_1" id="custom_1" class="inputfield" value="<?= ($event) ? $event->custom_1 : "" ?>" />
	            <span class="tiny">Specify age limitations, or other special requirements.</span></td>
	        </tr>
	        <tr>
	            <td>Cost (Members): </td>
	            <td><input type="text" name="cost_members" id="cost_members" class="inputfield short" value="<?= ($event) ? $event->cost_members : "" ?>" />
		            <input type="checkbox" name="members_only" id="members_only" style="vertical-align:middle;" <?= ($event && $event->members_only === "1") ? "checked=checked" : "" ?> />
		            <label for="members_only">This event is for members only</label>
	            </td>
	        </tr>
	        <tr>
	            <td>Cost (Public): </td>
	            <td><input type="text" name="cost_public" id="cost_public" class="inputfield short" value="<?= ($event) ? $event->cost_public : "" ?>" 
	            		<?= ($event && $event->members_only === "1") ? "disabled" : "" ?> /></td>
	        </tr>
	        <tr>
	            <td>Image: </td>
	            <td><input type="file" name="thumbnail" />
		            <?php
		            	if($event && $event->image) {
		            		$path = "/events/uploads/" . $event->image;
		            		echo "<img src=\"" . $path . "\" height='50' width='50' />";
		            	}
		            ?>
	            </td>
	        </tr>
	        <tr>
	            <td>Special Note: </td>
	            <td><textarea name="special_note" id="special_note" class="short"><?= ($event) ? $event->special_note : "" ?></textarea>
	            <span class="tiny">This message will be displayed under the image.</span></td>
	        </tr>
	        <tr>
	            <td>Facebook URL: </td>
	            <td><input type="text" name="fb_link" id="fb_link" class="inputfield" value="<?= ($event) ? $event->fb_link : "" ?>" /></td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center"><input type="button" onclick="validate()" class="button" value="<?= ($event) ? "Edit" : "Create" ?> Event" />
				<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a></td></td>
			</tr> 
		</tbody>
    </table>
</form>

<?php

	//Show a delete link if we are in edit mode
	if($event) {
		echo "<div class=\"delete\"><a href=\"#\" id=\"deleteLink\">Delete this event</a></div>";
	}	
	
?>

<div id="delete-confirm" title="You're deleting an event." style="display:none;">
	<p>Do you want to delete this and all occurrences of this event, or only the selected occurrence?</p>
</div>

<script type="text/javascript">

	function validate() {
		var name = $('#name').val();
		var dates = $('#ui-datepicker-div').multiDatesPicker('getDates');
		var start_time = $('#start_time').val();
		var all_day = $('#all_day').is(':checked');
		
		if(!start_time && !all_day) {
			$('#startError').show();
		} else {
			$('#startError').hide();
		}
		
		if(dates.length == 0) {
			$('#dateError').show();
		} else {
			$('#dateError').hide();
		}
		
		$('#nameError').toggle(name == "");
		
		if(dates.length == 0 || !name || (!start_time && !all_day) ) {
			$("#validationdiv").show();
		}
		else {
			$("#validationdiv").hide();
						
			//check to see if we are editing a multi-day event
			var group_id = <?= ($event) ? $event->group_id : "null" ?>;
			if(group_id > 0) {
				//TODO: prompt the user about editing a group event. Figure out what to do.
			}
	
			$('#addEvent').submit();
		}
	}

	$(function() {
		$("#ui-datepicker-div").multiDatesPicker({
			altField: '#date'
			<?php
				if($event && $event->date) {
					echo ",maxPicks: 1, defaultDate: \"". date("m/d/y", strtotime($event->date)) . "\", addDates: [ parseDate('" . $event->date . "') ]";
				}
			?>
		});
	});
	
	//Safer approach to creating the date from the php string rather than just dropping it right into Date() as a dateString.
	function parseDate(d) {
		var parts = d.match(/(\d+)/g);
		return new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
	}
	
	$("#members_only").change(function() {
		$("#cost_public").prop("disabled", this.checked);
	});
	
	//Disable start/end times for all-day events
	$("#all_day").change(function() {
		$("#end_time").prop("disabled", this.checked);
		$("#start_time").prop("disabled", this.checked);
	});
	
	function cancel() {
		window.location.href = "/events/special/";
	}
	
	<?php if($event) { ?>
		function deleteEvent(e) {
			e.preventDefault();
			var event_id = <?= $event->id ?>;
			var group_id = <?= $event->group_id ?>;
			
			//If this event is part of a group, we must ask what the user wants to do.
			if(group_id != "0") {
				$("#delete-confirm").dialog({
					resizable: false,
					modal: true,
					width: 500,
					buttons: {
						"Delete only this event": function() {
							postDelete({ event_type : "special", event_id : event_id });
							$(this).dialog( "close" );
						},
						"Delete all events": function() {
							postDelete({ event_type : "special", event_id : event_id, group_id: group_id });
							$(this).dialog( "close" );
						}
					}
				});
			} else {
				var yes = confirm("Are you sure you want to delete \"<?= $event->name ?>\"?");	
				if(yes) postDelete({ event_type : "special", event_id : event_id });
			}
		}
		
		function postDelete(postArgs) {
			$.ajax({
				type: "POST",
				url: "/events/php/delete.php",
				data: postArgs,
				success: function(response){
			  		if(response.trim() === "OK") {
				  		window.location.href = "/events/special/";
			  		} else {
				  		$("#errordiv").show();
			  		}
			  }
			});
		}
		
		$("#deleteLink").click(function(e) {
			deleteEvent(e);		
		});
	
	<?php } ?>

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>
