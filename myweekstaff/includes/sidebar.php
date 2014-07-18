	        <?
            $totalMissingInfo = 0;
			if(!($staff->phoneNumber != null && strlen($staff->phoneNumber))) {
				$totalMissingInfo++;
			}
			$additionalProfile = "";
			if($totalMissingInfo > 0) {
				$additionalProfile =  "&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"count badge badge-warning\">" . $totalMissingInfo . "</span>";
			}
			?>
            <a href="index.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active"><i class="icon-dashboard"></i>Dashboard</a>
            <a href="profile.php?passkey=<?= $_GET['passkey'] ?>" class="nav-header active" ><i class="icon-user"></i>Profile<?= $additionalProfile ?></a>