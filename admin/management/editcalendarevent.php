<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get the calendar event that we are editing
$calendarEvent = $calendarEventService->getCalendarEvent($_REQUEST['calendarEventId']);

// check if they have submitted the payschedule creation form
if(isset($_POST['title']) && $currentRole->hasPermission(org\fos\Role::$EDIT_PAYSCHEDULE)) {
	// get the information from the form
	$title = $_POST['title'];
	$location = $_POST['location'];
	$startTime = $_POST['startTimeRaw'];
	$endTime = $_POST['endTimeRaw'];
	$ofAgeMarker = $_POST['ofAgeMarker'];
	$notes = $_POST['notes'];
	
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
	if($endTime < $starTime) {
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
		$calendarEvent->title = (!get_magic_quotes_gpc()) ? addslashes($title) : $title;
		$calendarEvent->location = (!get_magic_quotes_gpc()) ? addslashes($location) : $location;
		$calendarEvent->startTime = $startTime;
		$calendarEvent->endTime = $endTime;
		$calendarEvent->ofAgeMarker = $ofAgeMarker;
		$calendarEvent->isTemplate = false;
		$calendarEvent->event = $currentEvent;
		$calendarEvent->notes = (!get_magic_quotes_gpc()) ? addslashes($notes) : $notes;
		
		// save it!
		$calendarEventService->saveCalendarEvent($calendarEvent);
		$successMessage = "The calendar event was successfully created!";
		redirect("schedule.php");//?success=" . urlencode($successMessage));
	} else {
		// generate the error message
		$errorMessage = "The calendar event could not be created as the form was missing the following:<br />" . $errorMessage;
	}
}

// see if they have asked to delete a calendar event
if(isset($_POST['calendarEventIdToDelete']) && $currentRole->hasPermission(org\fos\Role::$EDIT_SCHEDULE)) {
	// ask to delete the user
	$calendarEventService->deleteCalendarEventById($_POST['calendarEventId']);
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

// a function that will check if the passed variable matched the value to determine if a select item should be selected
function checkForSelected($toCheck, $desiredMatch) {
	if($toCheck == $desiredMatch) {
		return "selected";
	} else {
		return "";
	}
}
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
            	<a href="schedule.php">back to Calendar Schedule</a>
                <br /><br />
                <header><h1>Add Calendar Event</h1></header>
                <form method='post'>
                	<label for='title'><strong>Title*:</label><br />
                	<input type='text' name='title' style="width:90%" value="<?= toPrettyPrint($calendarEvent->title) ?>" /><br />
                    
                    <label for='location'><strong>Location:</strong></label><br />
                	<input type='text' name='location' style="width:90%" value="<?= toPrettyPrint($calendarEvent->location) ?>" /><br />
                    
                    <label for='startTime'><strong>Start Time*:</strong></label><br />
                	<input type='text' id='startTime' name='startTime' /><br />
                    
                    <label for='endTime'><strong>End Time*:</strong></label><br />
                	<input type='text' id='endTime' name='endTime' /><br />
                    
                    <label for='ofAgeMarker'><strong>Age Group*:</strong></label><br />
                    <select name='ofAgeMarker'>
                    	<option value="0" <?= checkForSelected($calendarEvent->ofAgeMarker, 0) ?>>All Ages</option>
                        <option value="1" <?= checkForSelected($calendarEvent->ofAgeMarker, 1) ?>>18+</option>
                        <option value="-1" <?= checkForSelected($calendarEvent->ofAgeMarker, -1) ?>>Underage Alternative</option>
                    </select><br /><br />
                    
                    <b>Notes:</b><br />
                	<textarea name='notes' style="width:100%" cols='100' rows='10'><?= $calendarEvent->notes == null ? $location : toTextareaPrint($calendarEvent->notes) ?></textarea><br />
                    
                    <input type="hidden" id="calendarEventId" name="calendarEventId" value="<?= $calendarEvent->id ?>" />
                	<input type='hidden' id='startTimeRaw' name='startTimeRaw' />
                	<input type='hidden' id='endTimeRaw' name='endTimeRaw' />
                    <input class='button' type='submit' value="Save Calendar Event" />
                </form>
            </article>
            <?
			} // end check for the user's permissions
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
    
    <script>
		$(document).ready(function() {
			// add the date/time pickers for the event start/end dates
			$('#startTime').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			$('#endTime').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			
			// set the dates to the values from php
			<?
			if($calendarEvent->startTime != null) {
				$tempDate = $calendarEvent->startTime->format('U') * 1000;
				?>
				$('#startTime').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#startTimeRaw').val($('#startTime').datepicker('getDate').getTime());
				<?
			}
			
			if($calendarEvent->endTime != null) {
				$tempDate = $calendarEvent->endTime->format('U') * 1000;
				?>
				$('#endTime').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#endTimeRaw').val($('#endTime').datepicker('getDate').getTime());
				<?
			}
			?>
			
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
    </script>
</body>
</html>