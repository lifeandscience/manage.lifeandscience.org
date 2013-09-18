<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	date_default_timezone_set('EST5EDT');
	ini_set('memory_limit', '3M');
	
	$dir = "../../uploads";
	$all_day = ($_POST['all_day'] == "on") ? 1 : 0;
	$adult_only = ($_POST['adult_only'] == "on") ? 1 : 0;
	
	$timeFields = array("start_time","end_time","sun_start_time","sun_end_time");
	
	$dateType = $_POST['dates_radio'];
	$isDateValid = false;
	if($dateType == "multidate" && $_POST['date'] != "") {
		$isDateValid = true;
	}
	else if($dateType == "range" && $_POST['daterange_start'] != "" && $_POST['daterange_end'] != "" ) {
		$isDateValid = true;
		$isDateRange = true;
	}
	
	//Validation - Check to make sure our required paramaters are not empty
	if($_POST['name'] == "" || !$isDateValid || ($_POST['start_time'] == "" && !$all_day)) {
		error_log("An error occurred saving the event because all required fields were not filled out. Try again.");
		$success = false;
	}
	else {
	
		//If we have a event_id, we are EDITING and not creating a new event.
		$event_id = $_POST["event_id"];
		
		$filename = "";
		if( $_FILES['thumbnail']['name'] ) {
			
			//User uploaded a custom thumbnail, get the filename for this upload
			$rand_num = rand(1, 9999);
			
			$filename = $rand_num . $_FILES['thumbnail']['name'];
			$filename = str_replace(" ","_",$filename);
			
			//now upload it!
			$filetmp = $_FILES['thumbnail']['tmp_name'];
			move_uploaded_file($filetmp, $dir . "/" . $filename);	
		}
		
		if($dateType == "multidate") {
			//See if we have multiple events
			$dates = explode(",", $_POST["date"]);
		} else {
			//Date range. Just put the start_date into the dates array as the only entry. (Less code changes required further down.)
			$dates = array($_POST['daterange_start']);
		}
		$num_dates = count($dates);
		if($num_dates == 0) {
			//TODO: Server-side validation here, please.
			error_log("no date selected");
		}
	
		$group_id = 0;	
		$members_only = ($_POST['members_only'] == "on") ? 1 : 0;
			
	
		//NEW EVENT - INSERT
		if($event_id == "") {
			$wasAdding = 1;
			$rows_inserted = 0;
			
			for($x = 0; $x < $num_dates; $x++) {
				$insert_params = array("name" => $_POST['name'],
									"date" => date("Y-m-d",strtotime($dates[$x])),
									"image" => $filename,
									"fb_link" => $_POST['fb_link'],
									"description" => $_POST['description'],
									"special_note" => $_POST['special_note'],
									"members_only" => $members_only,
									"all_day" => $all_day,
									"cost_members" => $_POST['cost_members'],
									"cost_public" => $_POST['cost_public'],
									"display_date" => $_POST['display_date'],
									"custom_1" => $_POST['custom_1'],
									"added" => date("Y-m-d H:i:s"),
									"adult_only" => $adult_only,
									"group_id" => $group_id
									);
				
				foreach($timeFields as $field) {
					if($_POST[$field]) {
						$insert_params[$field] = date("H:i", strtotime($_POST[$field]));
					}
				}
				
				if($isDateRange) {
					$insert_params["end_date"] = date("Y-m-d",strtotime($_POST['daterange_end']));
				}
			
				$success = $db->insert('events_special', $insert_params);
				if($success === 1) {
					$rows_inserted++;	
				} else {
					error_log("Unable to insert event '". $_POST["name"] . "' on " . $dates[$x]);
				}
				
				//if this is the first date we are saving, and there are multiple dates.. use the insert_id of the first as the group_id for all events.
				if($x == 0 && $num_dates > 1) {
					$group_id = $db->insert_id;
					//now, update that first row we just inserted with the right group_id
					$db->update('events_special', array("group_id" => $group_id), array("id" => $db->insert_id));
				}
			}
			
			if($rows_inserted == $num_dates) $success = 1; //only claim success if we inserted all of the rows
			
		} 
		//EDITING EVENT - UPDATE
		else {
		
			//Edit only this event, or all future events?
			$edit_all = $_POST["edit_all"];
			$group_id = $_POST["group_id"];

			$params = array("name" => $_POST['name'],
									"date" => date("Y-m-d",strtotime($dates[0])), //just use the first date for this edit. We'll address the other dates later.
									"fb_link" => $_POST['fb_link'],
									"description" => $_POST['description'],
									"special_note" => $_POST['special_note'],
									"members_only" => $members_only,
									"all_day" => $all_day,
									"cost_members" => $_POST['cost_members'],
									"cost_public" => $_POST['cost_public'],
									"display_date" => $_POST['display_date'],
									"adult_only" => $adult_only,
									"custom_1" => $_POST['custom_1'] );
			//Only update the filename if the user uploaded a new one.
			if($filename != "") {
				$params["image"] = $filename;
			} else if($_POST["removeicon"] === "true") {
				$params["image"] = "";
			}
			
			if($isDateRange) {
				$params["end_date"] = date("Y-m-d",strtotime($_POST['daterange_end']));
			} else {
				//NULL the end_date event in case it was previously set.
				
				//WPDB does not handle NULL well, so just run a separate query to clear the end date. :/
				$db->query($db->prepare("UPDATE `events_special` SET `end_date` = NULL WHERE `id` = %d", $event_id));
			}
			
			foreach($timeFields as $field) {				
				if($_POST[$field]) {
					$params[$field] = date("H:i", strtotime($_POST[$field]));
				} else {
					//WPDB does not handle NULL well, so just run a separate query to clear the time. :/
					$db->query($db->prepare("UPDATE `events_special` SET `{$field}` = NULL WHERE `id` = %d", $event_id));
				}
			}
			
			//Only update this event (not the entire group). so, we should orphan this event from the group.
			if($group_id > 0 && $edit_all === "false") {
				$params["group_id"] = 0;
			}
			$updated = $db->update('events_special', $params, array("id" => $event_id));
			if($updated !== false) $success = 1; //since a no-op update should not return an error. query actually returns # of rows updated		
			
			// If edit 'All future events' was selected. Update all properties *except the date* in all linked events.
			if($group_id > 0 && $edit_all === "true") {
				//Don't change all of the events in the group to the same date!
				unset($params["date"]);
				
				$updated2 = $db->update('events_special', $params, array("group_id" => $group_id));
				if($updated2 !== false && $success == 1) $success = 1;
			}
			
			//If multiple dates were selected during the edit, we need to create these additional events (rows)
			if($dateType == "multidate" && $num_dates > 1) {
				$rows_inserted = 0;
				for($x = 1; $x < $num_dates; $x++) { //start at index 1 since we already used the 1st date when saving the initial edit.
					$params["date"] = date("Y-m-d",strtotime($dates[$x]));
					$params["added"] = date("Y-m-d H:i:s");
					if(!empty($filename)) {
						$params["image"] = $filename;
					} else {
						$params["image"] = $_POST["originalImage"];
					}
					//Check to see if this event has a group_id intact, if so, use it on these new events as well. If not, we should still set a group_id for the new rows to the id of the initial edit event.
					if($group_id === "0") {
						$params["group_id"] = $event_id;
					} else {
						$params["group_id"] = $group_id;
					}
					//Create the new events and add them to the group
					$success2 = $db->insert('events_special', $params);
					if($success2 === 1) {
						$rows_inserted++;	
					} else {
						error_log("Unable to insert event '". $_POST["name"] . "' on " . $dates[$x]);
					}	
				}
				//If the original event was not a member of the group, but it is now (the group leader, in fact). We need to update its groupId, too
				if($group_id === "0" && $rows_inserted > 0) {
					$db->update('events_special', array("group_id" => $event_id), array("id" => $event_id));
				}
				
				if($rows_inserted == ($num_dates - 1) ) {
					$success = 1; //only claim success if we inserted all of the rows
				} else {
					$success = 0;
				}
			}
		}

	} //end validation block
	
	if($success === 1) {	
			
		if($wasAdding == 1) {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/special/add/?success=true';</script>";
		} else {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/special/edit/{$event_id}/?success=true';</script>";
		}
		
	} else {
		//An error occurred while adding the row. Could be validation related, but the client-side validation should have caught it.
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/special/add/?error';</script>";
	}	
?>

