<?
require_once('../functions.php');

session_start();

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/registration/step1.php");
}
if(!isset($_SESSION['step2Complete']) || $_SESSION['step2Complete'] != true) {
	redirect("/registration/step2.php");
}
if(!isset($_SESSION['step3Complete']) || $_SESSION['step3Complete'] != true) {
	redirect("/registration/step3.php");
}
if(!isset($_SESSION['step4Complete']) || $_SESSION['step4Complete'] != true) {
	redirect("/registration/step4.php");
}

$participantService = new services\ParticipantService();
$eventService = new services\EventService();

// get the participant based on student id
$participant = $participantService->getParticipantByStudentId($_SESSION['studentId']);

// see what payment style the user needs (no payments, all can be paypal, or only some can be paypal)
// start by parsing through the events to register for to see if any of them have a cost
// pull all of the events out of the session variable
$rawEvents = explode(";", $_SESSION['registeredEvents']);

// loop through each one to display it
$totalCost = 0;
$payPalCost = 0;
$eventsRequiringNoPayment = array();
$eventsRequiringPayPal = array();
$eventsRequiringInPerson = array();
$eventsAcceptingBoth = array();
foreach($rawEvents as $rawEvent) {
	// break it down again into an array of the ticket info for this event
	//ID, NAME, OPTION, COST, CANREMOVE, CATEGORY
	$pureEvent = explode(",", $rawEvent);
	
	// get the event from the database
	$event = $eventService->getEvent($pureEvent[0]);
	
	// determine what kind of payment it accepts
	if($pureEvent[3] == "0") {
		// there is no cost, so it doesn't require payment
		$eventsRequiringNoPayment[] = $event;
	} else {
		// there is a cost, so let's look at the event to see what it accepts
		$acceptedPayments = $event->acceptedPayments;
		if($acceptedPayments == "paypal,inperson" || $acceptedPayments == "inperson,paypal") {
			// it accepts both
			$eventsAcceptingBoth[] = $event;
			$payPalCost += $pureEvent[3];
		} elseif($acceptedPayments == "paypal") {
			// it only accepts paypal
			$eventsRequiringPayPal[] = $event;
			$payPalCost += $pureEvent[3];
		} elseif($acceptedPayments == "inperson") {
			// it only accepts in person
			$eventsRequiringInPerson[] = $event;
		}
	}
		
	
	// increase the current cost
	$totalCost += $pureEvent[3];
}

// set flags for the activities available on this page
// if there is any cost, then we need to ask the user to make a payment
$requiresPayment = false;
if($totalCost > 0) {
	$requiresPayment = true;
}

// prepare nice print of events that only take in person payments
$inpersonOnlyEvents = "";
for($i = 0; $i < count($eventsRequiringInPerson); $i++) {
	$inpersonOnlyEvents .= toPrettyPrint($eventsRequiringInPerson[$i]->eventName);
	
	if($i < count($eventsRequiringInPerson) - 1) {
		$inpersonOnlyEvents .= ", ";
	}
}

// prepare nice print events that take PayPal
$paypalEventNames = "";
for($i = 0; $i < count($eventsRequiringPayPal); $i++) {
	$paypalEventNames .= toPrettyPrint($eventsRequiringPayPal[$i]->eventName);
	
	if($i < count($eventsRequiringPayPal) - 1) {
		$paypalEventNames .= ", ";
	}
}
if(strlen($paypalEventNames) && count($eventsAcceptingBoth) > 0) {
	$paypalEventNames .= ", ";
}
for($i = 0; $i < count($eventsAcceptingBoth); $i++) {
	$paypalEventNames .= toPrettyPrint($eventsAcceptingBoth[$i]->eventName);
	
	if($i < count($eventsAcceptingBoth) - 1) {
		$paypalEventNames .= ", ";
	}
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 5</title>
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
            	<div class="well span6 offset3 sign-up">
                	<form method="POST" class="form-horizontal" action="/actions/registration/processStep5.php">
                    	<ul class="breadcrumb text-center">
                        	<li>
                            	Welcome
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	Personal Info
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	Events
                                <span class="divider">&gt;</span>
                            </li>
                            <li>
                            	Confirmation
                                <span class="divider">&gt;</span>
                            </li>
                            <li class="active">
                            	Payment
                            </li>
                        </ul>
                        
                        <h2 class="text-center">Registration<br />Step 5<br />Payment</h2>
                        
                        <?
						if($requiresPayment) {
						?>
                        <!-- REQUIRE PAYMENT CONTENT -->
                        <p>We're almost done.</p>
                        <p>You've registered for some events that have a cost, so we will now ask you to decide how to pay for those events.</p>
                        
                        <p>The following events are free to attend or are reminders that do not have a cost:</p>
                        <ul>
                        	<?
							foreach($eventsRequiringNoPayment as $event) {
								echo("<li>" . toPrettyPrint($event->eventName) . "</li>");
							}
							?>
                        </ul>
                        
                        <input type="hidden" id="methodSelect" name="methodSelect" />
                        <input type="hidden" name="save" value="step5done">
                        
						<? if(count($eventsRequiringPayPal)> 0 || count($eventsAcceptingBoth) > 0) { ?>
                            <!-- ALLOW BOTH PAYMENTS -->
                            <p>You can pay for your events (<?= $paypalEventNames ?>) via credit card, debit card, direct payment, or via PayPal account through PayPal by clicking below.</p>
                            <p class="lead"><input type="image" id="payWithPayPal" value="Pay via PayPal" onclick="$('#methodSelect').val('paypal'); return true;" style="vertical-align:top;margin-bottom:5px" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" /> $<?= $payPalCost ?></p>
                            <?
                            if(strlen($inpersonOnlyEvents)) {
                            ?>
                            <p><em>(Please note: The following events cannot be paid online - <?= $inpersonOnlyEvents ?>.  You will need to pay for these events (totalling $<?= ($totalCost-$payPalCost) ?>) once you arrive at the event itself.)</em></p>
                            <? } ?>
                            <br /><br />
                            
                            <p>Alternatively, if you are not able to pay via PayPal or you would simply prefer to pay in person (cash or credit card), you can pay in person by clicking below.  This means that you will need to pay for your events once you arrive at McGill.  You will receive instructions via email closer to your arrival.  Accepted forms of payment in person are generally cash and credit card.</p>
                            <input type="submit" id="payInPerson" value="Pay in Person" onClick="$('#methodSelect').val('inperson'); return true;" class="btn btn-main-small"/><br /><br />
                            <!-- END ALLOW BOTH PAYMENTS -->
                        <? } else { ?>
                            <!-- ALLOW TO ONLY PAY IN PERSON -->
                            <p>you can pay in person by clicking below.  This means that you will be for your events once you arrive at McGill.  Accepted forms of payment in person are generally cash and credit card.</p>
                            <input type="submit" id="payInPerson" value="Pay in Person" onClick="$('#methodSelect').val('inperson'); return true;" class="btn btn-main-small"/><br /><br />
                            <!-- END ALLOW TO ONLY PAY IN PERSON -->
                        <!-- END REQUIRE PAYMENT CONTENT -->
                        <? } 
						} else {
						?>
                            <!-- NO PAYMENT NECESSARY CONTENT -->
                            <p>And we're done!  Since all of the events you have registered for so far are free, you do not need to make any payments and your registration is now complete.</p>
                            <p>We are excited to have you registered for McGill Orientation Week 2013, and we look forward to seeing you on campus soon.</p>
                            <p>Now it's time to start familarizing yourself with myWeek, your Orientation Week portal for the duration of O-Week.  Content will be coming out between your registration and when O-Week actually starts, so check back frequently to see what's new.</p>
                            <p>myWeek will show you your calendar for the week, send you messages from various event coordinators, and be your all-around digital homebase during O-Week.</p>
                            <br /><br />
                            <a href="/myweek/index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Check it out!</a>
                            <?
                            // destroy the session so they cannot redo this
                            session_destroy();
                            unset($_SESSION);
                            ?>
                            <!-- END NO PAYMENT NECESSARY CONTENT -->
                        <?
                        } 
					?>
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