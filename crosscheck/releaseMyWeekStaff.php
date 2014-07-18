<?
/**
 * This tool send an email to all users with a desired message, generally used to notify of new features.
 */
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_GET['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// initialize services
$staffService = new services\StaffService();

// payment status counters
$totalSent = 0;
$errorCount = 0;

// loop through all participants
$staffs = $staffService->getStaffs();
foreach($staffs as $staff) {
	if($staff->email != null && $staff->email != "") {
		// create the message
		$message = "Dear " . $staff->displayName . ",\n\n";
		$message .= "Earlier we sent you a link to your myWeek page but sent the wrong URL.  Please note the changed URL below and be sure to bookmark this one instead!  The one from the previous email can be ignored completely.  Below is your correct link:\n\nhttp://orientation.ssmu.mcgill.ca/myweekstaff/?passkey=" . $staff->registrationPassword . "\n\nOur sincere apologies for the confusion!\n\nSincerely,\nSSMU Central Communications Team";
		
		$mailResult = mail($staff->email, "[IMPORTANT - FROSH] NEW LINK for myWeek", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
			
		if($mailResult) {
			// increase our sent count
			$totalSent++;
		} else {
			// list errors
			$errorCount++;
			echo("Failed to be accepted for delivery: " . $staff->email . "<br />");
		}
	}
}

echo("A total of " . $totalSent	. " emails were sent and a total of " . $errorCount . " errors were encountered.");
?>