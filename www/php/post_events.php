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

	//NEW VIDEO - INSERT
	if($event_id == "") {
		
		$insert_params = array("name" => $_POST['name'],
								"description" => $_POST['description'],
								"day_of_week" => $_POST['day_of_week'],
								"time" => $_POST['time'],
								"added" => date("Y-m-d H:i:s"),
								"icon" => $filename
								);

		$success = $db->insert('events_weekly', $insert_params);	
	} 
	//EDITING VIDEO - UPDATE
	else {
	
		$params = array("name" => $_POST['name'],
						"description" => $_POST['description'],
						"day_of_week" => $_POST['day_of_week'],						
						"time" => $_POST['time'] );
		//Only update the filename if the user uploaded a new one.
		if($filename != "") {
			$params["icon"] = $filename;
		}
		$updated = $db->update('events_weekly', $params, array("id" => $event_id));
		if($updated !== false) $success = 1; //since a no-op update should not return an error. query actually returns # of rows updated
	}
	
	if($success === 1 && $event_id == "") {	
		$event_id = $db->insert_id;
		$wasAdding = 1;
		
		if($cats) {
			for($x = 0; $x < count($cats); $x++) {
				$cat_id = $cats[$x];
				$r = $db->get_row($db->prepare("SELECT MAX(`sort_id`) as `maxval` FROM `video_category` WHERE `cat_id` = %d", $cat_id));
				$sort_id = $r->maxval + 1;
	 			$db->insert('video_category', array("event_id" => $event_id, "cat_id" => $cat_id, "sort_id" => $sort_id ));			
			}
		}

	}
	
	//Add the categories into the video_category table	
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








