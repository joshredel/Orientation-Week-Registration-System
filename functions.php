<?
/**
 * functions.php
 * Containts core database functions for use on the FOS website.
 */

$sitePath = "/home/orientation/html/";
$amfPath = $sitePath . "amfdoctrine/";

// include the Doctrine bootstrapper
require_once($amfPath . '/bootstrapper.php');
/*
// clean data for SQL injection
foreach($_POST as $key=>&$value) {
	$value = mysql_real_escape_string($value);
}
*/
// initialize the current variables
$currentUser = null;
$currentRole = null;
$currentEvent = null;

// services
$checkInService = null;
$costService = null;
$eventService = null;
$participantService = null;
$roleService = null;
$userService = null;

/**
 * Checks to make sure the user has logged in.  Redirects them if they have.
 */
function checkForSession() {
	// start the session
	session_start();
	
	// register loggedin if needed, this checks if they have logged in to the admin area
	if(!isset($_SESSION['currentUser'])) {
	  $_SESSION['currentUser'] = NULL;
	}
	
	// redirect the user if they are not logged in
	if (is_null($_SESSION['currentUser'])) {
		redirect("/admin/login.php");
	}
	
	// prepare the services
	global $checkInService;
	global $costService;
	global $eventService;
	global $participantService;
	global $roleService;
	global $userService;
	global $calendarEventService;
	$checkInService = new services\CheckInService();
	$costService = new services\CostService();
	$eventService = new services\EventService();
	$participantService = new services\ParticipantService();
	$roleService = new services\RoleService();
	$userService = new services\UserService();
	$calendarEventService = new services\CalendarEventService();
	
	// setup 'current' variables from the database
	$_SESSION['currentUser'] = $userService->getUser($_SESSION['currentUser']->id);
	$_SESSION['currentRole'] = $roleService->getRole($_SESSION['currentUser']->roles[0]->id);
	
	if($_SESSION['currentRole']->event == null) {
		$_SESSION['currentEvent'] = null;
	} else {
		$_SESSION['currentEvent'] = $eventService->getEvent($_SESSION['currentRole']->event->id);
	}
	
	// shortcuts
	global $currentUser;
	global $currentRole;
	global $currentEvent;
	$currentUser = $_SESSION['currentUser'];
	$currentRole = $_SESSION['currentRole'];
	$currentEvent = $_SESSION['currentEvent'];
}

/**
 * Checks to make sure the user has logged in.  Redirects them if they have.
 */
function checkForKioskSession() {
	// start the session
	session_start();
	
	// register loggedin if needed, this checks if they have logged in to the admin area
	if(!isset($_SESSION['currentKioskUser'])) {
	  $_SESSION['currentKioskUser'] = NULL;
	}
	
	// redirect the user if they are not logged in
	if (is_null($_SESSION['currentKioskUser'])) {
		redirect("/kiosk/login.php");
	}
	
	// prepare the services
	global $checkInService;
	global $costService;
	global $eventService;
	global $participantService;
	global $roleService;
	global $userService;
	global $calendarEventService;
	$checkInService = new services\CheckInService();
	$costService = new services\CostService();
	$eventService = new services\EventService();
	$participantService = new services\ParticipantService();
	$roleService = new services\RoleService();
	$userService = new services\UserService();
	$calendarEventService = new services\CalendarEventService();
	
	// setup 'current' variables from the database
	$_SESSION['currentKioskUser'] = $userService->getUser($_SESSION['currentKioskUser']->id);
	$_SESSION['currentKioskRole'] = $roleService->getRole($_SESSION['currentKioskUser']->roles[0]->id);
	if($_SESSION['currentKioskRole']->event == null) {
		$_SESSION['currentKioskEvent'] = null;
	} else {
		$_SESSION['currentKioskEvent'] = $eventService->getEvent($_SESSION['currentKioskRole']->event->id);
	}

	// shortcuts
	global $currentKioskUser;
	global $currentKioskRole;
	global $currentKioskEvent;
	$currentKioskUser = $_SESSION['currentKioskUser'];
	$currentKioskRole = $_SESSION['currentKioskRole'];
	$currentKioskEvent = $_SESSION['currentKioskEvent'];
}

/**
 * Redirects the user to another page.
 */
function redirect($location) {
	header("location:$location");
	exit;
}

/**
 * Checks to see if the passed role has the passed permission.
 * Returns true if the role does have the permission.
 */
function roleHasPermission($role, $permissionName) {
	// get a list of the permissions from the role
	$permissions = explode(",", $role->permissions);
	
	// see if the passed permission is part of that collection
	if(in_array($permissionName, $permissions)) {
		return true;
	} else {
		return false;
	}
}

/**
 * Formats a date (no time) for pretty display.
 */
function formatDate($date) {
	return $date->format('l, j F Y');
}

/**
 * Formats a date (no time) for pretty display without day of week.
 */
function formatSimpleDate($date) {
	return $date->format('j F Y');
}

/**
 * Formats a date/time for pretty display.
 */
function formatDateTime($dateTime) {
	return $dateTime->format('l, j F Y \a\t H\hi');
}

/**
 * Formats a date/time for calendar schedule in admin.
 */
function formatCalendarEventTime($dateTime) {
	return $dateTime->format('l (m-d) H\hi');
}

/**
 * Formats a date (no time) for pretty display.
 */
function formatSelectOptionDate($startTime, $endTime) {
	$text = $startTime->format('l, j F - H:i');
	$text .= " to ";
	$text .= $endTime->format('H:i');
	return $text;
}

/**
 * Formats a date for display in a datepicker
 */
function formatForDatepicker($date) {
	return $date->format('Y-m-d');
}

/**
 * Formats a time for reading in Twilio
 */
function formatForReading($date) {
	$minute = (int)($date->format('i'));
	if($minute == 0) {
		return $date->format('g A');
	} elseif($minute > 0 && $minute < 10) {
		$hour = $date->format('g');
		$amPm = $date->format('A');
		return "$hour oh $minute $amPm";
	} else {
		return $date->format('g i A');
	}
}

/**
 * Formats a time for texting in Twilio
 */
function formatForTexting($date) {
	return $date->format('g:i A');
}

/**
 * Formats a date/time for isonumeric display.
 */
function formatIsoDateTime($dateTime) {
	return $dateTime->format('Y-m-j H:i');
}

/**
 * Converts stored text with special characters into readable text.
 */
function toPrettyPrint($original) {
	//return nl2br(stripslashes(htmlentities($original)));
	//return stripslashes(nl2br(htmlentities($original, ENT_QUOTES, 'UTF-8')));
	return nl2br(stripslashes(htmlentities($original, ENT_QUOTES, 'UTF-8' )));
}

/**
 * Converts stored text to text for a textarea.
 */
function toTextareaPrint($original) {
	return stripslashes($original);
}

/**
 * Formatting helper function.
 * Shows a percentage with two decimal points of precision.
 */
function toPercentage($value) {
	return number_format(round($value, 2), 2) . "%";
}

/**
 * Simple helper to make the string 'proper case' (just the first letter capitalized).
 */
function strtoproper($string) {
	//$string = strtolower($string);
	//$string = substr_replace($string, strtoupper(substr($string, 0, 1)), 0, 1);
	//return $string;
	return ucwords($string);
}

function resizeLogo($imageSource, $maxWidth, $maxHeight, $returnHTML="alt='image'", $centerVertically = true) {
	if($sizes=getimagesize($imageSource)) {
		// get the original dimensions
		$originalWidth = $sizes[0];
		$originalHeight = $sizes[1];
		
		// check the dimensions of the source
		if($originalWidth == $originalHeight) {
			// it is square, so resize both sides to the smallest desired size
			if($maxWidth >= $maxHeight) {
				$newWidth = $newHeight = $maxHeight;
			} else {
				$newWidth = $newHeight = $maxWidth;
			}
		} else if($originalWidth < $originalHeight) {
			// it is rectangular with height being the largest dimension
			// set the new height to the desired height
			$newHeight = $maxHeight;
			
			// set the new width to the width proportional to the resize in height
			$newWidth = ($newHeight / $originalHeight) * $originalWidth;
			
			// make sure both dimensions fit
			if($newWidth > $maxWidth) {
				// scale the height down now
				$newWidth = $maxWidth;
				$newHeight = ($newWidth / $originalWidth) * $originalHeight;
			}
		} else {
			// it is rectangular with width being the largest dimension
			// set the width to the desired width
			$newWidth = $maxWidth;
			
			// set the new height to the height proportional to the resize in width
			$newHeight = ($newWidth / $originalWidth) * $originalHeight;
			
			// make sure both dimensions fit
			if($newHeight > $maxHeight) {
				// scale the height down now
				$newHeight = $maxHeight;
				$newWidth = ($newHeight / $originalHeight) * $originalWidth;
			}
		}
		
		// create an image tag
		$newWidth = round($newWidth);
		$newHeight = round($newHeight);
		
		// calculate the top value
		if($centerVertically) {
			$top = ($maxHeight - $newHeight) / 2;
		} else {
			$top = 0;
		}
		return "<img src='$imageSource' width='$newWidth' height='$newHeight' $returnHTML style='margin-top: {$top}px' />";
	} else {
		// something happened getting the image size (no such file or not an image)
		return false;
	}
}

/**
 * Determines if two dates overlap.
 */
function datesOverlap($first_start, $first_end, $second_start, $second_end) {
	$firstStartString = $first_start->getTimestamp();
	$firstEndString = $first_end->getTimestamp();
	$secondStartString = $second_start->getTimestamp();
	$secondEndString = $second_end->getTimestamp();
	
	return !(
		($firstStartString < $secondStartString && $firstEndString < $secondStartString)
		||
		($firstStartString > $secondEndString && $firstEndString > $secondEndString)
	);
}

/**
 * Compares two dates.  Returns true if they are on the same day, month, and year.
 */
function sameDate($firstDate, $secondDate) {
	$firstString = date("Y-m-d", $firstDate->getTimestamp());
	$secondString = date("Y-m-d", $secondDate->getTimestamp());
	
	if($firstString == $secondString) {
		return true;
	} else {
		return false;
	}
}

/**
 * Similar to in_array, except compares IDs of elements.
 * Returns true if needle is in haystack, false otherwise.
 */
function inDoctrineArray($needle, $haystack) {
	// loop through each item in the haystack
	foreach($haystack as $item) {
		if($item->id == $needle->id) {
			return true;
		}
	}
	
	// if we reach here, no match found
	return false;
}

/**
 * Checks to see if the passed email address is a valid address.
 */
function checkEmail($email) {
	if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)) {
		list($username,$domain)=explode('@',$email);
    	if(!checkdnsrr($domain,'MX')) {
      		return false;
    	}
    	return true;
  	}
  	return false;
}

/**
 * Converts a two-character faculty code into the full faculty name.
 * Returns the full name.
 */
function convertCodeToFaculty($code) {
	switch($code) {
		case "AG":
			return "Faculty of Agriculture / Environmental Science";
			break;
		case "AR":
			return "Faculty of Arts";
			break;
		case "AS":
			return "Faculty of Arts & Science";
			break;
		case "DE":
			return "Faculty of Dentistry";
			break;
		case "ED":
			return "Faculty of Education";
			break;
		case "EN":
			return "Faculty of Engineering";
			break;
		case "LW":
			return "Faculty of Law";
			break;
		case "MD":
			return "Faculty of Medicine";
			break;
		case "MG":
			return "Desautels Faculty of Management";
			break;
		case "MU":
			return "Schulich School of Music";
			break;
		case "NU":
			return "School of Nursing";
			break;
		case "PO":
			return "School of Physical & Occupational Therapy";
			break;
		case "RS":
			return "Faculty of Religious Studies";
			break;
		case "SC":
			return "Faculty of Science";
			break;
		default:
			return $code;
	}
}

/**
 * Converts the database storage version of living style into a sentance-worthy version.
 * Returns the full name.
 */
function convertCodeToLivingStyle($code) {
	switch($code) {
		case "InRez":
			return "in a McGill residence";
			break;
		case "OffCampus":
			return "off campus";
			break;
		default:
			return $code;
	}
}

/**
 * Converts the database storage version of the place of origin into a display name.
 */
function convertCodeToOrigin($code) {
	switch($code) {
		case "Quebec":
			return "Quebec";
			break;
		case "RestOfCanada":
			return "rest of Canada";
			break;
		case "International":
			return "abroad";
			break;
		default:
			return $code;
	}
}

/**
 * Converts the database storage version of categories into their desired display names.
 */
function convertCategoryToDisplay($category) {
	switch($category) {
		case org\fos\Event::REZ_FEST:
			return "Rez &amp; Off-Campus Fests";
			break;
		case org\fos\Event::DISCOVER_MCGILL:
			return "Discover McGill &amp; Engage McGill";
			break;
		case org\fos\Event::ACADEMIC_EXPECTATIONS:
			return "Discover McGill: Academic Expectations Events";
			break;
		case org\fos\Event::A_LA_CARTE:
			return "\"&Agrave; la carte\" Events";
			break;
		case org\fos\Event::ORIENTATION_CENTRE:
			return "Orientation Resource Centre and Drop-In Events";
			break;
		case org\fos\Event::DROP_IN:
			return "Drop-In Events";
			break;
		case org\fos\Event::FACULTY_FROSH:
			return "Faculty Froshes";
			break;
		case org\fos\Event::NON_FACULTY_FROSH:
			return "Non-Faculty Froshes";
			break;
		case org\fos\Event::ORANGE_EVENT:
			return "ORANGE Events";
			break;
		case org\fos\Event::OOHLALA:
			return "OOHLALA Discover My Campus";
			break;
		case org\fos\Event::PLUS_EVENT:
			return "Orientation PLUS Events";
			break;
		case org\fos\Event::INTERNATIONAL:
			return "International Student Events";
			break;
		default:
			return $category;
	}
}

/**
 * Converts the database storage version of display type into colouring information.
 */
function convertDisplayToColour($displayType) {
	switch($displayType) {
		case org\fos\Event::DISPLAY_STANDARD:
			return "#00A779";
			break;
		case org\fos\Event::DISPLAY_DONT_MISS:
			return "#00A779";
			break;
		case org\fos\Event::DISPLAY_FROSH:
			return "#FF9900";
			break;
		case org\fos\Event::DISPLAY_DROP_BY:
			return "#1F1AB2";
			break;
		default:
			return "green";
	}
}

/**
 * Converts the database storage version of display type into colouring information.
 */
function convertRezToDisplay($rez) {
	switch($rez) {
		case "McConnell":
			return "McConnell Hall";
			break;
		case "Molson":
			return "Molson Hall";
			break;
		case "Gardner":
			return "Gardner Hall";
			break;
		case "RVC":
			return "Royal Victoria College (RVC)";
			break;
		case "NewRez":
			return "New Residence Hall";
			break;
		case "Carrefour":
			return "Carrefour Sherbrooke";
			break;
		case "Solin":
			return "Solin Hall";
			break;
		case "Citadelle":
			return "La Citadelle";
			break;
		case "Greenbriar":
			return "Greenbriar Apartments";
			break;
		case "MORE":
			return "MORE Houses (inc. University Hall and Pres Rez)";
			break;
		case "Varcity515":
			return "Varcity515";
			break;
		default:
			return $rez;
	}
}

/**
 * Checks to see if a step has been completed in registration and prints the appropriate breadcrumb.
 */
function printBreadcrumbLinkIfComplete($step, $link, $title) {
	// check to see if the step has been completed
	if(isset($_SESSION[$step . 'Complete']) && $_SESSION[$step . 'Complete'] == true) {
		// the step has been completed, print the link
		echo("<a href=\"" . $link . ".php\">" . $title . "</a>");
	} else {
		// the step is not done, just print the title
		echo($title);
	}
}

/**
 * Chooses the proper way to print a participants first name
 */
function getDisplayName($participant) {
	if($participant->preferredName != null && strlen($participant->preferredName)) {
		return $participant->preferredName;
	} else {
		return $participant->firstName;
	}
}

/**
 * Makes a call to Google's TTS engine to improve voice in Twilio.
 * Returns the URL to insert into the <play> verb.
 */
function getTTSLink($message) {
	return "http://translate.google.com/translate_tts?ie=UTF-8&tl=en-ca&q=" . urlencode($message);
}

/**
 * Sets API information for Twilio.
 */
function setupTwilio() {
	session_start();
	
	// the API information
	$sid = "AC64aea60ecd667ddb260c9a89adc0e2d0"; 
	$token = "815ac8b5450cbd1fa78b9e2f1f2c8957"; 
	global $client;
	$client = new Services_Twilio($sid, $token);
	
	// a collection of all numbers
	global $twilioNumbers;
	$twilioNumbers = array("+15147001712", "+15147001713", "+15147001714", "+15147001716", "+15147001775", "+15147001785", "+15147001786", "+15147001796", "+15147001902", "+15147001904", "+15147001916", "+15147001917", "+15147001918", "+15147001923", "+15147001924", "+15147001925", "+15147001936", "+15147001937", "+15147001943", "+15147001946");
	
	// global counter for iterating through twilio numbers
	global $twilioIterator;
	$twilioIterator = 0;
}

function setupTwilioTest() {
	session_start();
	
	// the API information
	$sid = "AC42871e9a58da22d0d8add7db66624fe6"; 
	$token = "08d13a3d74925b8a976d37fe9333e7ca"; 
	global $client;
	$client = new Services_Twilio($sid, $token);
	
	// a collection of all numbers
	global $twilioNumbers;
	$twilioNumbers = array("+15147001712", "+15147001713", "+15147001714", "+15147001716", "+15147001775", "+15147001785", "+15147001786", "+15147001796", "+15147001902", "+15147001904", "+15147001916", "+15147001917", "+15147001918", "+15147001923", "+15147001924", "+15147001925", "+15147001936", "+15147001937", "+15147001943", "+15147001946");
	
	// global counter for iterating through twilio numbers
	global $twilioIterator;
	$twilioIterator = 0;
}

/**
 * Gets the next number in the series of Twilio numbers.
 */
function nextNumber() {
	global $twilioNumbers;
	global $twilioIterator;
	
	// get the current number
	$numberToReturn = $twilioNumbers[$twilioIterator];
	
	// increase the iterator and loop if necessary
	$twilioIterator++;
	if($twilioIterator >= count($twilioNumbers)) {
		$twilioIterator = 0;
	}
	
	return $numberToReturn;
}
?>
