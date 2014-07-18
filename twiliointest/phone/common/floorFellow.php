<?
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

// store the digits entered by the user
//$digits = $_REQUEST['Digits'];

// see if the participant is in rez
if($_SESSION['classification'] == org\fos\User::PARTICIPANT) {
	if($caller->livingStyle == "InRez") {
		// now see if they have provided us with a residence
		if($caller->froshAddress != null && $caller->froshAddress != "" && $caller->froshAddress != "" && 
		   $caller->froshAddress != "Varcity515" && $caller->froshAddress != "Greenbriar") {
			   // we have a residence that we can call, so just direct them automatically
			   redirect("/twiliointest/phone/common/connectFloorFellow.php?destination=" . $caller->froshAddress);
		}
	}
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Gather numDigits="1" action="/twiliointest/phone/common/handleFloorFellow.php">
    	<!--
        <Say>
        	Press 1 for McConnell Hall.
            Press 2 for Molson Hall.
            Press 3 for Gardner Hall.
            Press 4 for RVC, the Royal Victoria College.
            Press 5 for New Residence Hall.
            Press 6 for Carrefour Sherbrooke.
            Press 7 for Solin Hall.
            Press 8 for La Citadelle.
            Press 9 for MORE Houses (including University Hall and Pres Rez).
            Otherwise, press 0 to go back to the main menu.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/FloorFellowMenu.aif</Play>
    </Gather>
</Response>