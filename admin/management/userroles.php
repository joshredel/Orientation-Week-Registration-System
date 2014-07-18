<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check if they have submitted the role creation form
if(isset($_POST['rolename'])) {
	// get the information from the form
	$roleName = $_POST['rolename'];
	$permissions = $_POST['permissions'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($roleName == "") {
		$errorMessage .= "A name for the user role.<br />";
	}
	if(empty($permissions)) {
		$errorMessage .= "The permissions for the user role.<br />";
	}
	
	// generate the string version of the permissions array
	$stringPermissions = "";
	for($i = 0; $i < count($permissions); $i++) {
		$stringPermissions .= $permissions[$i];
		if($i < count($permissions) - 1) {
			$stringPermissions .= ";";
		}
	}
	
	// check if there are errors
	if($errorMessage == "") {
		// populate a new role
		$role = new org\fos\Role();
		$role->roleName = $roleName;
		$role->permissions = $stringPermissions;
		
		// get the event from the current user
		$role->event = $currentEvent;
		
		// save it to the database
		$roleService->saveRole($role);
		
		// notify of success
		$successMessage = "The role '$roleName' was successfully created.";
	} else {
		// generate the error message
		$errorMessage = "The role could not be created as the form was missing the following:<br />" . $errorMessage;
	}
}

// see if they have asked to delete a user
if(isset($_POST['roleid'])) {
	// ask to delete the user
	$roleService->deleteRoleById($_POST['roleid']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>myWeek Admin | Orientation Week Management</title>
    <link rel="stylesheet" type="text/css" href="/css/layout.css" />
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
    	   <p><a href="/admin/logout.php">logout</a></p>
    	</div>
    	<?
		$file = __FILE__; 
		include("../includes/html/topNav.php");
		include("../includes/html/secondNav.php");
		
	    if($errorMessage != "") {
		    echo("<div class='error'>$errorMessage</div>");
	    }
	    if($successMessage != "") {
	 	    echo("<div class='good'>$successMessage</div>");
	    }
	    ?>
    	<section id='content'>
    	    <article id='roleslist'>
    	        <header><h1>Current User Roles</h1></header>
                <?
			    // get the collection of roles
			    $roles = $currentEvent->roles;
			    
			    // print them
			    foreach($roles as $role) {
				     echo("<form method='post'><input type='hidden' name='roleid' value='" . $role->id . "' />");
				     echo("<input type='submit' value='X' />");
				     echo($role->roleName . " (Assigned to " . count($role->users) . " staff members)</form><br />");
			    }
			    ?>
    	    </article>
    	    <article id='rolescreation'>
    	        <header><h1>Create a User Role</h1></header>
                <form method='post'>
                    <input type='text' placeholder='Role Name' name='rolename' /><br />
                    Permissions:<br />
                    <input type='checkbox' name='permissions[]' value='CreateUsers' /> Can create users<br />
                    <input type='checkbox' name='permissions[]' value='CheckInParticipants' /> Can check in participants<br />
                    <input type='checkbox' name='permissions[]' value='ViewFinancials' /> Can view financial breakdown<br />
                    <input type='checkbox' name='permissions[]' value='ViewParticipants' /> Can view participant information
                    <input class='button' type='submit' />
                </form>
    	    </article>
    	</section>
    	<div id='footer'>
    	 
    	</div>
    </div>
</body>
</html>