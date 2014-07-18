<?
/**
 * Handles a digit submission from the leader main menu.
 */
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

/*
Press 1 to connect with safety services.
Press 2 to send a recorded message to the students in your group.
Press 3 to leave a message for your frosh event organizers.
Press 4 to know where you should be right now according to your event calendar.
Press 5 to connect to the floor fellow on duty in a residence.
Press 6 to let us know how you, your co-leaders, or anyone else are froshing.
Or, press 9 to repeat these options.
*/

// redirect based on digit
switch($digits) {
	case 1:
		header("location:/twiliointest/phone/common/safety.php");
		break;
	case 2:
		header("location:/twiliointest/phone/leader/contactParticipants.php");
		break;
	case 3:
		header("location:/twiliointest/phone/common/feedbackForCoords.php");
		break;
	case 4:
		header("location:/twiliointest/phone/leader/location.php");
		break;
	case 5:
		header("location:/twiliointest/phone/common/floorFellow.php");
		break;
	case 6:
		header("location:/twiliointest/phone/leader/feedback.php");
		break;
	case 9:
	default:
		header("location:/twiliointest/phone/leader/");
		break;
}
?>