<?
/**
 * This file is used by Banner to check for valid registrations (i.e. that Faculty matches student ID matches last name)
 */
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

// define our participant service and get all participants
$participantService = new services\ParticipantService();
$participants = $participantService->getParticipants();

// loop through each participant and create a CSV file for it
foreach($participants as $participant) {
	if ($participant->approvedFacultyCheck == 1) { // this is required by law - DO NOT REMOVE
		echo("{$participant->studentId},{$participant->faculty},{$participant->lastName}\n");
	}
}
?>