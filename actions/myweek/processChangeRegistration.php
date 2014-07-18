<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'changeRegistrationDone') { 
	redirect("/");
}

// prepare the services we need and the globally used participant
$participantService = new services\ParticipantService();
$eventService = new services\EventService();
$personalEventService = new services\PersonalEventService();
$participant = $participantService->getParticipantByRegistrationPassword($_POST['passkey']);

//redirect if the passkey doesn't match any participant or the eid doesn't match any events
if($participant == null) {
	redirect("/");
}

// store the event registration information
$participant->rawRegistrationData = $_POST['registeredEvents'];

// make a list of events we already have
$existingEventIds = array();
foreach($participant->events as $event) {
	$existingEventIds[] = $event->id;
}

// preprocess events to get around the weird issue we have when getting an event with an id
$preEvents = $eventService->getEvents();

// load th events into an associative array
foreach($preEvents as $preEvent) {
	$events[$preEvent->id] = $preEvent;
}

// get the events
// pull all of the events out of the session variable
$rawEvents = explode(";", $_POST['registeredEvents']);

// loop through each one to display it
$newEventsRequiringPayment = 0;
$newEvents = new Doctrine\Common\Collections\ArrayCollection();
$newEventIds = array();
foreach($rawEvents as $rawEvent) {
	// break it down again into an array of the ticket info for this event
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$pureEvent = explode(",", $rawEvent);
	
	// get the event from the database
	$event = $events[$pureEvent[0]];
	
	// add the event to the participant's events list (this is effectively registering them)
	$newEvents[] = $event;
	$newEventIds[] = (int)$pureEvent[0];
	
	// see if we already have this event
	if(!in_array((int)$pureEvent[0], $existingEventIds)) {
		// we don't; it's new
		// see if the event has a cost
		if($event->costs && count($event->costs) > 0) {
			// it has a cost and we have not yet tried paying for it, so mark as such
			$newEventsRequiringPayment++;
		}
	}
}

// find the events we deleted
$deletedEventIds = array();
foreach($existingEventIds as $eventId) {
	if(!in_array($eventId, $newEventIds)) {
		// this event is no longer in their registrations, so it has been deleted
		$deletedEventIds[] = $eventId;
		$message .= "deleted" . $eventId;
	}
}

// remove any personal events that might be associated with deleted events
foreach($participant->personalEvents as $personalEvent) {
	// see if the event associated with this personal event was removed
	if(in_array($personalEvent->eventId, $deletedEventIds)) {
		// it was, so also remove the personal event so it no longer displays in their calendar
		$personalEventService->deletePersonalEvent($personalEvent);
		//$message .= "deleted" . $personalEvent->eventId;
	}	
}

// save the participant again after the events have been added
$participant->events = $newEvents;
$participantService->saveParticipant($participant);

// were there new events requiring registration?
if($newEventsRequiringPayment > 0) {
	// if there are payments to be made, go to the select payment page
	redirect("/myweek/selectPayment.php?passkey=" . $_POST['passkey']);
} else {
	// otherwise, go back to my
	redirect("/myweek/?passkey=" . $_POST['passkey']);
}
?>