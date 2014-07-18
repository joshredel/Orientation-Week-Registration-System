<?
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// database connection params
$host = "localhost"; 
$user = "orientation2011"; 
$pass = "regerd8"; 

// the limits for emails to send on each run
$limit = 900;
$totalSent = 0;
$errorCount = 0;

// construct the message
$message = "Hello, \n\nYou are receiving this email because you asked to be reminded when registration opened.  And guess what... it's time!  Registration for McGill Orientation Week 2013 is now open.  Thank you kindly for your patience, and now the wait is over!  We invite you to register at the link below.  We highly recommend completing registration on a desktop or laptop computer.\n";
$message .= "http://orientation.ssmu.mcgill.ca/registration/\n\n";
$message .= "We look forward to welcoming you to McGill and seeing you soon!\n\nSincerely,\nThe McGill Orientation Team";

// connect to the database
mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
mysql_select_db("fos") or die("Could not connect to the FOS database.");

// select all reminders
$query = "SELECT * FROM ReminderRequests";
$result = mysql_query($query) or die(mysql_error());

while($row = mysql_fetch_array($result)) {
	// only send it if they haven't already been invited
	if($row['HasBeenInvited'] == 0) {
		// send the email!
		$mailResult = mail($row['Email'], "[McGill Orientation Week] It's time... Registration is now open!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
		
		if($mailResult) {
			// mark that they have recieved their email
			mysql_query("UPDATE ReminderRequests SET HasBeenInvited=1 WHERE Email='" . $row['Email'] . "'");
			
			// increase our sent count
			$totalSent++;
		} else {
			// list errors
			$errorCount++;
			echo("Failed to be accepted for delivery: " . $row['Email'] . "<br />");
		}
	}
	
	// see if we should end sending
	if($totalSent >= $limit) {
		break;
	}
}

echo("A total of " . $totalSent	. " emails were sent and a total of " . $errorCount . " errors were encountered.");

// close the database
mysql_close();
?>