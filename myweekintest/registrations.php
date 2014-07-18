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
            
            <a class="brand" href="index.php?passkey=<?= $_GET['passkey'] ?>"><img src="/images/frontend/logo.png" alt="Logo"></a>
	    </div>
	</div>
    <!-- top bar navigation --> 
    
    <!-- sidebar navigation -->
	<div data-offset-top="360" data-spy="affix" class="sidebar-nav affix">
    	<!-- floating logo with notifications -->
	    <div class="sidebar-avatar">
	        <img src="assets/images/myweek.jpg" alt="myWeek" class="thumbnail-avatar">
            <!--
	        <a href="#"><div class="sidebar-avatar-message"><div class="notify ">7</div></div></a>
	        <a href="#"><div class="sidebar-avatar-notify"><div class="notify notify-message"><i class="icon-envelope"></i></div></div></a>
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
            
	        <h1 class="page-title">Event Registrations and Payments</h1>
	    </div>
        <!-- end notification bar -->
	    
        <!-- main content -->
	    <div class="wrapper-content">
	    	<div class="container-fluid">
            	<div class="row-fluid">
	            	<div class="block span12">
	              		<div class="block-body">
                        	<h3>Welcome, <?= getDisplayName($participant) ?></h3>
                            <p>This is myWeek, or rather: your week.  This page is your digital home from now until the end of Orientation Week.  Make sure to bookmark this page on your computer, and if you have one, on your smartphone.  Enjoy your very first week at McGill!</p>
                        </div>
                    </div>
                </div>
                
				<div class="row-fluid">
	            	<div class="block span6">
                    	<p class="block-heading">Event Registrations</p>
	              		<div class="block-body">
                            <p>You are officially registered for the events you see below, regardless of payment status.  Click on the event title for more information.</p>
                            <p><strong>Registered for:</strong></p>
                            <div id="eventAccordion" class="accordion">
                                <?
								/*
                                // pull all of the events out of the session variable
                                $rawEvents = explode(";", $participant->rawRegistrationData);
                                
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
                                    echo("<li>" . $pureEvent[1] . $optionText . "</li>");
                                    
                                    // increase the current cost
                                    $totalCost += $pureEvent[3];
                                }
								*/
								// go through each of the events that the participant is registered for
								foreach($participant->events as $event) {
									// start the accordion
									echo("<div class=\"accordion-group\">");
									
									// see if they paid for the option as well
									$selectedOption = false;
									
									// loop through each cost to see if there is an optional cost
									$foundOptionalCost = false;
									$totalEventCost = 0;
									foreach($event->costs as $cost) {
										$totalEventCost += $cost->amount;
										if($cost->isOptional) {
											// we found an optional cost; see if they have
											$foundOptionalCost = true;
										}
									}
									
									// now see if they paid for as much as the optional cost is
									if($foundOptionalCost) {
										$totalPaid = 0;
										foreach($participant->payments as $payment) {
											if($payment->event->id == $event->id) {
												$totalPaid += $payment->finalCost;
											}
										}
										
										if($totalPaid == $totalEventCost) {
											$selectedOption = true;
										}
									}
									
									// make a displayable event name
									$displayName = toPrettyPrint($event->eventName);
									if($selectedOption) {
										$displayName .= " (with option)";
									}
									
									// make an organizer link
									$organizer = "<p><strong>Organized by:</strong> ";
									if($event->website) {
										$organizer .= "<a href=\"" . $event->website . "\" target=\"_blank\">" . toPrettyPrint($event->hostedBy) . "</a></p>";
									} else {
										$organizer .= toPrettyPrint($event->hostedBy) . "</p>";
									}
									
									// make contact us link if there is one
									$contactUsLink = "";
									if($event->email) {
										$contactUsLink = "<p><strong>Contact:</strong> <a href=\"mailto:" . $event->email . "\">Email us</a></p>";
									}
									
									// make the location if there is one
									$location = "";
									if($event->location) {
										$location = "<p><strong>Location:</strong> " . $event->location . "</p>";
									}
									
									// print the event name at the top of the accordion
									echo("<div class=\"accordion-heading btn btn-block\">
										      <a href=\"#collapse" . $event->id . "\" data-parent=\"#eventAccordion\" data-toggle=\"collapse\" class=\"accordion-toggle text-left\">
											      " . $displayName . "
										      </a>
										  </div>");
									
									// print the content
									echo("<div class=\"accordion-body collapse\" id=\"collapse" . $event->id . "\">
                                    	      <div class=\"accordion-inner\">
											      " . $organizer . $contactUsLink . $location . "
											      <p>" . toPrettyPrint($event->description) . "</p>
											  </div>
										  </div>");
									
									// close the accordion
									echo("</div>");
								}
                                ?>
                                <!-- sample accordion 
								<div class="accordion-group">
                                	<div class="accordion-heading btn btn-block">
                                    	<a href="#collapseOne" data-parent="#eventAccordion" data-toggle="collapse" class="accordion-toggle">
                                        	Collapsible Group Item #1
                                        </a>
                                    </div>
                                    <div class="accordion-body collapse in" id="collapseOne">
                                    	<div class="accordion-inner">
											Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                                        </div>
                                    </div>
                                </div>
                                 end sample accordion -->
                            </div>
                            <br />
                            <a href="changeRegistration.php?passkey=<?= $_GET['passkey'] ?>" class="btn btn-large btn-danger">Change Your Registration</a>
                        </div>         
                    </div>  

	            	<div class="block span6">
                    	<p class="block-heading">Payments</p>
	              		<div class="block-body">
                        	<?
							// loop through each registered event
							$printedSomething = false;
							foreach($participant->events as $registeredEvent) {
								// see if this event has a cost
								if(count($registeredEvent->costs) > 0) {
									// there is a cost, so we can keep processing this
									// loop through each of the participant's payments until we find one that matches this event
									$eventPayment = null;
									$totalCost = 0;
									foreach($participant->payments as $payment) {
										if($payment->event->id == $registeredEvent->id) {
											$totalCost += $payment->finalCost;
											// this payment is a payment for the current event
											if(!$payment->isAdminPayment) {
												$eventPayment = $payment;
											}
										}
									}
									
									// check that we have payment information
									if($eventPayment != null) {
										$paymentMethod = $eventPayment->method;
										
										// figure out the transaction status
										if($eventPayment->hasPaid) {
											// they've paid!
											if($paymentMethod == "paypal") {
												$paymentStatus = "<strong>Paid in full</strong><br />Payment completed successfully via PayPal.";
											} else {
												$paymentStatus = "<strong>Paid in full</strong><br />Payment completed in person.";
											}
										} else {
											// a payment has not been marked, so let's figure out what's up
											if($paymentMethod == "paypal") {
												if($eventPayment->status == null) {
													// they marked to pay via paypal, but no payment has been attempted yet
													$paymentStatus = "<strong class=\"label label-danger\">UNPAID</strong><br />You have selected to pay via PayPal but have not yet attempted to make a payment.  Please proceed to <a href=\"retryPayment.php?passkey=" . $_GET['passkey'] . "&eid=" . $eventPayment->event->id . "\">this page</a> to retry payment or to change payment method.";
												} else {
													// they marked to pay via paypal, but a payment has a status associated to it
													$paymentStatus = "<strong class=\"label label-danger\">UNPAID</strong><br />Payment via PayPal incomplete (" . $eventPayment->status . ").<br />Please <a href=\"/contact.php\">contact us</a> to help you resolve this payment.";
												}
											} else {
												if($registeredEvent->category == org\fos\Event::FACULTY_FROSH || $registeredEvent->category == org\fos\Event::NON_FACULTY_FROSH) {
													$paymentStatus = "<strong>Payment in person is incomplete.</strong><br />You will be able to pay once you arrive on campus at the tents located at the 'Y-Instersection' at the very middle of campus.";
												} else {
													$paymentStatus = "<strong>Payment in person is incomplete.</strong><br />You will be able to pay once you arrive at the event.";
												}
												if($registeredEvent->acceptedPayments == "paypal" || $registeredEvent->acceptedPayments == "paypal,inperson") {
													$paymentStatus .= "  If you would instead like to pay online via PayPal, please go to <a href=\"retryPayment.php?passkey=" . $_GET['passkey'] . "&eid=" . $eventPayment->event->id . "\">this page</a>.";
												}
											}
										}
									} else {
										// there was no payment, meaning they never even chose a payment method
										$paymentStatus = "<strong class=\"red\">Payment not yet attempted.</strong><br />You must select a method of payment for this event by going to <a href=\"selectPayment.php?passkey=" . $_GET['passkey'] . "\">this page</a>.";
									}
									
									// print the payment information
									echo("<h4>" . $registeredEvent->eventName . " ($" . $totalCost . ")</h4>");
									echo("<p>" . $paymentStatus . "</p>");
									$printedSomething = true;
								}
							}
							
							if(!$printedSomething) {
								echo("<p>You are not registered for any events that require payment.</p>");
							}
							?>
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
</body>
</html>

