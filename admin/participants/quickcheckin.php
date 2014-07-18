<?
require_once('../../functions.php');

// check for a session
checkForSession();
$paymentService = new services\PaymentService();

// get the current time
$today = new DateTime("now", new DateTimeZone("America/Montreal"));

// redirect if they do not have permission to be here
if(!$currentRole->hasPermission(org\fos\Role::$CHECK_IN_PARTICIPANTS) || $currentEvent == null) {
	// the user does not have permissions
	redirect("/admin/participants/");
}

// set the mode to the default
$currentMode = "waiting";
/*
// check if they have submitted the id check field
if(isset($_POST['studentId'])) {
	// store the student ID
	$studentId = $_POST['studentId'];
	
	// find the participant with that ID
	$participant = $participantService->getParticipantByStudentId($studentId);
	
	// set the mode
	$currentMode = "searched";
}
*/
// check if they have submitted the id check field
if(isset($_POST['searchInfo'])) {
	// store the student ID
	$searchInfo = $_POST['searchInfo'];
	
	// find the participant with that ID
	$participants = $participantService->getParticipantBySearch($searchInfo);
	
	// filter based on event
	$filteredParticipants = array();
	foreach($participants as $participant) {
		if($currentEvent == null || inDoctrineArray($currentEvent, $participant->events)) {
			$filteredParticipants[] = $participant;
		}
	}
	
	// if we found just one, go to their page
	$foundParticipant = null;
	if(count($participants) == 1 || count($filteredParticipants) == 1) {
		$foundParticipant = $filteredParticipants[0];
	}
	
	// set the mode
	$currentMode = "searched";
}

if(isset($_GET['message'])) {
	$successMessage = $_GET['message'];
}

// a date compare function for calendar events
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// see if the event has optional costs
$hasOptionalCosts = false;
$totalEventCost = 0;
$totalEventBaseCost = 0;
foreach($currentEvent->costs as $cost) {
	$totalEventCost += $cost->amount;
	if($cost->isOptional) {
		$hasOptionalCosts = true;
	} else {
		$totalEventBaseCost += $cost->amount;
	}
}

// sort the calendar events by start date
$calendarEvents = $currentEvent->calendarEvents->toArray();
usort($calendarEvents, 'dateCompare');

// determine if the event has custome fields
$hasCustomFields = false;
if($currentEvent->customFields != null && strlen($currentEvent->customFields)) {
	$hasCustomFields = true;
}

// see if there is a success message
if(isset($_SESSION['checkInSuccess'])) {
	$successMessage = $_SESSION['checkInSuccess'];
	unset($_SESSION['checkInSuccess']);
}

// set that this was the last page
$_SESSION['lastParticipantLocation'] = "quickcheckin.php";
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
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script type="text/javascript">
		// case insensitive version of 'contains'
		$(document).ready(function() {
			jQuery.expr[':'].Contains = function(a, i, m) { 
				return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; 
			};
		});
		
		function filterLinks() {
			// get the search term
			var searchTerm = $('#filter').val();
			
			// hide everything
			$("tr.event").hide();
			
			// only show each link if it contains the search item
			$("tr.event:Contains('" + searchTerm + "')").show();
		}
		
		function checkIDForm(form) {
			var confirmationText = "";
			
			// get everything from the form
			var studentid = form.elements['studentId'].value;
			
			// check the studentid
			if(isNaN(studentid) || studentid == "" || studentid.length < 9)
			{
				// it's not a friggin' number
				confirmationText = "The student ID entered is not valid.";
			}
			
			// Continue with processing or return errors
			if(confirmationText == "") {
				return true;
			} else {
				alert(confirmationText);
				return false;
			}
		}
	</script>
    
    <style type="text/css">
	form {
		width: 700px;
		margin:auto;
	}
	
	textarea {
		width: 400px;
		height: 75px;
		display: block;
		margin-left: 126px;
	}
	
	label {
		margin: auto;
	}
	
	label {
		float: left;
		text-align: right;
		width: 120px;
		vertical-align: middle;
		font-weight:bold;
	}
	
	input, select {
		margin: 7px;
		margin-top: 0;
		vertical-align: middle;
	}
	</style>
    <script>
	// toggle the paid checkbox
	function toggleCheckbox(element) {
		// get the DOM element
		var checkbox = document.getElementById(element);
		
		// toggle the checkbox
		if(!checkbox.disabled) {
			if(checkbox.checked) {
				checkbox.checked = false;
			} else {
				checkbox.checked = true;
			}
		}
		
		// also toggle the payment if it is the option box
		if(element == "checkInOption") {
			if(checkbox.checked) {
				// show the full option price in the price field
				document.getElementById("rate").innerHTML = "$<?= $totalEventCost ?>";
			} else {
				// show the base price in the price field
				document.getElementById("rate").innerHTML = "$<?= $totalEventBaseCost ?>";
			}
		} else if(element == "checkInPaid") {
			// show or hide the red if it has been paid
			if(checkbox.checked) {
				$("#hasPaidText").removeClass("red-text");
			} else {
				$("#hasPaidText").addClass("red-text");
			}
		}
	}
	
	// toggle the paid checkbox
	function toggleOptionPrice() {
		// get the DOM element
		var checkbox = document.getElementById("checkInOption");
		
		// also toggle the payment if it is the option box
		if(checkbox.checked) {
			// show the full option price in the price field
			document.getElementById("rate").innerHTML = "$<?= $totalEventCost ?>";
		} else {
			// show the base price in the price field
			document.getElementById("rate").innerHTML = "$<?= $totalEventBaseCost ?>";
		}
	}
	
	// toggle the paid checkbox
	function toggleHasPaid() {
		// get the DOM element
		var checkbox = document.getElementById("checkInPaid");
		
		// show or hide the red if it has been paid
		if(checkbox.checked) {
			$("#hasPaidText").removeClass("red-text");
		} else {
			$("#hasPaidText").addClass("red-text");
		}
	}
	
	function checkin(firstName, lastName, amount, studentId, passkey, method, paid, merch, bracelet, braceletNo, eventAge, option, phone, customAnswers, shirtSize) {
		// Clear form (Just in case)
		clearCheckin();
		
		// set form to current State
		document.getElementById("rate").innerHTML = amount;
		
		document.getElementById("ageField").innerHTML = eventAge;
		document.getElementById("shirtField").innerHTML = shirtSize;
		
		document.getElementById("checkInPaid").checked = paid;
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").checked = option;
		<? } ?>
		document.getElementById("checkInMerch").checked = merch;
		//document.getElementById("checkInBracelet").checked = bracelet;
		document.getElementById("checkInBraceletNumber").value = braceletNo;
		document.getElementById("checkInPasskey").value = passkey;
		document.getElementById("checkInPhone").value = phone;
		checkPhoneFormat(document.getElementById("checkInPhone"));
		
		// disable the paid chackbox if is paid and method is paypal
		if (method =="paypal" && paid) {
			document.getElementById("checkInPaid").disabled = true;
			document.getElementById("checkInWithPayPal").disabled = true;
			document.getElementById("checkInInPerson").disabled = true;
			<? if($hasOptionalCosts) { ?>
			document.getElementById("checkInOption").disabled = true;
			<? } ?>
		}
		
		// preselect the payment method based on existing payment
		if (method == "paypal") {
			document.getElementById("checkInWithPayPal").checked = true;
			document.getElementById("checkInInPerson").checked = false;
		} else {
			document.getElementById("checkInInPerson").checked = true;
			document.getElementById("checkInWithPayPal").checked = false;
		}
		
		<? if($hasCustomFields) { ?>
		// process the custom answers that were sent to us
		var answers = customAnswers.split("<:;:>");
		for(var i = 0; i < answers.length; i++) {
			// get all of the components
			var answerFields = answers[i].split("<::>");
			var eventId = answerFields[0];
			var questionId = answerFields[1];
			var fieldName = answerFields[2];
			var answerText = answerFields[3];
			
			// if the event matches, populate the field (we're only doing selects in this version!
			if(eventId == <?= $currentEvent->id ?>) {
				// get the selection field to operate on
				var selectField = document.getElementById("customField" + questionId);
				
				// loop through the available options and select the one that has a matching answer
				for(var j = 0; j < selectField.options.length; j++) {
					if(selectField.options[j].value == answerText) {
						selectField.selectedIndex = j;
						break;
					}
				}
			}
		}
		<? } ?>
	}
	
	function clearCheckin() {
		// clear the checkin panel
		document.getElementById("rate").innerHTML = "";
		document.getElementById("checkInPaid").checked = false;
		$("#hasPaidText").addClass("red-text");
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").checked = false;
		<? } ?>
		document.getElementById("checkInInPerson").checked = false;
		document.getElementById("checkInWithPayPal").checked = false;
		document.getElementById("checkInMerch").checked = false;
		//document.getElementById("checkInBracelet").checked = false;
		document.getElementById("checkInBraceletNumber").value = "";
		document.getElementById("checkInPasskey").value = "";
		document.getElementById("checkInPhone").value = "";
		
		document.getElementById("checkInPaid").disabled = false;		
		document.getElementById("checkInWithPayPal").disabled = false;
		document.getElementById("checkInInPerson").disabled = false;
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").disabled = false;
		<? } ?>
		// hide the checkin panel
		
	}
	
	function checkinFull(firstName, lastName, amount, studentId, passkey, method, paid, merch, bracelet, braceletNo, eventAge, option, phone, customAnswers, shirtSize) {
		// Clear form (Just in case)
		clearCheckinFull();
		
		// clear and enable the search bar
		document.getElementById("filter").value = "";
		document.getElementById("filter").disabled = true;
		
		// hide Table
		document.getElementById("people").style.display = "none";
		
		// show form
		document.getElementById("checkinBox").style.display = "";
		
		// set form to current State
		document.getElementById("name").innerHTML = "Check-In Participant - " + firstName + " " + lastName + " (" + studentId + ")";
		document.getElementById("rate").innerHTML = amount;
		
		document.getElementById("ageField").innerHTML = eventAge;
		document.getElementById("shirtField").innerHTML = shirtSize;
		
		document.getElementById("checkInPaid").checked = paid;
		if(paid) {
			$("#hasPaidText").removeClass("red-text");
		} else {
			$("#hasPaidText").addClass("red-text");
		}
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").checked = option;
		if(document.getElementById("checkInOption").checked) {
			// show the full option price in the price field
			document.getElementById("rate").innerHTML = "$<?= $totalEventCost ?>";
		} else {
			// show the base price in the price field
			document.getElementById("rate").innerHTML = "$<?= $totalEventBaseCost ?>";
		}
		<? } ?>
		document.getElementById("checkInMerch").checked = merch;
		//document.getElementById("checkInBracelet").checked = bracelet;
		document.getElementById("checkInBraceletNumber").value = braceletNo;
		document.getElementById("checkInPasskey").value = passkey;
		document.getElementById("checkInPhone").value = phone;
		checkPhoneFormat(document.getElementById("checkInPhone"));
		
		// disable the paid chackbox if is paid and method is paypal
		if (method =="paypal" && paid) {
			document.getElementById("checkInPaid").disabled = true;
			document.getElementById("checkInWithPayPal").disabled = true;
			document.getElementById("checkInInPerson").disabled = true;
			<? if($hasOptionalCosts) { ?>
			document.getElementById("checkInOption").disabled = true;
			<? } ?>
		}
		
		// preselect the payment method based on existing payment
		if (method == "paypal") {
			document.getElementById("checkInWithPayPal").checked = true;
			document.getElementById("checkInInPerson").checked = false;
		} else {
			document.getElementById("checkInInPerson").checked = true;
			document.getElementById("checkInWithPayPal").checked = false;
		}
		
		<? if($hasCustomFields) { ?>
		// process the custom answers that were sent to us
		var answers = customAnswers.split("<:;:>");
		for(var i = 0; i < answers.length; i++) {
			// get all of the components
			var answerFields = answers[i].split("<::>");
			var eventId = answerFields[0];
			var questionId = answerFields[1];
			var fieldName = answerFields[2];
			var answerText = answerFields[3];
			
			// if the event matches, populate the field (we're only doing selects in this version!
			if(eventId == <?= $currentEvent->id ?>) {
				// get the selection field to operate on
				var selectField = document.getElementById("customField" + questionId);
				
				// loop through the available options and select the one that has a matching answer
				for(var j = 0; j < selectField.options.length; j++) {
					if(selectField.options[j].value == answerText) {
						selectField.selectedIndex = j;
						break;
					}
				}
			}
		}
		<? } ?>
	}
	
	function clearCheckinFull() {
		// clear and enable the search bar
		document.getElementById("filter").value = "";
		document.getElementById("filter").disabled = false;
		
		filterLinks();
		
		// show the form
		document.getElementById("people").style.display = "";
		
		// hide the form
		document.getElementById("checkinBox").style.display = "none";
		
		// clear the checkin panel
		document.getElementById("name").innerHTML = "";
		document.getElementById("rate").innerHTML = "";
		document.getElementById("checkInPaid").checked = false;
		$("#hasPaidText").addClass("red-text");
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").checked = false;
		<? } ?>
		document.getElementById("checkInInPerson").checked = false;
		document.getElementById("checkInWithPayPal").checked = false;
		document.getElementById("checkInMerch").checked = false;
		//document.getElementById("checkInBracelet").checked = false;
		document.getElementById("checkInBraceletNumber").value = "";
		document.getElementById("checkInPasskey").value = "";
		document.getElementById("checkInPhone").value = "";
		
		document.getElementById("checkInPaid").disabled = false;		
		document.getElementById("checkInWithPayPal").disabled = false;
		document.getElementById("checkInInPerson").disabled = false;
		<? if($hasOptionalCosts) { ?>
		document.getElementById("checkInOption").disabled = false;
		<? } ?>
		// hide the checkin panel
		
	}
	
	function checkForm(form) {
		var confirmationText = "";
		
		// get everything from the form
		var braceletNumber = form.elements['braceletNumber'].value;
		var phoneNumber = form.elements['phone'].value;
		
		// check the studentid
		if(braceletNumber == "" || isNaN(braceletNumber)) {
			// it's not a number
			confirmationText = "The bracelet number is empty or not valid.  ";
		}
		
		// check the phone number
		if(phoneNumber != "" && phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
			var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if(!phoneNumber.match(phoneno)) {
				confirmationText += "The phone number is not a valid format."
			}
		}
		
		// see if they checked payment
		if(paymentMarked == false) {
			// they didn't mark it as paid
			confirmationText += "The participant has not been marked as paid.";
		}
		
		
		// Continue with processing or return errors
		if(confirmationText == "") {
			return true;
		} else {
			alert(confirmationText);
			return false;
		}
	}
	
	function checkPhoneFormat(phoneField) {
		// get the value
		var phoneNumber = phoneField.value;
		
		phoneFormat = "Unknown";
		if(phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
			var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if(phoneNumber.match(phoneno)) {
				phoneFormat = "North America";
			}
		} else {
			// it starts with a plus, but first check to see if it is a +1 and then 10 digits (a North America number)
			if(phoneNumber.length == 12 && phoneNumber.charAt(1) == '1') {
				// we have a north america number still
				phoneFormat = "North America";
			} else {
				phoneFormat = "International";
			}
		}
		
		$("#phoneFormat").html(phoneFormat);
	}
	</script>
</head>
<body <?= ($currentMode == "waiting" ? "onLoad='document.idform.searchInfo.focus();'" : "") ?>>
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
		
		if(isset($successMessage) && $successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
        	<?
			// check to see if this user has an event associated to them
			if(!$currentRole->hasPermission(org\fos\Role::$CHECK_IN_PARTICIPANTS)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
				if($currentMode == "waiting") {
					// we're in the ID checking phase
					?>
            		<article>
                    	<header><h1>Quick Check In</h1></header>
                        <p>
                        	Please enter a piece of information about the participant you wish to check in.
                        </p>
                        <p>
                        	This can be one of: first name, preferred name, last name, student ID, or email.
                        </p>
                        <form method="post" name="idform">
                        	<label for='searchInfo'><b>Search Terms:</b></label>
                   			<input type='text' name='searchInfo' style="width:80%" autocorrect="off" autocapitalize="off" /><br />
                            
                            <input class='button' type='submit' value='Search' />
                        </form>
                    </article>
            		<?
				} else {
					// we have a proper ID and can actually fill out the form
					?>
                	<article>
                    	<? if(count($participants) == 0) { ?>
                        <!-- start fresh -->
                        <header><h1>Needs Initial Registration</h1></header>
                        <p>
                        	A participant with information <?= $searchInfo ?> could not be found.<br /><br />
							Please direct them to the Information Kiosk so that they can perform initial registration.
                        </p>
                        
                        <button class="button" onClick="window.location = 'quickcheckin.php';">Back to Quick Check In</button>
                        <? } else {
							if($foundParticipant != null) {
								if(!inDoctrineArray($currentEvent, $foundParticipant->events)) {
									?>
									<p>
										A participant with student ID <?= $foundParticipant->studentId . ' (' . getDisplayName($foundParticipant) . ' ' . $foundParticipant->lastName . ')'?> exists in the system but has not yet registered for <strong><?= $currentEvent->eventName ?></strong>.  Please go to the <a href="/admin/participants/register.php">Cross Registration page</a> to complete check in.
										<br /><br />
										<button class="button" onClick="window.location = 'quickcheckin.php';">Back to Quick Check In</button>
									</p>
									<?
								} else {
									?>
									<!-- ask to import -->
									<header>
										<h2 id="name">Check-In Participant - <?=  getDisplayName($foundParticipant) . " " . toPrettyPrint($foundParticipant->lastName) . " (" . $foundParticipant->studentId . ")"?></h2>
									</header>
									
									<form method="post" action="../../actions/admin/processCheckin.php" name="checkinPanel" id="checkinPanel" onsubmit='return checkForm(this)'>
										<?
										// loop through each of the participants payments until we find one that matches this event
										if($currentEvent != null) {
											$eventPayment = null;
											$totalPaid = 0;
											foreach($foundParticipant->payments as $payment) {
												if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
													// this payment is a payment for the current event
													$eventPayment = $payment;
												}
												if($payment->event->id == $currentEvent->id) {
													$totalPaid += $payment->finalCost;
												}
											}
										
											// check that we have payment information
											if($eventPayment != null) {
												$rate = "$" . $eventPayment->finalCost;
												$method = $eventPayment->method;
											} else {
												$rate = "--";
												$method = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (?)</a>";
											}
											
											// create the printable payment info
											if(count($currentEvent->costs) == 0) {
												$paymentInfo = "";
											} else {
												// figure out the transaction status
												if($eventPayment != null) {
													if($eventPayment->hasPaid) {
														$paymentStatus = "paid";
														$paid = "true";
														
														// but see if there is a pending status somewhere in the status
														if(stripos($eventPayment->status, "pending") !== false){
															$paymentStatus = "<a onClick='alert(\"It is marked that this payment was completed but that the transaction was marked pending.  Please check your PayPal account to make sure you do not have any payments that you need to manually accept\")'>paid (?)</a>";
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
														$paid = "false";
													}
													
													// store the payment method
													$paymentMethod = $eventPayment->method;
												} else {
													$paymentStatus = "unpaid";
													$paid = "false";
													$paymentMethod = "inperson";
												}
											}
										}
										
										// find a checkin for this event and show results if applicable
										$validCheckin = null;
										foreach ($foundParticipant->checkIns as $checkin) {
											if ($currentEvent->id == $checkin->event->id) {
												$validCheckin = $checkin;
												break;
											}
										}
										
										// print checkin if available
										if ($validCheckin != null) {
											if ($validCheckin->gotMerchandise == 1) {
												$merch = "true";
											} else {
												$merch = "false";
											}
											
											if ($validCheckin->gotBracelet == 1) {
												$bracelet = "true";
											} else {
												$bracelet = "false";
											}
											
											if($validCheckin->braceletNumber == null) {
												$braceletNumber = "";
											} else {
												$braceletNumber = $validCheckin->braceletNumber;
											}
										} else {
											// else, do not display anything
											$bracelet = "false";
											$merch = "false";
											$braceletNumber = "";
										}
										
										// get their age for the event
										if(count($currentEvent->calendarEvents) > 0) {
											$length = count($calendarEvents);
											$eventDate = $calendarEvents[0]->startTime->getTimestamp();
											$eventEndDate = $calendarEvents[$length - 1]->endTime->getTimestamp();
											$birthDate = $foundParticipant->dateOfBirth->getTimestamp();
											
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
										
										// see if they paid for the option as well
										$selectedOption = "false";
										
										// now see if they paid for as much as the optional cost is
										if($hasOptionalCosts) {
											if($totalPaid > $totalEventBaseCost) {
												$selectedOption = "true";
											}
										}
										
										// see if they have a null option
										if($totalPaid == 0) {
											$totalPaid = $totalEventBaseCost;
										}
										
										// prepare the phone number for being re-displayed; strip the "+1" from it
										$phone = $foundParticipant->phoneNumber;
										
										// send custom field answers if appropriate
										$customAnswers = "";
										if($hasCustomFields) {
											$customAnswers = $foundParticipant->customFieldAnswers;
										}
										?>
										
										<table class="checkInTable">
											<tr>
												<td class="left-column">
													Age for event:
												</td>
												<td>
													<p id="ageField"></p>
												</td>
											</tr>
											<tr>
												<td class="left-column">
													Shirt size:
												</td>
												<td>
													<p id="shirtField"></p>
												</td>
											</tr>
											<!-- Has paid? -->
											<tr>
												<td onClick="toggleCheckbox('checkInPaid')" class="left-column">
													<span id="hasPaidText" class="red-text">Has paid?*</span>:
												</td>
												<td>
													<input id="checkInPaid" type="checkbox" name="paid" value="1" onChange="toggleHasPaid()" /><span id="rate" onClick="toggleCheckbox('checkInPaid')" style="font-size:26; font-weight:bold"></span><br />
													<input id="checkInInPerson" type="radio" name="paymentMethod" value="inperson" /><span>In Person</span>
													<input id="checkInWithPayPal" type="radio" name="paymentMethod" value="paypal" /><span>With PayPal</span>
												</td>
											</tr>
											<?
											if($hasOptionalCosts) {
												?>
												<tr>
													<td onClick="toggleCheckbox('checkInOption')" class="left-column">
														With option?:
													</td>
													<td>
														<input id="checkInOption" type="checkbox" name="option" value="1" onClick="toggleOptionPrice()" />
													</td>
												</tr>
												<!--
												<input id="checkInOption" type="checkbox" name="option" value="1" onClick="toggleOptionPrice()" /><label for="option" onClick="toggleCheckbox('checkInOption')">With option?</label><br />-->
												<?
											}
											?>
											
											<?
											if($hasCustomFields) {
												// process them and create the necessary form fields
												// break apart the different questions
												$questions = explode("<:;:>", $currentEvent->customFields);
												foreach($questions as $question) {
													// get the details about this question
													$customInfo = explode("<::>", $question);
													$fieldId = $customInfo[0];
													$fieldName = $customInfo[1];
													$fieldDescription = $customInfo[2];
													$fieldType = $customInfo[3];
													$fieldOptions = explode(",", $customInfo[4]);
													$fieldAdminOnly = $customInfo[5];
													
													// determine input to create based on field type
													if($fieldType == "dropdown") { // we're only going to dropdown for check in!
														echo("<tr><td class=\"left-column\">" . $fieldName . ":</td>");
														echo("<td><select name=\"customField" . $fieldId . "\" id=\"customField" . $fieldId . "\">");
														// print each of the options
														foreach($fieldOptions as $fieldOption) {
															echo("<option value=\"" . $fieldOption . "\">" . $fieldOption . "</option>");
														}
														echo("</select></td></tr>");
													}
												}
											}
											?>
											<!-- Got merch? -->
											<tr>
												<td onClick="toggleCheckbox('checkInMerch')" class="left-column">
													Got merchandise?:
												</td>
												<td>
													<input id="checkInMerch" type="checkbox" name="merch" value="1"  />
												</td>
											</tr>
											<!-- Bracelet number -->
											<tr>
												<td class="left-column">
													Bracelet number*:
												</td>
												<td>
													<input id="checkInBraceletNumber" type="text" name="braceletNumber" />
												</td>
											</tr>
											<!-- Phone Number -->
											<tr>
												<td class="left-column">
													Phone number:
												</td>
												<td>
													<input id="checkInPhone" type="text" name="phone" onChange="checkPhoneFormat(this)" onKeyUp="checkPhoneFormat(this)" /> (Detected format: <span id="phoneFormat"></span>)<br />
													<p style="font-size:14px">
														<strong>Phone formats:</strong><br />
														<em>North America:</em> xxx-xxx-xxxx or xxx.xxx.xxxx or xxx xxx xxxx or +1xxxxxxxxx<br />
														<em>International:</em> +xxxxxxxxx...
													</p>
												</td>
											</tr>
											
											<tr>
												<td>
													
												</td>
												<td>
													<input class="redbutton" style="display: inline-block" type="submit" value="&nbsp;&nbsp;&nbsp;Check-In&nbsp;&nbsp;&nbsp;" />
													<input type="button" class="button" style="display: inline-block" onClick="window.location = 'quickcheckin.php'" value="Cancel" />
												</td>
											</tr>
										</table>
										
										<input id="checkInPasskey" type="hidden" name="passkey" value="" />
										<input type="hidden" name="originator" value="quickcheckin.php" />
									</form>
									<?
								}
							} else { // we found multiple participants, theoretically (might not necessarily be in our event)
								if(count($filteredParticipants) == 0) {
									?>
									<p>
										Participants with the search term "<?= $searchInfo ?>" exist in the system but have not yet registered for <strong><?= $currentEvent->eventName ?></strong>.  Please go to the <a href="/admin/participants/register.php">Cross Registration page</a> to complete check in.
										<br /><br />
										<button class="button" onClick="window.location = 'quickcheckin.php';">Back to Quick Check In</button>
									</p>
                                    <?
								} else {
									// we have multiple people found in the search that can be checked in
									?>
									<form onSubmit="return false">
										<input id="filter" type="text" onKeyUp="filterLinks()" placeholder="Filter results..." >
									</form>
									<table id="people">
										<thead>
											<tr>
												<th style="max-width: 200px; word-wrap:break-word">Name</th>
												<th>Student ID</th>
												<? if(count($currentEvent->costs) > 0) { ?>
												<th>Rate</th>
												<th>Method</th>
												<th>Status</th>
												<? } ?>
												<th>Merch</th>
												<th>Bracelet</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
										<?
										foreach($filteredParticipants as $participant) {
											//$prettyName = toPrettyPrint($participant->firstName) . " " . toPrettyPrint($participant->lastName);
											$prettyName = toPrettyPrint(getDisplayName($participant) . " " . $participant->lastName);
											
											echo("<tr class='event'><td  style=\"max-width: 200px; word-wrap:break-word\"><a href='../participants/profile.php?id={$participant->id}'>{$prettyName}</a></td>");
											echo("<td>{$participant->studentId}</td>");
											
											// loop through each of the participants payments until we find one that matches this event
											if($currentEvent != null) {
												$eventPayment = null;
												$totalPaid = 0;
												foreach($participant->payments as $payment) {
													if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
														// this payment is a payment for the current event
														$eventPayment = $payment;
													}
													if($payment->event->id == $currentEvent->id) {
														$totalPaid += $payment->finalCost;
													}
												}
											
												// check that we have payment information
												if($eventPayment != null) {
													$rate = "$" . $eventPayment->finalCost;
													$method = $eventPayment->method;
												} else {
													$rate = "--";
													$method = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (?)</a>";
												}
												
												// create the printable payment info
												if(count($currentEvent->costs) == 0) {
													$paymentInfo = "";
												} else {
													// figure out the transaction status
													if($eventPayment != null) {
														if($eventPayment->hasPaid) {
															$paymentStatus = "paid";
															$paid = "true";
															
															// but see if there is a pending status somewhere in the status
															if(stripos($eventPayment->status, "pending") !== false){
																$paymentStatus = "<a onClick='alert(\"It is marked that this payment was completed but that the transaction was marked pending.  Please check your PayPal account to make sure you do not have any payments that you need to manually accept\")'>paid (?)</a>";
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
															$paid = "false";
														}
														
														// store the payment method
														$paymentMethod = $eventPayment->method;
													} else {
														$paymentStatus = "unpaid";
														$paid = "false";
														$paymentMethod = "inperson";
													}
													
													
													
													if($paymentStatus == "unpaid") {
														echo("<td>$rate</td><td>$method</td><td style=\"color: red\">$paymentStatus</td>");
													} else {
														echo("<td>$rate</td><td>$method</td><td>$paymentStatus</td>");
													}
												}
											}
											
											// find a checkin for this event and show results if applicable
											$validCheckin = null;
											foreach ($participant->checkIns as $checkin) {
												if ($currentEvent->id == $checkin->event->id) {
													$validCheckin = $checkin;
													break;
												}
											}
											
											// print checkin if available
											if ($validCheckin != null) {
												if ($validCheckin->gotMerchandise == 1) {
													echo "<td>YES</td>";
													$merch = "true";
												} else {
													echo "<td>NO</td>";
													$merch = "false";
												}
												
												if ($validCheckin->gotBracelet == 1) {
													echo "<td>" . $validCheckin->braceletNumber . "</td>";
													$bracelet = "true";
												} else {
													echo "<td>NO</td>";
													$bracelet = "false";
												}
												
												if($validCheckin->braceletNumber == null) {
													$braceletNumber = "";
												} else {
													$braceletNumber = $validCheckin->braceletNumber;
												}
											} else {
												// else, do not display anything
												echo("<td></td>");
												echo("<td></td>");
												$bracelet = "false";
												$merch = "false";
												$braceletNumber = "";
											}
											
											// get their age for the event
											if(count($currentEvent->calendarEvents) > 0) {
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
											
											// see if they paid for the option as well
											$selectedOption = "false";
											
											// now see if they paid for as much as the optional cost is
											if($hasOptionalCosts) {
												if($totalPaid > $totalEventBaseCost) {
													$selectedOption = "true";
												}
											}
											
											// see if they have a null option
											if($totalPaid == 0) {
												$totalPaid = $totalEventBaseCost;
											}
											
											// prepare the phone number for being re-displayed; strip the "+1" from it
											$phone = $participant->phoneNumber;
											
											// send custom field answers if appropriate
											$customAnswers = "";
											if($hasCustomFields) {
												$customAnswers = $participant->customFieldAnswers;
											}
												
											
											// print the button plus the checkin javascript
											echo("<td><input type='button' value='Check-In' onclick='checkinFull(\"" . getDisplayName($participant) . "\", \"{$participant->lastName}\", \"$" . $totalPaid . "\", \"{$participant->studentId}\", \"{$participant->registrationPassword}\", \"$paymentMethod\", $paid, $merch, $bracelet, \"$braceletNumber\", \"$eventAge\", $selectedOption, \"$phone\", \"$customAnswers\", \"{$participant->shirtSize}\")' /></td></tr>");
										}
										?>
										</tbody>
									</table>
                                    <?
								}
							}
                        } 
						?>
                    </article>
                    <?
					if($currentMode == "searched" && count($filteredParticipants) != 0) {
						?>
                    	<article id="checkinBox" style="display: none">
                            <form method="post" action="../../actions/admin/processCheckin.php" name="checkinPanel" id="checkinPanel" onsubmit='return checkForm(this)'>
                                <header>
                                    <h2 id="name">Check-In Participant - </h2>
                                </header>
                                
                                
                                <table  class="checkInTable">
                                    <tr>
                                        <td class="left-column">
                                            Age for event:
                                        </td>
                                        <td>
                                            <p id="ageField"></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="left-column">
                                            Shirt size:
                                        </td>
                                        <td>
                                            <p id="shirtField"></p>
                                        </td>
                                    </tr>
                                    <!-- Has paid? -->
                                    <tr>
                                        <td onClick="toggleCheckbox('checkInPaid')" class="left-column">
                                            <span id="hasPaidText" class="red-text">Has paid?*</span>:
                                        </td>
                                        <td>
                                            <input id="checkInPaid" type="checkbox" name="paid" value="1" onChange="toggleHasPaid()" /><span id="rate" onClick="toggleCheckbox('checkInPaid')" style="font-size:26; font-weight:bold"></span><br />
                                            <input id="checkInInPerson" type="radio" name="paymentMethod" value="inperson" /><span>In Person</span>
                                            <input id="checkInWithPayPal" type="radio" name="paymentMethod" value="paypal" /><span>With PayPal</span>
                                        </td>
                                    </tr>
                                    <?
                                    if($hasOptionalCosts) {
                                        ?>
                                        <tr>
                                            <td onClick="toggleCheckbox('checkInOption')" class="left-column">
                                                With option?:
                                            </td>
                                            <td>
                                                <input id="checkInOption" type="checkbox" name="option" value="1" onClick="toggleOptionPrice()" />
                                            </td>
                                        </tr>
                                        <!--
                                        <input id="checkInOption" type="checkbox" name="option" value="1" onClick="toggleOptionPrice()" /><label for="option" onClick="toggleCheckbox('checkInOption')">With option?</label><br />-->
                                        <?
                                    }
                                    ?>
                                    
                                    <?
                                    if($hasCustomFields) {
                                        // process them and create the necessary form fields
                                        // break apart the different questions
                                        $questions = explode("<:;:>", $currentEvent->customFields);
                                        foreach($questions as $question) {
                                            // get the details about this question
                                            $customInfo = explode("<::>", $question);
                                            $fieldId = $customInfo[0];
                                            $fieldName = $customInfo[1];
                                            $fieldDescription = $customInfo[2];
                                            $fieldType = $customInfo[3];
                                            $fieldOptions = explode(",", $customInfo[4]);
                                            $fieldAdminOnly = $customInfo[5];
                                            
                                            // determine input to create based on field type
                                            if($fieldType == "dropdown") { // we're only going to dropdown for check in!
                                                echo("<tr><td class=\"left-column\">" . $fieldName . ":</td>");
                                                echo("<td><select name=\"customField" . $fieldId . "\" id=\"customField" . $fieldId . "\">");
                                                // print each of the options
                                                foreach($fieldOptions as $fieldOption) {
                                                    echo("<option value=\"" . $fieldOption . "\">" . $fieldOption . "</option>");
                                                }
                                                echo("</select></td></tr>");
                                            }
                                        }
                                    }
                                    ?>
                                    <!-- Got merch? -->
                                    <tr>
                                        <td onClick="toggleCheckbox('checkInMerch')" class="left-column">
                                            Got merchandise?:
                                        </td>
                                        <td>
                                            <input id="checkInMerch" type="checkbox" name="merch" value="1"  />
                                        </td>
                                    </tr>
                                    <!-- Bracelet number -->
                                    <tr>
                                        <td class="left-column">
                                            Bracelet number*:
                                        </td>
                                        <td>
                                            <input id="checkInBraceletNumber" type="text" name="braceletNumber" />
                                        </td>
                                    </tr>
                                    <!-- Phone Number -->
                                    <tr>
                                        <td class="left-column">
                                            Phone number:
                                        </td>
                                        <td>
                                            <input id="checkInPhone" type="text" name="phone" onChange="checkPhoneFormat(this)" onKeyUp="checkPhoneFormat(this)" /> (Detected format: <span id="phoneFormat"></span>)<br />
                                            <p style="font-size:14px">
                                                <strong>Phone formats:</strong><br />
                                                <em>North America:</em> xxx-xxx-xxxx or xxx.xxx.xxxx or xxx xxx xxxx or +1xxxxxxxxx<br />
                                                <em>International:</em> +xxxxxxxxx...
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>
                                            
                                        </td>
                                        <td>
                                            <input class="redbutton" style="display: inline-block" type="submit" value="&nbsp;&nbsp;&nbsp;Check-In&nbsp;&nbsp;&nbsp;" />
                                            <input type="button" class="button" style="display: inline-block" onClick="window.location = 'quickcheckin.php'" value="Cancel" />
                                        </td>
                                    </tr>
                                </table>
                                <input id="checkInPasskey" type="hidden" name="passkey" value="" />
								<input type="hidden" name="originator" value="quickcheckin.php" />
                            </form>
                           </article>
                           <?
					}
				}
			} // end check for the user's current event
            ?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
    <script>
		$(document).ready(function() {
			<?
			if($currentMode != "waiting" && $foundParticipant != null && inDoctrineArray($currentEvent, $foundParticipant->events)) {
				echo("checkin(\"" . getDisplayName($foundParticipant) . "\", \"{$foundParticipant->lastName}\", \"$" . $totalPaid . "\", \"{$foundParticipant->studentId}\", \"{$foundParticipant->registrationPassword}\", \"$paymentMethod\", $paid, $merch, $bracelet, \"$braceletNumber\", \"$eventAge\", $selectedOption, \"$phone\", \"$customAnswers\", \"{$foundParticipant->shirtSize}\");");
			}
			?>
      	});
	</script>
</body>
</html>