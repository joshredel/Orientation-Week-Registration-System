<?
require('../functions.php');

// open the session and then destroy it
checkForSession();
session_destroy();
redirect("/admin/login.php");
?>