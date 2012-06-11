<?php
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/config.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/bin/wp-db.php");
	
	$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
	
	$page = isset($_GET["page"]) ? $_GET["page"] : null;
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<title>Events Management</title>
	<meta charset="utf-8" /> 
	<link rel="stylesheet" href="/css/styles.css" type="text/css" />
	<link rel="stylesheet" href="/css/ui-lightness/jquery-ui-1.8.21.custom.css" type="text/css" />
	<script src="/js/jquery-1.7.2.min.js"></script>
	<script src="/js/jquery-ui-1.8.21.custom.min.js"></script>	

	<!--[if IE]>
	  <script type="text/javascript" src="/js/html5.js"></script>
	<![endif]-->
</head>

<body>

	<div class="wrapper">
	
		<header>
			<nav>
				<ul class="topnav"> 
					<li><a href="/">Home</a></li>				
					<li><a href="/events/add">Add Weekly Event</a></li>
					<li><a href="/events/addspecial">Add Special Event</a></li>
					<li><a href="/events/edit/1">Edit Event</a></li>
					<li><a href="/events/manage">Manage Events</a></li>
					<li><a href="/settings">Settings</a></li>			

		        </ul>
			</nav>
		</header>

		<section>
			<?php
				//LOADER			
				if($page) {
					include("php/$page.php");
				} else {
					include("php/main.php");
				}
			?>

		</section>
				
	</div>
 	
</body>
</html>



