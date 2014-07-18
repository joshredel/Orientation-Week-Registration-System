<?
/**
 * Sends a text message to the current caller with the message passed in a request.
 */
// initialize services
require_once("../../../functions.php");
session_start();

// get the requested message
$message = $_REQUEST['message'];

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

// send the user an email
mail($caller->email, "[myWeek Gateway] Current/Next Event Location", $message, "From: myWeek Gateway <orientation@ssmu.mcgill.ca>");

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>Message sent.</Say>
    <Redirect>/twiliointest/phone/<?= $goToLocation ?>/</Redirect>
</Response>