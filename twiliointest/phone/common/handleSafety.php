<?
// initialize services
require_once("../../../functions.php");
session_start();

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
		header("location:/twiliointest/phone/common/connectSafety.php?destination=WalkSafe");
		break;
	case 2:
		header("location:/twiliointest/phone/common/connectSafety.php?destination=DriveSafe");
		break;
	case 3:
		header("location:/twiliointest/phone/common/connectSafety.php?destination=SACOMSS");
		break;
	case 4:
		header("location:/twiliointest/phone/common/connectSafety.php?destination=McGillSecurity");
		break;
	case 5:
		header("location:/twiliointest/phone/common/connectSafety.php?destination=Nightline");
		break;
	case 9:
		header("location:/twiliointest/phone/common/leader/safety.php");
		break;
	case 0:
		header("location:/twiliointest/phone/" . $goToLocation . "/");
		break;
}
?>