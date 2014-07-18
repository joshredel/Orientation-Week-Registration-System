<?
require_once('../functions.php');

// check for a session
checkForSession();

// if we get here, then we should redirect to the overview page
redirect("/admin/overview/");
?>