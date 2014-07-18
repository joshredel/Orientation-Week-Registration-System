<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check if they have submitted the payschedule creation form
if(isset($_POST['amount']) && $currentRole->hasPermission(org\fos\Role::$EDIT_PAYSCHEDULE)) {
	// get the information from the form
	$startDate = $_POST['startdateraw'];
	$endDate = $_POST['enddateraw'];
	$amount = $_POST['amount'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($startDate == "") {
		$errorMessage .= "A start date for the schedule.<br />";
	}
	if($endDate == "") {
		$errorMessage .= "An end date for the schedule.<br />";
	}
	if($amount == "" || !is_numeric($amount)) {
		$errorMessage .= "A cost for the schedule.<br />";
	}
	
	// create dates from the raw values
	$timezone = new DateTimeZone('America/Montreal');
	
	$startDate = new DateTime('@' . ($startDate / 1000));
	$startDate->setTimezone($timezone);
	
	$endDate = new DateTime('@' . ($endDate / 1000));
	$endDate->setTimezone($timezone);
	
	// check to make sure it doesn't overlap with an existing payment date
	foreach($currentEvent->costs as $cost) {
		if(datesOverlap($startDate, $endDate, $cost->startDate, $cost->endDate)) {
			$errorMessage .= "A date range that does not overlap with an existing cost.<br />";
			break;
		}
	}
	
	// push the end date to 23:59:59 to cover the entire day on the end date
	$endDate->setTime(23, 59, 59);
	
	// check if there are errors
	if($errorMessage == "") {
		// popualte the current event
		$newCost = new org\fos\Cost();
		$newCost->startDate = $startDate;
		$newCost->endDate = $endDate;
		$newCost->amount = $amount;
		$newCost->event = $currentEvent;
		
		// save it!
		$costService->saveCost($newCost);
		
		// empty out the dates for the form
		$startDate = null;
		$endDate = null;
	} else {
		// generate the error message
		$errorMessage = "The event could not be saved as the form was missing the following:<br />" . $errorMessage;
		
		// empty out the dates for the form
		$startDate = null;
		$endDate = null;
	}
}

// see if they have asked to delete a pay cost
if(isset($_POST['costid']) && $currentRole->hasPermission(org\fos\Role::$EDIT_PAYSCHEDULE)) {
	// ask to delete the user
	$costService->deleteCostById($_POST['costid']);
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
    <script type="text/javascript">
		$(function(){
			// add the date/time pickers for the event start/end dates
			$('#startdate').datepicker();
			$('#enddate').datepicker();
			
			// set the dates to the values from php
			<?
			if($startDate != null) {
				$startDate = $startDate->format('U') * 1000;
				?>
				$('#startdate').datepicker('setDate', (new Date(<?= $startDate ?>)) );
				$('#startdateraw').val($('#startdate').datepicker('getDate').getTime());
				<?
			}
			
			if($endDate != null) {
				$endDate = $endDate->format('U') * 1000;
				?>
				$('#enddate').datepicker('setDate', (new Date(<?= $endDate ?>)) );
				$('#enddateraw').val($('#enddate').datepicker('getDate').getTime());
				<?
			}
			?>
			
			// listen for the entry fields to change so we can pull the milliseconds from them
			$('#startdate').bind('change', function() {
				$('#startdateraw').val($('#startdate').datepicker('getDate').getTime());
			});
			
			$('#enddate').bind('change', function() {
				$('#enddateraw').val($('#enddate').datepicker('getDate').getTime());
			});
		});
	</script>
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
	    if($errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if($successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
       		<?
			// check to see if this user has an event associated to them
			if($currentEvent == null) {
				echo("<article><p>Your user account does not have an event associated to it.</p></article>");
			} elseif(!$currentRole->hasPermission(org\fos\Role::$EDIT_PAYSCHEDULE)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
            <article>
                <header><h1>Payment Schedule</h1></header>
                <table id='paySchedule'>
               		<tr><th>Start</th><th>End</th><th>Amount</th><th>Delete</th></tr>
                	<?
					// get all of the event's costs
					$currentEvent = $eventService->getEvent($currentRole->event->id);
					$costItems = $currentEvent->costs->toArray();
					
					// sort the costs by start date
					function dateCompare($a, $b) { 
						if(sameDate($a->startDate, $b->startDate)) {
							return 0;
						}
						return ($a->startDate->getTimestamp() < $b->startDate->getTimestamp()) ? -1 : 1;
					}
					usort($costItems, 'dateCompare');
					
					// loop through each cost, display it
					foreach($costItems as $costItem) {
						echo("<tr><td>" . formatDate($costItem->startDate) . "</td><td>" . formatDate($costItem->endDate));
						echo("</td><td>" . number_format($costItem->amount, 2) . "$</td>");
						echo("<td><form style='width:50%;' method='post'><input type='hidden' name='costid' value='" . $costItem->id . "' />");
				    	echo("<input type='submit' value='X' /></form></td></tr>");
					}
					?>
                </table>
                <?
				// check that we have a complete pay schedule
				// first check that the first cost is at the first day of registration
				if(count($costItems) > 0) {
					if(!sameDate($costItems[0]->startDate, $currentEvent->registrationOpenDate)) {
						$costError = "There are no costs defined for the opening of registration.";
					}
					
					// now check for a continuous series of dates
					$lastEndDate = $costItems[0]->endDate;
					for($i = 1; $i < count($costItems); $i++) {
						// get the current running cost
						$costItem = $costItems[$i];
						
						// check that this cost's start date is the day after the last end date
						$nextDay = new DateTime(date("Y-m-d", strtotime("+1 day", $lastEndDate->getTimestamp())));
						if(!sameDate($costItem->startDate, $nextDay)) {
							$costError = "There is a time not accounted for.";
							break;
						}
						
						// store the last end date
						$lastEndDate = $costItem->endDate;
					}
					
					// now check that the last cost date is at the end of registration
					if(!sameDate($costItems[sizeof($costItems) - 1]->endDate, $currentEvent->registrationCloseDate)) {
						$costError = "There are no costs defined through to the closing of registration.";
					}
					
					if($costError != "") {
						echo("<div class='error'>$costError</div>");
					}
				}
				?>
            </article>
            <article>
                <header><h1>Add Schedule</h1></header>
                <form method='post'>
                	<label for='startdate'>Start Date:</label>
                	<input type='text' id='startdate' name='startdate' /><br />
                    
                    <label for='enddate'>End Date:</label>
                	<input type='text' id='enddate' name='enddate' /><br />
                    
                    <label for='amount'>Amount:</label>
                	<input type='text' name='amount' /><br />
                    
                	<input type='hidden' id='startdateraw' name='startdateraw' />
                	<input type='hidden' id='enddateraw' name='enddateraw' />
                    <input class='button' type='submit' />
                </form>
            </article>
            <?
			} // end check for the user's permissions
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>