<?
// connect to the database
$link = mysql_connect('localhost', 'orientation2011', 'regerd8') or die('Could not connect: ' . mysql_error());
mysql_select_db('fos') or die('Could not select database');

// start the query to get all records
$query = 'SELECT * FROM GeneralAssembly';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// prepare stats
$totalStudents = 0;
$totalPress = 0;
$totalStaff = 0;

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
    <title>General Assembly - Overview</title>
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
	</style>
    <script type="text/JavaScript">
		function timedRefresh(timeoutPeriod) {
			setTimeout("location.reload(true);", timeoutPeriod);
		}
	</script>
</head>

<body onload="timedRefresh(15000)">
	<h1>General Assembly - Overview</h1>
    
    <h2>Tools</h2>
    Home || 
    <a href="checkin.php">Check In</a> || 
    <a href="checkout.php">Check Out</a>
    
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