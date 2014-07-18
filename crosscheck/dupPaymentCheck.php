<?
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// define our participant service and get all participants
$participantService = new services\ParticipantService();
$participants = $participantService->getParticipants();

/*
we want to loop through each participant, then each event that participant is in, and each payment.
we should find a payment for all non discover mcgill event the student is registered for.
if we find multiple payments for an event for a single participant, then report it!
*/

// loop through each participant
$sum = 0;
foreach($participants as $participant) {
	unset($eventPaymentCount);
	
	// loop through each payment for that participant
	$markedPayment = false;
	foreach($participant->payments as $payment) {
		// load the payment information
		$payment->load();
		$payment->event->load();
		
		// increment the count of the number of payments for this event
		if(!isset($eventPaymentCount[$payment->event->id])) {
			// first payment for an event
			$eventPaymentCount[$payment->event->id] = 1;
		} else {
			// hit another payment for a single event... mark it!
			$markedPayment = true;
			break;
		}
	}
	
	// see if there was a marked payment
	if($markedPayment) {
		echo("Participant ID {$participant->id} has a duplicate payment<br />");
		$sum++;
	}
}

echo("<br />There were $sum occurences.");
?>