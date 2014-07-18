<?
/**
 * Phone menu for ostaffs.
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
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingOStaff.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twiliointest/phone/ostaff/handleIndex.php">
    	<Say>
        	Main menu.
        	Please select from one of the following options.
            Press 1 to connect with safety services.
            Press 2 to leave a message for your frosh event organizers.
            Press 3 to know where you should be right now according to your event calendar.
            Press 4 to connect to the floor fellow on duty in a residence.
            Press 5 to let us know how you, your co o staff, or anyone else are froshing.
            Or, press 9 to repeat these options.
        </Say>
    </Gather>
    <Redirect>/twiliointest/phone/ostaff/</Redirect>
</Response>