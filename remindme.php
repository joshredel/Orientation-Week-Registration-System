<?
require_once('functions.php');

// send the reminder email if the form was submitted
$formCompleted = false;
if (isset($_POST['save']) && $_POST['save'] != 'remindme') {
	// mark that they have completed the form
	$formCompleted = true;
	
	// get their email
	$contactEmail = $_POST['contactEmail'];
	
	if($contactEmail == null || $contactEmail == "" || !checkEmail($contactEmail)) {
		$errorMessage = "Please enter a valid email address.";
	} else {
		// add it to the database
		$host = "localhost"; 
		$user = "orientation2011"; 
		$pass = "regerd8"; 
		
		// connect to the database
		mysql_connect($host, $user, $pass) or die("Could not connect to the database.");
		mysql_select_db("fos") or die("Could not connect to the FOS database.");
		
		// add the email
		$query = "INSERT INTO ReminderRequests (Email) VALUES('" . $contactEmail . "')";
		$r = mysql_query($query) or die(mysql_error());
		if(!$r) {
			$errorMessage = "We ran into a problem adding your email address.  Please try again later!";
		} else {
			$successMessage = "We have added you to the list to be automatically reminded when registration opens.  See you then!";
		}
		
		// close the database
		mysql_close();
	}
}


?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2014</title>
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
						<li><a href="contact.php">Contact Us</a></li>
						<li><a href="map.php">Map</a></li>
						<li><a href="faq.php">FAQs</a></li>
						<li><a href="parents.php">Parents</a></li>
						<li><a href="register.php"><span class="menu-button"> Register</span></a></li>
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
					<h2>Remind Me When Registration Opens</h2>
					<p class="lead">Registration will open around the end of July.</p>
                    <p>Enter your email below to be automatically reminded as soon as registration officially starts.</p>
                </div>
            </div>
            
			<div class="row-fluid">
            	<?
				// check for a message back from the post
				if($formCompleted && $successMessage != "") {
					echo "<div class=\"alert alert-success\">".$successMessage."</div><div><p><a href=\"index.php\" class=\"btn-main-small\">Back home</a></p></div>";
				} elseif ($formCompleted && $errorMessage != "") {
					echo "<div class=\"alert alert-error\">".$errorMessage."</div>";
				}
				
				if(!($formCompleted && $successMessage != "")) {
				?>
            	<form method="POST">
                	<div class="row-fluid">
                    	<div class="span4">
                        	<div class="control-group">
                            	<div class="controls">
                                	<input type="email" class="input-block-level" name="contactEmail" id="contactEmail" placeholder="Your email" autofocus>
                                    <input type="hidden" name="save" value="remind">
                                	<button type="submit" class="m-btn red-btn">Remind me!</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <? } ?>
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