<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_GET['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// see what payment style the user needs (no payments, all can be paypal, or only some can be paypal)
// start by parsing through the events to register for to see if any of them have a cost
// pull all of the events out of the session variable
$rawEvents = explode(";", $participant->rawRegistrationData);

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
	
	// check to see if the participant aleady has a payment for this event
	// this is to double check that they are not on this page by accident
	$alreadyRegistered = false;
	foreach($participant->payments as $payment) {
		if($payment->event->id == $event->id) {
			// we have a payment, so ignore this event
			$alreadyRegistered = true;
		}
	}
	
	if(!$alreadyRegistered) {
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
	<title>McGill Orientation Week 2013 | Select Payment</title>
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
                	<form method="POST" class="form-horizontal" action="/actions/myweek/processSelectPayment.php">
                        
                        <h2 class="text-center">myWeek<br />Select Payment</h2>
                        
                        <?
						if($requiresPayment) {
						?>
                        <!-- REQUIRE PAYMENT CONTENT -->
                        <p>You will now be able to pay for any events that did not yet have a payment method selected.</p>
                        
                        <input type="hidden" id="methodSelect" name="methodSelect">
                        <input type="hidden" id="passkey" name="passkey" value="<?= $_GET['passkey'] ?>">
                        <input type="hidden" id="save" name="save" value="selectPaymentDone">
                        
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
                            <p>You can pay in person by clicking below.  This means that you will be for your events once you arrive at McGill.  Accepted forms of payment in person are generally cash and credit card.</p>
                            <input type="submit" id="payInPerson" value="Pay in Person" onClick="$('#methodSelect').val('inperson'); return true;" class="btn btn-main-small"/><br /><br />
                            <!-- END ALLOW TO ONLY PAY IN PERSON -->
                        <!-- END REQUIRE PAYMENT CONTENT -->
                        <? } 
						} else {
						?>
                            <!-- NO PAYMENT NECESSARY CONTENT -->
                            <p>You already a have a payment method selected for your events.  You're good to go.</p>
                            <br /><br />
                            <a href="index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Back to myWeek</a>
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