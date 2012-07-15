<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	//If we have a event_id, we are EDITING and not creating a new event.
	$event_id = $_POST["event_id"];

	date_default_timezone_set('EST5EDT');
	ini_set('memory_limit', '3M');
	
	$dir = "../uploads";
	
	$all_day = ($_POST['all_day'] == "on") ? 1 : 0;	
	
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
								"day_of_week" => $_POST['day_of_week'],
								"start_time" => $_POST['start_time'],
								"end_time" => $_POST['end_time'],
								"all_day" => $all_day,
								"added" => date("Y-m-d H:i:s"),
								"icon" => $filename
								);

		$success = $db->insert('events_weekly', $insert_params);	
	} 
	//EDITING EVENT - UPDATE
	else {
	
		$params = array("name" => $_POST['name'],
						"description" => $_POST['description'],
						"day_of_week" => $_POST['day_of_week'],						
						"start_time" => $_POST['start_time'],
						"end_time" => $_POST['end_time'],
						"all_day" => $all_day );
		//Only update the filename if the user uploaded a new one.
		if($filename != "") {
			$params["icon"] = $filename;
		}
		$updated = $db->update('events_weekly', $params, array("id" => $event_id));
		if($updated !== false) $success = 1; //since a no-op update should not return an error. query actually returns # of rows updated
	}
	
	if($success === 1) {	
			
		if($wasAdding == 1) {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/add/?success=true';</script>";
		} else {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/edit/?event_id={$event_id}&success=true';</script>";
		}
		
	} else {
		//An error occurred while adding the row.
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/add/?error';</script>";
		
	}	
?>








