<?
// converts events for a particular user to JSON
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_REQUEST['passkey']);
$timezone = $_REQUEST['timezone']; // offset in hours

//redirect if the passkey doesn't match any participant
if($participant == null) {
	echo("error - no participant");
	exit();
}

// loop through all events
$eventToDisplay = array();
foreach($participant->events as $event) {
	// display calendar events for events that have a schedule of calendar events
	if(!$event->hasSelectableEvents) {
		// see if we should display the name of the master event
		$masterEventDisplay = (count($event->calendarEvents) > 1 ? $event->eventName : null);
		
		foreach($event->calendarEvents as $calendarEvent) {
			// determine if we can display this event for the user (based on age for event)
			$canDisplayForParticipant = false;
			if($calendarEvent->ofAgeMarker == 0) {
				// we can display; it's for all ages
				$canDisplayForParticipant = true;
			} elseif($calendarEvent->ofAgeMarker == 1) {
				// we need to make sure they are of age for this event
				if(ofAgeForEvent($participant, $calendarEvent->startTime)) {
					$canDisplayForParticipant = true;
				}
			} elseif($calendarEvent->ofAgeMarker == -1) {
				// they need to be underage to see this event (it is an underage alternative
				if(!ofAgeForEvent($participant, $calendarEvent->startTime)) {
					$canDisplayForParticipant = true;
				}
			}
			
			if($canDisplayForParticipant) {
				// determine the colour we want to use
				$displayType = $calendarEvent->event->displayType;
				$urgentBorder = $displayType == org\fos\Event::DISPLAY_DONT_MISS;
				$colour = convertDisplayToColour($calendarEvent->event->displayType);
				
				// determine if the user can unregister for this event
				if($event->category == org\fos\Event::REZ_FEST || 
				   $event->category == org\fos\Event::DISCOVER_MCGILL || 
				   $event->category == org\fos\Event::FACULTY_FROSH || 
				   $event->category == org\fos\Event::NON_FACULTY_FROSH) {
					   $unregisterable = false;
				} else {
					$unregisterable = true;
				}
				
				// send it for display
				$eventToDisplay[] = convertToSimple($calendarEvent, $colour, $urgentBorder, $masterEventDisplay, $unregisterable, $event->id, $timezone);
			}
		}
	}
}

// loop through all of their personal events
foreach($participant->personalEvents as $personalEvent) {
	if($personalEvent->calendarEvent != null) {
		// display the calendar event info instead of the personal event (it's a placeholder for a selection)
		$personalEvent->calendarEvent->load();
		
		// determine the colour we want to use
		$displayType = $personalEvent->calendarEvent->event->displayType;
		$urgentBorder = $displayType == org\fos\Event::DISPLAY_DONT_MISS;
		$colour = convertDisplayToColour($displayType);
		
		// send it for display
		$eventToDisplay[] = convertToSimple($personalEvent->calendarEvent, $colour, $urgentBorder, null, true, $personalEvent->calendarEvent->event->id, $timezone);
	} else {
		// display the actual personal event information
		$eventToDisplay[] = convertToSimple($personalEvent, "#353535", false, null, false, null, $timezone);
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

// determine whether or not they will be of age during a given date/time
function ofAgeForEvent($participant, $eventDate) {
	$eventDate = $eventDate->getTimestamp();
	$birthDate = $participant->dateOfBirth->getTimestamp();
	
	$eventMonth = date('n', $eventDate);
	$eventDay = date('j', $eventDate);
	$eventYear = date('Y', $eventDate);
	
	$birthMonth = date('n', $birthDate);
	$birthDay = date('j', $birthDate);
	$birthYear = date('Y', $birthDate);
	
	if(($eventMonth >= $birthMonth && $eventDay >= $birthDay) || ($eventMonth > $birthMonth)) {
		$futureAge = $eventYear - $birthYear;
	} else  {
		$futureAge = $eventYear - $birthYear - 1;
	}
	
	// see if they will be underage
	if($futureAge < 18) {
		// underage!
		return false;
	} else {
		// they will be of age
		return true;
	}
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