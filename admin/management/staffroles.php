<?
require_once('../../functions.php');

// check for a session
checkForSession();

// check if they have submitted the role creation form
if(isset($_POST['rolename']) && $currentRole->hasPermission(org\fos\Role::$EDIT_STAFF_ROLES)) {
	// get the information from the form
	$roleName = $_POST['rolename'];
	$permissions = $_POST['permissions'];
	
	// make sure all of the information has been submitted
	$errorMessage = "";
	if($roleName == "") {
		$errorMessage .= "A name for the staff role.<br />";
	}
	if(empty($permissions)) {
		$errorMessage .= "The permissions for the staff role.<br />";
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
if(isset($_POST['roleid']) && $currentRole->hasPermission(org\fos\Role::$EDIT_STAFF_ROLES)) {
	// get the role trying to be deleted
	$desiredRole = $roleService->getRole($_POST['roleid']);
	
	// only delete if the role has no users
	if(count($desiredRole->users) > 0) {
		// cannot delete; still has users attached
		$errorMessage = "The role could not be deleted because it has staff attached to it.<br />Please delete the staff or change their staff role first.";
	} else {
		// delete the user
		$roleService->deleteRoleById($_POST['roleid']);
		$successMessage = "The role was successfully deleted.";
	}
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
		include("../includes/html/secondNav.php");
		
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
			} elseif(!$currentRole->hasPermission(org\fos\Role::$EDIT_STAFF_ROLES)) {
				// the user does not have permissions
				echo("<article><p>Your user account does not have privilege to view this page.</p></article>");
			} else {
			?>
    	    <article id='roleslist'>
    	        <header><h1>Current Staff Roles</h1></header>
                <table class='innerTable'>
                    <tr>
                        <th>Role Name</th>
                        <th>No. Members</th>
                        <th>Manage</th>
                        <th>Delete</th>
                    </tr>
					<?
                    // get the collection of roles
                    $roles = $currentEvent->roles;
                    
                    // print them
                    foreach($roles as $role) {
                        echo("<tr><td>" . $role->roleName . "</td><td>" . count($role->users) . "</td>");
						echo("<td><a href='managestaffrole.php?id=" . $role->id . "'>(view / edit)</a></td>");
						echo("<td><form style='width:50%;' method='post'><input type='hidden' name='roleid' value='" . $role->id . "' />");
				    	echo("<input type='submit' value='X' /></form></td></tr>");
                    }
                    ?>
                </table>
    	    </article>
    	    <article id='rolescreation'>
    	        <header><h1>Create a Staff Role</h1></header>
                <form method='post'>
                	<label for='rolename'>Role Name:</label>
                    <input type='text' name='rolename' /><br /><br />
                    
                    Permissions:<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$ALL_PERMISSIONS ?>' /> Master permissions<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_FINANCIAL_OVERVIEW ?>' /> View financial reports<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_PARTICIPANTS ?>' /> View participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_PARTICIPANTS ?>' /> Edit participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$CHECK_IN_PARTICIPANTS ?>' /> Check in participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$DELETE_PARTICIPANTS ?>' /> Delete participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_STAFF ?>' /> View staff<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$MANAGE_STAFF ?>' /> Manage staff<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_REPORTS ?>' /> View reports<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_EVENT ?>' /> Edit event<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_PAYSCHEDULE ?>' /> Edit payment schedule<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_STAFF_ROLES ?>' /> Edit staff roles<br /><br />
                    
                    <input class='button' type='submit' />
                </form>
    	    </article>
            <?
			} // end check for the user's permissions
			?>
    	</section>
    	<div id='footer'>
    	 
    	</div>
    </div>
</body>
</html>