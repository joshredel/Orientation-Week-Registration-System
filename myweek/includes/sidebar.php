	        <?
            $totalMissingInfo = 0;
			if(!($participant->phoneNumber != null && strlen($participant->phoneNumber))) {
				$totalMissingInfo++;
			}
			if($participant->livingStyle == "InRez" && !($participant->froshAddress != null && strlen($participant->froshAddress))) {
				$totalMissingInfo++;
			}
			$additionalProfile = "";
			if($totalMissingInfo > 0) {
				$additionalProfile =  "&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"count badge badge-warning\">" . $totalMissingInfo . "</span>";
			}
			?>
            <a href="index.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active"><i class="icon-dashboard"></i>Dashboard</a>
	        <a href="calendar.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active" ><i class="icon-calendar"></i>Calendar <span class="remainingEventOptions hidden">&nbsp;&nbsp;&nbsp;<span class="count badge badge-warning remainingEventOptionsNumber">0</span></span></a>
            <a href="registrations.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active"><i class="icon-check"></i>Registrations</a>
            <a href="profile.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active" ><i class="icon-user"></i>Profile<?= $additionalProfile ?></a>