<?
/**
 * Phone menu for VP Internals.
 */
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<Response>
	<?
	if(!isset($_SESSION['heardGreeting']) || !$_SESSION['heardGreeting']) {
		$_SESSION['heardGreeting'] = true;
		?>
    	<!--<Say>Hi <?= $caller->displayName ?>, welcome to the Orientation Week Hotline for coords.</Say>-->
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingCoords.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twilio/phone/coord/handleIndex.php">
    	<Say>
        	Main menu.
            Press 1 to send a recorded message to all leaders.
            Press 2 to send a recorded message to all o staff.
            Press 3 to contact other coordinators in your event.
            Press 4 to connect to safety services.
            Press 5 to connect to a floor fellow on duty in a residence.
            Press 6 to leave a message for the S S M U central communications team.
            Otherwise, Press 9 to replay these options.
        </Say>
    </Gather>
    <Redirect>/twilio/phone/coord/</Redirect>
</Response>