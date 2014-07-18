<?
/**
 * This tool tells us which events do not have calendar events associated with it.
 * Events without calendar events will not display in a student's myWeek calendar.
 */
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// initialize services
$eventService = new services\EventService();

// loop through each event
$events = $eventService->getEvents();
foreach($events as $event) {
	if(count($event->calendarEvents) == 0) {
		if($event->category != "Master" && $event->category != org\fos\Event::ORANGE_EVENT && $event->category != org\fos\Event::OOHLALA) {
			echo("[Event " . $event->id . "] " . $event->eventName . " has no calendar events<br />");
		}
	}
}
?>