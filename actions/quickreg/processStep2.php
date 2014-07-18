<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'step2done') { 
	redirect("/quickreg/step1.php");
}

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/quickreg/step1.php");
}

// store all of the registration information from the form
$_SESSION['firstName'] = $_POST['firstName'];
$_SESSION['lastName'] = $_POST['lastName'];
$_SESSION['preferredName'] = $_POST['preferredName'];
$_SESSION['genderPronoun'] = $_POST['genderPronoun'];
$_SESSION['studentId'] = $_POST['studentId'];
$_SESSION['email'] = $_POST['email'];
$_SESSION['livingStyle'] = $_POST['livingStyle'];
$_SESSION['placeOfOrigin'] = $_POST['placeOfOrigin'];
$_SESSION['enteringYear'] = $_POST['enteringYear'];
$_SESSION['tshirtSize'] = $_POST['tshirtSize'];
$_SESSION['faculty'] = $_POST['faculty'];
$_SESSION['dateOfBirth'] = $_POST['dateOfBirth'];
$_SESSION['dateOfBirthRaw'] = $_POST['dateOfBirthRaw'];
$_SESSION['dietaryRestrictions'] = $_POST['dietaryRestrictions'];
$_SESSION['allergies'] = $_POST['allergies'];
$_SESSION['physicalNeeds'] = $_POST['physicalNeeds'];
$_SESSION['approveFacultyCheck'] = $_POST['approveFacultyCheck'];

// make sure this user doesn't already exist
// if user ID is already registered, redirect to error page
$participantService = new services\ParticipantService();
if ($participantService->getParticipantByStudentId($_SESSION['studentId']) != null) {
	redirect("/quickreg/duplicateid.php");
	exit();
}

// mark that we've completed step 2 and go to step 3
$_SESSION['step2Complete'] = true;
$_SESSION['step2Complete'] = true;
redirect("/actions/quickreg/processStep3.php");

?>