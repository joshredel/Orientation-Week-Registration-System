<?
/**
 * This tool runs some basic statistics on registrations.
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

// the statistics we want to keep track of
$maxEventsRegistered = 0;
$maxEventsRegisteredId = 0;
$registrationStatistics = array();

// loop through all participants
$participants = $participantService->getParticipants();
foreach($participants as $participant) {
	$regsiteredEventCount = count($participant->events);
	
	// see if this person has more events registered than the running max
	if($regsiteredEventCount > $maxEventsRegistered) {
		$maxEventsRegistered = $regsiteredEventCount;
		$maxEventsRegisteredId = $participant->id;
	}
	
	// record the number registered
	if(!isset($registrationStatistics[$regsiteredEventCount])) {
		$registrationStatistics[$regsiteredEventCount] = 1;
	} else {
		$registrationStatistics[$regsiteredEventCount]++;
	}
}

echo("[Participant $maxEventsRegisteredId] has the most events registered: $maxEventsRegistered<br /><br />");

// print details about number of people registered with different event counts
ksort($registrationStatistics);
foreach($registrationStatistics as $numberEvents=>$count) {
	echo("Registered for $numberEvents events: $count <br />");
	//echo("$numberEvents,$count<br />");
}
?>