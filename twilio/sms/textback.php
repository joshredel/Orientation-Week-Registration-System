<?
/**
 * Records the completed information from a call so that we have accounting/usage information.
 */
// get the information that we want about the call
$smsSid = $_REQUEST['Sid'];
$fromNumber = $_REQUEST['From'];
$toNumber = $_REQUEST['To'];
$body = $_REQUEST['Body'];
$smsStatus = $_REQUEST['SmsStatus'];

// set database access info
$host = "localhost"; 
$user = "orientation2011"; 
$pass = "regerd8"; 

// connect to the database
mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
mysql_select_db("fos") or die("Could not connect to the FOS database.");

// add the information
$query = "INSERT INTO Textbacks (SmsSid, FromNumber, ToNumber, Body, SmsStatus) VALUES ";
$query .= "('$SmsSid', '$fromNumber', '$toNumber', \"$body\", '$smsStatus')";
$r = mysql_query($query) or die(mysql_error());

// close the database
mysql_close();
?>