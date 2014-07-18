<?
require_once('../../functions.php');

// check for a session
checkForSession();

// get the requested role
$id = $_GET['id'];
$role = $roleService->getRole($id);

// redirect if the role doesn't exist
if($role == null) {
	redirect(".");
}

// redirect if they do not have permission to be here or the role is not in the event's roles
if(!$currentRole->hasPermission(org\fos\Role::$EDIT_STAFF_ROLES) || 
   !inDoctrineArray($role, $currentEvent->roles)) {
	// the user does not have permissions
	redirect(".");
}

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
		// populate the role
		$role->roleName = $roleName;
		$role->permissions = $stringPermissions;
		
		// get the event from the current user
		$role->event = $currentEvent;
		
		// save it to the database
		$roleService->saveRole($role);
		
		// notify of success
		$successMessage = "The role '$roleName' was successfully modified.";
	} else {
		// generate the error message
		$errorMessage = "The role could not be modified as the form was missing the following:<br />" . $errorMessage;
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

// create an array of permissions for this role
$permissions = explode(";", $role->permissions);
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
       		<article id='roleinfo'>
    	       <a href="../management/staffroles.php">back to Staff Roles</a>
               <br /><br />
               <header><h1>Edit Role Information</h1></header>
                <form method='post'>
                	<label for='rolename'>Role Name:</label>
                    <input type='text' name='rolename' value='<?= $role->roleName ?>' /><br /><br />
                    
                    Permissions:<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$ALL_PERMISSIONS ?>' <?= in_array(org\fos\Role::$ALL_PERMISSIONS, $permissions) == 1 ? 'checked' : '' ?> /> Master permissions<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_FINANCIAL_OVERVIEW ?>' <?= in_array(org\fos\Role::$VIEW_FINANCIAL_OVERVIEW, $permissions) == 1 ? 'checked' : '' ?> /> View financial reports<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_PARTICIPANTS ?>' <?= in_array(org\fos\Role::$VIEW_PARTICIPANTS, $permissions) == 1 ? 'checked' : '' ?> /> View participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_PARTICIPANTS ?>' <?= in_array(org\fos\Role::$EDIT_PARTICIPANTS, $permissions) == 1 ? 'checked' : '' ?> /> Edit participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$CHECK_IN_PARTICIPANTS ?>' <?= in_array(org\fos\Role::$CHECK_IN_PARTICIPANTS, $permissions) == 1 ? 'checked' : '' ?> /> Check in participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$DELETE_PARTICIPANTS ?>' <?= in_array(org\fos\Role::$DELETE_PARTICIPANTS, $permissions) == 1 ? 'checked' : '' ?> /> Delete participants<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_STAFF ?>' <?= in_array(org\fos\Role::$VIEW_STAFF, $permissions) == 1 ? 'checked' : '' ?> /> View staff<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$MANAGE_STAFF ?>' <?= in_array(org\fos\Role::$MANAGE_STAFF, $permissions) == 1 ? 'checked' : '' ?> /> Manage staff<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$VIEW_REPORTS ?>' <?= in_array(org\fos\Role::$VIEW_REPORTS, $permissions) == 1 ? 'checked' : '' ?> /> View reports<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_EVENT ?>' <?= in_array(org\fos\Role::$EDIT_EVENT, $permissions) == 1 ? 'checked' : '' ?> /> Edit event<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_PAYSCHEDULE ?>' <?= in_array(org\fos\Role::$EDIT_PAYSCHEDULE, $permissions) == 1 ? 'checked' : '' ?> /> Edit payment schedule<br />
                    <input type='checkbox' name='permissions[]' value='<?= org\fos\Role::$EDIT_STAFF_ROLES ?>' <?= in_array(org\fos\Role::$EDIT_STAFF_ROLES, $permissions) == 1 ? 'checked' : '' ?> /> Edit staff roles<br /><br />
                    
                    <input class='button' type='submit' />
                </form>
    	    </article>
    	    <article id='rolestaff'>
    	        <header><h1>Staff Members</h1></header>
                <table class='innerTable'>
                    <tr>
                        <th>Role Name</th>
                        <th>Manage</th>
                        <th>Delete</th>
                    </tr>
					<?
                    // get the collection of roles
                    $users = $role->users;
                    
                    // print them
                    foreach($users as $user) {
                        echo("<tr><td>" . $user->username . "</td>");
                        echo("<td><a href='managestaff.php?id=" . $user->id . "'>view / edit</a></td>");
                        echo("<td><form style='width:50%;' method='post'><input type='hidden' name='userid' value='" . $user->id . "' />");
                        echo("<input type='submit' value='X' /></form></td></tr>");
                    }
                    ?>
                </table>
    	    </article>
    	</section>
    	<div id='footer'>
    	 
    	</div>
    </div>
</body>
</html>