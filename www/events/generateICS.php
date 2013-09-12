<?php

// This file takes an event ID as a query param and outputs an .ics file for the event.
// Note: This currently only works with "Special" events.

require_once($_SERVER['DOCUMENT_ROOT'] . "/events/api/1/events/getEventById.php");
date_default_timezone_set('EST5EDT');
$timezone = "America/New_York";

$eventId = isset($_GET["eventId"]) ? $_GET["eventId"] : null;
$event = getEventById($eventId);

if($event) {
	$time = getTimeObject($event);
	$summary = $event->name;
	$fDate = date("Ymd", strtotime($event->date));
	$datestart = strtotime($fDate . $time["start"]);
	
	if($event->end_date) {
		$fEndDate = date("Ymd", strtotime($event->end_date . "+ 1 day")); // + 1 day is because the DTEND value is non-inclusive when no time is specified. We want inclusive.
		$dateend = strtotime($fEndDate . $time["end"]);
	}
	
	$location = "Museum of Life + Science";
	$uri = "http://lifeandscience.org/";
	$description = $event->description;
	$filename = "ncmls-event" . $event->id . ".ics";
}

function getTimeObject($event) {
	$time = array();
	if($event->all_day === "1") {
		$time["start"] = "";
		$time["end"] = "";
	} else {
		$time["start"] = "T" . date("His", strtotime($event->start_time));
		if($event->end_time) {
			$time["end"] = "T" . date("His", strtotime($event->end_time));

		}
	}	
	return $time;
}


// Variables used in this script:
//   $summary     - text title of the event
//   $datestart   - the starting date (in seconds since unix epoch)
//   $dateend     - the ending date (in seconds since unix epoch)
//   $location    - the event's location
//   $uri         - the URL of the event (add http://)
//   $description - text description of the event
//   $filename    - the name of this file for saving (e.g. my-event-name.ics)
//
// Notes:
//  - the UID should be unique to the event, so in this case I'm just using
//    uniqid to create a uid, but you could do whatever you'd like.
//
//  - iCal requires a date format of "yyyymmddThhiissZ". The "T" and "Z"
//    characters are not placeholders, just plain ol' characters. The "T"
//    character acts as a delimeter between the date (yyyymmdd) and the time
//    (hhiiss), and the "Z" states that the date is in UTC time. Note that if
//    you don't want to use UTC time, you must prepend your date-time values
//    with a TZID property. See RFC 5545 section 3.3.5
//
//  - The Content-Disposition: attachment; header tells the browser to save/open
//    the file. The filename param sets the name of the file, so you could set
//    it as "my-event-name.ics" or something similar.
//
//  - Read up on RFC 5545, the iCalendar specification. There is a lot of helpful
//    info in there, such as formatting rules. There are also many more options
//    to set, including alarms, invitees, busy status, etc.
//
//      https://www.ietf.org/rfc/rfc5545.txt

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// 2. Define helper functions

// Converts a unix timestamp to an ics-friendly format
// NOTE: "Z" means that this timestamp is a UTC timestamp. If you need
// to set a locale, remove the "\Z" and modify DTEND, DTSTAMP and DTSTART
// with TZID properties (see RFC 5545 section 3.3.5 for info)
//

function dateToCal($timestamp) {
  return date('Ymd\THis', $timestamp);
}

// Escapes a string of characters
function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "BEGIN:VEVENT\r\n";
if($event->all_day === "1") {
	echo "DTSTART;VALUE=DATE:" . date("Ymd", $datestart) . "\r\n";
} else {
	echo "DTSTART;TZID={$timezone}:" . dateToCal($datestart) . "\r\n";	
}

if($event->all_day === "1" && $event->end_date) {
	echo "DTEND;VALUE=DATE:" . $fEndDate . "\r\n";
} else if(!empty($time["end"])) {
	echo "DTEND;TZID={$timezone}:" . dateToCal(strtotime($fDate . $time["end"])) . "\r\n";	
}
//If this event is a date range and it's not an all day event, we should create reocurring events rather than 1 huge one.
if($event->start_time && $event->end_date) {
	echo "RRULE:FREQ=DAILY;UNTIL=" . dateToCal($dateend) . "\r\n";
}

echo "UID:" . uniqid() . "\r\n";
echo "DTSTAMP:" . dateToCal(time()) . "\r\n";
echo "LOCATION:" . escapeString($location) . "\r\n";
echo "DESCRIPTION:" . escapeString($description) . "\r\n";
echo "URL;VALUE=URI:" . escapeString($uri) . "\r\n";
echo "SUMMARY:" . escapeString($summary) . "\r\n";
echo "END:VEVENT\r\n";
echo "END:VCALENDAR\r\n";
?>