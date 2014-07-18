<?
require('../functions.php');

// open the session and then destroy it
checkForKioskSession();
session_destroy();
redirect("/kiosk/login.php");
?>