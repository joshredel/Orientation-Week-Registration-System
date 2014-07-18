<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check to make sure they can actually be here
if($currentEvent != null) {
	// they have an actual event... shouldn't be editing master events
	redirect(".");
}

// get the event from the id
if($_GET['id'] != "") {
	$masterEvent = $eventService->getEvent($_GET['id']);
} else {
	redirect("/admin/management");
}

// check if they have submitted the event creation form
if(isset($_POST['eventname'])) {
	// get the information from the form
	$eventname = $_POST['eventname'];
	$description = $_POST['description'];
	$priceBreakdown = $_POST['pricebreakdown'];
	//$username = $_POST['username'];
	$category = $_POST['category'];
	$paypalBusiness = $_POST['paypalbusiness'];
	$startDate = $_POST['startdateraw'];
	$endDate = $_POST['enddateraw'];
	$openDate = $_POST['opendateraw'];
	$closeDate = $_POST['closedateraw'];
	$participantCap = $_POST['participantcap'];
	$location = $_POST['location'];
	$hostedBy = $_POST['hostedby'];	
	$website = $_POST['website'];
	$email = $_POST['email'];
	
	// concatenate the faculties
	$faculties = "";
	if($masterEvent->category == org\fos\Event::FACULTY_FROSH) {
		foreach($_POST['faculty'] as $faculty) {
			$faculties .= "$faculty,";
		}
		
		// removing trailing comma
		if($faculties != "" ){
			$faculties = substr($faculties, 0, -1);
		}
	}
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($eventname == "") {
		$errorMessage .= "A name for the event.<br />";
	}
	if($description == "") {
		$errorMessage .= "A description for the event.<br />";
	}
	if($masterEvent->category == org\fos\Event::FACULTY_FROSH && $faculties == "") {
		$errorMessage .= "A faculty for the faculty event.<br />";
	}
	
	// create dates from the raw values
	$timezone = new DateTimeZone('America/Montreal');
	
	$startDate = new DateTime('@' . ($startDate / 1000));
	$startDate->setTimezone($timezone); 
	
	$endDate = new DateTime('@' . ($endDate / 1000));
	$endDate->setTimezone($timezone); 
	
	$openDate = new DateTime('@' . ($openDate / 1000));
	$openDate->setTimezone($timezone); 
	
	$closeDate = new DateTime('@' . ($closeDate / 1000));
	$closeDate->setTimezone($timezone); 
	$closeDate->setTime(23, 59, 59);
	
	// check if there are errors
	if($errorMessage == "") {
		// upload the logo first
		if($_FILES["logo"]["name"] != "") {
			if ((($_FILES["logo"]["type"] == "image/gif")
				 || ($_FILES["logo"]["type"] == "image/jpeg")
				 || ($_FILES["logo"]["type"] == "image/pjpeg")
				 || ($_FILES["logo"]["type"] == "image/png"))
				 && ($_FILES["logo"]["size"] < 1048576)) {
				if ($_FILES["logo"]["error"] > 0) {
					$errorMessage = "Logo upload failed: " . $_FILES["logo"]["error"];
				} else {
					if (file_exists("../../images/logos/" . $_FILES["logo"]["name"])) {
						$errorMessage = $_FILES["logo"]["name"] . " already exists.";
					} else {
						move_uploaded_file($_FILES["logo"]["tmp_name"], "../../images/logos/" . $_FILES["logo"]["name"]);
					}
				}
			} else {
				$errorMessage = "Unable to upload the logo, please try again!";
			}
		}
		
		// popualte a new event
		$event = $masterEvent;
		$event->eventName = $eventname;
		$event->description = (!get_magic_quotes_gpc()) ? addslashes($description) : $description;
		if($_FILES["logo"]["name"] != "") {
			$event->logoFileName = $_FILES["logo"]["name"];
		}
		$event->priceBreakdown = (!get_magic_quotes_gpc()) ? addslashes($priceBreakdown) : $priceBreakdown;
		$event->category = $category;
		$event->faculty = $faculties;
		$event->acceptedPayments = "paypal,inperson";
		$event->paypalBusiness = $paypalBusiness;
		$event->startDate = $startDate;
		$event->endDate = $endDate;
		$event->registrationOpenDate = $openDate;
		$event->registrationCloseDate = $closeDate;
		$event->participantCap = $participantCap;
		$event->hostedBy = $hostedBy;
		$event->website = $website;
		$event->email = $email;
		
		// save the event to the database
		$event->roles[0]->event = $event;
		$roleService->saveRole($event->roles[0]);
		
		// notify of success
		$successMessage = "The event '$eventname' was successfully saved.";
	} else {
		// generate the error message
		$errorMessage = "The event could not be saved as the form was missing the following:<br />" . $errorMessage;
	}
}

// create an array of faculties for this event
$faculties = explode(",", $masterEvent->faculty);
$tags = explode(",", $masterEvent->tags);
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
			$('#startdate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at ',
				minDate: new Date(2013, 7, 23),
				maxDate: new Date(2013, 8, 7)
			});
			$('#enddate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at ',
				minDate: new Date(2013, 7, 23),
				maxDate: new Date(2013, 8, 7)
			});
			
			// add the date pickers for the registration open/close dates
			$('#opendate').datepicker({
				minDate: new Date(2013, 6, 31),
				maxDate: new Date(2013, 8, 7)
			});
			$('#closedate').datepicker({
				minDate: new Date(2013, 7, 1),
				maxDate: new Date(2013, 8, 7)
			});
			
			// set the dates to the values from php
			<?
			if($masterEvent->startDate != null || $startDate != null) {
				$tempDate = ($startDate == null ? $masterEvent->startDate : $startDate);
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#startdate').datetimepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#startdateraw').val($('#startdate').datetimepicker('getDate').getTime());
				<?
			}
			
			if($masterEvent->endDate != null || $endDate != null) {
				$tempDate = ($endDate == null ? $masterEvent->endDate : $endDate);
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#enddate').datetimepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#enddateraw').val($('#enddate').datetimepicker('getDate').getTime());
				<?
			}
			
			if($masterEvent->registrationOpenDate != null || $openDate != null) {
				$tempDate = ($openDate == null ? $masterEvent->registrationOpenDate : $openDate);
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#opendate').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#opendateraw').val($('#opendate').datepicker('getDate').getTime());
				<?
			}
			
			if($masterEvent->registrationCloseDate != null || $closeDate != null) {
				$tempDate = ($closeDate == null ? $masterEvent->registrationCloseDate : $closeDate);
				$tempDate = $tempDate->format('U') * 1000;
				?>
				$('#closedate').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#closedateraw').val($('#closedate').datepicker('getDate').getTime());
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
            <article>
            	<a href="../management">back to Event Management</a>
                <br /><br />
            	<header><h1>Edit Event Information</h1></header>
            	<form method='post' enctype='multipart/form-data'>
                   <b>Event name:</b><br />
                   <input type='text' name='eventname' style="width:100%" value="<?= toPrettyPrint($masterEvent->eventName) ?>" /><br /><br />
                   
                   <b>Host:</b><br />
                   <input type='text' name='hostedby' style="width:100%" value="<?= toPrettyPrint($masterEvent->hostedBy) ?>"></input><br /><br />
                   
                    <b>Category:</b><br />
                   <select name='category' style="width:100%">
                       <?
					   foreach(org\fos\Event::$ALL_CATEGORIES as $mainCategory) {
					       echo("<option value='" . $mainCategory . "' ");
						   if($masterEvent->category == $mainCategory) {
						       echo("selected");
						   }
						   echo(">" . convertCategoryToDisplay($mainCategory) . "</option>\n");
					   }   
					   ?>
                   </select><br /><br />
				   <?
				   if($masterEvent->category == org\fos\Event::FACULTY_FROSH) {
				   ?>
				   <b>Faculty: </b><br />
                   <input type='checkbox' name='faculty[]' value='AG' <?= in_array("AG", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Agriculture / Environmental Science<br />
                   <input type='checkbox' name='faculty[]' value='AR' <?= in_array("AR", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Arts<br />
                   <input type='checkbox' name='faculty[]' value='AS' <?= in_array("AS", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Arts & Science<br />
                   <input type='checkbox' name='faculty[]' value='DE' <?= in_array("DE", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Dentistry<br />
                   <input type='checkbox' name='faculty[]' value='ED' <?= in_array("ED", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Education<br />
                   <input type='checkbox' name='faculty[]' value='EN' <?= in_array("EN", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Engineering<br />
                   <input type='checkbox' name='faculty[]' value='LW' <?= in_array("LW", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Law<br />
                   <input type='checkbox' name='faculty[]' value='MD' <?= in_array("MD", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Medicine<br />
                   <input type='checkbox' name='faculty[]' value='MG' <?= in_array("MG", $faculties) == 1 ? 'checked' : '' ?> />Desautels Faculty of Management<br />
                   <input type='checkbox' name='faculty[]' value='MU' <?= in_array("MU", $faculties) == 1 ? 'checked' : '' ?> />Schulich School of Music<br />
                   <input type='checkbox' name='faculty[]' value='NU' <?= in_array("NU", $faculties) == 1 ? 'checked' : '' ?> />School of Nursing<br />
                   <input type='checkbox' name='faculty[]' value='PO' <?= in_array("PO", $faculties) == 1 ? 'checked' : '' ?> />School of Physical & Occupational Therapy<br />
                   <input type='checkbox' name='faculty[]' value='RS' <?= in_array("RS", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Religious Studies<br />
                   <input type='checkbox' name='faculty[]' value='SC' <?= in_array("SC", $faculties) == 1 ? 'checked' : '' ?> />Faculty of Science<br /><br />
				   <? } // end of checking for faculty type event ?>
                   
                   <b>Current logo:</b><br />
                   <?
                   if($masterEvent->logoFileName != null) {
					   echo(resizeLogo("../../images/logos/{$masterEvent->logoFileName}", 190, 130, "alt='logo'", false) . "<br />");
				   } else {
					   echo("A logo has not yet been uploaded.<br />");
				   }
				   ?><br />
                   
                   <b>New logo:</b><br />
                   <input type='file' name='logo' style="width:100%" /><br /><br />
                   
                    <b>Description:</b><br />
                    <textarea name='description' style="width:100%" cols='100' rows='10'><?= toTextareaPrint($masterEvent->description) ?></textarea><br />
                    
                    <b>Price breakdown:</b><br />
                    <textarea name='pricebreakdown' style="width:100%" cols='100' rows='10'><?= toTextareaPrint($masterEvent->priceBreakdown) ?></textarea><br /><br />
                    
					<header><h1>Event schedule</h1></header>
					<b>Start Date:</b><br />
                    <input type='text' id='startdate' name='startdate' /><br /><br />
                    
					<b>End Date:</b><br />
                    <input type='text' id='enddate' name='enddate' /><br />
					</p>
					<input type='hidden' id='startdateraw' name='startdateraw' />
					<input type='hidden' id='enddateraw' name='enddateraw' />
                    
					<header><h1>Registration</h1></header>
					<p><b>PayPal Business (E-Mail Address):</b><br />
                    <input type='email' name='paypalbusiness' style="width:100%" value='<?= $masterEvent->paypalBusiness == null ? $paypalBusiness : $masterEvent->paypalBusiness ?>' /><br /><br />
                    
					<b>Open Date:</b><br />
                    <input type='text' id='opendate' name='opendate' /><br /><br />
                    
					<b>Close Date:</b><br />
                    <input type='text' id='closedate' name='closedate' /><br /><br />
                    
					<b>Participant Cap:</b><br />
                    <input type='number' name='participantcap' value='<?= $masterEvent->participantCap == null ? $participantCap : $masterEvent->participantCap ?>' /></p>
                    
					<input type='hidden' id='opendateraw' name='opendateraw' />
					<input type='hidden' id='closedateraw' name='closedateraw' />
					
                    
                    <header><h1>Contact Info</h1></header>
					<p><b>Website:</b><br />
                    <input type='text' name='website' style="width:100%" value='<?= $masterEvent->website == null ? $website : $masterEvent->website ?>' /><br /><br />
                    
					<b>Contact E-mail:</b><br />
                    <input type='email' name='email' style="width:100%" value='<?= $masterEvent->email == null ? $email : $masterEvent->email ?>' /></p>
                   
                   <input type='hidden' name='MAX_FILE_SIZE' value='1048576' />
                   <input class='button' type='submit' />
               </form>
            </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>