<?
// initialize services
require_once("../../../functions.php");
$recordingService = new services\CoordRecordingService();
session_start();

// get the caller
$caller = $_SESSION['caller'];

// determine the return location
switch($_SESSION['classification']) {
	case org\fos\User::GENERAL:
		$goToLocation = "general";
		break;
	case org\fos\User::PARTICIPANT:
		$goToLocation = "participant";
		break;
	case org\fos\User::LEADER:
		$goToLocation = "leader";
		break;
	case org\fos\User::OSTAFF:
		$goToLocation = "ostaff";
		break;
	case org\fos\User::COORDINATOR:
		$goToLocation = "coordinator";
		break;
	case org\fos\User::HEAD_COORDINATOR:
		$goToLocation = "headcoord";
		break;
	case org\fos\User::ADMINISTRATOR:
		$goToLocation = "admin";
		break;
	case org\fos\User::COMMUNICATIONS:
		$goToLocation = "comms";
		break;
}

// create the recording object
$recording = new org\fos\CoordRecording();
$recording->eventId = $_SESSION['froshEventId'];
$recording->submitterId = $caller->id;
$recording->url = $_REQUEST['RecordingUrl'];
$recording->recordingCategory = $_SESSION['classification'];//org\fos\CoordRecording::PARTICIPANT;

$recordingService->saveCoordRecording($recording);

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>Thank you for the message.</Say>
    <Redirect>/twiliointest/phone/<?= $goToLocation ?>/</Redirect>
</Response>