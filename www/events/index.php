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
	<link rel="stylesheet" href="/events/css/styles.css" type="text/css" />
	<link rel="stylesheet" href="/events/css/pepper-grinder/jquery-ui-1.8.21.custom.css" type="text/css" />
	<script src="/events/js/global.js"></script>
	<script src="/events/js/jquery-1.7.2.min.js"></script>
	<script src="/events/js/jquery-ui-1.8.21.custom.min.js"></script>
	<script src="/events/js/jquery-ui.multidatespicker.js"></script>	

	<!--[if IE]>
	  <script type="text/javascript" src="/events/js/html5.js"></script>
	<![endif]-->
</head>

<body>

	<div class="wrapper">
	
		<header>
			<h2 class="site_title"><a href="/events">NCMLS Event Management</a></h2>
			<nav>
				<ul class="topnav"> 
					<li><a href="/events"<?php if(!$page) echo " class='selected' "; ?>>Home</a></li>				
					<li><a href="/events/weekly"<?php if(strpos($page,"weekly") !== FALSE) echo " class='selected' "; ?>>Weekly Events</a></li>
					<li><a href="/events/special"<?php if(strpos($page,"special") !== FALSE) echo " class='selected' "; ?>>Special Events</a></li>
					<li><a href="/events/settings"<?php if($page == "settings") echo " class='selected' "; ?>>Settings</a></li>			

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



