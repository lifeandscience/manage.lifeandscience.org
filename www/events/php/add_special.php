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
		echo "<h2 class=\"eventTitle\"><span class=\"editEventName\">" . $event->name . "</span>" ;
		echo "<a class=\"backLink\" href=\"/events/special/\">Back to event list</a></h2>";
	} else {
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event created successfully.</div>";
		echo "<h2 class=\"eventTitle\">Create Special Event</h2>";
		echo "<p class=\"description\">These events occur on a specific date(s). (ex. Dec 25, 2012 at 7 AM, Sundays in April)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/php/post_special.php" method="post">
	<?php
		//We should send the event_id so the posting script knows to do an update instead of an insert
		if($event_id) {
			echo "<input id='event_id' name='event_id' type='hidden' value='" . $event_id ."' />";
			echo "<input id='group_id' name='group_id' type='hidden' value='" . $event->group_id ."' />";
			echo "<input id='edit_all' name='edit_all' type='hidden' value='' />";
		}
	?>
    <table>
		<thead>
			<th style="width:150px;">&nbsp;</th>
			<th>&nbsp;</th>
		</thead>
		<tbody>
	        <tr>
	            <td>Event Name: <span class="required">*</span></td>
	            <td><input type="text" name="name" id="name" class="inputfield" required value="<?= ($event) ? $event->name : "" ?>" />
		            <span class="inlineError" id="nameError">Enter an event name</span></td>
	        </tr>
			<tr>
	            <td>Date(s): <span class="required">*</span></td>
	            <td><div id="ui-datepicker-div"></div>
	            <input id="date" name="date" type="text" class="inputfield" placeholder="Select a date for this event" required>
	            <?php if(!$event) echo "<span class=\"tiny formHelp\">You can select multiple dates.</span>"; ?><span class="inlineError" id="dateError">Select a date</span></td>
	        </tr>
			<tr>
	            <td>Start time: <span class="required">*</span></td>
	            <td><select name="start_time" style="width: 120px;margin-right:15px;" id="start_time" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?>>
	            	<option selected="selected"></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
			            $matchFound = false;
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							$sel = "";
							if($event && $event->start_time === date('H:i', $i)) {
								$sel = "selected='selected'";
								$matchFound = true;
							}
							echo "<option value='". date('H:i', $i) . "' {$sel} >" . date('g:i A', $i) . "</option>";
						}
						if($event && !$matchFound) {
							//Time did not match any possible values. It's likely that the default interval was changed since this event was created.
							//Just add it into the select box anyway, since the user wants to see the current value.
							echo "<option value='". date('H:i', strtotime($event->start_time)) . "' selected='selected' >" . date('g:i A', strtotime($event->start_time)) . "</option>";
						}
		            ?>
					</select>
					<label class="checkbox inline"><input type="checkbox" name="all_day" id="all_day" <?= ($event && $event->all_day === "1") ? "checked=checked" : "" ?> /> All-day event</label>
	        		<span class="inlineError" id="startError">Select a start time or all-day event.</span>
				</td>
	        </tr>
	        <tr>
	            <td>End time: </td>
	            <td><select name="end_time" style="width: 120px" id="end_time" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?> >
	            	<option selected="selected"></option>
		            <?php
			            $begin = strtotime($START_TIME);
			            $end = strtotime($END_TIME);
			            $matchFound = false;
						for ($i = $begin; $i <= $end; $i += 60 * $INTERVAL) {
							$sel = "";
							if($event && $event->end_time === date('H:i', $i)) {
								$sel = "selected='selected'";
								$matchFound = true;
							}
							echo "<option value='". date('H:i', $i) . "' {$sel} >" . date('g:i A', $i) . "</option>";
						}
						if($event && !$matchFound) {
							//Time did not match any possible values. It's likely that the default interval was changed since this event was created.
							//Just add it into the select box anyway, since the user wants to see the current value.
							echo "<option value='". date('H:i', strtotime($event->end_time)) . "' selected='selected' >" . date('g:i A', strtotime($event->end_time)) . "</option>";
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
					<textarea id="original_description" name="original_description" style="display: none;"><?= ($event) ? $event->description : "" ?></textarea>
	        </tr>
	        <tr>
	            <td>Custom Field: </td>
	            <td><input type="text" name="custom_1" id="custom_1" class="inputfield" value="<?= ($event) ? $event->custom_1 : "" ?>" />
	            <span class="tiny formHelp" >Specify age limitations, or other special requirements.</span></td>
	        </tr>
	        <tr>
	            <td>Cost (Members): </td>
	            <td><input type="text" name="cost_members" id="cost_members" class="inputfield short" value="<?= ($event) ? $event->cost_members : "" ?>" style="margin-right:15px;" />
					<label class="checkbox inline"><input type="checkbox" name="members_only" id="members_only" <?= ($event && $event->members_only === "1") ? "checked=checked" : "" ?> /> This event is for members only</label>
	            </td>
	        </tr>
	        <tr>
	            <td>Cost (Public): </td>
	            <td><input type="text" name="cost_public" id="cost_public" class="inputfield short" value="<?= ($event) ? $event->cost_public : "" ?>" 
	            		<?= ($event && $event->members_only === "1") ? "disabled" : "" ?> /></td>
	        </tr>
	        <tr>
	            <td>Image: </td>
	            <td>
	            	<span class="file-wrapper">
					  <input type="file" name="thumbnail" id="thumbnail" />
					  <input type="hidden" name="removeicon" id="removeicon" value="false" />
					  <span class="button">Choose a <?= ($event && $event->image) ? " different " : "" ?> photo</span>
					</span>
		            <?php
		            	if($event && $event->image) {
		            		$path = "/events/uploads/" . $event->image;
		            		echo "<img src=\"" . $path . "\" height='50' width='50' id=\"theicon\" />";
		            		echo "<button id=\"clearicon\" title=\"Remove image\">&times;</button>";
		            	}
		            ?>
	            </td>
	        </tr>
	        <tr>
	            <td>Special Note: </td>
	            <td><textarea name="special_note" id="special_note" class="short"><?= ($event) ? $event->special_note : "" ?></textarea>
	            <span class="tiny formHelp">This message will be displayed under the image.</span></td>
	        </tr>
	        <tr>
	            <td>Facebook URL: </td>
	            <td><input type="text" name="fb_link" id="fb_link" class="inputfield" value="<?= ($event) ? $event->fb_link : "" ?>" /></td>
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

<div id="delete-confirm" title="You're deleting an event." style="display:none;">
	<p>Do you want to delete this and all occurrences of this event, or only the selected occurrence?</p>
</div>
<div id="edit-confirm" title="You're changing a repeating event." style="display:none;">
	<p>Do you want to change only this occurrence of the event, or this and all occurrences?</p>
</div>

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
	
	$('#clearicon').click(function(e) {
		e.preventDefault();
		$('#theicon').hide();
		$('#clearicon').hide();
		$('#removeicon').val(true);
	});
	
	function submitForm() {
		$('#editor').cleanHtml();
		//Copy editor content into textarea before submitting.
		$('#description').val($('#editor').html());
		$('#addEvent').submit();
	}

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
				var hasChanged = hasEventChanged();
				if(hasChanged) {
					//Prompt user to edit all events or only this one
					$("#edit-confirm").dialog({
						resizable: false,
						modal: true,
						width: 500,
						buttons: {
							"Only This Event": function() {
								$("#edit_all").val("false");
								$(this).dialog( "close" );
								submitForm()
							},
							"All Events": function() {
								$("#edit_all").val("true");
								$(this).dialog( "close" );
								submitForm()
							}	
						}
					});
				} else {
					submitForm()
				}
			} else {
				submitForm()				
			}
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
	
		//checks to see if at least 1 *non-date* field has changed
		//only called when we are editing a group event
		function hasEventChanged() {
			if($('#name').val() != "<?= $event->name ?>") return true;
			if($('#editor').html() != $('#original_description').val()) return true;
			if($('#start_time').val() != "<?= $event->start_time ?>") return true;
			if($('#end_time').val() != "<?= $event->end_time ?>") return true;
			if($('#fb_link').val() != "<?= $event->fb_link ?>") return true;
			if($('#thumbnail').val() != "") return true;
			if($('#special_note').val() != "<?= $event->special_note ?>") return true;
			if($('#custom_1').val() != "<?= $event->custom_1 ?>") return true;	
			if($('#cost_members').val() != "<?= $event->cost_members ?>") return true;
			if($('#cost_public').val() != "<?= $event->cost_public ?>") return true;	
			if($('#members_only').is(':checked') != <?= ($event->members_only === "1") ? 1 : 0 ?>) return true;
			if($('#all_day').is(':checked') != <?= ($event->all_day === "1") ? 1 : 0 ?>) return true;
			if($('#removeicon').val() === "true") return true;
			return false; //only date changed, or nothing changed at all
		}
	
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
