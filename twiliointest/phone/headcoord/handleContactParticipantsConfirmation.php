<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
$eventService = new services\EventService();
 
// see if they entered the proper passcode
if($_REQUEST['Digits'] != "260253249") {
	redirect("/twiliointest/phone/headcoord/");
}
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the caller and recording
$caller = $_SESSION['caller'];
$recordingUrl = $_REQUEST['RecordingUrl'];
$currentEvent = $eventService->getEvent($_SESSION['froshEventId']);//$_SESSION['froshEvent'];

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Say>The following message is now being sent to all froshies in your event.  You will receive a text confirmation when it has finished sending.  This could take up to five minutes.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twiliointest/phone/headcoord/</Redirect>
</Response>
<?

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($currentEvent->participants as $participant) {
	if($participant->phoneNumber != null && $participant->phoneNumber != "") {
		$triedCount++;
		try {
			// make the call
			$call = $client->account->calls->create(nextNumber(), $participant->phoneNumber, "http://orientation.ssmu.mcgill.ca/twilio/phone/headcoord/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue", "StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/phone/callback.php"));
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
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass voice message to all froshies was sent successfully to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twiliointest/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

mail("joshredel@mac.com", "Twilio: Contact Participants Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nRecording: $recordingUrl\nEvent name: " . $currentEvent->eventName . "\nClassification: $classification\nNumber of participants: " . count($currentEvent->participants) . "\nThe following errors occured:\n " . implode("\n", $errors));
?>