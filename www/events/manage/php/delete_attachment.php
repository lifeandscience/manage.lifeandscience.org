<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/api.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);

	$attachment_id = $_POST["id"];
	$dir = "../../uploads";

	
	function checkInput($attachment_id) {
		//ERROR: No Event ID specified
		if(!$attachment_id) {
			$error = new ErrorObject();
			$error->code = "ERROR";
			$error->message = "You must specify the attachment ID to be deleted.";
			$error->details = "You must include the `id` parameter.";
			echo json_encode($error);
			return false;
		}
		return true;
	}
	
	$isValid = checkInput($attachment_id);
	
	if($isValid) {

		$attachment = $db->get_row($db->prepare("SELECT * FROM `attachments` WHERE `id` = %d", $attachment_id));
		unlink($dir . "/" . $attachment->filename);
		
		$outcome = $db->query($db->prepare("DELETE FROM `attachments` WHERE `id` = %d", $attachment_id));
		echo $outcome ? "OK" : "ERROR";

	}
	
?>