<?
/**
 * Phone menu for participants.
 */
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

// see if the participant is in rez
$inRez = false;
if($caller->livingStyle == "InRez") {
	$inRez = true;
}

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<?
	if(!isset($_SESSION['heardGreeting']) || !$_SESSION['heardGreeting']) {
		$_SESSION['heardGreeting'] = true;
		?>
    	<!--<Say>Hi <?= getDisplayName($caller) ?>, welcome to the Orientation Week Hotline.</Say>-->
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingParticipants.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twiliointest/phone/participant/handleIndex.php">
    	<? if($inRez) { ?>
        <!--
        <Say>
        	Main menu.
            Press 1 to connect to safety services.
            Press 2 to know where you should be right now according to your my Week calendar.
            Press 3 to connect to your orientation leaders.
            Press 4 to connect to the floor fellow on duty in your residence.
            Press 5 to let us know how you or your leaders are froshing.
            Or, Press 6 to leave a message for your frosh event organizers.
            Otherwise, Press 9 to repeat this menu.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/ParticipantMainMenuWithRez.aif</Play>
        <? } else { ?>
        <!--
        <Say>
        	Main menu.
            Press 1 to connect to safety services.
            Press 2 to know where you should be right now according to your my Week calendar.
            Press 3 to connect to your orientation leaders.
            Press 4 to let us know how you or your leaders are froshing.
            Or, Press 5 to leave a message for your frosh event organizers.
            Otherwise, Press 9 to repeat this menu.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/ParticipantMainMenuWithoutRez.aif</Play>
        <? } ?>
    </Gather>
    <Redirect>/twiliointest/phone/participant/</Redirect>
</Response>