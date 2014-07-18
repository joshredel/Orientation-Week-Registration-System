<?
// initialize services
require_once("../../../functions.php");
session_start();

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
    <Gather numDigits="1" action="/twiliointest/phone/common/handleSafety.php">
    	<!--
        <Say>
        	Please select from one of the following services.  If this is an emergency, please hang up and dial 9 1 1.
            Press 1 for Walk Safe.
            Press 2 for Drive Safe.
            Press 3 for the Sexual Assault Centre.
            Press 4 for McGill Security.
            For other questions, Press 5 for McGill Nightline.
            Press 9 to repeat these options.
            Otherwise, press 0 to go back to the main menu.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/SafetyServicesMenu.aif</Play>
    </Gather>
    <Redirect>/twiliointest/phone/common/safety.php</Redirect>
</Response>