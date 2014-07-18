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
                Please record a message to send to other coordinators.
                Press the pound key when you are finished.
            </Say>
            <Record action="/twiliointest/phone/headcoord/handleContactCoordsRecording.php"  finishOnKey="#" />
            <Say>You did not record anything.</Say>
            <Redirect>/twiliointest/phone/headcoord/</Redirect>
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
                This will be played when each coordinator answers their call.
                Press the pound key when you are finished.
            </Say>
            <Record action="/twiliointest/phone/headcoord/handleContactCoordsConference.php"  finishOnKey="#" />
            <Say>You did not record anything.</Say>
            <Redirect>/twiliointest/phone/headcoord/</Redirect>
        </Response>
        <?
		break;
	
	case "9":
		header("location:/twiliointest/headcoord/contactHeadCoords.php");
		break;
	
	// return to the main menu
	case 0:
		header("location:/twiliointest/phone/headcoord/");
		break;
}
?>