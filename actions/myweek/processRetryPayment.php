<?
require_once('../../functions.php');

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'retryPaymentDone') { 
	redirect("/");
}

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_POST['passkey']);
$event = $eventService->getEvent($_POST['eid']);

//redirect if the passkey doesn't match any participant or the eid doesn't match any events
if($participant == null || $event == null) {
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
	$API_UserName = "chrisphilippona_api1.gmail.com";
	$API_Password = "JL52MDUFHU3VTGSL";
	$API_Signature = "AFcWxV21C7fd0v3bYYYRCpSSRl31Ab0MTMlrC1LF2mmjk2qqMeoVSZVU";
	$API_AppID = "APP-3DX76164C5786354F";
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
	"feesPayer" => "EACHRECEIVER"
	
	/* Example payment recipients */
	//"receiverList.receiver(0).email" => "chrisp_1343151177_biz@gmail.com", //TODO
	//"receiverList.receiver(0).amount" => "40.0", //TODO
	//"receiverList.receiver(1).email" => "chrisp_1341287724_biz@gmail.com", //TODO
	//"receiverList.receiver(1).amount" => "876.0" //TODO
);

// process all of the payments we have found
foreach($participant->payments as $payment) {
	// check for a match
	if($payment->event->id == $event->id) {
		// we found a payment for this event
		// change the payment type as appropriate
		$payment->method = $paymentMethod;
		
		// store the payment accordingly
		if($paymentMethod == "paypal") {
			// determine the buyer for this payment
			if($payment->isAdminPayment) {
				// add to the admin totals
				// get the admin event by working up and then down the child tree
				foreach($event->costs as $cost) {
					if($cost->isAdminFee) {
						$adminEvent = $eventService->getEvent($cost->adminEventId);
						break;
					}
				}
				
				// add the paypal information for payment
				$businessTotals[$adminEvent->paypalBusiness] += 0 + $payment->finalCost;
			} else {
				// add the paypal information for payment
				$businessTotals[$payment->event->paypalBusiness] += 0 + $payment->finalCost;
			}
			
			// store it in new paypal payments
			$payPalPayments[] = $payment;
		} else {
			// store it in inperson payments
			$inpersonPayments[] = $payment;
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
				if(strlen($payPalPayment->payKey)) {
					// append this payket to the existing ones
					$payPalPayment->payKey .= "," . $kArray["payKey"];
				} else {
					// this is the first paykey, so simply store it
					$payPalPayment->payKey = $kArray["payKey"];
				}
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