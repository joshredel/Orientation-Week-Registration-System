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
$staffService = new services\StaffService();
$staff = $staffService->getStaffByRegistrationPassword($_SESSION['caller']->registrationPassword);
$eventService = new services\EventService();
$event = $eventService->getEvent($_SESSION['froshEventId']);
//$event = $_SESSION['froshEvent'];

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
			$canDisplayForParticipant = true;
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
	<? if(count($eventsToProcess) > 0) { ?>
    <!--
    <Say>According to your schedule, you should be at <?= $readingString ?>.</Say>
    -->
    <!--<Play>https://s3.amazonaws.com/myWeek/Menus/BeAtLocationTitled.aif</Play>-->
    <Say>You should be at </Say>
    <?= $playingString ?>
    <Gather numDigits="1" action="/twilio/phone/ostaff/handleLocation.php?message=<?= urlencode($textingString) ?>">
    	<!--
    	<Say>
        	Press 1 if you would like to receive it in a text message.
            Press 2 if you would like it sent to your email.
            Press 9 to repeat these options.
            Otherwise, press 0 to go to the main menu.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/LocationMenu.aif</Play>
    </Gather>
    <Redirect>/twilio/phone/ostaff/location.php</Redirect>
    <? } else { ?>
    <Say>You have no events in your calendar for today.</Say>
    <Redirect>/twilio/phone/ostaff/</Redirect>
    <? } ?>
</Response>