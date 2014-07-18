<?
/**
 * Records the completed information from a call so that we have accounting/usage information.
 */
// get the information that we want about the call
$callSid = $_REQUEST['CallSid'];
$fromNumber = $_REQUEST['From'];
$toNumber = $_REQUEST['To'];
$callStatus = $_REQUEST['CallStatus'];
$direction = $_REQUEST['Direction'];
$callDuration = $_REQUEST['CallDuration'];

// set database access info
$host = "localhost"; 
$user = "orientation2011"; 
$pass = "regerd8"; 

// connect to the database
mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
mysql_select_db("fos") or die("Could not connect to the FOS database.");

// add the information
$query = "INSERT INTO Callbacks (CallSid, FromNumber, ToNumber, CallStatus, Direction, CallDuration) VALUES ";
$query .= "('$callSid', '$fromNumber', '$toNumber', '$callStatus', '$direction', $callDuration)";
$r = mysql_query($query) or die(mysql_error());
if(!$r) {
	$errorMessage = "We ran into a problem adding your email address.  Please try again later!";
} else {
	$successMessage = "We have added you to the list to be automatically reminded when registration opens.  See you then!";
}

// close the database
mysql_close();
?>