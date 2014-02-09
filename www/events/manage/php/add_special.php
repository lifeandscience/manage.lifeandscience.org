<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$MANAGE_ENDPOINT = "http://manage.lifeandscience.org";
	$upload_dir = "/events/uploads/";
	
	//Check to see if we are editing an existing event
	$event = null;
	$isArchived = 0;
	$event_id = isset($_GET["event_id"]) ? $_GET["event_id"] : null;
	if($event_id) {
		require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventById.php");
		$event = getEventById($event_id);
		if(!$event) {
			echo "<div class=\"alert alert-warning\">Unable to load event. This can occur if you just removed one or more dates from an existing event. <a href=\"/events/special\">Go back.</a></div>";
		}
		else if($event->active === "0") {
			//This event is archived
			$isArchived = 1;
			echo "<div class=\"alert alert-error\">This event has been archived. <a id=\"restoreLink\" href=\"#\">Restore it.</a></div>";
		}
		//Get a list of all the other event dates that exist with the same group_id
		$all_dates = array();
		$formatted_dates = array();
		if($event && $event->group_id && $event->group_id !== "0" ) {
			$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
			$events = $db->get_results($db->prepare("SELECT `date`, `end_date`, `id` FROM `events_special` WHERE `group_id` = %d", $event->group_id));
			foreach($events as $ev) {
				array_push($all_dates, date("m/d/Y", strtotime($ev->date)));
				array_push($formatted_dates, strtotime($ev->date) * 1000); //needed for Multi-Date Picker
				
				//TODO: What should we do here if the event also has an end_date (meaning it's a range... Not supported?)
			}
			$all_dates_string = implode(",", $all_dates);
			$all_dates_formatted_string = implode(",", $formatted_dates);			
		} else {
			$all_dates_string = date("m/d/Y", strtotime($event->date));
		}
	}
	$isDateRange = ($event && $event->end_date);
	
	//Get the list of all tags
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getTags.php");
	$tags = getTags();
	
	$REGISTRATION_CODES = array("Free with admission", "Fee applies", "Register", "Buy Tickets", "Sold Out"); //use index as 'code'
	$DEFAULT_TWEET = "Check out this cool event happening @lifeandscience: ";
	
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
		$viewLinks .= NCMLS_MONTHLY_DETAILS_ENDPOINT . "?id=" . $event->id;
		$viewLinks .= "\">Desktop</a> / <a href=\"";
		$viewLinks .= NCMLS_MONTHLY_DETAILS_ENDPOINT_MOBILE . "?id=" . $event->id;
		$viewLinks .= "\">Mobile</a>)</div>";
		
		echo "<div id=\"successdiv\" class=\"noDisplay alert alert-success\">";
		echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>Event has been saved.</div>";
		echo "<h2 class=\"eventTitle\"><span class=\"editEventName\">" . $event->name . $viewLinks . "</span>" ;
		if(!$isArchived) {
			echo "<a class=\"backLink\" href=\"/events/special/\">Back to event list</a></h2>";	
		} else {
			echo "<a class=\"backLink\" href=\"/events/archive/\">Back to Archive</a></h2>";
		}
		
	} else {
		echo "<h2 class=\"eventTitle\">Create Monthly Event</h2>";
		echo "<p class=\"description\">These events occur on a specific date(s). (ex. Dec 25 at 7 AM, Sundays in April, July 20-25)</p>";
	}
?>

<form id="addEvent" enctype="multipart/form-data" action="/events/manage/php/post_special.php" method="post">
	<input type="hidden" name="isLab" id="isLab" value="0" />
	<?php
		//We should send the event_id so the posting script knows to do an update instead of an insert
		if($event_id) {
			echo "<input id='event_id' name='event_id' type='hidden' value='" . $event_id ."' />";
			echo "<input id='group_id' name='group_id' type='hidden' value='" . $event->group_id ."' />";
			echo "<input id='original_dates' name='original_dates' type='hidden' value='" . $all_dates_string ."' />";
			echo "<input id='edit_all' name='edit_all' type='hidden' value='' />";
		}
	?>
    <table>
		<thead>
			<th style="width:170px;">&nbsp;</th>
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
			            <textarea id="date" name="date" type="text" class="inputfield" placeholder="Select a date for this event" style="height:100px;"></textarea>
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
	        	<td>Tag(s):</td>
	        	<td>
	        		<?php
	        			if($event) {
		        			$existing_tags = explode(",", $event->tags);
	        			}
	        			foreach($tags as $tag) {
							$tagId = $tag->id;
							$selected = "";
							if($existing_tags && in_array($tagId, $existing_tags)) {
								$selected = "checked";
							}
		        			echo "<label class=\"checkbox inline\"><input type=\"checkbox\" name=\"tags[]\" id=\"tag_" . $tagId . "\" value=\"" . $tagId . "\" " . $selected. " /> " . $tag->tag . "</label>";		
	        			}
	        		?>
		        	<span class="inlineError" id="dayError">Select a tag</div>
	        	</td>
	        </tr>
	        <tr>
	            <td>Summary: </td>
	            <td>
		            <div data-target="#editor" id="editor-toolbar" class="btn-toolbar">
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
					
					<div id="editor" class="editor"><?= ($event) ? $event->description : "" ?></div>
					<span class="tiny formHelp" style="margin:0;">A brief blurb excerpt approximately 40 words, more or less.</span>
					<textarea id="description" name="description" style="display: none;"></textarea>
					<textarea id="original_description" name="original_description" style="display: none;"><?= ($event) ? $event->description : "" ?></textarea>
				</td>
	        </tr>
	        <tr>
	            <td>Details Page <br />(Left Column): </td>
	            <td>
		            <div data-target="#col1" id="col1-toolbar" class="btn-toolbar">
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
				        <a id="pictureBtn2" title="" class="btn" data-original-title="Insert picture (or just drag &amp; drop)"><i class="icon-picture"></i></a>
				        <input type="file" data-edit="insertImage" data-target="#pictureBtn2" data-role="magic-overlay" style="opacity: 0; position: absolute; top: 0px; left: 0px; width: 39px; height: 30px;">
				        <a title="" onclick="viewHTML(this, '#col1')" class="btn" data-original-title="View HTML"><i class="icon-code"></i></a>
				      </div>
				      <div class="btn-group">
				        <a title="" data-edit="undo" class="btn" data-original-title="Undo"><i class="icon-undo"></i></a>
				        <a title="" data-edit="redo" class="btn" data-original-title="Redo"><i class="icon-repeat"></i></a>
				      </div>
				    </div>
					
					<div id="col1" class="editor full"><?= ($event) ? $event->col1 : "" ?></div>
					<span class="tiny formHelp" style="margin:0;">Register/Buy button, Event Date (Month XX), Event Time (X:xxam), Cost</span>
					<textarea id="col1_desc" name="col1_desc" style="display: none;"></textarea>
					<textarea id="col1_original_description" name="col1_original_description" style="display: none;"><?= ($event) ? $event->col1 : "" ?></textarea>
				</td>					
	        </tr>
	        <tr>
	            <td>Details Page <br />(Right Column): </td>
	            <td>
		            <div data-target="#col2" id="col2-toolbar" class="btn-toolbar">
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
				        <a id="pictureBtn3" title="" class="btn" data-original-title="Insert picture (or just drag &amp; drop)"><i class="icon-picture"></i></a>
				        <input type="file" data-edit="insertImage" data-target="#pictureBtn3" data-role="magic-overlay" style="opacity: 0; position: absolute; top: 0px; left: 0px; width: 39px; height: 30px;">
				        <a title="" onclick="viewHTML(this, '#col2')" class="btn" data-original-title="View HTML"><i class="icon-code"></i></a>
				      </div>
				      <div class="btn-group">
				        <a title="" data-edit="undo" class="btn" data-original-title="Undo"><i class="icon-undo"></i></a>
				        <a title="" data-edit="redo" class="btn" data-original-title="Redo"><i class="icon-repeat"></i></a>
				      </div>
				    </div>
					
					<div id="col2" class="editor full"><?= ($event) ? $event->col2 : "" ?></div>
					<span class="tiny formHelp" style="margin:0;">Event description, Learn More button</span>
					<textarea id="col2_desc" name="col2_desc" style="display: none;"></textarea>
					<textarea id="col2_original_description" name="col2_original_description" style="display: none;"><?= ($event) ? $event->col2 : "" ?></textarea>
				</td>
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
	        	<span class="tiny formHelp">RegOnline Link. (Required if "Register" or "Buy Tickets" is selected above.)</span></td>
	        </tr>
	        <tr>
	            <td>Cost/Requirements: </td>
	            <td><textarea name="custom_1" id="custom_1" class="short"><?= ($event) ? $event->custom_1 : "" ?></textarea>
	            <span class="tiny formHelp" >Specify cost, age limitations, or other special requirements.</span></td>
	        </tr>
	        <tr>
	            <td>Small Image: </td>
	            <td>
	            	<span class="file-wrapper">
					  <input type="file" name="thumbnail" id="thumbnail" />
					  <input type="hidden" name="removeicon" id="removeicon" value="false" />
					  <span class="button">Choose a <?= ($event && $event->image) ? " different " : "" ?> photo</span>
					</span>
		            <?php
		            	if($event && $event->image) {
		            		$path = $upload_dir . $event->image;
		            		echo "<img src=\"" . $path . "\" style='height:auto;width:100px;' id=\"theicon\" />";
		            		echo "<button id=\"clearicon\" title=\"Remove image\" class=\"clearicon\">&times;</button>";
							echo "<input type=\"hidden\" name=\"originalImage\" id=\"originalImage\" value=\"" . $event->image . "\" />";
		            	}
		            ?>
		            <span class="tiny formHelp" style="vertical-align:top;margin-top:10px;display:inline-block;">Menu/Default Image (550x300px)</span></td>
	            </td>
	        </tr>
	        <tr>
	            <td>Large Image: </td>
	            <td>
	            	<span class="file-wrapper">
					  <input type="file" name="bigimage" id="bigimage" />
					  <input type="hidden" name="removebigimage" id="removebigimage" value="false" />
					  <span class="button">Choose a <?= ($event && $event->big_image) ? " different " : "" ?> photo</span>
					</span>
		            <?php
		            	if($event && $event->big_image) {
		            		$path = $upload_dir . $event->big_image;
		            		echo "<img src=\"" . $path . "\" style='height:auto;width:200px;' id=\"thebigimage\" />";
		            		echo "<button id=\"clearbigimage\" title=\"Remove image\" class=\"clearicon\">&times;</button>";
							echo "<input type=\"hidden\" name=\"originalBigImage\" id=\"originalBigImage\" value=\"" . $event->big_image . "\" />";
		            	}
		            ?>
		            <span class="tiny formHelp" style="vertical-align:top;margin-top:10px;display:inline-block;">Details Page Image (550x300px)</span></td>
	            </td>
	        </tr>
	        <tr>
	            <td>Caption: </td>
	            <td><textarea name="special_note" id="special_note" class="short"><?= ($event) ? $event->special_note : "" ?></textarea>
	            <span class="tiny formHelp">This message will be displayed under the image.</span></td>
	        </tr>
	        <tr>
	            <td>Facebook URL: </td>
	            <td><input type="text" name="fb_link" id="fb_link" placeholder="http://" class="inputfield" value="<?= ($event) ? $event->fb_link : "" ?>" /></td>
	        </tr>
	        <tr>
	            <td>Default Tweet: </td>
	            <?php $tweet = ($event && $event->tweet) ? $event->tweet : $DEFAULT_TWEET; ?>
	            <td><input type="text" name="tweet" id="tweet" class="inputfield" onKeyUp="tweetCharacterCount()" value="<?= $tweet ?>" />
	            <span id="characterCount"><?= 117 - strlen($tweet) ?></span>
	            <span class="tiny formHelp">A link to this event will be automatically appended to this tweet.</span></td>
	        </tr>
	        <tr>
	            <td>Event URL: </td>
	            <td><input type="text" name="url" id="url" class="inputfield" placeholder="http://" value="<?= ($event) ? $event->url : "" ?>" />
	            <span class="tiny formHelp">MLS Landing Page URL (Overrides default event landing page)</span></td>
	        </tr>
	        <tr>
	            <td>Attachments: </td>
	            <td class="attachments">
	            	<div class="file-wrapper"><input type="file" name="attachments[]" /><span class="button">Attach File 1</span></div>
	            	<div class="file-wrapper"><input type="file" name="attachments[]" /><span class="button">Attach File 2</span></div>	
	            	<?php
	            		//List existing attachments
	            		if($event && $event->attachments) {
	            			echo "<h4>Existing attachments:</h4>";
	            			echo "<div class=\"attachment_area\">";
		            		foreach($event->attachments as $i=>$attachment) {
		            			$path = $MANAGE_ENDPOINT . $upload_dir . $attachment->filename;
			            		echo "<div class=\"attachment\" id=\"attachment_" . $attachment->id  . "\"><a href=\"" . $path . "\">" . $path . "</a>
			            			<span class=\"delAttachmentBtn clearicon\" title=\"Delete Attachment\" data-attachment=\"" . $attachment->id . "\">x</span>
			            		</div>";
		            		}
		            		echo "</div>";
	            		}
	            	?>
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
	var _hotKeys = {
	  	'shift+tab': 'outdent',
	  	'tab' : 'indent',
	    'ctrl+b meta+b': 'bold',
	    'ctrl+i meta+i': 'italic',
	    'ctrl+u meta+u': 'underline',
	    'ctrl+z meta+z': 'undo',
	    'ctrl+y meta+y meta+shift+z': 'redo'
	};
	$('#editor').wysiwyg({
	  toolbarSelector: "#editor-toolbar",
	  uploadScript: '/events/manage/php/upload_photo.php',
	  hotKeys: _hotKeys
	});
	$('#col1').wysiwyg({
	  toolbarSelector: "#col1-toolbar",
	  uploadScript: '/events/manage/php/upload_photo.php',
	  hotKeys: _hotKeys
	});
	$('#col2').wysiwyg({
	  toolbarSelector: "#col2-toolbar",
	  uploadScript: '/events/manage/php/upload_photo.php',
	  hotKeys: _hotKeys
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

	$('#clearbigimage').click(function(e) {
		e.preventDefault();
		$('#thebigimage').hide();
		$('#clearbigimage').hide();
		$('#removebigimage').val(true);
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
	
	function submitForm() {
		//Copy editor content into textarea before submitting.
		copyHtmlContent('#editor', '#description');
		copyHtmlContent('#col1', '#col1_desc');
		copyHtmlContent('#col2', '#col2_desc');
				
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
		var registration_radio = $('input:radio[name=registration_radio]:checked').length;
		
		if(!registration_radio) {
			$('#registrationError').show();
		} else {
			$('#registrationError').hide();
		}
		
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
		
		if( !areDatesValid || !name || (!start_time && !all_day) || !isEndValid || !isSundayEndValid || !registration_radio) {
			$("#validationdiv").show();
		}
		else {
			$("#validationdiv").hide();
			submitForm();
			
			/*			
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
			*/
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
					$dateString = $all_dates_formatted_string ? $all_dates_formatted_string : "parseDate('" . $event->date . "')";
					echo ", defaultDate: \"". date("m/d/y", strtotime($event->date)) . "\", addDates: [ " . $dateString . " ]";
				}
			?>
		});
	});
	
	//Safer approach to creating the date from the php string rather than just dropping it right into Date() as a dateString.
	function parseDate(d) {
		var parts = d.match(/(\d+)/g);
		return new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
	}
	
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

	function tweetCharacterCount() {
		var MAX = 117; //Since we are appending a URL, the t.co shortener currently uses 23 characters
		var $tweet = $('#tweet');
		var val = $tweet.val();
		if(val.length > MAX) {
			$tweet.val(val.substring(0, MAX));
		} else {
			$('#characterCount').html(MAX - val.length);
		}
	}
	
	<?php if($event) { ?>
	
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
		
		function postDeleteAttachment(attachment_id) {
			$.ajax({
				type: "POST",
				url: "/events/manage/php/delete_attachment.php",
				data: { id: attachment_id },
				success: function(response){
			  		if(response.trim() === "OK") {
			  			$('#attachment_' + attachment_id).fadeOut();
			  		} else {
				  		console.error("Unable to delete attachment");
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
		
		$(".delAttachmentBtn").click(function(e) {
			if(confirm("Are you sure you want to delete this attachment?")) {
				postDeleteAttachment($(this).data('attachment'));	
			}
		});
		
	<?php } ?>

	if(window.location.search.indexOf("error") != -1) {
		$("#errordiv").show();
	}
	
	if(window.location.search.indexOf("success") != -1) {
		$("#successdiv").show();
	}

</script>
