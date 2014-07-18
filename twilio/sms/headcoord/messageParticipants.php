<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
$staffService = new services\StaffService();
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the sender info
$caller = $_SESSION['caller'];
$fromName = $caller->displayName . " " . $caller->lastName;
$currentEvent = $eventService->getEvent($_SESSION['froshEventId']);

// break up the message as necessary
$original = stripslashes($caller->messageBuilder);
$message = "Message from coord $fromName: $original (message powered by http://twil.io)";
$messageForSms = substr(chunk_split($message, 160, "<::>"), 0, -4);
$messagesToSend = explode("<::>", $messageForSms);

// send them a text now just so we can start sending
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Sms>Your message is being sent to all of your froshies.  You will receive confirmation when complete.</Sms>
</Response>
<?

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($currentEvent->participants as $participant) {
	if($participant->phoneNumber != null && $participant->phoneNumber != "") {
		$triedCount++;
		try {
			$numberFrom = nextNumber();//"+15005550006"
			foreach($messagesToSend as $messageToSend) {
				$message = $client->account->sms_messages->create($numberFrom, $participant->phoneNumber, $messageToSend, array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
				$successCount++;
			}
		} catch (Exception $e) {
			$errors[] = $e;
			$errorCount++;
		}
	}
}

// message the sender to let them know it is complete
$triedCount++;
try {
	$numberFrom = "+15149000125";//"+15005550006";
	$message = $client->account->sms_messages->create($numberFrom, $caller->phoneNumber, "Your mass text to all froshies was sent successfully to $successCount people.", array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
	$successCount++;
} catch (Exception $e) {
	$errors[] = $e;
	$errorCount++;
}

// store that the main menu was the last command given and clear the message builder
$caller->lastText = "menu";
$userService->saveUser($caller);

// send debugger email
mail("joshredel@mac.com", "Twilio: Leader to Participant Texting Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nThe following errors occured:\n " . implode("\n", $errors));
?>