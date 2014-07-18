<?
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

/*
Press 1 to send a recorded message to all participants.
Press 2 to send a recorded message to all leaders.
Press 3 to send a recorded message to all o staff.
Press 4 to contact other coordinators in your event.
Press 5 to contact all head coordinators and VP Internals of other froshes.
Press 6 to connect to safety services.
Press 7 to connect to a floor fellow on duty in a residence.
Press 8 to leave a message for the S S M U central communications team.
Otherwise, Press 0 to replay these options.
*/

// redirect based on digit
switch($digits) {
	case 1:
		header("location:/twiliointest/phone/headcoord/contactParticipants.php");
		break;
	case 2:
		header("location:/twiliointest/phone/headcoord/contactStaff.php?classification=" . urlencode("Leader"));
		break;
	case 3:
		header("location:/twiliointest/phone/headcoord/contactStaff.php?classification=" . urlencode("O-Staff"));
		break;
	case 4:
		header("location:/twiliointest/phone/headcoord/contactCoords.php");
		break;
	case 5:
		header("location:/twiliointest/phone/headcoord/contactHeadCoords.php");
		break;
	case 6:
		header("location:/twiliointest/phone/common/safety.php");
		break;
	case 7:
		header("location:/twiliointest/phone/common/floorFellow.php");
		break;
	case 8:
		header("location:/twiliointest/phone/headcoord/feedback.php");
		break;
	case 0:
	default:
		header("location:/twiliointest/phone/headcoord/");
		break;
}
?>