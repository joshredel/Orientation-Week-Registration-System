<?
require_once('../../functions.php');

// check for a session
checkForSession();

if($currentEvent != null) {
	// get all participants
	$participants = $currentEvent->participants;
	
	// get all payments
	$payments = $currentEvent->payments;
	
	// get the registration stats
	// first, get all of the paid participants and tally
	// values we want
	$totalTheoretical = 0;
	$totalTheoreticalByPaypal = 0;
	$totalTheoreticalInPerson = 0;
	$totalGrossed = 0;
	$totalCountByPaypal = 0;
	$totalAmountByPaypal = 0;
	$totalCountInPerson = 0;
	$totalAdminCountInPerson = 0;
	$totalAmountInPerson = 0;
	$totalAdminAmountInPerson = 0;
	$numberPaid = 0;
	
	// loop through every registered froshie and take statistics
	if($payments != null) {
		foreach($payments as $payment) {
			if(!$payment->isAdminPayment) {
				// this payment is a payment for the current event
				$amount = $payment->finalCost;
				$paymentMethod = $payment->method;
				
				// add the amounts we should theoretically gross
				$totalTheoretical += $amount;
				
				if($paymentMethod == "paypal") {
					$totalTheoreticalByPaypal += $amount;
				} else {
					$totalTheoreticalInPerson += $amount;
				}
				
				// see if this payment has been paid
				if($payment->hasPaid) {
					// it has been paid, so count it
					$numberPaid++;
					
					// add the amounts that have actually been paid
					$totalGrossed += $amount;
					
					if($paymentMethod == "paypal") {
						$totalCountByPaypal++;
						$totalAmountByPaypal += $amount;
					} else {
						$totalCountInPerson++;
						$totalAmountInPerson += $amount;
					}
				}
			} else {
				// this payment is a payment for the current event
				$amount = $payment->finalCost;
				$paymentMethod = $payment->method;
				
				// see if this payment has been paid
				if($payment->hasPaid) {// add the amounts that have actually been paid
					$totalGrossed += $amount;
					
					if($paymentMethod == "inperson") {
						$totalAdminCountInPerson++;
						$totalAdminAmountInPerson += $amount;
					}
				}
			}
		}
	}
	
	// get participants that have no payments at all
	$totalErrors = 0;
	foreach($participants as $participant) {
		if($participant->payments == null || count($participant->payments) == 0) {
			$totalErrors++;
		}
	}
	
	// total number of people registered
	$numberRegistered = count($participants);
	if($currentEvent->participantCap > 0) {
		$percentageComplete = number_format(($numberRegistered / $currentEvent->participantCap) * 100, 1);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../../css/layout.css" />
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
    <script type="text/javascript">
		// case insensitive version of 'contains'
		$(document).ready(function() {
			jQuery.expr[':'].Contains = function(a, i, m) { 
				return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; 
			};
		});
		
		function filterEvents() {
			// get the search term
			var searchTerm = $('#filter').val();
			
			// hide everything
			$("p.eventInfo").hide();
			
			// only show each link if it contains the search item
			$("p.eventInfo:Contains('" + searchTerm + "')").show();
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
		include("../includes/html/reportsNav.php");
		?>
    	<section id='content'>
        	<?
			// check to see if this user has an event associated to them
			if($currentEvent == null) {
				?>
				<article>
                	<h2>Registration Information</h2>
                	<p>There are currently <?= count($participantService->getParticipants()) ?> people registered.</p>
                </article>
                
                <article>
                	<h2>"How's My Froshing" Recordings</h2>
                    <ul>
						<?
                        // get all recordings
                        $recordingService = new services\FeedbackRecordingService();
						$recordings = $recordingService->getFeedbackRecordings();
						
                        foreach($recordings as $recording) {
                            echo("<li><a target=\"_blank\" href=\"" . $recording->url . "\">" . formatDateTime($recording->messageDate) . "</a></li>");
                        }
                        ?>
                    </ul>
                </article>
                <?
			} elseif(!$currentRole->hasPermission(org\fos\Role::$VIEW_FINANCIAL_OVERVIEW)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
    	    <article id='froshies'>
    	        <header><h2>Registrations</h2></header>
    	        <p><strong><?= $numberRegistered ?></strong> people have registered 
                <?
				if($currentEvent->participantCap > 0) {
					echo("on a cap of " . $currentEvent->participantCap . " (" . $percentageComplete . "%) .");
				} else {
					echo(" (no event cap).");
				}
                ?>
                <br />
                </p>
                <?
                if(count($currentEvent->costs)) {
					?>
                    <header><h2>Completed Payments</h2></header>
                    <p>
                    <strong><?= $numberPaid ?></strong> people have paid, currently grossing <strong><?= number_format($totalGrossed, 2) ?>$</strong><br />
                    Out of these payments, <strong><?= $totalCountByPaypal ?></strong> people have paid <strong><?= number_format($totalAmountByPaypal, 2) ?>$</strong> via Pay-Pal and 
                    <strong><?= $totalCountInPerson ?></strong> people have paid <strong><?= number_format($totalAmountInPerson, 2) ?>$</strong> your event fee in person (this means should have collected <?= number_format(($totalAmountInPerson + $totalAdminAmountInPerson), 2) ?>$ total in cash payments; <?= number_format($totalAmountInPerson, 2) ?>$ is for your event and <?= number_format($totalAdminAmountInPerson, 2) ?>$ is for the admin cost).
                    </p>
                    <br />
                    
                    <header><h2>Theoretical Totals</h2></header>
                    <p>
                    In the end, you should gross <strong><?= number_format($totalTheoretical, 2) ?>$</strong><br />
                    <strong><?= number_format($totalTheoreticalByPaypal, 2) ?>$</strong> via Pay-Pal and <strong><?= number_format($totalTheoreticalInPerson, 2) ?>$</strong> in-person/cash.
                    </p>
                    
                    <header><h2>No Payment Selected Issue</h2></header>
                    <p>
                    So far, <?= $totalErrors ?> people have not selected a method of payment.  These people are not included in the counts above, so please find them in the participants list and encourage them to select a method of payment so your totals are accurate.
                    </p>
					<?
				}
				if($currentEvent->hasSelectableEvents) {
					?>
                    <header><h2>Attendance Per Session</h2></header>
                    <p>Please note that these numbers will not necessarily reflect the total number of people registered.  Students are able to register and then at a later point select which session they will attend.</p>
                    <ul>
                    <?
					foreach($currentEvent->calendarEvents as $calendarEvent) {
						echo("<li><strong>" . formatSelectOptionDate($calendarEvent->startTime, $calendarEvent->endTime) . "</strong><br />Attending: " . count($calendarEvent->personalEvents) . "</li>");
					}
					?>
                    </ul>
                    <?
				}
				?>
    	    </article>
            <? if($currentUser->username == "josh.redel") { ?>
            <article>
            	<header><h2>myWeek Communicator</h2></header>
                
                <form method="post" action="/twilio/web/vpinternal/conferenceCall.php">
                	Description:
                	<input type="text" maxlength="160" name="desc" id="desc">
                    <button type="submit">Make a Conference Call</button>
                </form>
            </article>
            <? } ?>
            <!--
    	    <article id='staff'>
    	        <header><h1>Staff</h1></header>
    	        <p> Total Registered: NUMBER_STAFF<br />
    	        NUMBER_PAID_STAFF have paid, currently grossing PAYMENTS_CURRENT_STAFF<br />
    	        Out of these payments, NUMBER_PAID_PAYPAL_STAFF have paid PAYMENTS_PAYPAL_STAFF via Pay-Pal and NUMBER_CASH_STAFF have paid PAYMANTS_CASH_STAFF in person.</p>
    	        <p>In the end, you should gross PAYMENTS_PROJECTED_STAFF<br />
    	        PAYMENTS_PAYPAL_PROJECTED_STAFF via Pay-Pal and PAYMENTS_CASH_PROJECTED_STAFF in-person/cash.</p>
    	    </article>
    	    <article id='totals'>
    	        <header><h1>Totals</h1></header>
    	        <p> Total Registered: NUMBER_TOTAL<br />
    	        NUMBER_PAID_TOTAL have paid, currently grossing PAYMENTS_CURRENT_TOTAL<br />
    	        Out of these payments, NUMBER_PAID_PAYPAL_TOTAL Pay-Pal and NUMBER_CASH_TOTAL have paid PAYMANTS_CASH_TOTAL in person.</p>
    	        <p>In the end, you should gross PAYMENTS_PROJECTED_TOTAL<br />
    	        PAYMENTS_PAYPAL_PROJECTED_TOTAL via Pay-Pal and PAYMENTS_CASH_PROJECTED_TOTAL in-person/cash.</p>
    	    </article>
            -->
            <?
			} // end check for the user's current event
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>