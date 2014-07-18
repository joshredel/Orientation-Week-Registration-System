<?
// initialize services
require_once("../../../functions.php");
session_start();

$classification = $_REQUEST['classification'];
if($classification == "O-Staff") {
	$plural = "O Staff";
} elseif($classification == "Leader") {
	$plural = "leaders";
} else {
	$plural = $classification;
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>
        Please record a message to send to all <?= $plural ?>.
        Press the pound key when you are finished.
    </Say>
    <Record action="/twiliointest/phone/headcoord/handleContactStaffRecording.php?classification=<?= urlencode($classification) ?>"  finishOnKey="#" />
    <Say>You did not record anything.</Say>
    <Redirect>/twiliointest/phone/headcoord/</Redirect>
</Response>