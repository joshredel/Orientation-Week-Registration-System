<?
require_once('../../functions.php');

session_start();

// check for form submission - if it doesn't exist then send back to contact form  
if (!isset($_POST['save']) || $_POST['save'] != 'step1done') { 
	redirect("/registration/step1.php");
} 

// mark that we've completed step 1 and go to step 2
$_SESSION['step1Complete'] = true;
redirect("/registration/step2.php");



/*
$data = array('save' => 'step2start');

// if it does exist, let's go to step 2!
$headers = "Content-type: application/x-www-form-urlencoded\r\n";
$headers .= "Location:../registration/step2.php\r\n";
$headers .= "Method: POST\r\n";
$headers .= http_build_query($data);


header($headers);
*/
?>