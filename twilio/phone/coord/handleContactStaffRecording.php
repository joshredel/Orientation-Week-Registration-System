<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
$eventService = new services\EventService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the caller and recording
$caller = $_SESSION['caller'];
$recordingUrl = $_REQUEST['RecordingUrl'];
$currentEvent = $eventService->getEvent($_SESSION['froshEventId']);//$_SESSION['froshEvent'];
$classification = $_REQUEST['classification'];
if($classification == "O-Staff") {
	$plural = "O Staff";
} elseif($classification == "Leader") {
	$plural = "leaders";
} else {
	$plural = $classification;
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Say>The following message is now being sent to all <?= $plural ?> in your event.  You will receive a text confirmation when it has finished sending.  This could take up to five minutes.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twilio/phone/coord/</Redirect>
</Response>
<?

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($currentEvent->staffs as $staff) {
	// check that this user isn't the current caller
	if($staff->classification == $classification) {
		if($staff->phoneNumber != null && $staff->phoneNumber != "") {
			$triedCount++;
			try {
				// make the call
				$call = $client->account->calls->create(nextNumber(), $staff->phoneNumber, "http://orientation.ssmu.mcgill.ca/twilio/phone/coord/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue"));
				$successCount++;
			} catch (Exception $e) {
				$errors[] = $e;
				$errorCount++;
			}
		}
	}
}

// message the sender to let them know it is complete
$triedCount++;
try {
	$numberFrom = "+15149000125";//"+15005550006";
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass voice message to all $plural was sent successfully to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

mail("joshredel@mac.com", "Twilio: Staff Recording Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nRecording: $recordingUrl\nEvent name: " . $currentEvent->eventName . "\nClassification: $classification\nNumber of staffs: " . count($currentEvent->staffs) . "\nThe following errors occured:\n " . implode("\n", $errors));
?>