<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the caller and recording
$caller = $_SESSION['caller'];
$recordingUrl = $_REQUEST['RecordingUrl'];

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Say>The following message is now being sent to other head coordinators.  You will receive a text confirmation when it has finished sending.  This could take up to five minutes.</Say>
    <Play><?= $recordingUrl ?></Play>
    <Redirect>/twilio/phone/headcoord/</Redirect>
</Response>
<?

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
$users = $userService->getUsersInClassification(org\fos\User::HEAD_COORDINATOR);
foreach($users as $user) {
	// check that this user isn't the current caller
	if($user->phoneNumber != $caller->phoneNumber) {
		if($user->phoneNumber != null && $user->phoneNumber != "") {
			$triedCount++;
			try {
				// make the call
				$call = $client->account->calls->create(nextNumber(), $user->phoneNumber, "http://orientation.ssmu.mcgill.ca/twilio/phone/headcoord/handleRecordingCall.php?from=" . urlencode($caller->displayName) . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Continue"));
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
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass voice message to all head coords was sent successfully to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

mail("joshredel@mac.com", "Twilio: Head Coords Recording Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nRecording: $recordingUrl\nThe following errors occured:\n " . implode("\n", $errors));
?>