<?
/**
 * SMS menu for leaders.
 */
// initialize services
require_once("../../../functions.php");
session_start();
$staffService = new services\StaffService();

// get the caller
$caller = $_SESSION['caller'];

// get the message they sent
$command = trim(strtolower($_REQUEST['Body']));

// let's branch based on their last text
$lastText = $caller->lastText;
switch($lastText) {
	case null:
	case "":
		// there is no last text, so we should give them the main menu
		// store that the main menu was the last command given
		$caller->lastText = "menu";
		$staffService->saveStaff($caller);
		
		// now print the menu
		echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		?>		
		<Response>
			<Sms>Main menu. Text "location" to know where you should be or "group" to text your froshies.</Sms>
		</Response>
        <?
		break;
	case "menu":
		switch($command) {
			case "location":
				// tell them where they should be right now
				redirect("location.php");
				break;
			case "group":
				// let them send a text to their leaders
				$caller->lastText = "group";
				$caller->messageBuilder = "";
				$staffService->saveStaff($caller);
				
				// ask them to enter their message
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Please enter your message as you wish to send it.  Message "send" as a separate message when complete or message "delete" as a separate message to cancel.</Sms>
				</Response>
				<?
				break;
			case "menu":
			case "hello":
			case "help":
				// command not recognized
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Main menu. Text "location" to know where you should be or "group" to text your froshies.</Sms>
				</Response>
				<?
				break;
			default:
				// command not recognized
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Command not recognized. Main menu. Text "location" to know where you should be or "group" to text your froshies.</Sms>
				</Response>
				<?
				break;
		}
		break;
	case "group":
		switch($command) {
			case "send":
				// they have finished sending
				redirect("messageParticipants.php");
				break;
			case "delete":
				// stop editing
				$caller->lastText = "menu";
				$staffService->saveStaff($caller);
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Cancelled. Main menu. Text "location" to know where you should be or "group" to text your froshies.</Sms>
				</Response>
				<?
				break;
			default:
				// we are concatenating their message
				$caller->messageBuilder .= addslashes($_REQUEST['Body']);
				$staffService->saveStaff($caller);
				
				// respond blank
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
				</Response>
				<?
				break;
		}
		break;
}
?>


