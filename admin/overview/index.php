<?
require_once('../../functions.php');

// check for a session
checkForSession();

if($currentEvent != null) {
	// get all participants
	$participants = $currentEvent->participants;
	
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
		?>
    	<section id='content'>
        	<article>
            	<header><h2>Navigation</h2></header>
                
                <table class="menu">
                	<!-- Participants -->
                	<tr>
                    	<td class="left-column">
                        	<div class="navButton headMenuButton"><a href="/admin/participants/">Participants</a></div>
                        </td>
                        <td>
                        	<div class='navButton'><a href='/admin/participants/'>Quick Search</a></div>
                            <div class='navButton'><a href='/admin/participants/full.php'>Full View</a></div><br />
   							<div class='navButton'><a href='/admin/participants/quickcheckin.php'>Quick Check In</a></div>
                            <div class='navButton'><a href='/admin/participants/checkin.php'>Full Check In View</a></div><br />
   							<div class='navButton'><a href='/admin/participants/register.php'>Cross Register</a></div>
                        </td>
                    </tr>
                    <!-- Reports -->
                    <tr>
                    	<td class="left-column">
                        	<div class="navButton headMenuButton"><a href="/admin/reports/">Reports</a></div>
                        </td>
                        <td>
                        	<div class='navButton'><a href='/admin/reports/'>General</a></div><br />
    						<div class='navButton'><a href='/admin/reports/payments.php'>Payment Summary</a></div>
                        </td>
                    </tr>
                    <!-- Event Management -->
                    <tr>
                    	<td class="left-column">
                        	<div class="navButton headMenuButton"><a href="/admin/management/">Event</a></div>
                        </td>
                        <td>
                        	<div class='navButton'><a href='/admin/management/'>Event Details</a></div><br />
                            <div class='navButton'><a href='/admin/management/schedule.php'>Calendar Schedule</a></div>
                        </td>
                    </tr>
                </table>
            </article>
        	<?
			// check to see if this user has an event associated to them
			if($currentEvent == null) {
				?>
                <!--
				<article>
                	<header><h2>Registration Information</h2></header>
                	<p>There are currently <? //count($participantService->getParticipants()) ?> people registered.</p>
                </article>
                -->
                
                <article>
                	<header><h2>"How's My Froshing" Recordings</h2></header>
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
                <? 
				}
			} // end check for the user's current event
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>