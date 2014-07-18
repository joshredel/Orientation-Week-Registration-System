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

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Say>The following message is now being sent to other coordinators.  You will receive a text confirmation when it has finished sending.  This could take up to five minutes.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twiliointest/phone/coord/</Redirect>
</Response>
<?

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($currentEvent->roles as $role) {
	foreach($role->users as $user) {
		// check that this user isn't the current caller
		if(($user->classification == org\fos\User::COORDINATOR || $user->classification == org\fos\User::HEAD_COORDINATOR) && $user->id != $caller->id) {
			if($user->phoneNumber != null && $user->phoneNumber != "") {
				// make the call
				$triedCount++;
				try {
					$call = $client->account->calls->create(nextNumber(), $user->phoneNumber, "http://orientation.ssmu.mcgill.ca/twiliointest/phone/coord/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue"));
					$successCount++;
				} catch (Exception $e) {
					$errors[] = $e;
					$errorCount++;
				}
			}
		}
	}
}

// message the sender to let them know it is complete
$triedCount++;
try {
	$numberFrom = "+15149000125";//"+15005550006";
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass voice message to all coords was sent successfully to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twiliointest/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

mail("joshredel@mac.com", "Twilio: Coords Recording Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nRecording: $recordingUrl\nThe following errors occured:\n " . implode("\n", $errors));
?>