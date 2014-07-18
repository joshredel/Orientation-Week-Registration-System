<?
require("../../functions.php");
// whether or not we are in sandbox mode for PayPal interactions
$sandbox = false;
$developerEmail = "joshredel@mac.com";

// instantiate the payment service
$paymentService = new services\PaymentService();

// see if we have header data in the raw post
if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
	// before doing anything else, save the raw data
	saveRawData($GLOBALS['HTTP_RAW_POST_DATA']);
	
	// get the request and format it for reading here
	$request = html_entity_decode($GLOBALS['HTTP_RAW_POST_DATA']);
	$request = str_replace('%5B', '[', $request);
	$request = str_replace('%5D', ']', $request);
	
	// set the url to send the replica data back to
	if($sandbox) {
		$paypal_validate_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'.$request;
	} else {
		$paypal_validate_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_notify-validate&'.$request;
	}
	
	// send the exact data back to PayPal for verification and store the response 
	$paypal_response = file_get_contents($paypal_validate_url);
	
	// check the response that we get back from paypal
	if(strcmp($paypal_response, "VERIFIED") == 0) {
		// paypal has confirmed our IPN response and we can continue to process payments
		// get all paypal posted items (ie: $post_array['transaction'][0][attribute])
		$paypal_array = decodePayPalIPN(file_get_contents("php://input"));
		
		// match transaction status to a payment and mark it accordingly
		// loop through all transactions that have been sent in this IPN 
		foreach($paypal_array['transaction'] as $paypal_transaction) {
			// loop through all Payment objects in FOS with matching paykey
			foreach($paymentService->getPaymentsContainingPayKey($paypal_array['pay_key']) as $payment) {
				// check for a matching business email (to eliminate the case in which their might be duplicate paykeys for separate payments)
				//if (strcmp(strtolower($payment->event->paypalBusiness), strtolower($paypal_transaction['receiver'])) == 0 ) {
				// check for a matching dollar amount (since the business will be the same)
				if((int)($payment->finalCost) == (int)(removeCurrency($paypal_transaction['amount']))) {
					// we have a match
					// check to see if the payment was completed
					if (strcmp(strtolower($paypal_array['status']), "completed") == 0) {
						// the status of the transaction is completed
						// check to see if the payment we found was already marked as paid
						if ($payment->hasPaid == 1) {
							// it was, and this should NOT normally happen, so send an email to the organizers
							$message = "We have noted that it is possible that a payment was made twice. Please look into this. The payment in question is for the following:\n\nEvent: " . $payment->event->eventName . "\nParticipant: " . $payment->participant->firstName . " " . $payment->participant->lastName . "\nStudent ID: " . $payment->participant->studentId . "\nE-Mail: " . $payment->participant->email . "\n\n This IS NOT necessarily a duplicate payment! This is an automated check that found a payment that may have been made twice. Please look into this for your records.\n\nBest,\n\n- The Orientation Website" ;
							mail($payment->event->paypalBusiness, "[Orientation Website] Possible Duplicate Payment", $message, "From: McGill Frosh Website <orientation@ssmu.mcgill.ca>");
						}
						
						// mark the payment as paid, save the status
						$payment->hasPaid = 1;
						$payment->status = $paypal_array['status'] . " (transaction: " . $paypal_transaction['status'] . ")";
						
						// if there is no paymentDate, create one
						if ($payment->paymentDate == null) {
							$payment->paymentDate = new DateTime(NULL, new DateTimeZone("America/Montreal"));
						}
					// Case 2: payment status is not Complete	
					} else {
						$payment->status = $paypal_array['status'] . " (transaction: " . $paypal_transaction['status'] . ")";
						$payment->hasPaid = 0;
						$payment->paymentDate = null;
					}
				
					// save the payment
					$paymentService->savePayment($payment);
				} // end check for matching business
			} // end foreach of all payments with matching paykey
		} // end foreach of all IPN transactions
	} else {
		// paypal send back a response saying our request was not verified
		mail($developerEmail, "[IPN Processing] IPN: Invalid", "Response was: \n $paypal_response");
	}
} else {
	// if nothing is posted in the header, email!
	//mail($developerEmail, "[IPN Processing] No header", "Nothing posted in the header");
	saveRawData("IPN triggered; nothing posted in header");
}


// function to decode the IPN response
function decodePayPalIPN($raw_post) {
    if (empty($raw_post)) {
        mail($developerEmail, "[IPN] Array Decode Empty", "Tried to decode raw post data but the input was empty.");
        return array();
    } # else:
    $post = array();
    $pairs = explode('&', $raw_post);
    foreach ($pairs as $pair) {
        list($key, $value) = explode('=', $pair, 2);
        $key = urldecode($key);
        $value = urldecode($value);
        # This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
        preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
        switch (count($key_parts)) {
            case 4:
                # Original key format: somekey[x].property
                # Converting to $post[somekey][x][property]
                if (!isset($post[$key_parts[1]])) {
                    $post[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
                } else if (!isset($post[$key_parts[1]][$key_parts[2]])) {
                    $post[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
                } else {
                    $post[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                }
                break;
            case 3:
                # Original key format: somekey[x]
                # Converting to $post[somkey][x] 
                if (!isset($post[$key_parts[1]])) {
                    $post[$key_parts[1]] = array();
                }
                $post[$key_parts[1]][$key_parts[2]] = $value;
                break;
            default:
                # No special format
                $post[$key] = $value;
                break;
        }#switch
    }#foreach
    
    return $post;
}#decodePayPalIPN()

// stores the raw header data in the database for retriever later for debugging purposes
function saveRawData($data) {
	// add it to the database
	$host = "localhost"; 
	$user = "orientation2011"; 
	$pass = "regerd8"; 
	
	// connect to the database
	mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
	mysql_select_db("fos") or die("Could not connect to the FOS database.");
	
	// add the email
	$query = "INSERT INTO RawIPNData (RawData) VALUES(\"" . ((!get_magic_quotes_gpc()) ? addslashes($data) : $data) . "\")";
	$r = mysql_query($query) or die(mysql_error());
	
	// close the database
	mysql_close();
	
	if(!$r) {
		return false;
	} else {
		return true;
	}
}

// remove the currentcy from a paypal amount
function removeCurrency($fullAmount) {
	$parts = explode(" ", $fullAmount);
	return $parts[1];
}
?>