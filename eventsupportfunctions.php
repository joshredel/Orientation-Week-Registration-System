<?
/**
 * eventsupportfunctions.php
 * Containts functions to print and work with events.
 */

require_once("functions.php");

/**
 * Cycles through all events for a given category and returns the 
 * printable version for that category.
 */
function printCategoryEvents($category, $facultyRestriction = null, $livingStyleRestriction = null, $numberOfColumns = 2) {
	// instantiate a new event service
	$eventService = new services\EventService();
	$roleService = new services\RoleService();
	
	// define the constant tags
	$openingRow = "<div class=\"row-fluid service-grid\">";
	$closingRow = "</div>";
	
	$openingEvent = "<div class=\"span" . (12 / $numberOfColumns) . "\"><div class=\"row-fluid\"><div class=\"span4 text-center\" style=\"padding-top:15px\">";
	$inbetweenEvent = "</div><div class=\"span8\">";
	$closingEvent = "</div></div></div>";
	
	// go through each event and print its info
	$counter = 0;
	$rowClosed = false;
	try {
		$eventsListing = $eventService->getEventsWithCategory($category);
	} catch (Exception $e) {
		echo("<p>We are working on some of the events in this category.  Please try again in 30 minutes to register for more events from this category.</p>");
		return;
	}
	
	if($category == org\fos\Event::NON_FACULTY_FROSH || $category == org\fos\Event::FACULTY_FROSH) {
		shuffle($eventsListing);
	}
	foreach($eventsListing as $event) {
		// see if we even want this event
		// skip if it isn't within the faculty we're filtering for
		if($facultyRestriction != null && !in_array($facultyRestriction, $event->faculties())) {
			continue;
		}
		
		// skip if it isn't within the living style we're filtering for
		if($livingStyleRestriction != null && $livingStyleRestriction != $event->livingStyle) {
			continue;
		}
		
		// skip if it is a sub-event
		if($event->parentEvent != null) {
			continue;
		}
		
		// see if we should create a new row
		if($counter % $numberOfColumns == 0) {
			// we need to create a new row
			echo($openingRow);
			$rowClosed = false;
		}
		
		// print the opening of the event
		echo($openingEvent);
		
		// add the logo
		if($event->logoFileName != null) {
			//echo(resizeLogo("../images/logos/{$event->logoFileName}", 190, 130, "alt='logo'", false));
			echo("<img src='../images/logos/{$event->logoFileName}' alt='logo' />");
		} else {
			echo("<i class=\"icon-chevron-right red service-icon\"></i>");
		}
		
		// add the part between the logo and the rest
		echo($inbetweenEvent);
		
		// print the event name
		echo("<h2 class=\"event-header\">" . toPrettyPrint($event->eventName) . "</h2>");
		
		// branch based on availability of event organizer information
		echo("<p class=\"event-sink\">");
		if($event->website != null) {
			echo("Organized by <a href=\"" . $event->website . "\" target=\"_blank\">" . toPrettyPrint($event->hostedBy) . "</a>");
		} else {
			echo("Organized by " . toPrettyPrint($event->hostedBy));
		}
		if($event->email != null) {
			echo(" (<a href=\"mailto:" . $event->email . "\">contact us</a>)");
		}
		echo("</p>");
		
		// change the look depending on whether there are child events
		$optionalCost = null;
		if($event->options->isEmpty()) {
			// regular event; print normally
			// branch based on pricing logic
			if($event->costs->isEmpty()) {
				// now check to see what kind of event it is...
				if($event->action == org\fos\Event::ACTION_REGISTER) {
					if($event->category == org\fos\Event::FACULTY_FROSH || $event->category == org\fos\Event::NON_FACULTY_FROSH) {
						// it's a frosh event and should have a price...  price coming soon
						//echo("<p class=\"lead event-sink\">(check back soon for price)</p>");
					} else {
						// it's not a frosh, so it's "free"
						echo("<p class=\"lead event-sink\">FREE</p>");
					}
				}
			} else {
				// it has a cost to it
				// add up all of the fees
				$mandatoryCosts = 0;
				$optionalCosts = 0;
				foreach($event->costs as $cost) {
					if($cost->isOptional) {
						$optionalCosts += $cost->amount;
						$optionalCost = $cost;
					} else {
						$mandatoryCosts += $cost->amount;
					}
				}
				
				// print the mandatory cost
				echo("<p class=\"lead event-sink\">$" . $mandatoryCosts . " ");
				
				// print the optional costs, if there are any
				if($optionalCosts > 0) {
					echo("(+ $" . $optionalCosts . " optional) ");
				}
				
				// close out the lead paragraph
				echo("</p>");
			}
			
			// print the description button
			echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-desc-" . $event->id . "\">Tell me more!</a></p>");
			
			// print the price button, if necessary
			if($event->priceBreakdown != null) {
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-price-" . $event->id . "\">What's included?</a></p>");
			}
			
			// print the options button, if necessary
			if($optionalCost != null) {
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-option-" . $optionalCost->id . "\">What's optional?</a></p>");
			}
			
			// print the accordians
			echo("<div class=\"accordion\" id=\"accordian-parent-" . $event->id . "\">");
			
			// first the option information accordian
			if($optionalCost != null) {
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-option-" . $optionalCost->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								" . toPrettyPrint($optionalCost->summary) . "
							</div>
						</div>
					</div>");
			}
			
			// now the price breakdown accordian
			if($event->priceBreakdown != null) {
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-price-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								" . toPrettyPrint($event->priceBreakdown) . "
							</div>
						</div>
					</div>");
			}
			
			// now print the description accordian
			echo("<div class=\"accordion-group event-accordian-group\">
					<div id=\"accordian-desc-" . $event->id . "\" class=\"accordion-body collapse\">
						<div class=\"accordion-inner boxed\">
							" . toPrettyPrint($event->description) . "
						</div>
					</div>
				</div>");
			
			// close the accordian
			echo("</div>");
		} else {
			// it's a special event with sub-event "options"
			echo("<h3>Options:</h3>
				  <div class=\"accordion\" id=\"event-option-accordian-" . $event->id . "\">");
			
			// print an accordian entry for each option
			foreach($event->options as $eventOption) {
				// branch based on pricing logic
				$optionCost = "";
				if($eventOption->costs->isEmpty()) {
					// now check to see what kind of event it is...
					if($eventOption->category == org\fos\Event::FACULTY_FROSH || $eventOption->category == org\fos\Event::NON_FACULTY_FROSH) {
						// it's a frosh event and should have a price...  price coming soon
						$optionCost = "(check back soon for price)";
					} else {
						// it's not a frosh, so it's "free"
						$optionCost = "FREE";
					}
				} else {
					// it has a cost to it
					// add up all of the fees
					$mandatoryCosts = 0;
					$optionalCosts = 0;
					foreach($eventOption->costs as $cost) {
						if($cost->isOptional) {
							$optionalCosts += $cost->amount;
						} else {
							$mandatoryCosts += $cost->amount;
						}
					}
					
					// print the mandatory cost
					$optionCost = "$" . $mandatoryCosts;
					
					// print the optional costs, if there are any
					if($optionalCosts > 0) {
						$optionCost .= "(+ $" . $optionalCosts . " optional)";
					}
				}
				
				// prepare the accordian header
				echo("<div class=\"accordion-group event-accordian-group\">
					  	<div class=\"accordion-heading\">
							<a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"#event-option-accordian-" . $event->id . "\" href=\"#event-option-body-" . $eventOption->id . "\">
								" . $eventOption->eventName . "
							</a>
						</div>");
				
				// prepare the accordian itself
				echo("<div id=\"event-option-body-" . $eventOption->id . "\" class=\"accordion-body collapse\">
						<div class=\"accordion-inner boxed\">
							<p class=\"lead event-sink\">" . $optionCost . "</p>
							<p class=\"event-sink\">" . toPrettyPrint($eventOption->description) . "</p>
							<h4>What's included?</h4>
							<p>" . toPrettyPrint($eventOption->priceBreakdown) . "</p>
						</div>
					</div>");
				
				// close the accordian group
				echo("</div>");
			}
			
			// close the accordian for this event
			echo("</div>");
		}
		
		// close the event
		echo($closingEvent);
		
		// see if we should end the row
		if($counter % $numberOfColumns == 1 || $numberOfColumns == 1) {
			// we need to end the row
			echo($closingRow);
			$rowClosed = true;
		}
		
		// increment the counter
		$counter++;
	}
	
	// see if we should end the row
	if($counter > 0 && $rowClosed == false) {
		// we need to end the row
		echo($closingRow);
	}
}

/**
 * Cycles through all events for a given category and returns the 
 * printable version for that category in the registration form.
 */
function printCategoryEventsForRegistration($category, $facultyRestriction = null, $livingStyleRestriction = null, $numberOfColumns = 2) {
	// instantiate a new event service
	$eventService = new services\EventService();
	
	// define the constant tags
	$openingRow = "<div class=\"row-fluid service-grid\">";
	$closingRow = "</div>";
	
	$openingEvent = "<div class=\"span" . (12 / $numberOfColumns) . "\"><div class=\"row-fluid\"><div class=\"span4 text-center\" style=\"padding-top:15px\">";
	$inbetweenEvent = "</div><div class=\"span8\">";
	$closingEvent = "</div></div></div>";
	
	// go through each event and print its info
	$counter = 0;
	$rowClosed = false;
	
	// we have to add faculty froshes by hand
	// this is an extremely bizarre error... fetching them from any sort of DQL causes an error 
	// that claims a duplicate column name when none exists... this is most likely a Doctrine bug that might
	// relate to some weird data in the database it doesn't like (reserved characters, etc.)
	// this really needs to be fixed!!!
	try {
		$eventsListing = $eventService->getEventsWithCategory($category);
	} catch (Exception $e) {
		echo("<p>We are working on some of the events in this category.  Please try again in 30 minutes to register for more events from this category.</p>");
		return;
	}
	/*if($category == org\fos\Event::REZ_FEST) {
		$eventsListing[] = $eventService->getEvent(4);
		$eventsListing[] = $eventService->getEvent(5);
		$eventsListing[] = $eventService->getEvent(60);
	} elseif($category == org\fos\Event::DISCOVER_MCGILL) {
		$eventsListing[] = $eventService->getEvent(2);
		$eventsListing[] = $eventService->getEvent(3);
	} elseif($category == org\fos\Event::ACADEMIC_EXPECTATIONS) {
		$eventsListing[] = $eventService->getEvent(6);
		$eventsListing[] = $eventService->getEvent(7);
		$eventsListing[] = $eventService->getEvent(8);
		$eventsListing[] = $eventService->getEvent(9);
		$eventsListing[] = $eventService->getEvent(10);
		$eventsListing[] = $eventService->getEvent(64);
		$eventsListing[] = $eventService->getEvent(65);
	} elseif($category == org\fos\Event::A_LA_CARTE) {
		$eventsListing[] = $eventService->getEvent(11);
		$eventsListing[] = $eventService->getEvent(12);
		$eventsListing[] = $eventService->getEvent(13);
		$eventsListing[] = $eventService->getEvent(15);
		$eventsListing[] = $eventService->getEvent(16);
		$eventsListing[] = $eventService->getEvent(17);
		$eventsListing[] = $eventService->getEvent(18);
		$eventsListing[] = $eventService->getEvent(19);
		$eventsListing[] = $eventService->getEvent(20);
		$eventsListing[] = $eventService->getEvent(21);
		$eventsListing[] = $eventService->getEvent(73);
	} elseif($category == org\fos\Event::ORIENTATION_CENTRE) {
		$eventsListing[] = $eventService->getEvent(24);
	} elseif($category == org\fos\Event::DROP_IN) {
		$eventsListing[] = $eventService->getEvent(25);
		$eventsListing[] = $eventService->getEvent(26);
		$eventsListing[] = $eventService->getEvent(27);
		$eventsListing[] = $eventService->getEvent(28);
		$eventsListing[] = $eventService->getEvent(29);
		$eventsListing[] = $eventService->getEvent(30);
		$eventsListing[] = $eventService->getEvent(31);
		$eventsListing[] = $eventService->getEvent(32);
		$eventsListing[] = $eventService->getEvent(35);
		$eventsListing[] = $eventService->getEvent(63);
	} elseif($category == org\fos\Event::FACULTY_FROSH) {
		$eventsListing[] = $eventService->getEvent(41);
		$eventsListing[] = $eventService->getEvent(42);
		$eventsListing[] = $eventService->getEvent(43);
		$eventsListing[] = $eventService->getEvent(44);
		$eventsListing[] = $eventService->getEvent(46);
		$eventsListing[] = $eventService->getEvent(47);
		$eventsListing[] = $eventService->getEvent(48);
		$eventsListing[] = $eventService->getEvent(49);
		$eventsListing[] = $eventService->getEvent(61);
	} elseif($category == org\fos\Event::NON_FACULTY_FROSH) {
		$eventsListing[] = $eventService->getEvent(39);
		$eventsListing[] = $eventService->getEvent(51);
		$eventsListing[] = $eventService->getEvent(52);
		$eventsListing[] = $eventService->getEvent(53);
		$eventsListing[] = $eventService->getEvent(54);
		$eventsListing[] = $eventService->getEvent(55);
		$eventsListing[] = $eventService->getEvent(56);
		$eventsListing[] = $eventService->getEvent(57);
		$eventsListing[] = $eventService->getEvent(58);
		$eventsListing[] = $eventService->getEvent(59);
	} elseif($category == org\fos\Event::ORANGE_EVENT) {
		$eventsListing[] = $eventService->getEvent(22);
		$eventsListing[] = $eventService->getEvent(23);
	} elseif($category == org\fos\Event::OOHLALA) {
		$eventsListing[] = $eventService->getEvent(36);
	} elseif($category == org\fos\Event::PLUS_EVENT) {
		$eventsListing[] = $eventService->getEvent(62);
		$eventsListing[] = $eventService->getEvent(37);
		$eventsListing[] = $eventService->getEvent(38);
		$eventsListing[] = $eventService->getEvent(72);
	} elseif($category == org\fos\Event::INTERNATIONAL) {
		$eventsListing[] = $eventService->getEvent(66);
		$eventsListing[] = $eventService->getEvent(67);
		$eventsListing[] = $eventService->getEvent(68);
	}*/
	
	if($category == org\fos\Event::NON_FACULTY_FROSH || $category == org\fos\Event::FACULTY_FROSH) {
		shuffle($eventsListing);
	}
	foreach($eventsListing as $event) {
		// see if we even want this event
		// skip if it isn't within the faculty we're filtering for
		if($facultyRestriction != null && !in_array($facultyRestriction, $event->faculties())) {
			continue;
		}
		
		// skip if it isn't within the living style we're filtering for
		if($livingStyleRestriction != null && $livingStyleRestriction != $event->livingStyle) {
			continue;
		}
		
		// skip if it is a sub-event
		if($event->parentEvent != null) {
			continue;
		}
		
		// see if we should create a new row
		if($counter % $numberOfColumns == 0) {
			// we need to create a new row
			echo($openingRow);
			$rowClosed = false;
		}
		
		// print the opening of the event
		echo($openingEvent);
		
		// add the logo
		if($event->logoFileName != null) {
			//echo(resizeLogo("../images/logos/{$event->logoFileName}", 190, 130, "alt='logo'", false));
			echo("<img src='../images/logos/{$event->logoFileName}' alt='logo' />");
		} else {
			echo("<i class=\"icon-chevron-right red service-icon\"></i>");
		}
		
		// add the part between the logo and the rest
		echo($inbetweenEvent);
		
		// print the event name
		echo("<h2 class=\"event-header\">" . toPrettyPrint($event->eventName) . "</h2>");
		
		// change the look depending on whether there are child events
		$optionalCost = null;
		$mandatoryCosts = 0;
		$optionalCosts = 0;
		if($event->options->isEmpty()) {
			// regular event; print normally
			// branch based on pricing logic
			if($event->costs->isEmpty()) {
				// now check to see what kind of event it is...
				if($event->action == org\fos\Event::ACTION_REGISTER) {
					if($event->category == org\fos\Event::FACULTY_FROSH || $event->category == org\fos\Event::NON_FACULTY_FROSH) {
						// it's a frosh event and should have a price...  price coming soon
						echo("<p class=\"lead event-sink\">(check back soon for price)</p>");
					} else {
						// it's not a frosh, so it's "free"
						echo("<p class=\"lead event-sink\">FREE</p>");
					}
				}
			} else {
				// it has a cost to it
				// add up all of the fees
				foreach($event->costs as $cost) {
					if($cost->isOptional) {
						$optionalCosts += $cost->amount;
						$optionalCost = $cost;
					} else {
						$mandatoryCosts += $cost->amount;
					}
				}
				
				// print the mandatory cost
				echo("<p class=\"lead event-sink\">$" . $mandatoryCosts . " ");
				
				// print the optional costs, if there are any
				if($optionalCosts > 0) {
					echo("(+ $" . $optionalCosts . " optional) ");
				}
				
				// close out the lead paragraph
				echo("</p>");
			}
			
			// print the description button
			echo("<p><a class=\"btn-main-small event-opener gray-reg\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-desc-" . $event->id . "\">Tell me more!</a></p>");
			
			// print the price button, if necessary
			if($event->priceBreakdown != null) {
				echo("<p><a class=\"btn-main-small event-opener gray-reg\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-price-" . $event->id . "\">What's included?</a></p>");
			}
			
			// print the options button, if necessary
			if($optionalCost != null) {
				echo("<p><a class=\"btn-main-small event-opener gray-reg\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-option-" . $optionalCost->id . "\">What's optional?</a></p>");
			}
			
			// print the action button
			if($event->action == org\fos\Event::ACTION_REGISTER) {
				// it's a standard register... ask them to "sign me up!"
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Sign me up!</a></p>");
			} elseif($event->action == org\fos\Event::ACTION_AUTO_REGISTER) {
				// it's an auto-regsiter event... tell them they've already been added
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Already Registered</a></p>");
			} elseif($event->action == org\fos\Event::ACTION_REMINDER) {
				// it's an event to be reminded... tell them they can ask to be reminded
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Remind me!</a></p>");
			} elseif($event->action == org\fos\Event::ACTION_INFO_ONLY) {
				// it's an event merely for information... create a standard "info" tab
				echo("<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">What to do...</a></p>");
			}
			
			// print the accordians
			echo("<div class=\"accordion\" id=\"accordian-parent-" . $event->id . "\">");
			
			// check to see if we should tell the user what to do if the event is for info only
			if($event->action == org\fos\Event::ACTION_REGISTER) {
				// it's a standard register... ask them to "sign me up!"
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-action-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">");
				
				// check some information about the event before allowing them to register
				$today = new DateTime(NULL, new DateTimeZone("America/Montreal"));
				if($event->participantCap != 0 && count($event->participants) >= $event->participantCap) {
					// the event is at capacity!
					echo("<p>Sorry, this event is already booked to capacity.  As such, you are unable to register for this event.</p>
						  <p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Thanks for letting me know!</a></p>");
				} elseif(!($today >= $event->registrationOpenDate && $today <= $event->registrationCloseDate)) {
					// today is outside of the registration open and close dates
					echo("<p>Online registration for this event has ended.  Stop by the Y-Intersection to register in person.</p>
						  <p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Thanks for letting me know!</a></p>");
				} elseif($event->costs->isEmpty() && ($event->category == org\fos\Event::FACULTY_FROSH || $event->category == org\fos\Event::NON_FACULTY_FROSH)) {
					// there is no cost for a faculty or non-faculty frosh (so we can't register!)
					echo("<p>The event hasn't yet been priced.  As such, you are unable to register for this event.  Please check back soon!</p>
						  <p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Thanks for letting me know!</a></p>");
				} else {
					// we can regsiter!
					// see if there is a price
					if($event->costs->isEmpty()) {
						// there is no price, so simply ask yes or no
						echo("<p>Would you like to register for " . toPrettyPrint($event->eventName) . "?</p>
							  <p><a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\" onClick=\"addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', false, 0, true, '" . $event->category . "')\">Yes, please!</a>&nbsp;&nbsp;
							  <a class=\"m-btn event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">No, cancel.</a></p>");
					} else {
						// there are prices
						// branch based on whether there are options
						if($optionalCost == null) {
							// there is just a base cost
							// see if there is a bursary notice
							if($event->bursaryNotice != null && strlen($event->bursaryNotice)) {
								// print the  bursary notice
								echo("<p>" . toPrettyPrint($event->bursaryNotice) . "</p>");
							}
							
							// print the standard info
							echo("<p>Would you like to register for \"" . toPrettyPrint($event->eventName) . "\" for <strong>$" . $mandatoryCosts . "</strong>?</p>
							  	  <p><a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\" onClick=\"addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', false, " . $mandatoryCosts . ", true, '" . $event->category . "')\">Yes, please!</a>&nbsp;&nbsp;
							  	  <a class=\"m-btn event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">No, cancel.</a></p>");
						} else {
							// there is a base and an optional cost
							$fullCost = $mandatoryCosts + $optionalCosts;
							
							echo("<p>Would you like to register for " . toPrettyPrint($event->eventName) . "?</p>
							  	  <p><a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\" onClick=\"addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', true, " . $fullCost . ", true, '" . $event->category . "')\">Yes, with  option! ($" . $fullCost . ")</a>
								  <a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\" onClick=\"addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', false, " . $mandatoryCosts . ", true, '" . $event->category . "')\">Yes, without option! ($" . $mandatoryCosts . ")</a>
							  	  <a class=\"m-btn event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">No, cancel.</a></p>");
						}
					}
				}
				
				// close out the accordion
				echo("</div></div></div>");
			} elseif($event->action == org\fos\Event::ACTION_AUTO_REGISTER) {
				// it's an auto-regsiter event... tell them they've already been added
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-action-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								<p>You've been automatically registered for this event, as it is crucial to your first week that you attend.</p>
								<p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Thanks for letting me know!</a></p>
								<script>addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', false, 0, false, '" . $event->category . "')</script>
							</div>
						</div>
					</div>");
			} elseif($event->action == org\fos\Event::ACTION_REMINDER) {
				// it's an event to be reminded... tell them they can ask to be reminded
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-action-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								<p>This event happens outside of the regular Orientation Event.<br />
								Would you like to be reminded of this event when it gets close?</p>
								<p><a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\" onClick=\"addEventToTicket(" . $event->id . ", '" . toPrettyPrint($event->eventName) . "', false, 0, true, '" . $event->category . "')\">Yes, please!</a>
								<a class=\"m-btn event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">No, cancel.</a></p>
							</div>
						</div>
					</div>");
			} elseif($event->action == org\fos\Event::ACTION_INFO_ONLY) {
				// it's an event merely for information... create a standard "info" tab
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-action-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								<p>This event is here just for your information.  Click \"Tell me more!\" to learn about the event.</p>
								<p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $event->id . "\" href=\"#accordian-action-" . $event->id . "\">Thanks for letting me know!</a></p>
							</div>
						</div>
					</div>");
			}
			
			// now the option information accordian
			if($optionalCost != null) {
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-option-" . $optionalCost->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								<p>" . toPrettyPrint($optionalCost->summary) . "</p>
							</div>
						</div>
					</div>");
			}
			
			// now the price breakdown accordian
			if($event->priceBreakdown != null) {
				echo("<div class=\"accordion-group event-accordian-group\">
						<div id=\"accordian-price-" . $event->id . "\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner boxed\">
								<p>" . toPrettyPrint($event->priceBreakdown) . "</p>
							</div>
						</div>
					</div>");
			}
			
			// now print the description accordian
			echo("<div class=\"accordion-group event-accordian-group\">
					<div id=\"accordian-desc-" . $event->id . "\" class=\"accordion-body collapse\">
						<div class=\"accordion-inner boxed\">
							<p>" . toPrettyPrint($event->description) . "</p>");
			// branch based on availability of event organizer information
			echo("<br /><p class=\"event-sink\">");
			if($event->website != null) {
				echo("Organized by <a href=\"" . $event->website . "\" target=\"_blank\">" . toPrettyPrint($event->hostedBy) . "</a>");
			} else {
				echo("Organized by " . toPrettyPrint($event->hostedBy));
			}
			if($event->email != null) {
				echo(" (<a href=\"mailto:" . $event->email . "\">contact us</a>)");
			}
			echo("</p></div></div></div>");
			
			// close the accordian
			echo("</div>");
		} else {
			// it's a special event with sub-event "options"
			echo("<h3>Options:</h3>
				  <div class=\"accordion\" id=\"event-option-accordian-" . $event->id . "\">");
			
			// print an accordian entry for each option
			foreach($event->options as $eventOption) {
				// branch based on pricing logic
				$optionCost = "";
				$mandatoryCosts = 0;
				$optionalCosts = 0;
				if($eventOption->costs->isEmpty()) {
					// now check to see what kind of event it is...
					if($eventOption->category == org\fos\Event::FACULTY_FROSH || $eventOption->category == org\fos\Event::NON_FACULTY_FROSH) {
						// it's a frosh event and should have a price...  price coming soon
						$optionCost = "(check back soon for price)";
					} else {
						// it's not a frosh, so it's "free"
						$optionCost = "FREE";
					}
				} else {
					// it has a cost to it
					// add up all of the fees
					foreach($eventOption->costs as $cost) {
						if($cost->isOptional) {
							$optionalCosts += $cost->amount;
						} else {
							$mandatoryCosts += $cost->amount;
						}
					}
					
					// print the mandatory cost
					$optionCost = "$" . $mandatoryCosts;
					
					// print the optional costs, if there are any
					if($optionalCosts > 0) {
						$optionCost .= "(+ $" . $optionalCosts . " optional)";
					}
				}
				
				// prepare the accordian header
				echo("<div class=\"accordion-group event-accordian-group\">
					  	<div class=\"accordion-heading\">
							<a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"#event-option-accordian-" . $event->id . "\" href=\"#event-option-body-" . $eventOption->id . "\">
								" . $eventOption->eventName . "
							</a>
						</div>");
				
				// prepare the accordian itself
				echo("<div id=\"event-option-body-" . $eventOption->id . "\" class=\"accordion-body collapse\">
						<div class=\"accordion-inner boxed\">
							<p class=\"lead event-sink\">" . $optionCost . "</p>
							<p><a class=\"btn-main-small event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-parent-" . $eventOption->id . "\" href=\"#accordian-action-" . $eventOption->id . "\">Sign me up!</a></p>
							<div class=\"accordion\" id=\"accordian-option-" . $eventOption->id . "\">
								<div class=\"accordion-group event-accordian-group\">
									<div id=\"accordian-action-" . $eventOption->id . "\" class=\"accordion-body collapse\">
										<div class=\"accordion-inner boxed\">");
				if($eventOption->participantCap != 0 && count($eventOption->participants) >= $eventOption->participantCap) {
					// the event is at capacity!
					echo("<p>Sorry, this event is already booked to capacity.  As such, you are unable to register for this event.</p>
						  <p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-option-" . $eventOption->id . "\" href=\"#accordian-option-" . $eventOption->id . "\">Thanks for letting me know!</a></p>");
				} elseif(!($today >= $eventOption->registrationOpenDate && $today <= $eventOption->registrationCloseDate)) {
					// today is outside of the registration open and close dates
					echo("<p>Online registration for this event has ended.  Stop by the Y-Intersection to register in person.</p>
						  <p><a class=\"m-btn btn-red event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-option-" . $eventOption->id . "\" href=\"#accordian-option-" . $eventOption->id . "\">Thanks for letting me know!</a></p>");
				} else {
											echo("<p>Would you like to register for \"" . toPrettyPrint($eventOption->eventName) . "\" for <strong>$" . $mandatoryCosts . "</strong>?</p>
											<p><a class=\"m-btn event-opener red-btn\" data-toggle=\"collapse\" data-parent=\"#accordian-option-" . $event->id . "\" href=\"#accordian-action-" . $eventOption->id . "\" onClick=\"addEventToTicket(" . $eventOption->id . ", '" . toPrettyPrint($eventOption->eventName) . "', false, " . $mandatoryCosts . ", true, '" . $eventOption->category . "')\">Yes, please!</a>&nbsp;&nbsp;
											<a class=\"m-btn event-opener\" data-toggle=\"collapse\" data-parent=\"#accordian-option-" . $eventOption->id . "\" href=\"#accordian-action-" . $eventOption->id . "\">No, cancel.</a></p>");}
										echo("</div>
									</div>
								</div>
							</div>
							<p class=\"event-sink\">" . toPrettyPrint($eventOption->description) . "</p>
							<h4>What's included?</h4>
							<p>" . toPrettyPrint($eventOption->priceBreakdown) . "</p>
							
						</div>
					</div>");
				
				// close the accordian group
				echo("</div>");
			}
			
			// close the accordian for this event
			echo("</div>");
		}
		
		// close the event
		echo($closingEvent);
		
		// see if we should end the row
		if($counter % $numberOfColumns == 1 || $numberOfColumns == 1) {
			// we need to end the row
			echo($closingRow);
			$rowClosed = true;
		}
		
		// increment the counter
		$counter++;
	}
	
	// see if we should end the row
	if($counter > 0 && $rowClosed == false) {
		// we need to end the row
		echo($closingRow);
	}
}
?>