<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2014 | Contact &amp; Connect</title>
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
						<li><a href="events.php">Events</a></li>
						<li class="active"><a href="contact.php">Contact Us</a></li>
						<li><a href="map.php">Map</a></li>
						<li><a href="faq.php">FAQs</a></li>
						<li><a href="parents.php">Parents</a></li>
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
					<h2>Contact the Orientation Week Team</h2>
					<p class="lead">We're always happy to help!</p>
                </div>
            </div>
			<div class="row-fluid">
				<div class="span4">
	                <img src="/images/frontend/contact.jpg" class="thumbnail" alt="friendly face"><br /><br />
                </div>
                
                <div class="span8">
					<p>The Orientation Week Communications team is here to help you make the best of your very first week at McGill University.  We want to make sure that the time leading up to and including Orientation Week are as easy for you as possible, so please make sure to let us know if you have any questions or conerns, and we will do our very best to answer (or at least point you in the right direction).</p>
                    <p>For questions regarding Orientation Week or the website, please use the form below.</p>
                    <p>For questions regarding a specific event or Frosh, please contact the organizer using the email provided in the <a href="./events.php">event description</a>.</p>
                </div>
            </div>
            
			<div class="row-fluid">
            	<?
				// check for a message back from the post
				if (isset($_GET['success'])) {
					echo "<div class=\"alert alert-success\">".$_GET['success']."</div>";
				} elseif (isset($_GET['error'])) {
					echo "<div class=\"alert alert-error\">".$_GET['error']."</div>";
				}
				?>
            	<form method="POST" action="/actions/contactFormSubmission.php">
                	<div class="row-fluid">
                    	<div class="span4">
                        	<div class="control-group">
                            	<label class="control-label" for="questiontype"><strong>Some information about you:</strong></label>
                            	<div class="controls">
                                	<input type="text" class="input-block-level" name="contactname" id="contactname" placeholder="Your name">
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <div class="controls">
                                    <input type="number" class="input-block-level" name="studentid" id="studentid" placeholder="Your student ID" maxlength="9">
                                </div>
                            </div>
                            
                            <div class="control-group">
                            	<label class="control-label" for="questiontype">Faculty of study:</label>
                                <div class="controls">
                                    <select class="input-block-level" id="faculty" name="faculty">
                                        <option value='AG'>Faculty of Agriculture / Environmental Science</option>
                                        <option value='AR'>Faculty of Arts</option>
                                        <option value='AS'>Faculty of Arts & Science</option>
                                        <option value='DE'>Faculty of Dentistry</option>
                                        <option value='ED'>Faculty of Education</option>
                                        <option value='EN'>Faculty of Engineering</option>
                                        <option value='LW'>Faculty of Law</option>
                                        <option value='MD'>Faculty of Medicine</option>
                                        <option value='RS'>Faculty of Religious Studies</option>
                                        <option value='SC'>Faculty of Science</option>
                                        <option value='MG'>Desautels Faculty of Management</option>
                                        <option value='MU'>Schulich School of Music</option>
                                        <option value='EN'>School of Architecture</option>
                                        <option value='NU'>School of Nursing</option>
                                        <option value='PO'>School of Physical & Occupational Therapy</option>
                                        <option value='AR'>School of Social Work</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="control-group">
                            	<div class="controls">
                                	<input type="email" class="input-block-level" name="contactemail" id="contactemail" placeholder="Your email address">
                                </div>
                            </div>
                        </div>
                        
                        <div class="span8">
                        	<div class="control-group">
                                <label class="control-label" for="questiontype"><strong>Tell us about your question or comment:</strong></label>
                                <div class="controls">
                                    <select class="input-block-level" id="questiontype" name="questiontype">
                                        <option value="">Select category of question...</option>
                                        <option value='Discover McGill'>Discover McGill</option>
                                        <option value='Faculty Frosh'>Faculty Frosh</option>
                                        <option value='Non-Faculty Frosh'>Non-Faculty Frosh</option>
                                        <option value='Event'>Tech Support</option>
                                        <option value='Website'>General Query</option>
                                    </select>
                                </div>
                            </div>
                            
                        	<div class="control-group"> 
                            	<div class="controls">
                                	<textarea name="question" id="question" class="input-block-level" rows="8" placeholder="The message you want to send to the team."></textarea>
                                </div>
                            </div>
                            
                            <div class="text-right">
                            	<input type="hidden" name="save" value="contact">
                                <button type="submit" class="m-btn" style="width: 200px;">Submit</button>
                            </div>
                        </div>
                    </div><!--End Row-->
                </form><br /><br />
            </div>
            
            <div class="row-fluid">
				<div class="span8">
					<p>Any questions regarding first-year transition issues (e.g. course registration, academics, McGill/Montreal essentials...) should be directed to the First-Year Office.</p>
                    <ul>
                    	<li><p>By phone (514-398-6913)</p></li>
                        <li><p>By email (<a href="mailto:firstyear@mcgill.ca">firstyear@mcgill.ca</a>)</p></li>
                    </li>
                    <br />
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
						<li><a href="index.php">Home</a></li>
						<li><a href="events.php">Events</a></li>
						<li><a href="contact.php">Contact Us</a></li>
						<li><a href="map.php">Map</a></li>
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
    
    <script>
      !function ($) {
        $(function(){
          $('#header').carousel()
        })
      }(window.jQuery)
    </script>
</body>
</html>