<?
// initialize services
require_once("../../../functions.php");
session_start();

$recordingUrl = $_REQUEST['RecordingUrl'];

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>
        You are about to send the following message to all participants of your event.  Please do not abuse this functionality.  Any abuses will be noted and billed directly to your faculty.
    </Say>
    <Play><?= $recordingUrl ?></Play>
    <Gather action="/twilio/phone/headcoord/handleContactParticipantsConfirmation.php?RecordingUrl=<?= urlencode($recordingUrl) ?>">
    	<Say>
            Please enter the mass message pass code given to you to proceed.  Press pound to confirm.
            Any other response will cancel.
        </Say>
    </Gather>
    <Redirect>/twilio/phone/headcoord/</Redirect>
</Response>