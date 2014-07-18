<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get all the participants for this event
$participants = $currentEvent->participants;

// the statistics we want
$underageCount = 0;
$ofAgeCount = 0;

// determine the starting date
// see if the event has calendar events and use the starting one as the start date
if(count($currentEvent->calendarEvents) > 0) {
	$eventDate = $currentEvent->calendarEvents[0]->startTime->getTimestamp();
	
	// now loop through them all
	if($participants != null) {
		foreach($participants as $participant) {
			// get the shirt sizes
			if(!isset($shirtCount[$participant->shirtSize])) {
				$shirtCount[$participant->shirtSize] = 1;
			} else {
				$shirtCount[$participant->shirtSize]++;
			}
			
			// calculate their age for the event
			$birthDate = $participant->dateOfBirth->getTimestamp();
			
			$eventMonth = date('n', $eventDate);
			$eventDay = date('j', $eventDate);
			$eventEndDay = date('j', $currentEvent->endDate->getTimestamp());
			$eventYear = date('Y', $eventDate);
			
			$birthMonth = date('n', $birthDate);
			$birthDay = date('j', $birthDate);
			$birthYear = date('Y', $birthDate);
			
			if(($eventMonth >= $birthMonth && $eventDay >= $birthDay) || ($eventMonth > $birthMonth)) {
				$futureAge = $eventYear - $birthYear;
			} else  {
				$futureAge = $eventYear - $birthYear - 1;
			}
			
			// see if they will be underage
			if($futureAge < 18) {
				// underage!
				$underageCount++;
			} else {
				// they will be of age
				$ofAgeCount++;
			}
			
			// get their dietary restrictions
			$restrictions = explode(",", $participant->dietaryRestrictions);
			foreach($restrictions as $restriction) {
				// count each restriction
				if(!isset($dietaryRestrictions[$restriction])) {
					$dietaryRestrictions[$restriction] = 0;
				} else {
					$dietaryRestrictions[$restriction]++;
				}
			}
		}
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
            	<header><h2>Event Registrations</h2></header>
                
                <form onSubmit="return false">
                    <input id="filter" type="text" onKeyUp="filterEvents()" placeholder="Search events..." >
                </form>
                
				<?
                // loop through each event we have and print the registration count
                $events = $eventService->getEvents();
                
                // print them
                foreach($events as $event) {
                    // get the number of people currently registered
					$amountRegistered = count($event->participants);
					
					// store the participant cap
                    $participantCap = $event->participantCap;
					if($participantCap == null || $participantCap == 0) {
						// display capless info
						echo("<p class='eventInfo'><strong>" . toPrettyPrint($event->eventName) . ":</strong><br /> $amountRegistered participants (no cap)</p>");
					} else {
                    	// display event info with cap
						// calculate the percentage capacity filled
						$percentFilled = number_format(($amountRegistered / $participantCap) * 100, 1);
						
						// print the info
						echo("<p class='eventInfo'><strong>" . toPrettyPrint($event->eventName) . ":</strong><br /> $amountRegistered of $participantCap spaces filled ($percentFilled%)</p>");
					}
                }
                ?>
            </article>
            <?
			} elseif(!$currentRole->hasPermission(org\fos\Role::$VIEW_REPORTS)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
            <article id='shirts'>
                <header><h1>T-Shirts</h1></header>
                <div class='left'>
                    <p><b>XS:</b> <?= ($shirtCount['XS'] == "" ? 0 : $shirtCount['XS']) ?><br />
                    <b>S:</b> <?= ($shirtCount['S'] == "" ? 0 : $shirtCount['S']) ?><br />
                    <b>M:</b> <?= ($shirtCount['M'] == "" ? 0 : $shirtCount['M']) ?><br />
                    <b>L:</b> <?= ($shirtCount['L'] == "" ? 0 : $shirtCount['L']) ?><br />
                    <b>XL:</b> <?= ($shirtCount['XL'] == "" ? 0 : $shirtCount['XL']) ?><br />
                    <b>XXL:</b> <?= ($shirtCount['XXL'] == "" ? 0 : $shirtCount['XXL']) ?></p>
                </div>
            </article>
            <article id='foodRestrictions'>
                <header><h1>Food Restrictions</h1></header>
                <div class='left'>
                    <header><h3>Dietary Restrictions</h3></header>
                    <p><b>Vegan:</b> <?= ($dietaryRestrictions['vegan'] == "" ? 0 : $dietaryRestrictions['vegan']) ?><br />
                    <b>Kosher:</b> <?= ($dietaryRestrictions['kosher'] == "" ? 0 : $dietaryRestrictions['kosher']) ?><br />
                    <b>Halal:</b> <?= ($dietaryRestrictions['halal'] == "" ? 0 : $dietaryRestrictions['halal']) ?><br />
                    <b>Vegetarian:</b> <?= ($dietaryRestrictions['vegetarian'] == "" ? 0 : $dietaryRestrictions['vegetarian']) ?><br />
                    <b>Celiac:</b> <?= ($dietaryRestrictions['celiac'] == "" ? 0 : $dietaryRestrictions['celiac']) ?></p>
                </div>
                <div id='alcohol' class='right'>
                    <header><h3>Alcohol Restrictions</h3></header>
                    <p><b>Under Age:</b> <?= $underageCount ?><br />
                    <b>Of Age:</b> <?= $ofAgeCount ?></p>
                </div>
            </article>
            <article id='reports'>
                <header><h1>Complete Reports (Download)</h1></header>
                <p><a href='generateReport.php?type=full'>Full Report - All Fields</a><br />
                <a href='generateReport.php?type=health'>Health Report - Diet/Allergies</a><br />
                <a href='generateReport.php?type=payment'>Payment Report - Payments &amp; Statuses</a><br />
                <a href='generateReport.php?type=checkin'>Checkin Report - Checked In Participants</a></p>
            </article>
            <?
			} // end check for the user's current event
            ?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>