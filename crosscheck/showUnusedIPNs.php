<?
/**
 * This tool will go through all unpaid PayPal payments to see if they have a raw IPN entry.
 * It appears that occasionally the an IPN is not actually processing properly and thus not marking
 * the appropriate payment as paid.  This tool attempts to find Payments that have an RawIPN with matching PayPal paykeys.
 */

// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// database connection parameters
$host = "localhost"; 
$user = "orientation2011"; 
$pass = "regerd8"; 

// connect to the database
mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
mysql_select_db("fos") or die("Could not connect to the FOS database.");

// initialize services
$paymentService = new services\PaymentService();

// issue counters
$unpaidPayPalPayments = 0;
$unusedIPNs = 0;
$nowPaid = 0;

// loop through all payments
$payments = $paymentService->getPayments();
foreach($payments as $payment) {
	if(!$payment->hasPaid && $payment->status == null && $payment->method == "paypal") {
		$unpaidPayPalPayments++;
		
		// now breakapart the PayKey
		$paykeys = explode(",", $payment->payKey);
		
		// search for IPNs containing these paykeys
		foreach($paykeys as $paykey) {
			$query = "SELECT * FROM  `RawIPNData` WHERE `RawData` LIKE '%" . $paykey . "%'";
			$result = mysql_query($query) or die(mysql_error());
			while($row = mysql_fetch_array($result)) {
				// we found an IPN that matches the paykey
				// let the user now
				echo("<br /><br />[RawIPN " . $row['DataID'] . "] has a paykey matching [Payment " . $payment->id . "]<br />");
				
				$paypal_array = decodePayPalIPN($row['RawData']);
		
				// match transaction status to a payment and mark it accordingly
				// loop through all transactions that have been sent in this IPN 
				foreach($paypal_array['transaction'] as $paypal_transaction) {
					// check for a matching dollar amount (since the business will be the same)
					if((int)($payment->finalCost) == (int)(removeCurrency($paypal_transaction['amount']))) {
						// we have a match
						// check to see if the payment was completed
						if (strcmp(strtolower($paypal_array['status']), "completed") == 0) {
							// the status of the transaction is completed
							// check to see if the payment we found was already marked as paid
							if ($payment->hasPaid == 1) {
								// it was, and this should NOT normally happen, so send an email to the organizers
								echo("----DUPLICATE FOUND----<br />");
							}
							
							echo("[Payment " . $payment->id . "] marked as paid<br />");
							
							// mark the payment as paid, save the status
							$payment->hasPaid = 1;
							$payment->status = $paypal_array['status'] . " (transaction: " . $paypal_transaction['status'] . ")";
							
							// if there is no paymentDate, create one
							if ($payment->paymentDate == null) {
								$payment->paymentDate = new DateTime(NULL, new DateTimeZone("America/Montreal"));
							}
							
							// count it
							$nowPaid++;
						// Case 2: payment status is not Complete	
						} else {
							echo("[Payment " . $payment->id . "] NOT PAID<br />");
							
							$payment->status = $paypal_array['status'] . " (transaction: " . $paypal_transaction['status'] . ")";
							$payment->hasPaid = 0;
							$payment->paymentDate = null;
						}
					
						// save the payment
						$paymentService->savePayment($payment);
					} // end check for matching transaction amount
				} // end foreach of all IPN transactions
				
				
				// count it
				$unusedIPNs++;
			}
		}
	}
}

// close the database
mysql_close();

echo("<br />We found $unpaidPayPalPayments unpaid payments with a null status marked for payment via PayPal.<br />");
echo("Of those, we believe $unusedIPNs unused IPNs exist that mark payment for one of them.<br />");
echo("Finally, $nowPaid payments have now been marked as paid.");

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

// remove the currentcy from a paypal amount
function removeCurrency($fullAmount) {
	$parts = explode(" ", $fullAmount);
	return $parts[1];
}
?>