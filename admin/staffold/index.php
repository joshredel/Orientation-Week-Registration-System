<?
require_once('../../functions.php');

// check for a session
checkForSession();

// generate a "named" array of role ids to their respective role object
//$roles = $roleService->getRoles();
$roles = $currentEvent->roles;
if($roles != null) {
	foreach($roles as $role) {
		$roleDictionary[$role->id] = $role;
	}
}

// check if they have submitted the user creation form
if(isset($_POST['username']) && $currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF)) {
	// get the information from the form
	$username = $_POST['username'];
	$password = sha1($_POST['password']);
	$role = $roleDictionary[$_POST['role']];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($username == "") {
		$errorMessage .= "A username for the new staff member.<br />";
	}
	if($password == "") {
		$errorMessage .= "A password for the new staff member.<br />";
	}
	if(is_null($role)) {
		$errorMessage .= "A role for the new staff member.<br />";
	}
	
	// check if there are errors
	if($errorMessage == "") {
		// popualte a new user
		$user = new org\fos\User();
		$user->username = $username;
		$user->password = $password;
		$user->roles[] = $role;
		
		// save it to the database
		$user = $userService->saveUser($user);
		
		// notify of success
		$successMessage = "The user '$username' was successfully created.";
	} else {
		// generate the error message
		$errorMessage = "The staff member could not be created as the form was missing the following:<br />" . $errorMessage;
	}
}

// see if they have asked to delete a user
if(isset($_POST['userid']) && $currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF)) {
	// ask to delete the user
	$userService->deleteUserById($_POST['userid']);
}
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
       		<?
			// check to see if this user has an event associated to them
			if($currentEvent == null) {
				echo("<article><p>Your user account does not have an event associated to it.</p></article>");
			} elseif(!$currentRole->hasPermission(org\fos\Role::$VIEW_STAFF)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
    	    <article id='userslist'>
    	        <header><h1>Current Staff</h1></header>
                <table class='innerTable'>
                    <tr>
                        <th>Username</th>
                        <th>Staff Role</th>
                        <th>Manage</th>
                        <th>Delete</th>
                    </tr>
					<?
                    // get the collection of roles
                    $users = $userService->getEventsUsers($currentEvent);
                    
                    // print them
                    foreach($users as $user) {
                        // find the role that corresponds to the current event
                        foreach($user->roles as $role) {
                            if($role->event->id == $currentEvent->id) {
                                $roleName = $role->roleName;
                                break;
                            }
                        }
						
						echo("<tr><td>" . $user->username . "</td><td>" . $roleName . "</td>");
						echo("<td><a href='managestaff.php?id=" . $user->id . "'>(view / edit)</a></td>");
						echo("<td><form style='width:50%;' method='post'><input type='hidden' name='userid' value='" . $user->id . "' />");
				    	echo("<input type='submit' value='X' /></form></td></tr>");
                    }
                    ?>
                </table>
    	    </article>
            <?
			if($currentRole->hasPermission(org\fos\Role::$MANAGE_STAFF)) {
			?>
    	    <article id='usercreation'>
    	        <header><h1>Add a Staff Member</h1></header>
                <form method='post'>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' /><br />
                    
                    <label for='password'>Default Password:</label>
                    <input type='password' name='password' /><br />
                    
                    <label for='role'>Role:</label>
                    <select name='role'>
                    <?
				    // output the options for the selection of roles
				    foreach($roleDictionary as $role) {
					    echo("<option value='" . $role->id . "'>" . $role->roleName . "</option>");
				    }
				    ?>
                    </select><br /><br />
                    
                    <input class='button' type='submit' />
                </form>
    	    </article>
            <?
			} // end check for permissions to manage staff
			} // end check for the user's current event
            ?>
    	</section>
    	<div id='footer'>
    	
    	</div>
    </div>
</body>
</html>