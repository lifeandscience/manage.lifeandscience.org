<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	date_default_timezone_set('EST5EDT');
	ini_set('memory_limit', '3M');
	
	$dir = "../../uploads";
	$all_day = ($_POST['all_day'] == "on") ? 1 : 0;
	$days = $_POST["days"];
	
	//Validation - Check to make sure our required paramaters are not empty
	if($_POST['name'] == "" || empty($days) || ($_POST['start_time'] == "" && !$all_day)) {
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
	
		//NEW EVENT - INSERT
		if($event_id == "") {
			$wasAdding = 1;
			$insert_params = array("name" => $_POST['name'],
									"description" => $_POST['description'],
									"start_time" => $_POST['start_time'],
									"end_time" => $_POST['end_time'],
									"all_day" => $all_day,
									"added" => date("Y-m-d H:i:s"),
									"icon" => $filename
									);
			
			//Add all of the selected days
			foreach($days as $i => $day) {
				$insert_params[$day] = 1;
			}
	
			$success = $db->insert('events_weekly', $insert_params);	
			$new_event_id = $db->insert_id;
		} 
		//EDITING EVENT - UPDATE
		else {
		
			$params = array("name" => $_POST['name'],
							"description" => $_POST['description'],
							"start_time" => $_POST['start_time'],
							"end_time" => $_POST['end_time'],
							"all_day" => $all_day );
			//Only update the filename if the user uploaded a new one.
			if($filename != "") {
				$params["icon"] = $filename;
			} else if($_POST["removeicon"] === "true") {
				$params["icon"] = "";
			}
			
			//Update selected days, but first make sure to clear previously selected days
			$col_names = array("mon","tue","wed","thu","fri","sat","sun");
			foreach($col_names as $i => $val) {
				$params[$val] = 0;
			}
			//Now, add all of the new/unchanged selected days
			foreach($days as $i => $day) {
				$params[$day] = 1;
			}
			
			$updated = $db->update('events_weekly', $params, array("id" => $event_id));
			if($updated !== false) $success = 1; //since a no-op update should not return an error. query actually returns # of rows updated
		}
		
	} //end validation block
	
	if($success === 1) {	
			
		if($wasAdding == 1) {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/weekly/add/?success=true&eid={$new_event_id}';</script>";
		} else {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/weekly/edit/{$event_id}/?success=true';</script>";
		}
		
	} else {
		//An error occurred while adding the row. Could be validation related, but the client-side validation should have caught it.
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/weekly/add/?error';</script>";
	}	
?>

