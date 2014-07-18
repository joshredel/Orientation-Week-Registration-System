<?
require_once('../../functions.php');

// check for a session
checkForSession();

// redirect if they do not have permission to be here
if(!$currentRole->hasPermission(org\fos\Role::$EDIT_EVENT)) {
	// the user does not have permissions
	redirect(".");
}

// check if they have submitted the event creation form
if(isset($_POST['description'])) {
	// get the information from the form
	$description = $_POST['description'];
	//$startDate = $_POST['startdateraw'];
	//$endDate = $_POST['enddateraw'];
	//$location = $_POST['location'];
	//$paypalBusiness = $_POST['paypalbusiness'];
	//$openDate = $_POST['opendateraw'];
	//$closeDate = $_POST['closedateraw'];
	//$participantCap = $_POST['participantcap'];
	$website = $_POST['website'];
	$email = $_POST['email'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	/*
	if($openDate == "") {
		$errorMessage .= "An open date for registration.<br />";
	}
	if($closeDate == "") {
		$errorMessage .= "A close date for registration.<br />";
	}
	if($paypalBusiness == "") {
		$errorMessage .= "A PayPal e-mail address for the event.<br />";
	}
	if($participantCap == "") {
		$errorMessage .= "A participant cap for the event.<br />";
	}
	*/
	if($description == "") {
		$errorMessage .= "A description for the event.<br />";
	}
	/*
	if($startDate == "") {
		$errorMessage .= "A start date for the event.<br />";
	}
	if($endDate == "") {
		$errorMessage .= "An end date for the event.<br />";
	}
	if($location == "") {
		$location .= "A description for the event.<br />";
	}
	*/
	if($website == "") {
		$errorMessage .= "A website for the event.<br />";
	}
	/*
	if($email == "") {
		$errorMessage .= "An email for the event.<br />";
	}
	*/
	
	// create dates from the raw values
	//$timezone = new DateTimeZone('America/Montreal');
	
	//$startDate = new DateTime('@' . ($startDate / 1000));
	//$startDate->setTimezone($timezone); 
	
	//$endDate = new DateTime('@' . ($endDate / 1000));
	//$endDate->setTimezone($timezone); 
	
	//$openDate = new DateTime('@' . ($openDate / 1000));
	//$openDate->setTimezone($timezone); 
	
	//$closeDate = new DateTime('@' . ($closeDate / 1000));
	//$closeDate->setTimezone($timezone); 
	//$closeDate->setTime(23, 59, 59);
	
	// check if there are errors
	if($errorMessage == "") {
		// popualte the current event
		$currentEvent->description = (!get_magic_quotes_gpc()) ? addslashes($description) : $description;
		//$currentEvent->startDate = $startDate;
		//$currentEvent->endDate = $endDate;
		//$currentEvent->location = (!get_magic_quotes_gpc()) ? addslashes($location) : $location;
		//$currentEvent->paypalBusiness = $paypalBusiness;
		//$currentEvent->registrationOpenDate = $openDate;
		//$currentEvent->registrationCloseDate = $closeDate;
		//$currentEvent->participantCap = $participantCap;
		$currentEvent->website = $website;
		$currentEvent->email = $email;
		
		// save the event to the database
		$currentRole->event = $currentEvent;
		$roleService->saveRole($currentRole);
		
		// notify of success
		redirect("/admin/management");
	} else {
		// generate the error message
		$errorMessage = "The event could not be saved as the form was missing the following:<br />" . $errorMessage;
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
    <script type="text/javascript">
		$(function(){
			/*
			// add the date/time pickers for the event start/end dates
			$('#startdate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			$('#enddate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at '
			});
			
			// add the date pickers for the registration open/close dates
			$('#opendate').datepicker({
				minDate: new Date(2012, 5, 20),
				maxDate: new Date(2012, 8, 5)
			});
			$('#closedate').datepicker({
				minDate: new Date(2012, 5, 20),
				maxDate: new Date(2012, 8, 5)
			});
			
			// set the dates to the values from php
			<?
			if($currentEvent->startDate != null) {
				$tempDate = $currentEvent->startDate;
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#startdate').datetimepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#startdateraw').val($('#startdate').datetimepicker('getDate').getTime());
				<?
			}
			
			if($currentEvent->endDate != null) {
				$tempDate = $currentEvent->endDate;
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#enddate').datetimepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#enddateraw').val($('#enddate').datetimepicker('getDate').getTime());
				<?
			}
			
			if($currentEvent->registrationOpenDate != null) {
				$tempDate = $currentEvent->registrationOpenDate;
				$tempDate = $tempDate->format('U') * 1000;
				?>
				//$('#opendate').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				//$('#opendateraw').val($('#opendate').datepicker('getDate').getTime());
				<?
			}
			
			if($currentEvent->registrationCloseDate != null) {
				$tempDate = $currentEvent->registrationCloseDate;
				$tempDate = $tempDate->format('U') * 1000;
				?>
				//$('#closedate').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				//$('#closedateraw').val($('#closedate').datepicker('getDate').getTime());
				<?
			}
			?>
			
			// listen for the entry fields to change so we can pull the milliseconds from them
			$('#startdate').bind('change', function() {
				$('#startdateraw').val($('#startdate').datetimepicker('getDate').getTime());
			});
			
			$('#enddate').bind('change', function() {
				$('#enddateraw').val($('#enddate').datetimepicker('getDate').getTime());
			});
			
			
			$('#opendate').bind('change', function() {
				$('#opendateraw').val($('#opendate').datepicker('getDate').getTime());
			});
			
			$('#closedate').bind('change', function() {
				$('#closedateraw').val($('#closedate').datepicker('getDate').getTime());
			});
			*/
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
		//include("../includes/html/secondNav.php");
		
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
			if(true) { // eventually check for edit priveleges here
			?>
            <form method='post'>
            <article id='description'>
                <header><h1>Description</h1></header>
                <h2><?= $currentEvent->eventName ?></h2>
                <p>
                	<b>Description*:</b><br />
                	<textarea name='description' style="width:100%" cols='100' rows='10'><?= $currentEvent->description == null ? $location : toTextareaPrint($currentEvent->description) ?></textarea><br />
                </p>
                <!--
                <p>
                    <b>Start Date*:</b><br /><input type='text' id='startdate' name='startdate' /><br />
                    <b>End Date*:</b><br /><input type='text' id='enddate' name='enddate' /><br />
                </p>
                <p><b>Location:</b><br /><input type='text' style="width:100%" name='location' value='<?= $currentEvent->location ?>' /><br /></p>-->
                <input type='hidden' id='startdateraw' name='startdateraw' />
                <input type='hidden' id='enddateraw' name='enddateraw' />
            </article>
            <!--<article id='registration'>
                <header><h1>Registration</h1></header>
                <p><b>PayPal E-Mail Address:</b> <input type='email' name='paypalbusiness' value='<?= $currentEvent->paypalBusiness ?>' /><br />
                <b>Open Date:</b> <input type='text' id='opendate' name='opendate' /><br />
                <b>Close Date:</b> <input type='text' id='closedate' name='closedate' /><br />
                <b>Participant Cap:</b> <input type='text' name='participantcap' value='<?= $currentEvent->participantCap ?>' /></p>
                <input type='hidden' id='opendateraw' name='opendateraw' />
                <input type='hidden' id='closedateraw' name='closedateraw' />
            </article>-->
            <article id='registration'>
                <header><h1>Registration</h1></header>
                <p><b>PayPal E-Mail Address:</b> <?= $currentEvent->paypalBusiness == null ? 'must be set' : $currentEvent->paypalBusiness ?><br />
                <b>Open:</b> <?= $currentEvent->registrationOpenDate == null ? 'must be set' : formatDate($currentEvent->registrationOpenDate) ?><br />
                <b>Close:</b> <?= $currentEvent->registrationCloseDate == null ? 'must be set' : formatDate($currentEvent->registrationCloseDate) ?><br />
                <b>Participant Cap:</b> <?= $currentEvent->participantCap == 0 ? 'no cap' : $currentEvent->participantCap ?></p>
            </article>
            <article id='contact'>
                <header><h1>Contact Info</h1></header>
                <p><b>Website*:</b><br /><input type='text' style="width:100%" name='website' value='<?= $currentEvent->website ?>' /><br />
                <b>Contact E-mail:</b><br /><input type='email' style="width:100%" name='email' value='<?= $currentEvent->email ?>' /></p>
            </article>
            <input class='button' type='submit' />
            </form>
            <?
			}
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>