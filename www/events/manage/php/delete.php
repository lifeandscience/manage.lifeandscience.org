<?php

	//This script handles event archiving and deletion (for both type of events)

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	$event_id = $_POST["event_id"];
	$group_id = $_POST["group_id"];
	$event_type = $_POST["event_type"];
	$full_delete = $_POST["delete_permanent"] === "1" ? true : false;
	
	function checkInput($event_id, $event_type) {
		//ERROR: No Event ID specified
		if(!$event_id) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "You must specify the event ID to be deleted.";
			$error->details = "You must include the `event_id` parameter.";
			echo json_encode($error);
			return false;
		}
		//ERROR: No recognized Event Type specified
		$event_type = strtolower($event_type);
		if($event_type != "weekly" && $event_type != "special") {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "You must specify the type of the event to be deleted.";
			$error->details = "You must include the `event_type` parameter with either 'weekly' or 'special'.";
			echo json_encode($error);
			return false;
		}
		return true;
	}
	
	$isValid = checkInput($event_id, $event_type);
	
	if($isValid) {
	
		//Archive a weekly event
		if($event_type == "weekly") {
			if($full_delete) {
				$outcome = $db->query($db->prepare("DELETE FROM `events_weekly` WHERE `id` = %d", $event_id));
			} else {
				$outcome = $db->update('events_weekly', array("active" => 0), array('id' => $event_id ));	
			}
			echo $outcome ? "OK" : "ERROR";
		}
		//Archive a special event
		if($event_type == "special") {
			//If we have a group_id, the user wants to archive all of the linked events
			if($group_id) {
				if($full_delete) {
					$outcome = $db->query($db->prepare("DELETE FROM `events_special` WHERE `group_id` = %d", $group_id));	
				} else {
					$outcome = $db->update('events_special', array("active" => 0), array('group_id' => $group_id ));
				}
			} else {
				if($full_delete) {
					$outcome = $db->query($db->prepare("DELETE FROM `events_special` WHERE `id` = %d", $event_id));	
				} else {
					$outcome = $db->update('events_special', array("active" => 0), array('id' => $event_id ));	
				}
				
			}
			echo $outcome ? "OK" : "ERROR";
		}

	}
	
?>