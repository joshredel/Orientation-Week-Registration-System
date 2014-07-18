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
    	<!--<Say>Hi <?= $caller->displayName ?>, welcome to the Orientation Week Hotline for VP Internals.</Say>-->
        <Play>https://s3.amazonaws.com/myWeek/Greetings/GreetingHeadCoords.aif</Play>
		<?
	}
	?>
    <Gather numDigits="1" action="/twiliointest/phone/headcoord/handleIndex.php">
    	<Say>
        	Main menu.
            Press 1 to send a recorded message to all participants.
            Press 2 to send a recorded message to all leaders.
            Press 3 to send a recorded message to all o staff.
            Press 4 to contact other coordinators in your event.
            Press 5 to contact all head coordinators and VP Internals of other froshes.
            Press 6 to connect to safety services.
            Press 7 to connect to a floor fellow on duty in a residence.
            Press 8 to leave a message for the S S M U central communications team.
            Otherwise, Press 0 to replay these options.
        </Say>
    </Gather>
    <Redirect>/twiliointest/phone/headcoord/</Redirect>
</Response>