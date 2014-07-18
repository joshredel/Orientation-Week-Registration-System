<?
/**
 * Sends a text message to the current caller with the message passed in a request.
 */
// initialize services
require_once("../../../functions.php");
session_start();

// get the requested message
$message = $_REQUEST['message'] . " (message powered by http://twil.io)";
$messageForSms = substr(chunk_split($message, 158, "</Sms><Sms from=\"+15149000125\">"), 0, -31);
$messageForSms = "<Sms from=\"+15149000125\">" . $messageForSms . "</Sms>";

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

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <!--<Sms from="+15149000125"><?= $message ?></Sms>-->
    <?= $messageForSms ?>
    <Say>Message sent.</Say>
    <Redirect>/twilio/phone/<?= $goToLocation ?>/</Redirect>
</Response>