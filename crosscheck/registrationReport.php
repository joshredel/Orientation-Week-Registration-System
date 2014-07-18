<?
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// check that the IP address from the request is within McGill
$ipAddress = $_SERVER['REMOTE_ADDR'];
$components = explode(".", $ipAddress);
if(!($components[0] == "132" && ($components[1] == "206" || $components[1] == "216"))) {
	redirect("/");
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=RegistrationReport.csv");
header("Pragma: no-cache");
header("Expires: 0");

// define our participant service and get all participants
$participantService = new services\ParticipantService();
$participants = $participantService->getParticipants();

// print a header
$outputData[] = array("Participant ID", "Last Name", "First Name", "Student ID", "Faculty", "Event Name");

// loop through each participant and create a CSV file for it
foreach($participants as $participant) {
	// loop through each event that participant is registered for
	foreach($participant->events as $event) {
		$outputData[] = array($participant->id, $participant->lastName, $participant->firstName, $participant->studentId, $participant->faculty, $event->eventName);
	}
}

// create the CSV file
outputCSV($outputData);

function outputCSV($data) {
    $outstream = fopen("php://output", 'w');
    function __outputCSV(&$vals, $key, $filehandler) {
        fputcsv($filehandler, $vals); // add parameters if you want
    }
    array_walk($data, '__outputCSV', $outstream);
    fclose($outstream);
}
?>