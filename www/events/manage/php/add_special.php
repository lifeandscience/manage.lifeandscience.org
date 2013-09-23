<?php
	
	//Check to see if we are editing an existing event
	$event = null;
	$isArchived = 0;
	$event_id = isset($_GET["event_id"]) ? $_GET["event_id"] : null;
	if($event_id) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventById.php");
		$event = getEventById($event_id);
		if(!$event) {
			echo "<div class=\"alert alert-error\">An error occurred trying to fetch this event. Check the error log.</div>";
		}
		else if($event->active === "0") {
			//This event is archived
			$isArchived = 1;
			echo "<div class=\"alert alert-error\">This event has been archived. <a id=\"restoreLink\" href=\"#\">Restore it.</a></div>";
		}
	}
	$isDateRange = ($event && $event->end_date);
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
		if(!$isArchived) {
			echo "<a class=\"backLink\" href=\"/events/special/\">Back to event list</a></h2>";	
		} else {
			echo "<a class=\"backLink\" href=\"/events/archive/\">Back to Archive</a></h2>";
		}
		
	} else {
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event created successfully.</div>";
		echo "<h2 class=\"eventTitle\">Create Special Event</h2>";
		echo "<p class=\"description\">These events occur on a specific date(s). (ex. Dec 25 at 7 AM, Sundays in April, July 20-25)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/manage/php/post_special.php" method="post">
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
	            <td>
					<label class="radio"><input type="radio" name="dates_radio" id="radio_datenormal" value="multidate" <?= ($event && !$isDateRange) ? "checked" : "" ?>>Specify one or more individual dates (ex. July 4, 6, 8)</label>    
					<label class="radio"><input type="radio" name="dates_radio" id="radio_daterange" value="range" <?= $isDateRange ? "checked" : "" ?>>Specify a date range (ex. July 4-8)</label>
					<span class="inlineError" id="dateTypeError">Select a date type.</span>
	            	<div id="date-range-picker" class="<?= $isDateRange ? "" : "noDisplay" ?>">
	            		<input name="daterange_start" id="daterange_start" type="text" placeholder="Start Date" style="width: 165px" value="<?= $isDateRange ? date('m/d/Y',strtotime($event->date)) : "" ?>" />
	            		<input name="daterange_end" id="daterange_end" type="text" placeholder="End Date" style="width: 165px" value="<?= $isDateRange ? date('m/d/Y',strtotime($event->end_date)) : "" ?>" />
	            		<span class="inlineError" id="dateRangeError"></span>
	            	</div>	            
		            <div id="normal-date-picker" class="<?= ($event && !$isDateRange) ? "" : "noDisplay" ?>">
			            <div id="ui-datepicker-div"></div>
			            <input id="date" name="date" type="text" class="inputfield" placeholder="Select a date for this event">
			            <?php if(!$event) echo "<span class=\"tiny formHelp\">You can select multiple dates.</span>"; ?><span class="inlineError" id="dateError">Select a date</span>
		            </div>
	           </td>
	        </tr>
	        <tr>
	            <td>Custom Date Text: </td>
	            <td><input type="text" name="display_date" id="display_date" class="inputfield" value="<?= ($event) ? $event->display_date : "" ?>" />
	            <span class="tiny formHelp">Optional. Use this to override the default date description. (e.g. Mondays in May)</span></td>
	        </tr>
			<tr>
	            <td>Start Time: <span class="required">*</span></td>
	            <td>
	            	<div class="input-append bootstrap-timepicker">
			            <input name="start_time" id="start_time" type="text" class="input-small" data-default-time="false" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?> />
			            <span class="add-on"><i class="icon-time"></i></span>
			        </div>
					<label class="checkbox inline"><input type="checkbox" name="all_day" id="all_day" <?= ($event && $event->all_day === "1") ? "checked=checked" : "" ?> /> All-day event</label>
	        		<span class="inlineError" id="startError">Select a start time or all-day event.</span>
				</td>
	        </tr>
	        <tr>
	            <td>End Time: </td>
	            <td>
	            	<div class="input-append bootstrap-timepicker">
			            <input name="end_time" id="end_time" type="text" class="input-small" data-default-time="false" />
			            <span class="add-on"><i class="icon-time"></i></span>
			        </div>
			        <span class="inlineError" id="endError">End time must be later than Start time.</span>
	            </td>
	        </tr>
	        <tr>
	            <td>Sunday Start Time: </td>
	            <td>
	            	<div class="input-append bootstrap-timepicker">
			            <input name="sun_start_time" id="sun_start_time" type="text" class="input-small" data-default-time="false" <?= ($event && $event->all_day === "1") ? "disabled" : "" ?> />
			            <span class="add-on"><i class="icon-time"></i></span>
			        </div>
			        <span class="tiny formHelp">Optional. Specify a special time for Sundays (if applicable).</span>
				</td>
	        </tr>
	        <tr>
	            <td>Sunday End Time: </td>
	            <td>
	            	<div class="input-append bootstrap-timepicker">
			            <input name="sun_end_time" id="sun_end_time" type="text" class="input-small" data-default-time="false" />
			            <span class="add-on"><i class="icon-time"></i></span>
			        </div>
			        <span class="inlineError" id="sundayEndError">Sunday End time must be later than Sunday Start time.</span>
	            </td>
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
				        <a title="" onclick="viewHTML(this)" class="btn" data-original-title="View HTML"><i class="icon-code"></i></a>
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
	            <td>Event URL: </td>
	            <td><input type="text" name="url" id="url" class="inputfield" placeholder="http://" value="<?= ($event) ? $event->url : "" ?>" />
	            <span class="tiny formHelp">Optional. Enter an existing URL that you would like this event to link to.</span></td>
	        </tr>
	        <tr>
	            <td>Custom Field: </td>
	            <td><input type="text" name="custom_1" id="custom_1" class="inputfield" value="<?= ($event) ? $event->custom_1 : "" ?>" />
	            <span class="tiny formHelp" >Specify age limitations, or other special requirements.</span></td>
	        </tr>
	        <tr>
	            <td>Cost (Members): </td>
	            <td><input type="text" name="cost_members" placeholder="$" id="cost_members" class="inputfield short" value="<?= ($event) ? $event->cost_members : "" ?>" style="margin-right:15px;" />
					<label class="checkbox inline"><input type="checkbox" name="members_only" id="members_only" <?= ($event && $event->members_only === "1") ? "checked=checked" : "" ?> /> This event is for members only</label>
	            </td>
	        </tr>
	        <tr>
	            <td>Cost (Public): </td>
	            <td><input type="text" name="cost_public" placeholder="$" id="cost_public" class="inputfield short" value="<?= ($event) ? $event->cost_public : "" ?>" 
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
							echo "<input type=\"hidden\" name=\"originalImage\" id=\"originalImage\" value=\"" . $event->image . "\" />";
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
	            <td>Adults Only: </td>
	            <td>
					<label class="checkbox inline"><input type="checkbox" name="adult_only" id="adult_only" <?= ($event && $event->adult_only === "1") ? "checked=checked" : "" ?> /> This event is for adults only</label>
				</td>
	        </tr>
	        <tr>
	            <td>Facebook URL: </td>
	            <td><input type="text" name="fb_link" id="fb_link" placeholder="http://" class="inputfield" value="<?= ($event) ? $event->fb_link : "" ?>" /></td>
	        </tr>
	        <tr>
	            <td colspan="2" align="center">
		            <?php
					
						//Show an archive or delete link if we are in edit mode
						if($event) {
							if($isArchived) {
								echo "<button class=\"btn btn-small btn-danger\" href=\"#\" id=\"deleteLink\">Delete Permanently</button>";
							} else {
								echo "<button class=\"btn btn-small btn-danger\" href=\"#\" id=\"deleteLink\">Archive</button>";
							}
						}
						
					?>
		            <input type="button" onclick="validate()" class="btn" value="<?= ($event) ? "Save" : "Create Event" ?>" />
					<span class="tiny">or</span> <a class="tiny" href="#" onclick="cancel();">Cancel</a>
				</td>
			</tr> 
		</tbody>
    </table>
</form>

<div id="delete-confirm" title="You are deleting an event." style="display:none;">
	<p>Do you want to <b>permanently delete</b> all occurrences of this event, or only the selected occurrence?</p>
</div>
<div id="archive-confirm" title="You are archiving an event." style="display:none;">
	<p>Do you want to archive all occurrences of this event, or only the selected occurrence?</p>
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
	
	var datepicker = $.fn.datepicker.noConflict(); // return $.fn.datepicker to previously assigned value
	$.fn.bootstrapDP = datepicker;  // give $().bootstrapDP the bootstrap-datepicker functionality
	
	$('#start_time').timepicker();
	$('#end_time').timepicker();
	$('#sun_start_time').timepicker();
	$('#sun_end_time').timepicker();	
		
	$('#clearicon').click(function(e) {
		e.preventDefault();
		$('#theicon').hide();
		$('#clearicon').hide();
		$('#removeicon').val(true);
	});
	
	//Toggle between the two date formats
	$('#radio_datenormal').click(function(e) {
		$('#date-range-picker').hide();
		$('#normal-date-picker').show();
		$('#dateTypeError').hide();
	});
	$('#radio_daterange').click(function(e) {
		$('#date-range-picker').show();
		$('#normal-date-picker').hide();
		$('#dateTypeError').hide();
	});
	
	//Create a custom button for toggling HTML view on/off
	var viewMode = "text";
	function viewHTML(btn) {
		if(viewMode == "text") {
			var html = $('#editor').html();
			$('#editor').text(html);
			viewMode = "html";
			if(btn) $(btn).attr("data-original-title", "Switch to WYSIWYG Editor");
		} else {
			var text = $('#editor').text();
			$('#editor').html(text);
			viewMode = "text";
			if(btn) $(btn).attr("data-original-title", "View HTML");
		}
	}
	
	function submitForm() {
		$('#editor').cleanHtml();
		//Copy editor content into textarea before submitting.
		$('#description').val($('#editor').html());
		$('#addEvent').submit();
	}
	
	function isEndTimeValid(start_time, end_time) {
		//Make sure the end time is not earlier than start time
		var end_time_error = false;
		if(end_time && start_time) {
			var sTime = new Date("1/1/2013 " + start_time);
			var eTime = new Date("1/1/2013 " + end_time);
			if(eTime < sTime) {
				end_time_error = true;
			}
		}
		return !end_time_error;
	}

	function validate() {
		var name = $('#name').val();
		var start_time = $('#start_time').val();
		var end_time = $('#end_time').val();
		var sun_start_time = $('#sun_start_time').val();
		var sun_end_time = $('#sun_end_time').val();
		var all_day = $('#all_day').is(':checked');
		
		var date_format = $('input:radio[name=dates_radio]:checked').val();
		var areDatesValid = false;
		if(date_format === "range") {
			var start_date = $('#daterange_start').val();
			var end_date = $('#daterange_end').val();
			var sDate = new Date(start_date);
			var eDate = new Date(end_date);
			if(sDate && eDate && (eDate < sDate)) {
				$('#dateRangeError').html("End date must be later than start date.");
			} else if(start_date && end_date) {
				areDatesValid = true;
			} else if(start_date) {
				$('#dateRangeError').html("You must select an end date.");
			} else {
				$('#dateRangeError').html("You must select a start date.");	
			}
			$('#dateRangeError').toggle(!areDatesValid);
			
		} else if(date_format === "multidate") {
			var dates = $('#ui-datepicker-div').multiDatesPicker('getDates');
			$('#dateError').toggle(!dates.length);
			areDatesValid = dates.length > 0;
		} else {
			//No date type selected. You must select a date!
			$('#dateTypeError').show();						
		}

		var isEndValid = isEndTimeValid(start_time, end_time);
		$('#endError').toggle(!isEndValid);
		
		var isSundayEndValid = isEndTimeValid(sun_start_time, sun_end_time);
		$('#sundayEndError').toggle(!isSundayEndValid);
		
		if(!start_time && !all_day) {
			$('#startError').show();
		} else {
			$('#startError').hide();
		}
			
		$('#nameError').toggle(name == "");
		
		if( !areDatesValid || !name || (!start_time && !all_day) || !isEndValid || !isSundayEndValid) {
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
		$('#daterange_start').bootstrapDP().on("changeDate", function() {
			$('#daterange_start').bootstrapDP("hide");
			$('#daterange_end').focus();
		});
		$('#daterange_end').bootstrapDP().on("changeDate", function() {
			$('#daterange_end').bootstrapDP("hide");
		});
		
		$("#ui-datepicker-div").multiDatesPicker({
			altField: '#date'
			<?php
				if($event && $event->date && !$event->end_date) {
					echo ", defaultDate: \"". date("m/d/y", strtotime($event->date)) . "\", addDates: [ parseDate('" . $event->date . "') ]";
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
		$("#sun_start_time").prop("disabled", this.checked);
		$("#sun_end_time").prop("disabled", this.checked);
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
			if($('#sun_start_time').val() != "<?= $event->sun_start_time ?>") return true;
			if($('#sun_end_time').val() != "<?= $event->sun_end_time ?>") return true;			
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
			var delete_permanent = <?= $isArchived ?>;

			//If this event is part of a group, we must ask what the user wants to do.
			if(group_id != "0") {
				if(delete_permanent) {
					$("#delete-confirm").dialog({
						resizable: false,
						modal: true,
						width: 500,
						buttons: {
							"Delete only this event": function() {
								postDelete({ event_type : "special", event_id : event_id, delete_permanent: delete_permanent });
								$(this).dialog( "close" );
							},
							"Delete all events": function() {
								postDelete({ event_type : "special", event_id : event_id, group_id: group_id, delete_permanent: delete_permanent });
								$(this).dialog( "close" );
							}
						}
					});
				} else {
					//Archive!
					$("#archive-confirm").dialog({
						resizable: false,
						modal: true,
						width: 500,
						buttons: {
							"Archive only this event": function() {
								postDelete({ event_type : "special", event_id : event_id });
								$(this).dialog( "close" );
							},
							"Archive all events": function() {
								postDelete({ event_type : "special", event_id : event_id, group_id: group_id });
								$(this).dialog( "close" );
							}
						}
					});
				}
				
			} else {
				if(delete_permanent) {
					var yes = confirm("Are you sure you want to permanently delete \"<?= $event->name ?>\"? This action cannot be reversed.");
				} else {
					var yes = confirm("Are you sure you want to archive \"<?= $event->name ?>\"?");	
				}
				if(yes) postDelete({ event_type : "special", event_id : event_id, delete_permanent: delete_permanent });
			}
		}
		
		function postDelete(postArgs) {
			var isArchived = <?= $isArchived ?>;
			$.ajax({
				type: "POST",
				url: "/events/manage/php/delete.php",
				data: postArgs,
				success: function(response){
			  		if(response.trim() === "OK") {
			  			if(isArchived) {
				  			window.location.href = "/events/archive/";
			  			} else {
				  			window.location.href = "/events/special/";	
			  			}
				  		
			  		} else {
				  		$("#errordiv").show();
			  		}
			  }
			});
		}
		
		function restoreEvent(e) {
			e.preventDefault();
			var event_id = <?= $event->id ?>;
			$.ajax({
				type: "POST",
				url: "/events/manage/php/restore.php",
				data: { event_type : "special", event_id : event_id },
				success: function(response){
			  		if(response.trim() === "OK") {
				  		window.location.href = "/events/special";
			  		} else {
				  		$("#errordiv").show();
			  		}
			  	}
			});
		}
		
		<?php
			if($event->start_time) {
				echo "$('#start_time').timepicker('setTime', '" . date("h:i A", strtotime($event->start_time)) . "');";		
			}
			if($event->end_time) {
				echo "$('#end_time').timepicker('setTime', '" . date("h:i A", strtotime($event->end_time)) . "');";		
			}
			if($event->sun_start_time) {
				echo "$('#sun_start_time').timepicker('setTime', '" . date("h:i A", strtotime($event->sun_start_time)) . "');";		
			}
			if($event->sun_end_time) {
				echo "$('#sun_end_time').timepicker('setTime', '" . date("h:i A", strtotime($event->sun_end_time)) . "');";		
			}
		?>
		
		$("#restoreLink").click(function(e) {
			restoreEvent(e);		
		});
		
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
