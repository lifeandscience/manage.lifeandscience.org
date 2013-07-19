<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	date_default_timezone_set('EST5EDT');
	ini_set('memory_limit', '3M');
	
	$dir = "../../uploads";
	$all_day = ($_POST['all_day'] == "on") ? 1 : 0;	
	
	//Validation - Check to make sure our required paramaters are not empty
	if($_POST['name'] == "" || $_POST['date'] == "" || ($_POST['start_time'] == "" && !$all_day)) {
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
		
		//See if we have multiple events
		$dates = explode(",", $_POST["date"]);
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
									"custom_1" => $_POST['custom_1'],																
									"added" => date("Y-m-d H:i:s"),
									"group_id" => $group_id
									);
	
				if($_POST['start_time']) {
					$insert_params["start_time"] = date("H:i", strtotime($_POST['start_time']));
				}
				
				if($_POST['end_time']) {
					$insert_params["end_time"] = date("H:i", strtotime($_POST['end_time']));
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
									"date" => date("Y-m-d",strtotime($_POST['date'])),
									"fb_link" => $_POST['fb_link'],
									"description" => $_POST['description'],
									"special_note" => $_POST['special_note'],
									"members_only" => $members_only,
									"all_day" => $all_day,
									"cost_members" => $_POST['cost_members'],
									"cost_public" => $_POST['cost_public'],
									"custom_1" => $_POST['custom_1'] );
			//Only update the filename if the user uploaded a new one.
			if($filename != "") {
				$params["image"] = $filename;
			} else if($_POST["removeicon"] === "true") {
				$params["image"] = "";
			}
			
			if($_POST['start_time']) {
				$params["start_time"] = date("H:i", strtotime($_POST['start_time']));
			} else {
				//WPDB does not handle NULL well, so just run a separate query to clear the time. :/
				$db->query($db->prepare("UPDATE `events_special` SET `start_time` = NULL WHERE `id` = %d", $event_id));
			}
			
			if($_POST['end_time']) {
				$params["end_time"] = date("H:i", strtotime($_POST['end_time']));
			} else {
				//WPDB does not handle NULL well, so just run a separate query to clear the time. :/
				$db->query($db->prepare("UPDATE `events_special` SET `end_time` = NULL WHERE `id` = %d", $event_id));
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

