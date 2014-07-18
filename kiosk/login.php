<?
require_once('../functions.php');

// see if the user entered information
if(isset($_POST['user'])) {
	// get the username and password from the post
	$username = $_POST['user'];
	$password = sha1($_POST['pass']);
	
	// attempt to login
	$userService = new services\UserService();
	$currentKioskUser = $userService->attemptLogin($username, $password);
	
	// check if the login was successful
	if($currentKioskUser) {
		// start the session and store the current user in a session variable
		session_start();
		$_SESSION['currentKioskUser'] = $currentKioskUser;
		//$_SESSION['currentRole'] = $currentUser->roles[0];
		
		// redirect to the admin main page
		redirect("/kiosk/overview/");
	} else {
		// the login was incorrect; show a message
		$errorMessage = "The username or password was incorrect.  Please try again.";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="../css/layout.css" />
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
<body onLoad='document.login.user.focus();'>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	</div>
    	<?
		$file = __FILE__; 
		include("./includes/html/topNav.php");
		
		// show messages if need be
	    if($errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
		?>
        <section id='content'>
            <div id='loginPane'>
               <div id='loginHeader'>
                   <h1>Info Kiosk Login</h1>
               </div>
               <form name='login' method='post'>
                   <input type='text' placeholder='Username' name='user' />
                   <input type='password' placeholder='Password' name='pass' />
                   <input class='button' type='submit' /> 
               </form>
            </div>
        </section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>