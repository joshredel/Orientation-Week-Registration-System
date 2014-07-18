<?
/**
 * This tool notifies participants of an issue where timezones were applied to birth dates when they were entered,
 * effectively shifting many birthdays a day backward.  It also looks for birthdays that do not seem correct, namely birthyear of 1999 or 1969.
 */

// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// database connection parameters
$host = "localhost"; 
$user = "orientation2011"; 
$pass = "regerd8"; 

// connect to the database
mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
mysql_select_db("fos") or die("Could not connect to the FOS database.");

// find all participants that have a weird birthday
$query = "SELECT * FROM  `Participants` WHERE `DateOfBirth` NOT LIKE '%00:00:00'";
$result = mysql_query($query) or die(mysql_error());
$totalWeirdBirthdays = 0;
while($row = mysql_fetch_array($result)) {
	// send them an email asking that they fix it
	$message = "Hello,\n\nWe are contacting you because due to either a technical or human error, your birthday may appear incorrect on your myWeek profile. It might be off by a day, or your birth year might erroneously appear as 1969 or 1999.\n\nPlease go to your myWeek profile and correct your birth date by inputting the correct information under the Corrections section of the Profile tab. The accuracy of your date of birth and age is important to the event organizers, so please take the time to ensure the information is correct or corrected and reflects the official birth date in your McGill record.\n\nhttp://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $row['RegistrationPassword'] . "\n\nWe thank you for your cooperation and apologize for any inconvenience.\n\nSincerely,\nThe Orientation Week Team";
	
	$mailResult = mail($row['Email'], "[McGill Orientation Week] Your myWeek Calendar is live!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
	
	$totalWeirdBirthdays++;
}

// find all participants that have a 1969 birthday
$query = "SELECT * FROM  `Participants` WHERE `DateOfBirth` LIKE '1969%'";
$result = mysql_query($query) or die(mysql_error());
$total1969Birthdays = 0;
while($row = mysql_fetch_array($result)) {
	// send them an email asking that they fix it
	$message = "Hello,\n\nWe are contacting you because due to either a technical or human error, your birthday may appear incorrect on your myWeek profile. It might be off by a day, or your birth year might erroneously appear as 1969 or 1999.\n\nPlease go to your myWeek profile and correct your birth date by inputting the correct information under the Corrections section of the Profile tab. The accuracy of your date of birth and age is important to the event organizers, so please take the time to ensure the information is correct or corrected and reflects the official birth date in your McGill record.\n\nhttp://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $row['RegistrationPassword'] . "\n\nWe thank you for your cooperation and apologize for any inconvenience.\n\nSincerely,\nThe Orientation Week Team";
	
	$mailResult = mail($row['Email'], "[McGill Orientation Week] Your myWeek Calendar is live!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
	
	$total1969Birthdays++;
}

// find all participants that have a 1999 birthday
$query = "SELECT * FROM  `Participants` WHERE `DateOfBirth` LIKE '1999%'";
$result = mysql_query($query) or die(mysql_error());
$total1999Birthdays = 0;
while($row = mysql_fetch_array($result)) {
	// send them an email asking that they fix it
	$message = "Hello,\n\nWe are contacting you because due to either a technical or human error, your birthday may appear incorrect on your myWeek profile. It might be off by a day, or your birth year might erroneously appear as 1969 or 1999.\n\nPlease go to your myWeek profile and correct your birth date by inputting the correct information under the Corrections section of the Profile tab. The accuracy of your date of birth and age is important to the event organizers, so please take the time to ensure the information is correct or corrected and reflects the official birth date in your McGill record.\n\nhttp://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $row['RegistrationPassword'] . "\n\nWe thank you for your cooperation and apologize for any inconvenience.\n\nSincerely,\nThe Orientation Week Team";
	
	$mailResult = mail($row['Email'], "[McGill Orientation Week] Your myWeek Calendar is live!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
	
	$total1999Birthdays++;
}

echo("We contacted $totalWeirdBirthdays people with timezone shifted birthdays, $total1969Birthdays with 1969 birthdays, and $total1999Birthdays with 1999 birthdays.");

// close the database
mysql_close();
?>