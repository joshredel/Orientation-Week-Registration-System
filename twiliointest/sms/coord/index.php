<?
/**
 * SMS menu for leaders.
 */
// initialize services
require_once("../../../functions.php");
session_start();
$userService = new services\UserService();

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
		$userService->saveUser($caller);
		
		// now print the menu
		echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		?>		
		<Response>
			<Sms>Main menu. Who do you wish to text? (command to send is in quotations): "leaders", specific "group" leaders, "ostaff", "coords".</Sms>
		</Response>
        <?
		break;
	case "menu":
		switch($command) {
			case "leaders":
			case "ostaff":
			case "coords":
			case "group":
				// let them send a text to their leaders
				$caller->lastText = $command;//"group";
				$caller->messageBuilder = "";
				$userService->saveUser($caller);
				
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
					<Sms>Main menu. Who do you wish to text? (command to send is in quotations): "leaders", specific "group" leaders, "ostaff", "coords".</Sms>
				</Response>
				<?
				break;
			default:
				// command not recognized
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Unknown command. Main menu. Who do you wish to text? (command to send is in quotations): "leaders", specific "group" leaders, "ostaff", "coords".</Sms>
				</Response>
				<?
				break;
		}
		break;
	case "participants":
	case "leaders":
	case "ostaff":
	case "coords":
	case "faculties":
	case "group":
		switch($command) {
			case "send":
				// they have finished sending
				// branch based on command
				switch($lastText) {
					case "leaders":
						// send the message to all leaders
						redirect("messageStaff.php?classification=Leader");
						break;
					case "ostaff":
						// send the message to all o-staff
						redirect("messageStaff.php?classification=O-Staff");
						break;
					case "coords":
						// send the message to the head coordinaotrs
						redirect("messageCoords.php");
						break;
					case "group":
						// ask them to enter the group before sending
						$caller->lastText = "send-group";
						$userService->saveUser($caller);
						echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
						?>		
						<Response>
							<Sms>Please text back the group number you wish to send the message to.</Sms>
						</Response>
						<?
						break;
				}
				break;
			case "delete":
				// stop editing
				$caller->lastText = "menu";
				$userService->saveUser($caller);
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
					<Sms>Cancelled. Main menu. Who do you wish to text? (command to send is in quotations): "leaders", specific "group" leaders, "ostaff", "coords".</Sms>
				</Response>
				<?
				break;
			default:
				// we are concatenating their message
				$caller->messageBuilder .= addslashes($_REQUEST['Body']);
				$userService->saveUser($caller);
				
				// respond blank
				echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
				?>		
				<Response>
				</Response>
				<?
				break;
		}
		break;
	case "send-group":
		// do an inital look to see if we actually have a group for this
		$staffService = new services\StaffService();
		$groupNumber = (int)$command;
		$staffs = $staffService->getStaffInGroup($_SESSION['froshEventId'], $groupNumber);
		if(count($staffs) > 0) {
			redirect("messageGroup.php?group=" . $groupNumber);
		} else {
			// no group found; try again
			$caller->lastText = "menu";
			$userService->saveUser($caller);
			echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
			?>		
			<Response>
            	<Sms>The group number you entered was not recognized.  Please start again from main menu.</Sms>
			</Response>
			<?
		}
		break;
}
?>


