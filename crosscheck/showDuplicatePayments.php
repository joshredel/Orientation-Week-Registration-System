<?
/**
 * This tool will go through all participants and check to see if they have multiple payments for the same event.
 * For some reason, some people have multiple payments/pairs of payments for a single event in the system.
 * The system is designed to only ever have one pair of payments for each event for every participant registered, 
 * event if they retry payment.  This is so we can make sure that we track PayPal payments properly.
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
$totalDuplicatePayments = 0;

// loop through all participants
$participants = $participantService->getParticipants();
foreach($participants as $participant) {
	// make an array of payments with event ID as keys
	$paymentCounts = array();
	
	// loop through each of their payments
	foreach($participant->payments as $payment) {
		// only count non-admin payments
		if(!$payment->isAdminPayment) {
			$payment->event->load();
			if(!isset($paymentCounts[$payment->event->id])) {
				$paymentCounts[$payment->event->id] = 1;
			} else {
				echo("[Participant " . $participant->id . "] has a duplicate payment for [Event " . $payment->event->id . "]<br />");
				$totalDuplicatePayments++;
			}
		}
	}
}

echo("There are $totalDuplicatePayments duplicate payments.<br /><br />");
?>