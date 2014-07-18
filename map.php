<!DOCTYPE html>
<html lang="en"><head>
	<meta charset="utf-8">
	<title>McGill Orientation Week 2014 | Map</title>
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
						<li class="active"><a href="map.php">Map</a></li>
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
				<div class="span10">
					<h2>Orientation Week Map</h2>
                    <p>Check out the map of important places around McGill campus for Orientation Week 2013.  An updated map for 2014 is coming soon!</p>
                    <!--<p>View the <a href="https://maps.google.com/maps/ms?msa=0&amp;msid=205423368461866748963.0004c5225276b0257398b&amp;hl=en&amp;ie=UTF8&amp;t=m&amp;ll=45.505008,-73.578281&amp;spn=0.230951,0.439453&amp;z=16&amp;source=embed" target="_blank">McGill Orientation Week map</a> in its own page.</p>-->
				</div>
			</div>
        </div>
    </section>
    
    <!--Map Section-->
    <section id="map">
	    	<iframe width="100%" height="500" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps/ms?msa=0&amp;msid=205423368461866748963.0004c5225276b0257398b&amp;hl=en&amp;ie=UTF8&amp;t=m&amp;ll=45.505008,-73.578281&amp;spn=0.230951,0.439453&amp;z=16&amp;output=embed"></iframe><br />
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
</body>
</html>