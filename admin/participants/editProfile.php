<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get the requested user
$id = $_GET['id'];
$participant = $participantService->getParticipant($id);

// redirect if the participant doesn't exist
if($participant == null) {
	redirect(".");
}

// redirect if they do not have permission to be here or the user is not in their event
if(!$currentRole->hasPermission(org\fos\Role::$EDIT_PARTICIPANTS) || 
   ($currentEvent != null && !inDoctrineArray($participant, $currentEvent->participants))) {
	// the user does not have permissions
	redirect(".");
}

// check if they have submitted the user creation form
if(isset($_POST['firstName']) && $currentRole->hasPermission(org\fos\Role::$EDIT_PARTICIPANTS)) {
	// make sure they didn't duplicate the student ID
	$testParticipant = $participantService->getParticipantByStudentId($_POST['studentId']);
	if($testParticipant != null && $testParticipant->id != $participant->id) {
		$errorMessage = "Participant info was not added.  Another user already has the student ID number '" . $_POST['studentId'] . "'";
	} else {
		// conver the date
		//$dateOfBirthRaw = $_POST['dateOfBirthRaw'];
		//$dateOfBirth = new DateTime('@' . ($dateOfBirthRaw / 1000));
		//$dateOfBirth->setTimezone(new DateTimeZone('America/Montreal'));
		$dateOfBirth = new DateTime($_POST['dateOfBirth'] . " 00:00:00");
		
		// get the parameters from the form
		$participant->firstName = $_POST['firstName'];
		$participant->lastName = $_POST['lastName'];
		$participant->preferredName = $_POST['preferredName'];
		$participant->preferredPronoun = $_POST['preferredPronoun'];
		$participant->studentId = $_POST['studentId'];
		$participant->faculty = $_POST['faculty'];
		$participant->dateOfBirth = $dateOfBirth;
		$participant->shirtSize = $_POST['shirtSize'];
		$participant->allergies = $_POST['allergies'];
		$participant->physicalNeeds = $_POST['physicalNeeds'];
		$participant->email = $_POST['email'];
		$participant->livingStyle = $_POST['livingStyle'];
		//$participant->froshAddress = $_POST['froshAddress'];
		$participant->placeOfOrigin = $_POST['placeOfOrigin'];
		$participant->enteringYear = $_POST['enteringYear'];
		
		// dietary restrictions
		if(isset($_POST['dietaryRestrictions'])) {
			$participant->dietaryRestrictions = implode(",", $_POST['dietaryRestrictions']);
		} else {
			$participant->dietaryRestrictions = "";
		}
		
		// save it!
		$participantService->saveParticipant($participant);
		$successMessage = "Participant information successfully updated.";
	}
}

// get their age for the event
if($currentEvent != null) {
	if($currentEvent->startDate != null && $currentEvent->endDate != null) {
		$eventDate = $currentEvent->startDate->getTimestamp();
		$birthDate = $participant->dateOfBirth->getTimestamp();
		
		$eventMonth = date('n', $eventDate);
		$eventDay = date('j', $eventDate);
		$eventEndDay = date('j', $currentEvent->endDate->getTimestamp());
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
			$eventAge = "$futureAge (<b>underage</b>)";
		} else {
			// they will be of age
			$eventAge = $futureAge;
		}
		
		// now also see if their birthday is during the event
		if($futureAge == 17 && ($birthMonth == $eventMonth && $birthDay >= $eventDay && $birthDay <= $eventEndDay)) {
			$eventAge .= " (note: they become of-age during the event)";
		}
	} else {
		$eventAge = "unknown (event does not have a date)";
	}
}

// get their dietary restrictions
$restrictions = explode(",", $participant->dietaryRestrictions);

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
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
        <script src="js/IE8.js" type="text/javascript"></script>
    <![endif]-->

    <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" media="all" href="css/ie6.css"/>
    <![endif]-->
    <script>
	function checkForm(form) {
		var confirmationText = "";
		
		// get everything from the form
		var firstname = form.elements['firstName'].value;
		var lastname = form.elements['lastName'].value;
		var studentId = form.elements['studentId'].value;
		var dateOfBirth = form.elements['dateOfBirth'].value;
		var email = form.elements['email'].value;
		
		// check the name
		if(firstname == "") {
			confirmationText += "- first name\n";
		}
		if(lastname == "") {
			confirmationText += "- last name\n";
		}
		
		// check that the emails match and are... emails
		if(email == "" || (email.indexOf('@') < 0 || email.indexOf('.') < 0)) {
			confirmationText += "- a valid email (you seem to be missing something...)\n";
		}
		
		// check the student ID
		if(isNaN(studentId) || studentId == "" || studentId.length != 9 ) {
			confirmationText += "- valid, 9-digit student ID numbers\n";
		}
		
		// check the emails
		if(email == "") {
			confirmationText += "- email address\n";
		}
		
		// check birth date
		if(dateOfBirthRaw == "") {
			confirmationText += "- date of birth\n";
		}
		
		// continue with processing or return errors
		if(confirmationText == "") {
			return true;
		} else {
			confirmationText = "The form is incomplete or invalid, you are missing:\n" + confirmationText;
			alert(confirmationText);
			return false;
		}
	}
	
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
		include("../includes/html/partNav.php");
		
		// show messages if need be
	    if(isset($errorMessage) && $errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if(isset($successMessage) && $successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
    	   <article id='userProfile'>
           	   <a href="../participants/profile.php?id=<?= $id ?>">back to Participant Overview</a>
               <br /><br />
               <header><h1>Editing Information for <?= $participant->firstName . " " . $participant->lastName ?></h1></header>
               <form method='post' onsubmit='return checkForm(this)'>
                   <h2>Main Information</h2>
                   <label for='firstName'><b>First name:</b></label>
                   <input type='text' name='firstName' value='<?= $participant->firstName ?>' size="50" /><br />
                   
                   <label for='lastName'><b>Last name:</b></label>
                   <input type='text' name='lastName' value='<?= $participant->lastName ?>' size="50" /><br />
                   
                   <label for='preferredName'><b>Preferred name:</b></label>
                   <input type='text' name='preferredName' value='<?= $participant->preferredName ?>' size="50" /><br />
                   
                   <label for='preferredPronoun'><b>Preferred pronoun:</b></label>
                   <input type='text' name='preferredPronoun' value='<?= $participant->preferredPronoun ?>' size="50" /><br />
                   
                   <label for='dateOfBirth'><b>Date of birth:</b></label>
                   <input type='text' id='dateOfBirth' name='dateOfBirth' value='<?= formatSimpleDate($participant->dateOfBirth) ?>' size="50" /><br />
                   <input type="hidden" id="dateOfBirthRaw" name="dateOfBirthRaw" value="<?= $dateOfBirthRaw ?>">
                   
                   <label for='faculty'><b>Faculty:</b></label>
                   <select name="faculty">
                        <option value="AG" <?= checkForSelected($participant->faculty, "AG") ?>>Faculty of Agriculture / Environmental Science</option>
                        <option value="AR" <?= checkForSelected($participant->faculty, "AR") ?>>Faculty of Arts</option>
                        <option value="AS" <?= checkForSelected($participant->faculty, "AS") ?>>Faculty of Arts & Science</option>
                        <!--<option value="DE" <?= checkForSelected($participant->faculty, "DE") ?>>Faculty of Dentistry</option>-->
                        <option value="ED" <?= checkForSelected($participant->faculty, "ED") ?>>Faculty of Education</option>
                        <option value="EN" <?= checkForSelected($participant->faculty, "EN") ?>>Faculty of Engineering</option>
                        <option value="LW" <?= checkForSelected($participant->faculty, "LW") ?>>Faculty of Law</option>
                        <!--<option value="MD" <?= checkForSelected($participant->faculty, "MD") ?>>Faculty of Medicine</option>-->
                        <option value="RS" <?= checkForSelected($participant->faculty, "RS") ?>>Faculty of Religious Studies</option>
                        <option value="SC" <?= checkForSelected($participant->faculty, "SC") ?>>Faculty of Science</option>
                        <option value="MG" <?= checkForSelected($participant->faculty, "MG") ?>>Desautels Faculty of Management</option>
                        <option value="MU" <?= checkForSelected($participant->faculty, "MU") ?>>Schulich School of Music</option>
                        <option value="EN" <?= checkForSelected($participant->faculty, "EN") ?>>School of Architecture</option>
                        <option value="NU" <?= checkForSelected($participant->faculty, "NU") ?>>School of Nursing</option>
                        <option value="PO" <?= checkForSelected($participant->faculty, "PO") ?>>School of Physical &amp; Occupational Therapy</option>
                        <option value="AR" <?= checkForSelected($participant->faculty, "AR") ?>>School of Social Work</option>
                    </select><br />
                    
                    <label for='preferredPronoun'><b>Student ID:</b></label>
                   <input type='number' name='studentId' value='<?= $participant->studentId ?>' size="50" maxlength="9" /><br />
                    
                   <label for='shirtSize'><b>Shirt size:</b> </label>
                   <select name='shirtSize'>
                       <option value='XS' <?= ($participant->shirtSize == "XS" ? 'selected' : '') ?>>XS</option>
                       <option value='S' <?= ($participant->shirtSize == "S" ? 'selected' : '') ?>>S</option>
                       <option value='M' <?= ($participant->shirtSize == "M" ? 'selected' : '') ?>>M</option>
                       <option value='L' <?= ($participant->shirtSize == "L" ? 'selected' : '') ?>>L</option>
                       <option value='XL' <?= ($participant->shirtSize == "XL" ? 'selected' : '') ?>>XL</option>
                       <option value='XXL' <?= ($participant->shirtSize == "XXL" ? 'selected' : '') ?>>XXL</option>
                   </select><br />
                   
                   <label><b>Dietary restrictions:</b></label><br />
                   	<input type="checkbox" name="dietaryRestrictions[]" value="vegan" <?= (in_array("vegan", $restrictions) ? 'checked' : '') ?>>Vegan<br />
                    <input type="checkbox" name="dietaryRestrictions[]" value="vegetarian" <?= (in_array("vegetarian", $restrictions) ? 'checked' : '') ?>>Vegetarian<br />
                    <input type="checkbox" name="dietaryRestrictions[]" value="kosher" <?= (in_array("kosher", $restrictions) ? 'checked' : '') ?>>Kosher<br />
                    <input type="checkbox" name="dietaryRestrictions[]" value="halal" <?= (in_array("halal", $restrictions) ? 'checked' : '') ?>>Halal<br />
                    <input type="checkbox" name="dietaryRestrictions[]" value="celiac" <?= (in_array("celiac", $restrictions) ? 'checked' : '') ?>>Celiac<br /><br />
                   
                   <label for='allergies'><b>Allergies:</b></label><br />
                   <textarea name='allergies' rows="6" cols="70"><?= $participant->allergies ?></textarea><br />
                   
                   <label for='allergies'><b>Physical needs:</b></label><br />
                   <textarea name='physicalNeeds' rows="6" cols="70"><?= $participant->physicalNeeds ?></textarea><br />
                   <br />
                   
                   <h2>Contact &amp; Other Information</h2>
                   <label for='email'><b>E-Mail:</b></label>
                   <input type='text' name='email' value='<?= $participant->email ?>' size="50" /><br />
                   
                   <label for='livingStyle'><b>Living style:</b> </label>
                   <select name="livingStyle">
                        <option value="InRez" <?= checkForSelected($participant->livingStyle, "InRez") ?>>In a McGill Residence</option>
                        <option value="OffCampus" <?= checkForSelected($participant->livingStyle, "OffCampus") ?>>Off Campus</option>
                    </select><br />
                   
                   <!--<label for='froshAddress'><b>Address:</b> </label><br />
                   <textarea name='froshAddress' rows="6" cols="70"><?= $participant->froshAddress ?></textarea><br /><br />-->
                   
                   <label for='placeOfOrigin'><b>Place of origin:</b></label>
                   <select name="placeOfOrigin">
                        <option value="Quebec" <?= checkForSelected($participant->placeOfOrigin, "Quebec") ?>>Quebec</option>
                        <option value="RestOfCanada" <?= checkForSelected($participant->placeOfOrigin, "RestOfCanada") ?>>Rest of Canada (outside of Quebec)</option>
                        <option value="International" <?= checkForSelected($participant->placeOfOrigin, "International") ?>>International</option>
                    </select><br />
                   
                   <label for='enteringYear'><b>Entering year:</b> </label>
                   <select name="enteringYear">
                        <option value="U0" <?= checkForSelected($participant->enteringYear, "U0") ?>>U0</option>
                        <option value="U1" <?= checkForSelected($participant->enteringYear, "U1") ?>>U1</option>
                        <option value="Transfer" <?= checkForSelected($participant->enteringYear, "Transfer") ?>>Transfer Student</option>
                        <option value="Exchange" <?= checkForSelected($participant->enteringYear, "Exchange") ?>>Exchange Student</option>
                    </select><br /><br />
                    
               	   <input class='button' type='submit' />
               </form>
    	   </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
    <script src="/js/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    
    <script>
		!function ($) {
			$(function(){
				$('#header').carousel()
			})
		}(window.jQuery)
		
		$(function(){
			// add the date pickers for the registration open/close dates
			$('#dateOfBirth').datepicker({ 
					dateFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					minDate: new Date(1950, 0, 0),
					maxDate: new Date(2000, 0, 0)
			});
			
			// set the dates to the values from php
			<?
			if($participant->dateOfBirth != null) {
				$tempDate = $participant->dateOfBirth->format('U') * 1000;
				?>
				$('#dateOfBirth').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				$('#dateOfBirthRaw').val($('#dateOfBirth').datepicker('getDate').getTime());
				<?
			}
			?>
			
			// listen for the entry fields to change so we can pull the milliseconds from them for use in PHP
			$('#dateOfBirth').bind('change', function() {
				$('#dateOfBirthRaw').val($('#dateOfBirth').datepicker('getDate').getTime());
			});
		});
	</script>
</body>
</html>