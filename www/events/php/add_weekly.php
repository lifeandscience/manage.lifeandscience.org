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
			echo "<div class=\"alert alert-error\">An error occurred trying to fetch this event. Check the error log.</div>";
		}
	}

?>

<div id="errordiv" class="noDisplay alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span>An error occurred.</span>
</div>
<div id="validationdiv" style="margin-top:8px;" class="noDisplay alert alert-error">Please correct the errors below and try again.</div>

<?php
	//Need to show unique labels if we are in Edit vs. Create mode
	if($event) {
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event changes have been saved.</div>";
		echo "<h2 class=\"eventTitle\"><span class=\"editEventName\">" . $event->name . "</span>";
		echo "<a class=\"backLink\" href=\"/events/weekly\">Back to event list</a></h2>";
	} else {
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event created successfully.</div>";
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
	            <td>Event Name: <span class="required">*</span> </td>
	            <td><input type="text" name="name" id="name" class="inputfield" required value="<?= ($event) ? $event->name : "" ?>" />
	            	<span class="inlineError" id="nameError">Enter an event name</span></td>
	        </tr>
	        <tr>
	        	<td>Day(s): <span class="required">*</span></td>
	        	<td>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_mon" value="mon" <?= $event->mon == "1" ? "checked" : ""; ?> /> Mon</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_tue" value="tue" <?= $event->tue == "1" ? "checked": ""; ?> /> Tue</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_wed" value="wed" <?= $event->wed == "1" ? "checked": ""; ?> /> Wed</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_thu" value="thu" <?= $event->thu == "1" ? "checked": ""; ?> /> Thu</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_fri" value="fri" <?= $event->fri == "1" ? "checked": ""; ?> /> Fri</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_sat" value="sat" <?= $event->sat == "1" ? "checked": ""; ?> /> Sat</label>
		        	<label class="checkbox inline"><input type="checkbox" name="days[]" id="day_sun" value="sun" <?= $event->sun == "1" ? "checked": ""; ?> /> Sun</label>
		        	<span class="inlineError" id="dayError">Select a day</div>
	        	</td>
	        </tr>
			<tr>
	            <td>Start time: <span class="required">*</span></td>
	            <td><select name="start_time" style="width: 120px;margin-right:15px;" id="start_time">
	            	<option></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							$sel = ($event && $event->start_time === date('H:i', $i)) ? "selected='selected'" : "";
							echo "<option value='". date('H:i', $i) . "' {$sel} >" . date('g:i A', $i) . "</option>";
						}
		            ?>
					</select>
					<label class="checkbox inline"><input type="checkbox" name="all_day" id="all_day" <?= ($event && $event->all_day === "1") ? "checked=checked" : "" ?> /> All-day event</label>
					<span class="inlineError" id="startError">Select a start time or all-day event.</span>
				</td>
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
	            <td>

		            <div data-target="#editor" data-role="editor-toolbar" class="btn-toolbar">
				      <div class="btn-group">
				        <a title="" data-edit="bold" class="btn" data-original-title="Bold"><i class="icon-bold"></i></a>
				        <a title="" data-edit="italic" class="btn" data-original-title="Italic"><i class="icon-italic"></i></a>
				        <a title="" data-edit="strikethrough" class="btn" data-original-title="Strikethrough"><i class="icon-strikethrough"></i></a>
				        <a title="" data-edit="underline" class="btn" data-original-title="Underline"><i class="icon-underline"></i></a>
				      </div>
				      <div class="btn-group">
				        <a title="" data-edit="insertunorderedlist" class="btn" data-original-title="Bullet list"><i class="icon-list-ul"></i></a>
				        <a title="" data-edit="insertorderedlist" class="btn" data-original-title="Number list"><i class="icon-list-ol"></i></a>
				        <a title="" data-edit="outdent" class="btn" data-original-title="Reduce indent"><i class="icon-indent-left"></i></a>
				        <a title="" data-edit="indent" class="btn" data-original-title="Indent"><i class="icon-indent-right"></i></a>
				      </div>
				      <div class="btn-group">
				        <a title="" data-edit="justifyleft" class="btn" data-original-title="Align Left"><i class="icon-align-left"></i></a>
				        <a title="" data-edit="justifycenter" class="btn" data-original-title="Center"><i class="icon-align-center"></i></a>
				        <a title="" data-edit="justifyright" class="btn" data-original-title="Align Right"><i class="icon-align-right"></i></a>
				        <a title="" data-edit="justifyfull" class="btn" data-original-title="Justify"><i class="icon-align-justify"></i></a>
				      </div>
				      <div class="btn-group">
						<a title="" data-toggle="dropdown" class="btn dropdown-toggle" data-original-title="Hyperlink"><i class="icon-link"></i></a>
					    <div class="dropdown-menu input-append">
						    <input type="text" data-edit="createLink" placeholder="URL" class="span2" />
						    <button type="button" class="btn">Add</button>
						</div>
				        <a title="" data-edit="unlink" class="btn" data-original-title="Remove Hyperlink"><i class="icon-cut"></i></a>
				      </div>
				      <div class="btn-group">
				        <a id="pictureBtn" title="" class="btn" data-original-title="Insert picture (or just drag &amp; drop)"><i class="icon-picture"></i></a>
				        <input type="file" data-edit="insertImage" data-target="#pictureBtn" data-role="magic-overlay" style="opacity: 0; position: absolute; top: 0px; left: 0px; width: 39px; height: 30px;">
				      </div>
				      <div class="btn-group">
				        <a title="" data-edit="undo" class="btn" data-original-title="Undo"><i class="icon-undo"></i></a>
				        <a title="" data-edit="redo" class="btn" data-original-title="Redo"><i class="icon-repeat"></i></a>
				      </div>
				    </div>
					
					<div id="editor" class="editor"><?= ($event) ? $event->description : "" ?></div></td>
					<textarea id="description" name="description" style="display: none;"></textarea>
	        </tr>
	        <tr>
	            <td>Icon: </td>
	            <td>
	            	<span class="file-wrapper">
					  <input type="file" name="thumbnail" id="thumbnail" />
					  <span class="button">Choose a <?= ($event && $event->icon) ? " different " : "" ?> photo</span>
					</span>
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
							echo "<button class=\"btn btn-small btn-danger\" href=\"#\" id=\"deleteLink\">Delete</button>";
						}	
						
					?>
		            <input type="button" onclick="validate()" class="btn" value="<?= ($event) ? "Save" : "Create Event" ?>" />
					<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a>
				</td>
			</tr> 
		</tbody>
    </table>
</form>



<script type="text/javascript">

	initToolbarBootstrapBindings();
	$('#editor').wysiwyg({
	  hotKeys: {
	  	'shift+tab': 'outdent',
	  	'tab' : 'indent',	  
	    'ctrl+b meta+b': 'bold',
	    'ctrl+i meta+i': 'italic',
	    'ctrl+u meta+u': 'underline',
	    'ctrl+z meta+z': 'undo',
	    'ctrl+y meta+y meta+shift+z': 'redo'
	  }
	});

	function validate() {		
		var name = $('#name').val();
		var start_time = $('#start_time').val();
		var all_day = $('#all_day').is(':checked');
		
		if(!start_time && !all_day) {
			$('#startError').show();
		} else {
			$('#startError').hide();
		}
		
		//Check to make sure at least one day is selected
		var days = $("input[name='days[]']").serializeArray(); 
		if (!days.length) {
			$('#dayError').show();
		} else {
			$('#dayError').hide();
		}
		
		$('#nameError').toggle(name == "");
		
		if(!days.length || !name || (!start_time && !all_day) ) {
			$("#validationdiv").show();
		}
		else {
			$("#validationdiv").hide();
			$('#editor').cleanHtml();
			//Copy editor content into textarea before submitting.
			$('#description').val($('#editor').html());
			$('#addEvent').submit();
		}
	}

	function cancel() {
		window.location.href = "/events/weekly";
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
					  		window.location.href = "/events/weekly";
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






