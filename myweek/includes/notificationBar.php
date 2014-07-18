                <div class="stat-box notify-disabled remainingEventOptions hidden">
                	<i class="icon-calendar"></i>&nbsp;
                    <span class="count badge badge-warning remainingEventOptionsNumber">0</span>
                    <span class="stat-text"><a href="calendar.php?passkey=<?= $_GET['passkey'] ?>">Events Requiring Your Attention</a> </span>
	          	</div>
                <!--
	          	<div class="stat-box notify-disabled">
	            	<i class="icon-wrench"></i>
	            	<span class="count badge badge-important">23</span>
	            	<span class="stat-text">Gateway Messages </span>
	          	</div>
                -->
                <?
				$totalMissingInfo = 0;
				if(!($participant->phoneNumber != null && strlen($participant->phoneNumber))) {
					$totalMissingInfo++;
				}
				if($participant->livingStyle == "InRez" && !($participant->froshAddress != null && strlen($participant->froshAddress))) {
					$totalMissingInfo++;
				}
				if($totalMissingInfo > 0) {
					?>
	          	<div class="stat-box notify-disabled">
	            	<i class="icon-user"></i>
	            	<span class="count badge badge-warning"><?= $totalMissingInfo ?></span>
	            	<span class="stat-text"><a href="profile.php?passkey=<?= $_GET['passkey'] ?>">Missing Information</a> </span>
	            </div>
                	<?
				}
                ?>