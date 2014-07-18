<?
require_once('../../functions.php');

// check for a session
checkForKioskSession();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../../css/layout.css" />
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
        <script src="js/IE8.js" type="text/javascript"></script>
    <![endif]-->

    <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" media="all" href="css/ie6.css"/>
    <![endif]-->
</head>
<body>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	   <p><a href="../logout.php">logout</a><br />
    	   <a href="../changepassword.php">change password</a></p>
    	</div>
    	<?
		$file = __FILE__;
		include("../includes/html/topNav.php");
		?>
    	<section id='content'>
        	<?
			// check to see if this user has an event associated to them
			if($currentKioskEvent == null) {
				echo("<article><p>Click on the 'Participants' tab to view all students currently registered in the system.<br />Click on the 'Reports' tab to view event capacities.</p></article>");
			} else {
				// the user does not have permissions
				echo("<article><p>Error</p></article>");
			}
			?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>