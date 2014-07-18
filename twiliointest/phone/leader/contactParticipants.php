<?
// initialize services
require_once("../../../functions.php");
session_start();
$participantService = new services\ParticipantService();
$caller = $_SESSION['caller'];
$participants = $participantService->getParticipantsInGroup($_SESSION['froshEventId'], $caller->groupNumber);

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<? if(count($participants) > 0) { ?>
<Response>
    <Say>
        Please record a message to send to the students in your group.
        Press the pound key when you are finished.
    </Say>
    <Record action="/twiliointest/phone/leader/handleContactParticipantsRecording.php"  finishOnKey="#" />
    <Say>You did not record anything.</Say>
    <Redirect>/twiliointest/phone/leader/</Redirect>
</Response>
<? } else { ?>
<Response>
    <Say>There are no members in your group.  Please add students on your my Week page.</Say>
    <Redirect>/twiliointest/phone/leader/</Redirect>
</Response>
<? } ?>