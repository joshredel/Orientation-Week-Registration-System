<?
// handle posted checkin data
require("../../functions.php");

checkForSession();
$paymentService = new services\PaymentService();

// make sure all posted data is valid, else redirect to checkin.php
if (!isset($_POST['passkey'])) {
	$message = "No changes have been made, some required data was not posted.";
	redirect("/admin/participants/checkin.php?error=$message");
}

// check for valid student
$participant = $participantService->getParticipantByRegistrationPassword($_POST['passkey']);
if ($participant == null) {
	$message = "No changes have been made, invalid participant posted.";
	redirect("/admin/participants/checkin.php?error=$message");
}

// check for a valid user /w permissions
if (!$currentRole->hasPermission(org\fos\Role::$CHECK_IN_PARTICIPANTS)) {
	$message = "No changes have been made, invalid user permissions.";
	redirect("/admin/participants/checkin.php?error=$message");
}

// get today
$today = new DateTime("now", new DateTimeZone("America/Montreal"));

// see if the event has optional costs
$hasOptionalCosts = false;
$totalOrganizerBaseCost = 0;
$totalOrganizerOptionCost = 0;
foreach($currentEvent->costs as $cost) {
	if(!$cost->isAdminFee) {
		$totalOrganizerOptionCost += $cost->amount;
		if($cost->isOptional) {
			$hasOptionalCosts = true;
		} else {
			$totalOrganizerBaseCost += $cost->amount;
		}
	}
}

// first see if the participant is registered for the event
if(isset($_POST['register']) && $_POST['register'] == "yes") {
	// register them for the event
	$participant->events[] = $currentEvent;
	
	// see if they took the option
	$tookOption = "false";
	if(isset($_POST['option']) && $_POST['option']) {
		$tookOption = "true";
	}
	
	// get rid of the dolar sign
	$finalRate = str_ireplace("$", "", $_POST['totalRate']);
	
	// update their raw registration data
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$rawInfo = array();
	$rawInfo[] = $currentEvent->id;
	$rawInfo[] = stripslashes($currentEvent->eventName);
	$rawInfo[] = $tookOption;
	$rawInfo[] = $finalRate;
	$rawInfo[] = "true";
	$rawInfo[] = $currentEvent->category;
	$additionalRawInfo = implode(",", $rawInfo);
	
	// add it to their existing data
	$newRawData = $participant->rawRegistrationData . ";" . $additionalRawInfo;
	$participant->rawRegistrationData = $newRawData;
	
	$participantService->saveParticipant($participant);
}

// check for a payment object to modify
// loop through each of the participant's payments until we find one that matches this event
$eventPayment = null;
foreach($participant->payments as $payment) {
	$payment->event->load();
	if($payment->event->id == $currentEvent->id) {
		// this payment is a payment for the current event
		$eventPayment = $payment;
		
		// if the payment has been paid, then continue... otherwise allow it to keep searching in case a later payment was actually made
		if($payment->hasPaid) {
			continue;
		}
		
		// handle cash payments for payments planned to be paid in paypal (do not modify/remove paykeys)
		// if this is being marked paid for the first time, make sure payment method is in person and set date to now
		if (isset($_POST['paid']) && $eventPayment->hasPaid == 0) {
			$eventPayment->method = $_POST['paymentMethod']; //"in Person";
			$eventPayment->hasPaid = $_POST['paid'];
			$eventPayment->paymentDate = $today;
		}
		
		// if the paid marker is not marked, mark them as unpaid (only if not paying via paypal)
		if (!isset($_POST['paid']) && $eventPayment->method == "inperson") {
			$eventPayment->hasPaid = 0;
			$eventPayment->paymentDate = NULL;
		}
		
		// see if this payment was for an option
		if($hasOptionalCosts && !$eventPayment->isAdminPayment) {
			//CASES:
			// 1. Had no option, now choosing option.
			// 2. Had no option, not choosing option.
			// 3. Had option, keeping option.
			// 4. Had option, dropping option
			if($eventPayment->finalCost > $totalOrganizerBaseCost) {
				// they had opted to pay for the option
				if(!(isset($_POST['option']) && $_POST['option'])) {
					// they no longer wish to pay for the option
					$eventPayment->finalCost = $totalOrganizerBaseCost;
				} elseif(isset($_POST['option']) && $_POST['option'] && $_POST['paymentMethod'] == "inperson") {
					$eventPayment->finalCost = $totalOrganizerOptionCost;
				}
			} else {
				// they had not yet opted for the option
				if(isset($_POST['option']) && $_POST['option']) {
					// they no longer wish to pay for the option
					$eventPayment->finalCost = $totalOrganizerOptionCost;
				}
			}
		}
		
		// save the payment
		$paymentService->savePayment($eventPayment);
	}
}

// if we have not found a payment, redirect
if ($eventPayment == null) {
	// get the costs for the event
	$eventOrganizerTotalCost = 0;
	$adminTotalCost = 0;
	$adminEventId = -1;
	foreach($currentEvent->costs as $cost) {
		// see if the cost is optional
		if($cost->isOptional) {
			if(isset($_POST['option']) && $_POST['option']) {
				// the event is optional AND the user wanted to sign up for the option
				$eventOrganizerTotalCost += $cost->amount;
			}
		} else {
			// see if it is an admin fee
			if($cost->isAdminFee) {
				// it is an admin fee, so count it as such
				$adminTotalCost += $cost->amount;
				$adminEventId = $cost->adminEventId;
			}else {
				// it is not an admin fee, so send it to the event organizer
				$eventOrganizerTotalCost += $cost->amount;
			}
		}
	}
	
	// determine how the user will pay for this event
	$acceptedPaymentMethods = $currentEvent->acceptedPayments;
	$calculatedPaymentMethod = $_POST['paymentMethod'];
	
	$hasPaid = false;
	if(isset($_POST['paid']) && $_POST['paid']) {
		$hasPaid = true;
	}
	
	// create a payment for the event organizer
	$payment = new org\fos\Payment();
	$payment->method = $calculatedPaymentMethod;
	$payment->finalCost = $eventOrganizerTotalCost;
	$payment->hasPaid = $hasPaid;
	$payment->description = "Payment for " . $currentEvent->eventName . " (Organizer Fee). Registered " . $today->format('Y-m-d H:i:s') . ". Price $" . $eventOrganizerTotalCost . " of total $" . ($eventOrganizerTotalCost + $adminTotalCost);
	$payment->participant = $participant;
	$payment->event = $currentEvent;
	$payment->isAdminPayment = false;
	$paymentService->savePayment($payment);
	
	// if there was an admin cost, then create a payment for the admin
	if($adminTotalCost > 0) {
		$payment = new org\fos\Payment();
		$payment->method = $calculatedPaymentMethod;
		$payment->finalCost = $adminTotalCost;
		$payment->hasPaid = $hasPaid;
		$payment->description = "Payment for " . $currentEvent->eventName . " (Admin Fee). Registered " . $today->format('Y-m-d H:i:s') . ". Price $" . $adminTotalCost . " of total $" . ($eventOrganizerTotalCost + $adminTotalCost);
		$payment->participant = $participant;
		$payment->event = $currentEvent;
		$payment->isAdminPayment = true;
		$paymentService->savePayment($payment);
	}
}

$checkin = null;
//loop through participant's checkins for a match
foreach ($participant->checkIns as $thisCheckin) {
	if ($thisCheckin->event->id == $currentEvent->id) {
		$checkin = $thisCheckin;
		$checkin->event->load();
		// maybe in the future we can work with this
		//$checkin->user .= "," . $currentUser->id;
		break;
	}
}
// create the new checkin object if no checkin was found
$saveEvent = false;
if ($checkin == null) {
	$checkin = new org\fos\CheckIn();
	$checkin->userId = $currentUser->id;
	$checkin->event = $currentEvent;
	$saveEvent = true;
}

// Fill in fields
$checkin->checkInDate = $today;
$checkin->participant = $participant;
$checkin->gotMerchandise = (isset($_POST['merch']) ? 1 : 0);
$checkin->gotBracelet = 1;
// see if the bracelet number changed
$newBraceletNumber = (isset($_POST['braceletNumber']) ? $_POST['braceletNumber'] : null);
if($checkin->braceletNumber != null && $checkin->braceletNumber != $newBraceletNumber) {
	// store the old ID
	if($checkin->pastBraceletNumbers != null && $checkin->pastBraceletNumbers != "") {
		$oldBracelets = explode(",", $checkin->pastBraceletNumbers);
	} else {
		$oldBracelets = array();
	}
	$oldBracelets[] = $checkin->braceletNumber;
	$checkin->pastBraceletNumbers = implode(",", $oldBracelets);
}
// save the new bracelet
$checkin->braceletNumber = $newBraceletNumber;

// see if the user ID changes
if($currentUser->id != $checkin->userId) {
	// store the old ID
	if($checkin->pastUserIds != null && $checkin->pastUserIds != "") {
		$oldIds = explode(",", $checkin->pastUserIds);
	} else {
		$oldsIds = array();
	}
	$oldIds[] = $checkin->userId;
	$checkin->pastUserIds = implode(",", $oldIds);
	
	// save the new ID
	$checkin->userId = $currentUser->id;
}

// save the checkin
$checkInService->saveCheckIn($checkin, $saveEvent);

// store any additional participant information
// get the phone number
$rawPhone = $_POST['phone'];

// strip any of the accepted non-numeric characters (space, -, .)
$rawPhone = str_ireplace("-", "", $rawPhone);
$rawPhone = str_ireplace(" ", "", $rawPhone);
$rawPhone = str_ireplace(".", "", $rawPhone);

// if it is only 10 digits, add the +1
if(strlen($rawPhone) == 10) {
	$rawPhone = "+1" . $rawPhone;
}

// and make sure it has a + at the beginning
if(strlen($rawPhone) > 0 && $rawPhone[0] != '+') {
	$rawPhone = "+" . $rawPhone;
}

// store it
$participant->phoneNumber = $rawPhone;

// save any custom fields they entered
if($currentEvent->customFields != null && strlen($currentEvent->customFields)) {
	// break apart the different questions and get the answers for it
	$questions = explode("<:;:>", $currentEvent->customFields);
	$responses = array();
	foreach($questions as $question) {
		// get the details about this question
		$customInfo = explode("<::>", $question);
		$fieldId = $customInfo[0];
		$fieldName = $customInfo[1];
		$fieldDescription = $customInfo[2];
		$fieldType = $customInfo[3];
		$fieldOptions = explode(",", $customInfo[4]);
		$fieldAdminOnly = $customInfo[5];
		
		// get the answer from POST
		$answerDOMField = "customField" . $fieldId;
		$answerToQuestion = $_POST[$answerDOMField];
		
		// compile it into a storage string
		$answerArray = array();
		$answerArray[] = $currentEvent->id;
		$answerArray[] = $fieldId;
		$answerArray[] = $fieldName;
		$answerArray[] = $answerToQuestion;
		$answerString = implode("<::>", $answerArray);
		
		// add it to our array of responses
		$responses[] = $answerString;
	}
	
	
	// now go through the users existing questions and keep any unrelated to this event
	$rawAnswers = explode("<:;:>", $participant->customFieldAnswers);
	if($rawAnswers != null && count($rawAnswers) > 0) {
		foreach($rawAnswers as $existingAnswer) {
			$answerFields = explode("<::>", $existingAnswer);
			$answerEventId = (int)$answerFields[0];
			
			if($answerEventId != $currentEvent->id) {
				$responses[] = $existingAnswer;
			}
		}
	}
	
	// compile the final database string and store
	$databaseString = implode("<:;:>", $responses);
	$participant->customFieldAnswers = $databaseString;
}

// save any changes we've made to the participant
$participantService->saveParticipant($participant);

$_SESSION['checkInSuccess'] = "Participant with ID " . $participant->studentId . " has been successfully checked in!";

// go back once we're done!
if(isset($_POST['originator'])) {
	redirect("/admin/participants/" . $_POST['originator']);
} else {
	redirect("/admin/participants/checkin.php");
}

?>