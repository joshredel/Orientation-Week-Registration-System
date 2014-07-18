<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$staffService = new services\StaffService();
$staff = $staffService->getStaffByRegistrationPassword($_REQUEST['passkey']);
$staff->event->load();
$currentEvent = $staff->event;
$timezone = 8;//$_REQUEST['timezone']; // offset in hours

// the iCal date format. Note the Z on the end indicates a UTC timestamp.
define('DATE_ICAL', 'Ymd\THis\Z');

// loop through all events
$eventToDisplay = array();
// display calendar events for events that have a schedule of calendar events
if(!$currentEvent->hasSelectableEvents) {
	// see if we should display the name of the master event
	$masterEventDisplay = (count($currentEvent->calendarEvents) > 1 ? $currentEvent->eventName : null);
	
	foreach($currentEvent->calendarEvents as $calendarEvent) {
		// determine the colour we want to use
		if($calendarEvent->ofAgeMarker == 0) {
			// it's for all ages
			$colour = "#FF9900";
		} elseif($calendarEvent->ofAgeMarker == 1) {
			// it's for of age participants only
			$colour = "#1F1AB2";
		} elseif($calendarEvent->ofAgeMarker == -1) {
			// this is an underage event only
			$colour = "#00A779";
		}
		
		// send it for display
		$eventToDisplay[] = convertToSimple($calendarEvent, $colour, false, $masterEventDisplay, false, $currentEvent->id, $timezone);
	}
}

// converts a database object to a simple object that can be read by the calendar
function convertToSimple($event, $color, $urgentBorder, $masterEventTitle, $unregisterable, $masterEventId, $timezone) {
	// get the timezone difference
	// our offset is "4" in JavaScript getTimezoneOffet()/60 terms
	// offset will be the number of hours to subtract (or add if negative)
	$offset = -1 * (4 - $timezone);
	
	// adjust the times accordingly
	$startTime = $event->startTime;
	$startTime->add(DateInterval::createFromDateString($offset . ' hours'));
	$endTime = $event->endTime; 
	$endTime->add(DateInterval::createFromDateString($offset . ' hours'));
	
	$simpleEvent['id'] = $event->id;
	$simpleEvent['title'] = stripslashes($event->title);
	$simpleEvent['masterEventTitle'] = ($masterEventTitle == null ? null : stripslashes($masterEventTitle));
	$simpleEvent['start'] = $startTime->getTimestamp();
	$simpleEvent['end'] = $endTime->getTimestamp();
	$simpleEvent['location'] = stripslashes($event->location);
	$simpleEvent['notes'] = ($event->notes == null ? null : stripslashes($event->notes));
	$simpleEvent['allDay'] = false;
	$simpleEvent['editable'] = false;
	$simpleEvent['color'] = $color;
	$simpleEvent['unregisterable'] = $unregisterable;
	if($urgentBorder) {
		$simpleEvent['borderColor'] = "red";
	}
	$simpleEvent['masterEventId'] = $masterEventId;
	return $simpleEvent;
}

header("Content-type: text/calendar; charset=utf-8");
header("Content-Disposition: inline; filename=calendar.ics");
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// max line length is 75 chars. New line is \\n
$output = "BEGIN:VCALENDAR\n";
$output .= "METHOD:PUBLISH\n";
$output .= "VERSION:2.0\n";
$output .= "PRODID:-//SSMU//myWeek//EN\n";
 
// loop over events
foreach($eventToDisplay as $cal) {
	$location = "";
	if($cal['location'] != null && strlen($cal['location'])) {
		$location = $cal['location'];
	}
	
	$output .= "BEGIN:VEVENT\n";
	$output .= "SUMMARY:" . $cal['title'] . "\n";
	$output .= "UID:" . $cal['id'] . $cal['masterEventId'] . "\n";
	$output .= "STATUS:CONFIRMED\n";
	$output .= "DTSTART:" . date(DATE_ICAL, $cal['start']) . "\n";
	$output .= "DTEND:" . date(DATE_ICAL, $cal['end']) . "\n";
	$output .= "LOCATION:" . $location . "\n";
	$output .= "END:VEVENT\n";
}
 
// close calendar
$output .= "END:VCALENDAR";

echo $output;
?>