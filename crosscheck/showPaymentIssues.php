<?
/**
 * This tool will go through all participants and check their payment status.
 * It counts the following cases:
 * - If there are no payments found for a participant with events that have a cost
 * - If the status of a payment for an event is "null"
 * This was constructed after myWeek initially did not allow participants to retry payments or choose a payment form if they failed to do so during registration.
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
$totalNullStatusPayPal = 0;
$totalNoPayments = 0;

// loop through all participants
$participants = $participantService->getParticipants();
foreach($participants as $participant) {
	// loop through each registered event for the current participant
	foreach($participant->events as $registeredEvent) {
		// see if this event has a cost
		if(count($registeredEvent->costs) > 0) {
			// there is a cost, so we can keep processing this
			// loop through each of the participant's payments until we find one that matches this event
			$eventPayment = null;
			foreach($participant->payments as $payment) {
				$payment->event->load();
				if($payment->event->id == $registeredEvent->id && !$payment->isAdminPayment) {
					// this payment is a payment for the current event
					$eventPayment = $payment;
				}
			}
			
			// check that we have payment information
			$message = "";
			if($eventPayment != null) {
				$paymentMethod = $eventPayment->method;
				
				// figure out the transaction status
				if(!$eventPayment->hasPaid) {
					// a payment has not been marked, so let's figure out what's up
					if($paymentMethod == "paypal") {
						if($eventPayment->status == null) {
							// they marked to pay via paypal, but no payment has been attempted yet (null status payments)
							$totalNullStatusPayPal++;
						}
					}
				}
			} else {
				// there was no payment, meaning they never even chose a payment method
				$totalNoPayments++;
			}
		}
	}
}

echo("There are $totalNullStatusPayPal unpaid payments marked for payment via PayPal that have a null status.<br /><br />");
echo("There are $totalNoPayments participants that have an event requiring payment that have not yet selected a method of payment.");
?>