<?
// initialize services
require_once("../../../functions.php");
session_start();

// get the caller
$caller = $_SESSION['caller'];

echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
?>

<?
if($_SESSION['froshEventId'] != null) {
	if($caller->groupNumber != null && $caller->groupNumber != "") {
		?>
        <Response>
        	<Play>https://s3.amazonaws.com/myWeek/Menus/ConnectingToLeaders.aif</Play>
            <Dial>
				<?
                $groupBreakdown = explode("::", $caller->groupNumber);
                $staffService = new services\StaffService();
                $staffs = $staffService->getStaffInGroup($groupBreakdown[0], $groupBreakdown[1]);
                foreach($staffs as $staff) {
                    if($staff->phoneNumber != null && $staff->phoneNumber != "") {
						echo("<Number>" . $staff->phoneNumber . "</Number>");
                    }
                }
                ?>
            </Dial>
        </Response>
        <?
	} else {
		?>	
		<Response>
			<Say>You are not registered in a group.  Please ask your leaders to add you to their group on their my Week page.</Say>
			<Redirect>/twiliointest/phone/participant/</Redirect>
		</Response>
		<?
	}
} else {
	?>
    <Response>
        <Say>You are not registered for a frosh event.</Say>
        <Redirect>/twiliointest/phone/participant/</Redirect>
    </Response>
	<?
}
?>