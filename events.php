<?
require_once('functions.php');
include_once('eventsupportfunctions.php');

// create an event service and get all events
$eventService = new services\EventService();
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Events</title>
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

<body>
	<? include_once("analytics.php") ?>
	<div class="navbar navbar-inverse navbar-fixed-top animated fadeInDownBig">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="index.php"><img src="/images/frontend/logo.png" alt="Title"></a>
				<div class="nav-collapse collapse">
					<ul class="nav pull-right">
						<li><a href="index.php">Home</a></li>
						<li class="active"><a href="events.php">Events</a></li>
						<li><a href="contact.php">Contact Us</a></li>
						<li><a href="map.php">Map</a></li>
						<!--<li><a href="tips.php">Helpful Hints</a></li>-->
						<li><a href="faq.php">FAQs</a></li>
						<li><a href="parents.php">Parents</a></li>
						<!--<li><a href="login.php"><span class="light-gray"><i class="icon-user"></i> Login</span></a></li>-->
						<li><a href="/registration/"><span class="menu-button">Register</span></a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
	</div>
    
    
    <!--Main Content-->
    <section id="content">
        <div class="container">
			<div class="row-fluid">
                <div class="span12">
                    <h2>Orientation Week Events</h2>
                    <p class="lead">Below is the vast selection of over 40 different ways to immerse yourself in all that is McGill during your first week!</p>
                    <p>Scan through each category to learn about each and every event available to you.  Some events are an absolute must, while other categories let you choose what fits you most!  Once you've finished browsing here, you can <a href="/registration/">register</a> for the events you want to attend.</p><br />
                </div>
            </div>
            <?
			if (!isset($_POST['save']) || $_POST['save'] != 'filter') {
			?>
            <div class="row-fluid">
                <div class="span6">
                    <p class="lead">Before we get started, we have a few simple questions...</p>
                    <form method="POST" enctype="multipart/form-data" class="form-horizontal" onSubmit="return checkForm(this);">
                        <!--
                        <div class="control-group">
                        	<label for="studentRez" class="span4 control-label"><strong>Where will you be living?</strong></label>
                            <div class="span8">
                                <select class="input-block-level field-popover" id="studentRez" name="studentRez" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Where will you be living in the coming year?">
                                	<option value="">Select your living style...</option>
                                    <option value='InRez'>In a McGill Residence</option>
                                    <option value='OffCampus'>Off Campus</option>
                                </select>
                            </div>
                        </div>
                        -->
                        
                        <div class="control-group">
                        	<label for="studentFaculty" class="span4 control-label"><strong>In which faculty are you a student?</strong></label>
                            <div class="span8">
                                <select class="input-block-level field-popover" id="studentFaculty" name="studentFaculty" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="Tell us in which faculty you will be studying at McGill.  This is important to make sure we know what events to help you pick (and don't lie... we'll know, we're quite keen!)<br /><br />Ok, so this can be a bit tricky.  But if you read up here, we hope we can help!<br /><br />This is the faculty in which you will be studying, not the degree program or the department.<br />Kinesiology (BSc) students should choose Faculty of Education<br />Pre-Med or Pre-Dent students should choose Faculty of Science">
                                    <option value="">Select your faculty...</option>
                                    <option value='AG'>Faculty of Agriculture / Environmental Science</option>
                                    <option value='AR'>Faculty of Arts</option>
                                    <option value='AS'>Faculty of Arts &amp; Science</option>
                                    <!--<option value='DE'>Faculty of Dentistry</option>-->
                                    <option value='ED'>Faculty of Education</option>
                                    <option value='EN'>Faculty of Engineering</option>
                                    <option value='LW'>Faculty of Law</option>
                                    <!--<option value='MD'>Faculty of Medicine</option>-->
                                    <option value='RS'>Faculty of Religious Studies</option>
                                    <option value='SC'>Faculty of Science</option>
                                    <option value='MG'>Desautels Faculty of Management</option>
                                    <option value='MU'>Schulich School of Music</option>
                                    <option value='EN'>School of Architecture</option>
                                    <option value='NU'>School of Nursing</option>
                                    <option value='PO'>School of Physical &amp; Occupational Therapy</option>
                                    <option value='AR'>School of Social Work</option>
                                </select>
                            </div>
                        </div>
                        
                        <!--
                        <div class="control-group">
                        	<label for="studentOrigin" class="span4 control-label"><strong>What is your place of origin?</strong></label>
                            <div class="span8">
                                <select class="input-block-level field-popover" id="studentOrigin" name="studentOrigin" required data-toggle="popover" data-placement="top" data-trigger="focus" data-html="true" data-content="This field should reflect your student status (what kind of tuition will you be paying?) and is important because it helps us make sure you are going to events best suited for wherever you are coming from!">
                                	<option value="">Select your place of origin...</option>
                                    <option value="Quebec">Quebec</option>
                                    <option value="RestOfCanada">Rest of Canada (outside of Quebec)</option>
                                    <option value="International">International</option>
                                </select>
                            </div>
                        </div>
                        -->
                        
                        <div class="control-group">
                        	<div class="span8 offset4">
                                <input type="hidden" name="save" value="filter">
                                <button type="submit" class="btn-main-small">Let's go!</button>
                            </div>
                        </div>
                	</form>
                </div>
            </div>
            <?
				// endif for if: END printing if the rez/faculty form was NOT filled in
			} else {
				// printing if the rez/faculty form was filled in
				// store the results of the form
				$selectedFaculty = $_POST['studentFaculty'];
				$selectedLivingStyle = $_POST['studentRez'];
				$selectedOrigin = $_POST['studentOrigin'];
            ?>
            <div class="row-fluid">
                <div class="span12">
                    <form method="POST" class="form-horizontal">
                        <div class="control-group">
                        	<div>
                            	<!--<p>You're viewing events filtered for students in the <strong><?= convertCodeToFaculty($selectedFaculty) ?></strong> living <strong><?= convertCodeToLivingStyle($selectedLivingStyle) ?></strong> originating from <strong><?= convertCodeToOrigin($selectedOrigin) ?></strong>.</p>-->
                                <p>You're viewing events filtered for students in the <strong><?= convertCodeToFaculty($selectedFaculty) ?></strong>.</p>
                                <p>Not for me? <button type="submit" class="btn btn-small">Reset my selection!</button></p>
                                <input type="hidden" name="save" value="unfilter">
                                
                            </div>
                        </div>
                	</form>
                </div>
            </div>
            
            <div class="row-fluid">
            	<div class="span12">
                    <div class="accordion" id="eventsAccordian">
                    	<!-- Discover McGill Day -->
                        <? if($selectedFaculty != "LW") { ?>
                        <div class="accordion-group master-event-group">
                        	<div class="accordion-heading">
                            	<a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#discovermcgill">
                                	Discover McGill &amp; Engage McGill<br /><i class="icon-angle-right"></i> Campus-wide, day-long orientation event for all new McGill students
                                </a>
                            </div>
                            <div id="discovermcgill" class="accordion-body collapse">
                            	<div class="accordion-inner">
                                	<h4>Tuesday, August 26 to Wednesday, August 27</h4>
                    				<!--<p>Discover McGill is a fun, energetic campus-wide welcome day to "kick-off" Orientation Week! Your attendance is crucial because you’ll meet returning students and new friends, and learn about important faculty-specific academic and advising information, as well as discover all of the many support services that exist just for you. New this year will be an Engage McGill closing event that will invite the larger McGill community to celebrate the new entering class and the start of the new school year.</p>-->
                                	<?
                                    printCategoryEvents(org\fos\Event::DISCOVER_MCGILL, $selectedFaculty);
									?>
                                </div>
                            </div>
                        </div>
                        <? } // end law check?>
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
                                	<h4>Thursday, August 28 to Sunday, August 31</h4>
                                    <p>Froshes are awesome, multi-day events run by student organizations and bring new students together in a social environment. They are an excellent way to get oriented to the city and to social life at McGill. You can register for your Faculty Frosh or for any of the Non-Faculty Froshes. Regardless of what you end up doing, no new student's welcome to McGill would be complete (or as memorable!) without doing a Frosh!</p>
                                    <div class="row-fluid">
                                    	<div class="span6">
                                        	<h3>Faculty Frosh</h3>
											<?
                                            printCategoryEvents(org\fos\Event::FACULTY_FROSH, $selectedFaculty, null, 1);
                                            ?>
                                            <!--<p>Register to see the froshes available for your faculty!</p>-->
                                        </div>
                                    	<div class="span6">
                                        	<h3>Non-Faculty Frosh</h3>
											<?
                                            printCategoryEvents(org\fos\Event::NON_FACULTY_FROSH, null, null, 1);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END Froshes -->
                        
                        
                        <!-- How to Frosh -->
                        <div class="accordion-group master-event-group">
                        	<div class="accordion-heading">
                            	<a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#howtofrosh">
                                	How to Frosh<br /><i class="icon-angle-right"></i> Learn about what to expect during your frosh
                                </a>
                            </div>
                            <div id="howtofrosh" class="accordion-body collapse">
                            	<div class="accordion-inner">
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/H2F.JPG' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">How to Frosh</h2>
                                        <!--<h4>August 22 to September 6</h4>-->
                                    	<p>Come meet, hear from, and speak to the student coordinators behind this year's various Froshes. Learn about how Frosh works, discover the programs and resources available to you during Frosh, and get answers to any of your lingering questions about Frosh and Orientation Week. A must-attend event for anyone planning to or still wondering if they should do Frosh!</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::ORIENTATION_CENTRE);
									?>
                                </div>
                            </div>
                        </div>
                        <!-- END How to Frosh -->
                        
                        
                    	<!-- RezFest-->
                    	<? if($selectedFaculty != "LW" && $selectedFaculty != "AG") { ?>
                        <div class="accordion-group master-event-group">
                        	<div class="accordion-heading">
                            	<a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#rezfest">
                                	Rez &amp; Off Campus Fests<br /><i class="icon-angle-right"></i> Day-long event for students living in residence or off campus
                                </a>
                            </div>
                            <div id="rezfest" class="accordion-body collapse">
                            	<div class="accordion-inner">
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/Rez.jpg' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">Rez &amp; Off Campus Fests</h2>
                                        <h4>Monday, August 25</h4>
                                        <p class="event-sink">Organized by <a href="http://www.mcgill.ca/students/housing/life" target="_blank">McGill Student Housing and Residence Life</a> (<a href="mailto:housing.residences@mcgill.ca">contact us</a>)</p>
                                        <p>Rez Fest is a celebration of all things Rez: it begins first thing in the morning when everyone gathers in their halls to get ready for the full day ahead. The day culminates in the highly-anticipated annual Rez Warz competition.</p>
                                        <p>OC Fest is for all the off-campus students and is hosted by our Off-Campus Fellows. These students will also get a chance to come together and explore what McGill and Montreal have to offer.</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::REZ_FEST, null, $selectedLivingStyle);
									?>
                                </div>
                            </div>
                        </div>
                        <? } // end law check?>
                        <!-- END RezFest -->
                        
                        
                        <!-- International Events -->
                        <!--
                        <? if($selectedOrigin == "International") { ?>
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
                                    printCategoryEvents(org\fos\Event::INTERNATIONAL);
									?>
                                </div>
                            </div>
                        </div>
                        <? } // end international check?>
                        -->
                        <!-- END International Events -->
                        
                        
                        <!-- DM Academic Expectations -->
                        <!--
                        <? if($selectedFaculty != "AG") { ?>
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
                                    printCategoryEvents(org\fos\Event::ACADEMIC_EXPECTATIONS);
									?>
                                </div>
                            </div>
                        </div>
                        <? } // end ag check ?>
                        -->
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
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/ALC.JPG' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">"&Agrave; la carte" Events</h2>
                                        <h4>Wednesday, August 27</h4>
                                        <p>Run by different on-campus services, groups, and clubs, &Agrave; la carte events let you explore some of the different things that might interest or be of service to you during your time at McGill. The full list of ALC events will be updated in the next few weeks.</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::A_LA_CARTE);
									?>
                                </div>
                            </div>
                        </div>
                        <!-- END A la carte Events -->
                        
                        
                        <!-- Drop-Ins -->
                        <div class="accordion-group master-event-group">
                        	<div class="accordion-heading">
                            	<a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#dropins">
                                	Orientation Resource Room Drop-Ins<br /><i class="icon-angle-right"></i> Drop in and talk to experts at McGill on a variety of topics
                                </a>
                            </div>
                            <div id="dropins" class="accordion-body collapse">
                            	<div class="accordion-inner">
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/RRDI.JPG' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">Resource Room Drop-Ins</h2>
                                		<p>Drop-in sessions in the Campus Life &amp; Engagement Resource Room will allow students to get their questions on different topics answered by those in-the-know. Stay tuned for more information on who will be spending time and answering questions in the Room in the next few weeks.</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::DROP_IN);
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
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/OC.png' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">Orientation Centre</h2>
                                        <h4>August 20 to September 5</h4>
                                        <p class="event-sink">Organized by <a href="http://www.mcgill.ca/firstyear/undergrad-students/week/orientation-centre-resource-room" target="_blank">Campus Life &amp; Engagement (CL&amp;E)</a> (<a href="mailto:firstyear@mcgill.ca">contact us</a>)</p>
                                        <p>A first and frequent stop for everyone! You will undoubtedly have a long list of questions when you arrive at McGill, and the best place to get honest answers is at the Orientation Centre. The Centre is staffed by experienced McGill students ready to provide information about clubs, organizations and services at McGill, as well as life in Montreal.  It's the longest-lasting orientation event on campus as it runs for two-and-a-half weeks!</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::ORIENTATION_CENTRE);
									?>
                                </div>
                            </div>
                        </div>
                        <!-- END Orientation Resource Centre -->
                        
                        
                        <!-- Orientation PLUS -->
                        <div class="accordion-group master-event-group">
                        	<div class="accordion-heading">
                            	<a class="accordion-toggle" data-toggle="collapse" data-parent="#eventsAccordian" href="#orientationplus">
                                	Orientation PLUS<br /><i class="icon-angle-right"></i> Make sure to attend these events happening after Orientation Week
                                </a>
                            </div>
                            <div id="orientationplus" class="accordion-body collapse">
                            	<div class="accordion-inner">
                                	<div class="span4 text-center" style="padding-bottom:10px">
                                    	<img src='../images/frontend/OP.JPG' alt='logo' />
                                    </div>
                                    <div class="span8">
                                    	<h2 class="event-header">Orientation PLUS</h2>
                                        <h4>September 1 onward</h4>
                                        <p>Orientation PLUS highlights events and opportunities occuring after the Orientation Week period, but that are still helpful in orienting you to McGill. The full list of Orienation PLUS events will be updated in the next few weeks.</p>
                                    </div>
                                	<?
                                    //printCategoryEvents(org\fos\Event::PLUS_EVENT);
									?>
                                </div>
                            </div>
                        </div>
                        <!-- END Orientation Plus -->
                    </div><!--End Accordion Group-->
                </div>
            </div>
            <?
			} // endif for else: END printing if the rez/faculty form was filled in
			?>
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
						<li><a href="index.php">Home</a></li>
						<li><a href="events.php">Events</a></li>
						<li><a href="contact.php">Contact &amp; Connect</a></li>
						<li><a href="map.php">Map</a></li>
						<li><a href="tips.php">Helpful Hints</a></li>
						<li><a href="faq.php">FAQs</a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>
    
    <!-- Javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-1.9.1.min.js"><\/script>')</script>-->
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/rotate.js"></script>
    <script src="/js/scrollTo.min.js"></script>
    
    <script>
		!function ($) {
			$(function(){
				$('#header').carousel()
			})
		}(window.jQuery)
		
		
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
		
		$('.field-popover').popover();
		
		function checkForm(form) {
			var confirmationText = "";
			
			// get everything from the form
			var livingStyle = form.elements['studentRez'].value;
			var faculty = form.elements['studentFaculty'].value;
			
			// check living style
			if(livingStyle == "") {
				confirmationText += "- living style\n";
			}
			
			// check that a place of origin has been selected
			if (placeOfOrigin == "") {
			   faculty += "- faculty\n";
			}
			
			// continue with processing or return errors
			if(confirmationText == "") {
				return true;
			} else {
				confirmationText = "It looks like the form is missing a few things:\n" + confirmationText;
				alert(confirmationText);
				saving = false;
				return false;
			}
		}
    </script>
</body>
</html>
