<?
// initialize services
require_once("../../../functions.php");
session_start();

// sort the calendar eventsby start date
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// sort the associative arrays by start date
function dateCompareAssociative($a, $b) { 
	if($a['startTime']->getTimestamp() == $b['startTime']->getTimestamp()) {
		return 0;
	}
	return ($a['startTime']->getTimestamp() < $b['startTime']->getTimestamp()) ? -1 : 1;
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

// converts a database object to a simple object that can be read by the calendar
function storeForReading($title, $partOf, $location, $calendarEventId, $startTime, $masterEventId) {
	$simpleEvent['title'] = $title;
	$simpleEvent['partOf'] = $partOf;
	$simpleEvent['location'] = $location;
	$simpleEvent['calendarEventId'] = $calendarEventId;
	$simpleEvent['startTime'] = $startTime;
	$simpleEvent['masterEventId'] = $masterEventId;
	return $simpleEvent;
}

// get the caller
//$participant = $_SESSION['caller'];
$participantService = new services\ParticipantService();
$participant = $participantService->getParticipantByRegistrationPassword($_SESSION['caller']->registrationPassword);

// figure out what event(s) they should be at now or that they have next
// first parse to see if they have an event going on right now
$startRange = new DateTime();

$currentHour = (int)($startRange->format('H'));
if($currentHour >= 3) {
	// if start range is after 3AM, then end range should be 3AM the next day
	$endRange = new DateTime($startRange->format("Y-m-d H:m:s"));
	$endRange->add(DateInterval::createFromDateString('1 day'));
	$endRange->setTime(3, 0, 0);
} else {
	// otherwise, the end range should be 3AM the same day
	$endRange = new DateTime($startRange->format("Y-m-d H:m:s"));
	$endRange->setTime(3, 0, 0);
}

$printedEvents = false;
$eventsToProcess = array();
foreach($participant->events as $event) {
	// display calendar events for events that have a schedule of calendar events
	if(!$event->hasSelectableEvents) {
		// see if we should display the name of the master event
		$masterEventTitle = (count($event->calendarEvents) > 1 ? $event->eventName : null);
		$masterEventId = (count($event->calendarEvents) > 1 ? $event->id : null);
		
		// sort the events in increasing order
		$calendarEvents = $event->calendarEvents;
		$calendarEvents = $calendarEvents->toArray();
		usort($calendarEvents, 'dateCompare');
		foreach($calendarEvents as $calendarEvent) {
			// THREE CASES:
			// 1- event is only during one day
			// 2- event spans multiple days, starting on the current day
			// 3- event spans multiple days, ending on the current day
			$canDisplayForParticipant = false;
			if($calendarEvent->startTime >= $startRange && $calendarEvent->startTime <= $endRange || 
			   $calendarEvent->endTime >= $startRange && $calendarEvent->endTime <= $endRange || 
			   $calendarEvent->startTime <= $startRange && $calendarEvent->endTime >= $endRange) {
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
			}
			
			if($canDisplayForParticipant) {
				//echo("Current Event Title: " . stripslashes($calendarEvent->title) . "\n");
				//echo("Current Event Start: " . formatDateTime($calendarEvent->startTime) . "\n");
				//echo("Current Event End: " . formatDateTime($calendarEvent->endTime) . "\n");
				// extract information
				$title = stripslashes($calendarEvent->title);
				$startTime = $calendarEvent->startTime;
				if($calendarEvent->location != null && $calendarEvent->location != "") {
					$location = stripslashes($calendarEvent->location);
				} else {
					$location = null;
				}
				
				// store for reading/processing
				$eventsToProcess[] = storeForReading($title, $masterEventTitle, $location, $calendarEvent->id, $startTime, $masterEventId);
				$printedEvents = true;
			}
		}
	}
}

// loop through all of their personal events
foreach($participant->personalEvents as $personalEvent) {
	if($personalEvent->calendarEvent != null) {
		// display the calendar event info instead of the personal event (it's a placeholder for a selection)
		$personalEvent->calendarEvent->load();
		
		$canDisplayForParticipant = false;
		if($personalEvent->calendarEvent->startTime >= $startRange && $personalEvent->calendarEvent->startTime <= $endRange || 
		   $personalEvent->calendarEvent->endTime >= $startRange && $personalEvent->calendarEvent->endTime <= $endRange || 
		   $personalEvent->calendarEvent->startTime <= $startRange && $personalEvent->calendarEvent->endTime >= $endRange) {
			$canDisplayForParticipant = true;
		}
		
		if($canDisplayForParticipant) {
			// extract information
			$title = stripslashes($personalEvent->calendarEvent->title);
			$startTime = $personalEvent->calendarEvent->startTime;
			if($personalEvent->calendarEvent->location != null && $personalEvent->calendarEvent->location != "") {
				$location = stripslashes($personalEvent->calendarEvent->location);
			} else {
				$location = null;
			}
			
			// store for reading/processing
			$eventsToProcess[] = storeForReading($title, null, $location, $personalEvent->calendarEvent->id, $startTime, null);
			$printedEvents = true;
		}
	}
}

// loop through all of the events we kept
usort($eventsToProcess, 'dateCompareAssociative');
$eventsNow = array();
foreach($eventsToProcess as $eventToProcess) {
	// see if there are any events that are happening right now
	if($eventToProcess['startTime'] <= $startRange) {
		$eventsNow[] = $eventToProcess;
	}
}

if(count($eventsNow) == 0) {
	// we found nothing going on right now, so let's just read the next event
	if(count($eventsToProcess) > 0) {
		$readingString = "the event titled " . $eventsToProcess[0]['title'];
		$playingString = "<Say> the event titled </Say><Play>https://s3.amazonaws.com/myWeek/EventTitles/" . $eventsToProcess[0]['calendarEventId'] . ".aif</Play>";
		$textingString = "You should be at the event titled " . $eventsToProcess[0]['title'];
		
		// read the master event
		if($eventsToProcess[0]['partOf']) {
			$readingString .= " part of " . $eventsToProcess[0]['partOf'];
			if($eventsToProcess[0]['masterEventId'] == 41 || $eventsToProcess[0]['masterEventId'] == 42 || $eventsToProcess[0]['masterEventId'] == 43 || $eventsToProcess[0]['masterEventId'] == 44 || $eventsToProcess[0]['masterEventId'] == 47) {
				$playingString .= "<Play>https://s3.amazonaws.com/myWeek/FroshNames/PartOf.aif</Play>";
				$playingString .= "<Play>https://s3.amazonaws.com/myWeek/FroshNames/" . $eventsToProcess[0]['masterEventId'] . ".aif</Play>";
			}
			$textingString .= " part of " . $eventsToProcess[0]['partOf'];
		}
		
		// read the location
		if($eventsToProcess[0]['location']) {
			$readingString .= " located at " . $eventsToProcess[0]['location'];
			$playingString .= "<Play>https://s3.amazonaws.com/myWeek/Menus/LocatedAt.aif</Play>";
			$playingString .= "<Play>https://s3.amazonaws.com/myWeek/Locations/" . $eventsToProcess[0]['calendarEventId'] . ".aif</Play>";
			$textingString .= " located at " . $eventsToProcess[0]['location'];
		}
		
		// read the start time
		$readingString .= " starting at " . formatForReading($eventsToProcess[0]['startTime']);
		$playingString .= "<Say>starting at " . formatForReading($eventsToProcess[0]['startTime']) . "</Say>";
		$textingString .= " starting at " . formatForTexting($eventsToProcess[0]['startTime']);
	}
} else {
	// there is one or more event going on right now, so read it/them
	$individualEvents = array();
	$playingPieces = array();
	$textingPieces = array();
	foreach($eventsNow as $eventNow) {
		$readOut = "the event titled " . $eventNow['title'];
		$playOut = "<Play>https://s3.amazonaws.com/myWeek/EventTitles/" . $eventNow['calendarEventId'] . ".aif</Play>";
		$textOut = "the event titled " . $eventNow['title'];
		
		// read the master event
		if($eventNow['partOf']) {
			$readOut .= " part of " . $eventNow['partOf'];
			if($eventNow['masterEventId'] == 41 || $eventNow['masterEventId'] == 42 || $eventNow['masterEventId'] == 43 || $eventNow['masterEventId'] == 44 || $eventNow['masterEventId'] == 47) {
				$playOut .= "<Play>https://s3.amazonaws.com/myWeek/FroshNames/PartOf.aif</Play>";
				$playOut .= "<Play>https://s3.amazonaws.com/myWeek/FroshNames/" . $eventNow['masterEventId'] . ".aif</Play>";
			}
			$textOut .= " part of " . $eventNow['partOf'];
		}
		
		// read the location
		if($eventNow['location']) {
			$readOut .= " located at " . $eventNow['location'];
			$playOut .= "<Play>https://s3.amazonaws.com/myWeek/Menus/LocatedAt.aif</Play>";
			$playOut .= "<Play>https://s3.amazonaws.com/myWeek/Locations/" . $eventNow['calendarEventId'] . ".aif</Play>";
			$textOut .= " located at " . $eventNow['location'];
		}
		
		// read the start time
		$readOut .= " which started at " . formatForReading($eventNow['startTime']);
		$playOut .= "<Say>which started at " . formatForReading($eventNow['startTime']) . "</Say>";
		$textOut .= " which started at " . formatForTexting($eventNow['startTime']);
		
		$individualEvents[] = $readOut;
		$playingPieces[] = $playOut;
		$textingPieces[] = $textOut;
	}
	
	$readingString = implode(" or ", $individualEvents);
	$playingString = implode("<Play>https://s3.amazonaws.com/myWeek/Commands/Or.aif</Play>", $playingPieces);
	$textingString = "You should be at " . implode(" or ", $textingPieces);
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<?
    if(count($eventsToProcess) > 0) {
		// get the requested message
        $message = $textingString . " (message powered by http://twil.io)";
        $messageForSms = substr(chunk_split($message, 158, "</Sms><Sms>"), 0, -11);
        $messageForSms = "<Sms>" . $messageForSms . "</Sms>";
		echo($messageForSms);
    } else {
		?>
        <Sms>You have no events in your calendar for today.</Sms>
		<?
    }
	?>
</Response>