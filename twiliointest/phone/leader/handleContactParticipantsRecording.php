<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the caller and recording
$caller = $_SESSION['caller'];
$recordingUrl = $_REQUEST['RecordingUrl'];
$currentEvent = $eventService->getEvent($_SESSION['froshEventId']);

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Say>The following message is now being sent to everyone in your group.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twiliointest/phone/leader/</Redirect>
</Response>
<?

// call all of members of their group
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
$participants = $participantService->getParticipantsInGroup($currentEvent->id, $caller->groupNumber);
foreach($participants as $participant) {
	if($participant->phoneNumber != null && $participant->phoneNumber != "") {
		$triedCount++;
		try {
			// make the call
			$call = $client->account->calls->create(nextNumber(), $participant->phoneNumber, "http://orientation.ssmu.mcgill.ca/twilio/phone/leader/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue", "StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/phone/callback.php"));
			$successCount++;
		} catch (Exception $e) {
			$errors[] = $e;
			$errorCount++;
		}
	}
}

// message the sender to let them know it is complete
$triedCount++;
try {
	$numberFrom = "+15149000125";//"+15005550006";
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass voice message to everyone in your group was successfully sent to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twiliointest/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}


mail("joshredel@mac.com", "Twilio: Leader to Participant Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nRecording: $recordingUrl\nGroup: " . $caller->groupNumber . "\nEventID: " . $_SESSION['froshEventId'] . "\nThe following errors occured:\n " . implode("\n", $errors));
?>