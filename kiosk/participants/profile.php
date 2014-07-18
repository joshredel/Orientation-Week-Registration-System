<?
require_once('../../functions.php');

// check for a session
checkForKioskSession();

// get the requested user
$id = $_GET['id'];
$participant = $participantService->getParticipant($id);

// redirect if the participant doesn't exist
if($participant == null) {
	redirect(".");
}

// redirect if they do not have permission to be here or the user is not in their event
if(!$currentKioskRole->hasPermission(org\fos\Role::$KIOSK_MODE)) {
	// the user does not have permissions
	redirect("/kiosk/");
}

// see if they want to resend the registration link
if(isset($_GET['resend']) && $_GET['resend'] == 'true') {
	$message = "Hello $participant->firstName, \n\nUsing the secure link below, you can sign up and pay for Orientation Week Events. ";
	$message .= "\n\nYou will also have the option of paying online with credit, debit, or PayPal or to pay in person right before your event.\n\n Here is your secure registration link: http://orientation.ssmu.mcgill.ca/orientation/actions/newerUpdateRegistrationAction.php?passkey=$participant->registrationPassword\n\nYou may use this link to view your registration status and to register for more events.\n\nLooking forward to welcoming you to McGill in 2012,\n\nThe McGill Orientation Team";
	
	// send the message
	mail($participant->email, "[McGill Orientation Week] Secure Registration Link", wordwrap($message, 80), "From: McGill Orientation Admin <mcgillfroshadmin@ssmu.mcgill.ca>");
	
	// refresh just to make sure the message doesn't get double sent
	redirect("/kiosk/participants/profile.php?id=$id");
}

// get their age for the event
if($currentKioskEvent->startDate != null && $currentKioskEvent->endDate != null) {
	$eventDate = $currentKioskEvent->startDate->getTimestamp();
	$birthDate = $participant->dateOfBirth->getTimestamp();
	
	$eventMonth = date('n', $eventDate);
	$eventDay = date('j', $eventDate);
	$eventEndDay = date('j', $currentKioskEvent->endDate->getTimestamp());
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
	if($payment->event->id == $currentKioskEvent->id) {
		// this payment is a payment for the current event
		$eventPayment = $payment;
		
		// if the payment has been paid, then break... otherwise allow it to keep searching in case a later payment was actually made
		if($payment->hasPaid) {
			break;
		}
	}
}

// check that we have payment information
if($eventPayment != null) {
	$paymentRate = number_format($eventPayment->finalCost, 2) . "$";
	$paymentMethod = $eventPayment->method;
} else {
	$paymentRate = "--";
	$paymentMethod = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (?)</a>";
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
		function confirmDelete() {
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
		?>
    	<section id='content'>
    	   <article id='userProfile'>
           	   <a href="../participants/">back to Participants</a>
               <br /><br />
               <header><h1><?= toPrettyPrint($participant->firstName) . " " . toPrettyPrint($participant->lastName) ?></h1></header>
               <?
               if($currentKioskRole->hasPermission(org\fos\Role::$KIOSK_MODE)) { ?>
                   <button class='edit' onClick="window.location = '?id=<?= $participant->id ?>&resend=true'">Resend Profile Link Email</button>
                   <?
				   if($currentKioskEvent == null) { ?>
                   	<button class="edit" onClick="window.location = 'http://orientation.ssmu.mcgill.ca/orientation/actions/newerUpdateRegistrationAction.php?passkey=<?= $participant->registrationPassword ?>'">View Student's Calendar</button><br /><br />
                   <? }
			   } // end of check for edit ?>
               
               <h2>Main Information</h2>
               <? if($currentKioskEvent != null) { ?>
               <p><b>Age for Event:&nbsp;</b><?= $eventAge ?></p>
               <? } ?>
               <!--<p><b>Group:&nbsp;</b>no group assigned</p>-->
               <p><b>Birthday:&nbsp;</b><?= formatSimpleDate($participant->dateOfBirth) ?></p>
    	       <p><b>Faculty:&nbsp;</b><?= convertCodeToFaculty($participant->faculty) ?></p>
    	       <p><b>Shirt Size:&nbsp;</b><?= $participant->shirtSize ?></p>
    	       <p><b>Dietary Restrictions:&nbsp;</b><br /><?= $participant->dietaryRestrictions ?></p>
    	       <p><b>Allergies:&nbsp;</b><br /><?= $participant->allergies == "" ? "none" : $participant->allergies ?></p>
               <br />
               
               <? if($currentKioskEvent != null && $currentKioskEvent->category != "discoverMcGill" && $currentKioskEvent->category != "callfortender") { ?>
               <!-- show payment information if there is an event -->
               <h2>Payment Information:</h2>
               <p><b>Rate:&nbsp;</b><?= $paymentRate ?></p>
               <p><b>Method:&nbsp;</b><?= $paymentMethod ?></p>
               <p><b>Paid?:&nbsp;</b><?= $eventPayment->hasPaid ? "yes" : "no" ?></p>
               <br />
               <? } ?>
               
               <h2>Contact &amp; Other Information</h2>
    	       <p><b>E-Mail:&nbsp;</b><a href='mailto:<?= $participant->email ?>'><?= $participant->email ?></a></p>
    	       <p><b>Address:&nbsp;</b><br /><?= $participant->froshAddress ?></p>
    	       <p><b>Place of Origin:&nbsp;</b><?= $participant->placeOfOrigin ?></p>
    	       <p><b>Entering Year:&nbsp;</b>U<?= $participant->enteringYear ?></p>
    	       <p><b>Registration Date:&nbsp;</b><?= formatSimpleDate($participant->registrationDate) ?></p>
               <br />
               
               <h2>Registered Events</h2>
               <?
			   // start the list if needed
			   if(count($participant->events) > 0) {
				   echo("<ul>");
			   }
			   
			   foreach($participant->events as $registeredEvent) {
				   // loop through each of the participant's payments until we find one that matches this event
					$eventPayment = null;
					foreach($participant->payments as $payment) {
						$payment->event->load();
						if($payment->event->id == $registeredEvent->id) {
							// this payment is a payment for the current event
							$eventPayment = $payment;
							
							// if the payment has been paid, then break... otherwise allow it to keep searching in case a later payment was actually made
							if($payment->hasPaid) {
								break;
							}
						}
					}
					
					// check that we have payment information
					if($eventPayment != null) {
						$paymentRate = number_format($eventPayment->finalCost, 2) . "$";
						$paymentMethod = $eventPayment->method;
					} else {
						$paymentRate = "??";
						$paymentMethod = "unknown";
					}
					
					// create the printable payment info
					if($registeredEvent->category == "discoverMcGill" || $registeredEvent->category == "callfortender" || $currentKioskEvent != null) {
						$paymentInfo = "";
					} else {
						$paymentInfo = "($paymentMethod @ $paymentRate, ";
						if($eventPayment->hasPaid) {
							$paymentInfo .= "has paid)";
						} else {
							if($paymentMethod == "paypal") {
								$paymentInfo .= "payment pending)";
							} elseif ($method == "in Person") {
								$paymentInfo .= "not received)";
							} else {
								$paymentInfo .= "student must choose payment option)";
							}
						}
						
					}
					
					// create a link for them to unregister the participant from this event
					if($currentKioskEvent == null) {
						$unregisterLink = "(<a href='?id=$id&unregister=true&eventId={$registeredEvent->id}'>unregister</a>)";
					} else {
						$unregisterLink = "";
					}
					
					// print the info
				    echo("<li>" . toPrettyPrint($registeredEvent->eventName) . " $paymentInfo $unregisterLink</li>");
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