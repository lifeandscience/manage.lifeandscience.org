<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getNote.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	$formattedDate = date("Y-m-d",strtotime($_POST["date-input"]));

	$mode = $_POST["mode"];

	if($mode === "check") {
		//See if an event already exists
		echo getNote($formattedDate);
		
	} else {
	
		$isEditing = $_POST["isEditing"];
		
		if($isEditing) {
			$success = $db->update('special_notes', array("notes" => $_POST["notes"]), array("date" => $formattedDate ));
		} else {
			$success = $db->insert('special_notes', array("date" => $formattedDate, "notes" => $_POST["notes"]));	
		}
		
		if($success) {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/notes?success';</script>";	
		} else {
			echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/notes?error';</script>";
		}	

	}

?>