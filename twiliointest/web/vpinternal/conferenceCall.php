<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
checkForSession();
 
// authorize twilio
$sid = "AC64aea60ecd667ddb260c9a89adc0e2d0"; 
$token = "815ac8b5450cbd1fa78b9e2f1f2c8957"; 
$client = new Services_Twilio($sid, $token);

// get the caller and recording
$caller = $currentUser;
$description = $_REQUEST['desc'];
$conferenceName = "Test456";

// call all of the VP Internals
$users = $userService->getUsersInClassification(org\fos\User::VP_INTERNAL);
foreach($users as $user) {
	// make the call
	$call = $client->account->calls->create("+15149000125", $user->phoneNumber, "http://orientation.ssmu.mcgill.ca/twiliointest/web/vpinternal/handleConferenceCall.php?from=" . urlencode($caller->displayName . ", " . $caller->title) . "&conference=" . $conferenceName . "&desc=" . urlencode($description), array());
}

redirect("/admin/overview/");
?>