<?
// converts events for a particular user to JSON
require_once('../functions.php');

$eventId = $_REQUEST['eventId'];
$optionId = $_REQUEST['optionId'];
$passkey = $_REQUEST['passkey'];

// prepare the services we need and the globally used participant
$participantService = new services\ParticipantService();
$calendarEventService = new services\CalendarEventService();
$personalEventService = new services\PersonalEventService();
$participant = $participantService->getParticipantByRegistrationPassword($_REQUEST['passkey']);
$calendarEvent = $calendarEventService->getCalendarEvent($optionId);

echo("You passed: [Event $eventId] - [CalendarEvent $optionId] - [Participant $participant->id]");

// see if the participant has already made a choice for this event
// loop through each of their personal events to find a personal event matching the event we are changing
$foundExisting = false;
foreach($participant->personalEvents as $personalEvent) {
	if($personalEvent->eventId == $eventId) {
		// the user has already selected an option for this event
		// update it to match the new optionId sent to us
		$personalEvent->calendarEvent = $calendarEvent;
		$personalEventService->savePersonalEvent($personalEvent);
		$foundExisting = true;
		echo("Updated an existing personal event");
		break;
	}
}

// see if we found a personal event for this event+option
if(!$foundExisting) {
	// create a new personal event to store their selection
	$personalEvent = new org\fos\PersonalEvent();
	$personalEvent->eventId = $eventId;
	$personalEvent->participant = $participant;
	$personalEvent->calendarEvent = $calendarEvent;
	
	// save it
	$personalEventService->savePersonalEvent($personalEvent);
	echo("Created a new personal event");
}


echo("Succeeded!");
/*
// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_REQUEST['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// loop through all events
$eventToDisplay = array();
foreach($participant->events as $event) {
	if(!$event->hasSelectableEvents) {
		foreach($event->calendarEvents as $calendarEvent) {
			// it's not, so we can display it from this array
			$eventToDisplay[] = convertToSimple($calendarEvent, "red");
		}
	}
}

// loop through all of their personal events
foreach($participant->personalEvents as $personalEvent) {
	$eventToDisplay[] = convertToSimple($personalEvent, "green");
}

// converts a database object to a simple object that can be read by the calendar
function convertToSimple($event, $color) {
	$simpleEvent['id'] = $event->id;
	$simpleEvent['title'] = $event->title;
	$simpleEvent['start'] = $event->startTime->getTimestamp();
	$simpleEvent['end'] = $event->endTime->getTimestamp();
	$simpleEvent['allDay'] = false;
	$simpleEvent['editable'] = false;
	$simpleEvent['color'] = $color;
	return $simpleEvent;
}

// output the JSON version of the events to send
echo(json_encode($eventToDisplay));
*/
?>