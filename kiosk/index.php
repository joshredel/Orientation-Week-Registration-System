<?
require_once('../functions.php');

// check for a session
checkForKioskSession();

// if we get here, then we should redirect to the overview page
redirect("/kiosk/overview/");
?>