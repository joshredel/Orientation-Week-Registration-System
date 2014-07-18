<?
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

/*
Press 1 to send a recorded message to all leaders.
Press 2 to send a recorded message to all o staff.
Press 3 to contact other coordinators in your event.
Press 4 to connect to safety services.
Press 5 to connect to a floor fellow on duty in a residence.
Press 6 to leave a message for the S S M U central communications team.
Otherwise, Press 9 to replay these options.
*/

// redirect based on digit
switch($digits) {
	case 1:
		header("location:/twiliointest/phone/coord/contactStaff.php?classification=" . urlencode("Leader"));
		break;
	case 2:
		header("location:/twiliointest/phone/coord/contactStaff.php?classification=" . urlencode("O-Staff"));
		break;
	case 3:
		header("location:/twiliointest/phone/coord/contactCoords.php");
		break;
	case 4:
		header("location:/twiliointest/phone/common/safety.php");
		break;
	case 5:
		header("location:/twiliointest/phone/common/floorFellow.php");
		break;
	case 6:
		header("location:/twiliointest/phone/coord/feedback.php");
		break;
	case 9:
	default:
		header("location:/twiliointest/phone/coord/");
		break;
}
?>