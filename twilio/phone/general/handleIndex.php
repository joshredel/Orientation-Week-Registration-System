<?
session_start();
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

// redirect based on digit
switch($digits) {
	case 1:
		header("location:/twilio/phone/common/safety.php");
		break;
	case 2:
		header("location:/twilio/phone/general/feedback.php");
		break;
	default:
		header("location:/twilio/phone/general/");
		break;
}
?>