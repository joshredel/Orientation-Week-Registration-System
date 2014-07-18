<?
require_once('../functions.php');

session_start();

// see if we have already done step 4
if(isset($_SESSION['step4Complete']) && $_SESSION['step4Complete'] == true) {
	// it has been; send them to step 5
	redirect("/quickreg/step5.php");
}
?>
<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2013 | Registration | Step 1</title>
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
            	<div class="well span6 offset3 text-center sign-up">
                	<form method="POST" class="form-horizontal" action="/actions/quickreg/processStep1.php">
                        <ul class="breadcrumb">
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
                        
                        <h2>Registration<br />Step 1<br />Welcome!</h2>
                        
                        <p>It's finally time!  We are about to start the process of getting you registered for Orientation Week 2013.  Throughout the process, we hope to provide as much help to you as possible.  There will be tips for filling out forms and a <i>Completion Guide</i> to help fill up your first week at McGill.</p><br />
                        
                        <p>The <i>Completion Guide</i> is there to make sure you are able to make the most of Orientation Week 2013.  Students and staff at McGill have been working for almost a full year to make this week the best experience it can be, so we strongly encourage following all of recommendations made by the <i>Completion Guide</i>.</p><br />
                        
                        <p>If at any point along the way you need more help than what is provided, please do not hesitate to <a href="../contact.html">contact us</a> for help!  Starting university can be a whirlwind experience, so we totally understand if something doesn't quite make sense or if you have any question whatsoever.</p><br />
                        
                        <p class="visible-phone visible-tablet"><strong>One last thing:</strong> It looks like you're on a mobile phone or a tablet or your browser window is rather narrow.  Because of the amazing amount of events taking place during Orientation Week, it makes it pretty hard to do registration on a small screen.  We really encourage you to complete this process on a laptop or desktop with your browser window maximized, as we cannot guarantee that everything will work perfectly on mobile and tablet browsers or be pretty to look at on narrow windows.  Proceed at your own risk!</p>
                        
                        <script>
						document.write("<p class=\"lead\">Sound good?  Ready?</p> \
                        \
                        <input type=\"hidden\" name=\"save\" value=\"step1done\"> \
                        <button type=\"submit\" class=\"btn btn-main\">Let's do this!</button>");
						</script>
						<noscript><p class="lead">Your browser does not support JavaScript or you have it disabled.  We rely heavily on it for the registration process, so we require that you have it enabled.</p></noscript>
                        
                        
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