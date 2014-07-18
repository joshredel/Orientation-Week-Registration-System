<?
require_once('../functions.php');

session_start();

// check to see if step 1 has been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/registration/step1.php");
}

// see if we have already done step 4
if(isset($_SESSION['step4Complete']) && $_SESSION['step4Complete'] == true) {
	// it has been; send them to step 5
	redirect("/registration/step5.php");
}

// set all form variables to blank
$firstName = "";
$lastName = "";
$preferredName = "";
$genderPronoun = "";
$studentId = "";
$email = "";
$livingStyle = "";
$placeOfOrigin = "";
$enteringYear = "";
$tshirtSize = "";
$faculty = "";
$dateOfBirth = ""; //**
$dateOfBirthRaw = "";
$dietaryRestrictions = "";
$allergies = ""; //**
$physicalNeeds = "";
$approveFacultyCheck = ""; //**
$approveFacultyCheckedOff = "";

// if we have already completed this step, then re-populate the form for editing
if(isset($_SESSION['step2Complete']) && $_SESSION['step2Complete'] == true) {
	$firstName = $_SESSION['firstName'];
	$lastName = $_SESSION['lastName'];
	$preferredName = $_SESSION['preferredName'];
	$genderPronoun = $_SESSION['genderPronoun'];
	$studentId = $_SESSION['studentId'];
	$email = $_SESSION['email'];
	$livingStyle = $_SESSION['livingStyle'];
	$placeOfOrigin = $_SESSION['placeOfOrigin'];
	$enteringYear = $_SESSION['enteringYear'];
	$tshirtSize = $_SESSION['tshirtSize'];
	$faculty = $_SESSION['faculty'];
	$dateOfBirth = $_SESSION['dateOfBirth'];
	$dateOfBirthRaw = $_SESSION['dateOfBirthRaw'];
	$dietaryRestrictions = $_SESSION['dietaryRestrictions'];
	$allergies = $_SESSION['allergies'];
	$physicalNeeds = $_SESSION['physicalNeeds'];
	$approveFacultyCheck = $_SESSION['approveFacultyCheck'];
	
	// see if the faculty release should be checked
	if($approveFacultyCheck) {
		$approveFacultyCheckedOff = "checked";
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

// determines whether the passed dietary restriction was already entered by the user to see if a checkbox should be automatically checked
function dietaryShouldBeChecked($restriction) {
	if(isset($_SESSION['step2Complete']) && $_SESSION['step2Complete'] == true && isset($_SESSION['dietaryRestrictions']) && in_array($restriction, $_SESSION['dietaryRestrictions'])) {
		return "checked";
	} else {
		return "";
	}
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 2</title>
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">

	<!-- Styles -->
	<link rel="stylesheet" href="/css/frontend/font-awesome.min.css">
	<link rel="stylesheet" href="/css/frontend/animate.css">
	<link href='http://fonts.googleapis.com/css?family=Lato:400,100,100italic,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
        
	<link rel="stylesheet" href="/css/frontend/bootstrap.min.css">
	<link rel="stylesheet" href="/css/frontend/main.css">
	<link rel="stylesheet" href="/css/frontend/custom-styles.css">
    
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />

	<script src="/js/modernizr-2.6.2-respond-1.1.0.min.js"></script>

	<!-- Fav and touch icons -->
	<!--<link rel="shortcut icon" href="/favicon.png">-->
</head>

<body class="sign-up">
	<? include_once("../analytics.php") ?>
	<div class="navbar navbar-inverse navbar-fixed-top animated fadeInDownBig">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="/index.php"><img src="/images/frontend/logo.png" alt="Title"></a>
				<div class="nav-collapse collapse">
					<ul class="nav pull-right">
						<li><a href="/index.php">Home</a></li>
						<li><a href="/events.php">Events</a></li>
						<li><a href="/contact.php">Contact &amp; Connect</a></li>
						<li><a href="/map.php">Map</a></li>
						<li><a href="/tips.php">Helpful Hints</a></li>
						<li><a href="/faq.php">FAQs</a></li>
						<li><a href="/parents.php">Parents</a></li>
						<!--<li><a href="login.php"><span class="light-gray"><i class="icon-user"></i> Login</span></a></li>-->
						<li><a href="/registration/"><span class="menu-button">Register</span></a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
	</div>
    
    
    <!--Main Content-->
    <section id="content" class="sign-up-page">
    	<div class="container">
        	<div class="row-fluid">
            	<div class="well span6 offset3 text-center sign-up">
                	<form id="signup" class="form-horizontal" method="post" action="/actions/registration/processStep2.php" onSubmit="saving = true;return checkForm(this);">
                    	<ul class="breadcrumb">
                        	<li class="active">
                            	<a href="step1.php">Welcome</a>
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	<?= printBreadcrumbLinkIfComplete("step1", "step2", "Personal Info") ?>
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	<?= printBreadcrumbLinkIfComplete("step2", "step3", "Events") ?>
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	<?= printBreadcrumbLinkIfComplete("step3", "step4", "Confirmation") ?>
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	Payment
                            </li>
                        </ul>
                        
                        <h2>Registration<br />Step 2<br />Personal Information</h2>
                        
                        <p>(* mandatory fields)</p><br />
                        
                        <!-- first name -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">First Name* <i class="icon-user"></i></span>
                                    <input type="text" class="input-xlarge field-popover" id="firstName" name="firstName" placeholder="Enter your first name" value="<?= $firstName ?>" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="We need your <strong>LEGAL</strong> first name, please!">
                                </div>
                            </div>
                        </div>
                        
                        <!-- last name -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Last Name* <i class="icon-user"></i></span>
                                    <input type="text" class="input-xlarge field-popover" id="lastName" name="lastName" placeholder="Enter your last name" value="<?= $lastName ?>" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="We'll also need your full, legal last name, please.">
                                </div>
                            </div>
                        </div>
                        
                        <!-- preferred name -->
                        <div class="control-group span12">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Preferred Name <i class="icon-user"></i></span>
                                    <input type="text" class="input-xlarge field-popover" id="preferredName" name="preferredName" placeholder="Enter your preferred name" value="<?= $preferredName ?>" data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="If you don't go by your legal first name, tell us what you prefer to be called, and we'll be sure to call you by this name.">
                                </div>
                            </div>
                        </div>
                        
                        <!-- preferred gender pronoun -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Preferred Pronoun <i class="icon-user"></i></span>
                                    <input type="text" class="input-xlarge field-popover" id="genderPronoun" name="genderPronoun" placeholder="Enter your preferred gender pronoun" value="<?= $genderPronoun ?>" data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Let us know how you identify (optionally, of course!).  We want to make sure we respect your gender identity, so fill this in to make sure we speak to you as you prefer.  Examples are: ze, zie, she, he, they, xe, etc (but we're open to whatever best represents you!).">
                                </div>
                            </div>
                        </div>
                        
                        <!--student ID -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Student ID* <i class="icon-barcode"></i></span>
                                    <input type="number" maxlength="9" class="input-xlarge field-popover" id="studentId" name="studentId" placeholder="Enter your student ID number" value="<?= $studentId ?>" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="This will be your McGill-issued, 9-digit student identification number.">
                                </div>
                            </div>
                        </div>
                        
                        <!-- email address -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Email* <i class="icon-envelope"></i></span>
                                    <input type="email" class="input-xlarge field-popover" id="email" name="email" placeholder="Enter your preferred email address" value="<?= $email ?>" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Let us know the email address where we can best contact you.  Please be sure to enter an email address that you will check frequently. (Promise?  Good.)">
                                </div>
                            </div>
                        </div>
                        
                        <!-- living style -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Living Style* <i class="icon-home"></i></span>
                                    <select class="input-xlarge field-popover" id="livingStyle" name="livingStyle" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Where will you be living in the coming year?">
                                        <option value="">Select your living style...</option>
                                        <option value="InRez" <?= checkForSelected($livingStyle, "InRez") ?>>In a McGill Residence</option>
                                        <option value="OffCampus" <?= checkForSelected($livingStyle, "OffCampus") ?>>Off Campus</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- place of origin -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Place of Origin* <i class="icon-globe"></i></span>
                                    <select class="input-xlarge field-popover" id="placeOfOrigin" name="placeOfOrigin" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="This field should reflect your student status (what kind of tuition will you be paying?) and is important because it helps us make sure you are going to events best suited for wherever you are coming from!">
                                        <option value="">Select your place of origin...</option>
                                        <option value="Quebec" <?= checkForSelected($placeOfOrigin, "Quebec") ?>>Quebec</option>
                                        <option value="RestOfCanada" <?= checkForSelected($placeOfOrigin, "RestOfCanada") ?>>Rest of Canada (outside of Quebec)</option>
                                        <option value="International" <?= checkForSelected($placeOfOrigin, "International") ?>>International</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- entering year -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Entering Year* <i class="icon-group"></i></span>
                                    <select class="input-xlarge field-popover" id="enteringYear" name="enteringYear" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Simple: Are you starting in U0 (typically no AP, IB, FB, or CEGEP) or U1 (completed a university-equivalent year before coming to McGill)?  If you aren't a 'first-year' but are a transfer or exchange student, let us know here!  This information will be available on Minerva.">
                                        <option value="">Select your entering year...</option>
                                        <option value="U0" <?= checkForSelected($enteringYear, "U0") ?>>U0</option>
                                        <option value="U1" <?= checkForSelected($enteringYear, "U1") ?>>U1</option>
                                        <option value="Transfer" <?= checkForSelected($enteringYear, "Transfer") ?>>Transfer Student</option>
                                        <option value="Exchange" <?= checkForSelected($enteringYear, "Exchange") ?>>Exchange Student</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- shirt size -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">T-Shirt Size* <i class="icon-resize-full"></i></span>
                                    <select class="input-xlarge field-popover" id="tshirtSize" name="tshirtSize" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Simple again!  What (American-standard) size t-shirt do you prefer to wear?  This will only apply if you are registering for an event that includes a t-shirt.">
                                        <option value="">Select your t-shirt size...</option>
                                        <option value="XS" <?= checkForSelected($tshirtSize, "XS") ?>>XS</option>
                                        <option value="S" <?= checkForSelected($tshirtSize, "S") ?>>S</option>
                                        <option value="M" <?= checkForSelected($tshirtSize, "M") ?>>M</option>
                                        <option value="L" <?= checkForSelected($tshirtSize, "L") ?>>L</option>
                                        <option value="XL" <?= checkForSelected($tshirtSize, "XL") ?>>XL</option>
                                        <option value="XXL" <?= checkForSelected($tshirtSize, "XXL") ?>>XXL</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- faculty -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Faculty* <i class="icon-book"></i></span>
                                    <select class="input-xlarge field-popover" id="faculty" name="faculty" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Tell us in which faculty you will be studying at McGill.  This is important to make sure we know what events to help you pick (and don't lie... we'll know, we're quite keen!)<br /><br />Ok, so this can be a bit tricky.  But if you read up here, we hope we can help!<br /><br />This is the faculty in which you will be studying, not the degree program or the department.<br />Kinesiology (BSc) students should choose Faculty of Education<br />Pre-Med or Pre-Dent students should choose Faculty of Science">
                                        <option value="">Select your faculty...</option>
                                        <option value="AG" <?= checkForSelected($faculty, "AG") ?>>Faculty of Agriculture / Environmental Science</option>
                                        <option value="AR" <?= checkForSelected($faculty, "AR") ?>>Faculty of Arts</option>
                                        <option value="AS" <?= checkForSelected($faculty, "AS") ?>>Faculty of Arts & Science</option>
                                        <!--<option value="DE" <?= checkForSelected($faculty, "DE") ?>>Faculty of Dentistry</option>-->
                                        <option value="ED" <?= checkForSelected($faculty, "ED") ?>>Faculty of Education</option>
                                        <option value="EN" <?= checkForSelected($faculty, "EN") ?>>Faculty of Engineering</option>
                                        <option value="LW" <?= checkForSelected($faculty, "LW") ?>>Faculty of Law</option>
                                        <!--<option value="MD" <?= checkForSelected($faculty, "MD") ?>>Faculty of Medicine</option>-->
                                        <option value="RS" <?= checkForSelected($faculty, "RS") ?>>Faculty of Religious Studies</option>
                                        <option value="SC" <?= checkForSelected($faculty, "SC") ?>>Faculty of Science</option>
                                        <option value="MG" <?= checkForSelected($faculty, "MG") ?>>Desautels Faculty of Management</option>
                                        <option value="MU" <?= checkForSelected($faculty, "MU") ?>>Schulich School of Music</option>
                                        <option value="EN" <?= checkForSelected($faculty, "EN") ?>>School of Architecture</option>
                                        <option value="NU" <?= checkForSelected($faculty, "NU") ?>>School of Nursing</option>
                                        <option value="PO" <?= checkForSelected($faculty, "PO") ?>>School of Physical &amp; Occupational Therapy</option>
                                        <option value="AR" <?= checkForSelected($faculty, "AR") ?>>School of Social Work</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- date of birth -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Date of Birth* <i class="icon-calendar"></i></span>
                                    <input type="text" class="input-xlarge field-popover" id="dateOfBirth" name="dateOfBirth" placeholder="Select your date of birth" value="<?= $dateOfBirth ?>" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Another easy one: what is your date of birth (remember: no lying; this isn't our first rodeo).">
                                    <input type="hidden" id="dateOfBirthRaw" name="dateOfBirthRaw" value="<?= $dateOfBirthRaw ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- dietary restrictions -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Dietary Needs <i class="icon-food"></i></span>
                                    <!--<textarea name="dietaryRestrictions" id="dietaryRestrictions" class="input-xlarge field-popover" rows="8" placeholder="The message you want to send to the team." data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Let us know how you prefer to eat.  At events that provide food, we will do our absolute best to make sure you have food that suits your needs.  But you have to tell us for that to happen!"></textarea>-->
                                    <label class="checkbox"><input type="checkbox" name="dietaryRestrictions[]" value="vegan" <?= dietaryShouldBeChecked("vegan") ?>>Vegan</label>
									<label class="checkbox"><input type="checkbox" name="dietaryRestrictions[]" value="vegetarian" <?= dietaryShouldBeChecked("vegetarian") ?>>Vegetarian</label>
                                    <label class="checkbox"><input type="checkbox" name="dietaryRestrictions[]" value="kosher" <?= dietaryShouldBeChecked("kosher") ?>>Kosher</label>
                                    <label class="checkbox"><input type="checkbox" name="dietaryRestrictions[]" value="halal" <?= dietaryShouldBeChecked("halal") ?>>Halal</label>
                                    <label class="checkbox"><input type="checkbox" name="dietaryRestrictions[]" value="celiac" <?= dietaryShouldBeChecked("celiac") ?>>Celiac</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- allergies -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Allergies <i class="icon-medkit"></i></span>
                                    <textarea name="allergies" id="allergies" class="input-xlarge field-popover" rows="8" placeholder="Give a brief description of any allergies you might have." data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="This one is <strong>super</strong> important!  Please tell us any allergies or health conditions that we should know about.  It can be anything minor (there is no allergy to small!) or major (this is particularly important)."><?= $allergies ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- physical needs -->
                        <div class="control-group">
                            <div class="controls">
                                <div class="input-prepend">
                                    <span class="add-on">Physical Needs <i class="icon-user"></i></span>
                                    <textarea name="physicalNeeds" id="physicalNeeds" class="input-xlarge field-popover" rows="8" placeholder="Give a brief description of any physical needs you might have." data-toggle="popover"  data-placement="top" data-trigger="focus" data-html="true" data-content="If you think we should know about any physical needs you might have and you are comfortable letting us know about them, please describe them here so that we can make sure Orientation Week works well for you!"><?= $physicalNeeds ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- password -->
                        <!--
                        <div class="control-group">
                            <div class="controls">
                                    <div class="input-prepend">
                                <span class="add-on">Password* <i class="icon-lock"></i></span>
                                    <input type="Password" id="passwd" class="input-xlarge field-popover" name="password" placeholder="Enter a password for your myWeek page" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="You will use this password to login into myWeek, the portal used to managed your events, view your Orientation Week schedule, and communicate with your Orientation Week leaders.">
                                </div>
                            </div>
                        </div>
                        -->
                        <!-- password confirmation -->
                        <!--
                        <div class="control-group">
                            <div class="controls">
                                    <div class="input-prepend">
                                <span class="add-on">Confirm Password* <i class="icon-lock"></i></span>
                                    <input type="Password" id="conpasswd" class="input-xlarge field-popover" name="passwordConfirmation" placeholder="Re-enter the password from above" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Easy: just confirm your desired password by re-entering it here.">
                                </div>
                            </div>
                        </div>
                        -->
                        
                        <!-- privacy info -->
                        <div class="control-group">
                            <div class="controls">
                                <legend>Privacy</legend>
                                <p>We realize that much of the information above is personal information, and we make a firm commitment to all students using this website that this information is kept confidential and will never be used for purposes outside of Orientation Week.  Anyone who has any sort of access to this information has signed confidentiality agreements.  If you ever have any concerns about the privacy of your information, please contact us.</p>
                            </div>
                        </div>
                        
                        <!-- approve faculty check -->
                        <div class="control-group">
                            <div class="controls">
                                <legend>Release Of Information</legend>
                                <p>I authorize the Students' Society of McGill University (SSMU) and the organizers of the Faculty orientation events at McGill University to release my orientation registration information to McGill University to permit the verification and validity of my orientation registration information. I understand that my Faculty orientation event registration must correspond to the Faculty to which I have been admitted and confirmed.  McGill University will communicate with individuals who have registered in an orientation session that does not conform to his/her admitted and confirmed status at McGill.<br /><br /></p>
                                
                                <label class='checkbox text-left'><input type='checkbox' name='approveFacultyCheck' value='1' <?= $approveFacultyCheckedOff ?>><b>I have read and agree to the above terms</b></label></br>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <div class="controls">
                            	<input type="hidden" name="save" value="step2done">
                                <button type="submit" class=" btn btn-main-small">On to Step 3 <i class="icon-chevron-right"></i></button>
                            </div>
                            <!--a class="small-message" href="#"><small>Already Registered?</small></a>-->
                        </div>
        	    	</form>
                </div><!--End Span8-->
            </div><!--End Row-->
	    </div><!--End Container-->
     </section>
        
    
    <!-- Footer -->
    <section id="footer">
		<div class="container">
			<div class="row-fluid">
				<div class="span4 text-left copyright">
					<p>&copy; Students' Society of McGill University. All Rights Reserved.</p><br />
				</div>
				
				<div class="span8 text-right">
					<ul class="footer-links">
						<li><a href="/index.php">Home</a></li>
						<li><a href="/events.php">Events</a></li>
						<li><a href="/contact.php">Contact &amp; Connect</a></li>
						<li><a href="/map.php">Map</a></li>
						<li><a href="/tips.php">Helpful Hints</a></li>
						<li><a href="/faq.php">FAQs</a></li>
						<li><a href="/parents.php">Parents</a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>
    
    <!-- Javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.9.1.min.js"><\/script>')</script>-->
    <script src="/js/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/rotate.js"></script>
    <script src="/js/formchanges.js"></script>
    
    <script>
    !function ($) {
		$(function(){
			$('#header').carousel()
		})
    }(window.jQuery)
	
	$('.field-popover').popover();
	
	$(function(){
		// add the date pickers for the registration open/close dates
		$('#dateOfBirth').datepicker({ 
				dateFormat: "yy-mm-dd",
				changeMonth: true,
      			changeYear: true,
				minDate: new Date(1950, 0, 0),
				maxDate: new Date(2000, 0, 0)
	  	});
		
		// listen for the entry fields to change so we can pull the milliseconds from them for use in PHP
		$('#dateOfBirth').bind('change', function() {
			$('#dateOfBirthRaw').val($('#dateOfBirth').datepicker('getDate').getTime());
		});
	});
	
	// get variables for the form update mechanisms
	var saving = false;
	var form = document.getElementById("signup");
	
	// check the form for completion
	function checkForm(form) {
		var confirmationText = "";
		
		// get everything from the form
		var firstName = form.elements['firstName'].value;
		var lastName = form.elements['lastName'].value;
		var studentId = form.elements['studentId'].value;
		var email = form.elements['email'].value;
		var livingStyle = form.elements['livingStyle'].value;
		var placeOfOrigin = form.elements['placeOfOrigin'].value;
		var enteringYear = form.elements['enteringYear'].value;
		var tshirtSize = form.elements['tshirtSize'].value;
		var faculty = form.elements['faculty'].value;
		var dateOfBirth = form.elements['dateOfBirthRaw'].value;
		
		// check the name
		if(firstName == "") {
			confirmationText += "- first name\n";
		}
		if(lastName == "") {
			confirmationText += "- last name\n";
		}
		
		// check the student ID
		if(isNaN(studentId) || studentId == "" || studentId.length != 9 )
		{
			confirmationText += "- valid, 9-digit student ID numbers\n";
		}
		
		// check the emails
		if(email == "") {
			confirmationText += "- email address\n";
		}
		
		// check living style
		if(livingStyle == "")
		{
			confirmationText += "- living style\n";
		}
		
		// check that a place of origin has been selected
		if(placeOfOrigin == "") {
		   confirmationText += "- place of origin\n";
		}
		
		// check the entering year
		if(enteringYear == "")
		{
			confirmationText += "- entering year\n";
		}
		
		// check that a shirt size has been selected
		if(tshirtSize == "") {
		   confirmationText += "- t-shirt size\n";
		}
		
		// check the Faculty
		if(faculty == "") {
			confirmationText += "- faculty\n";
		}	
		
		// check birth date
		if(dateOfBirthRaw == "") {
			confirmationText += "- date of birth\n";
		}
		
		/*
		// check passwords
		if(password == "" || password == "") {
			confirmationText += "- either of the password fields\n";
		} else {
			// now check to make sure they are the same
			if(password != password) {
				confirmationText += "- matching passwords for confirmation\n";
			}
		}
		*/
		
		// continue with processing or return errors
		if(confirmationText == "") {
			return true;
		} else {
			confirmationText = "It looks like the form is missing a few things:\n" + confirmationText;
			alert(confirmationText);
			saving = false;
			return false;
		}
	}
	
	$(window).on('beforeunload', function() {
		if(!saving) {
			var f = FormChanges(form);
			if (f.length > 0) {
				return "You haven't saved changes you have made on this page.  Please use the 'ON TO STEP 3' button to save your changes and continue.";
			}
		}
	});
    </script>
</body>
</html>