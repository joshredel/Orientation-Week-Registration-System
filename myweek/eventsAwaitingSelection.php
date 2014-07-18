<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$participantService = new services\ParticipantService();
$participant = $participantService->getParticipantByRegistrationPassword($_REQUEST['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	echo("error - no participant");
	exit();
}

// make a map of personal events with an event id and their corresponding option selection
$selectedOptions = array();
foreach($participant->personalEvents as $personalEvent) {
	// see if this personal event is a placeholder for an event option selection
	if($personalEvent->calendarEvent) {
		// it is a palceholder; store its info in the map
		$personalEvent->calendarEvent->load();
		$selectedOptions[$personalEvent->eventId] = $personalEvent->calendarEvent->id;
	}
}

// see if there are any events that the participant needs to make a selection for
$eventsAwaitingSelection = 0;
// loop through each of the participant's events
foreach($participant->events as $event) {
	// see if the event has selectable events
	if($event->hasSelectableEvents) {
		// see if they have made an initial decision
		if(!isset($selectedOptions[$event->id])) {
			// they haven't; show a selectable option
			$eventsAwaitingSelection++;
		}
	}
}

echo($eventsAwaitingSelection);
?>