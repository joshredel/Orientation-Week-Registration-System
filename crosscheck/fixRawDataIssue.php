<?
/**
 * This tool will go through all participants and check to see if they have raw registration data but not events associated with them.
 * This is a result of errors during registration.
 * If a participant is found to have raw data but no events, it will register them for those events.
 */

// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// initialize services
$participantService = new services\ParticipantService();
$eventService = new services\EventService();

// status counters
$totalRawDataIssues = 0;

// preprocess events to get around the weird issue we have when getting an event with an id
$preEvents = $eventService->getEvents();

// load th events into an associative array
foreach($preEvents as $preEvent) {
	$events[$preEvent->id] = $preEvent;
}

// loop through all participants
$participants = $participantService->getParticipants();
foreach($participants as $participant) {
	if($participant->rawRegistrationData != null && strlen($participant->rawRegistrationData) && ($participant->events == null || count($participant->events) == 0)) {
		// pull all of the events out of the session variable
		$rawEvents = explode(";", $participant->rawRegistrationData);
		
		// loop through each one to find its corresponding event
		foreach($rawEvents as $rawEvent) {
			// break it down again into an array of the ticket info for this event
			//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
			$pureEvent = explode(",", $rawEvent);
			
			// get the event that corresponds to this
			//echo("[" . $pureEvent[0] . "]{" . $events[$pureEvent[0]]->eventName . "}, ");
			$eventToRegister = $events[$pureEvent[0]];
			
			// add the event to the participant's events list (this is effectively registering them)
			$participant->events[] = $eventToRegister;
		}
		// save the participant with the new information
		$participantService->saveParticipant($participant);
		
		// notify
		$totalRawDataIssues++;
		echo("Issue with [" . $participant->id . "] resolved<br />");
	}
}

echo("There were $totalRawDataIssues issues in total; all were fixed.");
?>