<?
// store the digits entered by the user
$digits = $_REQUEST['Digits'];

// redirect based on digit
switch($digits) {
	// send the internals a recorded message
	case 1:
		echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		?>
        <Response>
        	<Say>
                Please record a message to send to all of the head coordinators.
                Press the pound key when you are finished.
            </Say>
            <Record action="/twilio/phone/headcoord/handleContactHeadCoordsRecording.php"  finishOnKey="#" />
            <Say>You did not record anything.</Say>
            <Redirect>/twilio/phone/headcoord/</Redirect>
        </Response>
        <?
		break;
	
	// create a conference call
	case 2:
		echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		?>
        <Response>
        	<Say>
                Please record the subject of the conference call you would like to host.
                This will be played when each head coordinator answers their call.
                Press the pound key when you are finished.
            </Say>
            <Record action="/twilio/phone/headcoord/handleContactHeadCoordsConference.php"  finishOnKey="#" />
            <Say>You did not record anything.</Say>
            <Redirect>/twilio/phone/headcoord/</Redirect>
        </Response>
        <?
		break;
	
	case "9":
		header("location:/twilio/headcoord/contactHeadCoords.php");
		break;
	
	// return to the main menu
	case 0:
		header("location:/twilio/phone/headcoord/");
		break;
}
?>