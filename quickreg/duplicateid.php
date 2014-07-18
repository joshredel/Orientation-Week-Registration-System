<?
require_once('../functions.php');

session_start();

$participantService = new services\ParticipantService();

// get the participant that has a duplicate ID
$participant = $participantService->getParticipantByStudentId($_SESSION['studentId']);

// make sure we actually have a duplicate
if($participant == null) {
	// send them to step 4, which will back step them if they haven't actually gotten that far
	redirect("/quickreg/step4.php");
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 4</title>
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
            	<div class="well span8 offset2 sign-up">
                	<form method="POST" class="form-horizontal" action="/actions/quickreg/processDuplicateId.php">
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
                        
                        <h2 class="text-center">Registration<br />Duplicate Student ID</h2>
                        
                        <p>It seems you have entered a student ID number that has already been registered in the system.</p>
                        
                        <p>If you are sure you entered, we can send you another email containing your myWeek link.</p>
                        <input type="hidden" name="save" value="duplicateId">
                        <button type="submit" class="btn btn-main-small">Send Me A New Link</button><br /><br />
                        
                        <p>Otherwise, please make sure to return to <a href="/quickreg/step2.php">Step 2</a> to double check the student ID number you entered.</p>
                    </form>
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