<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check if they have submitted the event creation form
if(isset($_POST['eventname']) && $currentEvent == null) {
	// get the information from the form
	$eventname = $_POST['eventname'];
	$hostedBy = $_POST['hostedby'];
	$category = $_POST['category'];
	$userAction = $_POST['useraction'];
	$description = $_POST['description'];
	$priceBreakdown = $_POST['pricebreakdown'];
	$username = $_POST['username'];
	$startDate = $_POST['startdateraw'];
	$endDate = $_POST['enddateraw'];
	$openDate = $_POST['opendateraw'];
	$closeDate = $_POST['closedateraw'];
	$participantCap = $_POST['participantcap'];
	$website = $_POST['website'];
	$email = $_POST['email'];
	
	// concatenate the faculties
	$faculties = "";
	if($category == org\fos\Event::FACULTY_FROSH) {
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
		$errorMessage .= "A name for the new event.<br />";
	}
	if($description == "") {
		$errorMessage .= "A description for the new event.<br />";
	}
	if($username == "") {
		$errorMessage .= "A default username for the new event.<br />";
	}
	if($category == "faculty" && $faculties == "") {
		$errorMessage .= "A faculty for the new faculty event.<br />";
	}
	if($startDate == "") {
		$errorMessage .= "A start date for the event.<br />";
	}
	if($endDate == "") {
		$errorMessage .= "An end date for the event.<br />";
	}
	if($openDate == "") {
		$errorMessage .= "An open date for registration.<br />";
	}
	if($closeDate == "") {
		$errorMessage .= "A close date for registration.<br />";
	}
	if($hostedBy == "") {
		$errorMessage .= "A host for the event.<br />";
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
				 || ($_FILES["logo"]["type"] == "image/pjpeg"))
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
		$event = new org\fos\Event();
		$event->eventName = (!get_magic_quotes_gpc()) ? addslashes($eventname) : $eventname;
		$event->hostedBy = (!get_magic_quotes_gpc()) ? addslashes($hostedBy) : $hostedBy;
		$event->category = $category;
		$event->faculty = $faculties;
		if($_FILES["logo"]["name"] != "") {
			$event->logoFileName = $_FILES["logo"]["name"];
		}
		$event->description = (!get_magic_quotes_gpc()) ? addslashes($description) : $description;
		$event->priceBreakdown = (!get_magic_quotes_gpc()) ? addslashes($priceBreakdown) : $priceBreakdown;
		$event->acceptedPayments = "paypal,inperson";
		$event->startDate = $startDate;
		$event->endDate = $endDate;
		$event->registrationOpenDate = $openDate;
		$event->registrationCloseDate = $closeDate;
		$event->participantCap = $participantCap;
		$event->website = $website;
		$event->email = $email;
		$event->action = $userAction;
		$event->hasSelectableEvents = 0;
		$event->displayType = "Standard";
		

		// save the event to the database
		$event = $eventService->saveEvent($event);
		
		// create a default role for the event
		$role = new org\fos\Role();
		$role->roleName = "Master Event Admin";
		//$role->permissions = org\fos\Role::$VIEW_STAFF . ";" . org\fos\Role::$MANAGE_STAFF . ";" . org\fos\Role::$EDIT_EVENT . ";" . org\fos\Role::$EDIT_PAYSCHEDULE . ";" . org\fos\Role::$EDIT_STAFF_ROLES;
		$role->permissions = org\fos\Role::$ALL_PERMISSIONS;
		$role->event = $event;
		
		$role = $roleService->saveRole($role);
		
		// create a default for the event and give it the created role
		$user = new org\fos\User();
		$user->username = $username;
		$user->password = sha1("temp");
		$user->roles[] = $role;
		$user = $userService->saveUser($user);
		
		// notify of success
		$successMessage = "The event '$eventname' was successfully created.<br />The default user's password is 'temp'";
		
		// clear the variables
		unset($eventname);
		unset($hostedBy);
		unset($category);
		unset($userAction);
		unset($description);
		unset($priceBreakdown);
		unset($username);
		unset($startDate);
		unset($endDate);
		unset($openDate);
		unset($closeDate);
		unset($participantCap);
		unset($website);
		unset($email);
	} else {
		// generate the error message
		$errorMessage = "The event could not be created as the form was missing the following:<br />" . $errorMessage;
	}
}

// see if they have asked to delete an event
if(isset($_POST['eventid']) && $currentEvent == null) {
	// ask to delete the event
	$eventService->deleteEventById($_POST['eventid']);
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
    <script type="text/javascript">
		function confirmDelete() {
			var answer = window.confirm("Deleting an event cannot be undone.  Click 'OK' only if you wish to completely delete the event.");
			if(answer) {
				return true;
			} else {
				return false;
			}
		}
	</script>
	<script type="text/javascript" src="../../js/jquery.js"></script>
	<script type="text/javascript" src="../../js/jquery-ui.js"></script>
	<script type="text/javascript" src="../../js/timepicker.js"></script>
    <script type="text/javascript">
		$(function(){
			// add the date/time pickers for the event start/end dates
			$('#startdate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at ',
				minDate: new Date(2014, 7, 23),
				maxDate: new Date(2014, 8, 7)
			});
			$('#enddate').datetimepicker({
				timeFormat: 'hh:mm',
				separator: ' at ',
				minDate: new Date(2014, 7, 23),
				maxDate: new Date(2014, 8, 7)
			});
			
			// add the date pickers for the registration open/close dates
			$('#opendate').datepicker({
				minDate: new Date(2014, 6, 31),
				maxDate: new Date(2014, 8, 7)
			});
			$('#closedate').datepicker({
				minDate: new Date(2014, 7, 1),
				maxDate: new Date(2014, 8, 7)
			});
			
			
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
			
			// set default dates
			$('#startdate').val("08/27/2014 at 17:00");
			$('#startdate').change();
			$('#enddate').val("08/31/2014 at 12:00");
			$('#enddate').change();
			$('#opendate').val("07/28/2014");
			$('#opendate').change();
			$('#closedate').val("08/26/2014");
			$('#closedate').change();
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
				if(roleHasPermission($currentRole, org\fos\Role::$ALL_PERMISSIONS)) {
			?>
            <article id='description'>
                <header><h1>Current Events</h1></header>
                <?
			    // get the collection of roles
			    $events = $eventService->getEvents();
			    
			    // print them
			    foreach($events as $event) {
				    echo("<form method='post' onsubmit='return confirmDelete();'><input type='hidden' name='eventid' value='" . $event->id . "' />");
				    echo("<input type='submit' value='X' />");
				    echo(toPrettyPrint($event->eventName) . "&nbsp;&nbsp;&nbsp;<a href='masterevent.php?id=" . $event->id . "'>(view / edit)</a></form><br />");
			    }
			    ?>
            </article>
            
            <article id='eventcreation'>
                <header><h1>Add an Event</h1></header>
                <form method='post' enctype='multipart/form-data'>
                	<article>
                       <b>Event name*:</b><br />
                       <input type='text' style="width:100%" name='eventname' value="<?= isset($eventname) ? $eventname : "" ?>" /><br /><br />
                                           
                       <b>Host*:</b><br />
                       <textarea name='hostedby' style="width:100%" cols='100' rows='2' value="<?= isset($hostedBy) ? $hostedBy : "" ?>" ></textarea><br /><br />
                        
                       <b>Category*:</b><br />
                       <select style="width:100%" name='category'>
                           <?
						   foreach(org\fos\Event::$ALL_CATEGORIES as $category) {
							   echo("<option value=\"" . $category . "\">" . convertCategoryToDisplay($category) . "</option>\n");
						   }
						   ?>
                       </select><br /><br />
                       
                       <b>Faculty:</b><br />
                       <input type='checkbox' name='faculty[]' value='AG' />Faculty of Agriculture / Environmental Science<br />
                       <input type='checkbox' name='faculty[]' value='AR' />Faculty of Arts<br />
                       <input type='checkbox' name='faculty[]' value='AS' />Faculty of Arts & Science<br />
                       <input type='checkbox' name='faculty[]' value='DE' />Faculty of Dentistry<br />
                       <input type='checkbox' name='faculty[]' value='ED' />Faculty of Education<br />
                       <input type='checkbox' name='faculty[]' value='EN' />Faculty of Engineering<br />
                       <input type='checkbox' name='faculty[]' value='LW' />Faculty of Law<br />
                       <input type='checkbox' name='faculty[]' value='MD' />Faculty of Medicine<br />
                       <input type='checkbox' name='faculty[]' value='MG' />Desautels Faculty of Management<br />
                       <input type='checkbox' name='faculty[]' value='MU' />Schulich School of Music<br />
                       <input type='checkbox' name='faculty[]' value='NU' />School of Nursing<br />
                       <input type='checkbox' name='faculty[]' value='PO' />School of Physical & Occupational Therapy<br />
                       <input type='checkbox' name='faculty[]' value='RS' />Faculty of Religious Studies<br />
                       <input type='checkbox' name='faculty[]' value='SC' />Faculty of Science<br /><br />
                       
                       <b>Logo:</b>
                       <input type='file' name='logo' /><br /><br />
                       
                       <b>Description*:</b><br />
                       <textarea name='description' style="width:100%" cols='100' rows='10'><?= isset($description) ? $description : "" ?></textarea><br /><br />
                       
                       <b>Default username*:</b><br />
                       <input type='text' style="width:100%" name='username' value="<?= isset($username) ? $username : "" ?>" /><br /><br />
                   </article>
                   
                   <article id='description'>
						<header><h1>Event schedule</h1></header>

						<b>Start Date*:</b><br />
                        <input type='text' id='startdate' name='startdate' /><br /><br />
						
                        <b>End Date*:</b><br />
                        <input type='text' id='enddate' name='enddate' /><br />
						
						<input type='hidden' id='startdateraw' name='startdateraw' />
						<input type='hidden' id='enddateraw' name='enddateraw' />
					</article>
					                   
                   
					<article id='registration'>
						<header><h1>Registration</h1></header>
						<b>Open Date*:</b><br />
                        <input type='text' id='opendate' name='opendate' /><br /><br />
						
                        <b>Close Date*:</b><br />
                        <input type='text' id='closedate' name='closedate' /><br /><br />
						
                        <b>Participant Cap:</b><br />
                        <input type='number' name='participantcap' /><br /><br />
                        
                        <b>User Action*:</b><br />
                       <select style="width:100%" name='useraction'>
                       		<option value="Register">Regiser</option>
                            <option value="Reminder">Reminder</option>
                            <option value="AutoRegister">Auto-Register</option>
                            <option value="InfoOnly">For Information Only</option>
                       </select><br /><br />
						
                        <input type='hidden' id='opendateraw' name='opendateraw' />
						<input type='hidden' id='closedateraw' name='closedateraw' />
					</article>
                    
					<article id='contact'>
						<header><h1>Contact Info</h1></header>
						<b>Website:</b><br />
                        <input type='text' style="width:100%"  name='website'value="<?= isset($website) ? $website : "" ?>"  /><br /><br />
						
                        <b>Contact E-mail:</b><br />
                        <input type='email' style="width:100%" name='email' value="<?= isset($email) ? $email : "" ?>" />
					</article>
					
                   <input type='hidden' name='MAX_FILE_SIZE' value='1048576' />
                   <input class='button' type='submit' />
                   
                   
               </form>
            </article>
            
            <?
				} else {
					echo("<article><p>Your user account does not have permission to view this page.</p></article>");
				}
			// else for check to see if the user has an event associated to them
			} else {
				if($currentRole->hasPermission(org\fos\Role::$EDIT_EVENT)) {
				?>
				<button class='edit' onClick="window.location = 'editevent.php'">Edit</button>
				<? } // end of check for edit ?>
            <article id='description'>
                <header><h1>Description</h1></header>
                <h2><?= $currentEvent->eventName ?></h2>
                <p>
                <?= toPrettyPrint($currentEvent->description) ?>
                </p>
                <p>
                <!--
                <b>Start Date:</b> <?= $currentEvent->startDate == null ? 'must be set' : formatDateTime($currentEvent->startDate) ?><br />
                <b>End Date:</b> <?= $currentEvent->endDate == null ? 'must be set' : formatDateTime($currentEvent->endDate) ?><br />
                <b>Location:</b> <?= $currentEvent->location == null ? 'must be set' : $currentEvent->location ?><br />
                -->
                </p>
            </article>
            <article id='registration'>
                <header><h1>Registration</h1></header>
                <p>
                	<? if($currentEvent->costs && count($currentEvent->costs)) { ?>
                    <b>PayPal E-Mail Address:</b> <?= $currentEvent->paypalBusiness == null ? 'must be set' : $currentEvent->paypalBusiness ?><br />
                    <? } ?>
                    <b>Open:</b> <?= $currentEvent->registrationOpenDate == null ? 'must be set' : formatDate($currentEvent->registrationOpenDate) ?><br />
                    <b>Close:</b> <?= $currentEvent->registrationCloseDate == null ? 'must be set' : formatDate($currentEvent->registrationCloseDate) ?><br />
                    <b>Participant Cap:</b> <?= $currentEvent->participantCap == 0 ? 'no cap' : $currentEvent->participantCap ?>
                </p>
            </article>
            <article id='contact'>
                <header><h1>Contact Info</h1></header>
                <h4><?= $currentEvent->website == null ? '(a website has not been entered)' : $currentEvent->website ?></h4>
                <p><?= $currentEvent->email == null ? '(a contact email address has not been entered)' : $currentEvent->email ?></p>
            </article>
            <?
			}
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>