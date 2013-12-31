<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	$formattedDate = date("Y-m-d",strtotime($_POST["date-input"]));
	$isEditing = $_POST["isEditing"];
	
	if($isEditing) {
		$success = $db->update('special_notes', array("notes" => $_POST["notes"], "dailyEventsDisabled" => $_POST["dailyEventsDisabled"] ), array("date" => $formattedDate ));
	} else {
		$success = $db->insert('special_notes', array("date" => $formattedDate, "notes" => $_POST["notes"], "dailyEventsDisabled" => $_POST["dailyEventsDisabled"]));	
	}
	
	if($success) {
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/notes?success';</script>";	
	} else {
		echo "<SCRIPT LANGUAGE='JavaScript'>window.location='/events/notes?error';</script>";
	}	

?>