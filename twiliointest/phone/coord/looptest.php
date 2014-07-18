<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
 
// see if they entered the proper passcode
if($_REQUEST['Digits'] != "260253249") {
	redirect("/twiliointest/phone/coord/");
}
 
// authorize twilio
setupTwilio();

// get the caller and recording
$caller = $_SESSION['caller'];
$recordingUrl = $_REQUEST['RecordingUrl'];
$eventService = new services\EventService();
$currentEvent = $eventService->getEvent(47);

// call all of the VP Internals
$callCount = 0;
foreach($currentEvent->participants as $participant) {
	if($participant->phoneNumber != null && $participant->phoneNumber != "") {
		// make the call
		echo("Calling " . nextNumber() . "<br />");
		$callCount++;
	}
}

echo("DONE - called $callCount people");
?>