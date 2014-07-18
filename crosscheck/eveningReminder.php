<?
/**
 * This tool sends a reminder email to all students with events happening on a certain date..
 */
// requre the functions
require_once('../functions.php');

// check the API key provided
$apiKey = $_REQUEST['api'];
if($apiKey != '29ed05022d4bfb3ae3738b302bbea19b872870a5') {
	redirect("/");
}

// initialize services
$participantService = new services\ParticipantService();

// payment status counters
$totalSent = 0;
$errorCount = 0;

				
// sort the costs by start date
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
}

// determine whether or not they will be of age during a given date/time
function ofAgeForEvent($participant, $eventDate) {
	$eventDate = $eventDate->getTimestamp();
	$birthDate = $participant->dateOfBirth->getTimestamp();
	
	$eventMonth = date('n', $eventDate);
	$eventDay = date('j', $eventDate);
	$eventYear = date('Y', $eventDate);
	
	$birthMonth = date('n', $birthDate);
	$birthDay = date('j', $birthDate);
	$birthYear = date('Y', $birthDate);
	
	if(($eventMonth >= $birthMonth && $eventDay >= $birthDay) || ($eventMonth > $birthMonth)) {
		$futureAge = $eventYear - $birthYear;
	} else  {
		$futureAge = $eventYear - $birthYear - 1;
	}
	
	// see if they will be underage
	if($futureAge < 18) {
		// underage!
		return false;
	} else {
		// they will be of age
		return true;
	}
}

function tryNext($nextId) {
	$totalParticipants = 5309;
	if($nextId <= $totalParticipants) {
		echo("READYFORNEXT");
	} else {
		echo("FINISHED");
	}
	exit();
}


// get the participant we are sending to
if(!isset($_REQUEST['nextId'])) {
	redirect("/");
}
$nextId = (int)$_REQUEST['nextId'];

$participant = $participantService->getParticipant($nextId);
if($participant == null) {
	tryNext($nextId);
}

// loop through all participants
//$participants = $participantService->getParticipants();
//foreach($participants as $participant) {
try {
	if($participant->sentNightlyReminder) {
		tryNext($nextId);
	}
	if($participant->lastName != "Redel") {
		//tryNext($nextId);
	}
	
	// create the message
	$message = "Dear " . getDisplayName($participant) . ",\n\n";
	$message .= "For those of you who started your froshes today, we hope you have (or are currently having) a great time!  Don't forget that you can always call the myWeek Gateway to get in touch with safety services, find out where you should be, leave feedback for your event organizers, leave anonymous feedback about how you or others are froshing, and contact your leaders at 514-900-0125.  Your myWeek page has also been updated to show you your leaders and their phone numbers if they added you on their myWeek page.  If they haven't, encourage them to do so!  To see what you have in store for Friday, then check your myWeek page below:\n\n http://orientation.ssmu.mcgill.ca/myweek/?passkey=" . $participant->registrationPassword . "\n\nDon’t forget that you can sync your myWeek calendar to your Outlook, iCal, iPhone, and certain other smartphones. Go to the Calendar tab and click the “sync” button near the top of the calendar. We hope this helps you better know what you are up to this week!\n\n";
	
	if($participant->phoneNumber == null || $participant->phoneNumber == "") {
		$message .= "Also, we have noted that we do not yet have your phone number on file. We are launching myWeek Gateway for Frosh, and to be the most useful, it requires your phone number. myWeek Gateway is a powerful phone, texting, and web service that will allow you to communicate with services across campus, to connect with your event organizers, and to leave feedback on your experience during the week. This will be most important during your frosh event, if you are doing one! Please take a few seconds to enter your cell phone number on your account.\n\n";
	}
	
	$message .= "Finally, if you have a smartphone, be sure to bookmark your myWeek page so that you have easy access throughout the week!  If you do not have a smartphone, myWeek Gateway will allow you to keep informed about your week via a phone call or text messaging.  More information will come soon!\n\nHere is your event digest for Friday, August 30. Please take note since some locations have changed:\n\n";
	
	// loop through all of their events and see if there is one "tomorrow" aka the date range set below
	$startRange = new DateTime("2013-08-30 00:00:00");
	$endRange = new DateTime("2013-08-30 23:59:59");
	$printedEvents = false;
	
	foreach($participant->events as $event) {
		// display calendar events for events that have a schedule of calendar events
		if(!$event->hasSelectableEvents) {
			$calendarEvents = $event->calendarEvents;
			$calendarEvents = $calendarEvents->toArray();
			usort($calendarEvents, 'dateCompare');
			foreach($calendarEvents as $calendarEvent) {
				// THREE CASES:
				// 1- event is only during one day
				// 2- event spans multiple days, starting on the current day
				// 3- event spans multiple days, ending on the current day
				$canDisplayForParticipant = false;
				if($calendarEvent->startTime >= $startRange && $calendarEvent->startTime <= $endRange || 
				   $calendarEvent->endTime >= $startRange && $calendarEvent->endTime <= $endRange || 
				   $calendarEvent->startTime <= $startRange && $calendarEvent->endTime >= $endRange) {
					$canDisplayForParticipant = false;
					if($calendarEvent->ofAgeMarker == 0) {
						// we can display; it's for all ages
						$canDisplayForParticipant = true;
					} elseif($calendarEvent->ofAgeMarker == 1) {
						// we need to make sure they are of age for this event
						if(ofAgeForEvent($participant, $calendarEvent->startTime)) {
							$canDisplayForParticipant = true;
						}
					} elseif($calendarEvent->ofAgeMarker == -1) {
						// they need to be underage to see this event (it is an underage alternative
						if(!ofAgeForEvent($participant, $calendarEvent->startTime)) {
							$canDisplayForParticipant = true;
						}
					}
				}
				
				if($canDisplayForParticipant) {
					// print the digest
					$message .= "Event: " . stripslashes($calendarEvent->title) . "\n";
					$message .= "Start: " . formatDateTime($calendarEvent->startTime) . "\n";
					$message .= "End: " . formatDateTime($calendarEvent->endTime) . "\n";
					if($calendarEvent->location != null && $calendarEvent->location != "") {
						$message .= "Location: " . stripslashes($calendarEvent->location) . "\n";
					}
					if($calendarEvent->notes != null && $calendarEvent->notes != "") {
						$message .= "Notes: " . stripslashes($calendarEvent->notes) . "\n";
					}
					$message .= "\n";
					$printedEvents = true;
				}
			}
		}
	}
	
	// loop through all of their personal events
	foreach($participant->personalEvents as $personalEvent) {
		if($personalEvent->calendarEvent != null) {
			// display the calendar event info instead of the personal event (it's a placeholder for a selection)
			$personalEvent->calendarEvent->load();
			
			$canDisplayForParticipant = false;
			if($personalEvent->calendarEvent->startTime >= $startRange && $personalEvent->calendarEvent->startTime <= $endRange || 
			   $personalEvent->calendarEvent->endTime >= $startRange && $personalEvent->calendarEvent->endTime <= $endRange || 
			   $personalEvent->calendarEvent->startTime <= $startRange && $personalEvent->calendarEvent->endTime >= $endRange) {
				$canDisplayForParticipant = true;
			}
			
			if($canDisplayForParticipant) {
				// print the digest
				$message .= "Event: " . stripslashes($personalEvent->calendarEvent->title) . "\n";
				$message .= "Start: " . formatDateTime($personalEvent->calendarEvent->startTime) . "\n";
				$message .= "End: " . formatDateTime($personalEvent->calendarEvent->endTime) . "\n";
				if($personalEvent->calendarEvent->location != null && $personalEvent->calendarEvent->location != "") {
					$message .= "Location: " . stripslashes($personalEvent->calendarEvent->location) . "\n";
				}
				if($personalEvent->calendarEvent->notes != null && $personalEvent->calendarEvent->notes != "") {
					$message .= "Notes: " . stripslashes($personalEvent->calendarEvent->notes) . "\n";
				}
				$message .= "\n";
				$printedEvents = true;
			}
		} else {
			$canDisplayForParticipant = false;
			if($personalEvent->startTime >= $startRange && $personalEvent->startTime <= $endRange || 
			   $personalEvent->endTime >= $startRange && $personalEvent->endTime <= $endRange || 
			   $personalEvent->startTime <= $startRange && $personalEvent->endTime >= $endRange) {
				$canDisplayForParticipant = true;
			}
			
			if($canDisplayForParticipant) {
				// print the digest
				$message .= "Event: " . stripslashes($personalEvent->title) . "\n";
				$message .= "Start: " . formatDateTime($personalEvent->startTime) . "\n";
				$message .= "End: " . formatDateTime($personalEvent->endTime) . "\n";
				if($personalEvent->location != null && $personalEvent->location != "") {
					$message .= "Location: " . stripslashes($personalEvent->location) . "\n";
				}
				if($personalEvent->notes != null && $personalEvent->notes != "") {
					$message .= "Notes: " . stripslashes($personalEvent->notes) . "\n";
				}
				$message .= "\n";
				$printedEvents = true;
			}
		}
	}
	
	$message .= "Sincerely,\nThe Orientation Week Team";
	
	// mark that we have sent them a message
	$participant->sentNightlyReminder = true;
	$participantService->saveParticipant($participant);
	
	if($printedEvents) {
		$mailResult = mail($participant->email, "[McGill Orientation Week] Your nightly myWeek update!", $message, "From: McGill Orientation Communications Team <orientation@ssmu.mcgill.ca>");
		//echo("Sent mail for user: " . $participant->id . "<br />");
		echo("SENT");
		if($mailResult) {
			// increase our sent count
			$totalSent++;
		} else {
			// list errors
			$errorCount++;
			echo("Failed to be accepted for delivery: " . $participant->email . "<br />");
		}
	}
} catch(Exception $e) {
	tryNext($nextId);
}
//}

tryNext($nextId);
//echo("A total of " . $totalSent	. " emails were sent and a total of " . $errorCount . " errors were encountered.");
?>