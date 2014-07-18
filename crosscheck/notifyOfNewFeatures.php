<?
/**
 * This tool send an email to all users with a desired message, generally used to notify of new features.
 */
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// initialize services
$participantService = new services\ParticipantService();

// payment status counters
$totalSent = 0;
$errorCount = 0;

// loop through all participants
$participants = $participantService->getParticipants();
foreach($participants as $participant) {
	if($participant->lastName != "Redel") {
		//continue;
	}
	// create the message
	$message = "Dear " . getDisplayName($participant) . ",\n\n";
	$message .= "We are happy to let you know that your myWeek account is now better (looking) than ever. myWeek now includes an interactive calendar of your week, as well as expanded information about your events. And stay tuned for even more fun features coming soon (but they’re a secret for now).\n\nCheck out your new myWeek here: http://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\nSome of your events may be offered at multiple times, so make sure to choose the offering you’d like to appear on your calendar. You may also want to unregister for events that conflict; click on the event and press the red unregister button to do so (function unavailable for Discover McGill and Froshes).\n\nAlso make sure to check your profile and email us if any of the information there is incorrect.\n\nHave fun building your calendar and we look forward to seeing you soon at McGill University!\n\nOh, and have you seen the new McGill Orientation Week 2013 welcome video? Watch it here: https://www.youtube.com/watch?v=h82BBbXWuRY. Spoiler alert: after watching it, you will be more excited for Orientation Week than you ever thought possible.\n\nSincerely,\nThe Orientation Week Team";
	
	$mailResult = mail($participant->email, "[McGill Orientation Week] Your myWeek Calendar is live!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
		
	if($mailResult) {
		// increase our sent count
		$totalSent++;
	} else {
		// list errors
		$errorCount++;
		echo("Failed to be accepted for delivery: " . $participant->email . "<br />");
	}
}

echo("A total of " . $totalSent	. " emails were sent and a total of " . $errorCount . " errors were encountered.");
?>