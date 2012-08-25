<?php

	//Get the time settings from the database
	$settings = $db->get_results($db->prepare("SELECT * FROM `settings`"), "OBJECT_K");
	$START_TIME = $settings["start_time"]->value;
	$END_TIME = $settings["end_time"]->value;
	$INTERVAL = $settings["time_interval"]->value;

?>

<div id="errordiv" style="margin-top:8px;" class="noDisplay error">An error occurred.</div>
<div id="successdiv" style="margin-top:8px;" class="noDisplay success">Event created successfully.</div>

<h2>Create Weekly Event</h2>
<p>These events occur every week on a specific day and time. (ex: Monday at 3pm, Friday at 11am)</p>
<form id="addEvent" enctype="multipart/form-data" action="/events/php/post_events.php" method="post">
    <table>
		<thead>
			<th style="width:130px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: </td>
	            <td><input type="text" name="name" id="name" class="inputfield" /><span class="required">*</span></td>
	        </tr>
			<tr>
	            <td>Day: </td>
	            <td><select name="day_of_week" style="width: 120px">
	            	<option selected="selected"></option>
					<?php
						$timestamp = strtotime('next Monday');
						for ($x = 0; $x < 7; $x++) {
							$name = strftime('%A', $timestamp);
							echo "<option name='day_{$x}' value='{$name}'/>{$name}</option>";
							$timestamp = strtotime('+1 day', $timestamp);
						}
					?>
	             </select><span class="required">*</span></td>
	        </tr>
			<tr>
	            <td>Start time: </td>
	            <td><select name="start_time" style="width: 120px" id="start_time">
	            	<option selected="selected"></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							echo "<option value='". date('H:i', $i) . "'>" . date('g:i A', $i) . "</option>";
						}
		            ?>
	        </select><span class="required">*</span>
	        <input type="checkbox" name="all_day" id="all_day" style="vertical-align:middle;" /><label for="all_day">All-day event</label></td>
	        </tr>
	        <tr>
	            <td>End time: </td>
	            <td><select name="end_time" style="width: 120px" id="end_time">
	            	<option selected="selected"></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							echo "<option value='". date('H:i', $i) . "'>" . date('g:i A', $i) . "</option>";
						}
		            ?>
	        </select><span class="required">*</span></td>
	        </tr>
	        <tr>
	            <td>Description: </td>
	            <td><textarea name="description" id="description"></textarea></td>
	        </tr>
	        <tr>
	            <td>Icon: </td>
	            <td><input type="file" name="thumbnail" /></td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center"><input type="submit" class="button" value="Create Event" />
				<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a></td></td>
			</tr> 
		</tbody>
    </table>
</form>

<script type="text/javascript">

	function cancel() {
		var ref = "<?= $_SERVER['HTTP_REFERER']; ?>" || "/";
		window.setTimeout("window.location.href = '" + ref + "';", 500);
	}
	
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
