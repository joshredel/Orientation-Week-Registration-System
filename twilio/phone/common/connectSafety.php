<?
/**
 * Calls a safety service based on the destination sent.
 */
// initialize services
require_once("../../../functions.php");
session_start();

//choose the safety service
$numberToCall = "";
$serviceName = "";
$soundByte = "";
switch($_REQUEST['destination']) {
	case "WalkSafe":
		$numberToCall = "+15143982498";
		$serviceName = "Walk Safe";
		$soundByte = "https://s3.amazonaws.com/myWeek/SafetyServices/WalkSafe.aif";
		break;
	case "DriveSafe":
		$numberToCall = "+15143988040";
		$serviceName = "Drive Safe";
		$soundByte = "https://s3.amazonaws.com/myWeek/SafetyServices/DriveSafe.aif";
		break;
	case "SACOMSS":
		$numberToCall = "+15143988500";
		$serviceName = "the Sexual Assault Centre";
		$soundByte = "https://s3.amazonaws.com/myWeek/SafetyServices/SACOMMS.aif";
		break;
	case "McGillSecurity":
		$numberToCall = "+15143983000";
		$serviceName = "McGill Security";
		$soundByte = "https://s3.amazonaws.com/myWeek/SafetyServices/McGillSecurity.aif";
		break;
	case "Nightline":
		$numberToCall = "+15143986246";
		$serviceName = "McGill Nightline";
		$soundByte = "https://s3.amazonaws.com/myWeek/SafetyServices/Nightline.aif";
		break;
	default:
		header("location:/twilio/phone/general/");
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<!--
	<Say>
    	You are now being connected to <?= $serviceName ?>.  Your phone will ring as if making a normal call.
    </Say>
    -->
    <Play>https://s3.amazonaws.com/myWeek/Commands/NowBeingConnectedTo.aif</Play>
    <Play><?= $soundByte ?></Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/YourPhoneWillRing.aif</Play>
	<Dial callerId="+15149000125">
    	<Number url="/twilio/phone/common/connectWait.php">
        	<?= $numberToCall ?>
        </Number>
    </Dial>
</Response>