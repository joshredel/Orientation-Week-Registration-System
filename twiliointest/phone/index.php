<?
/**
 * First responder to the voice request from Twilio.
 * 
 * Branches based on the classification of the caller.
 * Searches by phone number in participants first, then staff, then users.
 */
// initialize services
require_once("../../functions.php");
$participantService = new services\ParticipantService();
$staffService = new services\StaffService();
$userService = new services\UserService();
$roleService = new services\RoleService();
$eventService = new services\EventService();
session_start();

// get the phone number
$callerNumber = $_REQUEST['From'];

// see if there is a participant with the received phone number
$caller = $participantService->getParticipantByPhoneNumber($callerNumber);

// check to see if we found a participant
if($caller != null) {
	// we did, so store the caller
	$_SESSION['caller'] = $caller;
	$_SESSION['classification'] = org\fos\User::PARTICIPANT;
	
	// figure out which frosh they are part of
	$froshEventId = null;
	foreach($caller->events as $event) {
		if($event->category == org\fos\Event::FACULTY_FROSH || $event->category == org\fos\Event::NON_FACULTY_FROSH) {
			$froshEventId = $event->id;
			break;
		}
	}
	$_SESSION['froshEventId'] = $froshEventId;
	
	// go to the participant menu
	redirect("/twiliointest/phone/participant/");
} else {
	// we didn't find a participant; see if we can find a staff member
	$caller = $staffService->getStaffByPhoneNumber($callerNumber);
	
	if($caller != null) {
		// store the event ID we are working with
		$caller->event->load();
		$_SESSION['froshEventId'] = $caller->event->id;
		$_SESSION['froshEvent'] = $eventService->getEvent($caller->event->id);
	}
}

// we didn't find a participant or a staff; see if we can find a user
if($caller == null) {
	// store the caller
	$caller = $userService->getUserByPhoneNumber($callerNumber);
	
	if($caller != null) {
		// store the event ID we are working with
		$currentRole = $roleService->getRole($caller->roles[0]->id);
		
		if($currentRole->event == null) {
			$_SESSION['froshEventId'] = null;
			$_SESSION['froshEvent'] = null;
		} else {
			$_SESSION['froshEventId'] = $currentRole->event->id;
			$_SESSION['froshEvent'] = $eventService->getEvent($currentRole->event->id);
		}
	}
}

// go to the general menu if we can't find a staff or a user
if($caller == null) {
	$_SESSION['classification'] = org\fos\User::GENERAL;
	redirect("/twiliointest/phone/general/");
}

// we've found a staff or a user that matches the phone number
// we did, so store the caller
$_SESSION['caller'] = $caller;
$_SESSION['classification'] = $caller->classification;

// go to a menu based on their classification
switch($caller->classification) {
	case org\fos\User::LEADER:
		redirect("/twiliointest/phone/leader/");
		break;
	case org\fos\User::OSTAFF:
		redirect("/twiliointest/phone/ostaff/");
		break;
	case org\fos\User::COORDINATOR:
		redirect("/twiliointest/phone/coord/");
		break;
	case org\fos\User::HEAD_COORDINATOR:
		redirect("/twiliointest/phone/headcoord/");
		break;
	case org\fos\User::ADMINISTRATOR:
	case org\fos\User::COMMUNICATIONS:
	default:
		redirect("/twiliointest/phone/general/");
		break;
}
?>