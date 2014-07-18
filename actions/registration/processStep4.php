<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'step4done') { 
	redirect("/registration/step4.php");
}

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/registration/step1.php");
}
if(!isset($_SESSION['step2Complete']) || $_SESSION['step2Complete'] != true) {
	redirect("/registration/step2.php");
}
if(!isset($_SESSION['step3Complete']) || $_SESSION['step3Complete'] != true) {
	redirect("/registration/step3.php");
}

// see if we have already done step 4
if(isset($_SESSION['step4Complete']) && $_SESSION['step4Complete'] == true) {
	// it has been; send them to step 5
	redirect("/registration/step5.php");
}

$participantService = new services\ParticipantService();
$eventService = new services\EventService();

// make sure this user doesn't already exist
// if user ID is already registered, redirect to error page
if ($participantService->getParticipantByStudentId($_SESSION['studentId']) != null) {
	redirect("/registration/duplicateid.php");
	exit();
}

// start the process to save the user in the database ("register" the participant)
// create the participant's record object
$participant = new org\fos\Participant();

// adjust the approve faculty check for database usage
if ($_SESSION['approveFacultyCheck'] == NULL) {
	$approveCheck = 0;
} else {
	$approveCheck = 1;
}

// adjust the birth date for Doctrine
//$dateOfBirthRaw = $_SESSION['dateOfBirthRaw'];
//$dateOfBirth = new DateTime('@' . ($dateOfBirthRaw / 1000));
//$dateOfBirth->setTimezone(new DateTimeZone('America/Montreal'));
$dateOfBirth = new DateTime($_SESSION['dateOfBirth'] . " 00:00:00");

//populate participant object
$participant->firstName = $_SESSION['firstName'];
$participant->lastName = $_SESSION['lastName'];
$participant->preferredName = $_SESSION['preferredName'];
$participant->preferredPronoun = $_SESSION['genderPronoun'];
$participant->studentId = $_SESSION['studentId'];
$participant->email = $_SESSION['email'];
$participant->livingStyle = $_SESSION['livingStyle'];
$participant->placeOfOrigin = $_SESSION['placeOfOrigin'];
$participant->enteringYear = $_SESSION['enteringYear'];
$participant->shirtSize = $_SESSION['tshirtSize'];
$participant->faculty = $_SESSION['faculty'];
$participant->dateOfBirth = $dateOfBirth;
$participant->dietaryRestrictions = implode(",", $_SESSION['dietaryRestrictions']);
$participant->allergies = $_SESSION['allergies'];
$participant->physicalNeeds = $_SESSION['physicalNeeds'];
$participant->approvedFacultyCheck = $approveCheck;
$participant->registrationPassword = md5(time() . $participant->lastName . $participant->email . $participant->firstName . rand());
$participant->registrationDate = date_create(NULL, timezone_open('America/Montreal'));
$participant->rawRegistrationData = $_SESSION['registeredEvents'];
$participant->sentNightlyReminder = false;
$participant->recordedName = null;
$participant->groupNumber = null;
$participant->lastText = null;
$participant->messageBuilder = null;

// save participant
$participantService->saveParticipant($participant);

// get the events
// pull all of the events out of the session variable
$rawEvents = explode(";", $_SESSION['registeredEvents']);

// loop through each one to display it
$totalCost = 0;
foreach($rawEvents as $rawEvent) {
	// break it down again into an array of the ticket info for this event
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$pureEvent = explode(",", $rawEvent);
	
	// get the event from the database
	$event = $eventService->getEvent($pureEvent[0]);
	
	// add the event to the participant's events list (this is effectively registering them)
	$participant->events[] = $event;
}

// save the participant again after the events have been added
$participantService->saveParticipant($participant);

// send the participant a welcome email
//TODO!! - write this message!
$message = "Hello " . getDisplayName($participant) . ", \n\nThank you for signing up for McGill Orientation Week 2013. Now that we have your general information, you will be able to access your personalized myWeek page.\n\n";
$message .= "Below, you will find a secure link to view your registration information on myWeek. For events that require payment, you will have the option of viewing the status of your payment or to pay in person on the lower campus of McGill University between August 23rd and 29th.\n\n";
$message .= "Here is a link to your myWeek page:\nhttp://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\n";
$message .= "You may use this link to view your registration status and to register for more events. The myWeek page will be growing and changing in the next few days, so check back regularly for more features.\n\n";
$message .= "We look forward to welcoming you to McGill and seeing you soon!\n\nSincerely,\nThe McGill Orientation Team";

mail($participant->email, "[McGill Orientation Week] Registration Link", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");

// mark that we've completed step 4 and go to step 5
$_SESSION['step4Complete'] = true;
redirect("/registration/step5.php");

?>