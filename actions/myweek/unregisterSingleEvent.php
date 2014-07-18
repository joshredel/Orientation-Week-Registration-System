<?
// unregister a single event
require_once('../../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$personalEventService = new services\PersonalEventService();
$participant = $participantService->getParticipantByRegistrationPassword($_POST['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// get the event we want to remove them from
if(!isset($_POST['eventId'])) {
	redirect("/");
}
$eventId = $_POST['eventId'];

// remove the participant from this event
// recreate the events collection, but without the unregistered event
$newEvents = new Doctrine\Common\Collections\ArrayCollection();
$newEventIds = array();
$deletedEventIds = array();
foreach($participant->events as $thisEvent) {
	// only keep this event if it's not the one we want to delete
	if($thisEvent->id != $eventId) {
		$newEvents[] = $thisEvent;
		$newEventIds[] = $thisEvent->id;
	}  else {
		// it's being tossed; store it
		$deletedEventIds[] = $thisEvent->id;
	}
}

// update their raw registration data
// pull all of the events out of the session variable
$rawEvents = explode(";", $participant->rawRegistrationData);

// loop through each one to display it
$eventsToKeep = array();
foreach($rawEvents as $rawEvent) {
	// break it down again into an array of the ticket info for this event
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$pureEvent = explode(",", $rawEvent);
	
	// if this isn't the current event, then add it back to the array
	if($pureEvent[0] != $eventId) {
		// re-store it
		$eventsToKeep[] = $rawEvent;
	}
}

// implode the array into the pure raw registration data
$rawRegistrationData = implode(";", $eventsToKeep);

// remove any personal events that might be associated with deleted events
foreach($participant->personalEvents as $personalEvent) {
	// see if the event associated with this personal event was removed
	if(in_array($personalEvent->eventId, $deletedEventIds)) {
		// it was, so also remove the personal event so it no longer displays in their calendar
		$personalEventService->deletePersonalEvent($personalEvent);
		$message .= "deleted" . $personalEvent->eventId;
	}	
}

// save the participant with its new events
$participant->events = $newEvents;
$participant->rawRegistrationData = $rawRegistrationData;
$participantService->saveParticipant($participant);

// go back to the calendar 
redirect("/myweek/calendar.php?passkey=" . $_POST['passkey']);
?>