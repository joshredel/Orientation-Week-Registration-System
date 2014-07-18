<?
/**
 * Phone menu for the general public.
 */
// initialize services
require_once("../../../functions.php");
session_start();

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<?
	if(!isset($_SESSION['heardGreeting']) || !$_SESSION['heardGreeting']) {
		$_SESSION['heardGreeting'] = true;
		?>
    	<!--<Say>Welcome to the Orientation Week Hotline.</Say>-->
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingCommunity.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twilio/phone/general/handleIndex.php">
    	<!--
        <Say>
        	Main menu.
        	Please select from one of the following options.
            Press 1 to connect with safety services.
            Or, press 2 to let us know how anyone you have seen is or has been froshing.
        </Say>
        -->
        <Play>https://s3.amazonaws.com/myWeek/CompiledMenus/CommunityMainMenu.aif</Play>
    </Gather>
    <Redirect>/twilio/phone/general/</Redirect>
</Response>