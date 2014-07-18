<?
require_once('../../functions.php');

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'selectPaymentDone') { 
	redirect("/");
}

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_POST['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// store the payment method
$paymentMethod = $_POST['methodSelect'];

// set today
$today = new DateTime(null, new DateTimeZone("America/Montreal"));

$sandbox = false;
$developerEmail = "joshredel@mac.com";

//turn php errors on
if($sandbox) {
	ini_set("track_errors", true);
}

// set the api information
if($sandbox) {
	//set PayPal Endpoint to sandbox
	$url = trim("https://svcs.sandbox.paypal.com/AdaptivePayments/Pay");
	$API_UserName = "API_1341289497_biz_api1.gmail.com";
	$API_Password = "1341289528";
	$API_Signature = "A.xapJUPNE9g.Rz28wkJH8lV6RI0A5cPUeWIql9z29r2fOz.wj1ag8c0";
	$API_AppID = "APP-80W284485P519543T";
} else {
	// Set PayPal Endpoint to LIVE
	$url = trim("https://svcs.paypal.com/AdaptivePayments/Pay");
	/*
	// Chris's old information
	$API_UserName = "chrisphilippona_api1.gmail.com";
	$API_Password = "JL52MDUFHU3VTGSL";
	$API_Signature = "AFcWxV21C7fd0v3bYYYRCpSSRl31Ab0MTMlrC1LF2mmjk2qqMeoVSZVU";
	$API_AppID = "APP-3DX76164C5786354F";
	*/
	$API_UserName = "redel.joshua_api1.gmail.com";
	$API_Password = "V3H5FWWZ82EAUKWY";
	$API_Signature = "An5ns1Kso7MWUdW4ErQKJJJ4qi4-A-yszXAO1wXH-tsbazodWCyKW3nv";
	$API_AppID = "APP-9B580010E31236245";
}

// Request/Response Formats
$API_RequestFormat = "NV";
$API_ResponseFormat = "NV";

//Create request payload with minimum required parameters
$bodyparams = array (	
	"requestEnvelope.errorLanguage" => "en_US",
	"actionType" => "PAY",
	"currencyCode" => "CAD",
	"requestEnvelope.errorLanguage" => "en_US",
	"cancelUrl" => "http://orientation.ssmu.mcgill.ca/myweek/complete.php?status=cancelled&passkey=" . $participant->registrationPassword,
	"returnUrl" => "http://orientation.ssmu.mcgill.ca/myweek/complete.php?status=completed&passkey=" . $participant->registrationPassword,
	"ipnNotificationUrl" => "http://orientation.ssmu.mcgill.ca/actions/registration/processPayPalIPN.php",
	"reverseAllParallelPaymentsOnError" => true,
	"feesPayer" => "EACHRECEIVER",
	
	/* Example payment recipients */
	//"receiverList.receiver(0).email" => "chrisp_1343151177_biz@gmail.com", //TODO
	//"receiverList.receiver(0).amount" => "40.0", //TODO
	//"receiverList.receiver(1).email" => "chrisp_1341287724_biz@gmail.com", //TODO
	//"receiverList.receiver(1).amount" => "876.0" //TODO
);

// pull all of the raw events from the user
$rawEvents = explode(";", $participant->rawRegistrationData);

// loop through each event we are to register
$inpersonPayments = array();
$payPalPayments = array();
$businessTotals = array();
foreach($rawEvents as $rawEvent) {
	// break it down again into an array of the ticket info for this event
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$pureEvent = explode(",", $rawEvent);
	
	// check to see if the participant aleady has a payment for this event
	// this is to double check that they are not on this page by accident
	$alreadyRegistered = false;
	foreach($participant->payments as $payment) {
		if($payment->event->id == (int)$pureEvent[0]) {
			// we have a payment, so ignore this event
			$alreadyRegistered = true;
		}
	}
	
	if(!$alreadyRegistered) {
		// ignore events with no cost
		if($pureEvent[3] != "0") {
			// there is a cost
			// get the event from the database
			$event = $eventService->getEvent($pureEvent[0]);
			
			// get the costs for the event
			$eventOrganizerTotalCost = 0;
			$adminTotalCost = 0;
			$adminEventId = -1;
			foreach($event->costs as $cost) {
				// see if the event is option
				if($cost->isOptional) {
					if($pureEvent[2] == "true") {
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
			
			// determine how the user will pay for this event based on their selection in the last step 
			// and the availalability of that action for this event (essentially: if the user wants to pay 
			// via PayPal for most of their events but has some that only take in person payments, then we will let 
			// them pay via PayPal for what they can and pay in person for the rest)
			$acceptedPaymentMethods = $event->acceptedPayments;
			$calculatedPaymentMethod = "";
			
			if($acceptedPaymentMethods == "paypal,inperson" || $acceptedPaymentMethods == "inperson,paypal" || $paymentMethod == $acceptedPaymentMethods) {
				// they can pay with whatever method they selected
				$calculatedPaymentMethod = $paymentMethod;
			} elseif($paymentMethod == "paypal" && $acceptedPaymentMethods == "inperson") {
				// they want to pay via paypal, but the event only allows payment in person
				// override their selection to mark this for payment in person (and it will not be added to the paypal queue)
				$calculatedPaymentMethod = "inperson";
			}
			//TODO - we might want to add the case where paymentMethod=inperson and acceptedMethods=paypal, but this is not a case that exists currently
			
			
			// create a payment for the event organizer
			$payment = new org\fos\Payment();
			$payment->method = $calculatedPaymentMethod;
			$payment->finalCost = $eventOrganizerTotalCost;
			$payment->hasPaid = 0;
			$payment->description = "Payment for " . $event->eventName . " (Organizer Fee). Registered " . $today->format('Y-m-d H:i:s') . ". Price $" . $eventOrganizerTotalCost . " of total $" . ($eventOrganizerTotalCost + $adminTotalCost);
			$payment->participant = $participant;
			$payment->event = $event;
			$payment->isAdminPayment = false;
			
			// store the payment accordingly
			if($calculatedPaymentMethod == "paypal") {
				// store it in new paypal payments
				$payPalPayments[] = $payment;
				
				// add the paypal information for payment
				//$bodyparams["receiverList.receiver(" . $receiverNumber . ").email"] = $payment->event->paypalBusiness;
				//$bodyparams["receiverList.receiver(" . $receiverNumber . ").amount"] = (string)$payment->finalCost;
				//$receiverNumber++;
				$businessTotals[$payment->event->paypalBusiness] += 0 + $payment->finalCost;
			} else {
				// store it in inperson payments
				$inpersonPayments[] = $payment;
			}
			
			// if there was an admin cost, then create a payment for the admin
			
			if($adminTotalCost > 0) {
				$payment = new org\fos\Payment();
				$payment->method = $calculatedPaymentMethod;
				$payment->finalCost = $adminTotalCost;
				$payment->hasPaid = 0;
				$payment->description = "Payment for " . $event->eventName . " (Admin Fee). Registered " . $today->format('Y-m-d H:i:s') . ". Price $" . $adminTotalCost . " of total $" . ($eventOrganizerTotalCost + $adminTotalCost);
				$payment->participant = $participant;
				$payment->event = $event;
				$payment->isAdminPayment = true;
				
				// store the payment accordingly
				if($calculatedPaymentMethod == "paypal") {
					// store it in new paypal payments
					$payPalPayments[] = $payment;
					
					// get the admin event
					$adminEvent = $eventService->getEvent($adminEventId);
					
					// add the paypal information for payment
					//$bodyparams["receiverList.receiver(" . $receiverNumber . ").email"] = $payment->event->paypalBusiness;
					//$bodyparams["receiverList.receiver(" . $receiverNumber . ").amount"] = (string)$adminTotalCost;
					//$receiverNumber++;
					$businessTotals[$adminEvent->paypalBusiness] += 0 + $adminTotalCost;
				} else {
					// store it in inperson payments
					$inpersonPayments[] = $payment;
				}
			}
		}
	}
}

// build up all of the amount to be paid to each group
$receiverNumber = 0;
foreach($businessTotals as $business => $amount) {
	$bodyparams["receiverList.receiver(" . $receiverNumber . ").email"] = $business;
	$bodyparams["receiverList.receiver(" . $receiverNumber . ").amount"] = (string)$amount;
	$receiverNumber++;
}
								
// convert payload array into url encoded query string
$body_data = http_build_query($bodyparams, "", chr(38));

// only proceed with paypal if we actually have one or more paypal payments
if(count($payPalPayments) > 0) {
	try {
		//create request and add headers
		$params = array("http" => array("method" => "POST",
										"content" => $body_data,
										"header" =>  "X-PAYPAL-SECURITY-USERID: " . $API_UserName . "\r\n" .
													 "X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature . "\r\n" .
													 "X-PAYPAL-SECURITY-PASSWORD: " . $API_Password . "\r\n" .
													 "X-PAYPAL-APPLICATION-ID: " . $API_AppID . "\r\n" .
													 "X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat . "\r\n" .
													 "X-PAYPAL-RESPONSE-DATA-FORMAT: " . $API_ResponseFormat . "\r\n"
										));
		
		//create stream context
		$ctx = stream_context_create($params);
		
		//open the stream and send request
		$fp = fopen($url, "r", false, $ctx);
		
		//get response
		$response = stream_get_contents($fp);
		
		//check to see if stream is open
		if ($response === false) {
			throw new Exception("php error message = " . "$php_errormsg");
			$errorMessage = "There was an error communicating with PayPal (stream not responding).  Your payments have not been completed.  <a href=index.php?passkey=" . $_POST['passkey'] . ">Back to myWeek.</a>";
			redirect("/myweek/complete.php?status=error&passkey=" . $participant->registrationPassword . "&error=" . urlencode($errorMessage));
		}
			   
		//close the stream
		fclose($fp);
		
		//parse the ap key from the response
		$keyArray = explode("&", $response);
		
		foreach ($keyArray as $rVal){
			list($qKey, $qVal) = explode ("=", $rVal);
				$kArray[$qKey] = $qVal;
		}
		
		// set url to send the user to pay for the transaction
		if($sandbox) {
			$payPalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=" . $kArray["payKey"];
		} else {
			$payPalURL = "https://www.paypal.com/webscr?cmd=_ap-payment&paykey=" . $kArray["payKey"];
		}
		
		//print the url to screen for testing purposes
		if($kArray["responseEnvelope.ack"] == "Success") {
			// the API call was a sucess
			//mail($developerEmail, "PAYMENTS", "Payment was a success");
			
			// store the paykey to each paypal payment and save it
			foreach($payPalPayments as $payPalPayment) {
				$payPalPayment->payKey =  $kArray["payKey"];
				$paymentService->savePayment($payPalPayment);
			}
			
			// save any other payments
			foreach($inpersonPayments as $inpersonPayment) {
				$paymentService->savePayment($inpersonPayment);
			}
			
			// take the user to paypal to complete the transaction
			redirect($payPalURL);
			exit();
		} else {
			// the API call was not successful
			//mail($developerEmail, "PAYMENTS", "Payment was NOT a success");
	
			echo 'ERROR Code: ' .  $kArray["error(0).errorId"] . " <br/>";
			echo 'ERROR Message: ' .  urldecode($kArray["error(0).message"]) . " <br/>";
			//mail($developerEmail, "PAYMENTS", "echoed");
	
			$errorMessage .= "There was an error communicating with PayPal. Your payments have not been completed (" . urldecode($kArray["error(0).message"]) . ") <a href=index.php?passkey=" . $_POST['passkey'] . ">Back to myWeek.</a>";
			redirect("/myweek/complete.php?status=error&passkey=" . $participant->registrationPassword . "&error=" . urlencode($errorMessage));
			//mail($developerEmail, "PAYMENTS", $errorMessage);
		}
	} catch(Exception $e) {
		echo "Message: ||" .$e->getMessage()."||";
		redirect("/myweek/complete.php?status=error&passkey=" . $participant->registrationPassword . "&error=Exception: " . urlencode($e->getMessage));
	}
} else {
	// there are no paypal payments
	// save any other payments
	foreach($inpersonPayments as $inpersonPayment) {
		$paymentService->savePayment($inpersonPayment);
	}
	
	// all done!
	redirect("/myweek/complete.php?status=completed&passkey=" . $participant->registrationPassword);
	exit();	
}


// we should NOT get here
$errorMessage = "Something went wrong! We made it to the end of the script and nothing has happened. You were not registered for any additional events.";
redirect("/myweek/complete.php?status=error&passkey=" . $participant->registrationPassword . "&error=" . urlencode($errorMessage));
exit();
?>