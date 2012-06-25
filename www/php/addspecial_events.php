<?php

	//Get the time settings from the database
	$settings = $db->get_results($db->prepare("SELECT * FROM `settings`"), "OBJECT_K");
	$START_TIME = $settings["start_time"]->value;
	$END_TIME = $settings["end_time"]->value;
	$INTERVAL = $settings["time_interval"]->value;

?>

<div id="errordiv" style="margin-top:8px;" class="noDisplay error">An error occurred.</div>
<div id="successdiv" style="margin-top:8px;" class="noDisplay success">Event created successfully.</div>

<h2>Create Special Event</h2>
<p>These events occur on a specific date(s). (ex. Dec 25, 2012 at 7 AM, Sundays in April)</p>
<form id="addEvent" enctype="multipart/form-data" action="/php/post_special_events.php" method="post">
    <table>
		<thead>
			<th style="width:150px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: </td>
	            <td><input type="text" name="name" id="name" class="inputfield" /><span class="required">*</span></td>
	        </tr>
			<tr>
	            <td>Date(s): </td>
	            <td><div id="ui-datepicker-div"></div>
	            <input id="date" name="date" type="text" class="inputfield" placeholder="Select a date for this event"><span class="required">*</span>
	            <span class="tiny">You can select multiple dates.</span></td>
	        </tr>
			<tr>
	            <td>Time: </td>
	            <td><select name="time" style="width: 120px">
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
	            <td>Custom Field: </td>
	            <td><input type="text" name="custom_1" id="custom_1" class="inputfield" />
	            <span class="tiny">Specify age limitations, or other special requirements.</span></td>
	        </tr>
	        <tr>
	            <td>Cost (Members): </td>
	            <td><input type="text" name="cost_members" id="cost_members" class="inputfield short" />
		            <input type="checkbox" name="members_only" id="members_only" style="vertical-align:middle;" /><label for="members_only">This event is for members only</label>
	            </td>
	        </tr>
	        <tr>
	            <td>Cost (Public): </td>
	            <td><input type="text" name="cost_public" id="cost_public" class="inputfield short" /></td>
	        </tr>
	        <tr>
	            <td>Image: </td>
	            <td><input type="file" name="thumbnail" /></td>
	        </tr>
	        <tr>
	            <td>Special Note: </td>
	            <td><textarea name="special_note" id="special_note" class="short"></textarea>
	            <span class="tiny">This message will be displayed under the image.</span></td>
	        </tr>
	        <tr>
	            <td>Facebook URL: </td>
	            <td><input type="text" name="fb_link" id="fb_link" class="inputfield" /></td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center"><input type="submit" class="button" value="Create Event" />
				<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a></td></td>
			</tr> 
		</tbody>
    </table>
</form>

<script type="text/javascript">

	$(function() {
		$("#ui-datepicker-div ").multiDatesPicker({
			altField: '#date'
		});
	});
	
	$("#members_only").change(function() {
		$("#cost_public").prop("disabled", this.checked);
	});
	
	function cancel() {
		var ref = "<?= $_SERVER['HTTP_REFERER']; ?>" || "/";
		window.setTimeout("window.location.href = '" + ref + "';", 500);
	}

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>
