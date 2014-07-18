<?
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

// redirect based on digit
switch($digits) {
	case "1":
		header("location:/twilio/phone/common/text.php?message=" . urlencode($_REQUEST['message']));
		break;
	case "2":
		header("location:/twilio/phone/common/email.php?message=" . urlencode($_REQUEST['message']));
		break;
	case "9":
		header("location:/twilio/phone/leader/location.php");
		break;
	case "0":
		header("location:/twilio/phone/leader/");
		break;
}
?>