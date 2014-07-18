<?
require_once('../functions.php');

// prepare the services we need and the globally used participant
$participantService = new services\ParticipantService();
$participant = $participantService->getParticipantByRegistrationPassword($_GET['passkey']);

//redirect if the passkey doesn't match any participant
if($participant == null) {
	redirect("/");
}

// make a map of personal events with an event id and their corresponding option selection
$selectedOptions = array();
foreach($participant->personalEvents as $personalEvent) {
	// see if this personal event is a placeholder for an event option selection
	if($personalEvent->calendarEvent) {
		// it is a palceholder; store its info in the map
		$personalEvent->calendarEvent->load();
		$selectedOptions[$personalEvent->eventId] = $personalEvent->calendarEvent->id;
	}
}

// see if there are any events that the participant needs to make a selection for
$eventsAwaitingSelection = 0;
$hasEventsWithSelections = false;
// loop through each of the participant's events
foreach($participant->events as $event) {
	// see if the event has selectable events
	if($event->hasSelectableEvents) {
		// mark that we have at least on event that has selections
		$hasEventsWithSelections = true;
		
		// see if they have made an initial decision
		if(!isset($selectedOptions[$event->id])) {
			// they haven't; show a selectable option
			$eventsAwaitingSelection++;
		}
	}
}

// sorts calendar/personal events
function dateCompare($a, $b) { 
	if($a->startTime->getTimestamp() == $b->startTime->getTimestamp()) {
		return 0;
	}
	return ($a->startTime->getTimestamp() < $b->startTime->getTimestamp()) ? -1 : 1;
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
	        <img src="assets/images/myweek.jpg" alt="avatar" class="thumbnail-avatar">
            <!--
	        <a href="#"><div class="sidebar-avatar-message"><div class="notify notify-message"><i class="icon-envelope"></i></div></div></a>
	        <a href="#"><div class="sidebar-avatar-notify"><div class="notify ">7</div></div></a>
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
            
	        <h1 class="page-title">Full Calendar</h1>
	    </div>
        <!-- end notification bar -->
        
        <!-- main content -->
        <div class="wrapper-content">
        	<div class="container-fluid">
            	<div class="row-fluid">
                	<div class="block span8">
                    	<p class="block-heading">
                        	<span id="calendarLoading" class="block-icon pull-right">
                            	<a href="#" rel="tooltip" title="We are fetching your calendar..."><i class="icon-spinner"></i> Loading... &nbsp;</a>
                            </span>
                            <span class="block-icon pull-right">
                            	<a href="webcal://orientation.ssmu.mcgill.ca/myweekintest/vcal.php?passkey=<?= $_GET['passkey'] ?>" rel="tooltip" title="Sync this calendar with your device"><i class="icon-download-alt"></i>Sync</a>
                            </span>
                            
                            <span>Calendar</span>
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
                            <p>Click on the events in your calendar to see more information, including location, or to unregister from events you can no longer attend.</p>
                            <p>
                            	Key: 
                            	<span class="calendar-key standard calendarInfoPopover" data-title="Standard Events" data-content="Show up to these events at the start time and expect to be there until the event ends.">Standard</span>
                                <span class="calendar-key dont-miss calendarInfoPopover" data-title="Don't Miss Events" data-content="Similar to standard events, you will be at these from start to finish, but these are events you should not miss during Orientation Week!">Don't Miss!</span>
                                <span class="calendar-key drop-by calendarInfoPopover" data-title="Drop By Events" data-content="These events are come-as-you-please; stop by any time during the event slot in your calendar.">Drop By Anytime</span>
                                <span class="calendar-key frosh calendarInfoPopover" data-title="Frosh Events" data-content="These are all of the events that are occuring during the frosh you are registered for.">Frosh</span>
                            </p>
                            <div id='calendar'></div>
                        </div>
                    </div>
                    
                    <div class="block span4">
                    	<p class="block-heading">Events with Selections</p>
	              		<div class="block-body">
                        	<p><a data-toggle="modal" class="btn btn-small btn-primary" role="button" href="#capNote"><i class="icon-tag"></i>&nbsp;&nbsp;A Note on Event Caps</a></p>
                        	<div aria-hidden="true" aria-labelledby="capNoteLabel" role="dialog" tabindex="-1" class="modal hide fade" id="capNote" style="display: none;">
                            	<div class="modal-header modal-default">
                                	<button aria-hidden="true" data-dismiss="modal" class="close" type="button"><i class="icon-remove-sign"></i></button>
                                    <h3 id="capNoteLabel">A Note on Event Caps</h3>
                                </div>
                                <div class="modal-body">
                                	<p>Please note that registration is not capped for Discover McGill: Academic Expectations workshops, Orientation Resource Room Drop-Ins, and "&Agrave; la carte" events (except the Amazing Library Race and the 4PM Masster Secret Tour).</p>
                                    <p>We know that your first week on campus can be hectic and that you might find yourself needing to spend time during Orientation Week elsewhere than the events you registered for weeks ago. We completely understand that, and we hope you understand why, for fairness to all students, we do not cap these events and instead make them “first-come, first-attend”. This system also ensures that no matter when you created your myWeek account, you would still be able to add the event to your calendar. The event organizer and the legal capacity of certain venues will dictate how many people can attend an event, but the event organizer may also be in touch with all registrants following Orientation Week to let them know if they will be hosting the same or a similar event later in the semester.</p>
                                </div>
                                <div class="modal-footer">
                                	<button class="btn btn-primary" data-dismiss="modal">Got it!</button>
                                </div>
                            </div>
                            
                            <?
							if($hasEventsWithSelections) {
								?>
                                <p>The following events are offered at multiple times.  Select which offering you want to go to!</p>
                                <p><strong>Events with multiple offerings:</strong></p>
                                <?
                                // loop through each of the participant's events
                                foreach($participant->events as $event) {
                                    // see if the event has selectable events
                                    if($event->hasSelectableEvents) {
                                        // see if they have made an initial decision
                                        $spanClass = "";
                                        if(!isset($selectedOptions[$event->id])) {
                                            $spanClass = "btn-warning";
                                        }
                                        
                                        // it does, so list it here
                                        echo("<p><span id=\"choiceSpan" . $event->id . "\" class=\"$spanClass\">" . $event->eventName . "</span><br />");
                                        echo("<select id=\"choice" . $event->id . "\" style=\"width:100%\" onchange=\"updateOptionChoice(" . $event->id . ", this.value)\">");
                                        
                                        // see if they have made an initial decision
                                        if(!isset($selectedOptions[$event->id])) {
                                            // they haven't; show a selectable option
                                            echo("<option value=\"\">Make a selection...</option>");
                                        }
                                        
                                        // sort the costs by start date
                                        $calendarEvents = $event->calendarEvents->toArray();
                                        usort($calendarEvents, 'dateCompare');
                                        
                                        foreach($calendarEvents as $option) {
                                            // see if it should be selected
                                            $selected = "";
                                            if(isset($selectedOptions[$event->id]) && $selectedOptions[$event->id] == $option->id) {
                                                $selected = "selected";
                                            }
                                            echo("<option value=\"" . $option->id . "\" $selected>" . formatSelectOptionDate($option->startTime, $option->endTime) . "</option>");
                                        }
                                        echo("<select></p>");
                                    }
                                }
                                ?>
                                <button class="btn btn-large btn-danger disabled"><i class="icon-save"></i> <span id="savingText">Saved</span></button>
                                <br />
                                <br />
                                <?
							}
							?>
                            <p>If you would like to register or unregister for events, you can do so here.</p>
                            <a href="changeRegistration.php?passkey=<?= $_GET['passkey'] ?>" class="btn btn-small btn-danger">Change Your Registration</a>
                        </div>
	                </div>
                    
                    <!--
                    <div class="block span4 todo">
                        <div class="block-heading">
                            <span class="block-icon pull-right">
                                <a href="#" class="demo-cancel-click" rel="tooltip" title="Refresh"><i class="icon-refresh"></i></a>
                            </span>
                            
                            <a href="#collapse-todo" data-toggle="collapse">Tasks</a>
                        </div>
                        
                        <div id="collapse-todo" class="block-body collapse in">
                            <section id="todoapp">
                                <header id="header">
                                    <input id="new-todo" placeholder="What needs to be done?" autofocus>
                                </header>
                                
                                <!- This section should be hidden by default and shown when there are todos ->
                                <section id="main">
                                    <ul id="todo-list">
                                        <!- These are here just to show the structure of the list items ->
                                        <!- List items should get the class `editing` when editing and `completed` when marked as completed ->
                                        <li class="completed">
                                            <div class="view">
                                                <div class="todo-check"><input class="toggle " type="checkbox" checked></div>
                                                <label>Create a Todo</label>
                                                <button class="destroy"></button>
                                            </div>
                                            <input class="edit" value="Create a TodoMVC template">
                                        </li>
                                        <li>
                                            <div class="view">
                                                <div class="todo-check"><input class="toggle" type="checkbox"></div>
                                                <label>Add color theme</label>
                                                <button class="destroy"></button>
                                            </div>
                                            <input class="edit" value="Rule the web">
                                        </li>
                                        <li>
                                            <div class="view">
                                                <div class="todo-check"><input class="toggle" type="checkbox"></div>
                                                <label>Remove edit class</label>
                                                <button class="destroy"></button>
                                            </div>
                                            <input class="edit" value="Rule the web">
                                        </li>
                                        <li>
                                            <div class="view">
                                                <div class="todo-check"><input class="toggle" type="checkbox"></div>
                                                <label>Mark as read</label>
                                                <button class="destroy"></button>
                                            </div>
                                            <input class="edit" value="Rule the web">
                                        </li>
                                        <li>
                                            <div class="view">
                                                <div class="todo-check"><input class="toggle" type="checkbox"></div>
                                                <label>Create a list item</label>
                                                <button class="destroy"></button>
                                            </div>
                                            <input class="edit" value="Rule the web">
                                        </li>       
                                    </ul>
                                </section>
                                
                                <footer id="footer">
                                    <span id="todo-count"><strong>1</strong> item left</span>
                                </footer>
                            </section>
                        </div>
                    </div>
                    -->
                    
                    <!--
                    <div class="block span4 notes">
                        <div class="block-heading">
                            <span class="block-icon pull-right">
                                <a href="#" class="demo-cancel-click" rel="tooltip" title="Save now"><i class="icon-save"></i></a>
                            </span>
                            
                            <a href="#collapse-task" data-toggle="collapse">Note</a>
                        </div> 
                        <div id="collapse-task" class="block-body collapse in">
                            <textarea cols="60" rows="10"></textarea>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
        <!-- end main content -->
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
					url: '/myweekintest/eventsToJson.php',
					type: 'POST',
					data: {
						passkey: '<?= $_GET['passkey'] ?>',
						timezone: currentTimezone
					},
					beforeSend: function() {
						$("#calendarLoading").html("<a id=\"loadingInfo\" href=\"#\" rel=\"tooltip\" title=\"We are fetching your calendar...\"><i class=\"icon-spinner\"></i> Loading... &nbsp;</a>");
						$("#loadingInfo").tooltip();
					}, 
					error: function() {
						alert("Oops!  We weren't able to load your calendar.  Please refresh the page");
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
					
					// see if they can unregister
					if(event.unregisterable) {
						popoverContent += "<form method=\"post\" action=\"/actions/myweek/unregisterSingleEvent.php\" onsubmit=\"return confirm('Are you sure you wish to unregister from this event?')\"><input name=\"eventId\" id=\"eventId\" type=\"hidden\" value=\"" + event.masterEventId + "\" /><input name=\"passkey\" id=\"passkey\" type=\"hidden\" value=\"<?= $_GET['passkey'] ?>\" /><button type=\"submit\" class=\"btn btn-small btn-danger\">Unregister</button></form>";
					}
					
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
		
		// saves the selection for this selectable event to the database
		function updateOptionChoice(passedEventId, passedOptionId) {
			if(passedOptionId) {
				// change the button text to show that we are saving
				$("#savingText").html("Saving...");
				
				// make the post
				$.post("/myweek/saveSelectionChoice.php", { eventId: passedEventId, 
																  optionId: passedOptionId,
																  passkey: '<?= $_GET['passkey'] ?>' },
						function(data){
							//alert("Data loaded: " + data);
							// refresh the calendar
							$('#calendar').fullCalendar('refetchEvents');
							
							// remove the blank option if it exists
							$("#choice" + passedEventId + " option[value='']").remove();
							
							// remove the caution title if it exists
							$("#choiceSpan" + passedEventId ).removeClass("btn-warning");
							
							// change the saving text to "savaed"
							$("#savingText").html("Saved");
							
							// see how many events we have left to select an option for
							calculateEventsAwaitingSelection();
						});
			}
		}
    </script>
    <? include("includes/unifiedJS.php") ?>
</body>
</html>