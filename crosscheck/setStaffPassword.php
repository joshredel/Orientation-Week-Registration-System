<?
/**
 * This tool runs some basic statistics on registrations.
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

// the statistics we want to keep track of

// loop through all participants
$staffs = $staffService->getStaffs();
foreach($staffs as $staff) {
	if($staff->registrationPassword == null) {
		$staff->registrationPassword = md5(time() . $staff->lastName . $staff->email . $staff->displayName . rand());
		$staffService->saveStaff($staff);
	}
}

echo("Complete");
?>