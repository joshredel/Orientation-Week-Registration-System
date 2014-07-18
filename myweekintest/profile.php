<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_REQUEST['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// check if they have submitted the user creation form
if(isset($_POST['dateOfBirth']) && $_POST['dateOfBirth'] != "") {
	// convert the date and adjust it for timezone differences
	$dateOfBirth = new DateTime($_POST['dateOfBirth'] . " 00:00:00");
	
	// save the date of birth
	$participant->dateOfBirth = $dateOfBirth;
	
	// save it!
	$participantService->saveParticipant($participant);
	$successMessage = "Your date of birth has been updated successfully.";
}

// check if they have submitted the contact form
if(isset($_POST['phoneNumber'])) {
	// get the phone number
	$rawPhone = $_POST['phoneNumber'];
	
	// strip any of the accepted non-numeric characters (space, -, .)
	$rawPhone = str_ireplace("-", "", $rawPhone);
	$rawPhone = str_ireplace(" ", "", $rawPhone);
	$rawPhone = str_ireplace(".", "", $rawPhone);
	
	// if it is only 10 digits, add the +1
	if(strlen($rawPhone) == 10) {
		$rawPhone = "+1" . $rawPhone;
	}
	
	// and make sure it has a + at the beginning
	if(strlen($rawPhone) > 0 && $rawPhone[0] != '+') {
		$rawPhone = "+" . $rawPhone;
	}
	
	// store it
	$participant->phoneNumber = $rawPhone;
	
	// store the residence they selected
	if(isset($_POST['residence'])) {
		$participant->froshAddress = $_POST['residence'];
	}
	
	// save the participant with its new information
	$participantService->saveParticipant($participant);
	$successMessage = "Your new contact information has been updated successfully.";
}

// format name
$nameForDisplay = $participant->firstName . " ";
if(strlen($participant->preferredName)) {
	$nameForDisplay .= "(" . $participant->preferredName . ") ";
}
$nameForDisplay .= $participant->lastName;
if(strlen($participant->preferredPronoun)) {
	$nameForDisplay .= " (" . strtolower($participant->preferredPronoun) . ")";
}
$nameForDisplay = toPrettyPrint($nameForDisplay);

// format the dietary needs
if(strlen($participant->dietaryRestrictions)) {
	$dietaryForDisplay = str_replace(",", ", ", $participant->dietaryRestrictions);
} else {
	$dietaryForDisplay = "None to note";
}

/*
// see if any of their events have custom fields
$customQuestionToPrint = "";
foreach($participant->events as $event) {
	if($event->customFields != null && $event->customFields != "") {
		// there are custom fields for this event; process them!
		// break apart the different questions
		$questions = explode("<:;:>", $event->customFields);
		foreach($questions as $question) {
			// get the details about this question
			$customInfo = explode("<::>", $question);
			$fieldId = $customInfo[0];
			$fieldName = $customInfo[1];
			$fieldDescription = $customInfo[2];
			$fieldType = $customInfo[3];
			$fieldOptions = explode(",", $customInfo[4]);
			
			// see if they have answered the question
			$haveAnsweredQuestion = false;
			$answerText = "You have not yet answered.";
			if($participant->customFieldAnswers != null && $participant->customFieldAnswers) {
				//break them down
				$answers = explode("<:;:>", $participant->customFieldAnswers);
				foreach($answers as $answer) {
					// break down the answer
					$answerPieces = explode("<::>", $answer);
					$eventId = (int)$answerPieces[0];
					$answerFieldId = (int)$answerPieces[1];
					$answerName = $answerPieces[2];
					$response = $answerPieces[3];
					
					// see if we have a match
					if($eventId == $event->id && $answerFieldId == $fieldId) {
						// we have a match; store the answer
						$haveAnsweredQuestion = true;
						$answerText = $response;
						break;
					}
				}
			}
			
			$paragraphClass = ($haveAnsweredQuestion ? "" : "btn-warning");
			$modalClass = ($haveAnsweredQuestion ? "btn-primary" : "btn-warning");
			
			// print the info
			$customQuestionToPrint .= "<p><strong><span class=\"" . $paragraphClass . "\">$fieldDescription</span></strong><br />$answerText <a data-toggle=\"modal\" class=\"btn btn-small $modalClass\" role=\"button\" href=\"#customModal" . $fieldId . "\">Answer the question</a></p>";
			
			// print the modal
			$customQuestionToPrint .= "<div aria-hidden=\"true\" aria-labelledby=\"customModalLabel" . $fieldId . "\" role=\"dialog\" tabindex=\"-1\" class=\"modal hide fade\" id=\"customModal" . $fieldId . "\" style=\"display: none;\">
                        <div class=\"modal-header modal-default\">
                          <button aria-hidden=\"true\" data-dismiss=\"modal\" class=\"close\" type=\"button\"><i class=\"icon-remove-sign\"></i></button>
                          <h3 id=\"customModalLabel" . $fieldId . "\">Modal Header</h3>
                        </div>
                        <div class=\"modal-body\">
                          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Soluta quia fugit ad neque tempora reiciendis itaque placeat voluptatibus nemo asperiores officia dignissimos nulla fuga quis officiis inventore ea repellendus. Vero.</p>
                        </div>
                        <div class=\"modal-footer\">
                          <button aria-hidden=\"true\" data-dismiss=\"modal\" class=\"btn\">Cancel</button>
                          <button class=\"btn btn-primary\">Save</button>
                        </div>
                      </div>";
		}
	}
}
*/

// a function to display "None" if the passed text is empty
function formatOptionalText($text) {
	if(strlen($text)) {
		return $text;
	} else {
		return "None to note";
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
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>McGill Orientation Week 2013 | myWeek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- end: Meta -->

    <? include("includes/cssJsHeader.php") ?>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="assets/lib/pnotify/jquery.pnotify.default.css">
    <link rel="stylesheet" type="text/css" href="assets/lib/pnotify/jquery.pnotify.default.icons.css">
	<script src="assets/lib/pnotify/jquery.pnotify.min.js"></script>

</head><!--end: head -->

<body> 
	<!-- top bar navigation -->
	<div class="navbar">
	    <div class="navbar-inner">
            <ul class="nav pull-right">
                <!-- mail menu -->
                <!--
                <li class="dropdown header-border">
                    <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="email-notify"><i class="icon-envelope-alt"></i><span class="email-alert"><i class="icon-circle"></i> </span></span>
                    </a>

                    <ul class="dropdown-menu block-dark messages">
                        <li class="view-all"><a href="#">View all messages</a></li>   
                        <li><a href="#">
                            <div class="avatar"><img height="45" width="45" src="assets/images/face-1.jpg" alt="Your profile"></div>
                            <div class="info">Antonio Heide <span class="timer">36 min</span></div>    
                            <div class="message">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatem necessitatibus.</div>
                            </a>
                        </li>
                        <li><a href="#">
                            <div class="avatar"><img height="45" width="45" src="assets/images/face-2.jpg" alt="Your profile"></div>
                            <div class="info">Melissa Evans <span class="timer">52 min</span></div>    
                            <div class="message">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quis optio ad placeat incidunt iste non enim nisi quae culpa.</div>
                            </a>
                        </li>                                               
                    </ul>
                </li>
                -->
                <!-- end mail menu -->
                
                <!-- notificataions menu -->
                <!--
                <li class="dropdown header-border">
                    <a href="#" role="button" class="dropdown-toggle " data-toggle="dropdown">
                        <div class="notify">7</div>
                    </a>

                    <ul class="dropdown-menu block-dark messages msg-notify">
                        <li class="view-all"><a href="#">View all notifications</a></li>   
                        <li><a href="#"><div><i class="icon-comment"></i>  new comment <span class="timer">2 min</span></div></a></li>
                        <li><a href="#"><div><i class="icon-twitter"></i>  new follower <span class="timer">6 min</span></div></a></li>
                        <li><a href="#"><div><i class="icon-thumbs-up"></i> new like <span class="timer">9 min</span></div></a></li>                
                        <li><a href="#"><div><i class="icon-twitter"></i>  new follower <span class="timer">25 min</span></div></a></li>
                        <li><a href="#"><div><i class="icon-user"></i>  new registration <span class="timer">32 min</span></div></a></li>                    
                        <li><a href="#"><div><i class="icon-comment"></i>  new comment <span class="timer">2 hours</span></div></a></li>
                        <li><a href="#"><div><i class="icon-map-marker"></i>  new localization <span class="timer">Yesterday</span></div></a></li>
                    </ul>
                </li>
                -->
                <!-- end notifications menu -->
            </ul>
            
            <a class="brand" href="index.php?passkey=<?= $_REQUEST['passkey'] ?>"><img src="/images/frontend/logo.png" alt="Logo"></a>
	    </div>
	</div>
    <!-- top bar navigation --> 
    
    <!-- sidebar navigation -->
	<div data-offset-top="360" data-spy="affix" class="sidebar-nav affix">
    	<!-- floating logo with notifications -->
	    <div class="sidebar-avatar">
	        <img src="assets/images/myweek.jpg" alt="avatar" class="thumbnail-avatar">
            <!--
	        <a href="#"><div class="sidebar-avatar-message"><div class="notify notify-message"><i class="icon-envelope"></i></div></div></a>
	        <a href="#"><div class="sidebar-avatar-notify"><div class="notify ">7</div></div></a>
            -->
	    </div>
        <!-- end floating logo with notiticactions -->
        
        <!-- navigation hidden menu toggler (for mobile) -->
	    <a data-toggle="collapse" data-target=".nav-collapse" class="btn-sidebar">
	        <span class="notify navigation span12"><i class="icon-reorder"></i> Navigation <span class="pull-right label sidebar-label label-danger"><i class="icon-angle-down"></i> </span></span>
	    </a>
        <!-- end navigation hidden menu toggler (for mobile) -->
        
        <!-- main sidebar navigation options -->
	    <div class="nav-collapse subnav-collapse collapse ">
	        <? include("includes/sidebar.php") ?>
	    </div>
        <!-- end main sidebar navigation options -->
	</div>
    <!-- end sidebar navigation --> 
    
    <!-- full content -->
	<div class="content">
    	<!-- notification bar -->
	    <div class="header">
	        <div class="stats">
                <? include("includes/notificationBar.php") ?>
            </div>
            
	        <h1 class="page-title">Profile</h1>
	    </div>
        <!-- end notification bar -->
	    
        <!-- main content -->
	    <div class="wrapper-content">
	    	<div class="container-fluid">
				<div class="row-fluid">
	            	<div class="block span6">
                    	<p class="block-heading">Main Information</p>
	              		<div class="block-body">
                        	<p><strong>Identity:</strong><br /><?= $nameForDisplay ?></p>
                            <p><strong>Birthday:</strong><br /><?= formatSimpleDate($participant->dateOfBirth) ?></p>
                            <p><strong>Faculty:</strong><br /><?= convertCodeToFaculty($participant->faculty) ?></p>
                            <p><strong>Student ID:</strong><br /><?= $participant->studentId ?></p>
                            <p><strong>Shirt Size:</strong><br /><?= $participant->shirtSize ?></p>
                            <p><strong>Dietary Restrictions:</strong><br /><?= $dietaryForDisplay ?></p>
                            <p><strong>Allergies:</strong><br /><?= formatOptionalText($participant->allergies) ?></p>
                            <p><strong>Physical Needs:</strong><br /><?= formatOptionalText($participant->physicalNeeds) ?></p>
                        </div>         
                    </div>  

	            	<div class="block span6">
                    	<p class="block-heading">Contact &amp; Other Information</p>
	              		<div class="block-body">
                        	<p><strong>E-Mail:</strong><br /><?= $participant->email ?></p>
                            <?
							if($participant->phoneNumber != null && strlen($participant->phoneNumber)) {
								$phoneNumber = $participant->phoneNumber;
								?>
                                <p><strong>Phone Number:</strong><br /><?= $participant->phoneNumber ?>&nbsp;&nbsp;<a data-toggle="modal" class="btn btn-small" role="button" href="#extraDataModal">Edit</a></p>
                                <?
							} else {
								$phoneNumber = "";
								?>
                                <p><strong class="btn-warning">Phone Number:</strong>&nbsp;
                                <a data-toggle="modal" class="btn btn-small btn-warning" role="button" href="#extraDataModal">Please enter your phone number.</a><br />
                                A very important part of McGill Orientation Week is the myWeek Gateway system.  This allows you to call in and get instant information on your week and to receive messages from your events.</p>
								<?
							}
							?>
                            <p><strong>Living Style:</strong><br /><?= strtoproper(convertCodeToLivingStyle($participant->livingStyle)) ?></p>
                            <?
							if($participant->livingStyle == "InRez") {
								if($participant->froshAddress != null && strlen($participant->froshAddress)) {
									?>
									<p><strong>Residence:</strong><br /><?= convertRezToDisplay($participant->froshAddress) ?>&nbsp;&nbsp;<a data-toggle="modal" class="btn btn-small" role="button" href="#extraDataModal">Edit</a></p>
									<?
								} else {
									?>
									<p><strong class="btn-warning">Residence:</strong>&nbsp;
									<a data-toggle="modal" class="btn btn-small btn-warning" role="button" href="#extraDataModal">Please select your residence.</a><br />
									We are able to provide more services to you if you let us know what residence you will be living in.</p>
									<?
								}
							}
							?>
                            <p><strong>Place of Origin:</strong><br /><?= strtoproper(convertCodeToOrigin($participant->placeOfOrigin)) ?></p>
                            <p><strong>Entering Year:</strong><br /><?= $participant->enteringYear ?></p>
                            <p><strong>Registration Date:</strong><br /><?= formatDateTime($participant->registrationDate) ?></p>
                            
                            <div aria-hidden="true" aria-labelledby="extraDataModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="extraDataModal" style="display: none;">
                                <div class="modal-header modal-inverse">
                                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button"><i class="icon-remove-sign"></i></button>
                                    <h3 id="extraDataModalLabel">Edit Extra Contact Information</h3>
                                </div>
                                <div class="modal-body">
                                	<form id="contactForm" method="post" onSubmit="return checkForm(this)">
                                        <p><strong>Please enter your phone number.</strong><br />This should ideally be your permanent Montreal phone number, but we will accept international numbers.  Formatting is described below.</p>
                                        <input id="phoneNumber" type="text" name="phoneNumber" onChange="checkPhoneFormat(this)" onKeyUp="checkPhoneFormat(this)" value="<?= $phoneNumber ?>" /> (Detected format: <span id="phoneFormat">Incomplete</span>)
                                        <p>
                                            <strong>Phone formats:</strong><br />
                                            <em>North America:</em> xxx-xxx-xxxx or xxx.xxx.xxxx or xxx xxx xxxx or +1xxxxxxxxx<br />
                                            <em>International:</em> +xxxxxxxxx...
                                        </p>
                                        <?
										if($participant->livingStyle == "InRez") {
											?>
                                            <br />
                                            <p><strong>Select your residence.</strong><br />Since you've noted you will be living in a McGill residence, this information will help us connect you better with your floor fellows if needed.</p>
                                            <select name="residence">
                                                <option value="McConnell" <?= checkForSelected($participant->froshAddress, "McConnell") ?>>McConnell Hall</option>
                                                <option value="Molson" <?= checkForSelected($participant->froshAddress, "Molson") ?>>Molson Hall</option>
                                                <option value="Gardner" <?= checkForSelected($participant->froshAddress, "Gardner") ?>>Gardner Hall</option>
                                                <option value="RVC" <?= checkForSelected($participant->froshAddress, "RVC") ?>>Royal Victoria College (RVC)</option>
                                                <option value="NewRez" <?= checkForSelected($participant->froshAddress, "NewRez") ?>>New Residence Hall</option>
                                                <option value="Carrefour" <?= checkForSelected($participant->froshAddress, "Carrefour") ?>>Carrefour Sherbrooke</option>
                                                <option value="Solin" <?= checkForSelected($participant->froshAddress, "Solin") ?>>Solin Hall</option>
                                                <option value="Citadelle" <?= checkForSelected($participant->froshAddress, "Citadelle") ?>>La Citadelle</option>
                                                <option value="Greenbriar" <?= checkForSelected($participant->froshAddress, "Greenbriar") ?>>Greenbriar Apartments</option>
                                                <option value="MORE" <?= checkForSelected($participant->froshAddress, "MORE") ?>>MORE Houses (inc. University Hall and Pres Rez)</option>
                                                <option value="Varcity515" <?= checkForSelected($participant->froshAddress, "MORE") ?>>Varcity515</option>
                                            </select>
                                            <?
										}
										?>
                                        
                                        <input type="hidden" name="passkey" value="<?= $_REQUEST['passkey'] ?>" />
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button aria-hidden="true" data-dismiss="modal" class="btn">Cancel</button>
                                    <button class="btn btn-inverse" onClick="$('#contactForm').submit()">Save</button>
                                </div>
                            </div>
                        </div>
	                </div>
                    
                    <div class="block span6">
                    	<p class="block-heading">Corrections</p>
	              		<div class="block-body">
                        	<form method="post">
                            	<p>Does your birthday look wrong?  Correct it here:</p>
                                <label for="dateOfBirth"><b>Date of birth:</b></label>
                                <input type="text" id="dateOfBirth" name="dateOfBirth" value="<?= formatForDatepicker($participant->dateOfBirth) ?>" size="50" /><br />
                                <input type="hidden" id="passkey" name="passkey" value="<?= $_REQUEST['passkey'] ?>">
                                
                                <button type="submit" class="btn btn-small btn-danger">Save</button>
                            </form>
                        	<p>If anything else on this page looks incorrect, please <a href="/contact.php">let us know</a>!</p>
                        </div>
	                </div>
	            </div>
	          </div>
	      </div>
	    </div>
        <!-- main-content -->    
	</div>
    <!-- full content -->
    
	<footer>
		<div class="clearfix">
			<p class="pull-left"><a class="notify-disabled" href="#"><i class="icon-chevron-up"></i></a></p>
		    <p class="pull-right">&copy; 2013 <a href="http://ssmu.mcgill.ca" target="_blank">Students' Society of McGill University.</a>  All Rights Reserved.</p>
		</div>
	</footer>
    <? include("includes/unifiedJS.php") ?>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script>
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
				//$('#dateOfBirth').datepicker('setDate', (new Date(<?= $tempDate ?>)) );
				//$('#dateOfBirthRaw').val($('#dateOfBirth').datepicker('getDate').getTime());
				<?
			}
			?>
			
			// listen for the entry fields to change so we can pull the milliseconds from them for use in PHP
			$('#dateOfBirth').bind('change', function() {
				$('#dateOfBirthRaw').val($('#dateOfBirth').datepicker('getDate').getTime());
			});
			
			<?
			if(isset($successMessage)) {
				?>
				$.pnotify({title: 'Success!',
                           text: '<?= $successMessage ?>',
                           type: 'success',
                           shadow: false,
                           sticker: false
                });
				<?
			}
			?>
		});
		
		function checkForm(form) {
			var confirmationText = "";
			
			// get everything from the form
			var phoneNumber = form.elements['phoneNumber'].value;
			
			// check the phone number
			if(phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
				var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
				if(!phoneNumber.match(phoneno)) {
					confirmationText += "The phone number is not a valid format."
				}
			} else {
				if(phoneNumber.length < 10) {
					confirmationText += "The number seems like it is international but is too short.";
				}
			}
			
			
			// Continue with processing or return errors
			if(confirmationText == "") {
				return true;
			} else {
				alert(confirmationText);
				return false;
			}
		}
		
		function checkPhoneFormat(phoneField) {
			// get the value
			var phoneNumber = phoneField.value;
			
			phoneFormat = "Unknown";
			if(phoneNumber.charAt(0) != "+") { // a + marks that it is an international, so don't check for northamerica
				var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
				if(phoneNumber.match(phoneno)) {
					phoneFormat = "North America";
				}
			} else {
				// it starts with a plus, but first check to see if it is a +1 and then 10 digits (a North America number)
				if(phoneNumber.length == 12 && phoneNumber.charAt(1) == '1') {
					// we have a north america number still
					phoneFormat = "North America";
				} else {
					if(phoneNumber.length < 10) {
						phoneFormat = "Incomplete";
					} else {
						phoneFormat = "International";
					}
				}
			}
			
			$("#phoneFormat").html(phoneFormat);
		}
	</script>
</body>
</html>