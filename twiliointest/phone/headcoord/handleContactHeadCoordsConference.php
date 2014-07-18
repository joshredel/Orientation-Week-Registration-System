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
$conferenceName = "CoordConference" . $_SESSION['froshEventId'];

// call all head coordinators
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
				$call = $client->account->calls->create("+15149000125", $user->phoneNumber, "http://orientation.ssmu.mcgill.ca/twiliointest/phone/headcoord/handleConferenceCallHeadCoords.php?from=" . urlencode($caller->displayName) . "&conference=" . $conferenceName . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Hangup", "StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/phone/callback.php"));
				$successCount++;
			} catch (Exception $e) {
				$errors[] = $e;
				$errorCount++;
			}
		}
	}
}

mail("joshredel@mac.com", "Twilio: Head Coords Conference Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nThe following errors occured:\n " . implode("\n", $errors));

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<Say>You are now entering the conference.  Hold music will play until others join.</Say>
    <Dial>
    	<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars"><?= $conferenceName ?></Conference>
    </Dial>
    <Redirect>/twiliointest/phone/headcoord/</Redirect>
</Response>