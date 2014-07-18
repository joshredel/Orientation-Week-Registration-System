<?
require_once('../functions.php');

// check for form submission - if it doesn't exist then send back to contact form  
if (!isset($_POST['save']) || $_POST['save'] != 'contact') { 
	redirect("/contact.php");
} 
	 
// get the posted data 
$contactName = $_POST['contactname'];
$contactEmail = $_POST['contactemail'];
$studentId = $_POST['studentid'];
$faculty = $_POST['faculty']; 
$faculty = convertCodeToFaculty($faculty);
$questionType = $_POST['questiontype'];
//$message = toPrettyPrint($_POST['question']);
$message = toTextareaPrint($_POST['question']);

// check the form
if (empty($contactName)) {
	// check that a name was entered
	$error = 'You must enter your name.'; 
} elseif (empty($contactEmail)) {
	// check that an email address was entered 
	$error = 'You must enter your email address.';
} elseif (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $contactEmail)) {
	// check for a valid email address 
	$error = 'You must enter a valid email address.';
} elseif (empty($message)) {
	// check that a message was entered 
	$error = 'You must enter a message.';
} elseif (empty($faculty)) {
	// check that a faculty was selected
	$error = 'You must eneter your faculty';
} elseif (empty($studentId)) {
	// check if a student id was entered
	$error = 'You must enter a student ID.';
} elseif (!is_numeric($studentId)) {
	// check if the student id was a number
	$error = 'Your student ID must be a number.';
} elseif (empty($questionType)) {
	// check if they selected a question type
	$error = 'You must select the type of question.';
}

// check if an error was found - if there was, send the user back to the form 
if (isset($error)) { 
	redirect('/contact.php?error=' . urlencode($error));
}

// compose the subject
$emailSubject = "[O-Week Contact Form] " . $questionType . " (" . $faculty . ")";

// write the email content 
$emailContent = "Name: " . $contactName . "\n";
$emailContent .= "Student ID: " . $studentId . "\n";
$emailContent .= "Email: " . $contactEmail . "\n";
$emailContent .= "Faculty: " . $faculty . "\n";
$emailContent .= "Message:\n\n" . $message;
	 
// send the email 
mail ("orientation@ssmu.mcgill.ca", $emailSubject, $emailContent, "From: " . $contactEmail . "\nSender: Contact Us Form <orientation@ssmu.mcgill.ca>"); 
	 
// send the user back to the form 
redirect('/contact.php?success=' . urlencode('Your message was successfully sent to the O-Week team.'));
?>