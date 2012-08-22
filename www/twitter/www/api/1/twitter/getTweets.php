<?php

/*
                RESOURCE: getTweetsBySearch
                API VERSION: 1
                URL: /api/1/tweets/getTweets.php?term=nodejs&start_date=20120101&end_date=20120201&page=1&count=10&order=asc

                PARAMETERS:

                        term    	(optional)              ex. nodejs	 //search terms tweets where archived by from a twitter search 
                        start_date 	(optional)		ex. 20120101  	 // returns tweets that are dated before this date
  			end_date 	(optional)		ex. 20120101  	 // return tweets that are dated after this date
 			page 		(optional) 		ex. 1            // page number default is 1
			count 		(optional)		ex. 10       	 // number of tweets to return default 10
			order 		(optional)		ex. asc or desc  // order tweets by date default asc which would mean older tweets are first

                EXAMPLE RESPONSE:

			{ 
				created_at: 'Fri, 17 Aug 2012 15:30:43 +0000',
       				id: 236485212790464500,
       				text: 'This election has a inward vs. outward-looking dynamic to it: RT @lemondefr: Le business international vote pour Obama http://t.co/PmqhScsM'
			}
                NOTE: This API is for tweets archived by search terms and date.  If nothing is passed in the tweets are returned in order of tweet id. 

        */



	function getTweets($term = null, $start_date = null, $end_date = null, $page = null, $count = null, $order = null) {

		require_once($_SERVER['DOCUMENT_ROOT'] . "/api/1/twitter/db.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/api/1/twitter/wp-db.php");

		$db = new wpdb(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_NAME, SITE_DB_HOST);
		
		$fields = "`text`, `id`, `created_at`";

		$select_sql = "SELECT " . $fields . " FROM `json_tweets` ";
		$where = false;

		if ($page && $page == 1) $page = 0;
		$offset = ($page && $count) ? $page * $count : 0;
 		if(!$count) $count = 10;
 		if(!$order) $order = 'asc';
 		else if (!($order == 'asc' || $order == 'desc')) $order = 'asc'; 

		if ($term) {
 		  $select_sql = $select_sql . "WHERE search_query = '" . $term . "'";
                  $where = true;
		}
   		if ($start_date && $end_date) {
		  if ($where) {
			$select_sql = $select_sql . " AND created_at_date BETWEEN " . $start_date . " AND " . $end_date;
		  } else {
  			$select_sql = $select_sql . "WHERE created_at_date BETWEEN " . $start_date . " AND " . $end_date;
			$where = true;	
		  }
 		} else if ($start_date) {
		  if ($where) {
			$select_sql = $select_sql . " AND created_at_date >= " . $start_date;
		  } else {
  			$select_sql = $select_sql . "WHERE created_at_date >= " . $start_date;
			$where = true;	
		  }
		} else if ($end_date) {
		  if ($where) {
 		  	$select_sql = $select_sql . " AND created_at_date <= " . $end_date;
		  } else {
			$select_sql = $select_sql . "WHERE created_at_date <= " . $end_date;
                        $where = true;
		  }
		}
 		$select_sql = $select_sql . " ORDER BY `created_at_date` " . $order . " LIMIT " . $offset . " , " . $count;
		//echo $select_sql . "<br/>";
		$tweets = $db->get_results($db->prepare($select_sql));

		
		
		return $tweets;
	}


	$term = isset($_GET["term"]) ? $_GET["term"] : null;
	$count = isset($_GET["count"]) ? $_GET["count"] : null;
	$page = isset($_GET["page"]) ? $_GET["page"] : null;
	$start_date = isset($_GET["start_date"]) ? $_GET["start_date"] : null;
	$end_date = isset($_GET["end_date"]) ? $_GET["end_date"] : null;
	$order = isset($_GET["order"]) ? $_GET["order"] : null;

	$tweets = getTweets($term, $start_date, $end_date, $page, $count, $order);

        echo json_encode($tweets);
?>
