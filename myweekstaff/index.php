<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$eventService = new services\EventService();
$participantService = new services\ParticipantService();
$staffService = new services\StaffService();
$staff = $staffService->getStaffByRegistrationPassword($_GET['passkey']);

//redirect if the passkey doesn't match any participant
if($staff == null) {
	redirect("/");
}

// store the event info
$staff->event->load();
$currentEvent = $staff->event;

// see if the delete id is set
if(isset($_POST['deleteId'])) {
	// remove any group information from the marked participant
	$participant = $participantService->getParticipant($_POST['deleteId']);
	
	// make sure it's a real participant
	if($participant != null) {
		// remove group info
		$participant->groupNumber = null;
		$participantService->saveParticipant($participant);
	}
}

// see if the add id is set
if(isset($_POST['addId'])) {
	// remove any group information from the marked participant
	$participant = $participantService->getParticipant($_POST['addId']);
	
	// make sure it's a real participant
	if($participant != null) {
		// remove group info
		$participant->groupNumber = $currentEvent->id . "::" . $staff->groupNumber;
		$participantService->saveParticipant($participant);
	}
}

// see if they made a search
$currentMode = "waiting";
if(isset($_POST['searchInfo'])) {
	// store the student ID
	$searchInfo = $_POST['searchInfo'];
	
	// find the participant with that ID
	$participants = $participantService->getParticipantBySearch($searchInfo);
	
	// filter based on event
	$filteredParticipants = array();
	foreach($participants as $participant) {
		if(inDoctrineArray($currentEvent, $participant->events)) {
			$filteredParticipants[] = $participant;
		}
	}
	
	// set the mode
	$currentMode = "searched";
}

function getDisplayName($participant) {
	// format name
	if(strlen($participant->preferredName)) {
		$nameForDisplay = $participant->preferredName . " ";
	} else {
		$nameForDisplay = $participant->firstName . " ";
	}
	$nameForDisplay .= $participant->lastName;
	if(strlen($participant->preferredPronoun)) {
		$nameForDisplay .= " (" . strtolower($participant->preferredPronoun) . ")";
	}
	$nameForDisplay = toPrettyPrint($nameForDisplay);
	return $nameForDisplay;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>McGill Orientation Week 2013 | myWeek for Leaders and O-Staff</title>
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
	        <img src="assets/images/myweek.jpg" alt="myweekstaff" class="thumbnail-avatar">
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
            
	        <h1 class="page-title">myWeek Dashboard for Leaders &amp; O-Staff</h1>
	    </div>
        <!-- end notification bar -->
	    
        <!-- main content -->
	    <div class="wrapper-content">
	    	<div class="container-fluid">
            	<div class="row-fluid">
	            	<div class="block span12">
	              		<div class="block-body">
                        	<h3>Welcome, <?= $staff->displayName ?></h3>
                            <p>This is myWeek for Leaders &amp; O-Staff for <strong><?= $currentEvent->eventName ?></strong>.  Use this page to manage and keep in touch with members of your group.</p>
                            <? if($staff->classification == "Leader") { ?>
                            <p>You are a leader for <strong>Group #<?= $staff->groupNumber ?></strong></p>
                            <? }?>
                        </div>
                    </div>
                </div>
                
				<div class="row-fluid">
                	<? if($staff->classification == "Leader") { ?>
	            	<div class="block span6">
                    	<p class="block-heading">Group Members</p>
	              		<div class="block-body">
                            <h3>You are co-leading with:</h3>
                            <ul>
                            	<?
								$coStaffs = $staffService->getCoStaffs($currentEvent->id, $staff->groupNumber, $staff->id);
								foreach($coStaffs as $coStaff) {
									echo("<li>" . $coStaff->displayName . " " . $coStaff->lastName . "</li>");
								}
								?>
                            </ul>
                            
                            <h3>Students in your group:</h3>
                            <ul>
                            	<?
								$participants = $participantService->getParticipantsInGroup($currentEvent->id, $staff->groupNumber);
								foreach($participants as $participant) {
									if($participant->phoneNumber != null && $participant->phoneNumber != "") {
										$phoneNumber = str_ireplace("+", "", $participant->phoneNumber);
										$phoneNumberText = " (<a href=\"tel:$phoneNumber\">$phoneNumber</a>)";
									} else {
										$phoneNumberText = "";
									}
									echo("<li>" . getDisplayName($participant) . "$phoneNumberText&nbsp;&nbsp;&nbsp;&nbsp;<button class=\"btn\" onClick=\"deleteFromGroup(" . $participant->id . ")\">Remove</button></li>");
								}
								?>
                            </ul>
                            
                            <form class="hidden" method="post" id="deleteForm" action="index.php?passkey=<?= $_GET['passkey'] ?>">
                            	<input type="hidden" name="passkey" id="passkey" value="<?= $_GET['passkey'] ?>">
                                <input type="hidden" name="deleteId" id="deleteId">
                            </form>
                            
                            <h3>Add students to your group:</h3>
                            <?
                            if($currentMode == "waiting") {
								?>
                                <p>
                                    Please enter a piece of information about the person you wish to add to your group.
                                    This can be one of: first name, preferred name, last name, student ID, or email.
                                </p>
                                <form method="post" name="idform">
                                    <label for="searchInfo"><strong>Search Terms:</strong></label>
                                    <input type="text" name="searchInfo" style="width:80%" autocorrect="off" autocapitalize="off" />
                                    <input class="btn" type="submit" value="Search" />
                                </form>
                            <?
                            } else {
								?>
                                <ul>
                                <?
								foreach($filteredParticipants as $participant) {
									// see if the user is already in a group
									$warnForPoaching = "false";
									if($participant->groupNumber != null || $participant->groupNumber != "") {
										$warnForPoaching = "true";
									}
									echo("<li>" . getDisplayName($participant) . "&nbsp;&nbsp;&nbsp;&nbsp;<button class=\"btn\" onClick=\"addToGroup(" . $participant->id . ", $warnForPoaching)\">Add</button></li>");
								}
								if(count($filteredParticipants) == 0) {
									echo("<p>No one found... try again.</p>");
								}
								?>
                                </ul>
                                <button class="btn" onClick="window.location = 'index.php?passkey=<?= $_GET['passkey']?>'">New Search</button>
                                <form class="hidden" method="post" id="addForm" action="index.php?passkey=<?= $_GET['passkey'] ?>">
                                    <input type="hidden" name="passkey" id="passkey" value="<?= $_GET['passkey'] ?>">
                                    <input type="hidden" name="addId" id="addId">
                                </form>
                                <?
                            }
							?>
                        </div>         
                    </div>  
                    <? } ?>

	            	<div class="block span6">
                    	<p class="block-heading">
                        	<span id="calendarLoading" class="block-icon pull-right">
                            	<a href="#" rel="tooltip" title="We are fetching your calendar..."><i class="icon-spinner"></i> Loading... &nbsp;</a>
                            </span>
                            <span class="block-icon pull-right">
                            	<a href="webcal://orientation.ssmu.mcgill.ca/myweekstaff/vcal.php?passkey=<?= $_GET['passkey'] ?>" rel="tooltip" title="Sync this calendar with your device"><i class="icon-download-alt"></i>Sync</a>
                            </span>
                            
                            <span>Frosh Calendar</span>
                        </p>
	              		<div class="block-body">
                        	<p>
                            	<button class="btn" onClick="$('#calendar').fullCalendar('prev');"><i class="icon-chevron-left"></i><i class="icon-chevron-left"></i></button>
                                &nbsp;&nbsp;
                                <span class="btn-group">
                                    <button class="btn" onClick="$('#calendar').fullCalendar('changeView', 'agendaWeek');">Week</button>
                                    <button class="btn" onClick="$('#calendar').fullCalendar('changeView', 'agendaDay');">Day</button>
                                </span>
                                &nbsp;&nbsp;
                            	<button class="btn" onClick="$('#calendar').fullCalendar('next');"><i class="icon-chevron-right"></i><i class="icon-chevron-right"></i></button>
                            </p>
                            <p>Click on the events in your calendar to see more information, including location.</p>
                            <p>
                            	Key:
                                <span class="calendar-key frosh calendarInfoPopover" data-title="Frosh Events" data-content="Anyone is able to attend these events.">All Ages</span>
                                <span class="calendar-key drop-by calendarInfoPopover" data-title="Drop By Events" data-content="Your students must be 18+ to come to these events; make sure they know where underage events are if they are under 18.">18+ ONLY</span>
                                <span class="calendar-key standard calendarInfoPopover" data-title="Don't Miss Events" data-content="These events are for underage students as an alternative to the 18+ events.">Underage</span>
                                
                            </p>
                            <div id='calendar'></div>
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
    <script>
		$(document).ready(function() {
			var date = new Date();
       		var d = date.getDate();
        	var m = date.getMonth();
        	var y = date.getFullYear();
			var currentTimezone = date.getTimezoneOffset() / 60;
			
			//tooltip
			$("[rel=tooltip]").tooltip();
			
			// build the full calendar
        	$('#calendar').fullCalendar({
          		header: {
					left: '',
					center: '',
					right: ''
          		},
          		editable: true,
				defaultView: 'agendaDay',
				allDaySlot: false,
				firstDay: 1,
				snapMinutes: 5,
				firstHour: 8,
				timeFormat: '', 
				axisFormat: 'H:mm',
          		eventSources: [{
					url: '/myweekstaff/eventsToJson.php',
					type: 'POST',
					data: {
						passkey: '<?= $_GET['passkey'] ?>',
						timezone: currentTimezone
					},
					beforeSend: function() {
						$("#calendarLoading").html("<a id=\"loadingInfo\" href=\"#\" rel=\"tooltip\" title=\"We are fetching your calendar...\"><i class=\"icon-spinner\"></i> Loading... &nbsp;</a>");
						$("#loadingInfo").tooltip();
					}, 
					error: function(jqXHR, textStatus, errorThrown) {
						alert("Oops!  We weren't able to load your calendar.  Please refresh the page.  [" + textStatus + "]");
					},
					success: function() {
						$("#calendarLoading").html("<a id=\"loadingInfo\" href=\"#\" rel=\"tooltip\" title=\"Calendar loaded successfully.\"><i class=\"icon-ok\"></i></a>");
						$("#loadingInfo").tooltip();
					}
				}],
				eventRender: function(event, element) {
					// format start and end times
					var startTime = event.start.toString("H:mm");
					var endTime = event.end.toString("H:mm");
					
					// make some information for the content of the popover
					var popoverContent = "<p>" + startTime + " to " + endTime;
					// see if it is part of a larger event
					if(event.masterEventTitle != null) {
						popoverContent += "<br />(part of " + event.masterEventTitle + ")";
					}
					if(event.location != null) {
						popoverContent += "<br /><strong>Location:</strong><br />" + event.location;
					}
					if(event.notes != null) {
						popoverContent += "<br /><strong>Notes:</strong><br />" + event.notes;
					}
					popoverContent += "</p>";
					
					// render the element
					$(element).css("overflow", "hidden");
					$(element).css("cursor", "pointer");
					$(element).addClass("calendarInfoPopover");
					$(element).popover({
						html: true,          
						title: event.title,
						trigger: "click",
						content: popoverContent,
						placement: "right",
						container: "body",
						placement: function(tip, element) {
							var $element, above, actualHeight, actualWidth, below, boundBottom, boundLeft, boundRight, boundTop, elementAbove, elementBelow, elementLeft, elementRight, isWithinBounds, left, pos, right;
							isWithinBounds = function(elementPosition) {
								return boundTop < elementPosition.top && boundLeft < elementPosition.left && boundRight > (elementPosition.left + actualWidth) && boundBottom > (elementPosition.top + actualHeight);
							};
							$element = $(element);
							pos = $.extend({}, $element.offset(), {
								 width: element.offsetWidth,
								 height: element.offsetHeight
							});
							actualWidth = 283;
							actualHeight = 117;
							boundTop = $(document).scrollTop();
							boundLeft = $(document).scrollLeft();
							boundRight = boundLeft + $(window).width();
							boundBottom = boundTop + $(window).height();
							elementAbove = {
								top: pos.top - actualHeight,
								left: pos.left + pos.width / 2 - actualWidth / 2
							};
							elementBelow = {
								top: pos.top + pos.height,
								left: pos.left + pos.width / 2 - actualWidth / 2
							};
							elementLeft = {
								top: pos.top + pos.height / 2 - actualHeight / 2,
								 left: pos.left - actualWidth
							};
							elementRight = {
								top: pos.top + pos.height / 2 - actualHeight / 2,
								left: pos.left + pos.width
							};
							above = isWithinBounds(elementAbove);
							below = isWithinBounds(elementBelow);
							left = isWithinBounds(elementLeft);
							right = isWithinBounds(elementRight);
							if (above) {
								return "top";
							} else {
								if (below) {
									return "bottom";
								} else {
									if (left) {
										return "left";
									} else {
										if (right) {
											return "right";
										} else {
											return "right";
										}
									}
								}
							}
						}
					});
					$(element).on('click', function (e) {
						$(".calendarInfoPopover").not(this).popover('hide');
						e.stopImmediatePropagation();
					});
				}
        	});
			//$('#calendar').fullCalendar('gotoDate', 2013, 7, 26);
			$('#calendar').fullCalendar('gotoDate', y, m, d);
      	});
		
		$(document).on('click', function (e) {
			$(".calendarInfoPopover").popover('hide');
		});
		
		// set up the calendar keys
		$(".calendar-key").popover({html: true, 
									trigger: "click",
									placement: "bottom",
									container: "body"});
		$(".calendar-key").on('click', function (e) {
			$(".calendarInfoPopover").not(this).popover('hide');
			e.stopImmediatePropagation();
		});
		
		function deleteFromGroup(deleteId) {
			if(confirm("Are you sure you wish to remove this person from your group?")) {
				// set the participant Id to delete
				$("#deleteId").val(deleteId);
				
				// submit the form
				$("#deleteForm").submit();
			}
		}
		
		function addToGroup(addId, warnForPoaching) {
			if(warnForPoaching) {
				if(!confirm("This person is already in another group.  Are you sure you want to switch them to your group?  No poaching!")) {
					return;
				}
			}
			// set the participant Id to delete
			$("#addId").val(addId);
			
			// submit the form
			$("#addForm").submit();
		}
    </script>
    <? include("includes/unifiedJS.php") ?>
</body>
</html>

