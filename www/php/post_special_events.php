<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	//If we have a event_id, we are EDITING and not creating a new event.
	$event_id = $_POST["event_id"];

	date_default_timezone_set('EST5EDT');
	ini_set('memory_limit', '3M');
	
	$dir = "../uploads";
	
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
								"time" => $_POST['time'],
								"image" => $filename,
								"fb_link" => $_POST['fb_link'],
								"description" => $_POST['description'],
								"special_note" => $_POST['special_note'],
								"members_only" => $members_only,
								"cost_members" => $_POST['cost_members'],
								"cost_public" => $_POST['cost_public'],
								"custom_1" => $_POST['custom_1'],																
								"added" => date("Y-m-d H:i:s"),
								"group_id" => $group_id
								);

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
		
//		error_log($rows_inserted . " of " . $num_dates . " rows inserted into the database");
		if($rows_inserted == $num_dates) $success = 1; //only claim success if we inserted all of the rows
		
		
	} 
	//EDITING EVENT - UPDATE
	else {
	
		$params = array("name" => $_POST['name'],
								"date" => date("Y-m-d",strtotime($_POST['date'])),
								"time" => $_POST['time'],
								"fb_link" => $_POST['fb_link'],
								"description" => $_POST['description'],
								"special_note" => $_POST['special_note'],
								"members_only" => $members_only,
								"cost_members" => $_POST['cost_members'],
								"cost_public" => $_POST['cost_public'],
								"custom_1" => $_POST['custom_1'] );
		//Only update the filename if the user uploaded a new one.
		if($filename != "") {
			$params["icon"] = $filename;
		}
		$updated = $db->update('events_special', $params, array("id" => $event_id));
		if($updated !== false) $success = 1; //since a no-op update should not return an error. query actually returns # of rows updated
	}
	
	if($success === 1) {	
			
		if($wasAdding == 1) {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/addspecial/?success=true';</script>";
		} else {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/edit/?event_id={$event_id}&success=true';</script>";
		}
		
	} else {
		//An error occurred while adding the row.
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/addspecial/?error';</script>";
		
	}	
?>

