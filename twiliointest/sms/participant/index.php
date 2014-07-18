<?
/**
 * SMS menu for participants.
 */
// initialize services
require_once("../../../functions.php");
session_start();
$participantService = new services\ParticipantService();

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
		$participantService->saveParticipant($caller);
		
		// now print the menu
		echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		?>		
		<Response>
			<Sms>Main menu. Text "location" to know where you should be or "leaders" to text your frosh leaders.</Sms>
		</Response>
        <?
		break;
	case "menu":
		switch($command) {
			case "location":
				// tell them where they should be right now
				redirect("location.php");
				break;
			case "leaders":
				// let them send a text to their leaders
				// but first make sure they actually have a group
				if($caller->groupNumber != null && $caller->groupNumber != "") {
					$caller->lastText = "leaders";
					$caller->messageBuilder = "";
					$participantService->saveParticipant($caller);
					
					// ask them to enter their message
					echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
					?>		
					<Response>
						<Sms>Please enter your message as you wish to send it.  Message "send" as a separate message when complete or message "delete" as a separate message to cancel.</Sms>
					</Response>
					<?
				} else {
					echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
					?>		
					<Response>
						<Sms>You are not in a frosh or are not recorded in a group.  Ask your leaders to add you on their myWeek page.</Sms>
					</Response>
					<?
				}
				break;
			case "menu":
			case "hello":
			case "help":
				// command not recognized
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Main menu. Text "location" to know where you should be or "leaders" to text your frosh leaders.</Sms>
				</Response>
				<?
				break;
			default:
				// command not recognized
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Command not recognized. Main menu. Text "location" to know where you should be or "leaders" to text your frosh leaders.</Sms>
				</Response>
				<?
				break;
		}
		break;
	case "leaders":
		switch($command) {
			case "send":
				// they have finished sending
				redirect("messageLeaders.php");
				break;
			case "delete":
				// stop editing
				$caller->lastText = "menu";
				$participantService->saveParticipant($caller);
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Cancelled. Main menu. Text "location" to know where you should be or "leaders" to text your frosh leaders.</Sms>
				</Response>
				<?
				break;
			default:
				// we are concatenating their message
				$caller->messageBuilder .= addslashes($_REQUEST['Body']);
				$participantService->saveParticipant($caller);
				
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


