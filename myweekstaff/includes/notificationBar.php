                <!--
	          	<div class="stat-box notify-disabled">
	            	<i class="icon-wrench"></i>
	            	<span class="count badge badge-important">23</span>
	            	<span class="stat-text">Gateway Messages </span>
	          	</div>
                -->
                <?
				$totalMissingInfo = 0;
				if(!($staff->phoneNumber != null && strlen($staff->phoneNumber))) {
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