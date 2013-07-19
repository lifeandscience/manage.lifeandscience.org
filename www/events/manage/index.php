<?php
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/events/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	$page = isset($_GET["page"]) ? $_GET["page"] : null;
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>Events Management</title>
	<meta charset="utf-8" /> 
	<link rel="stylesheet" href="/events/manage/css/bootstrap-noicons.min.css" type="text/css" />
	<link rel="stylesheet" href="/events/manage/css/bootstrap-timepicker.min.css" type="text/css" />
	<link rel="stylesheet" href="/events/manage/css/cupertino/jquery-ui-1.10.3.custom.min.css" type="text/css" />
	<link rel="stylesheet" href="/events/manage/css/styles.css" type="text/css" />
	<link href="http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	
	<script src="/events/manage/js/jquery-2.0.2.min.js"></script>
	<script src="/events/manage/js/jquery-ui-1.10.3.custom.min.js"></script>
	<script src="/events/manage/js/jquery.hotkeys.js"></script>
	<script src="/events/manage/js/bootstrap-noicons.min.js"></script>
	<script src="/events/manage/js/bootstrap-wysiwyg.js"></script>
	<script src="/events/manage/js/bootstrap-timepicker.min.js"></script>
	<script src="/events/manage/js/jquery-ui.multidatespicker.js"></script>
	<script src="/events/manage/js/global.js"></script>

	<!--[if IE]>
	  <script type="text/javascript" src="/events/manage/js/html5.js"></script>
	<![endif]-->
</head>

<body>

	<div class="wrapper">
	
		<header>
			<h2 class="site_title"><a href="/events/manage">NCMLS Event Management</a></h2>
			<nav>
				<ul class="topnav"> 
					<li><a href="/events/manage"<?php if(!$page) echo " class='selected' "; ?>>Home</a></li>				
					<li><a href="/events/weekly"<?php if(strpos($page,"weekly") !== FALSE) echo " class='selected' "; ?>>Weekly Events</a></li>
					<li><a href="/events/special"<?php if(strpos($page,"special") !== FALSE) echo " class='selected' "; ?>>Special Events</a></li>
		        </ul>
			</nav>
		</header>

		<section>
			<?php
				//LOADER			
				if($page) {
					include("php/$page.php");
				} else {
					include("php/home.php");
				}
			?>

		</section>
				
	</div>
 	
</body>
</html>



