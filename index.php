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
						<li class="active"><a href="index.php">Home</a></li>
						<li><a href="events.php">Events</a></li>
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
	
	<!-- Carousel -->
	<div id="header" class="carousel slide animated fadeIn">
		<div class="carousel-inner">
			<div class="item one active">
				<div class="container animated fadeInUp">
					<div class="carousel-caption">
						<h1><i>Welcome&nbsp;&nbsp;to&nbsp;&nbsp;O-Week 2014</i></h1>
						<h2><i>A new student's guide to McGill</i></h2>
					</div>
				</div>
			</div>
		</div>
		<!--<a class="left carousel-control hidden-phone" href="#header" data-slide="prev"><i class="icon-angle-left"></i></a>-->
		<!--<a class="right carousel-control hidden-phone" href="#header" data-slide="next"><i class="icon-angle-right"></i></a>-->
	</div><!-- /.carousel -->
    
    
	<!--Large Well-->
    <!--
	<section id="well">
		<div class="well well-large">
			<div class="container">
				<div class="row-fluid">
					<div class="span12 text-center">
						<h3><strong>IT'S TIME. GET REGISTERED.</strong></h3>
                        <br>
                        <p><a href="/registration/" class="btn-main">Start Registration</a></p>
					</div>
				</div>
			</div>
		</div>
	</section>
    -->
    <section id="well">
		<div class="well well-large">
			<div class="container">
				<div class="row-fluid">
					<div class="span12 text-center">
						<h3><strong>REGISTRATION STARTS THE END OF JULY. BE THE FIRST TO KNOW WHEN IT STARTS.</strong></h3>
                        <br>
                        <p><a href="/remindme.php" class="btn-main">Sign Up for a Reminder Email</a></p>
					</div>
				</div>
			</div>
		</div>
	</section>
    
    
	<!--Main Content-->    
	<section id="content">
		<div class="container">
			<div class="row-fluid">
				<div class="span8">
					<h2>About Orientation Week</h2>
					<p class="lead">McGill Orientation Week provides newly arrived McGill students with an opportunity to become acquainted with university life and create unforgettable memories!</p>
                    <p>McGill provides students with a wide array of activities both specific to the individual faculty and the larger undergraduate community as a whole. Beyond this, McGill and its associated organizations offer an expanded selection of unique events and activities that suit whatever your interests may be. All events are optional and all are a phenomenal way to get to know McGill.</p>
                    <p>No matter what kind of student you are, Orientation Week is your best opportunity to begin your life here at McGill!</p>
				</div>
                <div class="span4">
	            	<iframe src="https://www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fmcgillorientation&amp;width=292&amp;height=558&amp;show_faces=true&amp;colorscheme=light&amp;stream=true&amp;show_border=false&amp;header=false" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100%; height:558px;" allowTransparency="true"></iframe>
                </div>
			</div>
        </div>
    </section>
    
    
	<!--Message Section-->
	<section id="message">
		<div class="container">
			<div class="row-fluid">
				<div id="videocontainer" class="span12">
                    <h2 class="border">Watch our Welcome Video from 2013</h2>
                    <!--<iframe width="560" height="315" src="http://www.youtube.com/embed/jd2vvMfneis?wmode=transparent" frameborder="0" allowfullscreen></iframe>-->
                    <!--<iframe width="1366" height="768" src="http://www.youtube.com/embed/v4Mb5qWGCbw?wmode=transparent&rel=0" frameborder="0" allowfullscreen></iframe>-->
                    <iframe width="1366" height="768" src="http://www.youtube.com/embed/h82BBbXWuRY?wmode=transparent&rel=0" frameborder="0" allowfullscreen></iframe>
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
						<!--<li><a href="tips.php">Helpful Hints</a></li>-->
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
    
    <script>
		// By Chris Coyier & tweaked by Mathias Bynens
		$(function() {
			// Find all YouTube videos
			var $allVideos = $("iframe[src^='http://www.youtube.com']"),
				// The element that is fluid width
				$fluidEl = $("#videocontainer");
			
			// Figure out and save aspect ratio for each video
			$allVideos.each(function() {
				$(this)
					.data('aspectRatio', this.height / this.width)
					
					// and remove the hard coded width/height
					.removeAttr('height')
					.removeAttr('width');
			});
			
			// When the window is resized
			// (You'll probably want to debounce this)
			$(window).resize(function() {
				var newWidth = $fluidEl.width();
				// Resize all videos according to their own aspect ratio
				$allVideos.each(function() {
					
					var $el = $(this);
					$el
						.width(newWidth)
						.height(newWidth * $el.data('aspectRatio'));
					
				});
				
			// Kick off one resize to fix all videos on page load
			}).resize();
		});
	</script>
</body>
</html>