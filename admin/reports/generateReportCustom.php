<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check that they can view reoports
// redirect if they do not have permission to be here or the user is not in their event
if(!$currentRole->hasPermission(org\fos\Role::$VIEW_REPORTS) || !isset($_GET['type'])) {
	// the user does not have permissions
	redirect(".");
}

// store the report type
$reportType = $_GET['type'];

/*
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=Orientation Week Report - " . ucwords($reportType) . ".csv");
header("Pragma: no-cache");
header("Expires: 0");
*/
// define our participant service and get all participants
$participants = $participants = $currentEvent->participants;

function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// see what kind of report was requested
if($reportType == "full") {
	/////////
	// FULL REPORT
	/////////
	// start preparing the header
	$header = array("Participant ID", "Last Name", "First Name", "Preferred Name", "Preferred Pronoun", "Student ID", "Email", "Faculty", "Date of Birth", "Shirt Size", "Dietary Restrictions", "Allergies", "Physical Needs", "Place of Origin", "Entering Year", "Registration Date", "Living Style");
	
	// see if we have custom fields for the event
	// determine if the event has custome fields
	$hasCustomFields = false;
	if($currentEvent->customFields != null && strlen($currentEvent->customFields)) {
		$hasCustomFields = true;
		
		// get the names of the fields
		// break apart the different questions and get the answers for it
		$questions = explode("<:;:>", $currentEvent->customFields);
		$orderOfCustomHeaders = array();
		foreach($questions as $question) {
			// get the details about this question
			$customInfo = explode("<::>", $question);
			$fieldId = $customInfo[0];
			$fieldName = $customInfo[1];
			
			// create the header for it
			$header[] = $fieldName;
			$orderOfCustomHeaders[] = $fieldId;
		}
	}
	
	// print a header
	$outputData[] = $header;
	
	// loop through each participant and create a CSV file for it
	foreach($participants as $participant) {
		// loop through each event that participant is registered for
		// prepare the data to add
		$participantData = array($participant->id, toCellPrint($participant->lastName), toCellPrint($participant->firstName), toCellPrint($participant->preferredName), toCellPrint($participant->preferredPronoun), $participant->studentId, $participant->email, convertCodeToFaculty($participant->faculty), formatSimpleDate($participant->dateOfBirth), $participant->shirtSize, $participant->dietaryRestrictions, $participant->allergies, $participant->physicalNeeds, ucwords(convertCodeToOrigin($participant->placeOfOrigin)), $participant->enteringYear, formatSimpleDate($participant->registrationDate), convertCodeToLivingStyle($participant->livingStyle));
		
		// get custom fields
		// prepare the custom fields
		if($participant->customFieldAnswers != null && strlen($participant->customFieldAnswers)) {
			// show custom fields
			$customFields = explode("<:;:>", $participant->customFieldAnswers);
			$answers = array();
			foreach($customFields as $customField) {
				// get the information for this field
				$pair = explode("<::>", $customField);
				
				// see if it is for the current event
				if((int)$pair[0] == $currentEvent->id) {
					// it matches, so show the field and its value
					$fieldValue = ($pair[3] == "" ? "not provided" : $pair[3]);
					$answers[$pair[1]] = $fieldValue;
				}
			}
			
			// add them to the participant data for this row
			foreach($orderOfCustomHeaders as $orderInfo) {
				$participantData[] = $answers[$orderInfo];
			}
		}
		
		// print the data
		$outputData[] = $participantData;
	}
} elseif($reportType == "health") {
	/////////
	// HEALTH REPORT
	/////////
	// print a header
	$outputData[] = array("Participant ID", "Last Name", "Preferred Name", "Student ID", "Dietary Restrictions", "Allergies", "Physical Needs");
	
	// loop through each participant and create a CSV file for it
	foreach($participants as $participant) {
		if(($participant->dietaryRestrictions != null && $participant->dietaryRestrictions != "") || ($participant->allergies != null && $participant->allergies != "") || ($participant->physicalNeeds != null && $participant->physicalNeeds != "")) {
			$outputData[] = array($participant->id, toCellPrint($participant->lastName), getDisplayName($participant), $participant->studentId, stripslashes($participant->dietaryRestrictions), stripslashes($participant->allergies), stripslashes($participant->physicalNeeds));
		}
	}
} elseif($reportType == "payment") {
	/////////
	// PAYMENT REPORT
	/////////
	// print a header
	$outputData[] = array("Participant ID", "Last Name", "Preferred Name", "Student ID", "Email", "Payment Method", "Payment Amount", "Payment Status");
	
	// loop through each participant and create a CSV file for it
	foreach($participants as $participant) {
		// loop through each of the participants payments until we find one that matches this event
		if($currentEvent != null) {
			$eventPayment = null;
			foreach($participant->payments as $payment) {
				if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
					// this payment is a payment for the current event
					$eventPayment = $payment;
				}
			}
		
			// check that we have payment information
			if($eventPayment != null) {
				$rate = "$" . $eventPayment->finalCost;
				$method = $eventPayment->method;
			} else {
				$rate = "--";
				$method = "no method selected";
			}
			
			// create the printable payment info
			if(count($currentEvent->costs) == 0) {
				$paymentInfo = "";
			} else {
				// figure out the transaction status
				if($eventPayment->hasPaid) {
					$paymentStatus = "paid";
					
					// but see if there is a pending status somewhere in the status
					if(stripos($eventPayment->status, "pending") !== false){
						$paymentStatus = "paid (" . $eventPayment->status . ")";
					}
				} else {
					if($method == "paypal") {
						if($eventPayment->status == null) {
							$paymentStatus = "unpaid";
						} else {
							$paymentStatus = "unpaid";
						}
					} else {
						$paymentStatus = "unpaid";
					}
				}
			}
		}
		
		// print the info
		$outputData[] = array($participant->id, toCellPrint($participant->lastName), getDisplayName($participant), $participant->studentId, $participant->email, $method, $rate, $paymentStatus);
	}
} elseif($reportType == "checkin") {
	/////////
	// CHECKIN REPORT
	/////////
	// print a header
	$outputData[] = array("Participant ID", "Last Name", "First Name", "Student ID", "Email", "Date Checked In", "Got Merch", "Got Bracelet", "Bracelet Number", "Age for Event", "Payment Method", "Payment Amount", "Payment Status", "Checked In By");
	
	// prefetch all users
	$users = $userService->getUsers();
	$usersById = array();
	foreach($users as $user) {
		$usersById[$user->id] = $user->displayName . " " . $user->lastName;
	}
	
	// loop through each participant and create a CSV file for it
	$bracelets = array();
	foreach($participants as $participant) {
		// loop through each of the participants payments until we find one that matches this event
		// find the checkin made for this participant
		$eventCheckIn = null;
		foreach($participant->checkIns as $checkIn) {
			if($checkIn->event->id == $currentEvent->id) {
				// this checkin is a checkin for the current event
				$eventCheckIn = $checkIn;
				
				// get some basic info
				if ($eventCheckIn->gotMerchandise == 1) {
					$merch = "YES";
				} else {
					$merch = "NO";
				}
				
				if ($eventCheckIn->gotBracelet == 1) {
					$bracelet = "YES";
				} else {
					$bracelet = "NO";
				}
				
				if($eventCheckIn->braceletNumber == null) {
					$braceletNumber = "";
				} else {
					$braceletNumber = $eventCheckIn->braceletNumber;
				}
				
				// get the user that checked in the participant
				$checkedInBy = $usersById[$checkIn->userId];
				
				// store the bracelet number
				$bracelets[(int)$braceletNumber] = "checked";
				
				break;
			}
		}
		
		// find the payment made for this event
		if($currentEvent != null) {
			$eventPayment = null;
			foreach($participant->payments as $payment) {
				if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
					// this payment is a payment for the current event
					$eventPayment = $payment;
				}
			}
		
			// check that we have payment information
			if($eventPayment != null) {
				$rate = "$" . $eventPayment->finalCost;
				$method = $eventPayment->method;
			} else {
				$rate = "--";
				$method = "no method selected";
			}
			
			// create the printable payment info
			if(count($currentEvent->costs) == 0) {
				$paymentInfo = "";
			} else {
				// figure out the transaction status
				if($eventPayment->hasPaid) {
					$paymentStatus = "paid";
					
					// but see if there is a pending status somewhere in the status
					if(stripos($eventPayment->status, "pending") !== false){
						$paymentStatus = "paid (" . $eventPayment->status . ")";
					}
				} else {
					if($method == "paypal") {
						if($eventPayment->status == null) {
							$paymentStatus = "unpaid";
						} else {
							$paymentStatus = "unpaid";
						}
					} else {
						$paymentStatus = "unpaid";
					}
				}
			}
		}
		
		// get their age for the event
		if(count($currentEvent->calendarEvents) > 0) {
			// sort the calendar events by start date
			$calendarEvents = $currentEvent->calendarEvents->toArray();
			usort($calendarEvents, 'dateCompare');
			
			$length = count($calendarEvents);
			$eventDate = $calendarEvents[0]->startTime->getTimestamp();
			$eventEndDate = $calendarEvents[$length - 1]->endTime->getTimestamp();
			$birthDate = $participant->dateOfBirth->getTimestamp();
			
			$eventMonth = date('n', $eventDate);
			$eventDay = date('j', $eventDate);
			$eventEndDay = date('j', $eventEndDate);
			$eventYear = date('Y', $eventDate);
			
			$birthMonth = date('n', $birthDate);
			$birthDay = date('j', $birthDate);
			$birthYear = date('Y', $birthDate);
			
			if(($eventMonth >= $birthMonth && $eventDay >= $birthDay) || ($eventMonth > $birthMonth)) {
				$futureAge = $eventYear - $birthYear;
			} else  {
				$futureAge = $eventYear - $birthYear - 1;
			}
			
			// see if they will be underage
			if($futureAge < 18) {
				// underage!
				$eventAge = "$futureAge (underage)";
			} else {
				// they will be of age
				$eventAge = $futureAge;
			}
			
			// now also see if their birthday is during the event
			if($futureAge == 17 && ($birthMonth == $eventMonth && $birthDay >= $eventDay && $birthDay <= $eventEndDay)) {
				$eventAge .= " (note: they become of-age during the event)";
			}
		} else {
			$eventAge = "unknown (event does not have a date)";
		}
		
		// print the info
		if($eventCheckIn != null) {
			$outputData[] = array($participant->id, toCellPrint($participant->lastName), getDisplayName($participant), $participant->studentId, $participant->email, formatIsoDateTime($checkIn->checkInDate), $merch, $bracelet, $braceletNumber, $eventAge, $method, $rate, $paymentStatus, $checkedInBy);
		} else {
			$outputData[] = array($participant->id, toCellPrint($participant->lastName), getDisplayName($participant), $participant->studentId, $participant->email, "", "", "", "", $eventAge, $method, $rate, $paymentStatus, "");
		}
	}
	
	// go through all bracelets
	for($i = 1; $i < 1200; $i++) {
		if(isset($bracelets[$i])) {
			echo($i . ",used\n");
		} else {
			echo($i . ",UNUSED\n");
		}
	}
	
	exit();
} elseif($reportType == "custom") {
	/////////
	// CUSTOM REPORT
	/////////
	// print a header
	$outputData[] = array("Participant ID", "Last Name", "First Name", "Student ID", "Faculty", "Event Name");
	
	// loop through each participant and create a CSV file for it
	foreach($participants as $participant) {
		$outputData[] = array($participant->id, toCellPrint($participant->lastName), getDisplayName($participant), $participant->studentId, $participant->faculty, $event->eventName);
	}
} else {
	// no matching report type given...
	redirect("/admin/reports/");
}

// create the CSV file
//outputCSV($outputData);

function outputCSV($data) {
    $outstream = fopen("php://output", 'w');
    function __outputCSV(&$vals, $key, $filehandler) {
        fputcsv($filehandler, $vals); // add parameters if you want
    }
    array_walk($data, '__outputCSV', $outstream);
    fclose($outstream);
}

function toCellPrint($string) {
	return utf8_decode(stripslashes($string));
}

function getDisplayName($participant) {
	if($participant->preferredName != null && strlen($participant->preferredName)) {
		return utf8_decode(stripslashes($participant->preferredName));
	} else {
		return utf8_decode(stripslashes($participant->firstName));
	}
}
?>