<?
require_once('../functions.php');
include_once('../eventsupportfunctions.php');

session_start();

// make sure all previous steps have been completed
if(!isset($_SESSION['step1Complete']) || $_SESSION['step1Complete'] != true) {
	redirect("/registration/step1.php");
}
if(!isset($_SESSION['step2Complete']) || $_SESSION['step2Complete'] != true) {
	redirect("/registration/step2.php");
}

// see if we have already done step 4
if(isset($_SESSION['step4Complete']) && $_SESSION['step4Complete'] == true) {
	// it has been; send them to step 5
	redirect("/registration/step5.php");
}

// create an event service and get all events
$eventService = new services\EventService();

// store the selected faculty and living style
$selectedFaculty = $_SESSION['faculty'];
$selectedLivingStyle = $_SESSION['livingStyle'];
$selectedOrigin = $_SESSION['placeOfOrigin'];

// determine if different elements can be displayed based on faculty or origin
$shouldPresentDM = true;
if($selectedFaculty == "LW" || $selectedFaculty == "AG") {
	$shouldPresentDM = false;
}

$shouldPresentRezFest = true;
if($selectedFaculty == "LW" || $selectedFaculty == "AG") {
	$shouldPresentRezFest = false;
}

$shouldPresentInternational = false;
if($selectedOrigin == "International") {
	$shouldPresentInternational = true;
}

$shouldPresentDMAE = true;
if($selectedFaculty == "AG") {
	$shouldPresentDMAE = false;
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 3</title>
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
    <!-- Javascript
    ================================================== -->
    <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.9.1.min.js"><\/script>')</script>-->
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/rotate.js"></script>
    <script src="/js/scrollTo.min.js"></script>
    <script src="/js/formchanges.js"></script>
    
    <script>
		!function ($) {
			$(function(){
				$('#header').carousel()
			})
		}(window.jQuery)
		
		// prepare the floating menu
		/*
		$(function() {
			function moveFloatMenu() {
				var menuOffset = menuYloc.top + $(this).scrollTop() + "px";
				$('#float-menu').animate({
					top: menuOffset
				}, {
					duration: 500,
					queue: false
				});
			}
		
			menuYloc = $('#float-menu').offset();
		
			$(window).scroll(moveFloatMenu);
		
			moveFloatMenu();
		});
		*/
		
		// for checking if the form is dirty
		var saving = false;
		var formIsDirty = false;
		var settingUpEvents = true;
		
		// for handling the event ticket
		var ticketEvents = [];
		var totalCost = 0;
		
		// for handling the completion guide
		var completionCounts = new Array();
		completionCounts['Faculty'] = 0;
		completionCounts['NonFaculty'] = 0;
		completionCounts['Orange'] = 0;
		completionCounts['RezFest'] = 0;
		completionCounts['International'] = 0;
		completionCounts['AcademicExpectations'] = 0;
		completionCounts['ALaCarte'] = 0;
		completionCounts['DropIn'] = 0;
		completionCounts['OrientationCentre'] = 0;
		
		// a "class" for the events we want to store
		function TicketEvent(eventId, eventName, tookOption, cost, canRemove, category) {
			// the ID of the event
			this.eventId = eventId;
			
			// the name of the event
			this.eventName = eventName
			
			// whether the user took the option with it
			this.tookOption = tookOption;
			
			// how much the total ticket was (used only for display)
			this.cost = cost;
			
			// whether the user can remove/un-register from this event (false normally for auto-registered events)
			this.canRemove = canRemove;
			
			// the events category
			this.category = category;
		}
		
		// adds the new ticket-style event to our collection
		function addEventToTicket(eventId, eventName, tookOption, cost, canRemove, category) {
			if(!haveAlreadyRegistered(eventId)) {
				// create the new ticket from the 
				var addedEvent = new TicketEvent(eventId, eventName, tookOption, cost, canRemove, category);
				
				// add it to our ticket array
				ticketEvents.push(addedEvent);
				
				// update the registered events in the form
				updateRegisteredEventsInForm();
				
				// increase the cost accordingly
				totalCost += cost;
				
				// update the cost display
				$('#current-price').html("$" + totalCost);
				
				// build the text to go in the list
				var listingText = "<strong>$" + cost + "</strong>: " + eventName;
				if(tookOption) {
					listingText += " (with option)";
				}
				if(canRemove) {
					listingText += " <a onClick=\"removeEventFromTicket(" + eventId + ")\"><i class=\"icon-trash red\"></i></a>";
				}
				
				// update the event listings display
				//$('#event-ticket li:eq(-1)').before('<li class="features">Inserted</li>');
				$('#event-ticket').append("<li id='ticket-item-" + eventId + "'>" + listingText + "</li>");
				
				// update the completion guide
				updateCompletionGuide(category, 1);
			} else {
				// they've already registered!
				alert("It looks like you've already registered for '" + eventName + "'.  You can only register once!");
			}
		}
		
		// determines if they've already registered for the event they clicked
		function haveAlreadyRegistered(eventId) {
			// loop through each event we've registered for and see if we have it
			var alreadyRegistered = false;
			for(var i = 0; i < ticketEvents.length; i++) {
				var currentEvent = ticketEvents[i];
				
				if(currentEvent.eventId == eventId) {
					// they've registered!
					alreadyRegistered = true;
					break;
				}
			}
			
			return alreadyRegistered;
		}
		
		// remove the desired event from the list of registered events
		function removeEventFromTicket(eventId) {
			// find the event to remove
			var indexToRemove = -1;
			var priceToReduce = 0;
			var removedCategory = "";
			for(var i = 0; i < ticketEvents.length; i++) {
				var currentEvent = ticketEvents[i];
				
				if(currentEvent.eventId == eventId) {
					// this is the event to remove
					indexToRemove = i;
					priceToReduce = currentEvent.cost;
					removedCategory = currentEvent.category;
					break;
				}
			}
			
			if(indexToRemove > -1) {
				// remove the item from the array
				ticketEvents.splice(indexToRemove, 1);
				
				// update the registered events in the form
				updateRegisteredEventsInForm()
				
				// remove the item from display
				$("#ticket-item-" + eventId + "").remove();
				
				// descrease the cost accordingly
				totalCost -= priceToReduce;
				
				// update the cost display
				$('#current-price').html("$" + totalCost);
				
				// update the completion guide
				updateCompletionGuide(removedCategory, -1);
			} else {
				alert("Whoops... there's nothing to remove...");
			}
		}
		
		function updateCompletionGuide(category, count) {
			// update the totals
			completionCounts[category] += count;
			var currentCount = completionCounts[category]; 
			
			// now go through each category and determine the best look
			switch(category) {
				case "Faculty":
				case "NonFaculty":
				case "Orange":
					// remove the header style
					removeAllLabelStyle("#completion-frosh-header");
					
					// branch based on how many events we have
					var totalFrosh = completionCounts["Faculty"] + completionCounts["NonFaculty"];// + completionCounts["Orange"];
					if(totalFrosh == 0) {
						// suggest to do one
						$("#completion-frosh").html("<p class=\"alert alert-warning\">You haven't selected a frosh event to participate in.  We highly recommend doing one, but we also understand if you don't want to!<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#froshes\">a faculty or non-faculty frosh</a>.</p>");
						$("#completion-frosh-header").addClass("btn-warning");
					} else if(totalFrosh == 1) {
						// perfect!
						$("#completion-frosh").html("<p class=\"alert alert-success\">You've signed up for a frosh event, good call!</p>");
						$("#completion-frosh-header").addClass("btn-success");
					} else if(totalFrosh > 1) {
						// they've done too many
						$("#completion-frosh").html("<p class=\"alert alert-danger\">You've signed up for more than one frosh.  We highly discourage this as the events will overlap almost completely.  You will be charged for both and there is no refund for not being able to attend all events of each frosh.  You've been warned!</p>");
						$("#completion-frosh-header").addClass("btn-danger");
					}
					break;
					
				case "RezFest":
					// remove the header style
					removeAllLabelStyle("#completion-rezfest-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-rezfest").html("<p class=\"alert alert-warning\">You haven't selected a Rez &amp; Off Campus Fest to participate in.  We highly recommend doing one!  It is an excellent way to meet people.<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#rezfest\">a Rez/Off-Campus Fest event</a>.</p>");
						$("#completion-rezfest-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-rezfest").html("<p class=\"alert alert-success\">Perfect, you're signed up for a Rez/Off-Campus Fest.</p>");
						$("#completion-rezfest-header").addClass("btn-success");
					}
					break;
				
				case "International":
					// remove the header style
					removeAllLabelStyle("#completion-international-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-international").html("<p class=\"alert alert-warning\">As an international student, we highly recommend going to one of the excellent International Student Services events.<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#international\">an International Student Events reminder</a>.</p>");
						$("#completion-international-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-international").html("<p class=\"alert alert-success\">Excellent, you have signed up for an International Student event.</p>");
						$("#completion-international-header").addClass("btn-success");
					}
					break;
					
				case "AcademicExpectations":
					// remove the header style
					removeAllLabelStyle("#completion-academic-expectations-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-academic-expectations").html("<p class=\"alert alert-warning\">You haven't selected any Academic Expectations events to participate in.  We highly recommend doing at least one!  These events will help prepare you for the academic world you will work in for your duration at McGill.<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#dmacademic\">a DM: Academic Expectation event</a>.</p>");
						$("#completion-academic-expectations-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-academic-expectations").html("<p class=\"alert alert-success\">You've signed up for at least one Academic Expectations event; great!  Why not choose some more...</p>");
						$("#completion-academic-expectations-header").addClass("btn-success");
					}
					break;
					
				case "ALaCarte":
					// remove the header style
					removeAllLabelStyle("#completion-a-la-carte-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-a-la-carte").html("<p class=\"alert alert-warning\">You haven't selected any \"&Agrave; la carte\" events to participate in.  We highly recommend doing at least one!  Various groups across campus host these events just for YOU, so why not do some!<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#alacarte\">an \"&Agrave; la carte\" event</a>.</p>");
						$("#completion-a-la-carte-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-a-la-carte").html("<p class=\"alert alert-success\">You've signed up for at least one \"&Agrave; la carte\" event; awesome!  No harm in registering for a few more...</p>");
						$("#completion-a-la-carte-header").addClass("btn-success");
					}
					break;
					
				case "DropIn":
					// remove the header style
					removeAllLabelStyle("#completion-drop-in-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-drop-in").html("<p class=\"alert alert-warning\">You haven't selected any Drop-Ins to participate in.  You're more than welcome to simply \"drop-in\", as the name implies, but we suggest registering for them here so they appear on your schedule for the week.  Wouldn't want to forget!<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#dropins\">a Drop-In event</a>.</p>");
						$("#completion-drop-in-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-drop-in").html("<p class=\"alert alert-success\">You've signed up for at least one Drop-In event; super!  A few more wouldn't hurt...</p>");
						$("#completion-drop-in-header").addClass("btn-success");
					}
					break;
					
				case "OrientationCentre":
					// remove the header style
					removeAllLabelStyle("#completion-orientation-centre-header");
					
					// branch based on how many events we have
					if(currentCount == 0) {
						// suggest to do one
						$("#completion-orientation-centre").html("<p class=\"alert alert-warning\">You haven't registered for the Orientation Centre.  You're more than welcome to drop by, but we suggest registering for it here so they appear on your schedule for the week.<br />Fix this by registering for <a data-toggle=\"collapse\" data-parent=\"#eventsAccordian\" href=\"#resourcecentre\">Orientation Centre</a>.</p>");
						$("#completion-orientation-centre-header").addClass("btn-warning");
					} else if(currentCount >= 1) {
						// perfect!
						$("#completion-orientation-centre").html("<p class=\"alert alert-success\">You've signed up for the Orientation Centre; fantastic!</p>");
						$("#completion-orientation-centre-header").addClass("btn-success");
					}
					break;
			}
		}
		
		// remove all label info styles
		function removeAllLabelStyle(target) {
			$(target).removeClass("btn-success");
			$(target).removeClass("btn-warning");
			$(target).removeClass("btn-danger");
		}
		
		// update the registered events in the form
		function updateRegisteredEventsInForm() {
			// make a text implosion of the contents of the array
			var registeredEventInfo = "";
			for(var i = 0; i < ticketEvents.length; i++) {
				var currentEvent = ticketEvents[i];
				registeredEventInfo += currentEvent.eventId + ",";
				registeredEventInfo += currentEvent.eventName + ",";
				registeredEventInfo += currentEvent.tookOption + ",";
				registeredEventInfo += currentEvent.cost + ",";
				registeredEventInfo += currentEvent.canRemove + ",";
				registeredEventInfo += currentEvent.category;
				
				if(i < ticketEvents.length - 1) {
					registeredEventInfo += ";";
				}
			}
			
			// save it to the field
			$("#registeredEvents").val(registeredEventInfo);
			
			// mark the form as dirty
			markFormAsDirty();
		}
		
		// register session events
		function registerSessionEvents(rawSessionData) {
			// get the array of each event that has been registered for
			var rawEvents = rawSessionData.split(";");
			
			// iterate through each raw event and have it added
			for(var i = 0; i < rawEvents.length; i++) {
				// break down the raw event
				var pureEvent = rawEvents[i].split(",");
				
				// check to see if we have already registered for this event
				if(!haveAlreadyRegistered(pureEvent[0])) {
					// we haven't, so register for it
					//addEventToTicket(ID, NAME, OPTION, COST, CANREMOVE, CATEGORY);
					addEventToTicket(pureEvent[0], pureEvent[1], textToBoolean(pureEvent[2]), parseInt(pureEvent[3]), textToBoolean(pureEvent[4]), pureEvent[5]);
				}
			}
		}
		
		// helper function that translates text to boolean
		function textToBoolean(text) {
			if(text == "true") {
				return true;
			} else {
				return false;
			}
		}
		
		// marks the form as dirty if an event is added/removed after setup
		function markFormAsDirty() {
			if(!settingUpEvents) {
				formIsDirty = true;
			}
		}
    </script>
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
            	<div class="well span12 sign-up">
                	<ul class="breadcrumb text-center">
                        <li class="active">
                            <a href="step1.php">Welcome</a>
                            <span class="divider">&gt;</span>
                        </li>
                        <li>
                            <?= printBreadcrumbLinkIfComplete("step1", "step2", "Personal Info") ?>
                            <span class="divider">&gt;</span>
                        </li>
                        <li>
                            <?= printBreadcrumbLinkIfComplete("step2", "step3", "Events") ?>
                            <span class="divider">&gt;</span>
                        </li>
                        <li>
                            <?= printBreadcrumbLinkIfComplete("step3", "step4", "Confirmation") ?>
                            <span class="divider">&gt;</span>
                        </li>
                        <li>
                            Payment
                        </li>
                    </ul>
                    
                    <h2 class="text-center">Registration<br />Step 3<br />Choose Your Events!</h2>
                    
                    <p>Choose the events you'd like to register for below.  Use the <span><em>Completion Guide</em></span> to help you make the most of your week!  We highly suggest following its recommendations... try to do at least one event from each category and maybe even more (if applicable).</p>
                    
                    <div class="row-fluid">
                    	<div class="span4">
                        	<div id="float-menu">
                                <div class="boxed">
                                    <h3>Your O-Week Ticket</h3>
                                    <p>You're O-Week ticket is currently costing:</p>
                                    <p id="current-price" class="lead">$0</p>
                                    <p>You're registered for:</p>
                                    <ul id="event-ticket"></ul>
                                    <p>Click the trash can to unregister from the event.</p>
                                    <br />
                                    <form id="eventRegistrationForm" method="POST" action="/actions/registration/processStep3.php" onSubmit="saving = true">
                                    	<input type="hidden" id="registeredEvents" name="registeredEvents">
                                        <input type="hidden" id="customAnswers" name="customAnswers">
                                        <input type="hidden" name="save" value="step3done">
                                    	<button class="btn btn-main-small">On to Step 4 <i class="icon-chevron-right"></i></button>
                                    </form>
                                    
                                    <h3>Completion Guide</h3>
                                    
                                    <? if($shouldPresentDM) { ?>
                                    <!-- Discover McGill -->
                                    <button id="completion-frosh-dm" class="toggler btn guide-btn btn-success" data-toggle="collapse" data-target="#completion-dm">Discover McGill &amp; Engage McGill</button>
                                    <div id="completion-dm" class="collapse">
                                        <p class="alert alert-success">Good to go... we've signed you up for both automatically!</p>
                                    </div>
                                    <? } ?>
                                    
                                    <!-- Froshes -->
                                    <button id="completion-frosh-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-frosh">Froshes</button>
                                    <div id="completion-frosh" class="collapse">
                                        <p class="alert alert-warning">You haven't selected a frosh event to participate in.  We highly recommend doing one!<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#froshes">a faculty or non-faculty frosh</a>.  However, if you really do not want to do one, that is totally fine by us!</p>
                                    </div>
                                    
                                    <!-- RezFest -->
                                    <? if($shouldPresentRezFest) { ?>
                                    <button id="completion-rezfest-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-rezfest">Rez &amp; Off Campus Fests</button>
                                    <div id="completion-rezfest" class="collapse">
                                        <p class="alert alert-warning">You haven't selected a Rez &amp; Off Campus Fest to participate in.  We highly recommend doing one!  It is an excellent way to meet people.<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#rezfest">a Rez/Off-Campus Fest event</a>.</p>
                                    </div>
                                    <? } ?>
                                    
                                    
                                    <!-- International -->
                                    <? if($shouldPresentInternational) { ?>
                                    <button id="completion-international-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-international">International Student Events</button>
                                    <div id="completion-international" class="collapse">
                                        <p class="alert alert-warning">As an international student, we highly recommend going to one of the excellent International Student Services events.<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#international">an International Student Events reminder</a>.</p>
                                    </div>
                                    <? } ?>
                                    
                                    <!-- DM:AE -->
                                    <? if($shouldPresentDMAE) { ?>
                                    <button id="completion-academic-expectations-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-academic-expectations">DM: Academic Expectations</button>
                                    <div id="completion-academic-expectations" class="collapse">
                                        <p class="alert alert-warning">You haven't selected any Academic Expectations events to participate in.  We highly recommend doing at least one!  These events will help prepare you for the academic world you will work in for your duration at McGill.<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#dmacademic">a DM: Academic Expectation event</a>.</p>
                                    </div>
                                    <? } ?>
                                    
                                    <!-- A La Carte -->
                                    <button id="completion-a-la-carte-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-a-la-carte">"&Agrave; la carte"</button>
                                    <div id="completion-a-la-carte" class="collapse">
                                        <p class="alert alert-warning">You haven't selected any "&Agrave; la carte" events to participate in.  We highly recommend doing at least one!  Various groups across campus host these events just for YOU, so why not do some!<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#alacarte">an "&Agrave; la carte" event</a>.</p>
                                    </div>
                                    
                                    <!-- Drop Ins -->
                                    <button id="completion-drop-in-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-drop-in">Drop-Ins</button>
                                    <div id="completion-drop-in" class="collapse">
                                        <p class="alert alert-warning">You haven't selected any Drop-Ins to participate in.  You're more than welcome to simply "drop-in", as the name implies, but we suggest registering for them here so they appear on your schedule for the week.  Wouldn't want to forget!<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#dropins">a Drop-In event</a>.</p>
                                    </div>
                                    <!--<p class="alert alert-success">You've signed up for at least one Drop-In event; super!  A few more wouldn't hurt...</p>
                                    <p class="alert alert-warning">You haven't selected any Drop-Ins to participate in.  You're more than welcome to simply "drop-in", as the name implies, but we suggest registering for them here so they appear on your schedule for the week.  Wouldn't want to forget!</p>-->
                                    
                                    <!-- Orientation Centre -->
                                    <button id="completion-orientation-centre-header" class="toggler btn guide-btn btn-warning" data-toggle="collapse" data-target="#completion-orientation-centre">Orientation Centre</button>
                                    <div id="completion-orientation-centre" class="collapse">
                                        <p class="alert alert-warning">You haven't registered for the Orientation Centre.  You're more than welcome to drop by, but we suggest registering for it here so they appear on your schedule for the week.<br />Fix this by registering for <a data-toggle="collapse" data-parent="#eventsAccordian" href="#resourcecentre">Orientation Centre</a>.</p>
                                    </div>
                                    <!--<p class="alert alert-success">You've signed up for the Orientation Centre; fantastic!</p>
                                    <p class="alert alert-warning">You haven't registered for the Orientation Centre.  You're more than welcome to drop by, but we suggest registering for it here so they appear on your schedule for the week.</p>-->
                                </div>
                            </div><!-- end floating menu -->
                        </div>
                        
            			<div class="span8">
                            <div class="accordion" id="eventsAccordian">
                                <!-- Discover McGill Day -->
                                <? if($shouldPresentDM) { ?>
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#discovermcgill">
                                            Discover McGill &amp; Engage McGill<br /><i class="icon-angle-right"></i> Campus-wide, day-long orientation event for all new McGill students
                                        </a>
                                    </div>
                                    <div id="discovermcgill" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Tuesday, August 27</h4>
                                            <p>Discover McGill is a fun, energetic campus-wide welcome day to "kick-off" Orientation Week! Your attendance is crucial because you’ll meet returning students and new friends, and learn about important faculty-specific academic and advising information, as well as discover all of the many support services that exist just for you. New this year will be an Engage McGill closing event that will invite the larger McGill community to celebrate the new entering class and the start of the new school year.</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::DISCOVER_MCGILL);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <? } ?>
                                <!-- END Discover McGill Day -->
                                
                                
                                <!-- Froshes -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#froshes">
                                            Froshes<br /><i class="icon-angle-right"></i> Faculty and interest-based multi-day social events
                                        </a>
                                    </div>
                                    <div id="froshes" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Thursday, August 29 to Sunday, September 1</h4>
                                            <p>Froshes are awesome, multi-day events run by student organizations and bring new students together in a social environment. They are an excellent way to get oriented to the city and to social life at McGill. You can register for your Faculty Frosh or for any of the Non-Faculty Froshes. Regardless of what you end up doing, no new student's welcome to McGill would be complete (or as memorable!) without doing a Frosh!</p>
                                            <p>We highly recommend doing one frosh, but probably not more.  While it is possible to register for more than one frosh, it is highly discouraged as almost all programming during the events will overlap.  So, choose one that sounds best to you!</p>
                                            <div class="row-fluid">
                                                <div class="span6">
                                                    <h3>Faculty Frosh</h3>
                                                    <?
                                                    printCategoryEventsForRegistration(org\fos\Event::FACULTY_FROSH, $selectedFaculty, null, 1);
                                                    ?>
                                                </div>
                                                <div class="span6">
                                                    <h3>Non-Faculty Frosh</h3>
                                                    <?
                                                    printCategoryEventsForRegistration(org\fos\Event::NON_FACULTY_FROSH, null, null, 1);
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Froshes -->
                                
                                
                                <!-- RezFest-->
                                <? if($shouldPresentRezFest) { ?>
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#rezfest">
                                            Rez &amp; Off Campus Fests<br /><i class="icon-angle-right"></i> Day-long event for students living in residence or off campus
                                        </a>
                                    </div>
                                    <div id="rezfest" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Monday, August 26</h4>
                                            <p>Rez Fest is a celebration of all things Rez: it begins first thing in the morning when everyone gathers in their halls to get ready for the full day ahead. The day culminates in the highly-anticipated annual Rez Warz competition.</p>
                                            <p>OC Fest is for all the off-campus students and is hosted by our Off-Campus Fellows. These students will also get a chance to come together and explore what McGill and Montreal have to offer.</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::REZ_FEST, null, $selectedLivingStyle);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <? } ?>
                                <!-- END RezFest -->
                                
                                
                                <!-- International Events -->
								<? if($shouldPresentInternational) { ?>
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#international">
                                            International Student Events<br /><i class="icon-angle-right"></i> Events for all new international students, including exchange students
                                        </a>
                                    </div>
                                    <div id="international" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Friday, August 23 to Tuesday, September 24</h4>
                                            <p>Information sessions and events hosted by International Student Services, a member of McGill Student Services.</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::INTERNATIONAL);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <? } // end international check ?>
                                
                                
                                <!-- DM Academic Expectations -->
                                <? if($shouldPresentDMAE) { ?>
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#dmacademic">
                                            Discover McGill: Academic Expectations Day<br /><i class="icon-angle-right"></i> Workshops that help prepare new students for academic success
                                        </a>
                                    </div>
                                    <div id="dmacademic" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Wednesday, August 28</h4>
                                            <p>Campus Life &amp; Engagement's Discover McGill: Academic Expectations Day offers a variety of workshops that provide you with plenty of tips and advice to sharpen your study skills. Take advantage of this opportunity to get a head start on your university career!</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::ACADEMIC_EXPECTATIONS);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <? } ?>
                                <!-- END DM Academic Expectations -->
                                
                                
                                <!-- A la carte Events -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#alacarte">
                                            "&Agrave; la carte" Events<br /><i class="icon-angle-right"></i> Different fun events put on by campus groups
                                        </a>
                                    </div>
                                    <div id="alacarte" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>Sunday, August 25 to Sunday, September 1 </h4>
                                            <p>Run by different on-campus services, groups, and clubs, &Agrave; la carte events let you explore some of the different things that might interest or be of service to you during your time at McGill. From specialized tours to city excursions to panels and everything in between, every new student should try to add a few of these to their week's menu!</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::A_LA_CARTE);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- END A la carte Events -->
                                
                                
                                <!-- Drop-Ins -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#dropins">
                                            Drop-Ins<br /><i class="icon-angle-right"></i> Drop in and talk to experts at McGill on a variety of topics
                                        </a>
                                    </div>
                                    <div id="dropins" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <p>Drop in and talk to experts at McGill on a variety of topics, at Campus Life &amp; Engagement's Orientation Resource Room</p>
                                			<?
                                            printCategoryEventsForRegistration(org\fos\Event::DROP_IN);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Drop-Ins -->
                                
                                
                                <!-- Orientation Resource Centre -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#resourcecentre">
                                            Orientation Centre<br /><i class="icon-angle-right"></i> Campus Life &amp; Engagement’s must-see Orientation Centre in the Brown Building
                                        </a>
                                    </div>
                                    <div id="resourcecentre" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <h4>August 22 to September 6</h4>
                                    		<p>A first stop for everyone! You will undoubtedly have a long list of questions when you arrive at McGill, and the best place to get honest answers is at the Orientation Centre (2nd floor, Brown Student Services Building, 3600 McTavish Street).</p>
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::ORIENTATION_CENTRE);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Orientation Resource Centre -->
                                
                                
                                <!-- OOHLALA -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#oohlala">
                                            OOHLALA Discover My Campus<br /><i class="icon-angle-right"></i> Play a campus tour game on the OOHLALA app
                                        </a>
                                    </div>
                                    <div id="oohlala" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::OOHLALA);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- END OOHLALA -->
                                
                                
                                <!-- Orientation PLUS -->
                                <div class="accordion-group master-event-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#orientationplus">
                                            Orientation PLUS<br /><i class="icon-angle-right"></i> Make sure to attend these events happening after Orientation Week
                                        </a>
                                    </div>
                                    <div id="orientationplus" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                            <?
                                            printCategoryEventsForRegistration(org\fos\Event::PLUS_EVENT);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Orientation Plus -->
                            </div><!--End Accordion Group-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    
    <script>
		// setup a scroll-to for all accordian titles
		// - scrolls to the top of the accordian content when it is opened
		$(".master-event-group").on("shown", function (e) {
			var selected = $(this);
			//var collapseh = $(".collapse .in").height();
			var collapseh = $(".navbar-inner").height() + $(".accordion-heading").height() + 10;
			
			$.scrollTo(selected, 350, {offset: -(collapseh)} );
		});
		
		// stop propagation of the shown event of price/description  so that auto scroll doesn't also trigger
		$(".event-accordian-group").on("shown", function (e) {
			e.stopPropagation();
		});
		
		<?
		// see if we have already completed this step so we can restore registered events
		if(isset($_SESSION['step3Complete']) && $_SESSION['step3Complete'] == true) {
			$registeredEvents = $_SESSION['registeredEvents'];
			echo("registerSessionEvents(\"" . $registeredEvents . "\");");
		}
		?>
		
		// get variables for the form update mechanisms
		var form = document.getElementById("eventRegistrationForm");
		
		$(window).on('beforeunload', function() {
			if(!saving) {
				if(formIsDirty) {
					return "You haven't saved changes you have made on this page.  Please use the 'ON TO STEP 4' button to save your changes and continue.";
				}
			}
		});
		
		// when we get here, we are done setting up events
		settingUpEvents = false;
	</script>
</body>
</html>
