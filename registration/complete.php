<?
require_once('../functions.php');

// get the participant
$participantService = new services\ParticipantService();
$participant = $participantService->getParticipantByRegistrationPassword($_GET['passkey']);

// store the status and error messages, as applicable
$status = $_GET['status'];
$errorMessage = $_GET['error'];
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Complete</title>
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
            	<div class="well span6 offset3 sign-up">
                	<?
					if($status == "completed") {
					?>
                        <h2 class="text-center">Registration<br />Complete!</h2>
                        <p class="lead">We're done.</p>
                        <p>We are excited to have you registered for McGill Orientation Week 2013, and we look forward to seeing you on campus soon.</p>
                        <p>Now it's time to start familarizing yourself with <strong>myWeek</strong>, your Orientation Week portal for the duration of O-Week.  Content will be coming out between your registration and when O-Week actually starts, so check back frequently to see what's new.</p>
                        <p><strong>myWeek</strong> will show you your calendar for the week, send you messages from various event coordinators, and be your all-around digital homebase during O-Week.</p>
                        <br /><br />
                        <a href="/myweek/index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Check it out!</a>
                    <?
					} elseif($status == "cancelled") {
					?>
                    	<h2 class="text-center">Registration<br />Complete<br />(Payment Pending)</h2>
                        <p>It looks like you cancelled payment.  You will need to check your myWeek page for a link to try again, but registration is otherwise complete.</p>
                        <p>We are excited to have you registered for McGill Orientation Week 2013, and we look forward to seeing you on campus soon.</p>
                        <p>Now it's time to start familarizing yourself with <strong>myWeek</strong>, your Orientation Week portal for the duration of O-Week.  Content will be coming out between your registration and when O-Week actually starts, so check back frequently to see what's new.</p>
                        <p><strong>myWeek</strong> will show you your calendar for the week, send you messages from various event coordinators, and be your all-around digital homebase during O-Week.</p>
                        <p>You can also finish making your payments in <strong>myWeek</strong>, so head that way to get your cancelled payment resolved!</p>
                        <br /><br />
                        <a href="/myweek/index.php?passkey=<?= $participant->registrationPassword ?>" class="btn-main-small">Check it out!</a>
                    <?
					} elseif($status == "error") {
					?>
                    	<h2 class="text-center">Registration<br />Complete<br />(With Payment Errors)</h2>
                        <p>Your registration has been completed, but it seems there were some errors during payment.</p>
                        <p>Error: <?= $_GET['error'] ?></p>
                    <?
					} else {
					?>
                    	<p>Whoops... looks like you got a weird error!  Let's just <a href="/index.html">go home</a>.</p>
                    <?
					}
					?>
                </div><!--End Span8-->
            </div><!--End Row-->
	    </div><!--End Container-->
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