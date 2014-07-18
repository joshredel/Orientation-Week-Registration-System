<?
/**
 * Phone menu for leaders.
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
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingLeaders.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twiliointest/phone/leader/handleIndex.php">
    	<Say>
        	Main menu.
        	Please select from one of the following options.
            Press 1 to connect with safety services.
            Press 2 to send a recorded message to the students in your group.
            Press 3 to leave a message for your frosh event organizers.
            Press 4 to know where you should be right now according to your event calendar.
            Press 5 to connect to the floor fellow on duty in a residence.
            Press 6 to let us know how you, your co-leaders, or anyone else are froshing.
            Or, press 9 to repeat these options.
        </Say>
    </Gather>
    <Redirect>/twiliointest/phone/leader/</Redirect>
</Response>