<?
// initialize services
require_once("../../../functions.php");
require_once('../../Services/Twilio.php');
$userService = new services\UserService();
$staffService = new services\StaffService();
$participantService = new services\ParticipantService();
 
// authorize twilio
setupTwilio();
//setupTwilioTest();

// get the sender info
$caller = $_SESSION['caller'];
if($caller->preferredName != null && $caller->preferredName != "") {
	$fromName = stripslashes($caller->preferredName);
} else {
	$fromName = stripslashes($caller->firstName);
}
$fromName .= " " . $caller->lastName;

// break up the message as necessary
$original = stripslashes($caller->messageBuilder);
$message = "Message from $fromName: $original (message powered by http://twil.io)";
$messageForSms = substr(chunk_split($message, 160, "<::>"), 0, -4);
$messagesToSend = explode("<::>", $messageForSms);

// get group information
$groupBreakdown = explode("::", $caller->groupNumber);
$staffs = $staffService->getStaffInGroup($groupBreakdown[0], $groupBreakdown[1]);

// call all of the VP Internals
$errors = array();
$errorCount = 0;
$successCount = 0;
$triedCount = 0;
foreach($staffs as $staff) {
	// check that this user isn't the current caller
	if($staff->classification == "Leader") {
		if($staff->phoneNumber != null && $staff->phoneNumber != "" && $staff->phoneNumber != $caller->phoneNumber) {
			$triedCount++;
			try {
				$numberFrom = nextNumber();
				foreach($messagesToSend as $messageToSend) {
					$message = $client->account->sms_messages->create($numberFrom, $staff->phoneNumber, $messageToSend, array("StatusCallback" => "http://orientation.ssmu.mcgill.ca/twilio/sms/textback.php"));
					$successCount++;
				}
			} catch (Exception $e) {
				$errors[] = $e;
				$errorCount++;
			}
		}
	}
}

// store that the main menu was the last command given and clear the message builder
$caller->lastText = "menu";
$participantService->saveParticipant($caller);

// send debugger email
mail("joshredel@mac.com", "Twilio: Participant to Leader Texting Info", "Tried: $triedCount\nSuccesses: $successCount\nFailuers: $errorCount\nThe following errors occured:\n " . implode("\n", $errors));

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>
<Response>
	<Sms>Your message has been sent to all of your leaders.</Sms>
</Response>