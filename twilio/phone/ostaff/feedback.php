<?
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<!--
    <Say>
        Your message will be anonymous unless you state your name and phone number.  If you leave your name and number, you will receive a response as time allows and as appropriate.  This is not an emergency service.  If you have an emergency, please hang up and dial 9 1 1.
    </Say>
    -->
    <Play>https://s3.amazonaws.com/myWeek/Froshing/FroshingParticipantRecording.aif</Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/RecordMessageAfterBeep.aif</Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/PressPoundWhenFinished.aif</Play>
    <Record action="/twilio/phone/ostaff/handleFeedback.php"  finishOnKey="#" />
    <Say>You did not record anything.</Say>
    <Redirect>/twilio/phone/ostaff/</Redirect>
</Response>