<?
/**
 * Calls a safety service based on the destination sent.
 */
session_start();

// get the caller
$caller = $_SESSION['caller'];

/*
Press 1 for McConnell Hall.
Press 2 for Molson Hall.
Press 3 for Gardner Hall.
Press 4 for RVC, the Royal Victoria College.
Press 5 for New Residence Hall.
Press 6 for Carrefour Sherbrooke.
Press 7 for Solin Hall.
Press 8 for La Citadella.
Press 9 for MORE Houses (including University Hall and Pres Rez).
Otherwise, press 0 to go back to the main menu.
*/

//choose the safety service
$numberToCall = "";
$rezName = "";
switch($_REQUEST['destination']) {
	case "McConnell":
		$numberToCall = "+15142206606"; //"+15143861294";
		$rezName = "McConnell Hall";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/McConnell.aif";
		break;
	case "Molson":
		$numberToCall = "+15142206606"; //"+15143861277";
		$rezName = "Molson Hall";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/Molson.aif";
		break;
	case "Gardner":
		$numberToCall = "+15142206606"; //"+15143862639";
		$rezName = "RVC Hall";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/RVC.aif";
		break;
	case "RVC":
		$numberToCall = "+15142206606"; //"+15148696535";
		$rezName = "RVC, the Royal Victoria College";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/RVC.aif";
		break;
	case "NewRez":
		$numberToCall = "+15142206606"; //"+15144672382";
		$rezName = "New Residence Hall";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/NewRez.aif";
		break;
	case "Carrefour":
		$numberToCall = "+15142206606"; //"+15142322265";
		$rezName = "Carrefour Sherbrooke";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/Carrefour.aif";
		break;
	case "Solin":
		$numberToCall = "+15142206606"; //"+15149452309";
		$rezName = "Solin Hall";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/Solin.aif";
		break;
	case "Citadelle":
		$numberToCall = "+15142206606"; //"+15143470280";
		$rezName = "La Citadelle";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/Citadelle.aif";
		break;
	case "MORE":
		$numberToCall = "+15142206606"; //"+15145919057";
		$rezName = "MORE Houses (including University Hall and Pres Rez)";
		$playName = "https://s3.amazonaws.com/myWeek/Menus/MORE.aif";
		break;
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<!---
	<Say>
    	You are now being connected to the floor fellow on duty in <?= $rezName ?>.  Your phone will ring as if making a normal call.
    </Say>
    -->
    <Play>https://s3.amazonaws.com/myWeek/Menus/ConnectToFloorFellowIn.aif</Play>
    <Play><?= $playName ?></Play>
    <Play>https://s3.amazonaws.com/myWeek/Commands/YourPhoneWillRing.aif</Play>
	<Dial callerId="+15149000125">
    	<Number url="/twiliointest/phone/common/connectWait.php">
        	<?= $numberToCall ?>
        </Number>
    </Dial>
</Response>