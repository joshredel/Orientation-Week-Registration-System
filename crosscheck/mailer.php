<?
require_once('../functions.php');

// check for a session
checkForSession();

if($currentEvent != null) {
	redirect("/");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Evening Mailer</title>
<script type="text/javascript" src="/js/jquery.js"></script>
</head>

<body>
<p>Starting on ID: <span id="startingOn"></span></p>
<p>Currently sending to: <span id="sendingTo"></span></p>
<p>Status: <span id="status">Idle</span></p>
<br />
<br />
<button onclick="sendMassMail()">Start Mailing</button>

<script>
	var startingId = 1;
	var currentIdMailing;
	var totalMailed = 0;
	var sprintMailed;
	
	// starts sending a mass mailing
	function sendMassMail() {
		// reset the counter
		$("#startingOn").html(startingId);
		currentIdMailing = startingId;
		sprintMailed = 0;
		
		// set the current status
		$("#status").html("Sending...");
		
		// send the first message
		sendNextMessage(startingId);
	}
	
	
	function sendNextMessage(idToMail) {
		// show the status
		$("#sendingTo").html("[PID " + idToMail + "]");
		
		// make the post
		$.post("eveningReminder.php", { api: "29ed05022d4bfb3ae3738b302bbea19b872870a5", 
										nextId: idToMail},
				function(data){
					if(data == "FINISHED") {
						// the mailing finished
						totalMailed++;
						$("#status").html("Finished! Sent to " + totalMailed);
					} else if(data == "READYFORNEXT") {
						// it's ready for the next id!
						currentIdMailing++;
						sendNextMessage(currentIdMailing);
					} else if(data == "SENTREADYFORNEXT") {
						// it's ready for the next id!
						totalMailed++;
						sprintMailed++;
						if(sprintMailed >= 1000) {
							$("#status").html("Let's take a break! Sent to " + totalMailed);
							startingId = currentIdMailing;
						} else {
							currentIdMailing++;
							sendNextMessage(currentIdMailing);
						}
					} else {
						// unknown response from the sender
						$("#status").html("Unknown response... stopped at " + idToMail + " (Raw data: [" + data + "]");
					}
				}
		);
	}
</script>
</body>
</html>