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
       		<?
			// check to see if this user has an event associated to them
			if($currentKioskEvent == null) {
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
                    // store the participant cap
                    $participantCap = $event->participantCap;
                    
                    // get the number of people currently registered
                    $amountRegistered = count($event->participants);
                    
                    // calculate the percentage capacity filled
                    $percentFilled = number_format(($amountRegistered / $participantCap) * 100, 1);
                    
                    // print the info
                    echo("<p class='eventInfo'><strong>" . toPrettyPrint($event->eventName) . ":</strong><br /> $amountRegistered of $participantCap spaces filled ($percentFilled%)</p>");
                }
                ?>
            </article>
            <?
			}
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>