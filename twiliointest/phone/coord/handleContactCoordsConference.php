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
$conferenceName = "HeadCoordConference";
$currentEvent = $eventService->getEvent($_SESSION['froshEventId']);//$_SESSION['froshEvent'];

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
				$triedCount++;
				try {
					// make the call
					$call = $client->account->calls->create("+15149000125", $user->phoneNumber, "http://orientation.ssmu.mcgill.ca/twiliointest/phone/coord/handleConferenceCallCoords.php?from=" . urlencode($caller->displayName) . "&conference=" . $conferenceName . "&url=" . urlencode($recordingUrl), array("IfMachine" => "Hangup"));
					$successCount++;
				} catch (Exception $e) {
					$errors[] = $e;
					$errorCount++;
				}
			}
		}
	}
}

mail("joshredel@mac.com", "Twilio: Coords Conference Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nThe following errors occured:\n " . implode("\n", $errors));

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<Say>You are now entering the conference.  Hold music will play until others join.</Say>
    <Dial>
    	<Conference waitUrl="http://twimlets.com/holdmusic?Bucket=com.twilio.music.guitars"><?= $conferenceName ?></Conference>
    </Dial>
    <Redirect>/twiliointest/phone/coord/</Redirect>
</Response>