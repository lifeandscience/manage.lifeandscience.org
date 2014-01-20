<?php

	ini_set('memory_limit', '25M');
	$dir = "../../uploads/";
	$absolute_path = "http://manage.lifeandscience.org/events/uploads/";
	
	if(empty($_POST['filename'])) {
		error_log("An error occurred trying to save this photo. Filename is missing.");
		$success = FALSE;
	}
	else {
		$filename = rand(1, 9999) . $_POST['filename'];
		$filename = str_replace(" ", "_", $filename);
		$success = move_uploaded_file($_FILES["image"]['tmp_name'], $dir . "/" . $filename);
	}
	
	if($success === TRUE) {
		$output = array("url" => $absolute_path . $filename);
	} else {
		$output = array("error" => "Unable to upload photo.");
	}
	echo json_encode($output);
		
?>
