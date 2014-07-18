<?
require_once('../../functions.php');

// check for a session
checkForSession();

// set that this was the last page
$_SESSION['lastParticipantLocation'] = "full.php";
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
		include("../includes/html/partNav.php");
		?>
    	<section id='content'>
        	<?
			// check to see if this user has an event associated to them
			if(!$currentRole->hasPermission(org\fos\Role::$VIEW_PARTICIPANTS)) {
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
                    <!--<th>E-Mail</th>-->
                    <th>Student ID</th>
                    <? if($currentEvent != null && $currentEvent->costs && count($currentEvent->costs) > 0) { ?>
                    <th>Rate</th>
                    <th>Method</th>
                    <th>Status</th>
                    <!--<th>Paid</th>
                    <th>Merch</th>-->
                    <? } ?>
                </tr>
                <?
				// get the participants based on the current user
				if($currentEvent == null) {
					// show all participants
					$allParticipants = $participantService->getParticipants();
				} else {
					// get the participants for the current event
					$allParticipants = $currentEvent->participants;
				}
				
				
				foreach($allParticipants as $participant) {
					$prettyName = toPrettyPrint(getDisplayName($participant) . " " . $participant->lastName);
					
					echo("<tr class='event'><td><a href='profile.php?id={$participant->id}'>{$prettyName}</a></td>");
					//echo("<td><a href='mailto:{$participant->email}'>{$participant->email}</a></td>");
					echo("<td>{$participant->studentId}</td>");
					
					// loop through each of the participants payments until we find one that matches this event
					if($currentEvent != null) {
						$eventPayment = null;
						foreach($participant->payments as $payment) {
							if($payment->event->id == $currentEvent->id && !$payment->isAdminPayment) {
								// this payment is a payment for the current event
								$eventPayment = $payment;
							}
						}
					
						// check that we have payment information
						if($eventPayment != null) {
							$rate = "$" . $eventPayment->finalCost;
							$method = $eventPayment->method;
						} else {
							$rate = "--";
							$method = "<a onClick='alert(\"This person has not clicked on either of the payment options yet.  They should be encouraged to select to either pay in person or via PayPal via their secure link sent to them by email.\")'>N/A (?)</a>";
						}
						
						// create the printable payment info
						if(count($currentEvent->costs) == 0) {
							$paymentInfo = "";
						} else {
							// figure out the transaction status
							if($eventPayment != null) {
								if($eventPayment->hasPaid) 	{
									$paymentStatus = "paid";
									
									// but see if there is a pending status somewhere in the status
									if(stripos($eventPayment->status, "pending") !== false){
										$paymentStatus = "<a onClick='alert(\"It is marked that this payment was completed but that the transaction was marked pending.  Please check your PayPal account to make sure you do not have any payments that you need to manually accept\")'>paid (?)</a>";
									}
								} else {
									if($method == "paypal") {
										if($eventPayment->status == null) {
											$paymentStatus = "unpaid";
										} else {
											$paymentStatus = "unpaid";
										}
									} else {
										$paymentStatus = "unpaid";
									}
								}
							} else {
								$paymentStatus = "unpaid";
							}
							
							if($paymentStatus == "unpaid") {
								echo("<td>$rate</td><td>$method</td><td style=\"color: red\">$paymentStatus</td>");
							} else {
								echo("<td>$rate</td><td>$method</td><td>$paymentStatus</td>");
							}
						}
					}
						
					echo("</tr>");
				}
				?>
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