<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get the requested user
$id = $_GET['id'];
$participant = $participantService->getParticipant($id);

// redirect if the participant doesn't exist
if($participant == null) {
	redirect(".");
}

// redirect if they do not have permission to be here or the user is not in their event
if(!$currentRole->hasPermission(org\fos\Role::$VIEW_PARTICIPANTS) || 
   ($currentEvent != null && !inDoctrineArray($participant, $currentEvent->participants))) {
	// the user does not have permissions
	redirect("/admin/");
}

// see if they have asked to unregister a participant (for standard users)
if(isset($_GET['unregister']) && $_GET['unregister'] == 'true' && $currentRole->hasPermission(org\fos\Role::$EDIT_PARTICIPANTS)) {
	// remove the participant from this event
	// recreate the events collection, but without the unregistered event
	$newEvents = new Doctrine\Common\Collections\ArrayCollection();
	foreach($participant->events as $thisEvent) {
		// only keep this event if it's not the one we want to delete
		if($thisEvent->id != $currentEvent->id) {
			$newEvents[] = $thisEvent;
		}
	}
	
	// update their raw registration data
	// pull all of the events out of the session variable
	$rawEvents = explode(";", $participant->rawRegistrationData);
	
	// loop through each one to display it
	$eventsToKeep = array();
	foreach($rawEvents as $rawEvent) {
		// break it down again into an array of the ticket info for this event
		//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
		$pureEvent = explode(",", $rawEvent);
		
		// if this isn't the current event, then add it back to the array
		if($pureEvent[0] != $currentEvent->id) {
			// re-store it
			$eventsToKeep[] = $rawEvent;
		}
	}
	
	// implode the array into the pure raw registration data
	$rawRegistrationData = implode(";", $eventsToKeep);
	
	// save the participant with its new events
	$participant->events = $newEvents;
	$participant->rawRegistrationData = $rawRegistrationData;
	$participantService->saveParticipant($participant);
	
	redirect("/admin/participants/");
}

// see if they have asked to unregister a participant from an event (null-event/master user)
if(isset($_GET['unregisterMaster']) && $_GET['unregisterMaster'] == 'true' && $currentEvent == null) {
	// get the event we want to remove them from
	if(!isset($_GET['eventId'])) {
		redirect(".");
	}
	$eventId = $_GET['eventId'];
	
	// remove the participant from this event
	// recreate the events collection, but without the unregistered event
	$newEvents = new Doctrine\Common\Collections\ArrayCollection();
	foreach($participant->events as $thisEvent) {
		// only keep this event if it's not the one we want to delete
		if($thisEvent->id != $eventId) {
			$newEvents[] = $thisEvent;
		}
	}
	
	// update their raw registration data
	// pull all of the events out of the session variable
	$rawEvents = explode(";", $participant->rawRegistrationData);
	
	// loop through each one to display it
	$eventsToKeep = array();
	foreach($rawEvents as $rawEvent) {
		// break it down again into an array of the ticket info for this event
		//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
		$pureEvent = explode(",", $rawEvent);
		
		// if this isn't the current event, then add it back to the array
		if($pureEvent[0] != $eventId) {
			// re-store it
			$eventsToKeep[] = $rawEvent;
		}
	}
	
	// implode the array into the pure raw registration data
	$rawRegistrationData = implode(";", $eventsToKeep);
	
	// save the participant with its new events
	$participant->events = $newEvents;
	$participant->rawRegistrationData = $rawRegistrationData;
	$participantService->saveParticipant($participant);
	
	redirect("/admin/participants/profile.php?id=$id");
}

// see if they want to resend the registration link
if(isset($_GET['resend']) && $_GET['resend'] == 'true') {
	$message = "Hello " . getDisplayName($participant) . ", \n\nYou can use the link below to connect to your myWeek page\n\nhttp://orientation.ssmu.mcgill.ca/myweek/?passkey=$participant->registrationPassword\n\nYou may use this link to view your registration status and to register for more events.\n\nLooking forward to welcoming you to McGill in 2013,\n\nThe McGill Orientation Team";
	
	// send the message
	mail($participant->email, "[McGill Orientation Week] myWeek Link", wordwrap($message, 80), "From: McGill Orientation Admin <mcgillfroshadmin@ssmu.mcgill.ca>");
	
	// refresh just to make sure the message doesn't get double sent
	redirect("/admin/participants/profile.php?id=$id");
}

if($currentEvent != null) {
	// get their age for the event
	if(count($currentEvent->calendarEvents) > 0) {
		// sort the calendar events by start date
		function dateCompare($a, $b) { 
			if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
				return 0;
			}
			return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
		}
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
			$eventAge = "$futureAge (<b>underage</b>)";
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
	
	// loop through each of the participant's payments until we find one that matches this event
	$eventPayment = null;
	foreach($participant->payments as $payment) {
		$payment->event->load();
		if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
			// this payment is a payment for the current event
			$eventPayment = $payment;
		}
	}
	
	// check that we have payment information
	if($eventPayment != null) {
		$paymentRate = number_format($eventPayment->finalCost, 2) . "$";
		$paymentMethod = $eventPayment->method;
		
		// figure out the transaction status
		if($eventPayment->hasPaid) {
			$paymentStatus = "paid";
			
			// but see if there is a pending status somewhere in the status
			if(stripos($eventPayment->status, "pending") !== false){
				$paymentStatus = "<a onClick='alert(\"It is marked that this payment was completed but that the transaction was marked pending.  Please check your PayPal account to make sure you do not have any payments that you need to manually accept\")'>paid with a pending status (click for info)</a>";
			}
		} else {
			if($paymentMethod == "paypal") {
				if($eventPayment->status == null) {
					$paymentStatus = "unpaid (no payment has been attempted)";
				} else {
					$paymentStatus = "unpaid (" . $eventPayment->status . ")";
				}
			} else {
				$paymentStatus = "unpaid";
			}
		}
	} else {
		$paymentRate = "--";
		$paymentMethod = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (click to learn more about this issue)</a>";
		$paymentStatus = "--";
	}
}

// format name
$nameForDisplay = $participant->firstName . " ";
if(strlen($participant->preferredName)) {
	$nameForDisplay .= "(" . $participant->preferredName . ") ";
}
$nameForDisplay .= $participant->lastName;
if(strlen($participant->preferredPronoun)) {
	$nameForDisplay .= " (" . $participant->preferredPronoun . ")";
}
$nameForDisplay = toPrettyPrint($nameForDisplay);

// format the dietary needs
if(strlen($participant->dietaryRestrictions)) {
	$dietaryForDisplay = str_replace(",", ", ", $participant->dietaryRestrictions);
} else {
	$dietaryForDisplay = "None to note";
}

// a function to display "None" if the passed text is empty
function formatOptionalText($text) {
	if(strlen($text)) {
		return $text;
	} else {
		return "None to note";
	}
}

// determine if the event has custome fields
$hasCustomFields = false;
if($currentEvent != null && $currentEvent->customFields != null && strlen($currentEvent->customFields)) {
	$hasCustomFields = true;
}

// find the check in for this user
$currentCheckIn = null;
$checkInUser = null;
foreach($participant->checkIns as $checkIn) {
	if($checkIn->event->id == $currentEvent->id) {
		$currentCheckIn = $checkIn;
		$checkInUser = $userService->getUser($checkIn->userId);
		break;
	}
}

// get the last location
$lastLocation = "";
if(isset($_SESSION['lastParticipantLocation'])) {
	$lastLocation = $_SESSION['lastParticipantLocation'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../../css/layout.css" />
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
        <script src="js/IE8.js" type="text/javascript"></script>
    <![endif]-->

    <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" media="all" href="css/ie6.css"/>
    <![endif]-->
    <script type="text/javascript">
		function confirmUnregister() {
			var answer = window.confirm("Unregistering a participant cannot be undone.  Once you unregister a participant, you must also manually refund their payment.  Click 'OK' only if you wish to unregister the participant completely from this event.");
			if(answer) {
				return true;
			} else {
				return false;
			}
		}
	</script>
</head>
<body>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	   <p><a href="../logout.php">logout</a><br />
    	   <a href="../changepassword.php">change password</a></p>
    	</div>
    	<?
		$file = __FILE__;
		include("../includes/html/topNav.php");
		include("../includes/html/partNav.php");
		?>
    	<section id='content'>
    	   <article id='userProfile'>
           	   <a href="../participants/<?= $lastLocation ?>">Go Back</a>
               <br /><br />
               <header><h1><?= $nameForDisplay ?></h1></header>
               <?
               if($currentRole->hasPermission(org\fos\Role::$EDIT_PARTICIPANTS)) { ?>
			       <button class='edit' onClick="window.location = 'editProfile.php?id=<?= $participant->id ?>'">Edit Information</button>
                   <button class='edit' onClick="window.location = '?id=<?= $participant->id ?>&resend=true'">Resend Profile Link Email</button>
                   <?
                   if($currentEvent != null) { ?>
                   	<button class='edit' onClick="if(confirmUnregister()) { window.location = '?id=<?= $participant->id ?>&unregister=true'}">Unregister</button><br /><br />
                   <? }
				   if($currentEvent == null) { ?>
                   	<button class="edit" onClick="window.location = 'http://orientation.ssmu.mcgill.ca/myweek/?passkey=<?= $participant->registrationPassword ?>'">View Student's Calendar</button><br /><br />
                   <? }
			   } // end of check for edit ?>
               
               <h2>Main Information</h2>
               <? if($currentEvent != null) { ?>
               <p><b>Age for Event:&nbsp;</b><?= $eventAge ?></p>
               <? } ?>
               <!--<p><b>Group:&nbsp;</b>no group assigned</p>-->
               <p><b>Birthday:&nbsp;</b><?= formatSimpleDate($participant->dateOfBirth) ?></p>
    	       <p><b>Faculty:&nbsp;</b><?= convertCodeToFaculty($participant->faculty) ?></p>
               <p><b>Student ID:&nbsp;</b><?= $participant->studentId ?></p>
    	       <p><b>Shirt Size:&nbsp;</b><?= $participant->shirtSize ?></p>
    	       <p><b>Dietary Restrictions:&nbsp;</b><br /><?= $dietaryForDisplay ?></p>
    	       <p><b>Allergies:&nbsp;</b><br /><?= formatOptionalText($participant->allergies) ?></p>
               <p><b>Physical Needs:&nbsp;</b><br /><?= formatOptionalText($participant->physicalNeeds) ?></p>
               <br />
               
               <? if($currentEvent != null && $currentEvent->costs && count($currentEvent->costs) > 0) { ?>
               <!-- show payment information if there is an event -->
               <h2>Payment Information:</h2>
               <p><b>Rate:&nbsp;</b><?= $paymentRate ?></p>
               <p><b>Method:&nbsp;</b><?= $paymentMethod ?></p>
               <p><b>Paid?:&nbsp;</b><?= $paymentStatus ?></p>
               <br />
               <? } ?>
               
               <?
               if($participant->groupNumber != null) {
				   $groupBreakdown = explode("::", $participant->groupNumber);
				   $staffService = new services\StaffService();
				   $staffs = $staffService->getStaffInGroup($groupBreakdown[0], $groupBreakdown[1]);
				   echo("<h2>Group Information</h2>");
				   echo("<p><b>Group #:&nbsp;</b>" . $groupBreakdown[1] . "</p>");
				   echo("<p><b>Leaders:</b></p><ul>");
				   foreach($staffs as $staff) {
					    if($staff->phoneNumber != null && $staff->phoneNumber != "") {
							$phoneNumber = str_ireplace("+", "", $staff->phoneNumber);
							$phoneNumberText = " (<a href=\"tel:$phoneNumber\">$phoneNumber</a>)";
						} else {
							$phoneNumberText = "";
						}
						echo("<li>" . $staff->displayName . " " . $staff->lastName . "$phoneNumberText</li>");
				   }
				   echo("</ul>");
			   }
			   ?>
               
               <? if($currentCheckIn != null) { ?>
               <h2>Check In Information</h2>
               <p><b>Got Merch?&nbsp;</b><?= ($currentCheckIn->gotMerchandise ? "Yes" : "No") ?></p>
               <p><b>Bracelet Number:&nbsp;</b><?= $currentCheckIn->braceletNumber ?></p>
               <p><b>Check In Date/Time:&nbsp;</b><?= formatDateTime($currentCheckIn->checkInDate) ?></p>
               <p><b>Checked In By:&nbsp;</b><?= $checkInUser->username ?></p>
               <br />
               <? } elseif($currentEvent != null) { ?>
               <h2>Check In Information</h2>
               <p><b>This student has not yet been checked in.</b></p>
               <br />
               <? } ?>
               
               <h2>Contact &amp; Other Information</h2>
               <?
			   if($participant->phoneNumber != null && $participant->phoneNumber != "") {
					$phoneNumber = str_ireplace("+", "", $participant->phoneNumber);
					$phoneNumberText = "<a href=\"tel:$phoneNumber\">$phoneNumber</a>";
				} else {
					$phoneNumberText = "not provided";
				}
			   ?>
               <p><b>Phone:&nbsp;</b><?= $phoneNumberText ?></p>
    	       <p><b>E-Mail:&nbsp;</b><a href='mailto:<?= $participant->email ?>'><?= $participant->email ?></a></p>
    	       <p><b>Living Style:&nbsp;</b><?= strtoproper(convertCodeToLivingStyle($participant->livingStyle)) ?></p>
    	       <p><b>Place of Origin:&nbsp;</b><?= strtoproper(convertCodeToOrigin($participant->placeOfOrigin)) ?></p>
    	       <p><b>Entering Year:&nbsp;</b><?= $participant->enteringYear ?></p>
    	       <p><b>Registration Date:&nbsp;</b><?= formatDateTime($participant->registrationDate) ?></p>
               <?
               if($hasCustomFields) {
                   // now go through the users existing questions and keep any unrelated to this event
				   if($participant->customFieldAnswers != null && strlen($participant->customFieldAnswers)) {
					   $rawAnswers = explode("<:;:>", $participant->customFieldAnswers);
					   foreach($rawAnswers as $existingAnswer) {
						   if($existingAnswer != "") {
							   $answerFields = explode("<::>", $existingAnswer);
							   $answerEventId = (int)$answerFields[0];
							   $answerFieldName = $answerFields[2];
							   $answerToQuestion = $answerFields[3];
							   
							   if($answerEventId == $currentEvent->id) {
								   echo("<p><b>" . $answerFieldName . "&nbsp;</b>" . $answerToQuestion . "</p>");
							   }
						   }
					   }
				   }
			   }
			   ?>
               <br />
               
               <?
			   /*
			   if($currentEvent != null) {
				   // prepare the custom fields
				   if($participant->customFieldAnswers != null && strlen($participant->customFieldAnswers) > 0) {
					   $customFields = substr($participant->customFieldAnswers, 0, strlen($participant->customFieldAnswers) - 5);
				   }
				   
				   // see if we have custom fields for this event
				   if (strpos($customFields, $currentEvent->id . '<::>') === FALSE) {
					   $hasFields = "false";
				   } else {
					   $hasFields = "true";
				   }
				   
				   // show the title only if we have info for this event
				   //if(!(strstr($participant->customFieldAnswers, $currentEvent->id . "<::>") == false)) {
				   if($hasFields) {
					   echo("<h2>Event Specific Information</h2>");
				   }
				   
				   // show custom fields
				   $customFields = explode("<:;:>", $participant->customFieldAnswers);
				   foreach($customFields as $customField) {
					   // get the information for this field
					   $pair = explode("<::>", $customField);
					   
					   // see if it is for the current event
					   if((int)$pair[0] === $currentEvent->id) {
						   // it matches, so show the field and its value
						   $fieldName = ucwords($pair[1]);
						   $fieldValue = ($pair[2] == "" ? "not provided" : $pair[2]);
						   echo("<p><b>$fieldName:&nbsp;</b>$fieldValue</p>");
					   }
				   }
				   
				   // add the end padding if necessary
				   if($hasFields) {
					   echo("<br />");
				   }
			   }
			   */
			   ?>
               
               <h2>Registered Events</h2>
               <?
			   // start the list if needed
			   if(count($participant->events) > 0) {
				   echo("<ul>");
			   }
			   
				foreach($participant->events as $registeredEvent) {
					// create the printable payment info
					if($currentEvent != null) {
						$paymentInfo = "";
					} else {
						// loop through each of the participant's payments until we find one that matches this event
						if($registeredEvent->costs != null && count($registeredEvent->costs) > 0) {
							$eventPayment = null;
							$totalCost = 0;
							foreach($participant->payments as $payment) {
								$payment->event->load();
								if($payment->event->id == $registeredEvent->id) {
									// this payment is a payment for the current event
									$eventPayment = $payment;
									$totalCost += $payment->finalCost;
								}
							}
							
							// check that we have payment information
							if($eventPayment != null) {
								$paymentInfo = "$" . number_format($totalCost, 2);
								$paymentInfo .= " (" . $eventPayment->method;
								
								// figure out the transaction status
								if($eventPayment->hasPaid) {
									// but see if there is a pending status somewhere in the status
									if(stripos($eventPayment->status, "pending") !== false){
										$paymentInfo .= "<a onClick='alert(\"It is marked that this payment was completed but that the transaction was marked pending.  Please check your PayPal account to make sure you do not have any payments that you need to manually accept\")'>paid with a pending status (click for info)</a>";
									} else {
										$paymentInfo .= " - paid";
									}
								} else {
									if($eventPayment->method == "paypal") {
										if($eventPayment->status == null) {
											$paymentInfo .= " - unpaid (no payment has been attempted)";
										} else {
											$paymentInfo .= " - unpaid (" . $eventPayment->status . ")";
										}
									} else {
										$paymentInfo .= " - unpaid";
									}
								}
								$paymentInfo .= ")";
							} else {
								$paymentInfo = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (click to learn more about this issue)</a>";
							}
						} else {
							$paymentInfo = "Free event";
						}
					}
					
					// create a link for them to unregister the participant from this event
					if($currentEvent == null) {
						//$unregisterLink = "(<a href='?id=$id&unregisterMaster=true&eventId={$registeredEvent->id}'>unregister</a>)";
						$unregisterLink = "<button class=\"edit\" onClick=\"if(confirmUnregister()) { window.location = '?id=$id&unregisterMaster=true&eventId=$registeredEvent->id' }\">Unregister</button>";
						
						// print the info
				    	echo("<li><strong>" . toPrettyPrint($registeredEvent->eventName) . "</strong><br />$paymentInfo $unregisterLink</li>");
					} else {
						// print the info
				    	echo("<li><strong>" . toPrettyPrint($registeredEvent->eventName) . "</strong></li>");
					}
			   }
			   
			   // close the list if needed
			   if(count($participant->events) > 0) {
				   echo("</ul>");
			   }
			   ?>
               <br />
    	   </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>