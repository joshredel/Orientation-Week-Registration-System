<?
require_once('../../functions.php');

// check for a session
checkForKioskSession();
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
		
		function filterLinks() {
			// get the search term
			var searchTerm = $('#filter').val();
			
			// hide everything
			$("tr.event").hide();
			
			// only show each link if it contains the search item
			$("tr.event:Contains('" + searchTerm + "')").show();
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
		?>
    	<section id='content'>
        	<?
			// check to see if this user has an event associated to them
			if(!$currentKioskRole->hasPermission(org\fos\Role::$KIOSK_MODE)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
            <form onSubmit="return false">
                <input id="filter" type="text" onKeyUp="filterLinks()" placeholder="Search participants..." >
            </form>
            <table>
                <tr>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Student ID</th>
                    <? if($currentKioskEvent != null && $currentKioskEvent->category != "discoverMcGill" && $currentKioskEvent->category != "callfortender") { ?>
                    <th>Rate</th>
                    <th>Method</th>
                    <th>Status</th>
                    <!--<th>Paid</th>
                    <th>Merch</th>-->
                    <? } ?>
                </tr>
                <?
				// get the participants based on the current user
				if($currentKioskEvent == null) {
					// show all participants
					$allParticipants = $participantService->getParticipants();
				} else {
					// get the participants for the current event
					$allParticipants = $currentKioskEvent->participants;
				}
				
				
				foreach($allParticipants as $participant) {
					$prettyName = toPrettyPrint($participant->firstName) . " " . toPrettyPrint($participant->lastName);
					
					echo("<tr class='event'><td><a href='profile.php?id={$participant->id}'>{$prettyName}</a></td>");
					echo("<td><a href='mailto:{$participant->email}'>{$participant->email}</a></td>");
					echo("<td>{$participant->studentId}</td>");
					
					// loop through each of the participants payments until we find one that matches this event
					if($currentKioskEvent != null) {
						$eventPayment = null;
						foreach($participant->payments as $payment) {
							if($payment->event->id == $currentKioskEvent->id) {
								// this payment is a payment for the current event
								$eventPayment = $payment;
								
								// if the payment has been paid, then break... otherwise allow it to keep searching in case a later payment was actually made
								if($payment->hasPaid) {
									break;
								}
							}
						}
					
						// check that we have payment information
						if($eventPayment != null) {
							$rate = $eventPayment->finalCost . "$";
							$method = $eventPayment->method;
						} else {
							$rate = "--";
							$method = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (?)</a>";
						}
						
						// create the printable payment info
						if($currentKioskEvent->category == "discoverMcGill" || $currentKioskEvent->category == "callfortender") {
							$paymentInfo = "";
						} else {
							if($eventPayment->hasPaid) {
								$paymentInfo = "paid";
							} else {
								if($method == "paypal") {
									$paymentInfo = "pending";
								} elseif ($method == "in Person") {
									$paymentInfo = "not received";
								} else {
									$paymentInfo = "--";
								}
							}
							
							echo("<td>$rate</td><td>$method</td><td>$paymentInfo</td>");
						}
						//echo("<td><input type='checkbox' /></td>");
						//echo("<td><input type='checkbox' /></td></tr>");
					}
						
					echo("</tr>");
				}
				?>
                <!--
                <tr>
                    <td><a href='profile.php?user=1'>Dan Greencorn</a></td>
                    <td><a href='mailto:dan.greencorn@mail.mcgill.ca'>dan.greencorn@mail.mcgill.ca</a></td>
                    <td>260260260</td>
                    <td></td>
                    <td>Pay-Pal</td>
                    <td><input type='checkbox' /></td>
                    <td><input type='checkbox' disabled checked /></td>
                </tr>
                -->
            </table>
            <?
			} // end check for the user's current event
            ?>
    	</section>
    	<div id='footer'>
        
    	</div>
    </div>
</body>
</html>