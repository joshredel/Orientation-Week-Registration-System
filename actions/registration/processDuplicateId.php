<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'duplicateId') { 
	redirect("/registration/duplicateid.php");
}

$participantService = new services\ParticipantService();

// get the user to email
$participant = $participantService->getParticipantByStudentId($_SESSION['studentId']);

// construct the email
$message = "Dear " . getDisplayName($participant) . ",\n\n";
$message .= "You have requested to recieve a link to your myWeek account.  You can find the link below.\n";
$message .= "http://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\n";
$message .= "Sincerely,\nThe McGill Orientation Team";

mail($participant->email, "[McGill Orientation Week] Registration Link", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");

// destroy the session so they cannot redo this
session_destroy();
unset($_SESSION);

redirect("/");
?>