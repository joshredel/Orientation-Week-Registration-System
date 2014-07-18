<?
/**
 * This tool will go through all participants and check their payment status.
 * If there are no payments found for a participant with events that have a cost, it will ask them to choose a method of payment.
 * If the status of a payment for an event is "null", it will ask the participant to return to myWeek to try again.
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

// email send information
$totalSent = 0;
$errorCount = 0;

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
							// they marked to pay via paypal, but no payment has been attempted yet
							//SEND THEM AN EMAIL ABOUT HAVING NOT ATTEMPTED PAYMENT YET/PAYMENT TIMING OUT
							$message = "Dear " . getDisplayName($participant) . ",\n";
							$message .= "According to our records, you have selected to pay for an event via PayPal and have had your payment time out, closed a PayPal window, or simply did not complete your transaction with PayPal.  We have updated myWeek to now allow you to retry your payments.  Don't worry; even if you were not able to pay earlier, you were still registered in your event and your spot was reserved.\n\n";
							$message .= "So, please go to your myWeek page (link below) and retry your payment on PayPal by clicking the link you see under the 'Payment Status' header.  If you have any questions or problems, please do not hesitate to reply to this email.\n";
							$message .= "http://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\n";
							$message .= "Thank you kindly for your patience while we made this solution available.  See you in a week!\n";
							$message .= "Sincerely,\nMcGill Orientation Team";
						}
					}
				}
			} else {
				// there was no payment, meaning they never even chose a payment method
				//SEND THEM AN EMAIL ABOUT HAVING SELECTED A METHOD OF PAYMENT/EXITING BEFORE GETTING TO PAYPAL/ETC.
				$message = "Dear " . getDisplayName($participant) . ",\n";
				$message .= "According to our records, you have registered for an event that requires payment, but you have not seleceted how you want to pay (you ended registration before completing Step 5).  We have updated myWeek to now allow you to select a method of payment.  Don't worry; even if you were not able to pay earlier, you were still registered in your event and your spot was reserved.\n\n";
				$message .= "So, please go to your myWeek page (link below) and select a method of payment by clicking the link you see under the 'Payment Status' header.  If you have any questions or problems, please do not hesitate to reply to this email.\n";
				$message .= "http://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\n";
				$message .= "Thank you kindly for your patience while we made this solution available.  See you in a week!\n";
				$message .= "Sincerely,\nMcGill Orientation Team";
			}
			
			// send the email if appropriate
			if(strlen($message)) {
				$mailResult = mail($participant->email, "[McGill Orientation Week] Payment Fixes Available!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
		
				if($mailResult) {
					// increase our sent count
					$totalSent++;
				} else {
					// list errors
					$errorCount++;
					echo("Failed to be accepted for delivery: " . $participant->email . "<br />");
				}
				
				// no need to look at other events
				break;
			}
		}
	}
}

echo("A total of " . $totalSent	. " emails were sent and a total of " . $errorCount . " errors were encountered.");
?>