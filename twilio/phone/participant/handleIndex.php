<?
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

// store the digits entered by the user
$digits = $_REQUEST['Digits'];

// see if the participant is in rez
$inRez = false;
if($caller->livingStyle == "InRez") {
	$inRez = true;
}

/*
// In Rez
Press 1 to connect to safety services.
Press 2 to know where you should be right now according to your my Week calendar.
Press 3 to connect to your orientation leaders.
Press 4 to connect to the floor fellow on duty in your residence
Press 5 to let us know how you or your leaders are froshing.
Or, Press 6 to leave a message for your frosh event organizers.
Otherwise, Press 9 to repeat this menu.

// Off Campus
Press 1 to connect to safety services.
Press 2 to know where you should be right now according to your my Week calendar.
Press 3 to connect to your orientation leaders.
Press 4 to let us know how you or your leaders are froshing.
Or, Press 5 to leave a message for your frosh event organizers.
Otherwise, Press 9 to repeat this menu.
*/

// redirect based on digit
if($inRez) {
	switch($digits) {
		case 1:
			header("location:/twilio/phone/common/safety.php");
			break;
		case 2:
			header("location:/twilio/phone/participant/location.php");
			break;
		case 3:
			header("location:/twilio/phone/participant/connectToLeaders.php");
			break;
		case 4:
			header("location:/twilio/phone/common/floorFellow.php");
			break;
		case 5:
			header("location:/twilio/phone/participant/feedback.php");
			break;
		case 6:
			header("location:/twilio/phone/common/feedbackForCoords.php");
			break;
		case 9:
		default:
			header("location:/twilio/phone/participant/");
			break;
	}
} else {
	switch($digits) {
		case 1:
			header("location:/twilio/phone/common/safety.php");
			break;
		case 2:
			header("location:/twilio/phone/participant/location.php");
			break;
		case 3:
			header("location:/twilio/phone/participant/connectToLeaders.php");
			break;
		case 4:
			header("location:/twilio/phone/participant/feedback.php");
			break;
		case 5:
			header("location:/twilio/phone/common/feedbackForCoords.php");
			break;
		case 9:
		default:
			header("location:/twilio/phone/participant/");
			break;
	}
}
?>