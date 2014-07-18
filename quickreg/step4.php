<?
require_once('../functions.php');

session_start();

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/quickreg/step1.php");
}
if(!isset($_SESSION['step2Complete']) || $_SESSION['step2Complete'] != true) {
	redirect("/quickreg/step2.php");
}
if(!isset($_SESSION['step3Complete']) || $_SESSION['step3Complete'] != true) {
	redirect("/quickreg/step3.php");
}

// see if we have already done step 4
if(isset($_SESSION['step4Complete']) && $_SESSION['step4Complete'] == true) {
	// it has been; send them to step 5
	redirect("/quickreg/step5.php");
}

// format name
$nameForDisplay = $_SESSION['firstName'] . " ";
if(strlen($_SESSION['preferredName'])) {
	$nameForDisplay .= "(" . $_SESSION['preferredName'] . ") ";
}
$nameForDisplay .= $_SESSION['lastName'];
if(strlen($_SESSION['genderPronoun'])) {
	$nameForDisplay .= " (" . $_SESSION['genderPronoun'] . ")";
}

// format the dietary needs
if(count($_SESSION['dietaryRestrictions']) > 0) {
	$dietaryForDisplay = implode(", ", $_SESSION['dietaryRestrictions']);
	$dietaryForDisplay = ucwords($dietaryForDisplay);
} else {
	$dietaryForDisplay = "None to note";
}

// a function to display "None" if the passed text is empty
function formatOptionalText($text) {
	if(strlen($text)) {
		return $text;
	} else {
		return "None to note";
	}
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 4</title>
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
            	<div class="well span8 offset2 sign-up">
                	<form method="POST" class="form-horizontal" action="/actions/quickreg/processStep4.php" onSubmit="return confirmContinue()">
                    	<ul class="breadcrumb text-center">
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
                        
                        <h2 class="text-center">Registration<br />Step 4<br />Confirmation</h2>
                        
                        <p>Please confirm that the information below is completely correct.  After completing this step, you will not be able to change details until after payment (if applicable).</p>
                        <p>Please note: You can register and unregister for events that are free at any time after this step; however, events that have a cost and that you choose to pay for online can only be unregistered for by contacting the event organizer.  If you did not choose all of the paid events you want to register for, you will be able to register for them at a later time as well.</p>
                        <p>Finally, if your information does not match that on official McGill records, you will be contacted by McGill directly to change your information.  Please double check that everything is correct.</p>
                        
                        <h3>Personal Information</h3>
                        <p><strong>Identity:</strong><br /><?= $nameForDisplay ?></p>
                        <p><strong>Student ID:</strong><br /><?= $_SESSION['studentId'] ?></p>
                        <p><strong>Email:</strong><br /><?= $_SESSION['email'] ?></p>
                        <p><strong>Living:</strong><br /><?= ucwords(convertCodeToLivingStyle($_SESSION['livingStyle'])) ?></p>
                        <p><strong>Originating from:</strong><br /><?= strtoproper(toPrettyPrint(convertCodeToOrigin($_SESSION['placeOfOrigin']))) ?></p>
                        <p><strong>Entering year:</strong><br /><?= $_SESSION['enteringYear'] ?></p>
                        <p><strong>Shirt size:</strong><br /><?= $_SESSION['tshirtSize'] ?></p>
                        <p><strong>Faculty:</strong><br /><?= convertCodeToFaculty($_SESSION['faculty']) ?></p>
                        <p><strong>Born:</strong><br /><?= $_SESSION['dateOfBirth'] ?></p>
                        <p><strong>Dietary needs:</strong><br /><?= toPrettyPrint($dietaryForDisplay) ?></p>
                        <p><strong>Allergies:</strong><br /><?= toPrettyPrint(formatOptionalText($_SESSION['allergies'])) ?></p>
                        <p><strong>Physical needs:</strong><br /><?= toPrettyPrint(formatOptionalText($_SESSION['physicalNeeds'])) ?></p>
                        
                        <p>If any of this is incorrect, please head back to <a href="step2.php">Step 2</a> to edit it.  Everything you have done so far will be saved for a short while, so make whatever changes are necessary on the appropriate pages.</p>
                        
                        <h3>Event Registration</h3>
                        <p>Registered for:</p>
                        <ul>
                        	<?
							/**
							// pull all of the events out of the session variable
							$rawEvents = explode(";", $_SESSION['registeredEvents']);
							
							// loop through each one to display it
							$totalCost = 0;
							foreach($rawEvents as $rawEvent) {
								// break it down again into an array of the ticket info for this event
								//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
								$pureEvent = explode(",", $rawEvent);
								
								// did they take the option?
								$optionText = "";
								if($pureEvent[2] == "true") {
									$optionText = " (with option)";
								}
								
								// print the line
								echo("<li><strong>$" . $pureEvent[3] . "</strong>: " . $pureEvent[1] . $optionText . "</li>");
								
								// increase the current cost
								$totalCost += $pureEvent[3];
							}
							**/
							?>
                        	<!--<li><p>Frosh - <strong>$108</strong></p></li>-->
                        </ul>
                        <!--
                        <p class="lead">Total Cost: <strong>$<? //$totalCost ?></strong></p>
                        <p>If any of this is incorrect, please head back to <a href="step3.php">Step 3</a> to edit it.  Everything you have done so far will be saved for a short while, so make whatever changes are necessary on the appropriate pages.</p>
                        -->
                        <br />
                        
                        <input type="hidden" name="save" value="step4done">
                        <button type="submit" class="btn btn-main-small">Confirm and On To Step 5 <i class="icon-chevron-right"></i></button>
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
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/rotate.js"></script>
    
    <script>
      !function ($) {
        $(function(){
          $('#header').carousel()
        })
      }(window.jQuery)
	  
	  // asks the user if they are sure they want to continue
	  function confirmContinue() {
		  var response = confirm("Once you confirm on this step, you will not be able to make changes to your information until after the next page.  Make sure everything is correct before going on.  Are you ready to confirm and continue to the last step?");
		  return response;
	  }
    </script>
</body>
</html>