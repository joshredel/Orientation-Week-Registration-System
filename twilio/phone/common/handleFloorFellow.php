<?
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

// store the digits entered by the user
$digits = $_REQUEST['Digits'];

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

// redirect based on digit
switch($digits) {
	case 1:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=McConnell");
		break;
	case 2:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=Molson");
		break;
	case 3:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=Gardner");
		break;
	case 4:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=RVC");
		break;
	case 5:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=NewRez");
		break;
	case 6:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=Carrefour");
		break;
	case 7:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=Solin");
		break;
	case 8:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=Citadelle");
		break;
	case 9:
		header("location:/twilio/phone/common/connectFloorFellow.php?destination=MORE");
		break;
	case 0:
		header("location:/twilio/phone/" . $goToLocation . "/");
		break;
}
?>