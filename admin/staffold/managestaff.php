<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get the requested role
$id = $_GET['id'];
$user = $userService->getUser($id);

// redirect if the role doesn't exist
if($user == null) {
	redirect(".");
}

// redirect if they do not have permission to be here or the user is not in the event's staff
if(!$currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF) || 
   !inDoctrineArray($user, $userService->getEventsUsers($currentEvent))) {
	// the user does not have permissions
	redirect(".");
}

// generate a "named" array of role ids to their respective role object
$roles = $currentEvent->roles;
if($roles != null) {
	foreach($roles as $role) {
		$roleDictionary[$role->id] = $role;
	}
}

// find the role that corresponds to the current event
foreach($user->roles as $role) {
	$role->event->load();
	if($role->event->id == $currentEvent->id) {
		$usersRole = $role;
		break;
	}
}

// check if they have submitted the user creation form
if(isset($_POST['username']) && $currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF)) {
	// get the information from the form
	$username = $_POST['username'];
	$role = $roleDictionary[$_POST['role']];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($username == "") {
		$errorMessage .= "A username for the new staff member.<br />";
	}
	if(is_null($role)) {
		$errorMessage .= "A role for the new staff member.<br />";
	}
	
	// check if there are errors
	if($errorMessage == "") {
		// popualte the user
		$user->username = $username;
		
		// only change the role if it has changed
		if($role->id != $usersRole->id) {
			// find the old role and replace it
			$newRoles = new Doctrine\Common\Collections\ArrayCollection();
			foreach($user->roles as $insideRole) {
				if($insideRole->id != $usersRole->id) {
					// keep this role; it's not the one to replace
					$newRoles[] = $insideRole;
				} else {
					// replace this role
					$newRoles[] = $role;
				}
			}
			
			// store the modified roles
			$user->roles = $newRoles;
		}
		
		// save it to the database
		$user = $userService->saveUser($user);
		$usersRole = $role;
		
		// notify of success
		$successMessage = "The staff member '$username' was successfully modified.";
	} else {
		// generate the error message
		$errorMessage = "The staff member could not be modified as the form was missing the following:<br />" . $errorMessage;
	}
}

/*
// see if they have asked to delete a user
if(isset($_POST['userid']) && $currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF)) {
	// ask to delete the user
	$userServ->deleteCostById($_POST['userid']);
}
*/
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
		
		// show messages if need be
	    if($errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if($successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
	    ?>
    	<section id='content'>
       		<article id='usercreation'>
    	        <a href="../staff/">back to Staff</a>
                <br /><br />
                <header><h1>Edit Staff Member Information</h1></header>
                <form method='post'>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' value='<?= $user->username ?>' /><br />
                    
                    <label for='role'>Role:</label>
                    <select name='role'>
                    
                    <?
				    // output the options for the selection of roles
				    foreach($roleDictionary as $role) {
					    echo("<option value='" . $role->id . "'");
						if($role->id == $usersRole->id) {
							echo(" selected");
						}
						echo(">" . $role->roleName . "</option>");
				    }
				    ?>
                    </select><br /><br />
                    
                    <input class='button' type='submit' />
                </form>
    	    </article>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>