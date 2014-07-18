<?
// initialize services
require_once("../../../functions.php");
session_start();

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

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<? if($_SESSION['froshEventId'] != null) { ?>
<Response>
    <!--
    <Say>
        This service is for leaving feedback, not for information requests.  Your message will not be anonymous.  You will receive a response as time allows and as appropriate.
    </Say>
    <Say>
    	Please record your message after the beep.  Press the pound key when you are finished.
    </Say>
    -->
    <Play>https://s3.amazonaws.com/myWeek/Menus/LeaveMessageForCoordsRecording.aif</Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/RecordMessageAfterBeep.aif</Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/PressPoundWhenFinished.aif</Play>
    <Record action="/twilio/phone/common/handleFeedbackForCoords.php"  finishOnKey="#" />
    <Say>You did not record anything.</Say>
    <Redirect>/twilio/phone/<?= $goToLocation ?>/</Redirect>
</Response>
<? } else { ?>
<Response>
    <Say>You are not registered for a frosh event.</Say>
    <Redirect>/twilio/phone/<?= $goToLocation ?>/</Redirect>
</Response>
<? }?>