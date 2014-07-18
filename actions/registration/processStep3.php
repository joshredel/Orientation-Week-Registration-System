<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if(!isset($_POST['save']) || $_POST['save'] != 'step3done') { 
	redirect("/registration/step3.php");
}

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/registration/step1.php");
}
if(!isset($_SESSION['step2Complete']) || $_SESSION['step2Complete'] != true) {
	redirect("/registration/step2.php");
}

// store the event registration information
$_SESSION['registeredEvents'] = $_POST['registeredEvents'];
$_SESSION['customAnswers'] = $_POST['customAnswers'];

// mark that we've completed step 3 and go to step 4
$_SESSION['step3Complete'] = true;
redirect("/registration/step4.php");

?>