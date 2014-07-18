<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check if they have submitted the payschedule creation form
if(isset($_POST['title']) && $currentRole->hasPermission(org\fos\Role::$EDIT_PAYSCHEDULE)) {
	// get the information from the form
	$title = $_POST['title'];
	$location = $_POST['location'];
	$startTime = $_POST['startTimeRaw'];
	$endTime = $_POST['endTimeRaw'];
	$ofAgeMarker = $_POST['ofAgeMarker'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($title == "") {
		$errorMessage .= "- An event title.<br />";
	}
	if($startTime == "") {
		$errorMessage .= "- A starting time.<br />";
	}
	if($endTime == "") {
		$errorMessage .= "- An ending time.<br />";
	}
	
	// see if times are wrong
	if($startTime == $endTime) {
		$errorMessage .= "- Start time and end time cannot be the same.<br />";
	}
	if($endTime < $startTime) {
		$errorMessage .= "- End time must be after the start time.<br />";
	}
	if($startTime > $endTime) {
		$errorMessage .= "- Start time must be before the end time.<br />";
	}
	
	// create dates from the raw values
	$timezone = new DateTimeZone('America/Montreal');
	
	$startTime = new DateTime('@' . ($startTime / 1000));
	$startTime->setTimezone($timezone);
	
	$endTime = new DateTime('@' . ($endTime / 1000));
	$endTime->setTimezone($timezone);
	
	// check if there are errors
	if($errorMessage == "") {
		// popualte the current event
		$newCalendarEvent = new org\fos\CalendarEvent();
		$newCalendarEvent->title = (!get_magic_quotes_gpc()) ? addslashes($description) : $title;
		$newCalendarEvent->location = (!get_magic_quotes_gpc()) ? addslashes($location) : $location;
		$newCalendarEvent->startTime = $startTime;
		$newCalendarEvent->endTime = $endTime;
		$newCalendarEvent->ofAgeMarker = $ofAgeMarker;
		$newCalendarEvent->isTemplate = false;
		$newCalendarEvent->event = $currentEvent;
		
		// save it!
		$calendarEventService->saveCalendarEvent($newCalendarEvent);
		$successMessage = "The calendar event was successfully created!";
	} else {
		// generate the error message
		$errorMessage = "The calendar event could not be created as the form was missing the following:<br />" . $errorMessage;
	}
}

// see if they have asked to delete a calendar event
if(isset($_POST['calendarEventIdToDelete']) && $currentRole->hasPermission(org\fos\Role::$EDIT_SCHEDULE)) {
	// ask to delete the user
	$calendarEventService->deleteCalendarEventById($_POST['calendarEventIdToDelete']);
}

if(!$currentEvent->hasSelectableEvents) {
	// this event has a calendar schedule (not multiple options)
	// loop through all events
	$underageEvents = array();
	$ofAgeEvents = array();
	foreach($currentEvent->calendarEvents as $calendarEvent) {
		// see who can go to this event
		
		if($calendarEvent->ofAgeMarker == -1 || $calendarEvent->ofAgeMarker == 0) {
			$underageEvents[] = convertToSimple($calendarEvent);
		}
		if($calendarEvent->ofAgeMarker == 1 || $calendarEvent->ofAgeMarker == 0) {
			$ofAgeEvents[] = convertToSimple($calendarEvent);
		}
	}
	
	// output the JSON version of the events to send
	$underageEventPreview = json_encode($underageEvents);
	$ofAgeEventPreview = json_encode($ofAgeEvents);
} else {
	// this event has multiple options (not a calendar schedule)
}

// converts a database object to a simple object that can be read by the calendar
function convertToSimple($event) {
	$simpleEvent['id'] = $event->id;
	$simpleEvent['title'] = stripslashes($event->title);
	$simpleEvent['start'] = $event->startTime->getTimestamp();
	$simpleEvent['end'] = $event->endTime->getTimestamp();
	$simpleEvent['allDay'] = false;
	$simpleEvent['editable'] = false;
	$simpleEvent['location'] = $event->location;
	$simpleEvent['masterEventId'] = $event->event->id;
	return $simpleEvent;
}

// converts an ofagemarked to printable text
function ofAgeMarkerToPrint($ofAgeMarker) {
	if($ofAgeMarker == 0) {
		return "all ages";
	} elseif ($ofAgeMarker == -1) {
		return "underage";
	} elseif ($ofAgeMarker == 1) {
		return "18+";
	} else {
		return "??";
	}
}

/*
if(isset($_POST['success'])) {
	$successMessage = $_GET['success'];
	unset($_GET['success']);
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../../css/layout.css" />
	<link type="text/css" href="../../css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" />	
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
        <script src="js/IE8.js" type="text/javascript"></script>
    <![endif]-->

    <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" media="all" href="css/ie6.css"/>
    <![endif]-->
	<script type="text/javascript" src="../../js/jquery.js"></script>
	<script type="text/javascript" src="../../js/jquery-ui.js"></script>
	<script type="text/javascript" src="../../js/timepicker.js"></script>
    <!-- start: CSS -->
    <link rel="stylesheet" type="text/css" href="/myweek/assets/lib/fullcalendar/fullcalendar.css">
    <link rel="stylesheet" type="text/css" href='/myweek/assets/lib/fullcalendar/fullcalendar.print.css' media='print'>
    <!-- end: CSS -->

    <!-- start: JS -->
    <script src='/myweek/assets/lib/fullcalendar/fullcalendar.min.js'></script>
    <!-- end: JS -->
</head>
<body>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	   <p><a href="../logout.php">logout</a><br />
    	   <a href="../changepassword.php">change password</a></p>
    	</div>
    	<?
		$file = __FILE__; 
		include("../includes/html/topNav.php");
		include("../includes/html/secondNav.php");
		
		// show messages if need be
	    if(isset($errorMessage) && $errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if(isset($successMessage) && $successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
       		<?
			// check to see if this user has an event associated to them
			if($currentEvent == null) {
				echo("<article><p>Your user account does not have an event associated to it.</p></article>");
			} elseif(!$currentRole->hasPermission(org\fos\Role::$EDIT_SCHEDULE)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
            <article>
            	<?
				if($currentEvent->hasSelectableEvents) {
					echo("<header><h1>Selectable Times</h1></header>");
				} else {
					echo("<header><h1>Calendar Schedule</h1></header>");
				}
				?>
                <table id='paySchedule'>
               		<tr><th>Start</th><th>End</th><th>Location</th><th>Age Group</th><th>Edit/Delete</th></tr>
                	<?
					// get all of the event's calendar events
					//$currentEvent = $eventService->getEvent($currentRole->event->id);
					//$costItems = $currentEvent->costs->toArray();
					$calendarEvents = $currentEvent->calendarEvents;
					
					// sort the costs by start date
					function dateCompare($a, $b) { 
						if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
							return 0;
						}
						return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
					}
					
					$calendarEvents = $calendarEvents->toArray();
					usort($calendarEvents, 'dateCompare');
					
					// loop through each cost, display it
					foreach($calendarEvents as $calendarEvent) {
						// see if there is a note
						$hasNotes = "No notes entered.";
						if($calendarEvent->notes != null && $calendarEvent->notes != "") {
							$hasNotes = "Notes entered; click edit to change.";
						}
						
						echo("<tr><td>" . formatCalendarEventTime($calendarEvent->startTime) . "</td>");
						echo("<td>" . formatCalendarEventTime($calendarEvent->endTime) . "</td>");
						echo("<td><strong>" . toPrettyPrint($calendarEvent->title) . "</strong><br /><em>Location:</em> " . toPrettyPrint($calendarEvent->location) . "<br /><em>Notes:</em> " . $hasNotes . "</td>");
						echo("<td>" . ofAgeMarkerToPrint($calendarEvent->ofAgeMarker) . "</td>");
						echo("<td><form style='width:50%;' method='get' action='editcalendarevent.php'><input type='hidden' name='calendarEventId' value='" . $calendarEvent->id . "' /><input type='submit' value='Edit' /></form>");
						echo("<form style='width:50%;' method='post' onsubmit=\"return alert('Are you sure you want to delete this calendar event?');\"><input type='hidden' name='calendarEventIdToDelete' value='" . $calendarEvent->id . "' /><input type='submit' value='Delete' /></form></td></tr>");
					}
					?>
                </table>
            </article>
            <article>
                <header><h1>Add Calendar Event</h1></header>
                <form method='post'>
                	<label for='title'><strong>Title:</label><br />
                	<input type='text' name='title' style="width:90%" /><br />
                    
                    <label for='location'><strong>Location:</strong></label><br />
                	<input type='text' name='location' style="width:90%" /><br />
                    
                    <label for='startTime'><strong>Start Time:</strong></label><br />
                	<input type='text' id='startTime' name='startTime' /><br />
                    
                    <label for='endTime'><strong>End Time:</strong></label><br />
                	<input type='text' id='endTime' name='endTime' /><br />
                    
                    <label for='ofAgeMarker'><strong>Age Group:</strong></label><br />
                    <select name='ofAgeMarker'>
                    	<option value="0">All Ages</option>
                        <option value="1">18+</option>
                        <option value="-1">Underage Alternative</option>
                    </select><br /><br />
                    
                	<input type='hidden' id='startTimeRaw' name='startTimeRaw' />
                	<input type='hidden' id='endTimeRaw' name='endTimeRaw' />
                    <input class='button' type='submit' value="Create Calendar Event" />
                </form>
            </article>
            <? if(!$currentEvent->hasSelectableEvents) { ?>
            <br /><br /><br />
            
            <audio id="eventReading">
                <source>
            </audio>
            <article>
                <header><h1>Calendar Preview (Of Age)</h1></header>
                <div id='calendarOfAge'></div>
            </article>
            
            <article>
                <header><h1>Calendar Preview (Underage)</h1></header>
                <div id='calendarUnderage'></div>
            </article>
            <? } ?>
            <?
			} // end check for the user's permissions
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
    
    <script>
		$(document).ready(function() {
			<?
			if(count($currentEvent->calendarEvents)) {
				echo("var date = new Date(" . ($currentEvent->calendarEvents[0]->startTime->getTimestamp() * 1000) . ");");
			} else {
				echo("var date = new Date();");
			}
			?>
       		var d = date.getDate();
        	var m = date.getMonth();
        	var y = date.getFullYear();
			<? 
			if(!$currentEvent->hasSelectableEvents) {
				?>
				$('#calendarOfAge').fullCalendar({
					header: {
						left: 'prev,next',
						center: 'title',
						right: 'agendaWeek,agendaDay'
					},
					editable: false,
					defaultView: 'agendaDay',
					allDaySlot: false,
					snapMinutes: 5,
					firstHour: 8,
					eventClick: function(calEvent, jsEvent, view) {
						//whatever is your audio element
						var container = document.getElementById('eventReading'); 
						
						var piecesToSay = new Array();
						piecesToSay.push('https://s3.amazonaws.com/myWeek/EventTitles/' + calEvent.id + '.mp3');
						
						// see if there is a masterevent
						if(calEvent.masterEventId != null && (calEvent.masterEventId == 41 || calEvent.masterEventId == 42 || calEvent.masterEventId == 43 || calEvent.masterEventId == 44 || calEvent.masterEventId == 47)) {
							piecesToSay.push('https://s3.amazonaws.com/myWeek/FroshNames/PartOf.mp3');
							piecesToSay.push('https://s3.amazonaws.com/myWeek/FroshNames/' + calEvent.masterEventId + '.mp3');
						}
						
						// see if there is a location
						if(calEvent.location != null) {
							piecesToSay.push('https://s3.amazonaws.com/myWeek/Menus/LocatedAt.mp3');
							piecesToSay.push('https://s3.amazonaws.com/myWeek/Locations/' + calEvent.id + '.mp3');
						}
						
						//play files in a row
						new Mp3Queue(container, piecesToSay);
					},
					events: <?= $ofAgeEventPreview ?>
				});
				$('#calendarOfAge').fullCalendar('gotoDate', y, m, d);
				
				
				$('#calendarUnderage').fullCalendar({
					header: {
						left: 'prev,next',
						center: 'title',
						right: 'agendaWeek,agendaDay'
					},
					editable: false,
					defaultView: 'agendaDay',
					allDaySlot: false,
					snapMinutes: 5,
					firstHour: 8,
					eventClick: function(calEvent, jsEvent, view) {
						//whatever is your audio element
						var container = document.getElementById('eventReading'); 
						
						var piecesToSay = new Array();
						piecesToSay.push('https://s3.amazonaws.com/myWeek/EventTitles/' + calEvent.id + '.mp3');
						
						// see if there is a masterevent
						if(calEvent.masterEventId != null && (calEvent.masterEventId == 41 || calEvent.masterEventId == 42 || calEvent.masterEventId == 43 || calEvent.masterEventId == 44 || calEvent.masterEventId == 47)) {
							piecesToSay.push('https://s3.amazonaws.com/myWeek/FroshNames/PartOf.mp3');
							piecesToSay.push('https://s3.amazonaws.com/myWeek/FroshNames/' + calEvent.masterEventId + '.mp3');
						}
						
						// see if there is a location
						if(calEvent.location != null) {
							piecesToSay.push('https://s3.amazonaws.com/myWeek/Menus/LocatedAt.mp3');
							piecesToSay.push('https://s3.amazonaws.com/myWeek/Locations/' + calEvent.id + '.mp3');
						}
						
						//play files in a row
						new Mp3Queue(container, piecesToSay);
					},
					events: <?= $underageEventPreview ?>
				});
				$('#calendarUnderage').fullCalendar('gotoDate', y, m, d);
				<?
			}
			?>
			
			// add the date/time pickers for the event start/end dates
			$('#startTime').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			$('#endTime').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			
			// listen for the entry fields to change so we can pull the milliseconds from them
			$('#startTime').bind('change', function() {
				// store the raw value
				$('#startTimeRaw').val($('#startTime').datetimepicker('getDate').getTime());
				
				// restrict the end time
				//$('#endTime').datetimepicker('option', 'minDate', $('#startTime').datetimepicker('getDate'));
			});
			
			$('#endTime').bind('change', function() {
				$('#endTimeRaw').val($('#endTime').datetimepicker('getDate').getTime());
			});
      	});
		
		var Mp3Queue = function(container, files) {
			var index = 1;
			if(!container || !container.tagName || container.tagName !== 'AUDIO')throw 'Invalid container';
			if(!files || !files.length)throw 'Invalid files array';     
			
			var playNext = function() {
				if(index < files.length) {
					container.src = files[index];
					index += 1;
					container.play();
				} else {
					container.removeEventListener('ended', playNext, false);
				}
			};
		
			container.addEventListener('ended', playNext);
		
			container.src = files[0];
			
			container.play();
		};
    </script>
</body>
</html>