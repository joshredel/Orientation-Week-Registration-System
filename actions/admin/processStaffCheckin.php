<?
// handle posted checkin data
require("../../functions.php");

checkForSession();
$staffService = new services\StaffService();

// make sure all posted data is valid, else redirect to checkin.php
if (!isset($_POST['passkey'])) {
	$message = "No changes have been made, some required data was not posted.";
	redirect("/admin/staff/index.php?error=$message");
}

// check for valid student
$staff = $staffService->getStaffByRegistrationPassword($_POST['passkey']);
if ($staff == null) {
	$message = "No changes have been made, invalid staff posted.";
	redirect("/admin/staff/index.php?error=$message");
}

// check for a valid user /w permissions
if (!$currentRole->hasPermission(org\fos\Role::$CHECK_IN_PARTICIPANTS)) {
	$message = "No changes have been made, invalid user permissions.";
	redirect("/admin/staff/index.php?error=$message");
}

// get today
$today = new DateTime("now", new DateTimeZone("America/Montreal"));

// Fill in fields
$staff->checkInDate = $today;

// mark as paid/unpaid
if (isset($_POST['paid'])) {
	$staff->hasPaid = $_POST['paid'];
} else {
	$staff->hasPaid = 0;
}

// mark as checked in for faculty
if (isset($_POST['checkedInFaculty'])) {
	$staff->checkedInFaculty = $_POST['checkedInFaculty'];
} else {
	$staff->checkedInFaculty = 0;
}

// mark as checked in for ssmu
if (isset($_POST['checkedInSSMU'])) {
	$staff->checkedInSsmu = $_POST['checkedInSSMU'];
} else {
	$staff->checkedInSsmu = 0;
}

// see if the bracelet number changed
$newBraceletNumber = (isset($_POST['braceletNumberSsmu']) ? $_POST['braceletNumberSsmu'] : null);
if($staff->braceletNumberSsmu != null && $staff->braceletNumberSsmu != $newBraceletNumber) {
	// store the old ID
	if($staff->pastBraceletNumbersSsmu != null && $staff->pastBraceletNumbersSsmu != "") {
		$oldBracelets = explode(",", $staff->pastBraceletNumbersSsmu);
	} else {
		$oldBracelets = array();
	}
	$oldBracelets[] = $staff->braceletNumberSsmu;
	$staff->pastBraceletNumbersSsmu = implode(",", $oldBracelets);
}

// save the new bracelet
$staff->braceletNumberSsmu = $newBraceletNumber;

// see if the bracelet number changed
$newBraceletNumber = (isset($_POST['braceletNumberFaculty']) ? $_POST['braceletNumberFaculty'] : null);
if($staff->braceletNumberFaculty != null && $staff->braceletNumberFaculty != $newBraceletNumber) {
	// store the old ID
	if($staff->pastBraceletNumbersFaculty != null && $staff->pastBraceletNumbersFaculty != "") {
		$oldBracelets = explode(",", $staff->pastBraceletNumbersFaculty);
	} else {
		$oldBracelets = array();
	}
	$oldBracelets[] = $staff->braceletNumberFaculty;
	$staff->pastBraceletNumbersFaculty = implode(",", $oldBracelets);
}

// save the new bracelet
$staff->braceletNumberFaculty = $newBraceletNumber;

// see if the user ID changes
if($currentUser->id != $staff->userId) {
	// store the old ID
	if($staff->pastUserIds != null && $staff->pastUserIds != "") {
		$oldIds = explode(",", $staff->pastUserIds);
	} else {
		$oldsIds = array();
	}
	$oldIds[] = $staff->userId;
	$staff->pastUserIds = implode(",", $oldIds);
	
	// save the new ID
	$staff->userId = $currentUser->id;
}

// store any additional participant information
// get the phone number
$rawPhone = $_POST['phone'];

// strip any of the accepted non-numeric characters (space, -, .)
$rawPhone = str_ireplace("-", "", $rawPhone);
$rawPhone = str_ireplace(" ", "", $rawPhone);
$rawPhone = str_ireplace(".", "", $rawPhone);

// if it is only 10 digits, add the +1
if(strlen($rawPhone) == 10) {
	$rawPhone = "+1" . $rawPhone;
}

// and make sure it has a + at the beginning
if(strlen($rawPhone) > 0 && $rawPhone[0] != '+') {
	$rawPhone = "+" . $rawPhone;
}

// store it
$staff->phoneNumber = $rawPhone;

// store the student ID
$staff->studentId = $_POST['studentId'];

// store the email
$staff->email = $_POST['email'];

echo("Done");
// save any changes we've made to the participant
$staffService->saveStaff($staff);

$_SESSION['checkInSuccess'] = "Staff with ID " . $staff->studentId . " has been successfully checked in!";

// go back once we're done!
redirect("/admin/staff/index.php");
?>