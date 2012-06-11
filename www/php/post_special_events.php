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
	
	//Reformat the date to fit the MySQL date structure
	$date = date("Y-m-d",strtotime($_POST['date']));
	
	$members_only = ($_POST['members_only'] == "on") ? 1 : 0;	

	//NEW EVENT - INSERT
	if($event_id == "") {
		$wasAdding = 1;
		$insert_params = array("name" => $_POST['name'],
								"date" => $date,
								"time" => $_POST['time'],
								"image" => $filename,
								"fb_link" => $_POST['fb_link'],
								"description" => $_POST['description'],
								"special_note" => $_POST['special_note'],
								"members_only" => $members_only,
								"cost_members" => $_POST['cost_members'],
								"cost_public" => $_POST['cost_public'],
								"custom_1" => $_POST['custom_1'],																
								"added" => date("Y-m-d H:i:s")
								);

		$success = $db->insert('events_special', $insert_params);	
	} 
	//EDITING EVENT - UPDATE
	else {
	
		$params = array("name" => $_POST['name'],
								"date" => $date,
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

