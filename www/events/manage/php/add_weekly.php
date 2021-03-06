<?php
	
	//Check to see if we are editing an existing event
	$event = null;
	$isArchived = 0;
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
		else if($event->active === "0") {
			//This event is archived
			$isArchived = 1;
			echo "<div class=\"alert alert-error\">This event has been archived. <a id=\"restoreLink\" href=\"#\">Restore it.</a></div>";
		}
	}
	
	$REGISTRATION_CODES = array("Free with admission", "Fee applies", "Register", "Buy Tickets", "Sold Out"); //use index as 'code'

?>

<div id="errordiv" class="noDisplay alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span>An error occurred.</span>
</div>
<div id="validationdiv" style="margin-top:8px;" class="noDisplay alert alert-error">Please correct the errors below and try again.</div>

<?php
	//Need to show unique labels if we are in Edit vs. Create mode
	if($event) {
	
		//Create the "View" links
		$viewLinks = "<div class=\"createLink\"> (View: <a href=\"";
		
		//Determine the first day that this event occurs on
		if($event->mon) $_dayName = "Monday";
		else if($event->tue) $_dayName = "Tuesday";
		else if($event->wed) $_dayName = "Wednesday";
		else if($event->thu) $_dayName = "Thursday";
		else if($event->fri) $_dayName = "Friday";
		else if($event->sat) $_dayName = "Saturday";
		else if($event->sun) $_dayName = "Sunday";
		
		$dispDate = date('Ymd', strtotime("this " . $_dayName));
		
		$viewLinks .= NCMLS_DAILY_EVENTS_ENDPOINT . "?date=" . $dispDate;
		$viewLinks .= "\">Desktop</a> / <a href=\"";
		$viewLinks .= NCMLS_DAILY_EVENTS_ENDPOINT_MOBILE . "?date=" . $dispDate;
		$viewLinks .= "\">Mobile</a>)</div>";
		
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event has been saved.</div>";
		echo "<h2 class=\"eventTitle\"><span class=\"editEventName\">" . $event->name . $viewLinks . "</span>";
		if(!$isArchived) {
			echo "<a class=\"backLink\" href=\"/events/weekly/\">Back to event list</a></h2>";	
		} else {
			echo "<a class=\"backLink\" href=\"/events/archive/\">Back to Archive</a></h2>";
		}
	} else {
		echo "<h2 class=\"eventTitle\">Create Daily Event</h2>";
		echo "<p class=\"description\">These events occur every week on a specific day and time. (ex: Monday at 3pm, Friday at 11am)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/manage/php/post_weekly.php" method="post">
	<?php
		//We should send the event_id so the posting script knows to do an update instead of an insert
		if($event_id) {
			echo "<input id='event_id' name='event_id' type='hidden' value='" . $event_id ."' />";
		}
	?>
    <table>
		<thead>
			<th style="width:200px;">&nbsp;</th>
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
	            <td>End time: </td>
	            <td>
	            	<div class="input-append bootstrap-timepicker">
			            <input name="end_time" id="end_time" type="text" class="input-small" data-default-time="false" />
			            <span class="add-on"><i class="icon-time"></i></span>
			        </div>
			        <span class="inlineError" id="endError">End time must be later than Start time.</span>
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
				        <a title="" onclick="viewHTML(this, '#editor')" class="btn" data-original-title="View HTML"><i class="icon-code"></i></a>
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
	        	<td>Registration Type: <span class="required">*</span></td>
	        	<td>
	        		<?php
	        			foreach($REGISTRATION_CODES as $code => $label) {
		        			$checked = ($event && $event->registration_code == $code) ? "checked" : "";
		        			echo "<label class=\"radio\"><input type=\"radio\" name=\"registration_radio\" value=\"" . $code . "\"" . $checked . " >" . $label . "</label>";
	        			}
	        		?>
	        		<span class="inlineError" id="registrationError">You must select a registration type.</span>
	        	</td>
	        </tr>
	        <tr>
	        	<td>Registration URL: </td>
	        	<td><input type="text" name="registration_url" id="registration_url" class="inputfield" placeholder="http://" value="<?= ($event) ? $event->registration_url : "" ?>" />
	        	<span class="tiny formHelp">RegOnline Link. (Required if "Register" or "Buy Tickets" is selected.)</span></td>
	        </tr>
	        <tr>
	            <td>Image: </td>
	            <td>
	            	<span class="file-wrapper">
					  <input type="file" name="thumbnail" id="thumbnail" />
					  <input type="hidden" name="removeicon" id="removeicon" value="false" />
					  <span class="button">Choose a <?= ($event && $event->icon) ? " different " : "" ?> photo</span> 
					</span>
		            <?php
		            	if($event && $event->icon) {
		            		$path = "/events/uploads/" . $event->icon;
		            		echo "<img src=\"" . $path . "\" height='50' width='50' id=\"theicon\" />";
		            		echo "<button id=\"clearicon\" title=\"Remove icon\">&times;</button>";
		            	}
		            ?>
		            <span class="tiny formHelp" style="vertical-align:top;margin-top:10px;display:inline-block;">Menu/Default Image (550x300px)</span></td>
	            </td>
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
	  },
	  uploadScript: '/events/manage/php/upload_photo.php'
	});
	
	$('#start_time').timepicker();
	$('#end_time').timepicker();
	
	$('#clearicon').click(function(e) {
		e.preventDefault();
		$('#theicon').hide();
		$('#clearicon').hide();
		$('#removeicon').val(true);
	});
	

	//Create a custom button for toggling HTML view on/off
	function viewHTML(btn, editor) {
		var $editor = $(editor);
		var viewMode = $editor.attr("data-viewMode");

		//Set default
		if(!viewMode) {
			viewMode = "text";
		}
		if(viewMode == "text") {
			var html = $(editor).html();
			$editor.text(html);
			$editor.attr("data-viewMode", "html");
			if(btn) $(btn).attr("data-original-title", "Switch to WYSIWYG Editor");
		} else {
			var text = $(editor).text();
			$editor.html(text);
			$editor.attr("data-viewMode", "text");
			if(btn) $(btn).attr("data-original-title", "View HTML");
		}
	}
	
	function copyHtmlContent(editor, textarea) {
		var $editor = $(editor);
		$editor.cleanHtml();
		var viewMode = $editor.attr("data-viewMode");
		if(viewMode == "html") {
			console.log($editor.text());
			$(textarea).val($editor.text());	
		} else {
			$(textarea).val($editor.html());
		}
	}

	function validate() {		
		var name = $('#name').val();
		var start_time = $('#start_time').val();
		var end_time = $('#end_time').val();
		var all_day = $('#all_day').is(':checked');
		var registration_radio = $('input:radio[name=registration_radio]:checked').length;
		
		if(!registration_radio) {
			$('#registrationError').show();
		} else {
			$('#registrationError').hide();
		}
		
		//Make sure the end time is not earlier than start time
		var end_time_error = false;
		if(end_time && start_time) {
			var sTime = new Date("1/1/2013 " + start_time);
			var eTime = new Date("1/1/2013 " + end_time);
			if(eTime < sTime) {
				end_time_error = true;
				$('#endError').show();
			} else {
				$('#endError').hide();
			}
		}
		
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
		
		if(!days.length || !name || (!start_time && !all_day) || end_time_error || !registration_radio) {
			$("#validationdiv").show();
		}
		else {
			$("#validationdiv").hide();
			copyHtmlContent('#editor', '#description');
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
			var delete_permanent = <?= $isArchived ?>;
			if(delete_permanent) {
				var yesDelete = confirm("Are you sure you want to permanently delete \"<?= $event->name ?>\"? This action cannot be reversed.");
			} else {
				var yesDelete = confirm("Are you sure you want to archive \"<?= $event->name ?>\"?");	
			}
			
			if(yesDelete) {
				$.ajax({
					type: "POST",
					url: "/events/manage/php/delete.php",
					data: { event_type : "weekly", event_id : event_id, delete_permanent: delete_permanent },
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
		
		function restoreEvent(e) {
			e.preventDefault();
			var event_id = <?= $event->id ?>;
			$.ajax({
				type: "POST",
				url: "/events/manage/php/restore.php",
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
		
		<?php
			if($event->start_time) {
				echo "$('#start_time').timepicker('setTime', '" . date("h:i A", strtotime($event->start_time)) . "');";		
			}
			if($event->end_time) {
				echo "$('#end_time').timepicker('setTime', '" . date("h:i A", strtotime($event->end_time)) . "');";		
			}
		?>
		
		$("#restoreLink").click(function(e) {
			restoreEvent(e);		
		});
		
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
		var event_id = getQueryParam("eid");
		if(event_id) $('<a>',{ text: 'View your new event.', href: '/events/weekly/edit/' + event_id }).appendTo("#successdiv");
		$("#successdiv").show();
	}

</script>






