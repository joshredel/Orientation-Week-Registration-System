<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

$original = "Hi group 8 this is Eric Kueper.  I'm texting you to make you aware that one of your froshies (Kevin) was received by msert and an ambulance was called.  Asa is with him and will update us accordingly.  If you have further questions feel free to contact me.  He should be fine, these things can happen.";
$message = "Message from Eric from Frosh of Thrones: " . $original . " (message powered by http://twil.io)";
$messageForSms = substr(chunk_split($message, 158, "<::>"), 0, -4);
$messagesToSend = explode("<::>", $messageForSms);


// get the caller and recording
//$caller = $_SESSION['caller'];
//$recordingUrl = $_REQUEST['RecordingUrl'];
//$currentEvent = $_SESSION['froshEvent'];
/*$classification = $_REQUEST['classification'];
if($classification == "O-Staff") {
	$plural = "O Staff";
} elseif($classification == "Leader") {
	$plural = "leaders";
} else {
	$plural = $classification;
}*/

//$eventService = new services\EventService();
//$currentEvent = $eventService->getEvent(43);//$_SESSION['froshEventId']);
//$staffs = $currentEvent->staffs;
$staffService = new services\StaffService();
$staffs = $staffService->getStaffInGroup(43, 8);
foreach($staffs as $staff) {
	echo("Member: " . $staff->displayName . "<br />");
}

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($staffs as $staff) {
	// check that this user isn't the current caller
	if($staff->classification == "Leader") {//$classification) {
		if($staff->phoneNumber != null && $staff->phoneNumber != "") {
			$triedCount++;
			try {
				// make the call
				//$call = $client->account->calls->create(nextNumber(), $staff->phoneNumber, "http://orientation.ssmu.mcgill.ca/twilio/phone/headcoord/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue"));
				$numberFrom = nextNumber();
				foreach($messagesToSend as $messageToSend) {
					$message = $client->account->sms_messages->create($numberFrom, $staff->phoneNumber, $messageToSend, array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
					echo("Texting " . $staff->phoneNumber . " [$messageToSend]<br />");
					echo($message->sid . "<br />");
					$successCount++;
				}
				echo("<br />");
			} catch (Exception $e) {
				$errors[] = $e;
				$errorCount++;
			}
		}
	}
}

// make a text to web developer for cross check
$triedCount++;
try {
	// make the text
	$numberFrom = nextNumber(); //"+15005550006"
	foreach($messagesToSend as $messageToSend) {
		$message = $client->account->sms_messages->create($numberFrom, "+15142206606", $messageToSend, array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
echo $message->sid;
		echo("Texting +15142206606 <br />");
		echo($message->sid . " [$messageToSend]<br />");
		$successCount++;
	}
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

mail("joshredel@mac.com", "Twilio: Staff Texting Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nEvent title: " . $currentEvent->eventName . "\nNumber of staffs: " . count($currentEvent->staffs) . "\nThe following errors occured:\n " . implode("\n", $errors));

/*echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");*/
?>
<!--
<Response>
	<Say>The following message has been sent successfully to all <?= $plural ?>.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twilio/phone/headcoord/</Redirect>
</Response>
-->