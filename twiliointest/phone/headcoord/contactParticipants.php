<?
// initialize services
require_once("../../../functions.php");
session_start();

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Say>
        Please record a message to send to all participants.
        Press the pound key when you are finished.
    </Say>
    <Record action="/twiliointest/phone/headcoord/handleContactParticipantsRecording.php"  finishOnKey="#" />
    <Say>You did not record anything.</Say>
    <Redirect>/twiliointest/phone/headcoord/</Redirect>
</Response>