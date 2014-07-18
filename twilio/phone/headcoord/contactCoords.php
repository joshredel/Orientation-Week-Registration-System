<?

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Gather numDigits="1" action="/twilio/phone/headcoord/handleContactCoords.php">
    	<Say>
            Press 1 to send a recorded message to the other coordinators.
            Press 2 to start a conference call with all of the coordinators.
            Press 9 to repeat these options.
            Otherwise, press 0 to go to the main menu.
        </Say>
    </Gather>
    <Redirect>/twilio/phone/headcoord/contactCoords.php</Redirect>
</Response>