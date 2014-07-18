<?
require_once('../functions.php');

// check for a session
checkForKioskSession();

// check if they have submitted the change password form
if(isset($_POST['original'])) {
	// get the information from the form
	$original = $_POST['original'];
	$password1 = $_POST['password1'];
	$password2 = $_POST['password2'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($original == "") {
		$errorMessage .= "Your original password.<br />";
	}
	if($password1 == "" || $password2 == "") {
		$errorMessage .= "A new password.<br />";
	}
	if(sha1($original) != $currentKioskUser->password) {
		$errorMessage .= "A correct original password.<br />";
	}
	if($password1 != $password2) {
		$errorMessage .= "Matching new passwords.<br />";
	}
	
	// check if there are errors
	if($errorMessage == "") {
		// save the user to the database
		$currentKioskUser->password = sha1($password1);
		$currentKioskUser = $userService->saveUser($currentKioskUser);
		
		// notify of success
		$successMessage = "Your password was successfully changes.";
	} else {
		// generate the error message
		$errorMessage = "Your password could not be changed because the following was missing:<br />" . $errorMessage;
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
<body>
	<div id='container'>
    	<div id='header'>
    	   <h1 id="title">myWeek Admin</h1>
    	   <h2 id="caption">Orientation Week Management</h2>
    	   <p><a href="logout.php">logout</a><br />
    	   <a href="changepassword.php">change password</a></p>
    	</div>
    	<?
		$file = __FILE__;
		include("./includes/html/topNav.php");
		
		// show messages if need be
	    if($errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if($successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
		?>
    	<section id='content'>
    	   <article id='changepassword'>
    	        <header><h1>Change Password</h1></header>
                <form method='post'>
                    <label for='original'>Original password:</label>
                    <input type='password' name='original' /><br />
                    
                    <label for='password1'>New password:</label>
                    <input type='password' name='password1' /><br />
                    
                    <label for='password2'>New password (re-enter):</label>
                    <input type='password' name='password2' /><br />
                    
                    <input class='button' type='submit' />
                </form>
    	    </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>