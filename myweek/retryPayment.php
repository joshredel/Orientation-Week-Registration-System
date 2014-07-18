<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$paymentService = new services\PaymentService();
$participant = $participantService->getParticipantByRegistrationPassword($_GET['passkey']);
$event = $eventService->getEvent($_GET['eid']);

//redirect if the passkey doesn't match any participant or the eid doesn't match any events
if($participant == null || $event == null) {
	redirect("/");
}

// first, we need to see if any of the payments we are about to retry have a status message
// if they do, we cannot proceed!
// we can also calculate total payment owed in this loop
$hasStatus = false;
$totalCost = 0;
foreach($participant->payments as $payment) {
	// check for a match
	if($payment->event->id == $event->id) {
		// we found a payment for this event
		// check if it has a status
		if($payment->status != null || $payment->hasPaid == true) {
			// there was a non-null status; mark as such!
			$hasStatus = true;
			break;
		}
		
		// add the total
		$totalCost += $payment->finalCost;
	}
}

// see if our event accepts both payment types
$acceptedPayments = "";
if($event->acceptedPayments == "paypal,inperson" || $event->acceptedPayments == "inperson,paypal") {
	// it accepts both
	$acceptedPayments = "both";
} elseif($acceptedPayments == "paypal") {
	// it only accepts paypal
	$acceptedPayments = "paypal";
} elseif($acceptedPayments == "inperson") {
	// it only accepts in person
	$acceptedPayments = "inperson";
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Retry Payment</title>
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
                	<?
					if($hasStatus) {
						?>
                        <p>You cannot retry payment for <?= toPrettyPrint($event->eventName) ?> as you have already successfully completed payment or already attempted payment and that payment has a pending status.</p>
                        <p>Please check your PayPal account and <a href="/contact.php">contact us</a> if you need help resolving this issue.</p>
                        <br /><br />
                        <a href="index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Back to myWeek</a>
                        <?
					} else {
						?>
                        <form method="POST" class="form-horizontal" action="/actions/myweek/processRetryPayment.php">
                            
                            <h2 class="text-center">myWeek<br />Retry Payment for:<br /><?= toPrettyPrint($event->eventName) ?></h2>
                            
                            <p>You will now be able to retry payment for the above event.  You can either try again with the same method or change your desired method of payment.</p>
                            <br />
                            
                            <input type="hidden" id="methodSelect" name="methodSelect">
                            <input type="hidden" id="passkey" name="passkey" value="<?= $_GET['passkey'] ?>">
                            <input type="hidden" id="eid" name="eid" value="<?= $_GET['eid'] ?>">
                            <input type="hidden" id="save" name="save" value="retryPaymentDone">
                            
                            <? if($acceptedPayments == "both") { ?>
                                <!-- ALLOW BOTH PAYMENTS -->
                                <p>You can retry paying for <?= toPrettyPrint($event->eventName) ?> via credit card, debit card, direct payment, or via PayPal account through PayPal by clicking below.</p>
                                <p class="lead"><input type="image" id="payWithPayPal" value="Pay via PayPal" onclick="$('#methodSelect').val('paypal'); return true;" style="vertical-align:top;margin-bottom:5px" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" /> $<?= $totalCost ?></p>
                                <br />
                                
                                <p>Alternatively, if you are not able to pay via PayPal or you would simply prefer to pay in person (cash or credit card), you can pay in person by clicking below.  This means that you will need to pay for your events once you arrive at McGill.  You will receive instructions via email closer to your arrival.  Accepted forms of payment in person are generally cash and credit card.</p>
                                <input type="submit" id="payInPerson" value="Pay in Person" onClick="$('#methodSelect').val('inperson'); return true;" class="btn btn-main-small"/><br /><br />
                                <!-- END ALLOW BOTH PAYMENTS -->
                            <? } else { ?>
                                <!-- ALLOW TO ONLY PAY IN PERSON -->
                                <p>You can only pay for this event in person, and you have already marked that you want to pay in person.</p>
                                <!-- END ALLOW TO ONLY PAY IN PERSON -->
                            <? } ?>
                            
                            <p>If you don't wish to retry any payments, you can go back.</p>
                            <a href="index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Back to myWeek</a>
                        </form>
                        <?
					}
					?>
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