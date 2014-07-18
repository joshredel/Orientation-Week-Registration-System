<?
// connect to the database
$link = mysql_connect('localhost', 'orientation2011', 'regerd8') or die('Could not connect: ' . mysql_error());
mysql_select_db('fos') or die('Could not select database');

// if something was posted, add it to the database
if(isset($_POST['studentid'])) {
	// first check if we actually have such a student in the database
	// compose the select query to look for an existing student
	$checkQuery = "SELECT * FROM GeneralAssembly WHERE StudentID=" . $_POST['studentid'] . " AND ClickerNumber='" . $_POST['clickernumber'] . "'";
	$checkResult = mysql_query($checkQuery) or die('Check query failed: ' . mysql_error());
	
	// see how many results we have (sloppy but it works for this application)
	$foundCount = 0;
	while($entry = mysql_fetch_array($checkResult, MYSQL_ASSOC)) {
		$foundCount++;
	}
	
	// did we find it?
	if($foundCount >= 1) {
		// we found one, so delete it
		// generate the MySQL query
		$insertQuery = "DELETE FROM GeneralAssembly WHERE StudentID=" . $_POST['studentid'];
		
		// generate a query to add a deletion entry into the permanent table
		$insertQuery2 = "INSERT INTO GeneralAssemblyPermanent (StudentID, RegistrationTime) ";
		$insertQuery2 .= "VALUES (-" . $_POST['studentid'] . ", "; // use a negative to denote check out
		$insertQuery2 .= "NOW())";
		
		// run the query
		$insertResult = mysql_query($insertQuery) or die('Query 1 failed: ' . mysql_error());
		$insertResult = mysql_query($insertQuery2) or die('Query 2 failed: ' . mysql_error());
		
		// create a message
		$message = "Student " . $_POST['studentid'] . " was successfully removed.";
	} else {
		// we didn't find one...
		$badMessage = "Student " . $_POST['studentid'] . " with clicker " . $_POST['clickernumber'] . " was never checked in...";
	}
}

// start the query to get all records
$query = 'SELECT * FROM GeneralAssembly';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// prepare stats
$totalStudents = 0;
$totalPress = 0;
$totalVolunteers = 0;

// iterate through all of the entries to get stats
while($entry = mysql_fetch_array($result, MYSQL_ASSOC)) {
	// collect stats
	$totalStudents++;
	
	// check if they are in the press
	if(substr($entry['PassInfo'], 0, -2) == 'PRESS') {
		$totalPress++;
	}
	
	// check if they are a volunteer
	if(substr($entry['PassInfo'], 0, -2) == 'STAFF') {
		$totalStaff++;
	}
	
	// add them to the count for their faculty
	$facultyCount[$entry['Faculty']]++;
}

// clean up
mysql_free_result($result);
mysql_close($link);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>General Assembly - Check Out</title>
    <style>
		table {
			border-collapse: collapse;
		}
		
		table, th, td {
			border: 1px solid black;
		}
		
		th {
			width: 150px;
		}
		
		td {
			text-align: left;
		}
		
		label {
			display:block;
			font-weight:bold;
			text-align:right;
			width:140px;
			float:left;
		}
		
		input {
			float:left;
			font-size:12px;
			padding:4px 2px;
			border:solid 1px;
			width:200px;
			margin:2px 0 20px 10px;
		}
		
		.spacer {
			clear:both;
			height:1px;
		}
		
		button {
			clear: both;
			margin-left:150px;
			width:125px;
			height:31px;
			text-align:center;
			line-height:31px;
			font-size:11px;
			font-weight:bold;
		}
		
		#message {
			color: #090;
		}
		
		#badmessage {
			color: #900;
		}
	</style>
    <script type="text/JavaScript">
		function autoSelectAndFade() {
			// auto select the faculty field
			var textInput = document.getElementById('studentid');
			textInput.focus();
			textInput.select();
			
			// fade out the message field after 3 seconds
			setTimeout("var messageField = document.getElementById('message');messageField.style.display = 'none';", 3000);
			//setTimeout("var messageField = document.getElementById('badmessage');messageField.style.display = 'none';", 3000);
		}
		
		function checkForm(form) {
			// check for an ID
			if(form.studentid.value == "") {
				alert("Please enter a student ID.");
				form.studentid.focus();
				form.studentid.select();
				return false;
			}
			
			// good to go!
			return true;
		}
	</script>
</head>

<body onload="autoSelectAndFade()">
	<h1>General Assembly - Check Out</h1>
    
    <h2>Tools</h2>
    <a href="index.php">Home</a> || 
    <a href="checkin.php">Check In</a> || 
    Check Out
    
    <h2>Remove A Participant</h2>
    <div id="message"><?= $message ?></div><br />
    <div id="badmessage"><?= $badMessage ?></div><br />
    <form method="post" onsubmit="return checkForm(this)">
    	<label for="studentid">Student ID:</label>
        <input name="studentid" id="studentid" type="number" />
        <div class="spacer"></div>
        
        <label for="clickernumber">Clicker number:</label>
        <input name="clickernumber" type="text" value="0" />
        <div class="spacer"></div>
        
        <button type="submit">Remove</button>
		<div class="spacer"></div>
    </form>
    <br />
    <br />
    
    <h2>Current Statistics</h2>
    <strong>Total Students:</strong><br />
	<?= $totalStudents ?><br /><br />
    
    <strong>Quorum Breakdown</strong>
    <table>
    	<tr>
        	<th>Faculty</th>
            <th>Count</th>
        </tr>
        <?
		foreach($facultyCount as $faculty=>$count) {
			echo("<tr><td>" . $faculty . "</td><td>" . $count . "</td></tr>");
		}
		?>
    </table><br />
    
    <strong>Total Press:</strong><br />
	<?= $totalPress ?>
</body>
</html>