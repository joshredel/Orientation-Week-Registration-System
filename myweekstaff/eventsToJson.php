<?
// converts events for a particular user to JSON
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$staffService = new services\StaffService();
$staff = $staffService->getStaffByRegistrationPassword($_REQUEST['passkey']);
$staff->event->load();
$currentEvent = $staff->event;
$timezone = $_REQUEST['timezone']; // offset in hours

//redirect if the passkey doesn't match any participant
if($staff == null) {
	echo("error - no participant");
	exit();
}

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
	
	$simpleEvent['id'] = $masterEventId;
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

// sort the calendar events by start date
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// output the JSON version of the events to send
echo(json_encode($eventToDisplay));
?>